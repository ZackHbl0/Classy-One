<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Filament\Resources\AuditLogResource\Widgets\AuditLogOverviewWidget;
use App\Models\AuditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = "Journal d'activités";

    protected static ?string $modelLabel = "Activité";

    protected static ?string $pluralModelLabel = "Journal d'activités";

    protected static ?int $navigationSort = 100;

    /**
     * Only Admin has access to Audit Logs
     */
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user && $user->isAdmin();
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user && $user->isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make("Détails de l'action")
                    ->schema([
                        Forms\Components\TextInput::make('user_name')
                            ->label('Auteur / Responsable')
                            ->disabled(),
                        Forms\Components\TextInput::make('user_role')
                            ->label('Rôle')
                            ->disabled(),
                        Forms\Components\TextInput::make('action')
                            ->label('Type d\'action')
                            ->disabled(),
                        Forms\Components\TextInput::make('subject_type')
                            ->label('Module concerné')
                            ->disabled(),
                        Forms\Components\TextInput::make('ip_address')
                            ->label('Adresse IP')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Date & Heure exacte')
                            ->disabled(),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->disabled(),
                        Forms\Components\KeyValue::make('details')
                            ->label('Données complémentaires')
                            ->columnSpanFull()
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Horodatage')
                    ->dateTime('d/m/Y H:i:s')
                    ->description(fn (AuditLog $record): string => $record->created_at ? $record->created_at->diffForHumans() : '')
                    ->sortable(),

                TextColumn::make('user_name')
                    ->label('Personnel')
                    ->searchable()
                    ->weight('bold')
                    ->icon('heroicon-m-user-circle')
                    ->description(fn (AuditLog $record): string => $record->user?->email ?? ''),

                TextColumn::make('user_role')
                    ->label('Rôle')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Directeur / Admin',
                        'secretaire' => 'Secrétariat',
                        'professeur' => 'Professeur',
                        default => ucfirst($state),
                    })
                    ->colors([
                        'primary' => 'admin',
                        'info' => 'secretaire',
                        'warning' => 'professeur',
                    ]),

                TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->colors([
                        'success' => fn ($state) => in_array($state, ['create', 'Saisie Note', 'Approbation Document', 'Nouvelle Inscription']),
                        'warning' => fn ($state) => in_array($state, ['update', 'Signalement Absence', 'Publication Avis']),
                        'danger' => fn ($state) => in_array($state, ['delete', 'Rejet Document', 'Suppression']),
                        'info' => fn ($state) => in_array($state, ['Traitement Demande', 'Configuration Système']),
                    ])
                    ->searchable(),

                TextColumn::make('subject_type')
                    ->label('Module')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('ip_address')
                    ->label('Adresse IP')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->fontFamily('mono')
                    ->badge()
                    ->color('gray'),
            ])
            ->filters([
                SelectFilter::make('user_role')
                    ->label('Filtrer par Rôle')
                    ->options([
                        'admin' => 'Directeurs (Admin)',
                        'secretaire' => 'Secrétaires',
                        'professeur' => 'Professeurs',
                    ]),

                SelectFilter::make('subject_type')
                    ->label('Filtrer par Module')
                    ->options([
                        'Note / Évaluation' => 'Notes & Évaluations',
                        'Absence' => 'Absences',
                        'Demande de Document' => 'Demandes de Documents',
                        'Avis & Annonce' => 'Avis & Annonces',
                        'Paramètres' => 'Paramètres Système',
                    ]),

                Filter::make('recent')
                    ->label('Aujourd\'hui uniquement')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', now()->toDateString())),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Détails')
                    ->icon('heroicon-m-eye')
                    ->color('primary'),
            ])
            ->bulkActions([
                // Read-only audit logs for security
            ]);
    }

    public static function getWidgets(): array
    {
        return [
            AuditLogOverviewWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
        ];
    }
}
