<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components;
use Filament\Notifications\Notification as FilamentNotification;
use App\Models\Student;
use App\Models\Notification as NotificationModel;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;

class PushNotifications extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static string $view = 'filament.pages.push-notifications';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\TextInput::make('title')
                    ->label('Titre')
                    ->required(),
                Components\Textarea::make('body')
                    ->label('Message')
                    ->required(),
                Components\Select::make('category')
                    ->label('Catégorie (optionnel)')
                    ->options([
                        'Examen' => 'Examen',
                        'Sortie' => 'Sortie',
                        'Réunion' => 'Réunion',
                        'Information' => 'Information',
                        'Urgent' => 'Urgent',
                    ]),
                Components\Select::make('target_students')
                    ->label('Étudiants cibles (Laisser vide pour tous)')
                    ->multiple()
                    ->options(Student::pluck('nom', 'idStudent')->toArray()),
            ])
            ->statePath('data');
    }

    public function sendNotification(Messaging $messaging): void
    {
        $data = $this->form->getState();
        $tokens = [];

        if (!empty($data['target_students'])) {
            $students = Student::whereIn('idStudent', $data['target_students'])->get();
        } else {
            $students = Student::all();
        }

        // Save to DB
        $notificationModel = NotificationModel::create([
            'titre' => $data['title'],
            'description' => $data['body'],
            'categorie' => $data['category'] ?? 'Information',
        ]);

        foreach ($students as $student) {
            if ($student->fcmToken) {
                $tokens[] = $student->fcmToken;
            }
        }

        if (empty($tokens)) {
            FilamentNotification::make()
                ->title('Erreur')
                ->body('Aucun étudiant cible ne possède de token FCM.')
                ->danger()
                ->send();
            return;
        }

        $message = CloudMessage::new()->withNotification([
            'title' => $data['title'],
            'body' => $data['body'],
        ]);

        try {
            $messaging->sendMulticast($message, $tokens);
            FilamentNotification::make()
                ->title('Succès')
                ->body('Notification envoyée avec succès.')
                ->success()
                ->send();
            $this->form->fill();
        } catch (\Exception $e) {
            FilamentNotification::make()
                ->title('Erreur')
                ->body('Échec de l\'envoi : ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
