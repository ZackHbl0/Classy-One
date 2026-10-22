<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Paiement;
use App\Models\Student;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentService
{
    /**
     * Record a new payment with strict database transaction integrity and audit logging.
     */
    public function recordPayment(array $data, ?User $staff = null): Paiement
    {
        return DB::transaction(function () use ($data, $staff) {
            $year = date('Y');
            
            // Generate robust reference if not provided
            if (empty($data['reference'])) {
                $count = Paiement::whereYear('created_at', $year)->count() + 1;
                $random = strtoupper(bin2hex(random_bytes(2)));
                $data['reference'] = sprintf('REF-%s-%04d%s', $year, $count, $random);
            }

            if ($staff && empty($data['recorded_by'])) {
                $data['recorded_by'] = $staff->id;
            }

            $paiement = Paiement::create($data);

            // Fetch student information for audit logging
            $student = $paiement->student;
            $studentName = $student ? $student->full_name : 'Inconnu';
            $matricule = $student ? $student->matricule : 'N/A';

            AuditLog::record(
                action: 'PAIEMENT_CREATE',
                description: "Enregistrement du paiement {$paiement->reference} de {$paiement->montant} MAD pour l'élève {$studentName} ({$matricule}) [Mode: {$paiement->modePaiement}, Statut: {$paiement->statut}].",
                subjectType: Paiement::class,
                subjectId: (string) $paiement->id,
                details: [
                    'reference' => $paiement->reference,
                    'montant' => (float) $paiement->montant,
                    'statut' => $paiement->statut,
                    'modePaiement' => $paiement->modePaiement,
                    'dateEcheance' => $paiement->dateEcheance?->format('Y-m-d'),
                    'student_id' => $student?->idStudent,
                    'student_name' => $studentName,
                    'student_matricule' => $matricule,
                    'recorded_by' => $staff?->name ?? 'Système',
                ],
                actor: $staff
            );

            return $paiement;
        });
    }

    /**
     * Update an existing payment inside a transaction with full audit trail.
     */
    public function updatePayment(Paiement $paiement, array $data, ?User $staff = null): Paiement
    {
        return DB::transaction(function () use ($paiement, $data, $staff) {
            $oldValues = [
                'montant' => (float) $paiement->montant,
                'statut' => $paiement->statut,
                'modePaiement' => $paiement->modePaiement,
                'dateEcheance' => $paiement->dateEcheance?->format('Y-m-d'),
            ];

            $paiement->update($data);

            $student = $paiement->student;
            $studentName = $student ? $student->full_name : 'Inconnu';

            AuditLog::record(
                action: 'PAIEMENT_UPDATE',
                description: "Modification du paiement {$paiement->reference} (Élève: {$studentName}) : Statut '{$oldValues['statut']}' -> '{$paiement->statut}', Montant {$oldValues['montant']} -> {$paiement->montant} MAD.",
                subjectType: Paiement::class,
                subjectId: (string) $paiement->id,
                details: [
                    'reference' => $paiement->reference,
                    'avant' => $oldValues,
                    'apres' => [
                        'montant' => (float) $paiement->montant,
                        'statut' => $paiement->statut,
                        'modePaiement' => $paiement->modePaiement,
                        'dateEcheance' => $paiement->dateEcheance?->format('Y-m-d'),
                    ],
                    'updated_by' => $staff?->name ?? 'Système',
                ],
                actor: $staff
            );

            return $paiement;
        });
    }

    /**
     * Delete a payment inside a transaction.
     */
    public function deletePayment(Paiement $paiement, ?User $staff = null): bool
    {
        return DB::transaction(function () use ($paiement, $staff) {
            $ref = $paiement->reference;
            $montant = $paiement->montant;
            $id = $paiement->id;
            $student = $paiement->student;
            $studentName = $student ? $student->full_name : 'Inconnu';

            AuditLog::record(
                action: 'PAIEMENT_DELETE',
                description: "Suppression du paiement {$ref} d'un montant de {$montant} MAD (Élève: {$studentName}).",
                subjectType: Paiement::class,
                subjectId: (string) $id,
                details: [
                    'reference' => $ref,
                    'montant' => (float) $montant,
                    'student_name' => $studentName,
                    'deleted_by' => $staff?->name ?? 'Système',
                ],
                actor: $staff
            );

            return $paiement->delete();
        });
    }

    /**
     * Apply or modify a scholarship/discount for a student inside a database transaction.
     */
    public function updateStudentDiscount(Student $student, float $remiseMontant, string $remiseType, ?string $motif = null, ?User $staff = null): Student
    {
        return DB::transaction(function () use ($student, $remiseMontant, $remiseType, $motif, $staff) {
            $oldDiscount = (float) ($student->remise_montant ?? 0.0);
            $oldType = $student->remise_type ?? 'aucune';

            $student->remise_montant = max(0.0, $remiseMontant);
            $student->remise_type = $remiseType;
            $student->remise_motif = $motif;
            $student->save();

            AuditLog::record(
                action: 'STUDENT_DISCOUNT_UPDATE',
                description: "Mise à jour bourse/remise pour {$student->full_name} ({$student->matricule}) : {$oldDiscount} MAD ({$oldType}) -> {$student->remise_montant} MAD ({$student->remise_type}).",
                subjectType: Student::class,
                subjectId: (string) $student->idStudent,
                details: [
                    'matricule' => $student->matricule,
                    'base_tuition' => $student->base_tuition,
                    'remise_montant' => $student->remise_montant,
                    'remise_type' => $student->remise_type,
                    'remise_motif' => $student->remise_motif,
                    'net_target_tuition' => $student->frais_scolarite_total,
                    'staff' => $staff?->name ?? 'Système',
                ],
                actor: $staff
            );

            return $student;
        });
    }

    /**
     * Generate a professional PDF payment receipt for a given transaction.
     */
    public function generateReceiptPdf(Paiement $paiement)
    {
        $paiement->loadMissing(['registre.student.classe', 'registre.student.parents', 'recordedBy']);

        $student = $paiement->student ?? $paiement->registre?->student;
        $classe = $student?->classe;
        $staff = $paiement->recordedBy ?? auth()->user();

        $data = [
            'paiement' => $paiement,
            'student' => $student,
            'classe' => $classe,
            'staff' => $staff,
            'school_name' => 'Groupe Scolaire OSBT — Classy-One',
            'school_address' => 'Avenue Mohammed VI, Casablanca, Maroc',
            'school_phone' => '+212 5 22 00 00 00',
            'school_email' => 'contact@osbt.com',
            'date_impression' => Carbon::now()->format('d/m/Y H:i'),
        ];

        return Pdf::loadView('pdf.receipt', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);
    }

    /**
     * Compute comprehensive financial analytics, revenue tracking, and breakdown.
     */
    public function getFinancialSummary(?int $year = null, ?int $month = null): array
    {
        $year = $year ?? (int) date('Y');

        // Total payments collected
        $totalCollectedAllTime = (float) Paiement::whereIn(DB::raw('LOWER(statut)'), ['payé', 'paye', 'validé', 'valide'])->sum('montant');
        $totalPendingAllTime = (float) Paiement::whereNotIn(DB::raw('LOWER(statut)'), ['payé', 'paye', 'validé', 'valide'])->sum('montant');

        // Total year-to-date
        $yearCollected = (float) Paiement::whereIn(DB::raw('LOWER(statut)'), ['payé', 'paye', 'validé', 'valide'])
            ->whereYear('dateEcheance', $year)
            ->sum('montant');

        $yearPending = (float) Paiement::whereNotIn(DB::raw('LOWER(statut)'), ['payé', 'paye', 'validé', 'valide'])
            ->whereYear('dateEcheance', $year)
            ->sum('montant');

        // Selected Month handling
        $selectedMonthNum = ($month && $month >= 1 && $month <= 12) ? (int) $month : null;
        $selectedMonthName = $selectedMonthNum 
            ? ucfirst(Carbon::create($year, $selectedMonthNum, 1)->locale('fr')->translatedFormat('F'))
            : "Tous les mois ($year)";

        $monthCollected = $selectedMonthNum
            ? (float) Paiement::whereIn(DB::raw('LOWER(statut)'), ['payé', 'paye', 'validé', 'valide'])
                ->whereYear('dateEcheance', $year)
                ->whereMonth('dateEcheance', $selectedMonthNum)
                ->sum('montant')
            : $yearCollected;

        $monthPending = $selectedMonthNum
            ? (float) Paiement::whereNotIn(DB::raw('LOWER(statut)'), ['payé', 'paye', 'validé', 'valide'])
                ->whereYear('dateEcheance', $year)
                ->whereMonth('dateEcheance', $selectedMonthNum)
                ->sum('montant')
            : $yearPending;

        // Global student tuition calculations
        $students = Student::all();
        $totalBaseTuition = 0.0;
        $totalDiscountsGranted = 0.0;
        $totalTargetTuition = 0.0;
        $totalRemainingBalance = 0.0;

        foreach ($students as $st) {
            $totalBaseTuition += $st->base_tuition;
            $totalDiscountsGranted += $st->remise_montant_total;
            $totalTargetTuition += $st->frais_scolarite_total;
            $totalRemainingBalance += $st->reste_a_payer;
        }

        // Method breakdown (Filtered by year, and optionally by month)
        $methodQuery = Paiement::whereIn(DB::raw('LOWER(statut)'), ['payé', 'paye', 'validé', 'valide'])
            ->whereYear('dateEcheance', $year);

        if ($selectedMonthNum) {
            $methodQuery->whereMonth('dateEcheance', $selectedMonthNum);
        }

        $methodBreakdown = $methodQuery
            ->select('modePaiement', DB::raw('SUM(montant) as total_amount'), DB::raw('COUNT(*) as count'))
            ->groupBy('modePaiement')
            ->get()
            ->mapWithKeys(function ($item) {
                $method = $item->modePaiement ?: 'Espèce';
                return [$method => [
                    'amount' => (float) $item->total_amount,
                    'count' => (int) $item->count,
                ]];
            })->toArray();

        // If no methods found for this specific month, fallback to year or empty array with default keys
        if (empty($methodBreakdown)) {
            $methodBreakdown = Paiement::whereIn(DB::raw('LOWER(statut)'), ['payé', 'paye', 'validé', 'valide'])
                ->select('modePaiement', DB::raw('SUM(montant) as total_amount'), DB::raw('COUNT(*) as count'))
                ->groupBy('modePaiement')
                ->get()
                ->mapWithKeys(fn($item) => [
                    ($item->modePaiement ?: 'Espèce') => [
                        'amount' => (float) $item->total_amount,
                        'count' => (int) $item->count,
                    ]
                ])->toArray();
        }

        // Monthly breakdown for selected year
        $monthlyRevenue = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthLabel = Carbon::create($year, $m, 1)->locale('fr')->translatedFormat('F');
            $val = (float) Paiement::whereIn(DB::raw('LOWER(statut)'), ['payé', 'paye', 'validé', 'valide'])
                ->whereYear('dateEcheance', $year)
                ->whereMonth('dateEcheance', $m)
                ->sum('montant');
            $monthlyRevenue[] = [
                'month_num' => $m,
                'month_name' => ucfirst($monthLabel),
                'amount' => $val,
                'is_selected' => ($selectedMonthNum === $m),
            ];
        }

        // Recent payment transactions
        $txQuery = Paiement::with('registre.student')
            ->whereYear('dateEcheance', $year);

        if ($selectedMonthNum) {
            $monthTxCount = (clone $txQuery)->whereMonth('dateEcheance', $selectedMonthNum)->count();
            if ($monthTxCount > 0) {
                $txQuery->whereMonth('dateEcheance', $selectedMonthNum);
            }
        }

        $recentTransactions = $txQuery
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'reference' => $p->reference,
                    'student_name' => $p->student?->full_name ?? 'Élève inconnu',
                    'matricule' => $p->student?->matricule ?? 'N/A',
                    'montant' => (float) $p->montant,
                    'modePaiement' => $p->modePaiement ?? 'Espèce',
                    'statut' => $p->statut,
                    'dateEcheance' => $p->dateEcheance?->format('d/m/Y'),
                    'created_at' => $p->created_at?->format('d/m/Y H:i'),
                ];
            });

        return [
            'year' => $year,
            'selected_month' => $selectedMonthNum,
            'selected_month_name' => $selectedMonthName,
            'current_month' => $selectedMonthNum ?? (int) date('m'),
            'total_collected_all_time' => $totalCollectedAllTime,
            'total_pending_all_time' => $totalPendingAllTime,
            'year_collected' => $yearCollected,
            'year_pending' => $yearPending,
            'month_collected' => $monthCollected,
            'month_pending' => $monthPending,
            'total_students' => $students->count(),
            'total_base_tuition' => $totalBaseTuition,
            'total_discounts_granted' => $totalDiscountsGranted,
            'total_target_tuition' => $totalTargetTuition,
            'total_remaining_balance' => $totalRemainingBalance,
            'collection_rate' => $totalTargetTuition > 0 ? round(($totalCollectedAllTime / $totalTargetTuition) * 100, 1) : 0.0,
            'payment_methods' => $methodBreakdown,
            'monthly_revenue' => $monthlyRevenue,
            'recent_transactions' => $recentTransactions,
        ];
    }
}
