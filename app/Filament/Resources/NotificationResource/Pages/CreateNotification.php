<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Models\Student;
use Carbon\Carbon;

class CreateNotification extends CreateRecord
{
    protected static string $resource = NotificationResource::class;

    public array $targetStudents = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extract target_students so Eloquent doesn't crash trying to save it
        $this->targetStudents = $data['target_students'] ?? [];
        unset($data['target_students']);

        // Set created_at explicitly since Notification model has $timestamps = false
        $data['created_at'] = Carbon::now();

        return $data;
    }

    protected function afterCreate(): void
    {
        // Retrieve Firebase Messaging
        try {
            $messaging = app(Messaging::class);
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Avertissement Firebase')
                ->body('Le service Firebase Messaging n\'est pas configuré. Le message a été enregistré localement.')
                ->warning()
                ->send();
            return;
        }

        $tokens = [];

        // Check if we selected specific students
        if (!empty($this->targetStudents)) {
            $students = Student::whereIn('idStudent', $this->targetStudents)->get();
        } else {
            $students = Student::all(); // Blast to everyone
        }

        foreach ($students as $student) {
            if (!empty($student->fcmToken)) {
                $tokens[] = $student->fcmToken;
            }
        }

        if (empty($tokens)) {
            \Filament\Notifications\Notification::make()
                ->title('Avertissement')
                ->body('Notification enregistrée dans l\'historique, mais aucun étudiant cible ne possède de token FCM.')
                ->warning()
                ->send();
            return;
        }

        $message = CloudMessage::new()->withNotification([
            'title' => $this->record->titre,
            'body' => $this->record->message,
        ]);

        try {
            $messaging->sendMulticast($message, $tokens);
            
            \Filament\Notifications\Notification::make()
                ->title('Succès')
                ->body('Notification Push envoyée avec succès à ' . count($tokens) . ' appareil(s).')
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Erreur d\'envoi Push')
                ->body('Échec : ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
