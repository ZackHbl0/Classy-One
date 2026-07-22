<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbsenceResource\Pages;
use App\Filament\Resources\AbsenceResource\RelationManagers;
use App\Models\Absence;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AbsenceResource extends Resource
{
    protected static ?string $model = Absence::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    
    protected static ?string $navigationLabel = 'Gestion des Absences';
    protected static ?string $modelLabel = 'Absence';
    protected static ?string $pluralModelLabel = 'Absences';
    protected static ?int $navigationSort = 5;

    public static function shouldRegisterNavigation(): bool
    {
        $role = auth()->user()?->role;
        return in_array($role, ['admin', 'secretaire']);
    }

    public static function canViewAny(): bool
    {
        $role = auth()->user()?->role;
        return in_array($role, ['admin', 'secretaire']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->relationship('student', 'nom')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->nom} {$record->prenom}")
                    ->label('Étudiant')
                    ->required()
                    ->disabled(),
                Forms\Components\Select::make('classe_id')
                    ->relationship('classe', 'nomClasse')
                    ->label('Classe')
                    ->required()
                    ->disabled(),
                Forms\Components\Select::make('matiere')
                    ->label('Matière')
                    ->options(function () {
                        return collect(\App\Models\User::pluck('matieres'))
                            ->filter()
                            ->flatMap(function($m) {
                                if (is_array($m)) return $m;
                                $decoded = json_decode($m, true);
                                return is_array($decoded) ? $decoded : [$m];
                            })
                            ->filter()
                            ->unique()
                            ->sort()
                            ->mapWithKeys(fn($m) => [$m => $m])
                            ->toArray();
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('prof_id')
                    ->relationship('prof', 'name')
                    ->label('Professeur')
                    ->required(),
                Forms\Components\DatePicker::make('date')
                    ->required(),
                Forms\Components\TextInput::make('seance')
                    ->label('Séance (ex: 8h-10h)')
                    ->required(),
                Forms\Components\Textarea::make('student_explanation')
                    ->label('Explication de l\'étudiant')
                    ->disabled()
                    ->visible(fn ($record) => $record && $record->student_explanation),
                Forms\Components\Toggle::make('is_justified')
                    ->label('Justifiée ?')
                    ->reactive(),
                Forms\Components\Textarea::make('justification_reason')
                    ->label('Motif de justification')
                    ->visible(fn (\Filament\Forms\Get $get) => $get('is_justified')),
                Forms\Components\Select::make('status')
                    ->label('Statut')
                    ->options([
                        'pending_justification' => 'En attente',
                        'submitted_by_student' => 'Soumis par l\'étudiant',
                        'approved' => 'Approuvée',
                    ])
                    ->default('pending_justification')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('seance')
                    ->label('Séance'),
                Tables\Columns\TextColumn::make('student.nom')
                    ->label('Étudiant')
                    ->getStateUsing(fn($record) => $record->student ? "{$record->student->nom} {$record->student->prenom}" : '')
                    ->searchable(['nom', 'prenom'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('classe.nomClasse')
                    ->label('Classe')
                    ->sortable(),
                Tables\Columns\TextColumn::make('matiere')
                    ->label('Matière'),
                Tables\Columns\TextColumn::make('prof.name')
                    ->label('Professeur'),
                Tables\Columns\IconColumn::make('is_justified')
                    ->label('Justifiée')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->colors([
                        'warning' => 'pending_justification',
                        'info' => 'submitted_by_student',
                        'success' => 'approved',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending_justification' => 'En attente',
                        'submitted_by_student' => 'Soumis par l\'étudiant',
                        'approved' => 'Approuvée',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('student_explanation')
                    ->label('Explication')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('classe_id')
                    ->relationship('classe', 'nomClasse')
                    ->label('Classe'),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $data['date'] ? $query->whereDate('date', $data['date']) : $query;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('approveJustification')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'submitted_by_student')
                    ->form([
                        Forms\Components\Textarea::make('justification_reason')
                            ->label('Motif de justification final')
                            ->default(fn ($record) => $record->student_explanation)
                            ->required(),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'status' => 'approved',
                            'is_justified' => true,
                            'justification_reason' => $data['justification_reason'],
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Justification approuvée')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAbsences::route('/'),
            'create' => Pages\CreateAbsence::route('/create'),
            'view' => Pages\ViewAbsence::route('/{record}'),
            'edit' => Pages\EditAbsence::route('/{record}/edit'),
        ];
    }
}
