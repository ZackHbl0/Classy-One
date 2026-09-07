<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Student;
use App\Models\Paiement;
use App\Services\GradeCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ParentDashboardController extends Controller
{
    public function __construct(
        private readonly GradeCalculationService $gradeService
    ) {}
    /**
     * Get dashboard data for all children of the authenticated parent.
     */
    public function index(Request $request)
    {
        try {
            $parent = $request->user();

            if (!$parent) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Non authentifié',
                ], 401);
            }

            // Eager load children with relationships safely
            try {
                $parent->loadMissing([
                    'students.registres.classe.anneescolaire',
                    'students.absences.prof',
                    'students.grades.course',
                    'students.grades.teacher',
                ]);
            } catch (\Throwable $loadEx) {
                Log::warning('Parent relations eager loading notice: ' . $loadEx->getMessage());
            }

            $childrenData = [];
            $students = $parent->students ?? collect();

            foreach ($students as $student) {
                if ($student) {
                    $childrenData[] = $this->buildChildPayload($student);
                }
            }

            return response()->json([
                'status' => 'success',
                'parent' => [
                    'id' => (int) $parent->id,
                    'name' => (string) ($parent->name ?? ''),
                    'email' => (string) ($parent->email ?? ''),
                    'phone' => (string) ($parent->phone ?? ''),
                ],
                'children_count' => count($childrenData),
                'children' => $childrenData,
            ]);
        } catch (\Throwable $e) {
            Log::error('ParentDashboardController index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des données du tableau de bord: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get detailed data for a specific child of the authenticated parent.
     */
    public function childDetails(Request $request, $id)
    {
        try {
            $parent = $request->user();

            if (!$parent) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Non authentifié',
                ], 401);
            }

            $student = $parent->students()
                ->where('student.idStudent', $id)
                ->with([
                    'registres.classe.anneescolaire',
                    'absences.prof',
                    'grades.course',
                    'grades.teacher',
                ])
                ->first();

            if (!$student) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Élève non trouvé pour ce parent.',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $this->buildChildPayload($student),
            ]);
        } catch (\Throwable $e) {
            Log::error('ParentDashboardController childDetails error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des données de l\'enfant: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper to compile complete dashboard payload for a single child.
     */
    private function buildChildPayload(Student $student): array
    {
        // 0. Class & Academic Info
        $registres = $student->registres ?? collect();
        $registre = $registres->first();
        $classe = $registre ? $registre->classe : null;
        $classeId = $classe ? (string) $classe->id : null;
        $classeNom = $classe ? ($classe->nomClasse ?? 'Non assignée') : 'Non assignée';
        $anneeScolaire = ($classe && $classe->anneescolaire) ? ($classe->anneescolaire->libelle ?? '') : '';

        // 1. Absences & Attendance
        $absences = $student->absences ? $student->absences->sortByDesc('date') : collect();
        $totalAbsences = $absences->count();
        $justifiedAbsences = $absences->where('is_justified', true)->count();
        $unjustifiedAbsences = max(0, $totalAbsences - $justifiedAbsences);

        $absencesList = $absences->map(function ($absence) {
            $dateFormatted = null;
            if (!empty($absence->date)) {
                try {
                    $dateFormatted = Carbon::parse($absence->date)->format('d/m/Y');
                } catch (\Throwable $e) {
                    $dateFormatted = (string) $absence->date;
                }
            }

            return [
                'id' => (int) $absence->id,
                'date' => $absence->date ? (string) $absence->date : null,
                'date_formatted' => $dateFormatted,
                'seance' => (string) ($absence->seance ?? ''),
                'matiere' => (string) ($absence->matiere ?? 'Matière'),
                'is_justified' => (bool) $absence->is_justified,
                'justification_reason' => $absence->justification_reason,
                'student_explanation' => $absence->student_explanation,
                'status' => (string) ($absence->status ?? 'non_justifie'),
                'professeur' => $absence->prof ? ($absence->prof->name ?? 'Enseignant') : 'Enseignant',
            ];
        })->values()->all();

        // 2. Grades & Bulletins
        $grades = $student->grades ? $student->grades->sortByDesc('exam_date') : collect();
        $totalGrades = $grades->count();
        $rawAverage = 0.0;
        $passingRate = 0.0;
        $highestGrade = 0.0;
        $lowestGrade = 0.0;

        if ($totalGrades > 0) {
            $rawAverage   = (float) $grades->avg('note');
            $passingCount = $grades->filter(fn($g) => (float) ($g->note ?? 0) >= 10)->count();
            $passingRate  = round(($passingCount / $totalGrades) * 100, 1);
            $highestGrade = (float) $grades->max('note');
            $lowestGrade  = (float) $grades->min('note');
        }

        // Compute weighted averages using GradeCalculationService
        $weightedAvgS1  = $this->gradeService->calculateWeightedAverage($student->idStudent, 'S1');
        $weightedAvgS2  = $this->gradeService->calculateWeightedAverage($student->idStudent, 'S2');
        $weightedAvgAll = $this->gradeService->calculateWeightedAverage($student->idStudent);
        $mention        = $this->gradeService->getMention($weightedAvgAll);

        $gradesList = $grades->map(function ($grade) {
            $noteFloat = (float) ($grade->note ?? 0);
            $coeffFloat = (float) ($grade->coefficient ?? 1.0);
            $mentionGrade = $this->gradeService->getMention($noteFloat);
            $examDateFormatted = null;
            if (!empty($grade->exam_date)) {
                try {
                    $examDateFormatted = Carbon::parse($grade->exam_date)->format('d/m/Y');
                } catch (\Throwable $e) {
                    $examDateFormatted = (string) $grade->exam_date;
                }
            }

            return [
                'id' => (int) $grade->id,
                'subject_name' => (string) ($grade->subject_name ?? $grade->course?->title ?? 'Matière'),
                'note' => $noteFloat,
                'note_formatted' => number_format($noteFloat, 2),
                'coefficient' => $coeffFloat,
                'type' => (string) ($grade->type ?? 'Évaluation'),
                'status' => $mentionGrade,
                'mention' => $mentionGrade,
                'is_passing' => $noteFloat >= 10,
                'exam_date' => $grade->exam_date ? (string) $grade->exam_date : null,
                'exam_date_formatted' => $examDateFormatted,
                'semester' => (string) ($grade->semester ?? 'Semestre 1'),
                'comment' => $grade->comment,
                'teacher_name' => $grade->teacher ? ($grade->teacher->name ?? 'Professeur') : 'Professeur',
            ];
        })->values()->all();

        // 3. Fee Payments / Tranches
        $paiements = collect();
        try {
            $paiements = Paiement::select('paiement.id', 'paiement.montant', 'paiement.dateEcheance', 'paiement.statut')
                ->join('registre as r', 'paiement.Reg_id', '=', 'r.id')
                ->where('r.idStudent', $student->idStudent)
                ->orderBy('paiement.dateEcheance', 'asc')
                ->get();
        } catch (\Throwable $e) {
            Log::warning("Payments query fallback for student {$student->idStudent}: " . $e->getMessage());
        }

        $tranches = [];
        $totalAmount = 0.0;
        $totalPaid = 0.0;
        $hasOverdue = false;
        $counter = 1;

        foreach ($paiements as $p) {
            $montant = (float) ($p->montant ?? 0);
            $totalAmount += $montant;

            $statut = trim($p->statut ?? '');
            $displayStatus = 'En attente';
            $statutLower = mb_strtolower($statut);

            if ($statutLower === 'payé' || $statutLower === 'paye') {
                $displayStatus = 'Payé';
                $totalPaid += $montant;
            } elseif ($statutLower === 'en retard') {
                $displayStatus = 'En retard';
                $hasOverdue = true;
            } elseif (!empty($p->dateEcheance)) {
                try {
                    if (Carbon::parse($p->dateEcheance)->isPast()) {
                        $displayStatus = 'En retard';
                        $hasOverdue = true;
                    }
                } catch (\Throwable $e) {
                    // Ignore date parse failure
                }
            }

            $dueDateFormatted = 'N/A';
            if (!empty($p->dateEcheance)) {
                try {
                    $dueDateFormatted = Carbon::parse($p->dateEcheance)->format('d/m/Y');
                } catch (\Throwable $e) {
                    $dueDateFormatted = (string) $p->dateEcheance;
                }
            }

            $tranches[] = [
                'id' => (int) $p->id,
                'title' => 'Tranche ' . $counter,
                'amount' => number_format($montant, 0, ',', ' ') . ' MAD',
                'amount_raw' => $montant,
                'dueDate' => $dueDateFormatted,
                'dueDate_raw' => $p->dateEcheance ? (string) $p->dateEcheance : null,
                'status' => $displayStatus,
            ];
            $counter++;
        }

        $totalRemaining = max(0.0, $totalAmount - $totalPaid);
        $overallPaymentStatus = 'À jour';
        if ($hasOverdue) {
            $overallPaymentStatus = 'En retard';
        } elseif ($totalRemaining > 0) {
            $overallPaymentStatus = 'En cours';
        }

        // 4. Announcements & Notifications
        $announcements = [];
        try {
            $notifications = Notification::where(function ($query) use ($student, $classeId) {
                // 1. Target all
                $query->where('target_type', 'all')
                    // 2. Target specific student
                    ->orWhere(function ($q) use ($student) {
                        $q->where('target_type', 'students')
                            ->where('idStudent', $student->idStudent);
                    });

                // 3. Target class
                if ($classeId) {
                    $query->orWhere(function ($q) use ($classeId) {
                        $q->where('target_type', 'classes')
                            ->whereJsonContains('target_ids', $classeId);
                    });
                }
            })
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get();

            $announcements = $notifications->map(function ($n) {
                $createdAtFormatted = null;
                $dateRelative = '';
                if ($n->created_at) {
                    try {
                        $createdAtFormatted = $n->created_at->format('d/m/Y H:i');
                        $dateRelative = $n->created_at->diffForHumans();
                    } catch (\Throwable $e) {
                        $createdAtFormatted = (string) $n->created_at;
                    }
                }

                return [
                    'id' => (int) $n->id,
                    'titre' => (string) ($n->titre ?? 'Annonce'),
                    'message' => (string) ($n->message ?? ''),
                    'categorie' => (string) ($n->categorie ?? 'Général'),
                    'pieceJointe' => $n->pieceJointe,
                    'created_at' => $createdAtFormatted,
                    'date_relative' => $dateRelative,
                ];
            })->values()->all();
        } catch (\Throwable $e) {
            Log::warning('Announcements query fallback: ' . $e->getMessage());
            $announcements = [];
        }

        return [
            'details' => [
                'idStudent' => (int) $student->idStudent,
                'matricule' => (string) ($student->matricule ?? ''),
                'nom' => (string) ($student->nom ?? ''),
                'prenom' => (string) ($student->prenom ?? ''),
                'nom_complet' => trim(($student->nom ?? '') . ' ' . ($student->prenom ?? '')),
                'classe' => $classeNom,
                'classe_id' => $classeId,
                'annee_scolaire' => $anneeScolaire,
                'telephone' => $student->telephone,
            ],
            'attendance' => [
                'total_absences' => $totalAbsences,
                'justified_absences' => $justifiedAbsences,
                'unjustified_absences' => $unjustifiedAbsences,
                'history' => $absencesList,
            ],
            'grades' => [
                'average'                    => $weightedAvgAll,
                'average_formatted'          => number_format($weightedAvgAll, 2) . ' / 20',
                'weighted_average'           => $weightedAvgAll,
                'weighted_average_formatted' => number_format($weightedAvgAll, 2) . ' / 20',
                'weighted_average_s1'        => $weightedAvgS1,
                'weighted_average_s2'        => $weightedAvgS2,
                'mention'                    => $mention,
                'status'                     => $mention,
                'total_grades'               => $totalGrades,
                'passing_rate'               => $passingRate,
                'highest_grade'              => $highestGrade,
                'lowest_grade'               => $lowestGrade,
                'list'                       => $gradesList,
            ],
            'payments' => [
                'total_amount' => $totalAmount,
                'total_paid' => $totalPaid,
                'total_remaining' => $totalRemaining,
                'overall_status' => $overallPaymentStatus,
                'tranches' => $tranches,
            ],
            'announcements' => $announcements,
        ];
    }
}
