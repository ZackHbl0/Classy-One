<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Notification;

class RecentNotificationsWidget extends BaseWidget
{
    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return new \Illuminate\Support\HtmlString('
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 rounded-xl bg-sky-50 dark:bg-sky-900/30 p-2 border border-sky-100 dark:border-sky-800">
                    <svg class="w-5 h-5 text-sky-500 dark:text-sky-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                </div>
                <span class="text-base font-bold text-gray-900 dark:text-white">Recent Notifications</span>
            </div>
        ');
    }
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Notification::query()->latest('created_at')->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('titre')
                    ->label('Title')
                    ->limit(30),
                Tables\Columns\TextColumn::make('categorie')
                    ->label('Category')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'paiement' => 'success',
                        'urgent' => 'danger',
                        'test' => 'info',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->date('M d, Y'),
                // We'll mimic the status from the image (SENT) 
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->default('SENT')
                    ->badge()
                    ->color('success'),
            ])
            ->paginated(false);
    }
}
