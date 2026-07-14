<?php

namespace App\Filament\Resources\GradeResource\Pages;

use App\Filament\Resources\GradeResource;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Student;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;

class ViewStudentGrades extends Page
{
    protected static string $resource = GradeResource::class;
    protected static string $view = 'filament.resources.grade-resource.pages.view-student-grades';

    public Student $record;

    public function mount(Student $record): void
    {
        $this->record = $record;
    }

    public function getGradesProperty(): Collection
    {
        $query = Grade::where('student_id', $this->record->idStudent);

        $user = auth()->user();
        if ($user && in_array($user->role, ['professeur', 'prof'])) {
            $query->where('teacher_id', $user->id);
        }

        return $query->with(['course.classe'])->orderByDesc('exam_date')->get();
    }

    protected function getGradeForm(Form $form): Form
    {
        $student   = $this->record;
        $studentId = $student->idStudent;

        // Resolve the student's class ID via their latest registre (ordered by ID since table lacks timestamps)
        $classeId = $student->registres()->orderByDesc('id')->first()?->Cla_id;
        $user     = auth()->user();

        // ── Matières options from the professor's account ──────────────
        $matieres = $user?->matieres ?? [];

        // Ensure $matieres is an array (safeguard against DB casting issues)
        if (is_string($matieres)) {
            $decoded = json_decode($matieres, true);
            $matieres = is_array($decoded) ? $decoded : [$matieres];
        }

        $matiereOptions = empty($matieres)
            ? []
            : array_combine($matieres, $matieres); // ['Maths' => 'Maths']

        $defaultMatiere = !empty($matieres) ? $matieres[0] : null;

        return $form->schema([
            Forms\Components\Section::make('Informations de la Note')
                ->schema([

                    // ─── Étudiant (pré-rempli et verrouillé) ───────────────────
                    Forms\Components\Select::make('student_id')
                        ->label('Étudiant')
                        ->options(Student::all()->mapWithKeys(fn($s) => [
                            $s->idStudent => "{$s->matricule} - {$s->nom} {$s->prenom}"
                        ]))
                        ->default($studentId)
                        ->disabled()       // visually locked
                        ->dehydrated(true) // still saved on submit
                        ->required(),

                    // ─── Matière (Pré-remplie depuis le profil du prof) ────────
                    Forms\Components\Select::make('subject_name')
                        ->label('Matière')
                        ->options($matiereOptions)
                        ->default($defaultMatiere)
                        ->searchable()
                        ->required()
                        ->helperText(empty($matiereOptions)
                            ? "Vous n'avez aucune matière assignée dans votre profil."
                            : "Sélectionnez l'une des matières qui vous sont assignées."),

                    // ─── Enseignant (caché) ────────────────────────────────────
                    Forms\Components\Hidden::make('teacher_id')
                        ->default(fn() => auth()->id())
                        ->dehydrated(),

                    // ─── Note ──────────────────────────────────────────────────
                    Forms\Components\TextInput::make('note')
                        ->label('Note')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->maxValue(20)
                        ->step(0.25)
                        ->suffix('/ 20')
                        ->helperText('Saisissez une note entre 0 et 20'),

                    // ─── Type d'Évaluation ─────────────────────────────────────
                    Forms\Components\Select::make('type')
                        ->label("Type d'Évaluation")
                        ->options(array_combine(\App\Models\Grade::TYPES, \App\Models\Grade::TYPES))
                        ->required()
                        ->default('TP'),

                    // ─── Date de l'Examen ──────────────────────────────────────
                    Forms\Components\DatePicker::make('exam_date')
                        ->label("Date de l'Examen")
                        ->default(now())
                        ->maxDate(now())
                        ->displayFormat('d/m/Y'),

                    // ─── Semestre ──────────────────────────────────────────────
                    Forms\Components\Select::make('semester')
                        ->label('Semestre')
                        ->options([
                            'S1' => 'Semestre 1',
                            'S2' => 'Semestre 2',
                        ])
                        ->default('S1'),

                    // ─── Commentaire ───────────────────────────────────────────
                    Forms\Components\Textarea::make('comment')
                        ->label('Commentaire / Observation')
                        ->rows(3)
                        ->placeholder("Observations ou remarques sur la performance de l'étudiant...")
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public function createGradeAction(): Actions\Action
    {
        return Actions\CreateAction::make('createGrade')
            ->model(Grade::class)
            ->label('Ajouter une Note')
            ->icon('heroicon-o-plus')
            ->form(fn(Form $form) => $this->getGradeForm($form))
            ->mutateFormDataUsing(function (array $data): array {
                // Ensure course_id is explicitly null since we now use subject_name
                $data['course_id'] = null;
                $data['student_id'] = $this->record->idStudent;
                return $data;
            });
    }

    public function editGradeAction(): Actions\Action
    {
        return Actions\EditAction::make('editGrade')
            ->record(function (array $arguments) {
                return Grade::find($arguments['grade_id'] ?? null);
            })
            ->form(fn(Form $form) => $this->getGradeForm($form))
            ->mutateFormDataUsing(function (array $data): array {
                $data['course_id'] = null; // Always clear it out
                $data['student_id'] = $this->record->idStudent;
                return $data;
            });
    }

    public function deleteGradeAction(): Actions\Action
    {
        return Actions\DeleteAction::make('deleteGrade')
            ->record(function (array $arguments) {
                return Grade::find($arguments['grade_id'] ?? null);
            });
    }

    public function getTitle(): string
    {
        return "Notes de {$this->record->nom} {$this->record->prenom}";
    }
}
