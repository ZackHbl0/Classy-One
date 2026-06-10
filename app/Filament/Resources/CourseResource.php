<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    public static function shouldRegisterNavigation(): bool
    {
        // Only show to professors (teachers)
        return auth()->user()?->isProfesseur() ?? false;
    }

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Cours / LMS';

    protected static ?string $modelLabel = 'Cours';

    protected static ?string $pluralModelLabel = 'Cours';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user && $user->isProfesseur();
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();

        return $user?->isProfesseur() && (int) $record->professor_id === (int) $user->id;
    }

    public static function canDelete($record): bool
    {
        return static::canEdit($record);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user?->isProfesseur()) {
            $query->where('professor_id', $user->id);

            if ($user->classe_id) {
                $query->where('classe_id', $user->classe_id);
            }
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Contenu du cours')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Titre')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('categorie')
                        ->label('Catégorie')
                        ->options([
                            'Vidéo' => 'Vidéo',
                            'PDF' => 'PDF',
                            'TP' => 'TP',
                            'Cours' => 'Cours',
                        ])
                        ->required()
                        ->native(false)
                        ->default('Cours'),

                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->rows(4)
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('file_path')
                        ->label('Fichier (PDF / vidéo)')
                        ->disk('public')
                        ->directory('courses')
                        ->visibility('public')
                        ->acceptedFileTypes([
                            'video/mp4',
                            'video/quicktime',
                            'video/x-msvideo',
                            'video/x-matroska',
                            'video/webm',
                            'video/3gpp',
                            'video/x-flv',
                            'video/ogg',
                            'video/*',
                            'application/pdf',
                        ])
                        ->maxSize(512000) // 500 MB in KB
                        ->preserveFilenames()
                        ->columnSpanFull(),

                    Forms\Components\Select::make('classe_id')
                        ->label('Classe')
                        ->relationship('classe', 'nomClasse')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false)
                        ->default(fn (): ?int => auth()->user()?->classe_id),

                    Forms\Components\Select::make('professor_id')
                        ->label('Professeur')
                        ->relationship(
                            'professor',
                            'name',
                            fn (Builder $query) => $query->where('role', 'professeur')
                        )
                        ->required()
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false)
                        ->default(fn (): ?int => auth()->user()?->id),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('categorie')
                    ->label('Catégorie')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Vidéo' => 'success',
                        'PDF' => 'warning',
                        'TP' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('classe.nomClasse')
                    ->label('Classe')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('professor.name')
                    ->label('Professeur')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('file_path')
                    ->label('Fichier')
                    ->boolean()
                    ->getStateUsing(fn (Course $record): bool => filled($record->file_path)),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Publié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
