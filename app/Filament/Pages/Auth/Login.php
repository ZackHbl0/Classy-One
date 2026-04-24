<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Action;

class Login extends BaseLogin
{
    /**
     * @var view-string
     */
    protected static string $view = 'filament.pages.auth.login';

    /**
     * @var view-string
     */
    protected static string $layout = 'filament-panels::components.layout.base';

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Nom d\'utilisateur')
            ->placeholder('Saisir votre nom d\'utilisateur')
            ->prefixIcon('heroicon-o-user')
            // Using email type as it's default in filament, but if they want pure username we can remove ->email() 
            // In the default login it forces email type, if we want text we just omit ->email() if they use username. 
            // Often, it's really an email. The image says "Saisir votre nom d'utilisateur", but often users keep email structure. 
            // Let's keep it without ->email() modifier to allow typical strings, but wait, the default BaseLogin checks Filament::auth()->attempt with 'email' => $data['email']. So whether it has email validation or not, it works.
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Mot de passe')
            ->prefixIcon('heroicon-o-lock-closed')
            ->placeholder('••••••••')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required()
            ->extraInputAttributes(['tabindex' => 2]);
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label('Se connecter')
            ->submit('authenticate');
    }
}
