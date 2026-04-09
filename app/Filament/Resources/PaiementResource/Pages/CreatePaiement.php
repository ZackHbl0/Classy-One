<?php

namespace App\Filament\Resources\PaiementResource\Pages;

use App\Filament\Resources\PaiementResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaiement extends CreateRecord
{
    protected static string $resource = PaiementResource::class;

    /**
     * Automatically inject Ann_id before saving a new payment.
     * The paiement table requires Ann_id (Année scolaire / school year)
     * but the form does not expose it, so we resolve it from the
     * related Registre record and default to 1 if not found.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['Ann_id'])) {
            $registre = \App\Models\Registre::find($data['Reg_id'] ?? null);
            $data['Ann_id'] = $registre?->Ann_id ?? 1;
        }

        return $data;
    }
}
