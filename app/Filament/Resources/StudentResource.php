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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    public static function shouldRegisterNavigation(): bool
    {
        // Hide from professors - show only to admin/secretaire
        return auth()->user()?->role !== 'prof';
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
                        Forms\Components\TextInput::make('password')
                            ->label('Mot de passe (Mobile App)')
                            ->password()
                            ->dehydrated(fn(?string $state) => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create'),
                    ])->columns(2)
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\Group::make([
                                        Infolists\Components\TextEntry::make('nom_complet')
                                            ->label('')
                                            ->getStateUsing(fn($record) => "{$record->nom} {$record->prenom}")
                                            ->weight('bold')
                                            ->size('lg'),
                                        Infolists\Components\TextEntry::make('matricule')
                                            ->label('')
                                            ->badge()
                                            ->color('info'),
                                    ]),
                                ]),
                        ]),
                    ]),

                Infolists\Components\Tabs::make('Details')
                    ->tabs([
                        Infolists\Components\Tabs\Tab::make('Informations')
                            ->icon('heroicon-m-information-circle')
                            ->visible(fn() => auth()->user()?->role !== 'admin')
                            ->schema([
                                Infolists\Components\Grid::make(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('telephone')
                                            ->label('TÉLÉPHONE')
                                            ->placeholder('N/A'),
                                        Infolists\Components\TextEntry::make('classe.nomClasse')
                                            ->label('CLASSE')
                                            ->placeholder('Non assigné'),
                                        Infolists\Components\TextEntry::make('classe.anneescolaire.libelle')
                                            ->label('ANNÉE SCOLAIRE')
                                            ->placeholder('N/A'),
                                    ]),
                            ]),
                        Infolists\Components\Tabs\Tab::make('Paiements')
                            ->icon('heroicon-m-credit-card')
                            ->visible(fn() => auth()->user()?->role !== 'admin')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('paiements')
                                    ->label('')
                                    ->schema([
                                        Infolists\Components\Grid::make(3)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('montant')
                                                    ->label('Montant')
                                                    ->money('XAF'),
                                                Infolists\Components\TextEntry::make('dateEcheance')
                                                    ->label('Échéance')
                                                    ->date(),
                                                Infolists\Components\TextEntry::make('statut')
                                                    ->label('Statut')
                                                    ->badge()
                                                    ->color(fn(string $state): string => match ($state) {
                                                        'Payé' => 'success',
                                                        'En attente' => 'warning',
                                                        default => 'gray',
                                                    }),
                                            ]),
                                    ])
                                    ->placeholder('Aucun historique de paiement.'),
                            ]),
                        Infolists\Components\Tabs\Tab::make('Semestre 1 (S1)')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('grades_s1')
                                    ->label('')
                                    ->getStateUsing(function ($record) {
                                        return $record->grades()
                                            ->where('semester', 'S1')
                                            ->selectRaw('course_id, AVG(note) as average_note')
                                            ->groupBy('course_id')
                                            ->get()
                                            ->map(fn($g) => [
                                                'matiere' => $g->course?->title ?? 'Matière Inconnue',
                                                'moyenne' => number_format($g->average_note, 2) . ' / 20',
                                                'is_passing' => $g->average_note >= 10,
                                            ]);
                                    })
                                    ->schema([
                                        Infolists\Components\Grid::make(2)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('matiere')
                                                    ->label('Matière')
                                                    ->weight('bold'),
                                                Infolists\Components\TextEntry::make('moyenne')
                                                    ->label('Moyenne Générale')
                                                    ->badge()
                                                    ->color(fn($record) => $record['is_passing'] ? 'success' : 'danger'),
                                            ]),
                                    ])
                                    ->placeholder('Aucune note enregistrée pour ce semestre.'),
                            ]),
                        Infolists\Components\Tabs\Tab::make('Semestre 2 (S2)')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('grades_s2')
                                    ->label('')
                                    ->getStateUsing(function ($record) {
                                        return $record->grades()
                                            ->where('semester', 'S2')
                                            ->selectRaw('course_id, AVG(note) as average_note')
                                            ->groupBy('course_id')
                                            ->get()
                                            ->map(fn($g) => [
                                                'matiere' => $g->course?->title ?? 'Matière Inconnue',
                                                'moyenne' => number_format($g->average_note, 2) . ' / 20',
                                                'is_passing' => $g->average_note >= 10,
                                            ]);
                                    })
                                    ->schema([
                                        Infolists\Components\Grid::make(2)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('matiere')
                                                    ->label('Matière')
                                                    ->weight('bold'),
                                                Infolists\Components\TextEntry::make('moyenne')
                                                    ->label('Moyenne Générale')
                                                    ->badge()
                                                    ->color(fn($record) => $record['is_passing'] ? 'success' : 'danger'),
                                            ]),
                                    ])
                                    ->placeholder('Aucune note enregistrée pour ce semestre.'),
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
                    ->icon('heroicon-o-eye'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(fn (Student $record): string => StudentResource::getUrl('view', ['record' => $record]));
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
            'index' => Pages\ListStudents::route('/'),
            'view' => Pages\ViewStudent::route('/{record}'),
        ];
    }
}
