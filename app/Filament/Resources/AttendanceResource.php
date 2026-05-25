<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Planning;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceResource extends Resource
{
    protected static ?string $model = Planning::class;

    public static function shouldRegisterNavigation(): bool
    {
        // Hide from professors - show only to admin/secretaire
        return auth()->user()?->role !== 'professeur';
    }

    protected static ?string $slug = 'attendances';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Présences';
    protected static ?string $modelLabel = 'Présence';
    protected static ?string $pluralModelLabel = 'Présences';

    // ── Présences removed from UI ──────────────────────────────────────
    // Hidden from the sidebar for all roles (admin & secrétaire).
    protected static bool $navigationHidden = true;

    // Block direct URL access to /panel/attendances for all users.
    public static function canAccess(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Détails de Présence')
                    ->schema([
                        Forms\Components\Select::make('idStudent')
                            ->label('Étudiant')
                            ->options(Student::all()->mapWithKeys(function ($student) {
                                return [$student->idStudent => $student->nom . ' ' . $student->prenom . ' (' . $student->matricule . ')'];
                            }))
                            ->searchable()
                            ->required(),
                        Forms\Components\DatePicker::make('date')
                            ->label('Date')
                            ->default(now())
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'Absents' => 'Absent',
                                'Late in' => 'En retard',
                                'Early Leave' => 'Départ anticipé',
                                'Leaves' => 'En congé',
                            ])
                            ->required(),
                        Forms\Components\TimePicker::make('check_in')
                            ->label('Heure d\'arrivée (optionnel)'),
                        Forms\Components\TimePicker::make('check_out')
                            ->label('Heure de départ (optionnel)'),
                    ])->columns(2),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        // Only show pure student attendance records (not general class schedules)
        return parent::getEloquentQuery()
            ->whereNotNull('idStudent');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.nom')
                    ->label('Étudiant')
                    ->getStateUsing(fn($record) => optional($record->student)->nom . ' ' . optional($record->student)->prenom)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Absents' => 'danger',
                        'Late in' => 'warning',
                        'Early Leave' => 'warning',
                        'Leaves' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('check_in')
                    ->label('Check In')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('check_out')
                    ->label('Check Out')
                    ->time('H:i'),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Absents' => 'Absent',
                        'Late in' => 'En retard',
                        'Early Leave' => 'Départ anticipé',
                        'Leaves' => 'En congé',
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
