<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Professor Dashboard Stats Widget
 * Shows relevant statistics for the logged-in professor.
 * Only visible to users with 'professeur' role.
 */
class ProfessorStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && $user->role === 'professeur';
    }

    protected function getStats(): array
    {
        $professorId = auth()->id();

        // Total courses uploaded by this professor
        $totalCourses = Course::where('professor_id', $professorId)->count();

        // Get all classes assigned to this professor's courses
        $classesIds = Course::where('professor_id', $professorId)
            ->pluck('classe_id')
            ->unique();

        // Total students enrolled in those classes
        $totalStudents = Student::whereHas('registres', function ($query) use ($classesIds) {
            $query->whereIn('Cla_id', $classesIds);
        })->count();

        // Courses uploaded this month
        $coursesThisMonth = Course::where('professor_id', $professorId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Courses uploaded this week
        $coursesThisWeek = Course::where('professor_id', $professorId)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        return [
            Stat::make(
                'Total Courses',
                $totalCourses
            )
                ->description('Courses you have uploaded')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary')
                ->icon('heroicon-o-document-text'),

            Stat::make(
                'Students',
                $totalStudents
            )
                ->description('Enrolled in your classes')
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->icon('heroicon-o-user-group'),

            Stat::make(
                'This Month',
                $coursesThisMonth
            )
                ->description('Courses added this month')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info')
                ->icon('heroicon-o-calendar-days'),

            Stat::make(
                'This Week',
                $coursesThisWeek
            )
                ->description('Courses added this week')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->icon('heroicon-o-clock'),
        ];
    }
}
