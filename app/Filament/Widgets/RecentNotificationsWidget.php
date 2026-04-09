<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Notification;

class RecentNotificationsWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Notifications';
    protected int | string | array $columnSpan = 1;

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
                    ->color('gray'),
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
