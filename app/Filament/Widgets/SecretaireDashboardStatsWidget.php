<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Student;
use App\Models\DocumentRequest;
use App\Models\Absence;
use App\Models\Paiement;
use Carbon\Carbon;

class SecretaireDashboardStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && $user->isSecretaire();
    }

    protected function getStats(): array
    {
        $totalStudents = Student::count();
        $pendingDocs = DocumentRequest::where('status', 'pending')->count(); // Assuming 'pending' is a status
        $todayAbsences = Absence::whereDate('date', Carbon::today())->count();
        $paymentAlerts = Paiement::where('statut', '!=', 'Payé')->count(); // Using 'Payé' as seen in other files

        return [
            Stat::make('Total Students', number_format($totalStudents))
                ->description('Active students in the school')
                ->color('success')
                ->icon('heroicon-o-users'),

            Stat::make('Pending Document Requests', $pendingDocs)
                ->description('Requires attention')
                ->color($pendingDocs > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-document-text'),

            Stat::make("Today's Absences", $todayAbsences)
                ->description('Students absent today')
                ->color($todayAbsences > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-user-minus'),
                
            Stat::make('Payément Alerts', $paymentAlerts)
                ->description('Overdue or unpaid payments')
                ->color($paymentAlerts > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}
