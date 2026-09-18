<?php

namespace App\Filament\Widgets;

use App\Models\AuditLog;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;

class ActivityLogTableWidget extends BaseWidget
{
    protected static ?int $sort = 10;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && $user->isAdmin();
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return new HtmlString('
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 p-2 border border-emerald-200 dark:border-emerald-800">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </div>
                <div>
                    <span class="text-base font-bold text-gray-900 dark:text-white">Journal d\'activités</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-normal">Historique en temps réel des actions et opérations du système</p>
                </div>
            </div>
        ');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(AuditLog::query()->latest('created_at'))
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Rechercher par personnel, action, matière, description...')
            ->searchDebounce('500ms')
            ->defaultPaginationPageOption(5)
            ->paginationPageOptions([5, 10, 25])
            ->columns([
                TextColumn::make('created_at')
                    ->label('Horodatage')
                    ->dateTime('d/m/Y H:i:s', 'Africa/Casablanca')
                    ->description(fn (AuditLog $record): string => $record->created_at ? $record->created_at->locale('fr')->diffForHumans() : '')
                    ->sortable(),

                TextColumn::make('user_name')
                    ->label('Personnel')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($q) use ($search) {
                            $q->where('user_name', 'like', "%{$search}%")
                              ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$search}%"));
                        });
                    })
                    ->weight('bold')
                    ->icon('heroicon-m-user-circle')
                    ->description(fn (AuditLog $record): string => $record->user?->email ?? ''),

                TextColumn::make('user_role')
                    ->label('Rôle')
                    ->badge()
                    ->searchable()
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
                        'info' => fn ($state) => in_array($state, ['Traitement Demande', 'Configuration Système', 'Modification Paramètres']),
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
                    ->searchable()
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
                    ->label("Aujourd'hui uniquement")
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', Carbon::today('Africa/Casablanca'))),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Détails')
                    ->modalHeading("Détails de l'action enregistrée")
                    ->modalCancelActionLabel('Fermer')
                    ->icon('heroicon-m-eye')
                    ->color('primary')
                    ->form([
                        Forms\Components\Section::make("Informations de l'opération")
                            ->schema([
                                Forms\Components\TextInput::make('user_name')
                                    ->label('Auteur / Responsable')
                                    ->disabled(),
                                Forms\Components\TextInput::make('user_role')
                                    ->label('Rôle')
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        'admin' => 'Directeur / Admin',
                                        'secretaire' => 'Secrétariat',
                                        'professeur' => 'Professeur',
                                        default => ucfirst($state),
                                    })
                                    ->disabled(),
                                Forms\Components\TextInput::make('action')
                                    ->label("Type d'action")
                                    ->disabled(),
                                Forms\Components\TextInput::make('subject_type')
                                    ->label('Module concerné')
                                    ->disabled(),
                                Forms\Components\TextInput::make('ip_address')
                                    ->label('Adresse IP')
                                    ->disabled(),
                                Forms\Components\TextInput::make('created_at')
                                    ->label('Date & Heure exacte')
                                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->setTimezone('Africa/Casablanca')->format('d/m/Y H:i:s') : '')
                                    ->prefixIcon('heroicon-m-calendar-days')
                                    ->disabled(),
                                Forms\Components\Textarea::make('description')
                                    ->label('Description complète')
                                    ->columnSpanFull()
                                    ->disabled(),
                                Forms\Components\KeyValue::make('details')
                                    ->label('Données complémentaires')
                                    ->columnSpanFull()
                                    ->disabled(),
                            ])->columns(2),
                    ]),
            ])
            ->emptyStateHeading('Aucune activité enregistrée')
            ->emptyStateDescription('Toutes les actions effectuées par le personnel apparaîtront ici.')
            ->emptyStateIcon('heroicon-o-shield-check');
    }
}
