<x-filament-widgets::widget class="fi-wi-stats-overview">
    {{-- 2-Column Grid matching Image 2 --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

        <!-- Card 1: Students -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-5 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0 mr-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 p-3">
                    <x-heroicon-o-users class="w-7 h-7 text-emerald-500 dark:text-emerald-400" />
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-tight">Total Students</h4>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalStudents) }}</span>
                        <span class="text-xs font-semibold text-emerald-500">{{ $studentsGrowth }}</span>
                    </div>
                </div>
            </div>
            <!-- Sparkline (Green) -->
            <div class="hidden sm:block w-24 h-8 opacity-70">
                <svg viewBox="0 0 100 30" class="w-full h-full text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M0 25 C20 25, 30 10, 50 15 C70 20, 80 5, 100 10" />
                </svg>
            </div>
        </div>

        <!-- Card 2: Revenue -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-5 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0 mr-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 p-3">
                    <x-heroicon-o-banknotes class="w-7 h-7 text-emerald-500 dark:text-emerald-400" />
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-tight">Total Revenue</h4>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalRevenue, 2) }}</span>
                        <span class="text-xs font-semibold text-gray-400">MAD</span>
                    </div>
                    @if($pendingPaymentsSum > 0)
                        <div class="text-[10px] font-medium text-orange-400 mt-1">
                            {{ number_format($pendingPaymentsSum, 2) }} MAD pending
                        </div>
                    @endif
                </div>
            </div>
            <!-- Sparkline (Green) -->
            <div class="hidden sm:block w-24 h-8 opacity-70">
                <svg viewBox="0 0 100 30" class="w-full h-full text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M0 20 C20 20, 30 5, 50 15 C70 25, 80 10, 100 5" />
                </svg>
            </div>
        </div>

        <!-- Card 3: Notifications -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-5 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0 mr-4 rounded-xl bg-blue-50 dark:bg-blue-900/30 p-3">
                    <x-heroicon-o-bell class="w-7 h-7 text-blue-500 dark:text-blue-400" />
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-tight">Notifications Sent</h4>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalNotifications) }}</span>
                        <span class="text-xs font-semibold text-emerald-500">{{ $notificationsGrowth }}</span>
                    </div>
                </div>
            </div>
            <!-- Sparkline (Blue) -->
            <div class="hidden sm:block w-24 h-8 opacity-70">
                <svg viewBox="0 0 100 30" class="w-full h-full text-blue-500" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M0 15 C20 15, 30 25, 50 10 C70 -5, 80 20, 100 15" />
                </svg>
            </div>
        </div>

        <!-- Card 4: Events -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-5 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0 mr-4 rounded-xl bg-purple-50 dark:bg-purple-900/30 p-3">
                    <x-heroicon-o-calendar class="w-7 h-7 text-purple-500 dark:text-purple-400" />
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-tight">Upcoming Events</h4>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($upcomingEvents) }}</span>
                        <span class="text-xs font-semibold text-gray-400">Next: {{ $nextEventDate }}</span>
                    </div>
                </div>
            </div>
            <!-- Sparkline (Purple) -->
            <div class="hidden sm:block w-24 h-8 opacity-70">
                <svg viewBox="0 0 100 30" class="w-full h-full text-purple-500" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M0 10 C20 10, 30 20, 50 15 C70 10, 80 5, 100 20" />
                </svg>
            </div>
        </div>

        <!-- Card 5: Payment Alerts -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-5 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0 mr-4 rounded-xl bg-orange-50 dark:bg-orange-900/30 p-3">
                    <x-heroicon-o-credit-card class="w-7 h-7 text-orange-500 dark:text-orange-400" />
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-tight">Payment Alerts</h4>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($overduePayments) }}</span>
                        <span class="text-xs font-semibold text-orange-500">{{ number_format($overduePayments) }} overdue</span>
                    </div>
                </div>
            </div>
            <!-- Sparkline (Orange) -->
            <div class="hidden sm:block w-24 h-8 opacity-70">
                <svg viewBox="0 0 100 30" class="w-full h-full text-orange-500" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M0 25 C40 25, 60 25, 100 25" />
                </svg>
            </div>
        </div>

    </div>
</x-filament-widgets::widget>