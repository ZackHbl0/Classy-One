<?php

namespace App\Filament\Resources\AuditLogResource\Pages;

use App\Filament\Resources\AuditLogResource;
use App\Filament\Resources\AuditLogResource\Widgets\AuditLogOverviewWidget;
use App\Models\AuditLog;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditLogResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            AuditLogOverviewWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refresh_logs')
                ->label('Synchroniser l\'historique')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    AuditLog::seedFromExistingRecords();
                    Notification::make()
                        ->title('Journal actualisé')
                        ->body('Les dernières activités du personnel ont été synchronisées.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
