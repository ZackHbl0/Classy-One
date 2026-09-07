<?php

namespace App\Observers;

use App\Models\Grade;
use App\Services\FcmService;
use Illuminate\Support\Facades\Log;

class GradeObserver
{
    /**
     * Handle the Grade "created" event.
     * Dispatches push notification to both student and linked parents.
     */
    public function created(Grade $grade): void
    {
        try {
            $grade->loadMissing(['student.parents', 'course']);
            $student = $grade->student;

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
            $subjectName = $grade->subject_name ?: ($grade->course?->nomMatiere ?: 'Matière');
            $noteFormatted = number_format((float) $grade->note, 2);
            $type = $grade->type ?: 'Évaluation';

            $title = "Nouvelle note : {$studentName}";
            $message = "Une note a été attribuée en {$subjectName} : {$noteFormatted}/20 ({$type}).";

            FcmService::sendDirectPush(
                $tokens,
                $title,
                $message,
                [
                    'type' => 'Note',
                    'student_id' => (string) $student->idStudent,
                ]
            );
        } catch (\Exception $e) {
            Log::error("GradeObserver error: " . $e->getMessage());
        }
    }
}
