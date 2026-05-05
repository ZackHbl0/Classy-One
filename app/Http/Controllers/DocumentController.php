<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentRequest;
use Illuminate\Support\Facades\Validator;

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

        // Map strictly matching PHP behavior
        $mapped = $requests->map(function ($r) {
            $data = $r->toArray();
            $data['id'] = (int) $data['id'];
            // Alias fields for Flutter as requested by the user
            $data['admin_message'] = $r->admin_note;
            
            // Ensure we return a full absolute URL for the PDF
            if ($r->file_url) {
                $data['pdf_url'] = url(\Illuminate\Support\Facades\Storage::url($r->file_url));
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

    public function store(Request $request)
    {
        $student = $request->user();

        // Matches legacy create_document_request.php 
        $validator = Validator::make($request->all(), [
            'documentType' => 'required|string',
            'reason' => 'required|string',
            'urgency' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(["success" => false, "message" => "Données manquantes ou invalides."]);
        }

        DocumentRequest::create([
            'idStudent' => $student->idStudent,
            'document_type' => $request->input('documentType'),
            'reason' => $request->input('reason'),
            'urgency' => $request->input('urgency'),
            'status' => 'pending',
            'request_date' => date('Y-m-d H:i:s')
        ]);

        return response()->json([
            "success" => true,
            "message" => "Demande soumise avec succès."
        ]);
    }
}
