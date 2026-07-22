<?php

namespace App\Filament\Resources\ClasseResource\Pages;

use App\Filament\Resources\ClasseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClasses extends ListRecords
{
    protected static string $resource = ClasseResource::class;

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return new \Illuminate\Support\HtmlString('
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">Classes</h1>
            </div>
        ');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouvelle Classe')
                ->icon('heroicon-o-plus')
                ->extraAttributes([
                    'class' => 'bg-emerald-600 hover:bg-emerald-700 text-white rounded-full px-4 shadow-sm border-0',
                    'style' => 'border-radius: 9999px;'
                ]),
        ];
    }
}
