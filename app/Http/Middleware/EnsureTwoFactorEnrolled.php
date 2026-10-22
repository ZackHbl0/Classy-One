<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorEnrolled
{
    /**
     * Handle an incoming request.
     * Enforces mandatory 2FA enrollment for administrative accounts (admin, secretaire).
     * Any un-enrolled admin/secretaire is strictly blocked from accessing panel resources
     * until they complete TOTP setup on the TwoFactorSettings page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && in_array($user->role ?? '', ['admin', 'secretaire'], true)) {
            if (!$user->hasTwoFactorEnabled()) {
                // Allow access to 2FA configuration page and logout actions
                $path = trim($request->path(), '/');

                $isExempt = str_contains($path, 'two-factor-settings')
                    || str_contains($path, 'admin-logout')
                    || str_contains($path, 'logout');

                // For Livewire requests during 2FA configuration
                if ($request->hasHeader('X-Livewire') || str_starts_with($path, 'livewire')) {
                    $isExempt = true;
                }

                if (!$isExempt) {
                    if ($request->expectsJson() || $request->is('api/*')) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Configuration 2FA obligatoire pour les comptes administratifs.',
                            'error_code' => '2FA_ENROLLMENT_REQUIRED',
                        ], 403);
                    }

                    session()->flash('warning', 'Configuration 2FA obligatoire : vous devez impérativement activer l\'authentification à deux facteurs avant d\'accéder aux données administratives.');

                    return redirect('/panel/two-factor-settings');
                }
            }
        }

        return $next($request);
    }
}
