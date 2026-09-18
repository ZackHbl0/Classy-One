<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Student;
use App\Models\Paiement;
use Illuminate\Support\Facades\DB;

class DashboardStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && $user->isAdmin();
    }

    protected function getStats(): array
    {
        $totalStudents = Student::count();
        $totalNotifications = DB::table('notification')->count();
        $totalRevenue = Paiement::where('statut', 'Payé')->sum('montant');

        return [
            Stat::make('Total Students', number_format($totalStudents))
                ->description('+12 this month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->icon('heroicon-o-users'),

            Stat::make('Total Revenue', number_format($totalRevenue, 2) . ' MAD')
                ->description('Collected revenue')
                ->color('primary')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Notifications Sent', number_format($totalNotifications))
                ->description('+5% vs last week')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info')
                ->icon('heroicon-o-bell'),
        ];
    }
}