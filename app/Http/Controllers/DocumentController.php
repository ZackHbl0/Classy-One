<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentRequest;
use App\Http\Requests\CreateDocumentRequest;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user();

        $requests = DocumentRequest::where('idStudent', $student->idStudent)
            ->orderBy('request_date', 'desc')
            ->get([
                'id', 'document_type', 'reason', 'urgency', 'status', 
                'request_date', 'ready_date', 'admin_note', 'file_url'
            ]);

        $statusMap = [
            'pending' => 'En attente',
            'processing' => 'En cours',
            'ready' => 'Prêt',
            'rejected' => 'Rejeté',
        ];

        $mapped = $requests->map(function ($r) use ($statusMap) {
            $data = $r->toArray();
            $data['id'] = (int) $data['id'];
            $data['admin_message'] = $r->admin_note;
            $data['raw_status'] = $r->status;
            // Provide French status label so Flutter cards and badges display correctly
            $data['status'] = $statusMap[$r->status] ?? ucfirst($r->status);
            $data['ready_date'] = $r->ready_date;
            
            // Ensure we return a full absolute URL for the PDF
            if ($r->file_url) {
                $data['pdf_url'] = url(Storage::url($r->file_url));
            } else {
                $data['pdf_url'] = null;
            }
            
            return $data;
        });

        return response()->json([
            "success" => true,
            "data" => $mapped
        ]);
    }

    public function store(CreateDocumentRequest $request)
    {
        $student = $request->user();
        $validated = $request->validated();

        $urgencyRaw = strtolower($validated['urgency'] ?? 'normal');
        $urgency = str_starts_with($urgencyRaw, 'urg') ? 'urgent' : 'normal';

        $doc = DocumentRequest::create([
            'idStudent' => $student->idStudent,
            'document_type' => $validated['documentType'],
            'reason' => $validated['reason'] ?? 'Demande de document',
            'urgency' => $urgency,
            'status' => 'pending',
            'request_date' => now(),
        ]);

        return response()->json([
            "success" => true,
            "message" => "Demande soumise avec succès.",
            "data" => $doc,
        ]);
    }
}
