<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaiementResource\Pages;
use App\Models\Paiement;
use App\Services\PaymentService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaiementResource extends Resource
{
    protected static ?string $model = Paiement::class;

    public static function shouldRegisterNavigation(): bool
    {
        // Hide from professors - show only to admin/secretaire
        return auth()->user()?->role !== 'professeur';
    }

    /**
     * Delete permissions for financial records: Only Admin
     */
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Gestion Financière';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Paiements & Règlements';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Détails de la Transaction & Règlement')
                    ->description('Enregistrement sécurisé avec génération automatique de référence unique.')
                    ->schema([
                        Forms\Components\TextInput::make('reference')
                            ->label('Référence Unique')
                            ->placeholder('Généré automatiquement (ex: REF-2026-00042)')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),
                        Forms\Components\Select::make('Reg_id')
                            ->label('Élève / Inscription')
                            ->options(function () {
                                return \App\Models\Registre::with(['student.classe'])->get()
                                    ->mapWithKeys(function ($r) {
                                        $st = $r->student;
                                        if (!$st) return [$r->id => 'Reg #' . $r->id];
                                        $classeName = $st->classe?->nom ?? 'Sans classe';
                                        $reste = number_format($st->reste_a_payer, 0, ',', ' ');
                                        return [
                                            $r->id => "{$st->nom} {$st->prenom} ({$st->matricule}) — {$classeName} [Reste: {$reste} MAD]"
                                        ];
                                    });
                            })
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('montant')
                            ->label('Montant du versement')
                            ->numeric()
                            ->prefix('MAD')
                            ->required(),
                        Forms\Components\Select::make('modePaiement')
                            ->label('Mode de Paiement')
                            ->options([
                                'Espèce' => 'Espèce (Cash)',
                                'Chèque' => 'Chèque bancaire',
                                'Virement' => 'Virement bancaire',
                            ])
                            ->default('Espèce')
                            ->required(),
                        Forms\Components\DatePicker::make('dateEcheance')
                            ->label('Date d\'échéance / Date de valeur')
                            ->default(now())
                            ->required(),
                        Forms\Components\Select::make('statut')
                            ->label('Statut du Paiement')
                            ->options([
                                'Payé' => 'Payé (Encaissé / Validé)',
                                'Non Payé' => 'Non Payé (En attente)',
                            ])
                            ->default('Payé')
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Remarques / Référence chèque ou virement')
                            ->placeholder('N° de chèque, banque émettrice, motif de tranche...')
                            ->columnSpanFull(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Réf. Paiement')
                    ->badge()
                    ->color('gray')
                    ->copyable()
                    ->copyMessage('Référence copiée !')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('registre.student.nom')
                    ->label('Élève & Matricule')
                    ->description(fn($record) => $record->registre?->student?->matricule ?? '')
                    ->formatStateUsing(fn($record) => ($record->registre?->student?->nom ?? '') . ' ' . ($record->registre?->student?->prenom ?? ''))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('registre.student.classe.nom')
                    ->label('Classe')
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('montant')
                    ->label('Montant')
                    ->money('MAD')
                    ->weight('bold')
                    ->sortable(),
                Tables\Columns\TextColumn::make('modePaiement')
                    ->label('Mode')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'Espèce' => 'warning',
                        'Chèque' => 'info',
                        'Virement' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('dateEcheance')
                    ->label('Échéance')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn(string $state): string => match (mb_strtolower(trim($state))) {
                        'payé', 'paye', 'validé', 'valide' => 'success',
                        'non payé', 'non paye', 'en retard' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Enregistré le')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->options([
                        'Payé' => 'Payé',
                        'Non Payé' => 'Non Payé',
                    ]),
                Tables\Filters\SelectFilter::make('modePaiement')
                    ->label('Mode de Paiement')
                    ->options([
                        'Espèce' => 'Espèce',
                        'Chèque' => 'Chèque',
                        'Virement' => 'Virement',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('receipt')
                    ->label('Reçu PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn(Paiement $record) => route('paiements.receipt.download', ['id' => $record->id]))
                    ->openUrlInNewTab()
                    ->visible(fn(Paiement $record) => $record->isPaye()),
                Tables\Actions\Action::make('validate_payment')
                    ->label('Encaisser')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmer l\'encaissement')
                    ->modalDescription('Voulez-vous marquer ce versement comme Payé et générer le reçu officiel ?')
                    ->action(function (Paiement $record) {
                        app(PaymentService::class)->updatePayment($record, ['statut' => 'Payé'], auth()->user());
                        Notification::make()
                            ->title('Paiement validé avec succès')
                            ->body("Le reçu pour la référence {$record->reference} est désormais disponible.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Paiement $record) => !$record->isPaye()),
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
