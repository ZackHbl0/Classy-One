<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ChatPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Messagerie';
    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.chat-page';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        // Si l'utilisateur est un admin ou une secrétaire, on lui refuse l'accès
        if ($user instanceof \App\Models\User) {
            return $user->role === 'professeur';
        }

        // Autoriser l'accès si c'est un étudiant
        if ($user instanceof \App\Models\Student) {
            return true;
        }

        return false;
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    protected function getViewData(): array
    {
        return [
            'authUserId' => Auth::id(),
            'authUserName' => Auth::user()->name ?? 'Professeur',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('createGroup')
                ->label('Créer un groupe')
                ->icon('heroicon-o-user-group')
                ->form([
                    \Filament\Forms\Components\TextInput::make('name')
                        ->label('Nom du groupe')
                        ->required(),
                    \Filament\Forms\Components\Select::make('classe_id')
                        ->label('Classe associée (Optionnel)')
                        ->options(\App\Models\Classe::pluck('nomClasse', 'id'))
                        ->nullable()
                        ->searchable(),
                    \Filament\Forms\Components\Select::make('student_ids')
                        ->label('Sélectionner les étudiants')
                        ->multiple()
                        ->options(\App\Models\Student::all()->mapWithKeys(function ($student) {
                            return [$student->idStudent => trim($student->nom . ' ' . $student->prenom)];
                        }))
                        ->required()
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $conversation = \App\Models\Conversation::create([
                        'name' => $data['name'],
                        'type' => 'group',
                        'classe_id' => $data['classe_id'] ?? null,
                    ]);

                    $conversation->users()->attach(Auth::id());
                    
                    if (!empty($data['student_ids'])) {
                        $conversation->students()->attach($data['student_ids']);
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Groupe créé avec succès')
                        ->success()
                        ->send();
                    
                    $this->dispatch('group-created');
                })
        ];
    }

    public function groupInfoAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('groupInfo')
            ->modalHeading('Informations du groupe')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fermer')
            ->modalContent(fn (array $arguments) => view('filament.chat.group-info', [
                'group' => \App\Models\Conversation::with(['users', 'students'])->find($arguments['id'])
            ]));
    }
}
