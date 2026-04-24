<?php

namespace App\Observers;

use App\Models\Event;
use App\Services\NotificationService;

class EventObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {
        $this->notificationService->send(
            title: 'Nouvel Événement : ' . $event->titre,
            message: $event->description,
            category: 'Événement',
            targetType: 'all'
        );
    }
}
