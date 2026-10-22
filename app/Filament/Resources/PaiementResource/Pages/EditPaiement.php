<?php

namespace App\Filament\Resources\PaiementResource\Pages;

use App\Filament\Resources\PaiementResource;
use App\Services\PaymentService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPaiement extends EditRecord
{
    protected static string $resource = PaiementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('receipt')
                ->label('Télécharger Reçu PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn() => route('paiements.receipt.download', ['id' => $this->record->id]))
                ->openUrlInNewTab()
                ->visible(fn() => $this->record->isPaye()),
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Automatically preserve or inject Ann_id when editing a payment.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['Ann_id'])) {
            $data['Ann_id'] = $this->record->Ann_id
                ?? (\App\Models\Registre::find($data['Reg_id'] ?? null)?->Ann_id ?? 1);
        }

        return $data;
    }

    /**
     * Wrap payment update inside DB::transaction with audit logging.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(PaymentService::class)->updatePayment($record, $data, auth()->user());
    }

    /**
     * Wrap payment deletion inside DB::transaction with audit logging.
     */
    protected function handleRecordDeletion(Model $record): void
    {
        app(PaymentService::class)->deletePayment($record, auth()->user());
    }
}
