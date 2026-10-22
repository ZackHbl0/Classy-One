<?php

namespace App\Filament\Pages;

use App\Models\AuditLog;
use App\Services\TwoFactorAuthService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;

class TwoFactorSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Sécurité & Système';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'Double Authentification (2FA)';
    protected static ?string $title = 'Sécurité & Authentification à Deux Facteurs';
    protected static string $view = 'filament.pages.two-factor-settings';

    public bool $isConfiguring = false;
    public string $secret = '';
    public string $otpauthUri = '';
    public string $confirmationCode = '';
    public array $recoveryCodes = [];
    public string $passwordToDisable = '';
    public string $codeToDisable = '';
    public bool $justEnabled = false;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['admin', 'secretaire']);
    }

    public function startSetup(): void
    {
        $user = auth()->user();
        $tfaService = app(TwoFactorAuthService::class);

        $this->secret = $tfaService->generateSecret();
        $this->recoveryCodes = $tfaService->generateRecoveryCodes(8);
        $account = $user->email ?: ($user->matricule . '@classyone.ma');
        $this->otpauthUri = $tfaService->getOtpAuthUri($this->secret, $account, 'Classy-One');

        $this->confirmationCode = '';
        $this->isConfiguring = true;
        $this->justEnabled = false;
    }

    public function confirmSetup(): void
    {
        $this->validate([
            'confirmationCode' => 'required|string|size:6',
        ]);

        $tfaService = app(TwoFactorAuthService::class);
        $code = trim($this->confirmationCode);

        if (!$tfaService->verifyCode($this->secret, $code)) {
            Notification::make()
                ->title('Code TOTP incorrect')
                ->body('Le code à 6 chiffres saisi est invalide ou a expiré. Veuillez vérifier l\'heure de votre téléphone.')
                ->danger()
                ->send();
            return;
        }

        $user = auth()->user();
        $tfaService->enableForUser($user, $this->secret, $this->recoveryCodes);

        AuditLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'action' => '2FA_ENABLED',
            'subject_type' => get_class($user),
            'subject_id' => (string) $user->id,
            'description' => "Activation du 2FA TOTP par l'utilisateur.",
            'ip_address' => request()?->ip(),
            'details' => ['method' => 'TOTP_RFC6238'],
        ]);

        $this->isConfiguring = false;
        $this->justEnabled = true;

        Notification::make()
            ->title('2FA activé avec succès !')
            ->body('Votre compte est désormais protégé par l\'authentification à deux facteurs Google Authenticator.')
            ->success()
            ->send();
    }

    public function cancelSetup(): void
    {
        $this->isConfiguring = false;
        $this->secret = '';
        $this->confirmationCode = '';
        $this->recoveryCodes = [];
    }

    public function disable2FA(): void
    {
        $this->validate([
            'passwordToDisable' => 'required|string',
            'codeToDisable' => 'required|string',
        ]);

        $user = auth()->user();
        if (!Hash::check($this->passwordToDisable, $user->password)) {
            Notification::make()
                ->title('Mot de passe incorrect')
                ->body('Le mot de passe saisi ne correspond pas à votre mot de passe actuel.')
                ->danger()
                ->send();
            return;
        }

        $tfaService = app(TwoFactorAuthService::class);
        $code = trim($this->codeToDisable);
        $secret = $tfaService->getDecryptedSecret($user);
        $valid2fa = false;

        if ($secret && strlen($code) === 6 && ctype_digit($code)) {
            $valid2fa = $tfaService->verifyCode($secret, $code);
        }

        if (!$valid2fa) {
            $valid2fa = $tfaService->verifyAndConsumeRecoveryCode($user, $code);
        }

        if (!$valid2fa) {
            Notification::make()
                ->title('Code 2FA invalide')
                ->body('Le code TOTP ou code de secours saisi est incorrect. La désactivation exige impérativement la validation du second facteur.')
                ->danger()
                ->send();
            return;
        }

        $tfaService->disableForUser($user);

        AuditLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'action' => '2FA_DISABLED',
            'subject_type' => get_class($user),
            'subject_id' => (string) $user->id,
            'description' => "Désactivation du 2FA sur le compte.",
            'ip_address' => request()?->ip(),
            'details' => ['status' => 'disabled'],
        ]);

        $this->passwordToDisable = '';
        $this->codeToDisable = '';
        $this->justEnabled = false;

        Notification::make()
            ->title('2FA désactivé')
            ->body('L\'authentification à deux facteurs a été désactivée pour votre profil.')
            ->warning()
            ->send();
    }

    public function regenerateRecoveryCodes(): void
    {
        $user = auth()->user();
        if (!$user->hasTwoFactorEnabled()) {
            return;
        }

        $tfaService = app(TwoFactorAuthService::class);
        $newCodes = $tfaService->generateRecoveryCodes(8);

        $user->two_factor_recovery_codes = json_encode($tfaService->hashRecoveryCodes($newCodes));
        $user->save();

        $this->recoveryCodes = $newCodes;
        $this->justEnabled = true;

        Notification::make()
            ->title('Nouveaux codes de secours générés')
            ->body('Les anciens codes de secours ont été invalidés. Veuillez conserver ces nouveaux codes.')
            ->success()
            ->send();
    }
}
