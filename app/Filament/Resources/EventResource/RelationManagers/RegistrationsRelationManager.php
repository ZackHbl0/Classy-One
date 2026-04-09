<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Student;

class RegistrationsRelationManager extends RelationManager
{
    protected static string $relationship = 'registrations';
    protected static ?string $title = 'Inscriptions à l\'événement';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('idStudent')
                    ->label('Étudiant')
                    ->options(Student::all()->pluck('nom', 'idStudent')->toArray())
                    ->searchable()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('student.nom')
                    ->label('Étudiant')
                    ->formatStateUsing(fn ($record) => optional($record->student)->nom . ' ' . optional($record->student)->prenom)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date d\'inscription')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
