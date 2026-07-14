<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    public static function shouldRegisterNavigation(): bool
    {
        // Hide from professors - show only to admin/secretaire
        return auth()->user()?->role !== 'professeur';
    }

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Gestion des Employés';

    protected static ?string $modelLabel = 'Employé';

    protected static ?string $pluralModelLabel = 'Employés';

    protected static ?int $navigationSort = 99;

    // ─── Access Control ──────────────────────────────────────────────

    /**
     * Only Admins can see and use this resource.
     */
    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user && $user->isAdmin();
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        return $user && $user->isAdmin();
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();
        return $user && $user->isAdmin();
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();
        return $user && $user->isAdmin();
    }

    // ─── Form ────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informations du compte')
                ->description('Créez ou modifiez un compte employé.')
                ->icon('heroicon-o-user-circle')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nom complet')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label('Adresse e-mail')
                        ->email()
                        ->required()
                        ->unique(User::class, 'email', ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\TextInput::make('password')
                        ->label('Mot de passe')
                        ->password()
                        ->revealable()
                        ->dehydrateStateUsing(fn($state) => Hash::make($state))
                        ->dehydrated(fn($state) => filled($state))
                        ->required(fn(string $context) => $context === 'create')
                        ->helperText('Laissez vide pour conserver le mot de passe actuel lors de la modification.')
                        ->maxLength(255),

                    Forms\Components\Select::make('role')
                        ->label('Rôle')
                        ->options([
                            'admin'      => '👑 Administrateur / Directeur',
                            'secretaire' => '📋 Secrétaire',
                            'professeur' => '🎓 Professeur',
                        ])
                        ->required()
                        ->live()
                        ->native(false)
                        ->helperText('Les Secrétaires ont un accès limité sans données financières. Les Professeurs peuvent publier des cours pour leur classe assignée.'),

                    Forms\Components\Select::make('classe_id')
                        ->label('Classe assignée')
                        ->relationship('classe', 'nomClasse')
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->visible(fn(Get $get): bool => $get('role') === 'professeur')
                        ->required(fn(Get $get): bool => $get('role') === 'professeur')
                        ->helperText('Requis pour les professeurs. La classe représente la filière assignée (ex. DEV201).'),

                    \Filament\Forms\Components\Placeholder::make('filiere_info')
                        ->label('Note')
                        ->content('La classe sélectionnée ci-dessus détermine la filière du professeur. Les étudiants et les cours visibles seront filtrés en conséquence.')
                        ->visible(fn(Get $get): bool => $get('role') === 'professeur')
                        ->columnSpanFull(),

                    Forms\Components\TagsInput::make('matieres')
                        ->label('Matière(s) assignée(s)')
                        ->separator(',')
                        ->placeholder('Ajouter une matière (ex: Mathématiques, C#...)')
                        ->helperText('Saisissez une matière puis appuyez sur Entrée pour en ajouter d\'autres.')
                        ->visible(fn(Get $get): bool => $get('role') === 'professeur')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    // ─── Table ───────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\BadgeColumn::make('role')
                    ->label('Rôle')
                    ->colors([
                        'warning' => 'secretaire',
                        'success' => 'admin',
                        'info'    => 'professeur',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'admin'      => 'Administrateur',
                        'secretaire' => 'Secrétaire',
                        'professeur' => 'Professeur',
                        default      => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('classe.nomClasse')
                    ->label('Classe')
                    ->badge()
                    ->color('info')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Filtrer par rôle')
                    ->options([
                        'admin'      => 'Administrateur',
                        'secretaire' => 'Secrétaire',
                        'professeur' => 'Professeur',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    // ─── Pages ───────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
