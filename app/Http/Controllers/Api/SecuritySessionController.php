<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SecuritySessionController extends Controller
{
    /**
     * List all active tokens/sessions for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !method_exists($user, 'tokens')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Impossible de récupérer les sessions.',
            ], 400);
        }

        $currentTokenId = $user->currentAccessToken()?->id;

        $sessions = $user->tokens()
            ->orderBy('last_used_at', 'desc')
            ->get()
            ->map(function ($token) use ($currentTokenId) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'last_used_at' => $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Jamais utilisé',
                    'last_used_at_iso' => $token->last_used_at?->toIso8601String(),
                    'created_at' => $token->created_at?->toIso8601String(),
                    'is_current' => $token->id === $currentTokenId,
                ];
            });

        return response()->json([
            'status' => 'success',
            'count' => $sessions->count(),
            'sessions' => $sessions,
        ]);
    }

    /**
     * Revoke a specific session/token by its ID.
     */
    public function logoutDevice(Request $request, int|string $tokenId): JsonResponse
    {
        $user = $request->user();
        if (!$user || !method_exists($user, 'tokens')) {
            return response()->json(['status' => 'error', 'message' => 'Non autorisé.'], 401);
        }

        $deleted = $user->tokens()->where('id', $tokenId)->delete();

        if ($deleted) {
            AuditLog::create([
                'user_id' => ($user instanceof \App\Models\User) ? $user->id : null,
                'user_name' => $user->name ?? $user->nom ?? 'Utilisateur',
                'user_role' => $user->role ?? 'student',
                'action' => 'REVOKE_SESSION',
                'subject_type' => get_class($user),
                'subject_id' => (string) $user->getKey(),
                'description' => "Révocation de la session / jeton #{$tokenId}.",
                'ip_address' => $request->ip(),
                'details' => ['revoked_token_id' => $tokenId],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'La session spécifiée a été révoquée avec succès.',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Session introuvable ou déjà révoquée.',
        ], 404);
    }

    /**
     * Revoke all tokens except the currently active one.
     */
    public function logoutOtherDevices(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentTokenId = $user?->currentAccessToken()?->id;

        if (!$user || !method_exists($user, 'tokens') || !$currentTokenId) {
            return response()->json(['status' => 'error', 'message' => 'Session active introuvable.'], 400);
        }

        $revokedCount = $user->tokens()->where('id', '!=', $currentTokenId)->delete();

        // Also invalidate other web sessions from database if sessions table exists
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
                $query = \Illuminate\Support\Facades\DB::table('sessions')
                    ->where('user_id', $user->getKey());
                if ($request->hasSession()) {
                    $query->where('id', '!=', $request->session()->getId());
                }
                $query->delete();
            }
        } catch (\Throwable) {}

        // Clear sensitive cached user keys
        $this->clearSensitiveUserCache($user);

        AuditLog::create([
            'user_id' => ($user instanceof \App\Models\User) ? $user->id : null,
            'user_name' => $user->name ?? $user->nom ?? 'Utilisateur',
            'user_role' => $user->role ?? 'student',
            'action' => 'REVOKE_OTHER_SESSIONS',
            'subject_type' => get_class($user),
            'subject_id' => (string) $user->getKey(),
            'description' => "Déconnexion de {$revokedCount} autre(s) appareil(s).",
            'ip_address' => $request->ip(),
            'details' => ['revoked_count' => $revokedCount],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "{$revokedCount} autre(s) session(s) ont été déconnectées avec succès.",
            'revoked_count' => $revokedCount,
        ]);
    }

    /**
     * Revoke all tokens including current one (complete global logout).
     */
    public function logoutAllDevices(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user && method_exists($user, 'tokens')) {
            $user->tokens()->delete();

            // Invalidate all web cookie sessions for this user (Filament & Web)
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
                    \Illuminate\Support\Facades\DB::table('sessions')
                        ->where('user_id', $user->getKey())
                        ->delete();
                }
            } catch (\Throwable) {}

            $this->clearSensitiveUserCache($user);

            AuditLog::create([
                'user_id' => ($user instanceof \App\Models\User) ? $user->id : null,
                'user_name' => $user->name ?? $user->nom ?? 'Utilisateur',
                'user_role' => $user->role ?? 'student',
                'action' => 'REVOKE_ALL_SESSIONS',
                'subject_type' => get_class($user),
                'subject_id' => (string) $user->getKey(),
                'description' => "Déconnexion globale de tous les appareils (jetons API et sessions Web).",
                'ip_address' => $request->ip(),
                'details' => ['global_logout' => true],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Toutes vos sessions sur tous les appareils ont été révoquées.',
        ]);
    }

    /**
     * Clear user-specific sensitive cached items.
     */
    protected function clearSensitiveUserCache($user): void
    {
        $id = $user->getKey();
        $class = class_basename($user);

        Cache::forget("user_profile_{$class}_{$id}");
        Cache::forget("user_permissions_{$class}_{$id}");
        Cache::forget("user_dashboard_{$class}_{$id}");
    }
}
