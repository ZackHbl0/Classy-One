<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Grade;
use App\Models\Notification;
use App\Models\Paiement;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SuspiciousActivityService
{
    public const THRESHOLD_FAILED_LOGINS = 3;
    public const THRESHOLD_GRADE_DELTA = 5.0;
    public const THRESHOLD_FINANCIAL_CHANGE = 500.0;

    /**
     * Record a failed login attempt and check for brute-force attacks.
     */
    public function handleFailedLogin(string $identifier, string $ip, string $guard = 'web'): int
    {
        $normalizedId = strtolower(trim($identifier));
        $cacheKey = "failed_logins:{$normalizedId}:{$ip}";

        $attempts = (int) Cache::get($cacheKey, 0) + 1;
        Cache::put($cacheKey, $attempts, now()->addMinutes(10));

        if ($attempts >= self::THRESHOLD_FAILED_LOGINS) {
            $this->triggerAlert(
                type: 'repeated_failed_logins',
                title: 'Alerte Sécurité : Tentatives répétées de connexion suspectes',
                description: "{$attempts} tentatives infructueuses de connexion détectées pour l'identifiant '{$identifier}' depuis l'IP {$ip}.",
                details: [
                    'identifier' => $identifier,
                    'ip' => $ip,
                    'guard' => $guard,
                    'attempts' => $attempts,
                    'timestamp' => now()->toIso8601String(),
                ]
            );
        }

        return $attempts;
    }

    /**
     * Reset the failed login counter after a successful login.
     */
    public function handleSuccessfulLogin(string $identifier, string $ip): void
    {
        $normalizedId = strtolower(trim($identifier));
        Cache::forget("failed_logins:{$normalizedId}:{$ip}");
    }

    /**
     * Inspect grade update for abnormal point jumps.
     */
    public function inspectGradeUpdate(Grade $grade, float $oldNote, float $newNote): void
    {
        $delta = abs($newNote - $oldNote);

        if ($delta >= self::THRESHOLD_GRADE_DELTA) {
            $student = $grade->student;
            $studentName = $student ? "{$student->prenom} {$student->nom}" : "Élève #{$grade->student_id}";

            $this->triggerAlert(
                type: 'abnormal_grade_update',
                title: 'Alerte Sécurité : Modification anormale de note',
                description: "Écart de note anormal ({$delta} points) sur la matière {$grade->subject_name} pour {$studentName} (de {$oldNote}/20 à {$newNote}/20).",
                details: [
                    'grade_id' => $grade->id,
                    'student_id' => $grade->student_id,
                    'student_name' => $studentName,
                    'matiere' => $grade->subject_name,
                    'old_note' => $oldNote,
                    'new_note' => $newNote,
                    'delta' => $delta,
                    'modified_by' => auth()->user()?->name ?? 'Non authentifié',
                ]
            );
        }
    }

    /**
     * Inspect financial modification or deletion for sudden adjustments.
     */
    public function inspectFinancialAdjustment(Paiement $paiement, string $action, array $oldValues, array $newValues): void
    {
        $isSuspicious = false;
        $reason = '';

        if (isset($oldValues['montant'], $newValues['montant'])) {
            $diff = (float) $oldValues['montant'] - (float) $newValues['montant'];
            if ($diff >= self::THRESHOLD_FINANCIAL_CHANGE) {
                $isSuspicious = true;
                $reason = "Réduction soudaine de montant de " . number_format($diff, 2) . " MAD (de {$oldValues['montant']} à {$newValues['montant']}).";
            }
        }

        if (isset($oldValues['remise_montant'], $newValues['remise_montant'])) {
            $remiseDiff = (float) $newValues['remise_montant'] - (float) $oldValues['remise_montant'];
            if ($remiseDiff >= 1000.0) {
                $isSuspicious = true;
                $reason = "Augmentation brutale de remise de " . number_format($remiseDiff, 2) . " MAD accordée sur le paiement #{$paiement->id}.";
            }
        }

        if (isset($oldValues['statut']) && $oldValues['statut'] === 'paye' && isset($newValues['statut']) && $newValues['statut'] !== 'paye') {
            $isSuspicious = true;
            $reason = "Rétrogradation suspecte d'un paiement déjà validé ('paye' vers '{$newValues['statut']}').";
        }

        if ($isSuspicious) {
            $this->triggerAlert(
                type: 'sudden_financial_adjustment',
                title: 'Alerte Sécurité : Ajustement financier suspect',
                description: "Paiement #{$paiement->id} : {$reason}",
                details: [
                    'paiement_id' => $paiement->id,
                    'action' => $action,
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                    'performed_by' => auth()->user()?->name ?? 'Système',
                ]
            );
        }
    }

    /**
     * Trigger an administrative alert: Log, AuditLog, and Notification.
     */
    public function triggerAlert(string $type, string $title, string $description, array $details = []): void
    {
        // 1. Log to emergency / alert channel
        Log::channel('single')->alert("[SECURITY ALERT] {$title} - {$description}", $details);

        // 2. Persist in tamper-proof AuditLog
        AuditLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name ?? 'Système de Sécurité',
            'user_role' => auth()->user()?->role ?? 'security_engine',
            'action' => 'SUSPICIOUS_ALERT',
            'subject_type' => $type,
            'subject_id' => (string) ($details['grade_id'] ?? $details['paiement_id'] ?? $details['identifier'] ?? '0'),
            'description' => $description,
            'ip_address' => request()?->ip() ?? '127.0.0.1',
            'details' => array_merge(['alert_type' => $type, 'title' => $title], $details),
        ]);

        // 3. Dispatch in-app notification for administrators
        try {
            Notification::create([
                'titre' => $title,
                'message' => $description,
                'categorie' => 'Securite',
                'target_type' => 'all',
                'target_summary' => 'Administrateurs',
            ]);
        } catch (\Throwable $e) {
            Log::warning("Could not persist administrative notification: " . $e->getMessage());
        }
    }
}
