<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| These routes are loaded by the RouteServiceProvider and all will be
| assigned to the "api" middleware group.
|--------------------------------------------------------------------------
*/

// Public routes (no auth required)
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
Route::get('/test-push/{matricule}', [\App\Http\Controllers\NotificationController::class, 'sendDirectPush']);

// Protected routes (require Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout']);

    // Dashboard
    Route::post('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index']);

    // Events
    Route::post('/events', [\App\Http\Controllers\EventController::class, 'index']);
    Route::post('/events/register', [\App\Http\Controllers\EventController::class, 'register']);

    // Notifications
    Route::post('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::post('/notifications/mark-read', [\App\Http\Controllers\NotificationController::class, 'markRead']);
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy']);
    Route::post('/test-notification', [\App\Http\Controllers\NotificationController::class, 'sendTestPush']);

    // Payment
    Route::post('/paiement', [\App\Http\Controllers\PaymentController::class, 'index']);

    // Planning
    Route::post('/planning', [\App\Http\Controllers\PlanningController::class, 'index']);
    // Route::post('/attendance', [\App\Http\Controllers\AttendanceController::class, 'index']); // Disabled – Présences section removed

    // Documents
    Route::post('/documents', [\App\Http\Controllers\DocumentController::class, 'index']);
    Route::post('/documents/create', [\App\Http\Controllers\DocumentController::class, 'store']);
    Route::get('/documents/{id}/download', [\App\Http\Controllers\DocumentPdfController::class, 'download']);

    // Courses
    Route::post('/courses', [\App\Http\Controllers\CourseController::class, 'index']);

    // Profile
    Route::post('/profile/update-password', [\App\Http\Controllers\ProfileController::class, 'updatePassword']);
    Route::post('/profile/update-phone', [\App\Http\Controllers\ProfileController::class, 'updatePhone']);
    Route::post('/profile/update-fcm-token', [\App\Http\Controllers\ProfileController::class, 'updateFcmToken']);
    Route::post('/profile/update-preferences', [\App\Http\Controllers\ProfileController::class, 'updatePreferences']);

    // ─── Admin-only routes ──────────────────────────────────────────
    // Accessing /api/admin/* with a Secrétaire token will return 403.
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/revenue', [\App\Http\Controllers\DashboardController::class, 'revenue']);
    });
});
