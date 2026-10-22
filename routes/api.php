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
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\PaymentReceiptController;
use App\Http\Controllers\Api\TwoFactorAuthController;
use App\Http\Controllers\Api\SecuritySessionController;
use App\Http\Controllers\Api\SecureFileStreamController;

/*
|--------------------------------------------------------------------------
| API Routes - ClassyOne
|--------------------------------------------------------------------------
| Strict Sanctum Authentication, Role-based Middleware & Rate Limiting
|--------------------------------------------------------------------------
*/

// ─── Public Authentication Routes (Strict Rate Limiting & Anti-Brute Force) ──
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth');

// Professor Auth
Route::post('/professor/login', [ProfessorController::class, 'login'])->middleware('throttle:login');

// Staff Auth (Admin / Secretaire) with 2FA enforcement
Route::post('/staff/login', [TwoFactorAuthController::class, 'staffLogin'])->middleware('throttle:login');

// Parent Auth
Route::post('/parent/login', [ParentAuthController::class, 'login'])->middleware('throttle:login');

// Password Recovery Workflow (Throttled per minute)
Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword'])->middleware('throttle:password-reset');
Route::post('/verify-reset-code', [PasswordResetController::class, 'verifyCode'])->middleware('throttle:otp');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->middleware('throttle:password-reset');

// Two-Factor Authentication Login Challenge Verification
Route::post('/2fa/verify', [TwoFactorAuthController::class, 'challengeVerify'])->middleware('throttle:otp');

// ─── Secure Media Streaming & Protected Documents (HMAC-Signed URLs) ─
Route::get('/courses/stream/{course}/{filename?}', [CourseController::class, 'streamVideo'])->name('courses.stream');
Route::get('/documents/types', [DocumentPdfController::class, 'types'])->name('documents.types');
Route::get('/documents/{id}/signed-download', [DocumentPdfController::class, 'signedDownload'])->name('documents.signed_download');
Route::get('/paiements/{id}/signed-receipt', [PaymentReceiptController::class, 'download'])->name('paiements.signed_receipt');
Route::get('/secure/stream', [SecureFileStreamController::class, 'stream'])->middleware('signed')->name('secure.file.stream');

// ─── Protected Routes (Requires Sanctum Token & Global API Rate Limit) ─
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    // General Auth & Common Secured Endpoints
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/paiements/{id}/receipt', [PaymentReceiptController::class, 'download']);
    Route::get('/documents/{id}/download', [DocumentPdfController::class, 'download']);

    // ─── Active Session & Device Management ────────────────────────────
    Route::prefix('security')->group(function () {
        Route::get('/sessions', [SecuritySessionController::class, 'index']);
        Route::post('/logout-device/{tokenId}', [SecuritySessionController::class, 'logoutDevice']);
        Route::post('/logout-other-devices', [SecuritySessionController::class, 'logoutOtherDevices']);
        Route::post('/logout-all-devices', [SecuritySessionController::class, 'logoutAllDevices']);
        Route::post('/logout-all', [SecuritySessionController::class, 'logoutAllDevices']);
    });

    // ─── Staff 2FA / TOTP Configuration ───────────────────────────────
    Route::prefix('2fa')->group(function () {
        Route::post('/setup', [TwoFactorAuthController::class, 'setup']);
        Route::post('/confirm', [TwoFactorAuthController::class, 'confirm']);
        Route::post('/disable', [TwoFactorAuthController::class, 'disable']);
    });

    // ─── Admin / Staff Protected Operations ────────────────────────────
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/revenue', [DashboardController::class, 'revenue']);
        Route::get('/financial-summary', [DashboardController::class, 'financialSummary']);
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
        Route::match(['get', 'post'], '/documents', [DocumentController::class, 'index']);
        Route::get('/documents/requests', [DocumentController::class, 'index']);
        Route::post('/documents/requests', [DocumentController::class, 'store']);
        Route::post('/documents/create', [DocumentController::class, 'store']);

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
        Route::get('/documents/types', [ParentDocumentController::class, 'types']);
        Route::get('/documents', [ParentDocumentController::class, 'index']);
        Route::post('/documents', [ParentDocumentController::class, 'store']);
        Route::get('/bulletin/{studentId}', [BulletinController::class, 'download']);
    });
});
