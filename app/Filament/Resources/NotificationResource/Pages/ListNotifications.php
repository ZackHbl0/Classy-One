<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\Student;
use App\Models\Classe;
use App\Models\Notification as NotificationModel;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Filament\Notifications\Notification as FilamentNotification;

class ListNotifications extends ListRecords
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouvelle Notification')
                ->modalHeading('Créer une Notification')
                ->modalWidth('2xl')
                ->createAnother(false)
                ->mutateFormDataUsing(function (array $data): array {
                    if ($data['target_type'] === 'students') {
                        $students = Student::whereIn('idStudent', $data['target_students'])->get();
                        $data['target_ids'] = $data['target_students'];
                        $data['target_summary'] = 'Étudiant(s): ' . $students->pluck('full_name')->take(3)->implode(', ');
                        if ($students->count() > 3) $data['target_summary'] .= '...';
                    } elseif ($data['target_type'] === 'classes') {
                        $classes = Classe::whereIn('id', $data['target_classes'])->get();
                        $data['target_ids'] = $data['target_classes'];
                        $data['target_summary'] = 'Classe(s): ' . $classes->pluck('nomClasse')->implode(', ');
                    } else {
                        $data['target_ids'] = null;
                        $data['target_summary'] = 'Tous les étudiants';
                    }

                    return $data;
                })
                ->after(function (NotificationModel $record, Messaging $messaging) {
                    $this->sendPushNotification($record, $messaging);
                }),
        ];
    }

    protected function sendPushNotification(NotificationModel $record, Messaging $messaging): void
    {
        $tokens = [];
        $students = collect();

        if ($record->target_type === 'students' && $record->target_ids) {
            $students = Student::whereIn('idStudent', $record->target_ids)->get();
        } elseif ($record->target_type === 'classes' && $record->target_ids) {
            $students = Student::whereHas('registres', function ($query) use ($record) {
                $query->whereIn('Cla_id', $record->target_ids);
            })->get();
        } else {
            $students = Student::all();
        }

        foreach ($students as $student) {
            if ($student->fcmToken) {
                $tokens[] = $student->fcmToken;
            }
        }

        if (empty($tokens)) {
            FilamentNotification::make()
                ->title('Information')
                ->body('Notification enregistrée, mais aucun étudiant cible n\'a de token FCM pour le push.')
                ->warning()
                ->send();
            return;
        }

        $message = CloudMessage::new()->withNotification([
            'title' => $record->titre,
            'body' => $record->message,
        ]);

        try {
            $messaging->sendMulticast($message, $tokens);
            FilamentNotification::make()
                ->title('Succès')
                ->body('Notification envoyée et enregistrée.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            FilamentNotification::make()
                ->title('Erreur Push')
                ->body('Notification enregistrée, mais échec de l\'envoi push : ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
