<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Filament\Resources\NotificationResource\RelationManagers;
use App\Models\Notification;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $modelLabel = 'Notification';
    protected static ?string $pluralModelLabel = 'Notifications';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Détails de la Notification')
                    ->schema([
                        Forms\Components\TextInput::make('titre')
                            ->label('Titre')
                            ->required(),
                        Forms\Components\Select::make('categorie')
                            ->label('Catégorie')
                            ->options([
                                'Examen' => 'Examen',
                                'Sortie' => 'Sortie',
                                'Réunion' => 'Réunion',
                                'Information' => 'Information',
                                'Urgent' => 'Urgent',
                                'Paiement' => 'Paiement',
                            ])
                            ->default('Information')
                            ->required(),
                        Forms\Components\Textarea::make('message')
                            ->label('Message')
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Cibles (Push Mobile)')
                    ->schema([
                        Forms\Components\Select::make('target_students')
                            ->label('Étudiants cibles (Laisser vide pour envoyer à TOUT LE MONDE)')
                            ->multiple()
                            ->options(Student::pluck('nom', 'idStudent')->toArray())
                            ->columnSpanFull(),
                    ])->visibleOn('create'), // Only show targeting when creating a new push!
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titre')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('categorie')
                    ->label('Catégorie')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Urgent' => 'danger',
                        'Examen' => 'warning',
                        'Paiement' => 'warning',
                        'Information' => 'info',
                        default => 'primary',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('message')
                    ->label('Message')
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date d\'envoi')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('categorie')
                    ->options([
                        'Examen' => 'Examen',
                        'Sortie' => 'Sortie',
                        'Réunion' => 'Réunion',
                        'Information' => 'Information',
                        'Urgent' => 'Urgent',
                        'Paiement' => 'Paiement',
                    ])
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'edit' => Pages\EditNotification::route('/{record}/edit'),
        ];
    }
}
