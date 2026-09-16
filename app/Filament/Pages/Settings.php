<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Models\Anneescolaire;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use App\Models\AuditLog;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Paramètres';

    protected static ?string $title = 'Paramètres de l\'établissement';

    protected static string $view = 'filament.pages.settings';

    protected static ?int $navigationSort = 101;

    public ?array $data = [];

    /**
     * Accessible to Admin only
     */
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user && $user->isAdmin();
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        Setting::initializeDefaults();

        $settings = Setting::all()->pluck('value', 'key')->toArray();

        $this->form->fill([
            'school_name' => $settings['school_name'] ?? 'ClassyOne International Academy',
            'school_slogan' => $settings['school_slogan'] ?? 'Excellence, Innovation & Épanouissement Scolaire',
            'academic_year' => $settings['academic_year'] ?? '2025 - 2026',
            'currency' => $settings['currency'] ?? 'MAD (DH)',
            'school_logo' => $settings['school_logo'] ?? null,
            'contact_email' => $settings['contact_email'] ?? 'contact@classyone.edu',
            'contact_phone' => $settings['contact_phone'] ?? '+212 5 22 33 44 55',
            'address' => $settings['address'] ?? '124 Boulevard d\'Anfa',
            'city' => $settings['city'] ?? 'Casablanca, Maroc',
            'website' => $settings['website'] ?? 'https://classyone.edu',
            'timezone' => $settings['timezone'] ?? 'Africa/Casablanca (GMT+1)',
            'auto_absence_alert' => (bool) ($settings['auto_absence_alert'] ?? true),
            'auto_grade_notification' => (bool) ($settings['auto_grade_notification'] ?? true),
            'maintenance_mode' => (bool) ($settings['maintenance_mode'] ?? false),
        ]);
    }

    public function form(Form $form): Form
    {
        // Get academic years from DB
        $academicYearOptions = [];
        try {
            $years = Anneescolaire::pluck('libelle', 'libelle')->toArray();
            if (!empty($years)) {
                $academicYearOptions = $years;
            }
        } catch (\Throwable $e) {}

        if (!isset($academicYearOptions['2025 - 2026'])) {
            $academicYearOptions['2025 - 2026'] = '2025 - 2026 (Actuelle)';
        }
        if (!isset($academicYearOptions['2024 - 2025'])) {
            $academicYearOptions['2024 - 2025'] = '2024 - 2025';
        }

        return $form
            ->schema([
                Section::make('Identité & Configuration de l\'Établissement')
                    ->description('Renseignez les détails d\'identification de votre école.')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('school_name')
                                ->label('Nom de l\'établissement')
                                ->required()
                                ->placeholder('Ex: ClassyOne Academy')
                                ->prefixIcon('heroicon-m-building-library'),

                            TextInput::make('school_slogan')
                                ->label('Devise ou Slogan')
                                ->placeholder('Ex: L\'excellence au service de la réussite')
                                ->prefixIcon('heroicon-m-sparkles'),

                            Select::make('academic_year')
                                ->label('Année Scolaire Active')
                                ->options($academicYearOptions)
                                ->required()
                                ->prefixIcon('heroicon-m-calendar'),

                            Select::make('currency')
                                ->label('Devise Monétaire Principale')
                                ->options([
                                    'MAD (DH)' => 'MAD - Dirham Marocain (DH)',
                                    'EUR (€)' => 'EUR - Euro (€)',
                                    'USD ($)' => 'USD - Dollar Américain ($)',
                                    'XOF (CFA)' => 'XOF - Franc CFA (CFA)',
                                    'CAD ($)' => 'CAD - Dollar Canadien ($)',
                                ])
                                ->required()
                                ->prefixIcon('heroicon-m-banknotes'),
                        ]),
                    ]),

                Section::make('Identité Visuelle & Logo')
                    ->description('Logo utilisé sur les bulletins de notes, reçus et l\'application mobile.')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        FileUpload::make('school_logo')
                            ->label('Logo de l\'école')
                            ->image()
                            ->directory('school')
                            ->visibility('public')
                            ->imageResizeMode('contain')
                            ->imageCropAspectRatio('1:1')
                            ->maxSize(3072)
                            ->helperText('Format recommandé : PNG ou SVG transparent, dimensions minimum 400x400px.'),
                    ]),

                Section::make('Coordonnées & Informations Administratives')
                    ->description('Coordonnées officielles de contact affichées aux parents et élèves.')
                    ->icon('heroicon-o-envelope')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('contact_email')
                                ->label('Email administratif')
                                ->email()
                                ->required()
                                ->prefixIcon('heroicon-m-envelope'),

                            TextInput::make('contact_phone')
                                ->label('Téléphone de contact')
                                ->tel()
                                ->prefixIcon('heroicon-m-phone'),

                            TextInput::make('address')
                                ->label('Adresse physique')
                                ->prefixIcon('heroicon-m-map-pin'),

                            TextInput::make('city')
                                ->label('Ville & Pays')
                                ->prefixIcon('heroicon-m-globe-alt'),

                            TextInput::make('website')
                                ->label('Site web officiel')
                                ->url()
                                ->prefixIcon('heroicon-m-link')
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Système & Alertes Automatiques')
                    ->description('Configurez le comportement du système et les déclencheurs automatiques.')
                    ->icon('heroicon-o-cpu-chip')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('auto_absence_alert')
                                ->label('Alertes instantanées d\'absences aux parents')
                                ->helperText('Envoie une notification push instantanée dès qu\'un professeur signale une absence.')
                                ->default(true),

                            Toggle::make('auto_grade_notification')
                                ->label('Notifications des nouvelles notes')
                                ->helperText('Alerte les parents et élèves dès qu\'une évaluation est enregistrée.')
                                ->default(true),

                            Select::make('timezone')
                                ->label('Fuseau Horaire du Système')
                                ->options([
                                    'Africa/Casablanca (GMT+1)' => 'Casablanca / Rabat (GMT+1)',
                                    'Europe/Paris (GMT+2)' => 'Paris / Madrid (GMT+2)',
                                    'UTC' => 'Temps Universel Coordonné (UTC)',
                                ])
                                ->default('Africa/Casablanca (GMT+1)')
                                ->prefixIcon('heroicon-m-clock'),

                            Toggle::make('maintenance_mode')
                                ->label('Mode Maintenance')
                                ->helperText('Empêche la connexion des élèves et professeurs pour les mises à jour techniques.')
                                ->default(false),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            $group = in_array($key, ['auto_absence_alert', 'auto_grade_notification', 'maintenance_mode', 'timezone']) ? 'system' : 'general';
            Setting::set($key, is_bool($value) ? ($value ? '1' : '0') : (string) ($value ?? ''), $group);
        }

        // Record an audit log for this update!
        AuditLog::record(
            action: 'Modification Paramètres',
            description: 'Mise à jour des configurations globales de l\'établissement',
            subjectType: 'Paramètres',
            subjectId: '1',
            details: [
                'school_name' => $state['school_name'] ?? null,
                'academic_year' => $state['academic_year'] ?? null,
                'currency' => $state['currency'] ?? null,
            ]
        );

        Notification::make()
            ->title('Paramètres enregistrés avec succès')
            ->body('Toutes les configurations de l\'école ont été appliquées.')
            ->success()
            ->duration(4000)
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Enregistrer les modifications')
                ->submit('save')
                ->color('primary')
                ->icon('heroicon-o-check-circle'),
        ];
    }
}
