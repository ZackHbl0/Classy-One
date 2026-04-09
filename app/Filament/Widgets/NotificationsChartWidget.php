<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Notification;
use Illuminate\Support\Carbon;

class NotificationsChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Notifications per Week';
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        // For the last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('D'); // e.g. Mon, Tue
            $count = Notification::whereDate('created_at', $date->format('Y-m-d'))->count();
            $data[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Notifications',
                    'data' => $data,
                    'backgroundColor' => '#4f46e5', // Indigo-600
                    'borderRadius' => 4,
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
