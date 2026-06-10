<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Add dynamic URL middleware to API routes for mobile app compatibility
        $middleware->api(append: [
            \App\Http\Middleware\DynamicApiUrl::class,
        ]);

        $middleware->statefulApi();

        // Exclude Livewire and Filament logout routes from CSRF verification.
        // Livewire 3 secures /livewire/update via cryptographic snapshot signing,
        // so removing Laravel's CSRF check for it is safe and required on Laravel 11
        // where Livewire's automatic exclusion does not integrate with the new
        // middleware bootstrap API — causing persistent 419 on the dashboard.
        $middleware->validateCsrfTokens(except: [
            'livewire/update',
            'livewire/*',
            'panel/logout',
            'admin-logout',
        ]);

        // Register role middleware alias
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
