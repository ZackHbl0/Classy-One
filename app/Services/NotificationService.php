<?php

namespace App\Services;

use App\Models\Notification as NotificationModel;
use App\Models\Student;
use App\Models\Classe;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Filament\Notifications\Notification as FilamentNotification;

class NotificationService
{
    protected $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    /**
     * Send a notification to students.
     * Note: This method now primarily handles saving the notification to the DB.
     * The actual FCM broadcast is handled automatically by the Notification 
     * model's "created" hook to ensure consistency across the system.
     */
    public function send(
        string $title,
        string $message,
        string $category,
        string $targetType = 'all',
        ?array $targetIds = null
    ): void {
        $targetSummary = 'Tous les étudiants';

        if ($targetType === 'students' && !empty($targetIds)) {
            $students = Student::whereIn('idStudent', $targetIds)->get();
            $targetSummary = 'Étudiant(s): ' . $students->pluck('nom')->take(3)->implode(', ');
            if ($students->count() > 3) $targetSummary .= '...';
        } elseif ($targetType === 'classes' && !empty($targetIds)) {
            $classes = Classe::whereIn('id', $targetIds)->get();
            $targetSummary = 'Classe(s): ' . $classes->pluck('nomClasse')->implode(', ');
        }

        // Save to Database — this triggers FcmService::sendNotification() via Model Booted hook
        NotificationModel::create([
            'titre' => $title,
            'message' => $message,
            'categorie' => $category,
            'target_type' => $targetType,
            'target_ids' => $targetIds,
            'target_summary' => $targetSummary,
            'idStudent' => ($targetType === 'students' && count($targetIds ?? []) === 1) ? $targetIds[0] : null,
        ]);
    }
}
