<x-filament-panels::page>
    <style>
        /* ── Design System for Financial Audit & Reports (Polished & Resilient) ── */
        :root {
            --fa-card-bg: #ffffff;
            --fa-card-border: #e2e8f0;
            --fa-text-title: #0f172a;
            --fa-text-body: #334155;
            --fa-text-muted: #64748b;
            --fa-subtle-bg: #f8fafc;
            --fa-hover-bg: #f1f5f9;
            --fa-table-th-bg: #f8fafc;
            --fa-table-border: #e2e8f0;
            --fa-row-hover: #f8fafc;
        }

        .dark {
            --fa-card-bg: #1e293b;
            --fa-card-border: #334155;
            --fa-text-title: #f8fafc;
            --fa-text-body: #cbd5e1;
            --fa-text-muted: #94a3b8;
            --fa-subtle-bg: #0f172a;
            --fa-hover-bg: #334155;
            --fa-table-th-bg: #0f172a;
            --fa-table-border: #334155;
            --fa-row-hover: rgba(51, 65, 85, 0.4);
        }

        /* Modern Smooth Rounded Container Card */
        .fa-card {
            background-color: var(--fa-card-bg) !important;
            border: 1px solid var(--fa-card-border) !important;
            border-radius: 1.25rem !important; /* 20px */
            padding: 1.25rem !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05) !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease !important;
        }

        .fa-card-interactive:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.05) !important;
        }

        /* 4-Columns KPI Responsive Grid */
        .fa-kpi-grid {
            display: grid !important;
            grid-template-columns: 1fr !important;
            gap: 1rem !important;
            margin-top: 1.25rem !important;
            margin-bottom: 1.25rem !important;
        }
        @media (min-width: 640px) {
            .fa-kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
        }
        @media (min-width: 1024px) {
            .fa-kpi-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            }
        }

        /* Middle Section: 12-Month Calendar (2 cols) + Methods Breakdown (1 col) */
        .fa-middle-grid {
            display: grid !important;
            grid-template-columns: 1fr !important;
            gap: 1.5rem !important;
            margin-bottom: 1.5rem !important;
        }
        @media (min-width: 1024px) {
            .fa-middle-grid {
                grid-template-columns: 2fr 1fr !important;
            }
        }

        /* 12 Months Grid */
        .fa-months-grid {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 0.75rem !important;
        }
        @media (min-width: 640px) {
            .fa-months-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            }
        }
        @media (min-width: 768px) {
            .fa-months-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            }
        }

        /* Month Item Block */
        .fa-month-card {
            border-radius: 0.875rem !important;
            padding: 0.875rem !important;
            border: 1px solid var(--fa-card-border) !important;
            background-color: var(--fa-subtle-bg) !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            text-align: left !important;
            position: relative !important;
            overflow: hidden !important;
        }

        .fa-month-card:hover {
            background-color: var(--fa-hover-bg) !important;
            border-color: #10b981 !important;
            transform: translateY(-2px);
        }

        .fa-month-card.active-month {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.12) 0%, rgba(5, 150, 105, 0.22) 100%) !important;
            border: 2px solid #10b981 !important;
            box-shadow: 0 4px 12px -2px rgba(16, 185, 129, 0.25) !important;
            transform: scale(1.02);
        }

        /* Filter Select Inputs (Completely eliminates white-on-white text issues) */
        .fa-filter-input {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border: 1.5px solid #10b981 !important;
            border-radius: 0.75rem !important;
            padding: 0.5rem 2.25rem 0.5rem 0.85rem !important;
            font-size: 0.8125rem !important;
            font-weight: 700 !important;
            outline: none !important;
            cursor: pointer !important;
            width: 100% !important;
            height: 2.5rem !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23047857' stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
            background-position: right 0.65rem center !important;
            background-repeat: no-repeat !important;
            background-size: 1.15rem 1.15rem !important;
        }

        .fa-filter-input:focus {
            border-color: #047857 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.35) !important;
        }

        /* Native Option tags: Explicitly dark slate text on pure white background on all browsers */
        .fa-filter-input option {
            background-color: #ffffff !important;
            color: #0f172a !important;
            padding: 8px 12px !important;
            font-size: 0.8125rem !important;
            font-weight: 600 !important;
        }

        /* Hero Filter Container (Rounded smooth corners, no white lines) */
        .fa-filter-container {
            display: flex !important;
            flex-wrap: wrap !important;
            align-items: center !important;
            gap: 0.875rem !important;
            background-color: rgba(0, 0, 0, 0.28) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border-radius: 1.25rem !important; /* border-radius moderne et lisse (20px) */
            border: none !important; /* supprime la bordure blanche */
            border-top: none !important; /* supprime explicitement toute ligne blanche en haut */
            padding: 0.875rem 1.25rem !important;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.25) !important;
        }

        .fa-filter-label {
            display: block !important;
            font-size: 0.6875rem !important;
            text-transform: uppercase !important;
            font-weight: 700 !important;
            letter-spacing: 0.05em !important;
            color: #a7f3d0 !important; /* Vert émeraude clair très lisible */
            margin-bottom: 0.375rem !important;
        }

        /* Progress Bar */
        .fa-progress-bar-track {
            width: 100%;
            height: 0.625rem;
            border-radius: 9999px;
            background-color: #e2e8f0;
            overflow: hidden;
        }
        .dark .fa-progress-bar-track {
            background-color: #334155;
        }

        .fa-progress-bar-fill {
            height: 100%;
            border-radius: 9999px;
            transition: width 0.5s ease-in-out;
        }
    </style>

    @php
        $data = $this->financialData;
        $availableYears = $this->availableYears;
        $monthsList = $this->monthsList;
        $selectedMonth = $this->selectedMonth;
    @endphp

    <div class="space-y-6">
        <!-- Top Hero Banner Card with Smooth Rounded Borders -->
        <div class="rounded-2xl p-6 sm:p-8 relative overflow-hidden border border-emerald-900/10 shadow-sm"
             style="background: linear-gradient(135deg, #062b1f 0%, #0c4a35 45%, #047857 100%);">
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="text-white space-y-2 max-w-2xl">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-400/20 text-emerald-100 border border-emerald-400/30 backdrop-blur-md">
                            Direction & Audit Comptable
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-xs text-emerald-200/90 font-medium">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            Exercice {{ $data['year'] }} — {{ $data['selected_month_name'] }}
                        </span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                        Audit & Rapports Financiers
                    </h2>
                    <p class="text-xs sm:text-sm text-emerald-100/90 leading-relaxed">
                        Surveillez en temps réel le recouvrement des frais de scolarité, la distribution des bourses d'études, la ventilation par mode d'encaissement et le solde des créances scolaires.
                    </p>
                </div>

                <!-- Year & Month Selectors with Crisp Legibility in All Modes -->
                <div class="fa-filter-container">
                    <!-- Year Filter -->
                    <div style="min-width: 130px;">
                        <label class="fa-filter-label">
                            Année Fiscale
                        </label>
                        <select wire:model.live="selectedYear" class="fa-filter-input">
                            @foreach($availableYears as $yearOption)
                                <option value="{{ $yearOption }}">Année {{ $yearOption }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Month Filter -->
                    <div style="min-width: 170px;">
                        <label class="fa-filter-label">
                            Période / Mois
                        </label>
                        <select wire:model.live="selectedMonth" class="fa-filter-input">
                            <option value="">Tous les mois (Année entière)</option>
                            @foreach($monthsList as $num => $name)
                                <option value="{{ $num }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Decorative Ambient Glow -->
            <div class="absolute -right-10 -bottom-10 w-56 h-56 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute left-1/2 -top-12 w-48 h-48 bg-emerald-400/10 rounded-full blur-2xl pointer-events-none"></div>
        </div>

        <!-- 4 KPI Cards Grid with Modern Smooth Rounded Corners -->
        <div class="fa-kpi-grid">
            <!-- 1. Encaissé (Annuel / Période) -->
            <div class="fa-card fa-card-interactive relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        @if($data['selected_month'])
                            Encaissé ({{ $data['selected_month_name'] }})
                        @else
                            Recouvrement {{ $data['year'] }}
                        @endif
                    </span>
                    <span class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40">
                        <x-heroicon-o-banknotes class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-gray-900 dark:text-white">
                        {{ number_format($data['month_collected'], 2, ',', ' ') }} <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">MAD</span>
                    </div>
                    <div class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold mt-1.5 flex items-center gap-1">
                        <x-heroicon-m-check-badge class="w-4 h-4 shrink-0" />
                        <span>Total annuel : {{ number_format($data['year_collected'], 2, ',', ' ') }} MAD</span>
                    </div>
                </div>
            </div>

            <!-- 2. Reste à Recouvrer (Créances) -->
            <div class="fa-card fa-card-interactive relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Créances Scolaires
                    </span>
                    <span class="p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/70 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/40">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-rose-600 dark:text-rose-400">
                        {{ number_format($data['total_remaining_balance'], 2, ',', ' ') }} <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">MAD</span>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1.5">
                        Scolarité nette exigible : {{ number_format($data['total_target_tuition'], 0, ',', ' ') }} MAD
                    </div>
                </div>
            </div>

            <!-- 3. Bourses & Remises Accordées -->
            <div class="fa-card fa-card-interactive relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Bourses & Réductions
                    </span>
                    <span class="p-2.5 rounded-xl bg-blue-50 dark:bg-blue-950/70 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/40">
                        <x-heroicon-o-academic-cap class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-blue-600 dark:text-blue-400">
                        {{ number_format($data['total_discounts_granted'], 2, ',', ' ') }} <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">MAD</span>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1.5">
                        Tarif brut catalogue : {{ number_format($data['total_base_tuition'], 0, ',', ' ') }} MAD
                    </div>
                </div>
            </div>

            <!-- 4. Taux Global d'Encaissement -->
            <div class="fa-card fa-card-interactive relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Taux de Recouvrement
                    </span>
                    <span class="p-2.5 rounded-xl bg-purple-50 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400 border border-purple-100 dark:border-purple-900/40">
                        <x-heroicon-o-chart-pie class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-purple-600 dark:text-purple-400">
                        {{ $data['collection_rate'] }}%
                    </div>
                    <div class="fa-progress-bar-track mt-2">
                        <div class="fa-progress-bar-fill bg-purple-600 dark:bg-purple-500" style="width: {{ min(100, $data['collection_rate']) }}%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Middle Section: 12-Months Grid & Payment Method Breakdown -->
        <div class="fa-middle-grid">
            <!-- Monthly breakdown cards (2 cols) -->
            <div class="fa-card space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 dark:border-gray-800 pb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span>Évolution Mensuelle des Encaissements</span>
                            <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-extrabold">
                                {{ $data['year'] }}
                            </span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Cliquez sur un mois pour filtrer et isoler ses encaissements
                        </p>
                    </div>

                    @if($data['selected_month'])
                        <button type="button" wire:click="filterByMonth({{ $data['selected_month'] }})"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                            <x-heroicon-m-x-mark class="w-4 h-4" />
                            <span>Réinitialiser (Voir toute l'année)</span>
                        </button>
                    @endif
                </div>

                <!-- 12 Months Grid Blocks with Modern Smooth Rounded Corners -->
                <div class="fa-months-grid">
                    @foreach($data['monthly_revenue'] as $m)
                        @php
                            $isSelected = $m['is_selected'];
                            $hasRevenue = $m['amount'] > 0;
                        @endphp
                        <div wire:click="filterByMonth({{ $m['month_num'] }})"
                             class="fa-month-card {{ $isSelected ? 'active-month' : '' }}">
                            
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold {{ $isSelected ? 'text-emerald-800 dark:text-emerald-300' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ $m['month_name'] }}
                                </span>
                                @if($isSelected)
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                @endif
                            </div>

                            <div class="text-sm sm:text-base font-black mt-2 {{ $hasRevenue ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }}">
                                {{ number_format($m['amount'], 0, ',', ' ') }}
                                <span class="text-[10px] font-normal text-gray-500 dark:text-gray-400">MAD</span>
                            </div>

                            <div class="text-[10px] font-medium mt-1 {{ $hasRevenue ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-gray-400 dark:text-gray-500' }}">
                                {{ $hasRevenue ? 'Encaissé' : 'Aucun flux' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Payment Methods Breakdown with Smooth Rounded Borders (1 col) -->
            <div class="fa-card space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-800 pb-4">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">
                        Modes de Règlement
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Ventilation des encaissements par instrument
                    </p>
                </div>

                <div class="space-y-3">
                    @forelse($data['payment_methods'] as $method => $info)
                        @php
                            $pct = $data['total_collected_all_time'] > 0 
                                ? round(($info['amount'] / $data['total_collected_all_time']) * 100, 1) 
                                : 0;
                        @endphp
                        <div class="p-3.5 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-800/40 hover:bg-gray-100/60 dark:hover:bg-gray-800/70 transition">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-extrabold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full {{ match($method) { 'Espèce' => 'bg-amber-500', 'Chèque' => 'bg-blue-500', 'Virement' => 'bg-emerald-500', default => 'bg-gray-400' } }}"></span>
                                    {{ $method }}
                                </span>
                                <span class="text-xs font-black text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($info['amount'], 2, ',', ' ') }} MAD
                                </span>
                            </div>

                            <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 mb-1.5">
                                <span>{{ $info['count'] }} transaction(s)</span>
                                <span class="font-bold">{{ $pct }}%</span>
                            </div>

                            <!-- Visual indicator bar -->
                            <div class="fa-progress-bar-track" style="height: 0.375rem;">
                                <div class="fa-progress-bar-fill {{ match($method) { 'Espèce' => 'bg-amber-500', 'Chèque' => 'bg-blue-500', 'Virement' => 'bg-emerald-500', default => 'bg-gray-400' } }}"
                                     style="width: {{ min(100, $pct) }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-xs text-gray-400 dark:text-gray-500 italic py-6 text-center">
                            Aucun versement enregistré sur cette période.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Transactions Card with Rounded Table & Dark Mode Contrast -->
        <div class="fa-card space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 dark:border-gray-800 pb-4">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">
                        Dernières Transactions Enregistrées
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Historique des versements avec accès direct aux quittances et reçus officiels signés
                    </p>
                </div>
                <a href="{{ route('filament.admin.resources.paiements.index') }}"
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition">
                    <span>Ouvrir la gestion des paiements</span>
                    <x-heroicon-m-arrow-right class="w-3.5 h-3.5" />
                </a>
            </div>

            <!-- Table with Rounded Borders & Clean Dark Mode -->
            <div class="overflow-x-auto rounded-xl border border-gray-200/80 dark:border-gray-800">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/70 border-b border-gray-200/80 dark:border-gray-800 text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider text-[11px]">
                            <th class="py-3 px-4">Réf. Unique</th>
                            <th class="py-3 px-4">Élève (Matricule)</th>
                            <th class="py-3 px-4">Montant</th>
                            <th class="py-3 px-4">Mode</th>
                            <th class="py-3 px-4">Échéance</th>
                            <th class="py-3 px-4">Statut</th>
                            <th class="py-3 px-4 text-right">Quittance PDF</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800/80 bg-white dark:bg-gray-900">
                        @forelse($data['recent_transactions'] as $tx)
                            @php
                                $st = mb_strtolower(trim($tx['statut'] ?? ''));
                                $isPaye = in_array($st, ['payé', 'paye', 'validé', 'valide']);
                            @endphp
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-gray-700 dark:text-gray-300">
                                    <span class="px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-[11px] border border-gray-200/60 dark:border-gray-700">
                                        {{ $tx['reference'] ?? 'REF-N/A' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-gray-900 dark:text-white">
                                    {{ $tx['student_name'] }}
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 block font-normal">{{ $tx['matricule'] }}</span>
                                </td>
                                <td class="py-3.5 px-4 font-black text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($tx['montant'], 2, ',', ' ') }} MAD
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200/50 dark:border-gray-700">
                                        {{ $tx['modePaiement'] ?? 'Espèce' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-gray-600 dark:text-gray-400">
                                    {{ $tx['dateEcheance'] ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $isPaye 
                                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' 
                                        : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800' }}">
                                        {{ $tx['statut'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('paiements.receipt.download', ['id' => $tx['id']]) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white shadow-sm transition-all hover:shadow-md hover:scale-[1.02]"
                                       style="background: linear-gradient(90deg, #062b1f 0%, #047857 100%);">
                                        <x-heroicon-m-arrow-down-tray class="w-3.5 h-3.5" />
                                        <span>Reçu PDF</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-gray-400 dark:text-gray-500 italic">
                                    Aucun versement trouvé pour les critères sélectionnés.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
