<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Models\Student;
use App\Services\OfficialDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentPdfController extends Controller
{
    public function __construct(
        protected OfficialDocumentService $documentService
    ) {}

    /**
     * Get available document types.
     * GET /api/documents/types
     */
    public function types(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $this->documentService->getDocumentTypes(),
        ]);
    }

    /**
     * Download the certificate PDF for a specific request (requires auth:sanctum).
     * GET /api/documents/{id}/download
     */
    public function download(Request $request, $id)
    {
        $authUser = $request->user();
        if (!$authUser) {
            return response()->json(['success' => false, 'message' => 'Non authentifié.'], 401);
        }

        $docRequest = DocumentRequest::findOrFail($id);

        // Strict ownership & authorization check
        $isAuthorized = false;

        // Case 1: Student requesting their own document
        if (isset($authUser->idStudent) && (int) $authUser->idStudent === (int) $docRequest->idStudent) {
            $isAuthorized = true;
        }

        // Case 2: Parent requesting document of their linked child
        if (!$isAuthorized && method_exists($authUser, 'students')) {
            $isAuthorized = $authUser->students()
                ->where('student.idStudent', $docRequest->idStudent)
                ->exists();
        }

        // Case 3: Admin / Staff access
        if (!$isAuthorized && (
            (method_exists($authUser, 'isAdmin') && $authUser->isAdmin()) ||
            (isset($authUser->role) && in_array($authUser->role, ['admin', 'secretaire', 'professeur', 'prof', 'superadmin']))
        )) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Accès refusé. Vous n\'êtes pas autorisé à consulter ce document.'
            ], 403);
        }

        return $this->serveDocument($docRequest);
    }

    /**
     * Download certificate via HMAC temporary signed URL (no bearer token required in headers).
     * GET /documents/{id}/signed-download
     */
    public function signedDownload(Request $request, $id)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Lien de téléchargement expiré ou signature invalide.');
        }

        $docRequest = DocumentRequest::findOrFail($id);

        // Resolve authenticated user (session, bearer token or query token)
        $user = $request->user();
        if (!$user && ($tokenStr = $request->bearerToken() ?: $request->query('token'))) {
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenStr);
            if ($accessToken && (!$accessToken->expires_at || $accessToken->expires_at->isFuture())) {
                $user = $accessToken->tokenable;
                auth()->setUser($user);
            }
        }

        if (!$user) {
            abort(401, 'Non authentifié. Veuillez vous connecter pour télécharger ce document.');
        }

        // Authorization checks (Staff, Student Owner, Parent of Student)
        $isAuthorized = false;
        if (isset($user->role) && in_array($user->role, ['admin', 'secretaire'])) {
            $isAuthorized = true;
        }
        if (!$isAuthorized && isset($user->idStudent) && (int) $user->idStudent === (int) $docRequest->idStudent) {
            $isAuthorized = true;
        }
        if (!$isAuthorized && method_exists($user, 'students')) {
            $isAuthorized = $user->students()->where('student.idStudent', $docRequest->idStudent)->exists();
        }

        if (!$isAuthorized) {
            abort(403, 'Accès interdit. Vous n\'êtes pas autorisé à consulter ce document.');
        }

        return $this->serveDocument($docRequest);
    }

    /**
     * Stream or generate the document PDF safely.
     */
    protected function serveDocument(DocumentRequest $docRequest)
    {
        // Check if status is ready or approved
        if (!$docRequest->isReady()) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Le document n\'est pas encore prêt ou a été rejeté.'
            ], 403);
        }

        $student = Student::find($docRequest->idStudent);
        if (!$student) {
            abort(404, 'Élève associé introuvable.');
        }

        // If official file already generated and exists on disk, stream it
        if ($docRequest->file_url) {
            $cleanPath = preg_replace('#^(public/|storage/|private/)#', '', ltrim($docRequest->file_url, '/'));
            $disk = Storage::disk('local')->exists($cleanPath) ? Storage::disk('local') : (Storage::disk('public')->exists($cleanPath) ? Storage::disk('public') : null);
            if ($disk) {
                $filename = 'ClassyOne_' . Str::slug($docRequest->document_type_title) . '_' . ($student->matricule ?? $student->nom) . '.pdf';
                $resp = response()->file($disk->path($cleanPath), [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $filename . '"',
                    'X-Content-Type-Options' => 'nosniff',
                ]);
                $resp->setPrivate();
                $resp->headers->set('Cache-Control', 'private, no-cache, no-store, must-revalidate');
                return $resp;
            }
        }

        // Otherwise generate on the fly and save to private storage
        $savedPath = $this->documentService->generate($docRequest);
        $disk = Storage::disk('local')->exists($savedPath) ? Storage::disk('local') : Storage::disk('public');
        $filename = 'ClassyOne_' . Str::slug($docRequest->document_type_title) . '_' . ($student->matricule ?? $student->nom) . '.pdf';

        $resp = response()->file($disk->path($savedPath), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
        $resp->setPrivate();
        $resp->headers->set('Cache-Control', 'private, no-cache, no-store, must-revalidate');
        return $resp;
    }

    /**
     * Internal method to generate and save the PDF file path.
     */
    public function generateAndSave(DocumentRequest $docRequest)
    {
        return $this->documentService->generate($docRequest, auth()->user());
    }
}
