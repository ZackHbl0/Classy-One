<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Détails de l\'Événement')
                    ->schema([
                        Forms\Components\TextInput::make('titre')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('categorie')
                            ->label('Catégorie')
                            ->options([
                                'Examen' => 'Examen',
                                'Sortie' => 'Sortie',
                                'Réunion' => 'Réunion',
                                'Information' => 'Information',
                                'Autre' => 'Autre',
                            ])
                            ->required(),
                        Forms\Components\DateTimePicker::make('date_evenement')
                            ->label('Date & Heure')
                            ->required(),
                        Forms\Components\TextInput::make('lieu')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Pièces Jointes')
                    ->schema([
                        Forms\Components\FileUpload::make('pieceJointe')
                            ->label('Document / Image')
                            ->directory('events')
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('categorie')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_evenement')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lieu')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categorie')
                    ->options([
                        'Examen' => 'Examen',
                        'Sortie' => 'Sortie',
                        'Réunion' => 'Réunion',
                        'Information' => 'Information',
                        'Autre' => 'Autre',
                    ])
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
            RelationManagers\RegistrationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
