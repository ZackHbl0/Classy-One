<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Event;
use Illuminate\Support\Carbon;

class EventsChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Events Created per Month';
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        // For the last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::today()->startOfMonth()->subMonths($i);
            $labels[] = $month->format('M'); // e.g. Sep, Oct

            $count = Event::whereYear('date_evenement', $month->year)
                          ->whereMonth('date_evenement', $month->month)
                          ->count();
            
            $data[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Events',
                    'data' => $data,
                    'borderColor' => '#3b82f6', // blue-500
                    'backgroundColor' => 'transparent',
                    'tension' => 0.4, // Smooth curve
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
