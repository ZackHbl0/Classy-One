<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Event;

class UpcomingEventsWidget extends BaseWidget
{
    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return new \Illuminate\Support\HtmlString('
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 p-2 border border-emerald-100">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                </div>
                <span class="text-base font-bold text-gray-900 dark:text-white">Upcoming Events</span>
            </div>
        ');
    }
    protected int | string | array $columnSpan = 'full';

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
                    ->html()
                    ->formatStateUsing(function ($record) {
                        $isMatch = strtolower(substr($record->titre, 0, 5)) == 'match';
                        $icon = $isMatch 
                            ? '<svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" /></svg>'
                            : '<svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" /></svg>';
                        
                        $titre = e($record->titre);
                        $lieu = e($record->lieu ?? 'N/A');
                        
                        return "
                            <div class=\"flex items-center gap-3 my-1\">
                                <div class=\"flex-shrink-0 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl p-2 border border-emerald-100\">
                                    {$icon}
                                </div>
                                <div class=\"flex flex-col\">
                                    <span class=\"font-medium text-gray-900 dark:text-white\">{$titre}</span>
                                    <span class=\"text-xs text-gray-500\">{$lieu}</span>
                                </div>
                            </div>
                        ";
                    }),
                Tables\Columns\TextColumn::make('date_evenement')
                    ->label('Date')
                    ->html()
                    ->formatStateUsing(function ($record) {
                        $date = \Carbon\Carbon::parse($record->date_evenement)->format('M d, Y');
                        return "
                            <div class=\"flex items-center justify-between w-full\">
                                <span class=\"text-gray-600 dark:text-gray-300\">{$date}</span>
                                <svg class=\"w-4 h-4 text-gray-400\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\">
                                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M8.25 4.5l7.5 7.5-7.5 7.5\" />
                                </svg>
                            </div>
                        ";
                    }),
            ])
            ->emptyStateHeading('No upcoming events')
            ->emptyStateDescription("There are no events scheduled in the near future.")
            ->emptyStateIcon('heroicon-o-face-smile')
            ->paginated(false);
    }
}
