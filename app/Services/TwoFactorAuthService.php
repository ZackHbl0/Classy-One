<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TwoFactorAuthService
{
    protected const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a new Base32 secret key (16 uppercase characters).
     */
    public function generateSecret(int $length = 16): string
    {
        $secret = '';
        $alphabetLength = strlen(self::BASE32_ALPHABET);

        for ($i = 0; $i < $length; $i++) {
            $secret .= self::BASE32_ALPHABET[random_int(0, $alphabetLength - 1)];
        }

        return $secret;
    }

    /**
     * Generate 8 secure random recovery codes.
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $part1 = strtoupper(Str::random(5));
            $part2 = strtoupper(Str::random(5));
            $codes[] = "{$part1}-{$part2}";
        }
        return $codes;
    }

    /**
     * Hash recovery codes for secure storage.
     */
    public function hashRecoveryCodes(array $plainCodes): array
    {
        return array_map(function ($code) {
            return hash('sha256', trim(str_replace(' ', '', $code)));
        }, $plainCodes);
    }

    /**
     * Generate the standard TOTP URI for QR codes.
     */
    public function getOtpAuthUri(string $secret, string $accountName, string $issuer = 'Classy-One'): string
    {
        $encodedIssuer = rawurlencode($issuer);
        $encodedAccount = rawurlencode($accountName);
        return "otpauth://totp/{$encodedIssuer}:{$encodedAccount}?secret={$secret}&issuer={$encodedIssuer}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * Calculate current 6-digit TOTP code for a secret and optional timestamp.
     */
    public function calculateCode(string $secret, ?int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();
        $timeStep = (int) floor($timestamp / 30);

        $binarySecret = $this->base32Decode($secret);

        // Pack 64-bit integer into 8 bytes big-endian
        $binaryTime = pack('N2', ($timeStep >> 32) & 0xFFFFFFFF, $timeStep & 0xFFFFFFFF);

        $hash = hash_hmac('sha1', $binaryTime, $binarySecret, true);

        // Dynamic truncation (RFC 4226 / RFC 6238)
        $offset = ord(substr($hash, -1)) & 0x0F;
        $unpacked = unpack('N', substr($hash, $offset, 4));
        $value = $unpacked[1] & 0x7FFFFFFF;

        $code = (string) ($value % 1000000);
        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verify a 6-digit TOTP code against a secret with +/- 1 period window drift.
     * Enforces replay protection: a valid code cannot be submitted twice within its validity window.
     */
    public function verifyCode(string $secret, string $code, int $discrepancy = 1): bool
    {
        $code = trim($code);
        if (strlen($code) !== 6 || !ctype_digit($code)) {
            return false;
        }

        $currentTime = time();

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $timestamp = $currentTime + ($i * 30);
            $calculated = $this->calculateCode($secret, $timestamp);
            if (hash_equals($calculated, $code)) {
                $timeSlice = (int) floor($timestamp / 30);
                $replayKey = 'used_totp_' . hash('sha256', $secret . '_' . $code . '_' . $timeSlice);

                // If code was already consumed in this time window, reject replay
                if (\Illuminate\Support\Facades\Cache::has($replayKey)) {
                    return false;
                }

                // Mark code as used for the duration of the window (+ margin: 180s)
                \Illuminate\Support\Facades\Cache::put($replayKey, true, 180);

                return true;
            }
        }

        return false;
    }

    /**
     * Verify and consume a one-time backup recovery code for a user.
     */
    public function verifyAndConsumeRecoveryCode(User $user, string $code): bool
    {
        $normalizedCode = hash('sha256', trim(str_replace(' ', '', $code)));
        $storedCodes = json_decode($user->two_factor_recovery_codes ?? '[]', true) ?: [];

        $key = array_search($normalizedCode, $storedCodes, true);
        if ($key === false) {
            return false;
        }

        // Consume code
        unset($storedCodes[$key]);
        $user->two_factor_recovery_codes = json_encode(array_values($storedCodes));
        $user->save();

        return true;
    }

    /**
     * Enable 2FA for a user with the provided secret and backup codes.
     */
    public function enableForUser(User $user, string $plainSecret, array $plainRecoveryCodes): void
    {
        $user->two_factor_secret = Crypt::encryptString($plainSecret);
        $user->two_factor_recovery_codes = json_encode($this->hashRecoveryCodes($plainRecoveryCodes));
        $user->two_factor_confirmed_at = now();
        $user->two_factor_enabled = true;
        $user->save();
    }

    /**
     * Disable 2FA for a user.
     */
    public function disableForUser(User $user): void
    {
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->two_factor_enabled = false;
        $user->save();
    }

    /**
     * Decrypt user's stored 2FA secret.
     */
    public function getDecryptedSecret(User $user): ?string
    {
        if (empty($user->two_factor_secret)) {
            return null;
        }

        try {
            return Crypt::decryptString($user->two_factor_secret);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Decode a Base32 string to binary data.
     */
    protected function base32Decode(string $b32): string
    {
        $b32 = strtoupper(trim($b32));
        $buffer = 0;
        $bitsLeft = 0;
        $binary = '';

        for ($i = 0; $i < strlen($b32); $i++) {
            $char = $b32[$i];
            $val = strpos(self::BASE32_ALPHABET, $char);
            if ($val === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $binary .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $binary;
    }
}
