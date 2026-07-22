<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Event;
use Illuminate\Support\Carbon;

class EventsChartWidget extends ChartWidget
{
    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return new \Illuminate\Support\HtmlString('
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 rounded-xl bg-sky-50 dark:bg-sky-900/30 p-2 border border-sky-100 dark:border-sky-800">
                    <svg class="w-5 h-5 text-sky-500 dark:text-sky-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                </div>
                <span class="text-base font-bold text-gray-900 dark:text-white">Events Overview</span>
            </div>
        ');
    }

    protected int | string | array $columnSpan = 'full'; // Expanded to full width

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && $user->isAdmin();
    }

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        // For the last 12 months
        for ($i = 11; $i >= 0; $i--) {
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
                    'backgroundColor' => [
                        '#38bdf8', // Light Sky
                        '#0ea5e9', // Sky 500
                        '#0284c7', // Sky 600
                        '#60a5fa', // Light Blue
                        '#3b82f6', // Blue 500
                        '#818cf8', // Light Indigo
                        '#6366f1', // Indigo 500
                        '#a78bfa', // Light Violet
                        '#8b5cf6', // Violet 500
                        '#e879f9', // Light Fuchsia
                        '#d946ef', // Fuchsia 500
                        '#f43f5e', // Rose 500
                    ],
                    'borderRadius' => 8, // Heavily rounded
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Changed from line to bar
    }
}
