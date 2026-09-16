<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentPdfController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\BulletinController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\AbsenceController;
use App\Http\Controllers\Api\ProfessorController;
use App\Http\Controllers\Api\ParentAuthController;
use App\Http\Controllers\Api\ParentDashboardController;
use App\Http\Controllers\Api\ParentDocumentController;

/*
|--------------------------------------------------------------------------
| API Routes - ClassyOne
|--------------------------------------------------------------------------
| Strict Sanctum Authentication, Role-based Middleware & Rate Limiting
|--------------------------------------------------------------------------
*/

// ─── Public Authentication Routes (Strict Rate Limiting Applied) ─────
Route::middleware('throttle:auth')->group(function () {
    // Student Auth
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    // Professor Auth
    Route::post('/professor/login', [ProfessorController::class, 'login']);

    // Parent Auth
    Route::post('/parent/login', [ParentAuthController::class, 'login']);
});

// ─── Protected Routes (Requires Sanctum Token & Global API Rate Limit) ─
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    // General Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // ─── Admin / Staff Protected Operations ────────────────────────────
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/revenue', [DashboardController::class, 'revenue']);
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
        Route::post('/test-notification', [NotificationController::class, 'sendTestPush']);
        Route::get('/test-push/{matricule}', [NotificationController::class, 'sendDirectPush']);
    });

    // ─── Student Portal (role.student) ────────────────────────────────
    Route::middleware('role.student')->group(function () {
        // Dashboard
        Route::post('/dashboard', [DashboardController::class, 'index']);

        // Events
        Route::post('/events', [EventController::class, 'index']);
        Route::post('/events/register', [EventController::class, 'register']);

        // Notifications
        Route::post('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/mark-read', [NotificationController::class, 'markRead']);

        // Payment
        Route::post('/paiement', [PaymentController::class, 'index']);

        // Planning
        Route::post('/planning', [PlanningController::class, 'index']);

        // Absences
        Route::get('/student/absences', [AbsenceController::class, 'index']);
        Route::post('/student/absences/{id}/justify', [AbsenceController::class, 'justify']);

        // Documents
        Route::post('/documents', [DocumentController::class, 'index']);
        Route::post('/documents/create', [DocumentController::class, 'store']);
        Route::get('/documents/{id}/download', [DocumentPdfController::class, 'download']);

        // Courses
        Route::post('/courses', [CourseController::class, 'index']);

        // Grades
        Route::post('/grades', [GradeController::class, 'index']);
        Route::post('/grades/statistics', [GradeController::class, 'statistics']);
        Route::post('/grades/by-course', [GradeController::class, 'byCourse']);
        Route::post('/grades/by-type', [GradeController::class, 'byType']);

        // Bulletin PDF
        Route::get('/bulletin/{studentId}', [BulletinController::class, 'download']);

        // Profile
        Route::post('/profile/update-password', [ProfileController::class, 'updatePassword']);
        Route::post('/profile/update-phone', [ProfileController::class, 'updatePhone']);
        Route::post('/profile/update-fcm-token', [ProfileController::class, 'updateFcmToken']);
        Route::post('/profile/update-preferences', [ProfileController::class, 'updatePreferences']);
    });

    // ─── Professor Portal (role.professor) ────────────────────────────
    Route::middleware('role.professor')->prefix('professor')->group(function () {
        Route::post('/logout', [ProfessorController::class, 'logout']);
        Route::get('/students', [ProfessorController::class, 'getStudents']);
        Route::get('/schedules', [ProfessorController::class, 'getSchedules']);
        Route::post('/grades', [ProfessorController::class, 'enterGrade']);
        Route::post('/absences', [ProfessorController::class, 'markAbsence']);
    });

    // ─── Parent Portal (role.parent) ──────────────────────────────────
    Route::middleware('role.parent')->prefix('parent')->group(function () {
        Route::post('/logout', [ParentAuthController::class, 'logout']);
        Route::post('/update-fcm-token', [ParentAuthController::class, 'updateFcmToken']);
        Route::match(['get', 'post'], '/dashboard', [ParentDashboardController::class, 'index']);
        Route::get('/children/{id}', [ParentDashboardController::class, 'childDetails']);
        Route::get('/documents', [ParentDocumentController::class, 'index']);
        Route::post('/documents', [ParentDocumentController::class, 'store']);
        Route::get('/bulletin/{studentId}', [BulletinController::class, 'download']);
    });
});
