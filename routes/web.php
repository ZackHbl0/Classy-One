<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminLogoutController;

Route::get('/', function () {
    return redirect('/panel');
});

// Custom admin logout – accepts GET and POST because Filament's user-menu fires a
// POST via Livewire internally, even when the MenuItem URL is a plain anchor href.
// CSRF is excluded for this path in bootstrap/app.php, so no token is required.
// The controller invalidates the session and regenerates the CSRF token cleanly.
Route::any('/admin-logout', [AdminLogoutController::class, 'logout'])
    ->middleware('web')
    ->name('admin.logout');

// Payment Receipt PDF Download & Stream
Route::get('/paiements/{id}/receipt', [\App\Http\Controllers\PaymentReceiptController::class, 'download'])
    ->middleware('web')
    ->name('paiements.receipt.download');

// Strict interception: deny direct public access to sensitive uploads (certificates, official documents, courses)
Route::get('/storage/certificates/{path?}', fn() => abort(403, 'Accès direct interdit aux documents sensibles.'))->where('path', '.*');
Route::get('/storage/documents/{path?}', fn() => abort(403, 'Accès direct interdit aux documents sensibles.'))->where('path', '.*');
Route::get('/storage/courses/{path?}', fn() => abort(403, 'Accès direct interdit aux cours sensibles.'))->where('path', '.*');

// Deprecated chat routes (tables dropped)
// Route::middleware('auth')->group(function () {
//     Route::get('/web-chat/students', [\App\Http\Controllers\Api\MessageController::class, 'getChatStudents']);
//     Route::get('/web-chat/history/{userId}', [\App\Http\Controllers\Api\MessageController::class, 'getChatHistory']);
//     Route::post('/web-chat/send', [\App\Http\Controllers\Api\MessageController::class, 'sendMessage']);
//     Route::delete('/web-chat/messages/{id}', [\App\Http\Controllers\Api\MessageController::class, 'deleteMessage']);
// });

