<?php

namespace App\Filament\Resources\PlanningResource\Pages;

use App\Filament\Resources\PlanningResource;
use App\Models\Classe;
use App\Models\Planning;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPlannings extends ListRecords
{
    protected static string $resource = PlanningResource::class;

    protected static string $view = 'filament.resources.planning-resource.pages.list-plannings';

    public $classe_id = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter un cours')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getClassesProperty()
    {
        return Classe::all()->mapWithKeys(function ($c) {
            return [$c->id => collect([$c->nom_classe ?? $c->name ?? $c->libelle ?? null])->filter()->first() ?? 'Classe #' . $c->id];
        });
    }

    public function loadPlanning()
    {
        // Triggers Livewire re-render which re-evaluates getPlanningsProperty()
    }

    public function deleteCourse(int $id): void
    {
        $course = Planning::find($id);

        if (!$course) {
            Notification::make()
                ->title('Cours introuvable')
                ->danger()
                ->send();
            return;
        }

        $course->delete();

        Notification::make()
            ->title('Cours supprimé avec succès')
            ->success()
            ->send();
    }

    public function getPlanningsProperty()
    {
        if (!$this->classe_id) {
            return collect();
        }

        $plannings = Planning::where('classe_id', $this->classe_id)
            ->where(function ($query) {
                $query->where('status', 'Actif')
                    ->orWhere('status', 'Pending');
            })
            ->orderBy('check_in')
            ->get();

        $grouped = resetDays();
        foreach ($plannings as $plan) {
            $dayName = $plan->jour;
            if (isset($grouped[$dayName])) {
                $grouped[$dayName][] = $plan;
            }
        }

        return $grouped;
    }
}

function resetDays()
{
    return [
        'Lundi' => [],
        'Mardi' => [],
        'Mercredi' => [],
        'Jeudi' => [],
        'Vendredi' => [],
        'Samedi' => [],
        'Dimanche' => [],
    ];
}
