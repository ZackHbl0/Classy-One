<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Paiement;
use Carbon\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Revenue Evolution';
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected function getData(): array
    {
        // Simple monthly revenue for the current year
        $revenueData = [];
        $labels = [];
        for ($i = 1; $i <= 12; $i++) {
            // Note: dateEcheance might not represent payment date, but let's use it for estimation
            $sum = Paiement::where('statut', 'Payé')
                ->whereMonth('dateEcheance', $i)
                ->whereYear('dateEcheance', Carbon::now()->year)
                ->sum('montant');
            $revenueData[] = $sum;
            $labels[] = Carbon::create()->month($i)->format('M');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (MAD)',
                    'data' => $revenueData,
                    'borderColor' => '#10b981', // green
                    'fill' => true,
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
