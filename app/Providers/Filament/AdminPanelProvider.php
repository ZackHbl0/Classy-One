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
            ->spa()
            ->brandLogo(asset('classyone_logo.png'))
            ->brandLogoHeight('110px')
            ->login(\App\Filament\Pages\Auth\Login::class)

            ->colors([
                'primary' => \Filament\Support\Colors\Color::hex('#0f4c3a'),
                'success' => \Filament\Support\Colors\Color::hex('#22c55e'),
                'info'    => \Filament\Support\Colors\Color::hex('#3b82f6'),
                'warning' => \Filament\Support\Colors\Color::hex('#f97316'),
                'gray'    => \Filament\Support\Colors\Color::Slate,
            ])
            ->font('Geist', 'https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&display=swap')
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

                        // ── Custom User Menu Name Injection ────────────────────────
                        if (!window.location.pathname.includes("/login")) {
                            setTimeout(() => {
                                const userMenuBtn = document.querySelector(".fi-topbar .fi-user-menu button") || document.querySelector(".fi-user-menu-btn");
                                if (userMenuBtn && !document.querySelector(".custom-user-name")) {
                                    const nameEle = document.createElement("span");
                                    nameEle.className = "custom-user-name" ;
                                    nameEle.style.cssText = "font-size: 0.875rem; font-weight: 600; color: #1e293b; margin-left: 0.5rem; display: none;";
                                    nameEle.innerText = "' . addslashes((function () {
                $user = auth()->user();
                $name = $user?->name ?? '';
                $prefix = ($user?->role === "professeur" && !str_starts_with($name, "Prof.")) ? "Prof. " : "";
                return $prefix . $name;
            })()) . '";
                                    
                                    if(window.innerWidth > 640) {
                                        nameEle.style.display = "inline-block";
                                    }
                                    
                                    userMenuBtn.style.display = "flex";
                                    userMenuBtn.style.alignItems = "center";
                                    userMenuBtn.style.gap = "0.25rem";
                                    
                                    userMenuBtn.appendChild(nameEle);
                                    
                                    const arrow = document.createElement("span");
                                    arrow.innerHTML = \'<svg style="width: 1rem; height: 1rem; color: #94a3b8; margin-left: 0.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>\';
                                    userMenuBtn.appendChild(arrow);
                                    
                                    const srName = userMenuBtn.querySelector(".sr-only");
                                    if (srName) { srName.style.display = "none"; }
                                }
                            }, 50); /* Short delay to ensure Filament Alpine finishes init */
                        }
                    });
                </script>
            ',
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn(): string => '
                <style>
                    /* Force Font for all modes */
                    html, body {
                        font-family: "Geist", sans-serif !important;
                    }

                    /* ── LIGHT MODE ONLY OVERRIDES ── */
                    html:not(.dark) body, html:not(.dark) .fi-body {
                        background-color: #fafafa !important;
                    }

                    /* Topbar Minimalist */
                    html:not(.dark) .fi-topbar {
                        background-color: #ffffff !important;
                        border-bottom: 1px solid #f1f5f9 !important;
                        box-shadow: none !important;
                    }
                    html:not(.dark) .fi-topbar * {
                        color: #1e293b !important;
                    }

                    /* Sidebar */
                    html:not(.dark) .fi-sidebar {
                        background-color: #ffffff !important;
                        border-right: 1px solid #f1f5f9 !important;
                        box-shadow: none !important;
                    }
                    
                    /* Sidebar Items Active state customization */
                    html:not(.dark) .fi-sidebar-item-active > a, 
                    html:not(.dark) .fi-sidebar-item-active > button {
                        background-color: #f0fdf4 !important;
                        border-left: 4px solid #0f4c3a !important;
                        border-radius: 0 0.5rem 0.5rem 0 !important;
                        margin-left: -0.5rem; 
                    }
                    html:not(.dark) .fi-sidebar-item-active .fi-sidebar-item-label, 
                    html:not(.dark) .fi-sidebar-item-active .fi-icon {
                        color: #0f4c3a !important;
                        font-weight: 600 !important;
                    }

                    /* Stat/Overview Cards */
                    html:not(.dark) .fi-wi-stats-overview-stat {
                        background-color: #ffffff !important;
                        border: none !important;
                        border-radius: 1rem !important;
                        box-shadow: 0 4px 20px -2px rgba(0,0,0,0.03) !important;
                        position: relative;
                        overflow: hidden;
                    }
                    
                    /* Both modes: line below stat cards */
                    .fi-wi-stats-overview-stat::after {
                        content: "";
                        position: absolute;
                        bottom: 0;
                        left: 10%;
                        right: 10%;
                        height: 3px;
                        border-radius: 99px 99px 0 0;
                    }
                    div[style*="--c-50"]::after, .fi-color-primary::after, .fi-color-custom::after { background-color: var(--c-500, #0f4c3a); }
                    div[style*="--c-500"]::after { background-color: var(--c-500); }
                    
                    html:not(.dark) .fi-wi-stats-overview-stat-value {
                        font-size: 2.25rem !important;
                        color: #0f172a !important;
                        font-weight: 700 !important;
                        margin-top: 0.25rem !important;
                        margin-bottom: 0.25rem !important;
                    }

                    /* Table Overrides */
                    .fi-ta-content {
                        overflow-x: hidden !important;
                    }
                    .fi-ta-table {
                        table-layout: auto !important;
                        width: 100% !important;
                    }
                    .fi-ta-table td {
                        white-space: nowrap !important;
                        overflow: hidden !important;
                        text-overflow: ellipsis !important;
                        max-width: 20rem;
                    }
                    html:not(.dark) .fi-ta {
                        background-color: #ffffff !important;
                        border-radius: 1rem !important;
                        border: 1px solid #f1f5f9 !important;
                        box-shadow: 0 4px 20px -2px rgba(0,0,0,0.03) !important;
                    }
                    html:not(.dark) .fi-ta-header {
                        border-bottom: 1px solid #f1f5f9 !important;
                        background-color: #ffffff !important;
                        padding: 1.5rem !important;
                    }
                    html:not(.dark) .fi-ta-header-heading {
                        font-weight: 600 !important;
                        font-size: 1.1rem !important;
                        color: #0f172a !important;
                    }
                    html:not(.dark) .fi-ta-table th {
                        font-size: 0.75rem !important;
                        color: #94a3b8 !important;
                        text-transform: uppercase !important;
                        letter-spacing: 0.05em !important;
                        background: #ffffff !important;
                        border-bottom: 1px solid #f1f5f9 !important;
                        padding-top: 1rem !important;
                        padding-bottom: 1rem !important;
                    }
                    html:not(.dark) .fi-ta-table td {
                        border-bottom: 1px solid #f1f5f9 !important;
                        border-left: none !important;
                        border-right: none !important;
                        font-size: 0.875rem !important;
                        color: #334155 !important;
                    }
                    html:not(.dark) .fi-ta-table tr:hover td {
                        background-color: #fafafa !important;
                    }
                    
                    /* Reset dropdown menu colors inside topbar */
                    html:not(.dark) .fi-dropdown *, html:not(.dark) .fi-dropdown-panel * {
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
