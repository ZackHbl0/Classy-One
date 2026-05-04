<x-filament-widgets::widget class="fi-wi-stats-overview">
    {{-- Header & Quick Actions --}}
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Dashboard</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Overview of OSBT Notify activity</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ \App\Filament\Resources\StudentResource::getUrl('index') }}" 
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors duration-150 gap-2">
                <x-heroicon-o-users class="w-5 h-5" />
                Students List
            </a>
            <a href="{{ \App\Filament\Resources\PaiementResource::getUrl('create') }}" 
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors duration-150 gap-2">
                <x-heroicon-o-currency-dollar class="w-5 h-5" />
                Add Payment
            </a>
        </div>
    </div>

    {{-- 5-Column Grid --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">

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

        <!-- Card 2: Revenue -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 shadow-sm flex items-center">
            <div class="flex-shrink-0 mr-4 rounded-full bg-emerald-50 dark:bg-emerald-900/30 p-3">
                <x-heroicon-o-banknotes class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-tight">Total Revenue</h4>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalRevenue, 2) }}</span>
                    <span class="text-xs font-medium text-gray-500">MAD</span>
                </div>
                @if($pendingPaymentsSum > 0)
                <div class="text-[10px] font-medium text-amber-500 mt-1">
                    {{ number_format($pendingPaymentsSum, 2) }} MAD pending
                </div>
                @endif
            </div>
        </div>

        <!-- Card 3: Notifications -->
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

        <!-- Card 4: Events -->
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

        <!-- Card 5: Payment Alerts -->
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
