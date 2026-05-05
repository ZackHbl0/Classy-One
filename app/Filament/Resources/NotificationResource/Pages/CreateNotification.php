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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 1. Handle targeting data mapping
        $targetType = $data['target_type'] ?? 'all';
        $targetIds = [];
        $targetSummary = 'Tous les étudiants';

        if ($targetType === 'students') {
            $targetIds = $data['target_students'] ?? [];
            $students = \App\Models\Student::whereIn('idStudent', $targetIds)->get();
            $targetSummary = 'Étudiants: ' . $students->pluck('nom')->take(3)->implode(', ');
            if ($students->count() > 3)
                $targetSummary .= '...';

            // If single student, set idStudent for convenience
            if (count($targetIds) === 1) {
                $data['idStudent'] = $targetIds[0];
            }
        } elseif ($targetType === 'classes') {
            $targetIds = $data['target_classes'] ?? [];
            $classes = \App\Models\Classe::whereIn('id', $targetIds)->get();
            $targetSummary = 'Classes: ' . $classes->pluck('nomClasse')->implode(', ');
        }

        // 2. Populate model fields
        $data['target_ids'] = $targetIds;
        $data['target_summary'] = $targetSummary;
        $data['created_at'] = \Carbon\Carbon::now();

        // 3. Clean up temporary form fields
        unset($data['target_students']);
        unset($data['target_classes']);

        return $data;
    }

    /**
     * Note: afterCreate logic removed. 
     * The Notification model's "created" hook now handles the FCM broadcast 
     * via FcmService::sendNotification() to ensure consistency and avoid 
     * duplicate sends.
     */
}
