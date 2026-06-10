<?php

namespace App\Filament\Resources\GradeResource\Pages;

use App\Filament\Resources\GradeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditGrade extends EditRecord
{
    protected static string $resource = GradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Note mise à jour')
            ->body('La note a été modifiée avec succès.');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Update classe_id if course changed
        if (isset($data['course_id'])) {
            $course = \App\Models\Course::find($data['course_id']);
            $data['classe_id'] = $course?->classe_id;
            $data['subject_name'] = $course?->title;
        }

        return $data;
    }
}
