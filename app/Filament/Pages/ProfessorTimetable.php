<?php

namespace App\Filament\Pages;

use App\Models\Planning;
use App\Models\User;
use Filament\Pages\Page;
use Carbon\Carbon;

class ProfessorTimetable extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Emploi du temps du prof';

    protected static ?string $title = '';

    protected static ?string $slug = 'emploi-du-temps';

    protected static string $view = 'filament.pages.professor-timetable';

    protected static ?int $navigationSort = 2;

    public ?string $selectedProfessor = null;

    public string $selectedClasseId = 'all';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['professeur', 'admin', 'secretaire']);
    }

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['professeur', 'admin', 'secretaire']), 403);

        if ($user->isProfesseur()) {
            $this->selectedProfessor = $user->name;
        } else {
            // Admin & Secrétaire: default to first professor or current
            $firstProf = User::where('role', 'professeur')->first();
            $this->selectedProfessor = $firstProf?->name ?? $user->name;
        }
    }

    public function updatedSelectedProfessor(): void
    {
        $this->selectedClasseId = 'all';
    }

    public function getProfessorsProperty()
    {
        return User::where('role', 'professeur')->pluck('name', 'name')->toArray();
    }

    public function getAvailableClassesProperty(): array
    {
        if (!$this->selectedProfessor) {
            return [];
        }

        return Planning::with('classe')
            ->where(function ($query) {
                $query->where('status', 'Actif')
                    ->orWhere('status', 'Pending')
                    ->orWhereNull('status');
            })
            ->where(function ($q) {
                $q->where('professeur_name', $this->selectedProfessor)
                  ->orWhere('professeur_name', 'like', '%' . $this->selectedProfessor . '%');
            })
            ->get()
            ->groupBy('classe_id')
            ->map(function ($items, $classeId) {
                $first = $items->first();
                return [
                    'id' => (string) $classeId,
                    'name' => $first->classe?->nomClasse ?? ('Classe #' . $classeId),
                    'count' => $items->count(),
                ];
            })
            ->values()
            ->toArray();
    }

    public function getTotalCoursesCountProperty(): int
    {
        return array_sum(array_column($this->availableClasses, 'count'));
    }

    public function getDefaultDayProperty(): string
    {
        $dayMap = [
            'Monday' => 'Lundi',
            'Tuesday' => 'Mardi',
            'Wednesday' => 'Mercredi',
            'Thursday' => 'Jeudi',
            'Friday' => 'Vendredi',
            'Saturday' => 'Samedi',
            'Sunday' => 'Dimanche',
        ];

        return $dayMap[Carbon::now()->format('l')] ?? 'Lundi';
    }

    public function getPlanningsProperty(): array
    {
        $days = [
            'Lundi' => [],
            'Mardi' => [],
            'Mercredi' => [],
            'Jeudi' => [],
            'Vendredi' => [],
            'Samedi' => [],
            'Dimanche' => [],
        ];

        if (!$this->selectedProfessor) {
            return $days;
        }

        $query = Planning::with('classe')
            ->where(function ($query) {
                $query->where('status', 'Actif')
                    ->orWhere('status', 'Pending')
                    ->orWhereNull('status');
            })
            ->where(function ($q) {
                $q->where('professeur_name', $this->selectedProfessor)
                  ->orWhere('professeur_name', 'like', '%' . $this->selectedProfessor . '%');
            });

        if ($this->selectedClasseId && $this->selectedClasseId !== 'all') {
            $query->where('classe_id', $this->selectedClasseId);
        }

        $plannings = $query->orderBy('check_in', 'asc')->get();

        foreach ($plannings as $plan) {
            $dayName = $plan->jour;
            if (isset($days[$dayName])) {
                $days[$dayName][] = $plan;
            }
        }

        return $days;
    }
}
