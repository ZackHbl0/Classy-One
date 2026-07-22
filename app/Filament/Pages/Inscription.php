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
                    ->icon('heroicon-o-user')
                    ->extraAttributes(['class' => 'custom-section-student'])
                    ->schema([
                        TextInput::make('matricule')
                            ->label('Matricule')
                            ->prefixIcon('heroicon-o-identification')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique('student', 'matricule', ignoreRecord: true),
                        TextInput::make('nom')
                            ->label('Nom')
                            ->placeholder('Entrez le nom')
                            ->prefixIcon('heroicon-o-user')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('prenom')
                            ->label('Prénom')
                            ->placeholder('Entrez le prénom')
                            ->prefixIcon('heroicon-o-user')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('telephone')
                            ->label('Numéro de Téléphone')
                            ->placeholder('Entrez le numéro de téléphone')
                            ->prefixIcon('heroicon-o-phone')
                            ->tel()
                            ->maxLength(30),
                        TextInput::make('numero_tuteur')
                            ->label('Numéro du tuteur')
                            ->placeholder('Entrez le numéro du tuteur')
                            ->prefixIcon('heroicon-o-phone')
                            ->tel()
                            ->maxLength(30),
                        TextInput::make('password')
                            ->label('Mot de passe (Mobile App)')
                            ->placeholder('Entrez le mot de passe')
                            ->prefixIcon('heroicon-o-lock-closed')
                            ->password()
                            ->revealable()
                            ->required()
                            ->dehydrated(fn($state) => filled($state))
                            ->helperText('Ce mot de passe sera utilisé par l\'étudiant sur l\'application mobile.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Affectation à une Classe')
                    ->description('Sélectionnez la classe dans laquelle l\'étudiant sera inscrit.')
                    ->icon('heroicon-o-academic-cap')
                    ->extraAttributes(['class' => 'custom-section-class'])
                    ->schema([
                        Select::make('classe_id')
                            ->label('Classe')
                            ->placeholder('Sélectionnez une classe')
                            ->prefixIcon('heroicon-o-users')
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

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return new \Illuminate\Support\HtmlString('
            <div class="mb-2">
                <h1 class="text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">Nouvelle Inscription</h1>
                <div class="h-1 w-20 bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full mt-3"></div>
            </div>
        ');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('register')
                ->label('Confirmer l\'inscription')
                ->submit('register')
                ->icon('heroicon-o-user-plus')
                ->extraAttributes([
                    'class' => 'custom-submit-btn'
                ]),
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
                'numero_tuteur' => $data['numero_tuteur'] ?? null,
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
