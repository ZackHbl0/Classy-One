<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ParentsRelationManager extends RelationManager
{
    protected static string $relationship = 'parents';

    protected static ?string $title = 'Parents / Tuteurs (أولياء الأمور)';

    protected static ?string $modelLabel = 'Parent';

    protected static ?string $pluralModelLabel = 'Parents';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom complet')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('Téléphone')
                    ->tel()
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(fn($record) => "{$record->name} ({$record->phone})")
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom complet')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Adresse e-mail')
                    ->searchable()
                    ->copyable(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Associer un parent')
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
