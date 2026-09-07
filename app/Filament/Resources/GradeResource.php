<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeResource\Pages;
use App\Filament\Resources\StudentResource;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Course;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Colors\Color;

class GradeResource extends Resource
{
    protected static ?string $model = Grade::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Notes';

    protected static ?string $modelLabel = 'Note';

    protected static ?string $pluralModelLabel = 'Notes';

    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        $role = auth()->user()?->role;
        return in_array($role, ['professeur', 'prof']);
    }

    public static function canViewAny(): bool
    {
        $role = auth()->user()?->role;
        return in_array($role, ['professeur', 'prof']);
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if (in_array($user->role, ['admin', 'secretaire'])) return true;

        // Professors can only edit grades they assigned
        return in_array($user->role, ['professeur', 'prof']) && (int) $record->teacher_id === (int) $user->id;
    }

    public static function canDelete($record): bool
    {
        return static::canEdit($record);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // Professors see grades for their courses OR grades they personally entered (teacher_id)
        if ($user && in_array($user->role, ['professeur', 'prof'])) {
            $courseIds = Course::where('professor_id', $user->id)->pluck('id')->toArray();
            $query->where(function ($q) use ($courseIds, $user) {
                $q->whereIn('course_id', $courseIds)
                  ->orWhere('teacher_id', $user->id);
            });
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isProfessor = $user?->role === 'professeur';

        return $form
            ->schema([
                Forms\Components\Section::make('Informations de la Note')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('Étudiant')
                            ->options(function () use ($user, $isProfessor) {
                                if ($isProfessor) {
                                    // Professors: students from their classes
                                    $classIds = Course::where('professor_id', $user->id)
                                        ->pluck('classe_id')
                                        ->push($user->classe_id)
                                        ->filter()
                                        ->unique()
                                        ->toArray();

                                    return Student::whereHas('registres', function ($q) use ($classIds) {
                                        $q->whereIn('Cla_id', $classIds);
                                    })
                                        ->get()
                                        ->mapWithKeys(fn($s) => [
                                            $s->idStudent => "{$s->matricule} - {$s->nom} {$s->prenom}"
                                        ]);
                                }

                                // Admin/Secretaire: all students
                                return Student::all()->mapWithKeys(fn($s) => [
                                    $s->idStudent => "{$s->matricule} - {$s->nom} {$s->prenom}"
                                ]);
                            })
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->preload(),

                        Forms\Components\Select::make('course_id')
                            ->label('Matière / Cours')
                            ->options(function () use ($user, $isProfessor) {
                                if ($isProfessor) {
                                    // Professors: only their courses
                                    return Course::where('professor_id', $user->id)
                                        ->with('classe')
                                        ->get()
                                        ->mapWithKeys(fn($c) => [
                                            $c->id => "{$c->title} ({$c->classe->nomClasse})"
                                        ]);
                                }

                                // Admin/Secretaire: all courses
                                return Course::with('classe')
                                    ->get()
                                    ->mapWithKeys(fn($c) => [
                                        $c->id => "{$c->title} ({$c->classe->nomClasse})"
                                    ]);
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Auto-fill coefficient from the selected course
                                if ($state) {
                                    $course = Course::find($state);
                                    if ($course && $course->coefficient) {
                                        $set('coefficient', (float) $course->coefficient);
                                    }
                                }
                            })
                            ->preload(),

                        Forms\Components\TextInput::make('coefficient')
                            ->label('Coefficient')
                            ->numeric()
                            ->default(1)
                            ->minValue(0.5)
                            ->maxValue(10)
                            ->step(0.5)
                            ->required()
                            ->helperText('Coefficient de la matière (auto-rempli depuis le cours)'),

                        Forms\Components\Hidden::make('teacher_id')
                            ->default(fn() => auth()->id())
                            ->dehydrated(),

                        Forms\Components\TextInput::make('note')
                            ->label('Note')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(20)
                            ->step(0.25)
                            ->suffix('/ 20')
                            ->helperText('Saisissez une note entre 0 et 20'),

                        Forms\Components\Select::make('type')
                            ->label('Type d\'Évaluation')
                            ->options([
                                'Contrôle 1' => 'Contrôle 1',
                                'Contrôle 2' => 'Contrôle 2',
                                'Examen Final' => 'Examen Final',
                                'Examen Blanc' => 'Examen Blanc',
                                'Devoir' => 'Devoir',
                                'TP' => 'TP (Travaux Pratiques)',
                                'Projet' => 'Projet',
                            ])
                            ->required()
                            ->default('Contrôle 1'),

                        Forms\Components\DatePicker::make('exam_date')
                            ->label('Date de l\'Examen')
                            ->default(now())
                            ->maxDate(now())
                            ->displayFormat('d/m/Y'),

                        Forms\Components\Select::make('semester')
                            ->label('Semestre')
                            ->options([
                                'S1' => 'Semestre 1',
                                'S2' => 'Semestre 2',
                            ])
                            ->default('S1'),

                        Forms\Components\Textarea::make('comment')
                            ->label('Commentaire / Observation')
                            ->rows(3)
                            ->placeholder('Observations ou remarques sur la performance de l\'étudiant...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Détails de la Note')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('student.matricule')
                                    ->label('Matricule')
                                    ->badge()
                                    ->color('gray'),

                                Infolists\Components\TextEntry::make('student')
                                    ->label('Étudiant')
                                    ->getStateUsing(fn($record) => "{$record->student->nom} {$record->student->prenom}")
                                    ->weight('bold'),

                                Infolists\Components\TextEntry::make('student.classe.nomClasse')
                                    ->label('Classe')
                                    ->badge()
                                    ->color('success'),
                            ]),

                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('note')
                                    ->label('Note')
                                    ->badge()
                                    ->size('lg')
                                    ->suffix(' / 20')
                                    ->color(fn($record) => $record->color),

                                Infolists\Components\TextEntry::make('status')
                                    ->label('Appréciation')
                                    ->badge()
                                    ->color(fn($record) => $record->color),

                                Infolists\Components\TextEntry::make('type')
                                    ->label('Type')
                                    ->badge()
                                    ->color('info'),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('course.title')
                                    ->label('Matière'),

                                Infolists\Components\TextEntry::make('teacher.name')
                                    ->label('Professeur'),

                                Infolists\Components\TextEntry::make('exam_date')
                                    ->label('Date de l\'Examen')
                                    ->date('d/m/Y')
                                    ->placeholder('Non spécifiée'),

                                Infolists\Components\TextEntry::make('semester')
                                    ->label('Semestre')
                                    ->badge()
                                    ->placeholder('Non spécifié'),
                            ]),

                        Infolists\Components\TextEntry::make('comment')
                            ->label('Commentaire')
                            ->placeholder('Aucun commentaire')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.matricule')
                    ->label('Matricule')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->copyable()
                    ->copyMessage('Matricule copié!'),

                Tables\Columns\TextColumn::make('student.nom')
                    ->label('Étudiant')
                    ->getStateUsing(fn($record) => "{$record->student->nom} {$record->student->prenom}")
                    ->searchable(['nom', 'prenom'])
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('course.title')
                    ->label('Matière')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->visible(fn() => auth()->user()?->role !== 'admin'),

                Tables\Columns\TextColumn::make('note')
                    ->label('Note')
                    ->badge()
                    ->suffix(' / 20')
                    ->sortable()
                    ->color(fn($record) => $record->color)
                    ->weight('bold')
                    ->alignCenter()
                    ->visible(fn() => auth()->user()?->role !== 'admin'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->visible(fn() => auth()->user()?->role !== 'admin'),

                Tables\Columns\TextColumn::make('exam_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable()
                    ->visible(fn() => auth()->user()?->role !== 'admin'),

                Tables\Columns\TextColumn::make('semester')
                    ->label('Semestre')
                    ->badge()
                    ->toggleable()
                    ->visible(fn() => auth()->user()?->role !== 'admin'),

                Tables\Columns\TextColumn::make('student.classe.nomClasse')
                    ->label('Classe')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Professeur')
                    ->toggleable()
                    ->visible(fn() => !in_array(auth()->user()?->role, ['professeur', 'prof', 'admin'])),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('student_id')
                    ->label('Étudiant')
                    ->relationship('student', 'matricule')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('course_id')
                    ->label('Matière')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'Contrôle 1' => 'Contrôle 1',
                        'Contrôle 2' => 'Contrôle 2',
                        'Examen Final' => 'Examen Final',
                        'Examen Blanc' => 'Examen Blanc',
                        'Devoir' => 'Devoir',
                        'TP' => 'TP',
                        'Projet' => 'Projet',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('semester')
                    ->label('Semestre')
                    ->options([
                        'S1' => 'Semestre 1',
                        'S2' => 'Semestre 2',
                    ])
                    ->multiple(),

                Tables\Filters\Filter::make('passing')
                    ->label('Notes >= 10')
                    ->query(fn($query) => $query->where('note', '>=', 10)),

                Tables\Filters\Filter::make('failing')
                    ->label('Notes < 10')
                    ->query(fn($query) => $query->where('note', '<', 10)),
            ])
            ->actions([
                Tables\Actions\Action::make('voir_bulletins')
                    ->label('Voir Bulletins')
                    ->icon('heroicon-o-document-text')
                    ->modal()
                    ->modalHeading(fn($record) => "Bulletins de {$record->student->nom} {$record->student->prenom}")
                    ->modalWidth('5xl')
                    ->infolist(function ($record) {
                        $svc = app(\App\Services\GradeCalculationService::class);
                        $student = $record->student;
                        $classeId = optional($student->registres->first())->Cla_id
                            ?? optional($student->classe)->id
                            ?? null;

                        $buildSemesterSchema = function (string $semester) use ($svc, $student, $classeId) {
                            $subjects    = $svc->getSubjectBreakdown($student->idStudent, $semester);
                            $weightedAvg = $svc->calculateWeightedAverage($student->idStudent, $semester);
                            $totalCoeff  = array_sum(array_column($subjects, 'coefficient'));
                            $mention     = $svc->getMention($weightedAvg);
                            $mentionColor = $svc->getMentionColor($weightedAvg);
                            $rank        = $classeId ? $svc->calculateRankInClass($student->idStudent, $classeId, $semester) : null;

                            return [
                                Infolists\Components\RepeatableEntry::make("grades_{$semester}")
                                    ->label('Matières')
                                    ->getStateUsing(fn() => $subjects)
                                    ->schema([
                                        Infolists\Components\Grid::make(4)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('subject_name')
                                                    ->label('Matière')
                                                    ->weight('bold'),
                                                Infolists\Components\TextEntry::make('coefficient')
                                                    ->label('Coeff.')
                                                    ->badge()
                                                    ->color('warning')
                                                    ->alignCenter(),
                                                Infolists\Components\TextEntry::make('subject_average')
                                                    ->label('Moyenne')
                                                    ->badge()
                                                    ->suffix(' / 20')
                                                    ->color(fn($record) => $record['is_passing'] ? 'success' : 'danger')
                                                    ->alignCenter(),
                                                Infolists\Components\TextEntry::make('is_passing')
                                                    ->label('Statut')
                                                    ->badge()
                                                    ->getStateUsing(fn($record) => $record['is_passing'] ? 'Admis' : 'Non admis')
                                                    ->color(fn($record) => $record['is_passing'] ? 'success' : 'danger')
                                                    ->alignCenter(),
                                            ]),
                                    ])
                                    ->placeholder('Aucune note enregistrée pour ce semestre.'),

                                Infolists\Components\Grid::make(4)
                                    ->schema([
                                        Infolists\Components\TextEntry::make("weighted_avg_{$semester}")
                                            ->label('Moyenne Générale Pondérée')
                                            ->getStateUsing(fn() => number_format($weightedAvg, 2) . ' / 20')
                                            ->badge()
                                            ->size('lg')
                                            ->color($mentionColor)
                                            ->weight('bold'),

                                        Infolists\Components\TextEntry::make("total_coeff_{$semester}")
                                            ->label('Total Coefficients')
                                            ->getStateUsing(fn() => $totalCoeff)
                                            ->badge()
                                            ->color('warning'),

                                        Infolists\Components\TextEntry::make("mention_{$semester}")
                                            ->label('Mention')
                                            ->getStateUsing(fn() => $mention)
                                            ->badge()
                                            ->color($mentionColor),

                                        Infolists\Components\TextEntry::make("rank_{$semester}")
                                            ->label('Rang')
                                            ->getStateUsing(fn() => $rank ? "#{$rank}" : 'N/A')
                                            ->badge()
                                            ->color('info'),
                                    ]),
                            ];
                        };

                        return [
                            Infolists\Components\Tabs::make('Bulletins')
                                ->tabs([
                                    Infolists\Components\Tabs\Tab::make('Semestre 1 (S1)')
                                        ->icon('heroicon-o-academic-cap')
                                        ->schema($buildSemesterSchema('S1')),
                                    Infolists\Components\Tabs\Tab::make('Semestre 2 (S2)')
                                        ->icon('heroicon-o-academic-cap')
                                        ->schema($buildSemesterSchema('S2')),
                                ])
                                ->columnSpanFull(),
                        ];
                    })
                    ->visible(fn() => auth()->user()?->role === 'admin'),
                Tables\Actions\ViewAction::make()
                    ->modalHeading('Détails de la Note')
                    ->visible(fn() => auth()->user()?->role !== 'admin'),
                Tables\Actions\EditAction::make()
                    ->visible(fn() => auth()->user()?->role !== 'admin'),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => auth()->user()?->role !== 'admin'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()?->role !== 'admin'),
                ]),
            ])
            ->defaultSort('exam_date', 'desc')
            ->emptyStateHeading('Aucune note enregistrée')
            ->emptyStateDescription('Commencez par ajouter des notes pour les étudiants.')
            ->emptyStateIcon('heroicon-o-academic-cap');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGrades::route('/'),
            'create' => Pages\CreateGrade::route('/create'),
            'edit' => Pages\EditGrade::route('/{record}/edit'),
            'student' => Pages\ViewStudentGrades::route('/student/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();

        if ($user && in_array($user->role, ['professeur', 'prof'])) {
            $courseIds = Course::where('professor_id', $user->id)->pluck('id')->toArray();
            $count = Grade::where(function ($q) use ($courseIds, $user) {
                $q->where('teacher_id', $user->id);
                if (!empty($courseIds)) {
                    $q->orWhereIn('course_id', $courseIds);
                }
            })->count();
            return $count > 0 ? (string) $count : null;
        }

        $count = Grade::count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
