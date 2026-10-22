<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentRequestResource\Pages;
use App\Models\DocumentRequest;
use App\Services\OfficialDocumentService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class DocumentRequestResource extends Resource
{
    protected static ?string $model = DocumentRequest::class;

    public static function shouldRegisterNavigation(): bool
    {
        // Visible to admin, superadmin, secretaire
        $role = auth()->user()?->role;
        return in_array($role, ['admin', 'superadmin', 'secretaire', 'direction']);
    }

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationGroup = 'Scolarité & Documents';
    protected static ?string $modelLabel = 'Demande de Document';
    protected static ?string $pluralModelLabel = 'Demandes de Documents';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Détails de la Demande')
                    ->schema([
                        Forms\Components\TextInput::make('reference')
                            ->label('Réf. Unique')
                            ->prefixIcon('heroicon-m-hashtag')
                            ->disabled(),
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
                            ->formatStateUsing(fn(?DocumentRequest $record) => $record?->document_type_title ?? $record?->document_type)
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
                            ->label('Statut du document')
                            ->options([
                                'nouveau' => 'Nouveau (En attente)',
                                'en_cours' => 'En cours de traitement',
                                'pret' => 'Prêt (Certifié & Disponible)',
                                'rejete' => 'Rejeté',
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if (in_array($state, ['pret', 'ready']) && empty($get('ready_date'))) {
                                    $set('ready_date', now()->setTimezone('Africa/Casablanca')->format('Y-m-d H:i'));
                                }
                            })
                            ->required(),
                        Forms\Components\DateTimePicker::make('request_date')
                            ->label('Date de demande')
                            ->displayFormat('d/m/Y H:i')
                            ->timezone('Africa/Casablanca')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('ready_date')
                            ->label('Date de mise à disposition')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->timezone('Africa/Casablanca')
                            ->helperText('Définissez la date et l\'heure à partir de laquelle le document est prêt.')
                            ->nullable(),
                        Forms\Components\Textarea::make('reason')
                            ->label('Raison / Motif formulé par l\'élève ou parent')
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Traitement & Fichier PDF Officiel')
                    ->schema([
                        Forms\Components\Textarea::make('admin_note')
                            ->label('Note administrative / Message pour le demandeur')
                            ->placeholder('Ex: Votre document a été généré et certifié. Vous pouvez le télécharger directement.')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Motif de rejet (en cas de refus)')
                            ->placeholder('Ex: Frais de scolarité non régularisés, dossier incomplet...')
                            ->visible(fn(Forms\Get $get) => in_array($get('status'), ['rejete', 'rejected']))
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('file_url')
                            ->label('Fichier PDF officiel attaché')
                            ->acceptedFileTypes(['application/pdf'])
                            ->disk('local')
                            ->directory('documents/official')
                            ->visibility('private')
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Réf. Unique')
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.nom')
                    ->label('Élève')
                    ->formatStateUsing(fn($record) => $record->student ? ($record->student->nom . ' ' . $record->student->prenom) : 'N/A')
                    ->description(fn($record) => $record->student?->matricule)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('applicant')
                    ->label('Demandeur')
                    ->getStateUsing(fn(DocumentRequest $record) => $record->parent ? "Parent : {$record->parent->name}" : 'Élève')
                    ->badge()
                    ->color(fn(DocumentRequest $record) => $record->parent ? 'warning' : 'info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Type de document')
                    ->formatStateUsing(fn($record) => $record->document_type_title)
                    ->searchable(),
                Tables\Columns\TextColumn::make('urgency')
                    ->label('Urgence')
                    ->badge()
                    ->color(fn(string $state): string => match (strtolower($state ?? 'normal')) {
                        'urgent' => 'danger',
                        default => 'info',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn(DocumentRequest $record) => $record->status_label)
                    ->color(fn(DocumentRequest $record) => $record->badge_color)
                    ->sortable(),
                Tables\Columns\TextColumn::make('request_date')
                    ->label('Date demande')
                    ->dateTime('d/m/Y H:i', 'Africa/Casablanca')
                    ->sortable(),
                Tables\Columns\IconColumn::make('file_url')
                    ->label('PDF')
                    ->icon(fn(?string $state): string => $state ? 'heroicon-m-document-check' : 'heroicon-m-minus')
                    ->color(fn(?string $state): string => $state ? 'success' : 'gray'),
            ])
            ->defaultSort('request_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'nouveau' => 'Nouveau (En attente)',
                        'en_cours' => 'En cours',
                        'pret' => 'Prêt',
                        'rejete' => 'Rejeté',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $val = $data['value'] ?? null;
                        if (!$val) return $query;
                        return match ($val) {
                            'nouveau' => $query->whereIn('status', ['nouveau', 'pending', 'new']),
                            'en_cours' => $query->whereIn('status', ['en_cours', 'processing']),
                            'pret' => $query->whereIn('status', ['pret', 'ready', 'approved']),
                            'rejete' => $query->whereIn('status', ['rejete', 'rejected']),
                            default => $query->where('status', $val),
                        };
                    }),
                Tables\Filters\SelectFilter::make('urgency')
                    ->label('Urgence')
                    ->options([
                        'normal' => 'Normale',
                        'urgent' => 'Urgente',
                    ]),
                Tables\Filters\SelectFilter::make('demandeur')
                    ->label('Demandé par')
                    ->options([
                        'parent' => 'Parents uniquement',
                        'student' => 'Élèves directement',
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
                // 1. Action: Generate Official PDF
                Tables\Actions\Action::make('generatePdf')
                    ->label('Générer PDF Officiel')
                    ->icon('heroicon-o-sparkles')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Génération du Document Officiel')
                    ->modalDescription(fn(DocumentRequest $record) => "Confirmez-vous la génération et certification officielle du document {$record->document_type_title} pour {$record->student?->nom} {$record->student?->prenom} ?")
                    ->action(function (DocumentRequest $record) {
                        try {
                            $service = app(OfficialDocumentService::class);
                            $service->generate($record, auth()->user());

                            Notification::make()
                                ->title('Document officiel généré')
                                ->body("Le document {$record->reference} a été généré, certifié et mis à disposition de l'élève.")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Erreur de génération')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // 2. Action: Download Ready PDF
                Tables\Actions\Action::make('downloadPdf')
                    ->label('Télécharger')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn(DocumentRequest $record) => $record->isReady() && !empty($record->file_url))
                    ->url(function (DocumentRequest $record) {
                        return URL::temporarySignedRoute(
                            'documents.signed_download',
                            now()->addHours(2),
                            ['id' => $record->id]
                        );
                    })
                    ->openUrlInNewTab(),

                // 3. Action: Reject request with note
                Tables\Actions\Action::make('reject')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(DocumentRequest $record) => !$record->isRejected())
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Motif du rejet (obligatoire)')
                            ->required()
                            ->placeholder('Ex: Dossier administratif incomplet, frais d\'inscription non acquittés...'),
                    ])
                    ->action(function (DocumentRequest $record, array $data) {
                        $service = app(OfficialDocumentService::class);
                        $service->reject($record, $data['rejection_reason'], auth()->user());

                        Notification::make()
                            ->title('Demande rejetée')
                            ->body("La demande {$record->reference} a été rejetée avec notification transmise au demandeur.")
                            ->warning()
                            ->send();
                    }),

                // 4. Action: Standard Edit
                Tables\Actions\EditAction::make()
                    ->label('Traiter')
                    ->icon('heroicon-o-pencil-square'),
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
