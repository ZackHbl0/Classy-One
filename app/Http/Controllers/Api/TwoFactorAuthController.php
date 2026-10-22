<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TwoFactorAuthController extends Controller
{
    public function __construct(
        protected TwoFactorAuthService $tfaService
    ) {}

    /**
     * Initialize 2FA setup: generate secret, QR URI, and backup recovery codes.
     */
    public function setup(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!($user instanceof User)) {
            return response()->json(['status' => 'error', 'message' => 'Le 2FA TOTP est réservé aux membres du personnel.'], 403);
        }

        $secret = $this->tfaService->generateSecret();
        $recoveryCodes = $this->tfaService->generateRecoveryCodes(8);
        $accountLabel = $user->email ?: ($user->matricule . '@classyone.ma');
        $otpauthUri = $this->tfaService->getOtpAuthUri($secret, $accountLabel, 'Classy-One');

        // Store pending secret temporarily in cache to avoid saving unconfirmed secret to database
        cache()->put("pending_2fa_secret_{$user->id}", $secret, now()->addMinutes(15));
        cache()->put("pending_2fa_codes_{$user->id}", $recoveryCodes, now()->addMinutes(15));

        return response()->json([
            'status' => 'success',
            'secret' => $secret,
            'otpauth_uri' => $otpauthUri,
            'recovery_codes' => $recoveryCodes,
            'message' => 'Scannez le code QR dans Google Authenticator ou entrez la clé secrète manuellement.',
        ]);
    }

    /**
     * Confirm 2FA setup by validating a 6-digit TOTP code.
     */
    public function confirm(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();
        if (!($user instanceof User)) {
            return response()->json(['status' => 'error', 'message' => 'Action non autorisée.'], 403);
        }

        $secret = cache()->get("pending_2fa_secret_{$user->id}");
        $recoveryCodes = cache()->get("pending_2fa_codes_{$user->id}");

        if (!$secret || !$recoveryCodes) {
            return response()->json([
                'status' => 'error',
                'message' => 'Session de configuration 2FA expirée. Veuillez relancer la configuration.',
            ], 422);
        }

        $code = trim($request->input('code'));
        if (!$this->tfaService->verifyCode($secret, $code)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Code d\'authentification incorrect. Vérifiez l\'heure de votre appareil et réessayez.',
            ], 422);
        }

        // Enable 2FA permanently
        $this->tfaService->enableForUser($user, $secret, $recoveryCodes);
        cache()->forget("pending_2fa_secret_{$user->id}");
        cache()->forget("pending_2fa_codes_{$user->id}");

        AuditLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'action' => '2FA_ENABLED',
            'subject_type' => User::class,
            'subject_id' => (string) $user->id,
            'description' => "Activation réussie de l'authentification à deux facteurs (2FA TOTP).",
            'ip_address' => $request->ip(),
            'details' => ['method' => 'TOTP_RFC6238'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'L\'authentification à deux facteurs est maintenant activée pour votre compte.',
            'two_factor_enabled' => true,
        ]);
    }

    /**
     * Disable 2FA with current password confirmation.
     */
    public function disable(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
            'code' => 'required|string',
        ]);

        $user = $request->user();
        if (!($user instanceof User)) {
            return response()->json(['status' => 'error', 'message' => 'Action non autorisée.'], 403);
        }

        if (!Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mot de passe incorrect.',
            ], 422);
        }

        $code = trim($request->input('code'));
        $secret = $this->tfaService->getDecryptedSecret($user);
        $valid2fa = false;

        if ($secret && strlen($code) === 6 && ctype_digit($code)) {
            $valid2fa = $this->tfaService->verifyCode($secret, $code);
        }

        if (!$valid2fa) {
            $valid2fa = $this->tfaService->verifyAndConsumeRecoveryCode($user, $code);
        }

        if (!$valid2fa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Code 2FA (TOTP ou code de secours) invalide. La désactivation requiert impérativement la validation du second facteur.',
            ], 422);
        }

        $this->tfaService->disableForUser($user);

        AuditLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'action' => '2FA_DISABLED',
            'subject_type' => User::class,
            'subject_id' => (string) $user->id,
            'description' => "Désactivation de l'authentification à deux facteurs (2FA).",
            'ip_address' => $request->ip(),
            'details' => ['status' => 'disabled'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'L\'authentification à deux facteurs a été désactivée.',
            'two_factor_enabled' => false,
        ]);
    }

    /**
     * Challenge verification during login: verify TOTP or recovery code.
     */
    public function challengeVerify(Request $request): JsonResponse
    {
        $request->validate([
            'pre_auth_token' => 'required|string',
            'code' => 'required|string',
        ]);

        $tokenData = cache()->get("2fa_preauth_" . $request->input('pre_auth_token'));
        if (!$tokenData) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jeton d\'authentification préliminaire expiré ou invalide.',
            ], 401);
        }

        $user = User::find($tokenData['user_id']);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Utilisateur introuvable.'], 404);
        }

        $inputCode = trim($request->input('code'));
        $secret = $this->tfaService->getDecryptedSecret($user);

        $verified = false;

        // Try TOTP code first
        if (strlen($inputCode) === 6 && ctype_digit($inputCode) && $secret) {
            $verified = $this->tfaService->verifyCode($secret, $inputCode);
        }

        // Try recovery code if TOTP failed or user supplied backup code format
        if (!$verified) {
            $verified = $this->tfaService->verifyAndConsumeRecoveryCode($user, $inputCode);
            if ($verified) {
                AuditLog::create([
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_role' => $user->role,
                    'action' => '2FA_RECOVERY_CODE_USED',
                    'subject_type' => User::class,
                    'subject_id' => (string) $user->id,
                    'description' => "Connexion via code de secours 2FA à usage unique.",
                    'ip_address' => $request->ip(),
                    'details' => ['method' => 'recovery_code'],
                ]);
            }
        }

        if (!$verified) {
            return response()->json([
                'status' => 'error',
                'message' => 'Code 2FA ou code de récupération invalide.',
            ], 422);
        }

        // Clean up pre-auth token
        cache()->forget("2fa_preauth_" . $request->input('pre_auth_token'));

        // Issue Sanctum token
        $token = $user->createToken('staff_2fa_auth_token', ["role:{$user->role}"])->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Authentification à deux facteurs validée avec succès.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'two_factor_enabled' => true,
            ],
        ]);
    }

    /**
     * Handle administrative staff (admin / secretaire) API login with 2FA enforcement.
     */
    public function staffLogin(Request $request): JsonResponse
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = trim($request->input('login'));
        $user = User::where(function ($q) use ($login) {
            $q->where('email', $login)
              ->orWhere('matricule', $login);
        })->whereIn('role', ['admin', 'secretaire'])->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            app(\App\Services\SuspiciousActivityService::class)->handleFailedLogin($login, $request->ip(), 'staff');
            return response()->json([
                'status' => 'error',
                'message' => 'Identifiants administratifs incorrects.',
            ], 401);
        }

        // Reset failed logins counter
        app(\App\Services\SuspiciousActivityService::class)->handleSuccessfulLogin($login, $request->ip());

        // Mandatory 2FA for staff:
        if ($user->hasTwoFactorEnabled()) {
            $preAuthToken = \Illuminate\Support\Str::random(64);
            cache()->put("2fa_preauth_{$preAuthToken}", [
                'user_id' => $user->id,
                'role' => $user->role,
                'ip' => $request->ip(),
            ], now()->addMinutes(5));

            return response()->json([
                'status' => '2fa_required',
                'two_factor_required' => true,
                'pre_auth_token' => $preAuthToken,
                'message' => 'Authentification à deux facteurs requise. Veuillez valider votre code via /api/2fa/verify.',
            ], 200);
        }

        // Un-enrolled staff cannot receive general tokens without enrolling in 2FA:
        return response()->json([
            'status' => '2fa_enrollment_required',
            'two_factor_required' => true,
            'message' => 'Enrôlement 2FA obligatoire pour ce compte administratif avant de pouvoir générer un jeton API.',
        ], 403);
    }
}
