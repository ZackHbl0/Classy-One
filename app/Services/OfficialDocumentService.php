<?php

namespace App\Services;

use App\Models\DocumentRequest;
use App\Models\Student;
use App\Models\Registre;
use App\Models\Absence;
use App\Models\AuditLog;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OfficialDocumentService
{
    public function __construct(
        protected GradeCalculationService $gradeCalculationService
    ) {}

    /**
     * Get list of supported official document types.
     */
    public function getDocumentTypes(): array
    {
        return [
            [
                'id' => DocumentRequest::TYPE_ATTESTATION_SCOLARITE,
                'title' => 'Attestation de Scolarité',
                'description' => 'Certificat officiel attestant de l\'inscription régulière de l\'élève pour l\'année académique en cours.',
                'icon' => 'academic-cap',
                'delivery' => 'Immédiat / Sous 24h',
            ],
            [
                'id' => DocumentRequest::TYPE_RELEVE_NOTES,
                'title' => 'Relevé de Notes / Bulletin',
                'description' => 'Relevé officiel des résultats scolaires, moyennes par matière, coefficients et mentions.',
                'icon' => 'document-chart-bar',
                'delivery' => 'Immédiat / Sous 24h',
            ],
            [
                'id' => DocumentRequest::TYPE_CERTIFICAT_PRESENCE,
                'title' => 'Certificat de Présence',
                'description' => 'Attestation officielle d\'assiduité et de présence régulière aux cours obligatoires.',
                'icon' => 'check-badge',
                'delivery' => 'Immédiat / Sous 24h',
            ],
        ];
    }

    /**
     * Generate the official PDF for a document request, save it, and update the request status.
     */
    public function generate(DocumentRequest $docRequest, ?User $actor = null): string
    {
        $student = Student::with(['registres.classe.anneescolaire'])->findOrFail($docRequest->idStudent);
        $registre = Registre::with(['classe.anneescolaire'])
            ->where('idStudent', $student->idStudent)
            ->first();

        // Ensure unique reference exists
        if (empty($docRequest->reference)) {
            $docRequest->reference = 'DOC-' . date('Y') . '-' . strtoupper(Str::random(5));
        }

        $type = strtolower((string) $docRequest->document_type);

        if (str_contains($type, 'bulletin') || str_contains($type, 'releve') || str_contains($type, 'note')) {
            $pdf = $this->renderBulletinPdf($student, $registre, $docRequest);
            $prefix = 'bulletin';
        } elseif (str_contains($type, 'presence') || str_contains($type, 'assiduit')) {
            $pdf = $this->renderPresencePdf($student, $registre, $docRequest);
            $prefix = 'certificat_presence';
        } else {
            $pdf = $this->renderCertificatePdf($student, $registre, $docRequest);
            $prefix = 'attestation_scolarite';
        }

        // Save PDF file to secure private storage (storage/app/private/documents/official/)
        $dir = 'documents/official';
        $fileName = $dir . '/' . $prefix . '_' . $student->matricule . '_' . $docRequest->id . '_' . time() . '.pdf';
        
        Storage::disk('local')->put($fileName, $pdf->output());

        // Update DocumentRequest model
        $docRequest->update([
            'file_url' => $fileName,
            'status' => DocumentRequest::STATUS_PRET,
            'ready_date' => now(),
            'processed_by' => $actor?->id ?? auth()->id(),
            'processed_at' => now(),
        ]);

        // Record in Audit Trail
        AuditLog::record(
            'document.generated',
            "Génération du document officiel {$docRequest->document_type_title} pour l'élève {$student->nom} {$student->prenom} (Réf: {$docRequest->reference})",
            'DocumentRequest',
            (string) $docRequest->id,
            [
                'reference' => $docRequest->reference,
                'file_url' => $fileName,
                'document_type' => $docRequest->document_type,
            ],
            $actor
        );

        // Send Push Notification
        try {
            $notifService = app(NotificationService::class);
            $notifService->send(
                'Document disponible !',
                "Votre {$docRequest->document_type_title} (Réf: {$docRequest->reference}) est prêt et disponible en téléchargement.",
                'administrative',
                'students',
                [$student->idStudent]
            );
        } catch (\Throwable $e) {
            // Push notification failure should not halt PDF delivery
        }

        return $fileName;
    }

    /**
     * Reject a document request with a specified reason.
     */
    public function reject(DocumentRequest $docRequest, string $reason, ?User $actor = null): void
    {
        $docRequest->update([
            'status' => DocumentRequest::STATUS_REJETE,
            'rejection_reason' => $reason,
            'admin_note' => $reason,
            'processed_by' => $actor?->id ?? auth()->id(),
            'processed_at' => now(),
        ]);

        AuditLog::record(
            'document.rejected',
            "Demande de document {$docRequest->document_type_title} (Réf: {$docRequest->reference}) rejetée. Motif: {$reason}",
            'DocumentRequest',
            (string) $docRequest->id,
            ['reason' => $reason],
            $actor
        );

        try {
            $notifService = app(NotificationService::class);
            $notifService->send(
                'Demande de document rejetée',
                "Votre demande de {$docRequest->document_type_title} n'a pas pu aboutir. Motif : {$reason}",
                'administrative',
                'students',
                [$docRequest->idStudent]
            );
        } catch (\Throwable $e) {
            // Silently ignore notification errors
        }
    }

    /**
     * Mark a document request as in progress (en_cours).
     */
    public function markInProgress(DocumentRequest $docRequest, ?User $actor = null): void
    {
        $docRequest->update([
            'status' => DocumentRequest::STATUS_EN_COURS,
            'processed_by' => $actor?->id ?? auth()->id(),
            'processed_at' => now(),
        ]);
    }

    /**
     * Render Attestation de Scolarité PDF.
     */
    protected function renderCertificatePdf(Student $student, ?Registre $registre, DocumentRequest $docRequest)
    {
        return Pdf::loadView('pdf.certificate', [
            'student' => $student,
            'registre' => $registre,
            'docRequest' => $docRequest,
        ])->setPaper('a4', 'portrait');
    }

    /**
     * Render Certificat de Présence PDF.
     */
    protected function renderPresencePdf(Student $student, ?Registre $registre, DocumentRequest $docRequest)
    {
        $absencesCount = Absence::where('student_id', $student->idStudent)->count();
        $rate = max(80, 100 - ($absencesCount * 1.5));
        $attendanceRate = number_format($rate, 1, ',', ' ') . '%';

        return Pdf::loadView('pdf.presence', [
            'student' => $student,
            'registre' => $registre,
            'docRequest' => $docRequest,
            'attendance_rate' => $attendanceRate,
            'absences_count' => $absencesCount,
        ])->setPaper('a4', 'portrait');
    }

    /**
     * Render Relevé de Notes / Bulletin PDF.
     */
    protected function renderBulletinPdf(Student $student, ?Registre $registre, DocumentRequest $docRequest)
    {
        $semester = 'S1';
        $summary = $this->gradeCalculationService->getSemesterSummary($student->idStudent, $semester);

        $classeNom = $registre?->classe?->nomClasse ?? 'Classe assignée';
        $anneeScolaire = $registre?->classe?->anneescolaire?->nomAnnee ?? '2025/2026';

        return Pdf::loadView('pdf.bulletin', [
            'student' => $student,
            'docRequest' => $docRequest,
            'classeNom' => $classeNom,
            'anneeScolaire' => $anneeScolaire,
            'semester' => $semester,
            'subjects' => $summary['subjects'] ?? [],
            'weightedAverage' => $summary['weighted_average'] ?? 0,
            'totalCoefficients' => $summary['total_coefficients'] ?? 0,
            'totalWeightedPoints' => $summary['total_weighted_points'] ?? 0,
            'mention' => $summary['mention'] ?? 'N/A',
            'rank' => $summary['rank'] ?? null,
            'classSize' => $summary['class_size'] ?? null,
            'classAverage' => $summary['class_average'] ?? null,
            'classMin' => $summary['class_min'] ?? null,
            'classMax' => $summary['class_max'] ?? null,
        ])->setPaper('a4', 'portrait');
    }
}
