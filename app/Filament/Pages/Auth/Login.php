<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Checkbox;
use Filament\Actions\Action;
use Filament\Support\Enums\IconPosition;

class Login extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.login';
    protected static string $layout = 'layouts.filament-login';

    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'email' => '',
            'password' => '',
            'remember' => false,
        ]);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Matricule')
            ->placeholder('Saisir votre matricule (ex: ADM001)')
            ->prefixIcon('heroicon-o-identification')
            ->required()
            ->autocomplete('off')
            ->autofocus()
            ->default('')
            ->extraInputAttributes([
                'tabindex' => 1,
                'autocomplete' => 'off',
                'data-lpignore' => 'true',
            ]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $login = trim($data['email'] ?? '');

        // If the user inputs an email, authenticate by email
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return [
                'email' => $login,
                'password' => $data['password'],
            ];
        }

        // Otherwise authenticate by matricule (case-insensitive search)
        $user = \App\Models\User::where('matricule', $login)
            ->orWhere('matricule', strtoupper($login))
            ->first();

        $matricule = $user ? $user->matricule : strtoupper($login);

        return [
            'matricule' => $matricule,
            'password' => $data['password'],
        ];
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema($this->getFormSchema())
                    ->statePath('data'),
            ),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getTwoFactorCodeFormComponent(),
            $this->getRememberFormComponent(),
        ];
    }

    protected function getTwoFactorCodeFormComponent(): Component
    {
        return TextInput::make('two_factor_code')
            ->label('Code 2FA (Google Authenticator / Secours)')
            ->placeholder('Code à 6 chiffres ou code secours')
            ->prefixIcon('heroicon-o-shield-check')
            ->autocomplete('one-time-code')
            ->helperText('Requis si l\'authentification à deux facteurs est activée pour votre profil.')
            ->extraInputAttributes([
                'tabindex' => 3,
                'autocomplete' => 'one-time-code',
                'data-lpignore' => 'true',
            ]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Mot de passe')
            ->prefixIcon('heroicon-o-lock-closed')
            ->placeholder('••••••••')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('new-password')
            ->default('')
            ->required()
            ->extraInputAttributes([
                'tabindex' => 2,
                'autocomplete' => 'new-password',
                'data-lpignore' => 'true',
            ]);
    }

    protected function getRememberFormComponent(): Component
    {
        return Checkbox::make('remember')
            ->label('Se souvenir de moi')
            ->default(false);
    }

    public function authenticate(): ?\Filament\Http\Responses\Auth\Contracts\LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (\DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();
            return null;
        }

        $data = $this->form->getState();
        $credentials = $this->getCredentialsFromFormData($data);
        $ip = request()?->ip() ?? '127.0.0.1';

        $user = \App\Models\User::where('matricule', $credentials['matricule'] ?? '')
            ->orWhere('email', $credentials['email'] ?? '')
            ->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
            app(\App\Services\SuspiciousActivityService::class)->handleFailedLogin(
                $credentials['matricule'] ?? $credentials['email'] ?? 'inconnu',
                $ip,
                'filament'
            );
            $this->throwFailureValidationException();
        }

        // Verify Two-Factor Authentication if enabled for this user
        if ($user->hasTwoFactorEnabled()) {
            $code = trim($data['two_factor_code'] ?? '');
            $tfaService = app(\App\Services\TwoFactorAuthService::class);
            $secret = $tfaService->getDecryptedSecret($user);

            $valid = false;
            if (!empty($code) && $secret) {
                if (strlen($code) === 6 && ctype_digit($code)) {
                    $valid = $tfaService->verifyCode($secret, $code);
                }
                if (!$valid) {
                    $valid = $tfaService->verifyAndConsumeRecoveryCode($user, $code);
                }
            }

            if (!$valid) {
                app(\App\Services\SuspiciousActivityService::class)->handleFailedLogin(
                    $user->matricule ?: $user->email,
                    $ip,
                    'filament_2fa'
                );

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'data.two_factor_code' => 'Code d\'authentification à 2 facteurs (TOTP) manquant ou invalide.',
                ]);
            }
        }

        // Reset failed login attempts on success
        app(\App\Services\SuspiciousActivityService::class)->handleSuccessfulLogin(
            $user->matricule ?: $user->email,
            $ip
        );

        return parent::authenticate();
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label('Se connecter')
            ->submit('authenticate')
            ->icon('heroicon-o-arrow-right')
            ->iconPosition(IconPosition::After);
    }
}