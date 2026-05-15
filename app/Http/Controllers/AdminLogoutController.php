<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLogoutController extends Controller
{
    /**
     * Log the admin user out, invalidate the database session, regenerate
     * the CSRF token, and perform a clean hard redirect to the login page.
     *
     * Fixes the 419 Page Expired error that occurs when:
     *  - The server is restarted (old session ID no longer exists in the DB)
     *  - AuthenticateSession silently kills the session mid-request
     *  - The browser holds a stale XSRF-TOKEN cookie from a previous session
     */
    public function logout(Request $request)
    {
        // Log out the authenticated user for the web guard
        Auth::guard('web')->logout();

        // Invalidate the current DB session row and regenerate the CSRF token.
        // This writes a fresh session to the database immediately.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Expire the two cookies the browser holds for this app.
        // The JS hook on /panel/login will clear any remaining stragglers.
        return redirect('/panel/login')
            ->withCookie(cookie()->forget('laravel-session'))
            ->withCookie(cookie()->forget('XSRF-TOKEN'));
    }
}

