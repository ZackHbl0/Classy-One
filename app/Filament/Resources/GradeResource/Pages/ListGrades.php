<?php

namespace App\Filament\Resources\GradeResource\Pages;

use App\Filament\Resources\GradeResource;
use App\Models\Course;
use App\Models\Student;
use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;

class ListGrades extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = GradeResource::class;

    // We will use a simple blade view that just renders $this->table
    protected static string $view = 'filament.resources.grade-resource.pages.list-grades';

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $user = auth()->user();
                if ($user && in_array($user->role, ['professeur', 'prof'])) {
                    $classIds = Course::where('professor_id', $user->id)
                        ->pluck('classe_id')
                        ->push($user->classe_id)
                        ->filter()
                        ->unique()
                        ->toArray();

                    return Student::whereHas('registres', function ($q) use ($classIds) {
                        $q->whereIn('Cla_id', $classIds);
                    });
                }

                return Student::query();
            })
            ->columns([
                TextColumn::make('matricule')
                    ->label('Matricule')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable(['nom'])
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('prenom')
                    ->label('Prénom')
                    ->searchable(['prenom'])
                    ->sortable(),

                TextColumn::make('classe.nomClasse')
                    ->label('Classe')
                    ->badge()
                    ->color('success'),

                TextColumn::make('telephone')
                    ->label('Téléphone')
                    ->searchable()
                    ->icon('heroicon-m-phone'),
            ])
            ->actions([
                Action::make('voir_notes')
                    ->label('Voir Notes')
                    ->icon('heroicon-o-book-open')
                    ->button()
                    ->color('primary')
                    ->url(fn(Student $record): string => GradeResource::getUrl('student', ['record' => $record->idStudent]))
            ]);
    }

    public function getTitle(): string
    {
        return 'Notes des Étudiants';
    }

    public function getSubheading(): ?string
    {
        return 'Sélectionnez un étudiant pour voir et gérer ses notes.';
    }
}
