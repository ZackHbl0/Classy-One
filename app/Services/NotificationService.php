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
     */
    public function send(
        string $title,
        string $message,
        string $category,
        string $targetType = 'all',
        ?array $targetIds = null
    ): void {
        $tokens = [];
        $students = collect();
        $targetSummary = 'Tous les étudiants';

        if ($targetType === 'students' && !empty($targetIds)) {
            $students = Student::whereIn('idStudent', $targetIds)->get();
            $targetSummary = 'Étudiant(s): ' . $students->pluck('full_name')->take(3)->implode(', ');
            if ($students->count() > 3) $targetSummary .= '...';
        } elseif ($targetType === 'classes' && !empty($targetIds)) {
            $students = Student::whereHas('registres', function ($query) use ($targetIds) {
                $query->whereIn('Cla_id', $targetIds);
            })->get();
            $classes = Classe::whereIn('id', $targetIds)->get();
            $targetSummary = 'Classe(s): ' . $classes->pluck('nomClasse')->implode(', ');
        } else {
            $students = Student::all();
        }

        // 1. Save to Database
        NotificationModel::create([
            'titre' => $title,
            'message' => $message,
            'categorie' => $category,
            'target_type' => $targetType,
            'target_ids' => $targetIds,
            'target_summary' => $targetSummary,
        ]);

        // 2. Prepare FCM Tokens
        foreach ($students as $student) {
            if ($student->fcmToken) {
                $tokens[] = $student->fcmToken;
            }
        }

        if (empty($tokens)) {
            return;
        }

        // 3. Send Push Notification
        $cloudMessage = CloudMessage::new()->withNotification([
            'title' => $title,
            'body' => $message,
        ]);

        try {
            $this->messaging->sendMulticast($cloudMessage, $tokens);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Firebase Push Error: ' . $e->getMessage());
        }
    }
}
