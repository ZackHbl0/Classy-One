<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanningResource\Pages;
use App\Filament\Resources\PlanningResource\RelationManagers;
use App\Models\Planning;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlanningResource extends Resource
{
    protected static ?string $model = Planning::class;

    public static function shouldRegisterNavigation(): bool
    {
        // Hide from professors - show only to admin/secretaire
        return auth()->user()?->role !== 'professeur';
    }

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('classe_id')
                    ->label('Classe')
                    ->options(function () {
                        return \App\Models\Classe::all()
                            ->mapWithKeys(fn($c) => [
                                $c->id => collect([$c->nomClasse ?? $c->name ?? $c->libelle ?? null])
                                    ->filter()->first() ?? 'Classe #' . $c->id
                            ]);
                    })
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('jour')
                    ->label('Jour')
                    ->options([
                        'Lundi' => 'Lundi',
                        'Mardi' => 'Mardi',
                        'Mercredi' => 'Mercredi',
                        'Jeudi' => 'Jeudi',
                        'Vendredi' => 'Vendredi',
                        'Samedi' => 'Samedi',
                        'Dimanche' => 'Dimanche',
                    ])
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('matiere')
                    ->label('Matière / Cours')
                    ->placeholder('e.g. Algorithmique')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TimePicker::make('check_in')
                    ->label('Heure Début')
                    ->required(),
                Forms\Components\TimePicker::make('check_out')
                    ->label('Heure Fin')
                    ->required(),
                Forms\Components\TextInput::make('salle')
                    ->label('Salle')
                    ->placeholder('Salle 101')
                    ->maxLength(255),
                Forms\Components\Select::make('professeur_name')
                    ->label('Prof')
                    ->placeholder('Sélectionnez un prof')
                    ->prefixIcon('heroicon-o-user')
                    ->options(function () {
                        return \App\Models\User::where('role', 'professeur')->pluck('name', 'name');
                    })
                    ->searchable(),
                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options([
                        'COURS' => 'COURS',
                        'TD' => 'TD',
                        'TP' => 'TP',
                        'EXAMEN' => 'EXAMEN',
                    ])
                    ->default('COURS')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Hidden::make('status')
                    ->default('Actif'),
                Forms\Components\Hidden::make('weekNumber')
                    ->default(fn() => \Carbon\Carbon::now()->weekOfYear),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('classe_id')
                    ->label('Classe')
                    ->getStateUsing(fn($record) => optional($record->classe)->nomClasse
                        ?? optional($record->classe)->name
                        ?? optional($record->classe)->libelle
                        ?? 'Classe #' . $record->classe_id)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('matiere')
                    ->label('Matière')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('professeur_name')
                    ->label('Professeur')
                    ->searchable(),
                Tables\Columns\TextColumn::make('weekNumber')
                    ->label('Semaine')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Actif' => 'success',
                        'Inactif' => 'gray',
                        'Annulé' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('classe_id')
                    ->label('Classe')
                    ->options(fn() => \App\Models\Classe::all()
                        ->mapWithKeys(fn($c) => [
                            $c->id => collect([$c->nomClasse ?? $c->name ?? $c->libelle ?? null])
                                ->filter()->first() ?? 'Classe #' . $c->id
                        ])),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Actif' => 'Actif',
                        'Inactif' => 'Inactif',
                        'Annulé' => 'Annulé',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlannings::route('/'),
            'create' => Pages\CreatePlanning::route('/create'),
            'edit' => Pages\EditPlanning::route('/{record}/edit'),
        ];
    }
}
