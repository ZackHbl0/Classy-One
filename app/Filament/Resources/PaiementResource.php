<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaiementResource\Pages;
use App\Filament\Resources\PaiementResource\RelationManagers;
use App\Models\Paiement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaiementResource extends Resource
{
    protected static ?string $model = Paiement::class;

    public static function shouldRegisterNavigation(): bool
    {
        // Hide from professors - show only to admin/secretaire
        return auth()->user()?->role !== 'professeur';
    }

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Détails du Paiement')
                    ->schema([
                        Forms\Components\Select::make('Reg_id')
                            ->label('Étudiant / Inscription')
                            ->options(function () {
                                return \App\Models\Registre::with('student')->get()
                                    ->mapWithKeys(fn($r) => [
                                        $r->id => ($r->student->nom ?? '?') . ' ' . ($r->student->prenom ?? '') . ' (Reg#' . $r->id . ')'
                                    ]);
                            })
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('montant')
                            ->numeric()
                            ->prefix('MAD')
                            ->required(),
                        Forms\Components\DatePicker::make('dateEcheance')
                            ->label('Date d\'échéance')
                            ->required(),
                        Forms\Components\Select::make('modePaiement')
                            ->options([
                                'Espèce' => 'Espèce',
                                'Chèque' => 'Chèque',
                                'Virement' => 'Virement',
                            ])
                            ->required(),
                        Forms\Components\Select::make('statut')
                            ->options([
                                'Payé' => 'Payé',
                                'Non Payé' => 'Non Payé',
                                'En attente' => 'En attente',
                            ])
                            ->required(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registre.student.nom')
                    ->label('Étudiant')
                    ->formatStateUsing(fn($record) => $record->registre->student->nom . ' ' . $record->registre->student->prenom)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('montant')
                    ->money('MAD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('dateEcheance')
                    ->label('Échéance')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Payé' => 'success',
                        'En attente' => 'warning',
                        'Non Payé' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->options([
                        'Payé' => 'Payé',
                        'Non Payé' => 'Non Payé',
                        'En attente' => 'En attente',
                    ])
            ])
            ->actions([
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
            'index' => Pages\ListPaiements::route('/'),
            'create' => Pages\CreatePaiement::route('/create'),
            'edit' => Pages\EditPaiement::route('/{record}/edit'),
        ];
    }
}
