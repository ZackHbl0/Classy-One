<?php

namespace App\Filament\Resources\ParentResource\Pages;

use App\Filament\Resources\ParentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateParent extends CreateRecord
{
    protected static string $resource = ParentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
