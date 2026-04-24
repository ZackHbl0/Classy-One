<?php

namespace App\Observers;

use App\Models\Paiement;
use App\Services\NotificationService;

class PaiementObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Paiement "updated" event.
     */
    public function updated(Paiement $paiement): void
    {
        if ($paiement->wasChanged('statut') && $paiement->statut === 'Payé') {
            $student = $paiement->registre->student ?? null;

            if ($student) {
                $this->notificationService->send(
                    title: 'Paiement Confirmé',
                    message: "Votre paiement de {$paiement->montant} MAD a été validé. Merci !",
                    category: 'Paiement',
                    targetType: 'students',
                    targetIds: [$student->idStudent]
                );
            }
        }
    }
}
