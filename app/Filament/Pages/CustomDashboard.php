<?php

namespace App\Filament\Pages;

class CustomDashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $slug = ''; // override root slug
    protected static ?string $title = 'Dashboard'; // maintain dashboard title

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        $role = auth()->user()?->role;

        return match ($role) {
            'professeur' => 'Professor Dashboard',
            'secretaire' => 'Secretary Dashboard',
            'admin' => 'Admin Dashboard',
            default => 'Dashboard',
        };
    }

    public function getSubheading(): ?string
    {
        $role = auth()->user()?->role;

        return match ($role) {
            'professeur' => 'Your teaching activities and course management',
            'secretaire' => 'Administrative overview',
            'admin' => 'Overview of ClassyOne activity',
            default => 'Welcome to your dashboard',
        };
    }

    /**
     * @return int | string | array<string, int | string | null>
     */
    public function getColumns(): int | string | array
    {
        return 2;
    }

    public function getWidgets(): array
    {
        // Check if user is a professor
        $isProfessor = auth()->user()?->role === 'professeur';

        // Base widgets visible to all non-professor roles
        $widgets = [
            // Admin-only full stats (revenue included)
            \App\Filament\Widgets\DashboardStatsWidget::class,
            // Secrétaire-only slim stats (no revenue)
            \App\Filament\Widgets\SecretaireDashboardStatsWidget::class,
            // Notifications replacing the old events chart
            \App\Filament\Widgets\RecentNotificationsWidget::class,
        ];

        // If professor: show professor-specific widgets only
        if ($isProfessor) {
            return [
                \App\Filament\Widgets\ProfessorStatsWidget::class,
                \App\Filament\Widgets\ProfessorLatestCoursesWidget::class,
            ];
        }

        // For all other roles (admin, secretaire, etc.): show default widgets + shared ones
        return array_merge($widgets, [
            // Visible to all roles
            \App\Filament\Widgets\UpcomingEventsWidget::class,
        ]);
    }
}
