<?php

namespace App\Observers;

use App\Models\Paiement;
use App\Models\AuditLog;
use App\Models\Registre;

class PaiementObserver
{
    /**
     * Handle the Paiement "created" event.
     */
    public function created(Paiement $paiement): void
    {
        $registre = Registre::with('student', 'classe')->find($paiement->Reg_id);
        $student = $registre?->student;
        $studentName = $student ? "{$student->nom} {$student->prenom}" : "Registre #{$paiement->Reg_id}";

        AuditLog::record(
            action: 'Création Paiement',
            description: "Enregistrement d'un versement de {$paiement->montant} MAD pour {$studentName} (Statut: {$paiement->statut})",
            subjectType: 'Paiement',
            subjectId: (string) $paiement->id,
            details: [
                'montant' => $paiement->montant,
                'statut' => $paiement->statut,
                'dateEcheance' => $paiement->dateEcheance,
                'student_id' => $student?->idStudent,
                'student_name' => $studentName,
                'classe' => $registre?->classe?->nomClasse,
            ]
        );
    }

    /**
     * Handle the Paiement "updated" event.
     */
    public function updated(Paiement $paiement): void
    {
        $dirty = $paiement->getDirty();
        $sensitiveKeys = ['montant', 'statut', 'dateEcheance', 'remise_montant'];
        $trackedChanges = array_intersect_key($dirty, array_flip($sensitiveKeys));

        if (empty($trackedChanges)) {
            return;
        }

        $oldValues = [];
        $newValues = [];
        foreach ($trackedChanges as $key => $newVal) {
            $oldValues[$key] = $paiement->getOriginal($key);
            $newValues[$key] = $newVal;
        }

        // Suspicious activity inspection
        app(\App\Services\SuspiciousActivityService::class)->inspectFinancialAdjustment(
            $paiement,
            'Modification Paiement',
            $oldValues,
            $newValues
        );

        $registre = Registre::with('student')->find($paiement->Reg_id);
        $student = $registre?->student;
        $studentName = $student ? "{$student->nom} {$student->prenom}" : "Registre #{$paiement->Reg_id}";

        $desc = "Mise à jour du paiement #{$paiement->id} pour {$studentName}";
        if (isset($trackedChanges['statut'])) {
            $desc .= " : Statut passé de '{$oldValues['statut']}' à '{$newValues['statut']}'";
        }
        if (isset($trackedChanges['montant'])) {
            $desc .= " : Montant modifié de {$oldValues['montant']} à {$newValues['montant']} MAD";
        }

        AuditLog::record(
            action: 'Modification Paiement',
            description: $desc,
            subjectType: 'Paiement',
            subjectId: (string) $paiement->id,
            details: [
                'student_id' => $student?->idStudent,
                'student_name' => $studentName,
                'old_values' => $oldValues,
                'new_values' => $newValues,
            ]
        );
    }

    /**
     * Handle the Paiement "deleted" event.
     */
    public function deleted(Paiement $paiement): void
    {
        $registre = Registre::with('student')->find($paiement->Reg_id);
        $student = $registre?->student;
        $studentName = $student ? "{$student->nom} {$student->prenom}" : "Registre #{$paiement->Reg_id}";

        // Alert on deletion of validated or high-value transactions
        if ($paiement->statut === 'paye' || (float) $paiement->montant >= 500) {
            app(\App\Services\SuspiciousActivityService::class)->triggerAlert(
                type: 'sudden_financial_adjustment',
                title: 'Alerte Sécurité : Suppression d\'un paiement',
                description: "Suppression du paiement #{$paiement->id} d'un montant de {$paiement->montant} MAD pour {$studentName} (Statut: {$paiement->statut}).",
                details: [
                    'paiement_id' => $paiement->id,
                    'montant' => $paiement->montant,
                    'statut' => $paiement->statut,
                    'student_id' => $student?->idStudent,
                ]
            );
        }

        AuditLog::record(
            action: 'Suppression Paiement',
            description: "Suppression du paiement #{$paiement->id} ({$paiement->montant} MAD) pour {$studentName}",
            subjectType: 'Paiement',
            subjectId: (string) $paiement->id,
            details: [
                'montant' => $paiement->montant,
                'statut' => $paiement->statut,
                'student_id' => $student?->idStudent,
            ]
        );
    }
}
