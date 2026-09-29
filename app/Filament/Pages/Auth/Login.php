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
            ->label('Nom d\'utilisateur')
            ->placeholder('Saisir votre nom d\'utilisateur')
            ->prefixIcon('heroicon-o-user')
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