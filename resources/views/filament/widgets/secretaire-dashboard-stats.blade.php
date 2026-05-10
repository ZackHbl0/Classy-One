<x-filament-widgets::widget class="fi-wi-stats-overview">
    {{-- 3-Column Grid (operational stats only, no financial data) --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

        <!-- Card 1: Students -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 shadow-sm flex items-center">
            <div class="flex-shrink-0 mr-4 rounded-full bg-indigo-50 dark:bg-indigo-900/30 p-3">
                <x-heroicon-o-users class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-tight">Total Students</h4>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalStudents) }}</span>
                    <span class="text-xs font-medium text-emerald-500">{{ $studentsGrowth }}</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Notifications -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 shadow-sm flex items-center">
            <div class="flex-shrink-0 mr-4 rounded-full bg-blue-50 dark:bg-blue-900/30 p-3">
                <x-heroicon-o-bell class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-tight">Notifications Sent</h4>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalNotifications) }}</span>
                    <span class="text-xs font-medium text-emerald-500">{{ $notificationsGrowth }}</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Upcoming Events -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 shadow-sm flex items-center">
            <div class="flex-shrink-0 mr-4 rounded-full bg-purple-50 dark:bg-purple-900/30 p-3">
                <x-heroicon-o-calendar class="w-6 h-6 text-purple-600 dark:text-purple-400" />
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-tight">Upcoming Events</h4>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($upcomingEvents) }}</span>
                    <span class="text-xs font-medium text-gray-500">Next: {{ $nextEventDate }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Access restricted notice --}}
    <div class="mt-4 flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20 px-4 py-3 text-sm text-amber-700 dark:text-amber-300">
        <x-heroicon-o-lock-closed class="w-4 h-4 flex-shrink-0" />
        <span>Les données financières sont réservées aux administrateurs.</span>
    </div>
</x-filament-widgets::widget>
