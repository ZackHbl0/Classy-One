<?php

namespace App\Filament\Resources\ParentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    protected static ?string $title = 'Enfants / Élèves rattachés';

    protected static ?string $modelLabel = 'Élève';

    protected static ?string $pluralModelLabel = 'Élèves';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('matricule')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nom')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('prenom')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(fn($record) => "{$record->nom} {$record->prenom} ({$record->matricule})")
            ->columns([
                Tables\Columns\TextColumn::make('matricule')
                    ->label('Matricule')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nom_complet')
                    ->label('Nom complet')
                    ->getStateUsing(fn($record) => "{$record->nom} {$record->prenom}")
                    ->searchable(['nom', 'prenom'])
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('telephone')
                    ->label('Téléphone')
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('classe.nomClasse')
                    ->label('Classe')
                    ->badge()
                    ->color('success')
                    ->placeholder('Non assigné'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Associer un élève')
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Dissocier'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('Dissocier sélectionnés'),
                ]),
            ]);
    }
}
