<?php

namespace App\Observers;

use App\Models\DocumentRequest;
use App\Services\NotificationService;

class DocumentRequestObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the DocumentRequest "updated" event.
     */
    public function updated(DocumentRequest $request): void
    {
        if ($request->wasChanged('status') && $request->status === 'ready') {
            $student = $request->student;

            if ($student) {
                $this->notificationService->send(
                    title: 'Document Prêt',
                    message: "Votre demande de \"{$request->document_type}\" est maintenant prête. Vous pouvez la récupérer à la scolarité.",
                    category: 'Général',
                    targetType: 'students',
                    targetIds: [$student->idStudent]
                );
            }
        }
    }
}
