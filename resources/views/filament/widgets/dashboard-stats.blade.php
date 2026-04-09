<x-filament-widgets::widget class="fi-wi-stats-overview">
    {{-- Header --}}
    <div class="mb-4">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Dashboard</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Overview of OSBT Notify activity</p>
    </div>

    {{-- 4-Column Grid --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">

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
            <div class="flex-shrink-0 mr-4 rounded-full bg-indigo-50 dark:bg-indigo-900/30 p-3">
                <x-heroicon-o-bell class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-tight">Notifications Sent</h4>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalNotifications) }}</span>
                    <span class="text-xs font-medium text-emerald-500">{{ $notificationsGrowth }}</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Events -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 shadow-sm flex items-center">
            <div class="flex-shrink-0 mr-4 rounded-full bg-indigo-50 dark:bg-indigo-900/30 p-3">
                <x-heroicon-o-calendar class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-tight">Upcoming Events</h4>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($upcomingEvents) }}</span>
                    <span class="text-xs font-medium text-gray-500">Next: {{ $nextEventDate }}</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Payments -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 shadow-sm flex items-center">
            <div class="flex-shrink-0 mr-4 rounded-full bg-red-50 dark:bg-red-900/30 p-3">
                <x-heroicon-o-credit-card class="w-6 h-6 text-red-600 dark:text-red-400" />
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-tight">Payment Alerts</h4>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($overduePayments) }}</span>
                    <span class="text-xs font-medium text-red-500">{{ number_format($overduePayments) }} overdue</span>
                </div>
            </div>
        </div>

    </div>
</x-filament-widgets::widget>
