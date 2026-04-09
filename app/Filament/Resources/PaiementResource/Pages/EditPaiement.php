<?php

namespace App\Filament\Resources\PaiementResource\Pages;

use App\Filament\Resources\PaiementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaiement extends EditRecord
{
    protected static string $resource = PaiementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Automatically preserve or inject Ann_id when editing a payment.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['Ann_id'])) {
            // Keep existing value from DB if available
            $data['Ann_id'] = $this->record->Ann_id
                ?? (\App\Models\Registre::find($data['Reg_id'] ?? null)?->Ann_id ?? 1);
        }

        return $data;
    }
}
