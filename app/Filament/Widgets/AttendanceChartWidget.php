<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Absence;
use Carbon\Carbon;

class AttendanceChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Attendance Tracking (Absences)';
    protected static ?int $sort = 3;
    
    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected function getData(): array
    {
        // Absences for the last 7 days
        $data = [];
        $labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = Absence::whereDate('date', $date)->count();
            $data[] = $count;
            $labels[] = $date->format('D, M d');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Number of Absences',
                    'data' => $data,
                    'backgroundColor' => '#f43f5e', // rose
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
