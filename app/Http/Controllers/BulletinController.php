<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\GradeCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class BulletinController extends Controller
{
    public function __construct(
        private readonly GradeCalculationService $gradeService
    ) {}

    /**
     * Download a PDF bulletin for a given student and semester.
     *
     * Route: GET /api/bulletin/{studentId}?semester=S1
     *
     * Accessible by:
     *  - The student themselves (auth:sanctum, student guard)
     *  - A parent whose child matches $studentId (auth:sanctum, parent guard)
     *
     * @param  Request $request
     * @param  int     $studentId
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function download(Request $request, int $studentId)
    {
        try {
            $semester = $request->query('semester', 'S1');

            if (!in_array($semester, ['S1', 'S2'])) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Semestre invalide. Valeurs acceptées : S1, S2',
                ], 422);
            }

            // Authorization: student accessing their own bulletin
            $authUser = $request->user();
            $student  = null;

            if ($authUser) {
                // Case 1: Authenticated student
                if (method_exists($authUser, 'idStudent') || isset($authUser->idStudent)) {
                    if ((int) $authUser->idStudent === $studentId) {
                        $student = Student::with(['registres.classe.anneescolaire'])
                            ->where('idStudent', $studentId)
                            ->first();
                    }
                }

                // Case 2: Authenticated parent checking their child
                if (!$student && method_exists($authUser, 'students')) {
                    $child = $authUser->students()
                        ->where('student.idStudent', $studentId)
                        ->with(['registres.classe.anneescolaire'])
                        ->first();
                    if ($child) {
                        $student = $child;
                    }
                }

                // Case 3: Admin / professor access
                if (!$student && method_exists($authUser, 'isAdmin') && $authUser->isAdmin()) {
                    $student = Student::with(['registres.classe.anneescolaire'])
                        ->where('idStudent', $studentId)
                        ->first();
                }
                if (!$student && isset($authUser->role) && in_array($authUser->role, ['admin', 'professeur', 'prof', 'secretaire'])) {
                    $student = Student::with(['registres.classe.anneescolaire'])
                        ->where('idStudent', $studentId)
                        ->first();
                }
            }

            if (!$student) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Accès non autorisé ou élève introuvable.',
                ], 403);
            }

            // Resolve class info
            $registre    = $student->registres->first();
            $classe      = $registre?->classe;
            $classeId    = $classe?->id;
            $classeNom   = $classe?->nomClasse ?? 'Non assignée';
            $anneeScolaire = $classe?->anneescolaire?->libelle ?? '2025/2026';

            // Build bulletin data using the service
            $subjects        = $this->gradeService->getSubjectBreakdown($student->idStudent, $semester);
            $weightedAverage = $this->gradeService->calculateWeightedAverage($student->idStudent, $semester);
            $totalCoefficient = array_sum(array_column($subjects, 'coefficient'));
            $mention         = $this->gradeService->getMention($weightedAverage);
            $rank            = $classeId
                ? $this->gradeService->calculateRankInClass($student->idStudent, $classeId, $semester)
                : null;

            // Render PDF
            $pdf = Pdf::loadView('pdf.bulletin', [
                'student'          => $student,
                'semester'         => $semester,
                'subjects'         => $subjects,
                'weightedAverage'  => $weightedAverage,
                'totalCoefficient' => $totalCoefficient,
                'mention'          => $mention,
                'rank'             => $rank,
                'classeNom'        => $classeNom,
                'anneeScolaire'    => $anneeScolaire,
            ])->setPaper('A4', 'portrait');

            $filename = "bulletin_{$student->matricule}_{$semester}.pdf";

            return $pdf->download($filename);
        } catch (\Throwable $e) {
            Log::error('BulletinController download error: ' . $e->getMessage(), [
                'student_id' => $studentId,
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Erreur lors de la génération du bulletin : ' . $e->getMessage(),
            ], 500);
        }
    }
}
