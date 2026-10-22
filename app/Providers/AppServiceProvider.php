<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (!app()->runningInConsole()) {
            // Lock the root URL so all Laravel-generated URLs include subfolder + port
            URL::forceRootUrl(config('app.url'));

            // Set session cookie path to root so the cookie is sent
            // with ALL requests (including /livewire/update rewritten by Apache)
            config(['session.path' => '/']);
        }

        \App\Models\Event::observe(\App\Observers\EventObserver::class);
        \App\Models\Paiement::observe(\App\Observers\PaiementObserver::class);
        \App\Models\DocumentRequest::observe(\App\Observers\DocumentRequestObserver::class);
        \App\Models\Absence::observe(\App\Observers\AbsenceObserver::class);
        \App\Models\Grade::observe(\App\Observers\GradeObserver::class);

        // ─── Rate Limiting & Anti-Brute Force Lockouts ─────────────────
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            $identifier = (string) ($request->input('matricule') ?: $request->input('email') ?: $request->input('login') ?: $request->input('identifier') ?: '');
            $cleanId = strtolower(trim($identifier));
            $ip = $request->ip() ?: '127.0.0.1';

            $responseCallback = function (Request $request, array $headers) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Trop de tentatives de connexion. Votre compte ou IP est temporairement bloqué pour 60 secondes (Protection anti-brute force et anti-password spraying).',
                    'retry_after' => $headers['Retry-After'] ?? 60,
                ], 429, $headers);
            };

            return [
                // 1. Account-specific limit (max 5 attempts/min per account)
                Limit::perMinute(5)->by('login_acc_' . $cleanId)->response($responseCallback),
                // 2. IP-level limit (max 10 attempts/min per IP) to prevent password spraying across rotating accounts
                Limit::perMinute(10)->by('login_ip_' . $ip)->response($responseCallback),
                // 3. Combined identifier|IP limit
                Limit::perMinute(5)->by('login_pair_' . $cleanId . '|' . $ip)->response($responseCallback),
            ];
        });

        RateLimiter::for('otp', function (Request $request) {
            $identifier = (string) ($request->input('identifier') ?: $request->input('matricule') ?: $request->input('login') ?: '');
            $cleanId = strtolower(trim($identifier));
            $ip = $request->ip() ?: '127.0.0.1';

            $responseCallback = function (Request $request, array $headers) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Trop de tentatives de validation OTP. Veuillez patienter 60 secondes avant de réessayer.',
                    'retry_after' => $headers['Retry-After'] ?? 60,
                ], 429, $headers);
            };

            return [
                Limit::perMinute(5)->by('otp_id_' . $cleanId)->response($responseCallback),
                Limit::perMinute(10)->by('otp_ip_' . $ip)->response($responseCallback),
            ];
        });

        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function (Request $request, array $headers) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Trop de requêtes de réinitialisation de mot de passe. Veuillez patienter avant de réessayer.',
                    'retry_after' => $headers['Retry-After'] ?? 60,
                ], 429, $headers);
            });
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // ─── Security Policies Registration ───────────────────────────
        Gate::policy(\App\Models\Paiement::class, \App\Policies\PaiementPolicy::class);
        Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);
        Gate::policy(\App\Models\AuditLog::class, \App\Policies\AuditLogPolicy::class);
    }
}
