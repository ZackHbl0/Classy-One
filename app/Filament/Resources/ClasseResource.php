<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClasseResource\Pages;
use App\Filament\Resources\ClasseResource\RelationManagers;
use App\Models\Anneescolaire;
use App\Models\Classe;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ClasseResource extends Resource
{
    protected static ?string $model = Classe::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $modelLabel = 'Classe';
    protected static ?string $pluralModelLabel = 'Classes';

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && in_array($user->role, ['admin', 'secretaire']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nomClasse')
                    ->label('Nom de la Classe')
                    ->placeholder('Ex: DEV202, MAN01, etc.')
                    ->required()
                    ->maxLength(254),
                Forms\Components\Select::make('Ann_id')
                    ->label('Année Scolaire')
                    ->options(Anneescolaire::all()->pluck('libelle', 'id'))
                    ->searchable()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomClasse')
                    ->label('NOM DE LA CLASSE')
                    ->formatStateUsing(function ($state) {
                        $prefix = strtoupper(substr($state, 0, 3));
                        if ($prefix === 'DEV') {
                            $theme = 'dev';
                            $subtitle = 'Développement';
                        } elseif ($prefix === 'MAN') {
                            $theme = 'man';
                            $subtitle = 'Management';
                        } else {
                            $theme = 'gen';
                            $subtitle = 'Général';
                        }

                        return new \Illuminate\Support\HtmlString('
                            <div class="flex items-center gap-4 py-2">
                                <div class="theme-'.$theme.'-bg p-3 rounded-2xl flex-shrink-0">
                                    <svg class="w-6 h-6 theme-'.$theme.'-text" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                </div>
                                <div>
                                    <div class="font-bold text-lg text-slate-800 dark:text-slate-200">'.$state.'</div>
                                    <div class="theme-'.$theme.'-badge theme-'.$theme.'-text text-xs font-bold px-2 py-0.5 rounded-md inline-block mt-1">'.$subtitle.'</div>
                                </div>
                            </div>
                        ');
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('anneescolaire.libelle')
                    ->label('ANNÉE SCOLAIRE')
                    ->formatStateUsing(function ($state, $record) {
                        $prefix = strtoupper(substr($record->nomClasse, 0, 3));
                        if ($prefix === 'DEV') {
                            $theme = 'dev';
                        } elseif ($prefix === 'MAN') {
                            $theme = 'man';
                        } else {
                            $theme = 'gen';
                        }

                        return new \Illuminate\Support\HtmlString('
                            <div class="flex items-center gap-2 font-bold theme-'.$theme.'-text bg-opacity-20 px-3 py-1.5 rounded-lg border border-opacity-10 border-current w-max">
                                <svg class="w-5 h-5 theme-'.$theme.'-text" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span>'.($state ?: '—').'</span>
                            </div>
                        ');
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('registres_count')
                    ->counts('registres')
                    ->label('ÉTUDIANTS')
                    ->formatStateUsing(function ($state, $record) {
                        $prefix = strtoupper(substr($record->nomClasse, 0, 3));
                        if ($prefix === 'DEV') {
                            $theme = 'dev';
                        } elseif ($prefix === 'MAN') {
                            $theme = 'man';
                        } else {
                            $theme = 'gen';
                        }

                        return new \Illuminate\Support\HtmlString('
                            <div class="theme-'.$theme.'-bg rounded-xl px-4 py-2 inline-flex flex-col items-center justify-center min-w-[5rem]">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4 theme-'.$theme.'-text" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    <span class="font-bold text-lg theme-'.$theme.'-text">'.($state ?: '0').'</span>
                                </div>
                                <span class="text-xs font-semibold theme-'.$theme.'-text opacity-80">Étudiants</span>
                            </div>
                        ');
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil-square')
                    ->tooltip('Modifier')
                    ->extraAttributes([
                        'class' => 'action-edit-btn'
                    ]),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-o-trash')
                    ->tooltip('Supprimer')
                    ->extraAttributes([
                        'class' => 'action-delete-btn'
                    ]),
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
            RelationManagers\RegistresRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClasses::route('/'),
            'create' => Pages\CreateClasse::route('/create'),
            'edit' => Pages\EditClasse::route('/{record}/edit'),
        ];
    }
}
