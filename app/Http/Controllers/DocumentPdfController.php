<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Models\Student;
use App\Models\Registre;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class DocumentPdfController extends Controller
{
    /**
     * Download the certificate PDF for a specific request.
     */
    public function download(Request $request, $id)
    {
        $student = $request->user();
        $docRequest = DocumentRequest::where('id', $id)
            ->where('idStudent', $student->idStudent)
            ->firstOrFail();

        // Check if status is ready/approved
        $status = strtolower($docRequest->status);
        if ($status !== 'approved' && $status !== 'ready') {
            return response()->json([
                'success' => false,
                'message' => 'Le document n\'est pas encore prêt.'
            ], 403);
        }

        // Get full student details for the template
        $fullStudent = Student::find($student->idStudent);
        $registre = Registre::where('idStudent', $student->idStudent)->first();

        // Generate PDF on the fly or stream it
        $pdf = Pdf::loadView('pdf.certificate', [
            'student' => $fullStudent,
            'registre' => $registre,
            'docRequest' => $docRequest
        ]);

        return $pdf->stream('Certificat_Scolarite_' . $fullStudent->nom . '.pdf');
    }

    /**
     * Internal method to generate and save the PDF file path (optional but requested).
     * This can be called from Filament when approving.
     */
    public function generateAndSave(DocumentRequest $docRequest)
    {
        $student = Student::find($docRequest->idStudent);
        $registre = Registre::where('idStudent', $docRequest->idStudent)->first();

        $pdf = Pdf::loadView('pdf.certificate', [
            'student' => $student,
            'registre' => $registre,
            'docRequest' => $docRequest
        ]);

        $fileName = 'certificates/cert_' . $docRequest->id . '_' . time() . '.pdf';
        Storage::disk('public')->put($fileName, $pdf->output());

        $docRequest->update([
            'file_url' => Storage::url($fileName),
            'status' => 'Prêt', // mark as ready once generated
            'ready_date' => now(),
        ]);

        return $docRequest->file_url;
    }
}
