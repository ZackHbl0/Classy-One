<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentRequestResource\Pages;
use App\Filament\Resources\DocumentRequestResource\RelationManagers;
use App\Models\DocumentRequest;
use App\Http\Controllers\DocumentPdfController;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocumentRequestResource extends Resource
{
    protected static ?string $model = DocumentRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $modelLabel = 'Demande de Document';
    protected static ?string $pluralModelLabel = 'Demandes de Documents';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Détails de la Demande')
                    ->schema([
                        Forms\Components\Select::make('idStudent')
                            ->label('Étudiant')
                            ->relationship('student', 'idStudent')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->nom . ' ' . $record->prenom . ' (' . $record->matricule . ')')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('document_type')
                            ->label('Type de document')
                            ->options([
                                'Certificat de Scolarité' => 'Certificat de Scolarité',
                                'Relevé de Notes' => 'Relevé de Notes',
                                'Attestation de Réussite' => 'Attestation de Réussite',
                                'Autre' => 'Autre',
                            ])
                            ->required(),
                        Forms\Components\Select::make('urgency')
                            ->label('Urgence')
                            ->options([
                                'Basse' => 'Basse',
                                'Moyenne' => 'Moyenne',
                                'Haute' => 'Haute',
                            ])
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'En attente' => 'En attente',
                                'En cours' => 'En cours',
                                'Prêt' => 'Prêt',
                                'Rejeté' => 'Rejeté',
                            ])
                            ->required(),
                        Forms\Components\DateTimePicker::make('request_date')
                            ->label('Date de demande')
                            ->default(now()),
                        Forms\Components\DateTimePicker::make('ready_date')
                            ->label('Date de disponibilité'),
                        Forms\Components\Textarea::make('reason')
                            ->label('Raison / Motif')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Traitement Administratif')
                    ->schema([
                        Forms\Components\FileUpload::make('admin_note')
                            ->label('Fichier / Document final')
                            ->directory('documents')
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.nom')
                    ->label('Étudiant')
                    ->formatStateUsing(fn ($record) => $record->student->nom . ' ' . $record->student->prenom)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('urgency')
                    ->label('Urgence')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Basse' => 'info',
                        'Moyenne' => 'warning',
                        'Haute' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'En attente' => 'gray',
                        'En cours' => 'warning',
                        'Prêt' => 'success',
                        'Rejeté' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('request_date')
                    ->label('Date demande')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('file_url')
                    ->label('PDF')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'En attente' => 'En attente',
                        'En cours' => 'En cours',
                        'Prêt' => 'Prêt',
                        'Rejeté' => 'Rejeté',
                    ]),
                Tables\Filters\SelectFilter::make('urgency')
                    ->options([
                        'Basse' => 'Basse',
                        'Moyenne' => 'Moyenne',
                        'Haute' => 'Haute',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('generatePdf')
                    ->label('Générer PDF')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->visible(fn (DocumentRequest $record) => $record->document_type === 'Certificat de Scolarité')
                    ->action(function (DocumentRequest $record) {
                        $controller = new DocumentPdfController();
                        $controller->generateAndSave($record);
                    })
                    ->requiresConfirmation()
                    ->successNotificationTitle('PDF généré avec succès'),
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
            'index' => Pages\ListDocumentRequests::route('/'),
            'create' => Pages\CreateDocumentRequest::route('/create'),
            'edit' => Pages\EditDocumentRequest::route('/{record}/edit'),
        ];
    }
}
