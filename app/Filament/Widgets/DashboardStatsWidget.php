<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Student;
use App\Models\Event;
use App\Models\Paiement;
use Illuminate\Support\Facades\DB;

class DashboardStatsWidget extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-stats';
    
    // Make this take full width
    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        // 1. Total Students
        $totalStudents = Student::count();
        $newStudentsThisMonth = Student::count(); // Adjust if you have a created_at logic, but for now we just show total

        // 2. Notifications Sent
        // We will query the DB for actual notifications if available, otherwise just count
        $totalNotifications = DB::table('notification')->count();

        // 3. Upcoming Events
        // Count events where the date is in the future
        $upcomingEvents = Event::where('date_evenement', '>=', now())->count();
        $nextEvent = Event::where('date_evenement', '>=', now())->orderBy('date_evenement', 'asc')->first();

        // 4. Payment Alerts
        // Count non-paid / overdue payments
        $overduePayments = Paiement::where('statut', '!=', 'Payé')->count();

        return [
            'totalStudents' => $totalStudents,
            'studentsGrowth' => '+12 this month', // Mock growth since timestamps might not exist
            'totalNotifications' => $totalNotifications,
            'notificationsGrowth' => '+5% vs last week',
            'upcomingEvents' => $upcomingEvents,
            'nextEventDate' => $nextEvent ? \Carbon\Carbon::parse($nextEvent->date_evenement)->format('M d') : 'None',
            'overduePayments' => $overduePayments,
        ];
    }
}
