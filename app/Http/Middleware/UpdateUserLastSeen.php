<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateUserLastSeen
{
    /**
     * Handle an incoming request.
     * Updates last_seen_at for User (web), Student, and SchoolParent (API/Sanctum) guards.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Web guard (User model – professors, admins, secrétaires)
            if (Auth::guard('web')->check()) {
                $user = Auth::guard('web')->user();
                if ($user) {
                    $user->timestamps = false;
                    $user->last_seen_at = Carbon::now();
                    $user->save();
                }
            }
            // Sanctum guard (Student or SchoolParent model)
            elseif (Auth::guard('sanctum')->check()) {
                $entity = Auth::guard('sanctum')->user();
                if ($entity && method_exists($entity, 'getTable')) {
                    $entity->timestamps = false;
                    $entity->last_seen_at = Carbon::now();
                    $entity->save();
                }
            }
        } catch (\Throwable $e) {
            // Log warning and continue request pipeline without throwing 500 error
            Log::warning('UpdateUserLastSeen error: ' . $e->getMessage());
        }

        return $next($request);
    }
}
