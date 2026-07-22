<x-filament-panels::page>

    {{-- ── Selector bar ─────────────────────────────────────────────── --}}
    <div class="planning-selector">
        <label for="classe_id" class="planning-selector-label">
            Sélectionner une classe
        </label>

        <div style="position: relative; display: flex; align-items: center;">
            <div class="planning-selector-icon">
                <x-heroicon-o-academic-cap />
            </div>

            <select wire:model.live="classe_id" id="classe_id" class="planning-selector-input">
                <option value="">-- Choisis une classe pour afficher son planning --</option>
                @foreach ($this->classes as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>

            <div class="planning-selector-chevron">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                        clip-rule="evenodd" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Injected custom CSS for the new layout -->
    <style>
        .planning-selector {
            background-color: white;
            border-radius: 1rem;
            border: 1px solid #f3f4f6;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            width: 100%;
        }

        .dark .planning-selector {
            background-color: #18181b;
            border-color: #27272a;
        }

        .planning-selector-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .dark .planning-selector-label {
            color: #d4d4d8;
        }

        .planning-selector-icon {
            position: absolute;
            left: 1rem;
            color: #6b7280;
            pointer-events: none;
            width: 1.25rem;
            height: 1.25rem;
        }

        .dark .planning-selector-icon {
            color: #a1a1aa;
        }

        .planning-selector-input {
            width: 100%;
            appearance: none;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 0.75rem 2.5rem 0.75rem 2.75rem;
            font-size: 0.95rem;
            font-weight: 500;
            color: #1f2937;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            cursor: pointer;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.02);
        }

        .dark .planning-selector-input {
            background-color: #27272a;
            border-color: #3f3f46;
            color: #f4f4f5;
        }

        .planning-selector-chevron {
            position: absolute;
            right: 1rem;
            color: #9ca3af;
            pointer-events: none;
            width: 1rem;
            height: 1rem;
        }

        .dark .planning-selector-chevron {
            color: #a1a1aa;
        }

        .tab-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .tab-container::-webkit-scrollbar {
            display: none;
        }

        .tab-btn {
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 700;
            border-radius: 1rem;
            white-space: nowrap;
            transition: all 0.2s;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .tab-btn-active {
            background-color: #0f4c3a;
            color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .tab-btn-inactive {
            background-color: white;
            color: #4b5563;
            border-color: #e5e7eb;
        }

        .tab-btn-inactive:hover {
            background-color: #f9fafb;
        }

        .dark .tab-btn-inactive {
            background-color: #18181b;
            color: #a1a1aa;
            border-color: #27272a;
        }

        .dark .tab-btn-inactive:hover {
            background-color: #27272a;
        }

        .day-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #374151;
            margin: 0;
        }

        .dark .day-title {
            color: #f4f4f5;
        }

        .course-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .course-card {
            position: relative;
            display: flex;
            background-color: white;
            border-radius: 1rem;
            border: 1px solid #f3f4f6;
            padding: 1.25rem;
            align-items: center;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: box-shadow 0.2s;
        }

        .course-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .dark .course-card {
            background-color: #18181b;
            border-color: #27272a;
        }

        .course-actions {
            position: absolute;
            top: 0.875rem;
            right: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .course-action-btn {
            color: #9ca3af;
            transition: all 0.2s;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0.375rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            outline: none;
        }

        .course-action-btn:hover {
            background-color: #f3f4f6;
        }

        .dark .course-action-btn:hover {
            background-color: #27272a;
        }

        .course-edit-btn:hover {
            color: #3b82f6;
        }

        .course-trash-btn:hover {
            color: #ef4444;
        }

        .course-action-btn svg {
            width: 1.15rem;
            height: 1.15rem;
        }

        .course-time-col {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding-right: 1.5rem;
            border-right: 2px solid #3b82f6;
            min-width: 90px;
        }

        .course-time-start {
            font-size: 1.125rem;
            font-weight: 700;
            color: #111827;
            line-height: 1.2;
            margin: 0;
        }

        .dark .course-time-start {
            color: #f4f4f5;
        }

        .course-time-end {
            font-size: 0.75rem;
            font-weight: 500;
            color: #9ca3af;
            margin-top: 0.25rem;
        }

        .course-details-col {
            padding-left: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .course-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1f2937;
            letter-spacing: 0.025em;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
            margin-top: 0;
        }

        .dark .course-title {
            color: #f4f4f5;
        }

        .course-meta-row {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 500;
        }

        .course-meta-item {
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .course-meta-item svg {
            width: 1rem;
            height: 1rem;
            opacity: 0.7;
        }

        .course-empty {
            padding: 3rem 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: white;
            border-radius: 1rem;
            border: 1px dashed #e5e7eb;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .dark .course-empty {
            background-color: #18181b;
            border-color: #3f3f46;
        }

        .course-empty svg {
            width: 3rem;
            height: 3rem;
            color: #d1d5db;
            margin-bottom: 0.75rem;
        }

        .course-empty-text {
            color: #6b7280;
            font-weight: 500;
            font-size: 0.875rem;
        }
    </style>

    @if ($classe_id)

        <div x-data="{ activeTab: 'Lundi' }" style="margin-top: 2rem;">
            {{-- Tabs --}}
            <div class="tab-container">
                @foreach (['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'] as $day)
                    <button @click="activeTab = '{{ $day }}'"
                        :class="activeTab === '{{ $day }}' ? 'tab-btn tab-btn-active' : 'tab-btn tab-btn-inactive'">
                        {{ strtoupper(substr($day, 0, 3)) }}
                    </button>
                @endforeach
            </div>

            {{-- Content --}}
            <div>
                @foreach ($this->plannings as $day => $courses)
                    <div x-show="activeTab === '{{ $day }}'" x-cloak>

                        <div style="margin-bottom: 1.5rem;">
                            <h2 class="day-title">{{ $day }}</h2>
                        </div>

                        <div class="course-list">
                            @forelse($courses as $course)
                                <div class="course-card">

                                    {{-- Actions --}}
                                    <div class="course-actions">
                                        <a href="{{ \App\Filament\Resources\PlanningResource::getUrl('edit', ['record' => $course->id]) }}"
                                            class="course-action-btn course-edit-btn" title="Modifier ce cours">
                                            <x-heroicon-o-pencil-square />
                                        </a>
                                        <button type="button" class="course-action-btn course-trash-btn"
                                            title="Supprimer ce cours" wire:click="deleteCourse({{ $course->id }})"
                                            wire:confirm="Supprimer ce cours définitivement ?">
                                            <x-heroicon-o-trash />
                                        </button>
                                    </div>

                                    {{-- Time --}}
                                    <div class="course-time-col">
                                        <span class="course-time-start">
                                            {{ $course->check_in ? \Carbon\Carbon::parse($course->check_in)->format('H:i') : '--:--' }}
                                        </span>
                                        <span class="course-time-end">
                                            {{ $course->check_out ? \Carbon\Carbon::parse($course->check_out)->format('H:i') : '--:--' }}
                                        </span>
                                    </div>

                                    {{-- Details --}}
                                    <div class="course-details-col">
                                        <h3 class="course-title">
                                            {{ $course->matiere ?: 'Sans matière' }}
                                        </h3>
                                        <div class="course-meta-row">
                                            <span class="course-meta-item">
                                                <x-heroicon-o-user />
                                                {{ $course->professeur_name ?: 'Non assigné' }}
                                            </span>
                                            <span class="course-meta-item">
                                                <x-heroicon-o-map-pin />
                                                {{ $course->salle ?: 'Pas de salle' }}
                                            </span>
                                        </div>
                                    </div>

                                </div>
                            @empty
                                <div class="course-empty">
                                    <x-heroicon-o-calendar />
                                    <span class="course-empty-text">Aucun cours prévu pour ce jour.</span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <x-filament-actions::modals />
</x-filament-panels::page>
