<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Student;
use App\Models\Event;
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
        
        $upcomingEvents = Event::where('date_evenement', '>=', now())->count();
        $nextEvent = Event::where('date_evenement', '>=', now())->orderBy('date_evenement', 'asc')->first();
        $nextEventDate = $nextEvent ? \Carbon\Carbon::parse($nextEvent->date_evenement)->format('M d') : 'None';

        $overduePayments = Paiement::where('statut', '!=', 'Payé')->count();
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

            Stat::make('Upcoming Events', $upcomingEvents)
                ->description('Next: ' . $nextEventDate)
                ->color('warning')
                ->icon('heroicon-o-calendar'),

            Stat::make('Payment Alerts', $overduePayments)
                ->description($overduePayments . ' overdue payments')
                ->color('danger')
                ->icon('heroicon-o-credit-card'),
        ];
    }
}
