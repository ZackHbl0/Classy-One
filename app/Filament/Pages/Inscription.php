<?php

namespace App\Filament\Pages;

use App\Models\Student;
use App\Models\Classe;
use App\Models\Registre;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class Inscription extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationLabel = 'Inscriptions';
    protected static string $view = 'filament.pages.inscription';
    protected static ?string $title = 'Nouvelle Inscription';

    public static function shouldRegisterNavigation(): bool
    {
        // Hide from professors - administrative task only
        return auth()->user()?->role !== 'professeur';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'matricule' => $this->generateMatricule(),
        ]);
    }

    public function generateMatricule(): string
    {
        $year = date('Y');
        $prefix = "ST{$year}";

        $latestStudent = Student::where('matricule', 'like', "{$prefix}%")
            ->orderBy('matricule', 'desc')
            ->first();

        if ($latestStudent) {
            $lastNumber = (int) substr($latestStudent->matricule, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "{$prefix}{$nextNumber}";
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations de l\'Étudiant')
                    ->description('Remplissez les informations personnelles de l\'étudiant.')
                    ->schema([
                        TextInput::make('matricule')
                            ->label('Matricule')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique('student', 'matricule', ignoreRecord: true),
                        TextInput::make('nom')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('prenom')
                            ->label('Prénom')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('telephone')
                            ->label('Numéro de Téléphone')
                            ->tel()
                            ->maxLength(30),
                        TextInput::make('password')
                            ->label('Mot de passe (Mobile App)')
                            ->password()
                            ->required()
                            ->dehydrated(fn($state) => filled($state))
                            ->helperText('Ce mot de passe sera utilisé par l\'étudiant sur l\'application mobile.'),
                    ])
                    ->columns(2),

                Section::make('Affectation à une Classe')
                    ->description('Sélectionnez la classe dans laquelle l\'étudiant sera inscrit.')
                    ->schema([
                        Select::make('classe_id')
                            ->label('Classe')
                            ->options(function () {
                                return Classe::all()->pluck('nomClasse', 'id')
                                    ->map(fn($label) => (string) ($label ?? 'Classe sans nom'));
                            })
                            ->searchable()
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('register')
                ->label('Confirmer l\'inscription')
                ->submit('register')
                ->color('primary'),
        ];
    }

    public function register(): void
    {
        $data = $this->form->getState();

        try {
            DB::beginTransaction();

            $student = Student::create([
                'matricule' => $data['matricule'],
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'telephone' => $data['telephone'],
                'password' => Hash::make($data['password']),
            ]);

            Registre::create([
                'idStudent' => $student->idStudent,
                'Cla_id' => $data['classe_id'],
            ]);

            DB::commit();

            Notification::make()
                ->title('Inscription réussie')
                ->body("L'étudiant {$student->nom} {$student->prenom} a été inscrit avec succès (Matricule: {$student->matricule}).")
                ->success()
                ->send();

            $this->form->fill([
                'matricule' => $this->generateMatricule(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Erreur lors de l\'inscription')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
