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
            \App\Http\Middleware\UpdateUserLastSeen::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\UpdateUserLastSeen::class,
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

        // Append Security Headers globally
        $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);

        // Register role middleware alias
        $middleware->alias([
            'admin'          => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'role.professor' => \App\Http\Middleware\EnsureUserIsProfessor::class,
            'role.parent'    => \App\Http\Middleware\EnsureUserIsParent::class,
            'role.student'   => \App\Http\Middleware\EnsureUserIsStudent::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Prevent database path and credentials leakage on SQL errors
        $exceptions->render(function (\Illuminate\Database\QueryException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Une erreur interne est survenue lors de l\'accès à la base de données.',
                    'error_code' => 'DATABASE_ERROR',
                ], 500);
            }
        });

        $exceptions->render(function (\PDOException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Erreur de connexion sécurisée aux services de données.',
                    'error_code' => 'DATABASE_CONNECTION_ERROR',
                ], 500);
            }
        });

        // In production mode (when debug is off), NEVER leak stack traces or internal exception details
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if (!config('app.debug') && ($request->is('api/*') || $request->expectsJson())) {
                if ($e instanceof \Illuminate\Validation\ValidationException ||
                    $e instanceof \Illuminate\Auth\AuthenticationException ||
                    $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                    return null; // Keep standard HTTP status codes
                }

                return response()->json([
                    'status' => 'error',
                    'message' => 'Une erreur inattendue est survenue sur le serveur.',
                ], 500);
            }
        });
    })->create();
