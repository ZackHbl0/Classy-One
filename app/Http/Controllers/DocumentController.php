<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentRequest;
use App\Http\Requests\CreateDocumentRequest;
use App\Services\OfficialDocumentService;
use Illuminate\Support\Facades\URL;

class DocumentController extends Controller
{
    public function __construct(
        protected OfficialDocumentService $documentService
    ) {}

    /**
     * List all document requests for the authenticated student.
     * POST /api/documents or GET /api/documents/requests
     */
    public function index(Request $request)
    {
        $student = $request->user();

        $requests = DocumentRequest::where('idStudent', $student->idStudent)
            ->orderBy('request_date', 'desc')
            ->get();

        $mapped = $requests->map(function ($r) {
            $data = $r->toArray();
            $data['id'] = (int) $data['id'];
            $data['reference'] = $r->reference;
            $data['document_type_title'] = $r->document_type_title;
            $data['admin_message'] = $r->admin_note;
            $data['rejection_reason'] = $r->rejection_reason ?? $r->admin_note;
            $data['raw_status'] = $r->status;
            $data['status'] = $r->status_label;
            $data['status_label'] = $r->status_label;
            $data['ready_date'] = $r->ready_date;
            $data['is_ready'] = $r->isReady();
            $data['is_rejected'] = $r->isRejected();

            // Return temporary signed HMAC URL if the document is ready
            if ($r->isReady()) {
                $relativeStorage = $r->file_url ? preg_replace('#^(public/|storage/)#', '', ltrim($r->file_url, '/')) : null;
                $data['file_url'] = $relativeStorage;
                $data['pdf_url'] = URL::temporarySignedRoute(
                    'documents.signed_download',
                    now()->addHours(4),
                    ['id' => $r->id]
                );
            } else {
                $data['file_url'] = null;
                $data['pdf_url'] = null;
            }

            return $data;
        });

        return response()->json([
            "success" => true,
            "status" => "success",
            "data" => $mapped,
        ]);
    }

    /**
     * Submit a new official document request for the student.
     * POST /api/documents/create or POST /api/documents/requests
     */
    public function store(CreateDocumentRequest $request)
    {
        $student = $request->user();
        $validated = $request->validated();

        $urgencyRaw = strtolower($validated['urgency'] ?? 'normal');
        $urgency = str_starts_with($urgencyRaw, 'urg') ? 'urgent' : 'normal';

        $doc = DocumentRequest::create([
            'idStudent' => $student->idStudent,
            'document_type' => $validated['documentType'] ?? $validated['document_type'],
            'reason' => $validated['reason'] ?? 'Demande de document administratif',
            'urgency' => $urgency,
            'status' => DocumentRequest::STATUS_NOUVEAU,
            'request_date' => now(),
        ]);

        return response()->json([
            "success" => true,
            "status" => "success",
            "message" => "Votre demande de document a été enregistrée avec succès.",
            "data" => [
                'id' => $doc->id,
                'reference' => $doc->reference,
                'document_type' => $doc->document_type,
                'document_type_title' => $doc->document_type_title,
                'status' => $doc->status_label,
                'raw_status' => $doc->status,
                'request_date' => $doc->request_date,
            ],
        ], 201);
    }

    /**
     * Return list of available official document types.
     * GET /api/documents/types
     */
    public function types()
    {
        return response()->json([
            'success' => true,
            'status' => 'success',
            'data' => $this->documentService->getDocumentTypes(),
        ]);
    }
}
