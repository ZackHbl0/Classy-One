<?php

namespace App\Filament\Resources\ClasseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RegistresRelationManager extends RelationManager
{
    protected static string $relationship = 'registres';

    protected static ?string $title = 'Étudiants';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('idStudent')
                    ->label('Étudiant')
                    ->options(function () {
                        return \App\Models\Student::all()->mapWithKeys(function ($student) {
                            return [$student->idStudent => $student->nom . ' ' . $student->prenom . ' (' . $student->matricule . ')'];
                        });
                    })
                    ->searchable()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('idStudent')
            ->columns([
                Tables\Columns\TextColumn::make('student.matricule')
                    ->label('Matricule')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.nom')
                    ->label('Étudiant')
                    ->formatStateUsing(fn($record) => optional($record->student)->nom . ' ' . optional($record->student)->prenom)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.telephone')
                    ->label('Téléphone')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Ajouter un Étudiant')
                    ->modalHeading('Ajouter un étudiant à la classe'),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label('Retirer')
                    ->modalHeading('Retirer l\'étudiant de la classe'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Retirer sélectionnés'),
                ]),
            ]);
    }
}
