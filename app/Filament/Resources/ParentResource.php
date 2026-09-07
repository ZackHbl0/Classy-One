<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParentResource\Pages;
use App\Filament\Resources\ParentResource\RelationManagers;
use App\Models\SchoolParent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class ParentResource extends Resource
{
    protected static ?string $model = SchoolParent::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Parents d\'élèves';

    protected static ?string $modelLabel = 'Parent d\'élève';

    protected static ?string $pluralModelLabel = 'Parents d\'élèves';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        $role = auth()->user()?->role;
        return !in_array($role, ['prof', 'professeur']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du Parent')
                    ->description('Coordonnées et accès du parent ou tuteur légal.')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom complet')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Numéro de téléphone')
                            ->tel()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Adresse e-mail')
                            ->email()
                            ->required()
                            ->unique(SchoolParent::class, 'email', ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->label('Mot de passe (Mobile App)')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->helperText('Laissez vide lors de la modification pour conserver le mot de passe actuel.')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Enfants / Élèves rattachés')
                    ->description('Sélectionnez les élèves sous la responsabilité de ce parent.')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Forms\Components\Select::make('students')
                            ->label('Élèves rattachés')
                            ->relationship('students', 'nom')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->nom} {$record->prenom} ({$record->matricule})")
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Un parent peut être associé à un ou plusieurs élèves simultanément.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom complet')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->icon('heroicon-m-phone'),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),

                Tables\Columns\TextColumn::make('students.nom')
                    ->label('Enfants')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(function ($record) {
                        return $record->students->map(fn($s) => "{$s->nom} {$s->prenom}")->join(', ');
                    })
                    ->placeholder('Aucun élève rattaché'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            RelationManagers\StudentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParents::route('/'),
            'create' => Pages\CreateParent::route('/create'),
            'edit' => Pages\EditParent::route('/{record}/edit'),
        ];
    }
}
