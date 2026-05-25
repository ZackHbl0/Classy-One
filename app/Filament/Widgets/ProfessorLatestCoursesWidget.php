<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * Professor Latest Courses Widget
 * Displays the last 5 courses uploaded by the logged-in professor.
 * Only visible to users with 'professeur' role.
 */
class ProfessorLatestCoursesWidget extends BaseWidget
{
    protected static ?string $heading = 'Your Latest Courses';

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && $user->role === 'professeur';
    }

    public function table(Table $table): Table
    {
        $professorId = auth()->id();

        return $table
            ->query(
                Course::where('professor_id', $professorId)
                    ->latest('created_at')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date d\'ajout')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->default('—'),

                Tables\Columns\TextColumn::make('classe.nomClasse')
                    ->label('Classe')
                    ->default('—'),

                Tables\Columns\TextColumn::make('file_path')
                    ->label('File')
                    ->formatStateUsing(function ($state) {
                        return $state ? '✓ Uploaded' : '✗ None';
                    })
                    ->color(fn($state) => str_contains($state, '✓') ? 'success' : 'danger'),
            ])
            ->paginated(false)
            ->striped();
    }
}
