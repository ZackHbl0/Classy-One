<?php

namespace App\Filament\Resources\PaiementResource\Pages;

use App\Filament\Resources\PaiementResource;
use App\Services\PaymentService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePaiement extends CreateRecord
{
    protected static string $resource = PaiementResource::class;

    /**
     * Automatically inject Ann_id before saving a new payment.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['Ann_id'])) {
            $registre = \App\Models\Registre::find($data['Reg_id'] ?? null);
            $data['Ann_id'] = $registre?->Ann_id ?? 1;
        }

        return $data;
    }

    /**
     * Wrap payment creation inside DB::transaction with audit logging & unique reference generator.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return app(PaymentService::class)->recordPayment($data, auth()->user());
    }
}
