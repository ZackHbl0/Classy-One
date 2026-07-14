<x-filament-panels::page>
    <div class="flex justify-between items-center mb-6">
        <div>
            <!-- Removing duplicate visual title since Filament page title already provides this context -->
        </div>
        <div>
            {{ $this->createGradeAction }}
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($this->grades as $grade)
            <div
                class="bg-white dark:bg-gray-800 border {{ match ($grade->color) {'success' => 'border-success-200','info' => 'border-info-200','primary' => 'border-primary-200','warning' => 'border-warning-200','danger' => 'border-danger-200',default => 'border-gray-200'} }} rounded-2xl shadow-sm p-6 relative overflow-hidden flex flex-col justify-between group transition hover:shadow-md">

                <!-- Top border indicator logic -->
                <div
                    class="absolute top-0 left-0 w-1.5 h-full {{ match ($grade->color) {'success' => 'bg-success-500','info' => 'bg-info-500','primary' => 'bg-primary-500','warning' => 'bg-warning-500','danger' => 'bg-danger-500',default => 'bg-gray-500'} }}">
                </div>

                <div class="ml-2">
                    <div class="flex justify-between items-start mb-5">
                        <div class="flex items-start gap-4">
                            <div class="bg-primary-50 dark:bg-gray-700 p-3 rounded-xl text-primary-600 shadow-sm">
                                <x-heroicon-s-book-open class="w-6 h-6" />
                            </div>
                            <div class="flex flex-col pt-1">
                                <h3
                                    class="font-extrabold text-gray-900 dark:text-white line-clamp-2 leading-tight pr-2">
                                    {{ $grade->subject_name ?? 'N/A' }}</h3>
                            </div>
                        </div>
                        <div class="text-right whitespace-nowrap pl-3">
                            <div
                                class="text-2xl font-black {{ match ($grade->color) {'success' => 'text-success-600','info' => 'text-info-600','primary' => 'text-primary-600','warning' => 'text-warning-600','danger' => 'text-danger-600',default => 'text-gray-900'} }}">
                                {{ $grade->formatted_note }} <span class="text-base font-bold opacity-60">/ 20</span>
                            </div>
                            <div
                                class="text-xs font-bold inline-block mt-1 {{ match ($grade->color) {'success' => 'text-success-600','info' => 'text-info-600','primary' => 'text-primary-600','warning' => 'text-warning-600','danger' => 'text-danger-600',default => ''} }}">
                                @if ($grade->note >= 10)
                                    <x-heroicon-s-star class="w-3.5 h-3.5 inline-block -mt-0.5" />
                                @endif
                                {{ $grade->status }}
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 mb-6 mt-2">
                        <span
                            class="px-3 py-1 rounded-lg text-xs font-bold border border-info-200 bg-info-50 text-info-700 flex items-center gap-1.5 shadow-sm">
                            <x-heroicon-o-document-text class="w-4 h-4" /> {{ $grade->type }}
                        </span>
                        <span
                            class="px-3 py-1 rounded-lg text-xs font-bold border border-success-200 bg-success-50 text-success-700 shadow-sm">
                            {{ $grade->classe?->nomClasse ?? ($this->record->registres->last()?->classe?->nomClasse ?? 'Classe') }}
                        </span>
                        <span
                            class="px-3 py-1 rounded-lg text-xs font-bold border border-primary-200 bg-primary-50 text-primary-700 shadow-sm">
                            {{ $grade->semester }}
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-between text-sm text-gray-500 pt-5 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-1.5 font-medium">
                            <x-heroicon-o-calendar class="w-5 h-5 text-gray-400" />
                            <span>{{ $grade->exam_date?->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <button wire:click="mountAction('editGrade', { grade_id: {{ $grade->id }} })"
                                class="h-10 w-10 rounded-full bg-primary-50 text-primary-600 flex items-center justify-center hover:bg-primary-100 hover:text-primary-700 transition shadow-sm">
                                <x-heroicon-s-pencil class="w-5 h-5" />
                            </button>
                            <button wire:click="mountAction('deleteGrade', { grade_id: {{ $grade->id }} })"
                                class="h-10 w-10 rounded-full bg-danger-50 text-danger-600 flex items-center justify-center hover:bg-danger-100 hover:text-danger-700 transition shadow-sm">
                                <x-heroicon-s-trash class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        @if ($this->grades->isEmpty())
            <div
                class="col-span-1 md:col-span-2 lg:col-span-3 text-center py-16 text-gray-500 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 flex flex-col items-center justify-center">
                <x-heroicon-o-document-magnifying-glass class="w-16 h-16 text-gray-300 mb-4" />
                <p class="font-bold text-xl text-gray-600">Aucune note assignée</p>
                <p class="text-base mt-2">Cet étudiant n'a pas encore reçu de notes. Utilisez le bouton "Ajouter une
                    Note" pour commencer.</p>
            </div>
        @endif
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
