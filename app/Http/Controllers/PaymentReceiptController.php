<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentReceiptController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Download or stream payment receipt PDF.
     */
    public function download(Request $request, $id)
    {
        $paiement = Paiement::findOrFail($id);
        $user = $request->user();

        // 1. If accessed via signed route or has signature parameter, strictly verify signature
        if ($request->routeIs('paiements.signed_receipt') || $request->has('signature')) {
            if (!$request->hasValidSignature()) {
                abort(403, 'Lien de téléchargement expiré ou signature invalide.');
            }
        }

        // 2. Resolve authenticated user (session, bearer token or query token)
        if (!$user && ($tokenStr = $request->bearerToken() ?: $request->query('token'))) {
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenStr);
            if ($accessToken && (!$accessToken->expires_at || $accessToken->expires_at->isFuture())) {
                $user = $accessToken->tokenable;
                auth()->setUser($user);
            }
        }

        if (!$user) {
            abort(401, 'Non authentifié. Veuillez vous connecter pour télécharger ce reçu.');
        }

        // 3. Strict business authorization checks (Staff, Student Owner, Parent of Student)
        $isAuthorized = false;

        // Staff (Admin / Secretaire)
        if (isset($user->role) && in_array($user->role, ['admin', 'secretaire'])) {
            $isAuthorized = true;
        }

        // Student owner
        if (!$isAuthorized && isset($user->idStudent)) {
            $studentId = $paiement->student?->idStudent ?? $paiement->registre?->idStudent;
            if ((int) $user->idStudent === (int) $studentId) {
                $isAuthorized = true;
            }
        }

        // Parent owner of the student
        if (!$isAuthorized && method_exists($user, 'students')) {
            $studentId = $paiement->student?->idStudent ?? $paiement->registre?->idStudent;
            $isAuthorized = $user->students()->where('student.idStudent', $studentId)->exists();
        }

        if (!$isAuthorized) {
            abort(403, 'Accès interdit. Vous n\'êtes pas autorisé à télécharger ce reçu de paiement.');
        }

        return $this->servePdf($paiement);
    }

    /**
     * Helper to stream PDF to browser.
     */
    protected function servePdf(Paiement $paiement)
    {
        $pdf = $this->paymentService->generateReceiptPdf($paiement);
        $filename = "Recu_Paiement_{$paiement->reference}.pdf";

        return $pdf->stream($filename);
    }
}
