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

    public static function shouldRegisterNavigation(): bool
    {
        // Hide from professors - show only to admin/secretaire
        return auth()->user()?->role !== 'professeur';
    }

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
                                'Planning' => 'Planning',
                                'Examen' => 'Examen',
                                'Événement' => 'Événement',
                                'Paiement' => 'Paiement',
                                'Urgent' => 'Urgent',
                                'Général' => 'Général',
                            ])
                            ->default('Général')
                            ->required(),
                        Forms\Components\Textarea::make('message')
                            ->label('Message')
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Cibles')
                    ->schema([
                        Forms\Components\Radio::make('target_type')
                            ->label('Type de cible')
                            ->options([
                                'all' => 'Tous les étudiants',
                                'classes' => 'Par Classe',
                                'students' => 'Par Étudiant',
                            ])
                            ->default('all')
                            ->live()
                            ->required(),

                        Forms\Components\Select::make('target_classes')
                            ->label('Sélectionner les classes')
                            ->multiple()
                            ->options(\App\Models\Classe::pluck('nomClasse', 'id')->toArray())
                            ->visible(fn(Forms\Get $get) => $get('target_type') === 'classes')
                            ->required(fn(Forms\Get $get) => $get('target_type') === 'classes')
                            ->searchable(),

                        Forms\Components\Select::make('target_students')
                            ->label('Sélectionner les étudiants')
                            ->multiple()
                            ->options(\App\Models\Student::all()->mapWithKeys(fn($s) => [$s->idStudent => $s->full_name])->toArray())
                            ->visible(fn(Forms\Get $get) => $get('target_type') === 'students')
                            ->required(fn(Forms\Get $get) => $get('target_type') === 'students')
                            ->searchable(),
                    ]),
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
                Tables\Columns\TextColumn::make('target_summary')
                    ->label('Cible')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('categorie')
                    ->label('Catégorie')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Urgent' => 'danger',
                        'Paiement' => 'warning',
                        'Examen' => 'warning',
                        'Planning' => 'info',
                        'Événement' => 'primary',
                        'Général' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('message')
                    ->label('Message')
                    ->limit(30),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date d\'envoi')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('target_type')
                    ->label('Type de cible')
                    ->options([
                        'all' => 'Tous',
                        'classes' => 'Classes',
                        'students' => 'Étudiants',
                    ]),
                Tables\Filters\SelectFilter::make('categorie')
                    ->options([
                        'Planning' => 'Planning',
                        'Examen' => 'Examen',
                        'Événement' => 'Événement',
                        'Paiement' => 'Paiement',
                        'Urgent' => 'Urgent',
                        'Général' => 'Général',
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

    public static function getWidgets(): array
    {
        return [
            NotificationResource\Widgets\NotificationFormWidget::class,
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
