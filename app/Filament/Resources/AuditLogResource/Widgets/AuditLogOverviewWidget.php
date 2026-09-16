<?php

namespace App\Filament\Resources\AuditLogResource\Widgets;

use App\Models\AuditLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AuditLogOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalLogs = AuditLog::count();
        $secretaireLogs = AuditLog::where('user_role', 'secretaire')->count();
        $professeurLogs = AuditLog::where('user_role', 'professeur')->count();
        $todayLogs = AuditLog::whereDate('created_at', Carbon::today())->count();

        return [
            Stat::make('Total Activités', $totalLogs)
                ->description('Actions tracées dans le système')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make('Actions Secrétariat', $secretaireLogs)
                ->description('Traitement demandes, avis & présences')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Actions Professeurs', $professeurLogs)
                ->description('Saisie de notes & cours')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('warning'),

            Stat::make('Dernières 24h', $todayLogs)
                ->description('Activité récente du personnel')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('success'),
        ];
    }
}
