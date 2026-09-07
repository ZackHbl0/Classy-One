<?php

namespace App\Observers;

use App\Models\Absence;
use App\Services\FcmService;
use Illuminate\Support\Facades\Log;

class AbsenceObserver
{
    /**
     * Handle the Absence "created" event.
     * Dispatches push notification to both student and linked parents.
     */
    public function created(Absence $absence): void
    {
        try {
            $absence->loadMissing(['student.parents']);
            $student = $absence->student;

            if (!$student) {
                return;
            }

            $tokens = [];
            if (!empty($student->fcmToken)) {
                $tokens[] = $student->fcmToken;
            }

            foreach ($student->parents as $parent) {
                if (!empty($parent->fcm_token)) {
                    $tokens[] = $parent->fcm_token;
                }
            }

            $tokens = array_unique(array_filter($tokens));

            if (empty($tokens)) {
                return;
            }

            $studentName = trim($student->nom . ' ' . $student->prenom);
            $dateStr = $absence->date ? $absence->date->format('d/m/Y') : date('d/m/Y');
            $title = "Avis d'absence : {$studentName}";
            $message = "Une absence a été signalée en {$absence->matiere} (Séance : {$absence->seance}) le {$dateStr}.";

            FcmService::sendDirectPush(
                $tokens,
                $title,
                $message,
                [
                    'type' => 'Absence',
                    'student_id' => (string) $student->idStudent,
                ]
            );
        } catch (\Exception $e) {
            Log::error("AbsenceObserver error: " . $e->getMessage());
        }
    }
}
