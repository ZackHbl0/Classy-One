<x-filament-panels::page>

    {{-- ── Selector bar ─────────────────────────────────────────────── --}}
    <div class="px-6 py-4 mb-6 bg-white rounded-xl shadow-none ring-1 ring-gray-100 dark:bg-gray-900 dark:ring-white/10">
        <label class="block mb-1.5 text-[9px] font-medium tracking-[0.15em] uppercase text-gray-400 dark:text-gray-500">
            Sélectionner une classe
        </label>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <div class="relative flex-grow max-w-lg">
                <select wire:model.live="classe_id"
                    class="w-full rounded-lg border border-gray-200 bg-white py-1.5 px-3 text-xs text-gray-700 focus:border-primary-400 focus:ring-1 focus:ring-primary-400 dark:bg-white/5 dark:text-white dark:border-white/10">
                    <option value="">-- Choisis une classe --</option>
                    @foreach ($this->classes as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <button wire:click="loadPlanning" type="button"
                class="inline-flex items-center px-5 py-1.5 text-xs font-medium text-primary-600 bg-white border border-primary-300 rounded-lg hover:bg-primary-50 transition-colors duration-150 whitespace-nowrap dark:bg-transparent dark:text-primary-400 dark:border-primary-400 dark:hover:bg-primary-500/10">
                Charger le planning
            </button>
        </div>
    </div>

    @if ($classe_id)

        {{-- ── 3-column grid using semantic CSS classes (defined in AdminPanelProvider HEAD_END) ── --}}
        <div class="planning-grid">

            @foreach ($this->plannings as $day => $courses)
                <div class="planning-day-card">

                    {{-- Day header --}}
                    <div class="planning-day-header">
                        <span class="planning-day-title">{{ $day }}</span>
                        <span class="planning-day-count">{{ count($courses) }}</span>
                    </div>

                    {{-- Course tiles --}}
                    <div class="planning-courses">
                        @forelse($courses as $course)
                            <div class="planning-course-tile">

                                {{-- Trash icon — wire:click fires the Livewire deleteCourse method --}}
                                <button type="button" class="planning-trash-btn" title="Supprimer ce cours"
                                    wire:click="deleteCourse({{ $course->id }})"
                                    wire:confirm="Supprimer ce cours définitivement ?">
                                    <x-heroicon-o-trash />
                                </button>

                                {{-- Time --}}
                                <div class="planning-course-time">
                                    {{ $course->check_in ? \Carbon\Carbon::parse($course->check_in)->format('H:i') : '--:--' }}
                                    –
                                    {{ $course->check_out ? \Carbon\Carbon::parse($course->check_out)->format('H:i') : '--:--' }}
                                </div>

                                {{-- Subject name --}}
                                <div class="planning-course-name">
                                    {{ $course->matiere ?: 'Sans matière' }}
                                </div>

                                {{-- Teacher --}}
                                <div class="planning-course-meta">
                                    <x-heroicon-m-user />
                                    {{ $course->professeur_name ?: 'Non assigné' }}
                                </div>

                                {{-- Room --}}
                                <div class="planning-course-meta">
                                    <x-heroicon-m-building-office />
                                    {{ $course->salle ?: 'Pas de salle' }}
                                </div>

                            </div>

                        @empty
                            <div class="planning-empty">Aucun cours prévu.</div>
                        @endforelse
                    </div>

                </div>
            @endforeach
        </div>

    @endif

    <x-filament-actions::modals />
</x-filament-panels::page>
