<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SchoolParent;

class EnsureUserIsParent
{
    /**
     * Handle an incoming request.
     * Returns 403 JSON if authenticated user is not a parent.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !($user instanceof SchoolParent)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Accès refusé. Cet espace est réservé aux parents d\'élèves.',
            ], 403);
        }

        return $next($request);
    }
}
