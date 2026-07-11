<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Resources\Pages\ViewRecord;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            StudentResource::getUrl('index') => 'Étudiants',
            '#' => 'Voir',
        ];
    }

    protected function getRedirectUrl(): string
    {
        return StudentResource::getUrl('index');
    }
}
