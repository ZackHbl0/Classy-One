<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class EnsureUserIsProfessor
{
    /**
     * Handle an incoming request.
     * Returns 403 JSON if authenticated user is not a professor.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !($user instanceof User) || !in_array($user->role, ['professeur', 'prof'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Accès refusé. Cet espace est réservé aux professeurs.',
            ], 403);
        }

        return $next($request);
    }
}
