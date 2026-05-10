<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Student;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

/**
 * Slim stats widget shown to Secrétaire users only.
 * Excludes all financial/revenue data.
 */
class SecretaireDashboardStatsWidget extends Widget
{
    protected static string $view = 'filament.widgets.secretaire-dashboard-stats';

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && $user->isSecretaire();
    }

    protected function getViewData(): array
    {
        $totalStudents    = Student::count();
        $totalNotifications = DB::table('notification')->count();
        $upcomingEvents   = Event::where('date_evenement', '>=', now())->count();
        $nextEvent        = Event::where('date_evenement', '>=', now())
            ->orderBy('date_evenement', 'asc')
            ->first();

        return [
            'totalStudents'      => $totalStudents,
            'studentsGrowth'     => '+12 this month',
            'totalNotifications' => $totalNotifications,
            'notificationsGrowth' => '+5% vs last week',
            'upcomingEvents'     => $upcomingEvents,
            'nextEventDate'      => $nextEvent
                ? \Carbon\Carbon::parse($nextEvent->date_evenement)->format('M d')
                : 'None',
        ];
    }
}
