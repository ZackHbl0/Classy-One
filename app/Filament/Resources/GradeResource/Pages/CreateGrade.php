<?php

namespace App\Filament\Resources\GradeResource\Pages;

use App\Filament\Resources\GradeResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateGrade extends CreateRecord
{
    protected static string $resource = GradeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Note enregistrée')
            ->body('La note a été ajoutée avec succès.');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-fill teacher_id if not set (professors)
        if (!isset($data['teacher_id'])) {
            $data['teacher_id'] = auth()->id();
        }

        // Auto-fill classe_id from course for faster queries
        if (isset($data['course_id'])) {
            $course = \App\Models\Course::find($data['course_id']);
            $data['classe_id'] = $course?->classe_id;
            $data['subject_name'] = $course?->title;
        }

        return $data;
    }
}
