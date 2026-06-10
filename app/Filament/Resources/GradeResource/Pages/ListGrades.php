<?php

namespace App\Filament\Resources\GradeResource\Pages;

use App\Filament\Resources\GradeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGrades extends ListRecords
{
    protected static string $resource = GradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter une Note')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTitle(): string
    {
        return 'Gestion des Notes';
    }

    public function getHeading(): string
    {
        return 'Notes des Étudiants';
    }

    public function getSubheading(): ?string
    {
        $role = auth()->user()?->role;

        return match ($role) {
            'professeur' => 'Notes des étudiants dans vos classes',
            'admin', 'secretaire' => 'Toutes les notes de tous les étudiants',
            default => null,
        };
    }
}
