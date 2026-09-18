<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\RepeatableEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    public static function shouldRegisterNavigation(): bool
    {
        // Hide from professors - show only to admin/secretaire
        $role = auth()->user()?->role;
        return !in_array($role, ['prof', 'professeur']);
    }

    protected static ?string $navigationIcon = 'heroicon-o-users';

        public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations Personnelles')
                    ->schema([
                        Forms\Components\TextInput::make('matricule')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nom')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('prenom')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('telephone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('numero_tuteur')
                            ->label('Numéro du tuteur (ولي الأمر)')
                            ->tel()
                            ->autocomplete('off')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('frais_scolarite')
                            ->label('Frais de scolarité annuels')
                            ->numeric()
                            ->prefix('MAD')
                            ->default(15000.00)
                            ->required(),
                        Forms\Components\TextInput::make('password')
                            ->label('Mot de passe (Mobile App)')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->dehydrated(fn(?string $state) => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->helperText(fn(string $operation) => $operation === 'edit' ? 'Laissez vide pour conserver le mot de passe actuel.' : null),
                        Forms\Components\TextInput::make('current_password_display')
                            ->label('Mot de passe actuel (BDD)')
                            ->prefixIcon('heroicon-o-key')
                            ->readOnly()
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('copyPassword')
                                    ->icon('heroicon-m-clipboard-document')
                                    ->tooltip('Copier le mot de passe')
                                    ->action(function ($livewire, $state) {
                                        $livewire->js('window.navigator.clipboard.writeText(' . json_encode($state) . ');');
                                        if (empty($state) || str_contains($state, 'Crypté') || str_contains($state, 'Non défini')) {
                                            \Filament\Notifications\Notification::make()
                                                ->title('Mot de passe non disponible')
                                                ->body('Veuillez saisir un nouveau mot de passe dans le champ ci-contre et enregistrer.')
                                                ->warning()
                                                ->send();
                                            return;
                                        }
                                        \Filament\Notifications\Notification::make()
                                            ->title('Mot de passe copié !')
                                            ->success()
                                            ->send();
                                    })
                            )
                            ->dehydrated(false)
                            ->visible(fn(string $operation): bool => $operation === 'edit')
                            ->helperText("Mot de passe d'origine enregistré en BDD pour envoi à l'étudiant.")
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, ?Student $record) {
                                if (!$record) {
                                    $component->state('Non défini');
                                    return;
                                }
                                $pwd = $record->password_plain;
                                if (empty($pwd)) {
                                    if (!empty($record->password) && !str_starts_with($record->password, '$2y$') && !str_starts_with($record->password, '$2a$')) {
                                        $pwd = $record->password;
                                    }
                                }
                                if (empty($pwd)) {
                                    if (!empty($record->password)) {
                                        $pwd = "•••••••• (Crypté en BDD)";
                                    } else {
                                        $pwd = 'Non défini';
                                    }
                                }
                                $component->state($pwd);
                            }),
                    ])->columns(2)
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Group::make([
                                    TextEntry::make('nom_complet')
                                        ->label('Étudiant')
                                        ->getStateUsing(fn($record) => "{$record->nom} {$record->prenom}")
                                        ->weight('bold')
                                        ->size('lg'),
                                    TextEntry::make('matricule')
                                        ->label('')
                                        ->badge()
                                        ->color('info'),
                                ])->columnSpan(1),

                                TextEntry::make('situation_financiere_badge')
                                    ->label('Situation Financière')
                                    ->html()
                                    ->getStateUsing(function ($record) {
                                        $fin = $record->statut_financier;
                                        if ($fin['is_en_regle']) {
                                            return '<div class="flex flex-col gap-1">
                                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 w-fit">
                                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    <span>En règle — Scolarité 100% payée</span>
                                                </div>
                                                <span class="text-xs text-emerald-600 font-medium">Réglé: ' . number_format($record->total_paye, 2) . ' / ' . number_format($record->frais_scolarite_total, 2) . ' MAD</span>
                                            </div>';
                                        }
                                        if ($fin['code'] === 'partiel') {
                                            return '<div class="flex flex-col gap-1">
                                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-800 w-fit">
                                                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    <span>Paiement en cours (' . $record->pourcentage_paye . '%)</span>
                                                </div>
                                                <span class="text-xs text-gray-500 font-medium">Payé : ' . number_format($record->total_paye, 2) . ' / ' . number_format($record->frais_scolarite_total, 2) . ' MAD</span>
                                            </div>';
                                        }
                                        return '<div class="flex flex-col gap-1">
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200 dark:border-rose-800 w-fit">
                                                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                <span>Aucun versement (0%)</span>
                                            </div>
                                            <span class="text-xs text-rose-600 font-bold">Total dû : ' . number_format($record->frais_scolarite_total, 2) . ' MAD</span>
                                        </div>';
                                    })->columnSpan(1),

                                TextEntry::make('reste_a_payer_montant')
                                    ->label('Reste à Payer')
                                    ->html()
                                    ->getStateUsing(function ($record) {
                                        $reste = $record->reste_a_payer;
                                        if ($reste <= 0.001) {
                                            return '<div class="flex items-center gap-2">
                                                <span class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400">0.00 MAD</span>
                                                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">0 dette</span>
                                            </div>';
                                        }
                                        return '<div class="flex items-center gap-2">
                                            <span class="text-xl font-extrabold text-rose-600 dark:text-rose-400">' . number_format($reste, 2) . ' MAD</span>
                                            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300">Restant</span>
                                        </div>';
                                    })->columnSpan(1),
                            ]),
                    ]),

                Tabs::make('Details')
                    ->tabs([
                        Tab::make('Informations')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('telephone')
                                            ->label('TÉLÉPHONE')
                                            ->placeholder('N/A'),
                                        TextEntry::make('numero_tuteur')
                                            ->label('NUMÉRO DU TUTEUR (ولي الأمر)')
                                            ->placeholder('N/A'),
                                        TextEntry::make('classe.nomClasse')
                                            ->label('CLASSE')
                                            ->placeholder('Non assigné'),
                                        TextEntry::make('classe.anneescolaire.libelle')
                                            ->label('ANNÉE SCOLAIRE')
                                            ->placeholder('N/A'),
                                    ]),
                            ]),
                        Tab::make('Paiements')
                            ->icon('heroicon-m-credit-card')
                            ->badge(function ($record) {
                                $count = $record->paiements->count();
                                return $count > 0 ? (string)$count : null;
                            })
                            ->badgeColor(fn($record) => $record->reste_a_payer > 0 ? 'warning' : 'success')
                            ->schema([
                                Section::make('Synthèse Scolarité (Total : 15 000,00 MAD)')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('kpi_scolarite')
                                                    ->label('SCOLARITÉ DUE')
                                                    ->html()
                                                    ->getStateUsing(fn($record) => '
                                                        <div class="flex items-center gap-3 p-3 rounded-xl bg-blue-50/60 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/30">
                                                            <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400">
                                                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                                            </div>
                                                            <div>
                                                                <div class="text-lg font-black text-blue-800 dark:text-blue-300">' . number_format($record->frais_scolarite_total, 2) . ' MAD</div>
                                                                <div class="text-xs text-blue-600/80 dark:text-blue-400/80 font-medium">Frais annuels scolaires</div>
                                                            </div>
                                                        </div>
                                                    '),

                                                TextEntry::make('kpi_paye')
                                                    ->label('TOTAL DÉJÀ PAYÉ')
                                                    ->html()
                                                    ->getStateUsing(fn($record) => '
                                                        <div class="flex items-center gap-3 p-3 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30">
                                                            <div class="p-2 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400">
                                                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            </div>
                                                            <div>
                                                                <div class="text-lg font-black text-emerald-700 dark:text-emerald-300">' . number_format($record->total_paye, 2) . ' MAD</div>
                                                                <div class="text-xs text-emerald-600/80 dark:text-emerald-400/80 font-medium">' . $record->pourcentage_paye . '% encaissé</div>
                                                            </div>
                                                        </div>
                                                    '),

                                                TextEntry::make('kpi_reste')
                                                    ->label('RESTE À PAYER')
                                                    ->html()
                                                    ->getStateUsing(function($record) {
                                                        $reste = $record->reste_a_payer;
                                                        if ($reste <= 0.001) {
                                                            return '
                                                                <div class="flex items-center gap-3 p-3 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30">
                                                                    <div class="p-2 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400">
                                                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                                    </div>
                                                                    <div>
                                                                        <div class="text-lg font-black text-emerald-700 dark:text-emerald-300">0.00 MAD</div>
                                                                        <div class="text-xs text-emerald-600/80 dark:text-emerald-400/80 font-medium">Scolarité soldée (100%)</div>
                                                                    </div>
                                                                </div>
                                                            ';
                                                        }
                                                        return '
                                                            <div class="flex items-center gap-3 p-3 rounded-xl bg-rose-50/60 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/30">
                                                                <div class="p-2 rounded-lg bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400">
                                                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                                </div>
                                                                <div>
                                                                    <div class="text-lg font-black text-rose-700 dark:text-rose-300">' . number_format($reste, 2) . ' MAD</div>
                                                                    <div class="text-xs text-rose-600/80 dark:text-rose-400/80 font-semibold">Montant restant dû</div>
                                                                </div>
                                                            </div>
                                                        ';
                                                    }),
                                            ]),
                                    ]),

                                RepeatableEntry::make('paiements')
                                    ->label('Historique des versements effectués')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('montant')
                                                    ->label('Montant versé')
                                                    ->formatStateUsing(fn($state) => number_format((float)$state, 2) . ' MAD')
                                                    ->weight('bold'),
                                                TextEntry::make('dateEcheance')
                                                    ->label("Date d'échéance")
                                                    ->date('d M Y'),
                                                TextEntry::make('statut')
                                                    ->label('Statut')
                                                    ->badge()
                                                    ->color(fn(string $state): string => match (mb_strtolower(trim($state))) {
                                                        'payé', 'paye' => 'success',
                                                        'non payé', 'non paye' => 'danger',
                                                        default => 'warning',
                                                    }),
                                            ]),
                                    ])
                                    ->placeholder('Aucun versement enregistré.'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('idStudent')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('matricule')
                    ->label('Matricule')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['class' => 'font-mono text-gray-500']),
                Tables\Columns\TextColumn::make('nom_complet')
                    ->label('Nom complet')
                    ->getStateUsing(fn($record) => "{$record->nom} {$record->prenom}")
                    ->searchable(['nom', 'prenom'])
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('telephone')
                    ->label('Téléphone')
                    ->searchable()
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('classe.nomClasse')
                    ->label('Classe')
                    ->placeholder('Non assigné')
                    ->badge(),
                Tables\Columns\TextColumn::make('situation_financiere')
                    ->label('Situation Financière')
                    ->html()
                    ->getStateUsing(function ($record) {
                        $reste = $record->reste_a_payer;
                        $pourcentage = $record->pourcentage_paye;
                        if ($reste <= 0.001) {
                            return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                En règle (0 MAD)
                            </span>';
                        }
                        if ($record->total_paye > 0) {
                            return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                <svg class="w-3.5 h-3.5 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                Reste: ' . number_format($reste, 2) . ' MAD (' . $pourcentage . '%)
                            </span>';
                        }
                        return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                            <svg class="w-3.5 h-3.5 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                            Reste: ' . number_format($reste, 2) . ' MAD (0%)
                        </span>';
                    }),
                Tables\Columns\TextColumn::make('classe.anneescolaire.libelle')
                    ->label('Année Scolaire')
                    ->toggleable(),
            ])
            ->filters([

                Tables\Filters\SelectFilter::make('class_id')
                    ->label('Classe')
                    ->relationship('registres.classe', 'nomClasse')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Voir Profil')
                    ->icon('heroicon-o-eye')
                    ->modal()
                    ->modalHeading('Profil de l\'Étudiant')
                    ->infolist([
                        Infolists\Components\Section::make('Informations Personnelles')
                            ->schema([
                                Infolists\Components\Grid::make(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('matricule')
                                            ->badge()
                                            ->color('gray'),
                                        Infolists\Components\TextEntry::make('nom_complet')
                                            ->label('Nom')
                                            ->getStateUsing(fn($record) => "{$record->nom} {$record->prenom}")
                                            ->weight('bold'),
                                        Infolists\Components\TextEntry::make('classe.nomClasse')
                                            ->label('Classe')
                                            ->badge()
                                            ->color('success'),
                                        Infolists\Components\TextEntry::make('telephone')
                                            ->label('Téléphone'),
                                        Infolists\Components\TextEntry::make('classe.anneescolaire.libelle')
                                            ->label('Année Scolaire'),
                                    ]),
                            ]),
                    ]),
                Tables\Actions\EditAction::make()
                    ->label('Modifier')
                    ->icon('heroicon-o-pencil'),
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
            \App\Filament\Resources\StudentResource\RelationManagers\ParentsRelationManager::class,
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'view' => Pages\ViewStudent::route('/{record}'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
