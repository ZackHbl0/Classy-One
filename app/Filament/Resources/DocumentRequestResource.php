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

    public static function shouldRegisterNavigation(): bool
    {
        // Hide from professors - show only to admin/secretaire
        return auth()->user()?->role !== 'professeur';
    }

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
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->nom . ' ' . $record->prenom . ' (' . $record->matricule . ')')
                            ->searchable()
                            ->disabled()
                            ->required(),
                        Forms\Components\TextInput::make('demandeur_info')
                            ->label('Demandé par')
                            ->formatStateUsing(fn(?DocumentRequest $record) => $record && $record->parent ? "Parent : {$record->parent->name} (Tél: {$record->parent->phone})" : "Étudiant directement")
                            ->disabled()
                            ->visible(fn(?DocumentRequest $record) => $record !== null),
                        Forms\Components\TextInput::make('document_type')
                            ->label('Type de document')
                            ->prefixIcon('heroicon-m-document-text')
                            ->disabled(),
                        Forms\Components\Select::make('urgency')
                            ->label('Urgence')
                            ->options([
                                'normal' => 'Normale',
                                'urgent' => 'Urgente',
                            ])
                            ->formatStateUsing(fn ($state) => strtolower($state ?? 'normal'))
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente',
                                'processing' => 'En cours',
                                'ready' => 'Prêt',
                                'rejected' => 'Rejeté',
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state === 'ready' && empty($get('ready_date'))) {
                                    $set('ready_date', now()->setTimezone('Africa/Casablanca')->format('Y-m-d H:i'));
                                }
                            })
                            ->required(),
                        Forms\Components\DateTimePicker::make('request_date')
                            ->label('Date de demande')
                            ->displayFormat('d/m/Y H:i:s')
                            ->timezone('Africa/Casablanca')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('ready_date')
                            ->label('Date de disponibilité')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->timezone('Africa/Casablanca')
                            ->placeholder('Choisir la date où le document sera prêt')
                            ->helperText('Définissez la date et l\'heure à partir de laquelle l\'étudiant peut récupérer son document.')
                            ->nullable(),
                        Forms\Components\Textarea::make('reason')
                            ->label('Raison / Motif')
                            ->disabled()
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Traitement Administratif')
                    ->schema([
                        Forms\Components\Textarea::make('admin_note')
                            ->label('Message pour l\'étudiant (Optionnel)')
                            ->placeholder('Ex: Votre document est prêt et peut être récupéré à la scolarité.')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('file_url')
                            ->label('Joindre un document PDF (Optionnel)')
                            ->acceptedFileTypes(['application/pdf'])
                            ->directory('documents/responses')
                            ->downloadable()
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
                    ->formatStateUsing(fn($record) => $record->student ? ($record->student->nom . ' ' . $record->student->prenom) : 'N/A')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('applicant')
                    ->label('Demandeur')
                    ->getStateUsing(fn(DocumentRequest $record) => $record->parent ? "Parent : {$record->parent->name}" : 'Étudiant')
                    ->badge()
                    ->color(fn(DocumentRequest $record) => $record->parent ? 'warning' : 'info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('urgency')
                    ->label('Urgence')
                    ->badge()
                    ->color(fn(string $state): string => match (strtolower($state ?? 'normal')) {
                        'normal' => 'info',
                        'urgent' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'warning',
                        'ready' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('request_date')
                    ->label('Date demande')
                    ->dateTime('d/m/Y H:i', 'Africa/Casablanca')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ready_date')
                    ->label('Disponibilité')
                    ->dateTime('d/m/Y H:i', 'Africa/Casablanca')
                    ->placeholder('-')
                    ->sortable(),
                Tables\Columns\IconColumn::make('file_url')
                    ->label('Document')
                    ->icon(fn(?string $state): string => $state ? 'heroicon-m-document-check' : 'heroicon-m-minus')
                    ->color(fn(?string $state): string => $state ? 'success' : 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'processing' => 'En cours',
                        'ready' => 'Prêt',
                        'rejected' => 'Rejeté',
                    ]),
                Tables\Filters\SelectFilter::make('urgency')
                    ->options([
                        'normal' => 'Normale',
                        'urgent' => 'Urgente',
                    ]),
                Tables\Filters\SelectFilter::make('demandeur')
                    ->label('Demandé par')
                    ->options([
                        'parent' => 'Parents uniquement',
                        'student' => 'Étudiants directement',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (($data['value'] ?? null) === 'parent') {
                            return $query->whereNotNull('parent_id');
                        }
                        if (($data['value'] ?? null) === 'student') {
                            return $query->whereNull('parent_id');
                        }
                        return $query;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('generatePdf')
                    ->label('Générer PDF')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->visible(fn(DocumentRequest $record) => stripos($record->document_type ?? '', 'scolarit') !== false)
                    ->action(function (DocumentRequest $record) {
                        $controller = new DocumentPdfController();
                        $controller->generateAndSave($record);
                    })
                    ->requiresConfirmation()
                    ->successNotificationTitle('PDF généré avec succès'),
                Tables\Actions\EditAction::make()
                    ->label('Répondre')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->modalHeading('Répondre à la demande')
                    ->modalSubmitActionLabel('Enregistrer'),
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
        ];
    }
}
