<x-filament-widgets::widget class="fi-wi-stats-overview">
    {{-- 3-Column Grid (operational stats only, no financial data) --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

        <!-- Card 1: Students -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm flex items-center">
            <div class="flex-shrink-0 mr-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 p-3">
                <x-heroicon-o-users class="w-7 h-7 text-emerald-600 dark:text-emerald-400" />
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-tight">Total Students</h4>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalStudents) }}</span>
                </div>
                <div class="flex items-center gap-1 mt-1">
                    <x-heroicon-m-arrow-trending-up class="w-4 h-4 text-emerald-500" />
                    <span class="text-xs font-bold text-emerald-500">{{ $studentsGrowth }}</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Notifications -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm flex items-center">
            <div class="flex-shrink-0 mr-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 p-3">
                <x-heroicon-o-bell class="w-7 h-7 text-emerald-600 dark:text-emerald-400" />
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-tight">Notifications Sent</h4>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalNotifications) }}</span>
                </div>
                <div class="flex items-center gap-1 mt-1">
                    <x-heroicon-m-arrow-trending-up class="w-4 h-4 text-emerald-500" />
                    <span class="text-xs font-bold text-emerald-500">{{ $notificationsGrowth }}</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Upcoming Events -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm flex items-center">
            <div class="flex-shrink-0 mr-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 p-3">
                <x-heroicon-o-calendar class="w-7 h-7 text-emerald-600 dark:text-emerald-400" />
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-tight">Upcoming Events</h4>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($upcomingEvents) }}</span>
                </div>
                <div class="flex items-center gap-1 mt-1">
                    <span class="text-xs font-bold text-emerald-500">Next: {{ $nextEventDate }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Access restricted notice --}}
    <div class="mt-4 flex items-center gap-2 rounded-lg border border-emerald-100 bg-emerald-50/50 dark:border-emerald-800 dark:bg-emerald-900/20 px-4 py-4 shadow-sm border-l-4 border-l-emerald-400">
        <x-heroicon-o-lock-closed class="w-5 h-5 flex-shrink-0 text-emerald-600 dark:text-emerald-400" />
        <span class="text-sm font-medium text-slate-700 dark:text-emerald-300">Les données financières sont réservées aux administrateurs.</span>
    </div>
</x-filament-widgets::widget>
