<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Event;

class UpcomingEventsWidget extends BaseWidget
{
    protected static ?string $heading = 'Upcoming Events';
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Event::query()
                    ->where('date_evenement', '>=', now())
                    ->orderBy('date_evenement', 'asc')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('titre')
                    ->label('Event')
                    ->description(fn (Event $record): string => $record->lieu ?? 'N/A')
                    ->limit(30),
                Tables\Columns\TextColumn::make('date_evenement')
                    ->label('Date')
                    ->date('M d, Y'),
            ])
            ->emptyStateHeading('No upcoming events')
            ->emptyStateDescription("There are no events scheduled in the near future.")
            ->emptyStateIcon('heroicon-o-face-smile')
            ->paginated(false);
    }
}
