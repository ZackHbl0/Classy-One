<?php

namespace App\Observers;

use App\Models\Grade;
use App\Models\AuditLog;
use App\Models\Student;

class GradeObserver
{
    /**
     * Handle the Grade "created" event.
     */
    public function created(Grade $grade): void
    {
        $student = Student::find($grade->student_id);
        $studentName = $student ? "{$student->nom} {$student->prenom}" : "Élève #{$grade->student_id}";

        AuditLog::record(
            action: 'Création Note',
            description: "Ajout d'une note ({$grade->note}/20 - {$grade->type}) en {$grade->subject_name} pour {$studentName}",
            subjectType: 'Grade',
            subjectId: (string) $grade->id,
            details: [
                'student_id' => $grade->student_id,
                'student_name' => $studentName,
                'classe_id' => $grade->classe_id,
                'matiere' => $grade->subject_name,
                'note' => $grade->note,
                'coefficient' => $grade->coefficient,
                'type' => $grade->type,
                'semester' => $grade->semester,
            ]
        );
    }

    /**
     * Handle the Grade "updated" event.
     */
    public function updated(Grade $grade): void
    {
        $dirty = $grade->getDirty();
        $sensitiveKeys = ['note', 'coefficient', 'type', 'semester', 'subject_name', 'comment'];
        $trackedChanges = array_intersect_key($dirty, array_flip($sensitiveKeys));

        if (empty($trackedChanges)) {
            return;
        }

        $oldValues = [];
        $newValues = [];
        foreach ($trackedChanges as $key => $newVal) {
            $oldValues[$key] = $grade->getOriginal($key);
            $newValues[$key] = $newVal;
        }

        $student = Student::find($grade->student_id);
        $studentName = $student ? "{$student->nom} {$student->prenom}" : "Élève #{$grade->student_id}";

        $desc = "Modification de note pour {$studentName} en {$grade->subject_name}";
        if (isset($trackedChanges['note'])) {
            $desc .= " : Note modifiée de {$oldValues['note']}/20 à {$newValues['note']}/20";
            app(\App\Services\SuspiciousActivityService::class)->inspectGradeUpdate(
                $grade,
                (float) $oldValues['note'],
                (float) $newValues['note']
            );
        }

        AuditLog::record(
            action: 'Modification Note',
            description: $desc,
            subjectType: 'Grade',
            subjectId: (string) $grade->id,
            details: [
                'student_id' => $grade->student_id,
                'student_name' => $studentName,
                'old_values' => $oldValues,
                'new_values' => $newValues,
            ]
        );
    }

    /**
     * Handle the Grade "deleted" event.
     */
    public function deleted(Grade $grade): void
    {
        $student = Student::find($grade->student_id);
        $studentName = $student ? "{$student->nom} {$student->prenom}" : "Élève #{$grade->student_id}";

        AuditLog::record(
            action: 'Suppression Note',
            description: "Suppression de la note ({$grade->note}/20 - {$grade->type}) de {$studentName}",
            subjectType: 'Grade',
            subjectId: (string) $grade->id,
            details: [
                'deleted_grade' => [
                    'student_id' => $grade->student_id,
                    'student_name' => $studentName,
                    'note' => $grade->note,
                    'type' => $grade->type,
                    'matiere' => $grade->subject_name,
                ]
            ]
        );
    }
}
