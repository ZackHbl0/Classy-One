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

        // ─── Rate Limiting Definitions ─────────────────────────────────
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
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
