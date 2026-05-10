<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     * Returns 403 JSON if the authenticated user is not an admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Accès refusé. Vous n\'avez pas les permissions nécessaires.',
            ], 403);
        }

        return $next($request);
    }
}
