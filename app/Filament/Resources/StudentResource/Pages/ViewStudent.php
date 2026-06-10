<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Filament\Resources\GradeResource;
use Filament\Resources\Pages\ViewRecord;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            GradeResource::getUrl('index') => 'Notes',
            '#' => 'Voir',
        ];
    }

    protected function getRedirectUrl(): string
    {
        return GradeResource::getUrl('index');
    }
}
