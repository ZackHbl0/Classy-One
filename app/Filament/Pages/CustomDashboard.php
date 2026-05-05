<?php

namespace App\Filament\Pages;

class CustomDashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $slug = ''; // override root slug
    protected static ?string $title = 'Dashboard'; // maintain dashboard title

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Dashboard';
    }

    public function getSubheading(): ?string
    {
        return 'Overview of OSBT Notify activity';
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
        return [
            \App\Filament\Widgets\DashboardStatsWidget::class,
            \App\Filament\Widgets\NotificationsChartWidget::class,
            \App\Filament\Widgets\EventsChartWidget::class,
            \App\Filament\Widgets\RecentNotificationsWidget::class,
            \App\Filament\Widgets\UpcomingEventsWidget::class,
        ];
    }
}
