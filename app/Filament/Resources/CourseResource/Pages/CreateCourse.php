<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if ($user?->isProfesseur()) {
            $data['professor_id'] = $user->id;
            if (empty($data['classe_id'])) {
                $data['classe_id'] = $user->classes->first()?->id ?? $user->classe_id;
            }
        }

        return $data;
    }
}
