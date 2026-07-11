<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('panel')
            ->brandLogo(asset('classyone_logo.png'))
            ->brandLogoHeight('110px')
            ->login(\App\Filament\Pages\Auth\Login::class)

            ->colors([
                'primary' => Color::Indigo,
                'gray' => Color::Slate,
            ])
            ->font('Inter')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\CustomDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Remove fallback widgets
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                // AuthenticateSession is intentionally removed:
                // It re-validates the user's password hash on every request and silently
                // invalidates the session on any mismatch (e.g. after a server restart),
                // which destroys the CSRF token and causes a 419 on the next Livewire request.
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            // Override the default Filament logout so it hits our clean-logout
            // controller that invalidates the session and regenerates CSRF before
            // redirecting – preventing the 419 Page Expired error.
            ->userMenuItems([
                'logout' => MenuItem::make()
                    ->label('Se déconnecter')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->url('/admin-logout'),
            ])
            ->sidebarCollapsibleOnDesktop();
    }

    public function boot(): void
    {

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn(): string => '
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const email = document.getElementById("data.email");
                        if (email) email.setAttribute("name", "email");
                        const password = document.getElementById("data.password");
                        if (password) password.setAttribute("name", "password");
                        const remember = document.getElementById("data.remember");
                        if (remember) remember.setAttribute("name", "remember");

                        // ── CSRF / Session cookie cleanup ──────────────────────────────
                        // On the login page, expire any stale XSRF-TOKEN or session cookie
                        // that the browser is holding from a previous server session.
                        // This prevents the "419 Page Expired" error after a server restart.
                        if (window.location.pathname.includes("/login")) {
                            // Cookie name is Str::slug(APP_NAME) + -session => laravel-session
                            // XSRF-TOKEN is the CSRF cookie set by Laravel for Axios/fetch.
                            var cookiesToClear = ["XSRF-TOKEN", "laravel-session"];
                            cookiesToClear.forEach(function(name) {
                                document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                                document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=localhost;";
                            });
                        }
                    });
                </script>
            ',
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn(): string => '
                <style>
                    .fi-topbar {
                        background-color: var(--primary-600) !important;
                    }
                    .fi-topbar * {
                        color: white !important;
                    }
                    .fi-topbar input {
                        color: black !important;
                    }
                    /* Reset dropdown menu colors inside topbar */
                    .fi-dropdown *, .fi-dropdown-panel * {
                        color: #374151 !important;
                    }
                    .dark .fi-dropdown *, .dark .fi-dropdown-panel * {
                        color: #e2e8f0 !important;
                    }

                    /* ── Planning grid — guaranteed 3-column layout ── */
                    .planning-grid {
                        display: grid;
                        grid-template-columns: repeat(3, minmax(0, 1fr));
                        gap: 2rem;
                        max-width: 72rem;
                        margin-left: auto;
                        margin-right: auto;
                        width: 100%;
                        padding-left: 1.5rem;
                        padding-right: 1.5rem;
                    }
                    .planning-day-card {
                        background: #ffffff;
                        border: 1px solid #f1f5f9;
                        border-radius: 0.75rem;
                        padding: 1.25rem;
                        display: flex;
                        flex-direction: column;
                    }
                    .planning-day-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding-bottom: 0.5rem;
                        margin-bottom: 1rem;
                        border-bottom: 1px solid #f1f5f9;
                    }
                    .planning-day-title {
                        font-size: 0.875rem;
                        font-weight: 600;
                        color: #374151;
                        text-transform: capitalize;
                    }
                    .planning-day-count {
                        font-size: 0.75rem;
                        font-weight: 700;
                        color: #6366f1;
                    }
                    .planning-courses {
                        display: flex;
                        flex-direction: column;
                        gap: 0.5rem;
                    }
                    .planning-course-tile {
                        position: relative;
                        background: #f8fafc;
                        border: 1px solid #e2e8f0;
                        border-radius: 0.5rem;
                        padding: 0.5rem 0.75rem;
                        transition: border-color 0.15s;
                    }
                    .planning-course-tile:hover {
                        border-color: #cbd5e1;
                    }
                    .planning-course-tile:hover .planning-trash-btn {
                        opacity: 1;
                    }
                    .planning-trash-btn {
                        position: absolute;
                        top: 0.375rem;
                        right: 0.375rem;
                        opacity: 0;
                        color: #cbd5e1;
                        transition: opacity 0.15s, color 0.15s;
                        background: none;
                        border: none;
                        cursor: pointer;
                        padding: 0;
                        line-height: 1;
                    }
                    .planning-trash-btn:hover { color: #f87171; }
                    .planning-trash-btn svg { width: 0.875rem; height: 0.875rem; }
                    .planning-course-time {
                        font-size: 0.6875rem;
                        color: #9ca3af;
                        margin-bottom: 0.125rem;
                        font-variant-numeric: tabular-nums;
                    }
                    .planning-course-name {
                        font-size: 0.875rem;
                        font-weight: 700;
                        color: #4f46e5;
                        margin-bottom: 0.25rem;
                        line-height: 1.2;
                    }
                    .planning-course-meta {
                        display: flex;
                        align-items: center;
                        gap: 0.25rem;
                        font-size: 0.6875rem;
                        color: #9ca3af;
                        margin-top: 0.125rem;
                    }
                    .planning-course-meta svg { width: 0.625rem; height: 0.625rem; opacity: 0.6; flex-shrink: 0; }
                    .planning-empty {
                        text-align: center;
                        padding: 1.5rem 0;
                        font-size: 0.6875rem;
                        color: #d1d5db;
                        font-style: italic;
                    }
                </style>
            ',
        );
    }
}
