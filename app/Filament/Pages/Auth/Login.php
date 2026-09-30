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

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label('Se connecter')
            ->submit('authenticate')
            ->icon('heroicon-o-arrow-right')
            ->iconPosition(IconPosition::After);
    }
}