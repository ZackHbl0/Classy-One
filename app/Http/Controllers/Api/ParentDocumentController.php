<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use Illuminate\Http\Request;
use App\Http\Requests\ParentDocumentRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ParentDocumentController extends Controller
{
    /**
     * List document requests for the parent's children.
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
                $statusLabels = [
                    'pending' => 'En attente',
                    'processing' => 'En cours',
                    'ready' => 'Prêt',
                    'rejected' => 'Rejeté',
                ];

                $statusLabel = $statusLabels[$r->status] ?? ucfirst($r->status);
                return [
                    'id' => (int) $r->id,
                    'student_id' => (int) $r->idStudent,
                    'student_name' => $r->student ? ($r->student->nom . ' ' . $r->student->prenom) : 'Élève',
                    'document_type' => $r->document_type,
                    'reason' => $r->reason,
                    'comments' => $r->reason,
                    'urgency' => $r->urgency,
                    'urgency_label' => $r->urgency === 'urgent' ? 'Urgente' : 'Normale',
                    'status' => $statusLabel,
                    'raw_status' => $r->status,
                    'status_label' => $statusLabel,
                    'request_date' => $r->request_date ? date('d/m/Y', strtotime($r->request_date)) : null,
                    'ready_date' => $r->ready_date,
                    'admin_note' => $r->admin_note,
                    'rejection_reason' => $r->status === 'rejected' ? $r->admin_note : null,
                    'is_ready' => $r->status === 'ready',
                    'has_pdf' => !empty($r->file_url),
                    'pdf_url' => $r->file_url ? url(Storage::url($r->file_url)) : null,
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
                    'message' => "Vous n'\u00eates pas autoris\u00e9 \u00e0 faire une demande pour cet \u00e9l\u00e8ve.",
                ], 403);
            }

            $docRequest = DocumentRequest::create([
                'idStudent' => $studentId,
                'parent_id' => $parent->id,
                'document_type' => $request->document_type,
                'reason' => $reason,
                'urgency' => $urgency,
                'status' => 'pending',
                'request_date' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Votre demande de document a été soumise avec succès.',
                'data' => [
                    'id' => $docRequest->id,
                    'document_type' => $docRequest->document_type,
                    'status' => $docRequest->status,
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
}
