<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UpdateUserLastSeen
{
    /**
     * Handle an incoming request.
     * Updates last_seen_at for both User (web) and Student (API/Sanctum) guards.
     */
    public function handle(Request $request, Closure $next)
    {
        // Web guard (User model – professors, admins, secrétaires)
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            $user->timestamps = false;
            $user->last_seen_at = Carbon::now();
            $user->save();
        }
        // Sanctum guard (could be Student model)
        elseif (Auth::guard('sanctum')->check()) {
            $entity = Auth::guard('sanctum')->user();
            if (method_exists($entity, 'getTable')) {
                $entity->timestamps = false;
                $entity->last_seen_at = Carbon::now();
                $entity->save();
            }
        }

        return $next($request);
    }
}
