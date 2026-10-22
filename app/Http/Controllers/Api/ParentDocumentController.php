<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use Illuminate\Http\Request;
use App\Http\Requests\ParentDocumentRequest;
use App\Services\OfficialDocumentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class ParentDocumentController extends Controller
{
    public function __construct(
        protected OfficialDocumentService $documentService
    ) {}

    /**
     * List document requests for the parent's children.
     * GET /api/parent/documents
     */
    public function index(Request $request)
    {
        try {
            $parent = $request->user();

            if (!$parent) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Non authentifié.',
                ], 401);
            }

            // Retrieve all child IDs linked to this parent
            $childIds = $parent->students()->pluck('student.idStudent')->toArray();

            if (empty($childIds)) {
                return response()->json([
                    'status' => 'success',
                    'data' => [],
                ]);
            }

            $query = DocumentRequest::with('student')
                ->where(function ($q) use ($childIds, $parent) {
                    $q->whereIn('idStudent', $childIds)
                      ->orWhere('parent_id', $parent->id);
                });

            // Optional filter by student_id
            if ($request->filled('student_id')) {
                $studentId = (int) $request->student_id;
                if (!in_array($studentId, $childIds)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Élève non autorisé pour ce parent.',
                    ], 403);
                }
                $query->where('idStudent', $studentId);
            }

            $requests = $query->orderBy('request_date', 'desc')->get();

            $mapped = $requests->map(function ($r) {
                return [
                    'id' => (int) $r->id,
                    'reference' => $r->reference,
                    'student_id' => (int) $r->idStudent,
                    'student_name' => $r->student ? ($r->student->nom . ' ' . $r->student->prenom) : 'Élève',
                    'matricule' => $r->student?->matricule,
                    'document_type' => $r->document_type,
                    'document_type_title' => $r->document_type_title,
                    'reason' => $r->reason,
                    'comments' => $r->reason,
                    'urgency' => $r->urgency,
                    'urgency_label' => $r->urgency === 'urgent' ? 'Urgente' : 'Normale',
                    'status' => $r->status_label,
                    'raw_status' => $r->status,
                    'status_label' => $r->status_label,
                    'request_date' => $r->request_date ? date('d/m/Y', strtotime($r->request_date)) : null,
                    'ready_date' => $r->ready_date,
                    'admin_note' => $r->admin_note,
                    'rejection_reason' => $r->rejection_reason ?? ($r->isRejected() ? $r->admin_note : null),
                    'is_ready' => $r->isReady(),
                    'is_rejected' => $r->isRejected(),
                    'has_pdf' => $r->isReady(),
                    'file_url' => $r->file_url ? preg_replace('#^(public/|storage/)#', '', ltrim($r->file_url, '/')) : null,
                    'pdf_url' => $r->isReady() ? URL::temporarySignedRoute(
                        'documents.signed_download',
                        now()->addHours(4),
                        ['id' => $r->id]
                    ) : null,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $mapped,
            ]);
        } catch (\Exception $e) {
            Log::error("ParentDocumentController@index error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors du chargement des demandes : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit a new document request on behalf of a child.
     * POST /api/parent/documents
     */
    public function store(ParentDocumentRequest $request)
    {
        try {
            $parent = $request->user();

            if (!$parent) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Non authentifié.',
                ], 401);
            }

            $reason = $request->input('reason') ?: $request->input('comments');
            if (empty(trim((string)$reason))) {
                $reason = 'Demande effectuée par le parent.';
            }

            $rawUrgency = strtolower((string) $request->input('urgency', 'normal'));
            $urgency = str_starts_with($rawUrgency, 'urg') ? 'urgent' : 'normal';

            $studentId = (int) $request->student_id;

            // Verify that this child belongs to the authenticated parent
            $isChildOfParent = $parent->students()->where('student.idStudent', $studentId)->exists();

            if (!$isChildOfParent) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Vous n'êtes pas autorisé à faire une demande pour cet élève.",
                ], 403);
            }

            $docRequest = DocumentRequest::create([
                'idStudent' => $studentId,
                'parent_id' => $parent->id,
                'document_type' => $request->document_type,
                'reason' => $reason,
                'urgency' => $urgency,
                'status' => DocumentRequest::STATUS_NOUVEAU,
                'request_date' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Votre demande de document a été soumise avec succès.',
                'data' => [
                    'id' => $docRequest->id,
                    'reference' => $docRequest->reference,
                    'document_type' => $docRequest->document_type,
                    'document_type_title' => $docRequest->document_type_title,
                    'status' => $docRequest->status_label,
                    'raw_status' => $docRequest->status,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error("ParentDocumentController@store error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la soumission de la demande: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available document types for parents.
     * GET /api/parent/documents/types
     */
    public function types()
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->documentService->getDocumentTypes(),
        ]);
    }
}
