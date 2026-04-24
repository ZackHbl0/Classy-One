<?php

namespace App\Filament\Resources\NotificationResource\Widgets;

use Filament\Widgets\Widget;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification as FilamentNotification;
use App\Models\Student;
use App\Models\Classe;
use App\Models\Notification as NotificationModel;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;

class NotificationFormWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.resources.notification-resource.widgets.notification-form-widget';

    protected int | string | array $columnSpan = 'full';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make('Nouvelle Notification')
                    ->description('Créez et envoyez une notification à vos étudiants.')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Components\TextInput::make('titre')
                                    ->label('Titre')
                                    ->required(),
                                Components\Select::make('categorie')
                                    ->label('Catégorie')
                                    ->options([
                                        'Planning' => 'Planning',
                                        'Examen' => 'Examen',
                                        'Événement' => 'Événement',
                                        'Paiement' => 'Paiement',
                                        'Urgent' => 'Urgent',
                                        'Information' => 'Information',
                                    ])
                                    ->default('Information')
                                    ->required(),
                            ]),
                        Components\Textarea::make('message')
                            ->label('Message')
                            ->required()
                            ->columnSpanFull(),

                        Components\Grid::make(3)
                            ->schema([
                                Components\Radio::make('target_type')
                                    ->label('Type de cible')
                                    ->options([
                                        'all' => 'Tous les étudiants',
                                        'classes' => 'Par Classe',
                                        'students' => 'Par Étudiant',
                                    ])
                                    ->default('all')
                                    ->live()
                                    ->required(),

                                Components\Select::make('target_classes')
                                    ->label('Sélectionner les classes')
                                    ->multiple()
                                    ->options(Classe::pluck('nomClasse', 'id')->toArray())
                                    ->visible(fn(Get $get) => $get('target_type') === 'classes')
                                    ->required(fn(Get $get) => $get('target_type') === 'classes')
                                    ->searchable(),

                                Components\Select::make('target_students')
                                    ->label('Sélectionner les étudiants')
                                    ->multiple()
                                    ->options(\App\Models\Student::all()->mapWithKeys(fn($s) => [$s->idStudent => $s->full_name])->toArray())
                                    ->visible(fn(Get $get) => $get('target_type') === 'students')
                                    ->required(fn(Get $get) => $get('target_type') === 'students')
                                    ->searchable(),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function sendNotification(Messaging $messaging): void
    {
        $data = $this->form->getState();
        $tokens = [];
        $targetSummary = 'Tous les étudiants';
        $targetIds = null;

        if ($data['target_type'] === 'students') {
            $students = \App\Models\Student::whereIn('idStudent', $data['target_students'])->get();
            $targetIds = $data['target_students'];
            $targetSummary = 'Étudiant(s): ' . $students->pluck('full_name')->take(3)->implode(', ');
            if ($students->count() > 3) $targetSummary .= '...';
        } elseif ($data['target_type'] === 'classes') {
            $classes = \App\Models\Classe::whereIn('id', $data['target_classes'])->get();
            $targetIds = $data['target_classes'];
            $targetSummary = 'Classe(s): ' . $classes->pluck('nomClasse')->implode(', ');

            // Get students in these classes
            $students = Student::whereHas('registres', function ($query) use ($data) {
                $query->whereIn('Cla_id', $data['target_classes']);
            })->get();
        } else {
            $students = Student::all();
        }

        // Save to DB
        NotificationModel::create([
            'titre' => $data['titre'],
            'message' => $data['message'],
            'categorie' => $data['categorie'],
            'target_type' => $data['target_type'],
            'target_ids' => $targetIds,
            'target_summary' => $targetSummary,
        ]);

        foreach ($students as $student) {
            if ($student->fcmToken) {
                $tokens[] = $student->fcmToken;
            }
        }

        if (empty($tokens)) {
            FilamentNotification::make()
                ->title('Information')
                ->body('Notification enregistrée dans l\'historique, mais aucun étudiant cible n\'a de token FCM pour le push.')
                ->warning()
                ->send();

            $this->form->fill();
            $this->dispatch('refreshNotificationsTable');
            return;
        }

        $message = CloudMessage::new()->withNotification([
            'title' => $data['titre'],
            'body' => $data['message'],
        ]);

        try {
            $messaging->sendMulticast($message, $tokens);
            FilamentNotification::make()
                ->title('Succès')
                ->body('Notification envoyée et enregistrée.')
                ->success()
                ->send();
            $this->form->fill();
            $this->dispatch('refreshNotificationsTable');
        } catch (\Exception $e) {
            FilamentNotification::make()
                ->title('Erreur Push')
                ->body('Notification enregistrée, mais échec de l\'envoi push : ' . $e->getMessage())
                ->danger()
                ->send();
            $this->form->fill();
            $this->dispatch('refreshNotificationsTable');
        }
    }
}
