<?php

namespace App\Filament\Resources\ProfessorStudentResource\Pages;

use App\Filament\Resources\ProfessorStudentResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListProfessorStudents extends ListRecords
{
    protected static string $resource = ProfessorStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for professors
        ];
    }

    public function getTitle(): string
    {
        return 'Mes Étudiants';
    }

    public function getHeading(): string
    {
        return 'Mes Étudiants';
    }

    public function getSubheading(): ?string
    {
        return 'Liste des étudiants dans les classes que vous enseignez';
    }
}
