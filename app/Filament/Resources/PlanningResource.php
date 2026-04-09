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

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Détails du Planning')
                    ->schema([
                        Forms\Components\Select::make('classe_id')
                            ->label('Classe')
                            ->options(function () {
                                return \App\Models\Classe::all()
                                    ->mapWithKeys(fn ($c) => [
                                        $c->id => collect([$c->nom_classe ?? $c->name ?? $c->libelle ?? null])
                                            ->filter()->first() ?? 'Classe #' . $c->id
                                    ]);
                            })
                            ->searchable()
                            ->required(),
                        Forms\Components\DatePicker::make('date')
                            ->required(),
                        Forms\Components\TextInput::make('weekNumber')
                            ->label('Numéro de semaine')
                            ->numeric()
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'Actif' => 'Actif',
                                'Inactif' => 'Inactif',
                                'Annulé' => 'Annulé',
                                'Pending' => 'Pending',
                                'Completed' => 'Completed',
                            ])
                            ->required(),
                        Forms\Components\TimePicker::make('check_in')
                            ->label('Heure de début'),
                        Forms\Components\TimePicker::make('check_out')
                            ->label('Heure de fin'),
                        Forms\Components\TextInput::make('matiere')
                            ->label('Matière')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('professeur_name')
                            ->label('Nom du Professeur')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('salle')
                            ->label('Salle')
                            ->maxLength(255),
                    ])->columns(2),
                Forms\Components\Section::make('Fichier de Planning')
                    ->schema([
                        Forms\Components\FileUpload::make('fileUrl')
                            ->label('Image ou PDF du Planning')
                            ->directory('plannings')
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('classe_id')
                    ->label('Classe')
                    ->getStateUsing(fn ($record) => optional($record->classe)->nom_classe
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
                    ->color(fn (string $state): string => match ($state) {
                        'Actif' => 'success',
                        'Inactif' => 'gray',
                        'Annulé' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('classe_id')
                    ->label('Classe')
                    ->options(fn () => \App\Models\Classe::all()
                        ->mapWithKeys(fn ($c) => [
                            $c->id => collect([$c->nom_classe ?? $c->name ?? $c->libelle ?? null])
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
