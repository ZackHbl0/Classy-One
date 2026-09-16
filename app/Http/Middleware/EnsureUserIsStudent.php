<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Student;

class EnsureUserIsStudent
{
    /**
     * Handle an incoming request.
     * Returns 403 JSON if authenticated user is not a student.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !($user instanceof Student)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Accès refusé. Cet espace est réservé aux élèves.',
            ], 403);
        }

        return $next($request);
    }
}
