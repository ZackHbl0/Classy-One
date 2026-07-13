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

Route::middleware('auth')->group(function () {
    Route::get('/web-chat/students', [\App\Http\Controllers\API\MessageController::class, 'getChatStudents']);
    Route::get('/web-chat/history/{userId}', [\App\Http\Controllers\API\MessageController::class, 'getChatHistory']);
    Route::post('/web-chat/send', [\App\Http\Controllers\API\MessageController::class, 'sendMessage']);
    Route::delete('/web-chat/messages/{id}', [\App\Http\Controllers\API\MessageController::class, 'deleteMessage']);
});
