<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SecureFileUploadService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecureFileStreamController extends Controller
{
    public function __construct(
        protected SecureFileUploadService $fileService
    ) {}

    /**
     * Stream a securely stored document using temporary HMAC signed route verification.
     */
    public function stream(Request $request): StreamedResponse
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Ce lien de téléchargement a expiré ou sa signature cryptographique est invalide.');
        }

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
            abort(401, 'Non authentifié. Veuillez vous connecter pour accéder à ce fichier.');
        }

        $encodedPath = $request->query('file_token');
        if (!$encodedPath) {
            abort(400, 'Paramètre de fichier manquant.');
        }

        $decodedPath = base64_decode($encodedPath, true);
        if ($decodedPath === false || str_contains($decodedPath, '..')) {
            abort(400, 'Chemin de fichier corrompu ou non autorisé.');
        }

        return $this->fileService->streamSecureFile($decodedPath);
    }
}
