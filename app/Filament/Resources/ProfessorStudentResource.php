<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfessorStudentResource\Pages;
use App\Models\Student;
use App\Models\Course;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;

class ProfessorStudentResource extends Resource
{
    protected static ?string $model = Student::class;

    public static function shouldRegisterNavigation(): bool
    {
        // Show only to professors
        return auth()->user()?->role === 'professeur';
    }

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Mes Étudiants';

    protected static ?string $modelLabel = 'Étudiant';

    protected static ?string $pluralModelLabel = 'Étudiants';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        // Professors cannot create students - administrative task
        return false;
    }

    public static function canEdit($record): bool
    {
        // Professors can only view, not edit
        return false;
    }

    public static function canDelete($record): bool
    {
        // Professors cannot delete students
        return false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'professeur';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Filter students to show only those in classes taught by this professor
        $user = auth()->user();

        if ($user && $user->role === 'professeur') {
            // Get class IDs from courses taught by this professor
            $classIds = Course::where('professor_id', $user->id)
                ->pluck('classe_id')
                ->push($user->classe_id)
                ->filter()
                ->unique()
                ->toArray();

            // Filter students enrolled in those classes
            $query->whereHas('registres', function ($q) use ($classIds) {
                $q->whereIn('Cla_id', $classIds);
            });
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        // Professors don't need the form - view only
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Profil de l\'Étudiant')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\Group::make([
                                        Infolists\Components\TextEntry::make('nom_complet')
                                            ->label('Nom Complet')
                                            ->getStateUsing(fn($record) => "{$record->nom} {$record->prenom}")
                                            ->weight('bold')
                                            ->size('lg'),
                                        Infolists\Components\TextEntry::make('matricule')
                                            ->label('Matricule')
                                            ->badge()
                                            ->color('info'),
                                    ]),
                                ]),
                        ]),
                    ]),

                Infolists\Components\Section::make('Informations')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('telephone')
                                    ->label('Téléphone')
                                    ->icon('heroicon-m-phone')
                                    ->placeholder('N/A'),
                                Infolists\Components\TextEntry::make('classe.nomClasse')
                                    ->label('Classe')
                                    ->icon('heroicon-m-academic-cap')
                                    ->badge()
                                    ->color('success')
                                    ->placeholder('Non assigné'),
                                Infolists\Components\TextEntry::make('classe.anneescolaire.libelle')
                                    ->label('Année Scolaire')
                                    ->icon('heroicon-m-calendar')
                                    ->placeholder('N/A'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('matricule')
                    ->label('Matricule')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->copyable()
                    ->copyMessage('Matricule copié!')
                    ->extraAttributes(['class' => 'font-mono']),
                Tables\Columns\TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('prenom')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('classe.nomClasse')
                    ->label('Classe')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->placeholder('Non assigné'),
                Tables\Columns\TextColumn::make('telephone')
                    ->label('Téléphone')
                    ->icon('heroicon-m-phone')
                    ->placeholder('N/A')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('classe')
                    ->label('Filtrer par Classe')
                    ->relationship('registres.classe', 'nomClasse')
                    ->searchable()
                    ->preload()
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Voir Profil')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn($record) => "Profil de {$record->nom} {$record->prenom}")
                    ->modalWidth('3xl'),
            ])
            ->bulkActions([
                // No bulk actions for professors
            ])
            ->emptyStateHeading('Aucun étudiant trouvé')
            ->emptyStateDescription('Vous n\'avez pas d\'étudiants dans les classes que vous enseignez.')
            ->emptyStateIcon('heroicon-o-user-group')
            ->defaultSort('nom', 'asc')
            ->striped()
            ->poll('30s'); // Auto-refresh every 30 seconds
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProfessorStudents::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        // Show count of students in professor's classes
        $user = auth()->user();

        if ($user && $user->role === 'professeur') {
            $classIds = Course::where('professor_id', $user->id)
                ->pluck('classe_id')
                ->push($user->classe_id)
                ->filter()
                ->unique()
                ->toArray();

            $count = Student::whereHas('registres', function ($q) use ($classIds) {
                $q->whereIn('Cla_id', $classIds);
            })->count();

            return $count > 0 ? (string) $count : null;
        }

        return null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
