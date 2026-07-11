<?php

namespace App\Filament\Resources\AbsenceResource\Pages;

use App\Filament\Resources\AbsenceResource;
use App\Models\Absence;
use App\Models\Student;
use App\Models\Classe;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;

class CreateAbsence extends CreateRecord
{
    protected static string $resource = AbsenceResource::class;
    
    protected static ?string $title = 'Saisir des absences';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date')
                    ->label('Date')
                    ->default(now())
                    ->required(),
                TextInput::make('seance')
                    ->label('Séance (ex: 8h-10h)')
                    ->required(),
                TextInput::make('matiere')
                    ->label('Matière')
                    ->required(),
                Select::make('prof_id')
                    ->label('Professeur')
                    ->options(User::whereIn('role', ['professeur', 'prof'])->pluck('name', 'id'))
                    ->required(),
                Select::make('classe_id')
                    ->label('Classe')
                    ->options(Classe::pluck('nomClasse', 'id'))
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        if ($state) {
                            $students = Student::whereHas('classe', function ($q) use ($state) {
                                $q->where('classe.id', $state);
                            })->get();
                            
                            $absencesData = [];
                            foreach ($students as $student) {
                                $uuid = (string) \Illuminate\Support\Str::uuid();
                                $absencesData[$uuid] = [
                                    'student_id' => $student->idStudent,
                                    'nom_complet' => $student->nom . ' ' . $student->prenom,
                                    'is_absent' => false,
                                    'is_justified' => false,
                                    'justification_reason' => null,
                                ];
                            }
                            $set('students_list', $absencesData);
                        } else {
                            $set('students_list', []);
                        }
                    })
                    ->required(),

                Repeater::make('students_list')
                    ->label('Liste des étudiants')
                    ->default([])
                    ->schema([
                        TextInput::make('nom_complet')
                            ->label('Étudiant')
                            ->disabled()
                            ->columnSpan(2),
                        Hidden::make('student_id'),
                        Toggle::make('is_absent')
                            ->label('Absent ?')
                            ->live(),
                        Toggle::make('is_justified')
                            ->label('Justifiée ?')
                            ->visible(fn (Get $get) => $get('is_absent') === true)
                            ->live(),
                        Textarea::make('justification_reason')
                            ->label('Motif')
                            ->visible(fn (Get $get) => $get('is_absent') === true && $get('is_justified') === true)
                            ->columnSpan(2),
                    ])
                    ->columns(4)
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->columnSpan('full'),
            ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $createdAbsences = [];
        
        foreach ($data['students_list'] as $studentData) {
            if ($studentData['is_absent']) {
                $createdAbsences[] = Absence::create([
                    'student_id' => $studentData['student_id'],
                    'classe_id' => $data['classe_id'],
                    'prof_id' => $data['prof_id'],
                    'matiere' => $data['matiere'],
                    'date' => $data['date'],
                    'seance' => $data['seance'],
                    'is_justified' => $studentData['is_justified'] ?? false,
                    'justification_reason' => $studentData['justification_reason'] ?? null,
                ]);
            }
        }

        if (count($createdAbsences) > 0) {
            return $createdAbsences[0];
        }

        return new Absence();
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
