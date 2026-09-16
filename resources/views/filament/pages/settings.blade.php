<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header Banner Card -->
        <div class="rounded-2xl p-6 relative overflow-hidden border border-emerald-900/10 shadow-sm"
             style="background: linear-gradient(135deg, #0f4c3a 0%, #166534 60%, #15803d 100%);">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="text-white">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-400/20 text-emerald-100 border border-emerald-400/30">
                            Administration Système
                        </span>
                    </div>
                    <h2 class="text-2xl font-bold tracking-tight text-white">Paramètres de l'Établissement</h2>
                    <p class="text-sm text-emerald-100/90 mt-1 max-w-2xl">
                        Configurez le nom officiel de l'école, l'année scolaire active, la devise monétaire, le logo institutionnel et les préférences du système.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-white/10 backdrop-blur-md rounded-xl border border-white/20 text-white shadow-inner hidden md:block">
                        <x-heroicon-o-cog-6-tooth class="w-8 h-8 text-emerald-100 animate-spin-slow" />
                    </div>
                </div>
            </div>
            <!-- Decorative circle -->
            <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>
        </div>

        <!-- Form Schema -->
        <form wire:submit.prevent="save">
            {{ $this->form }}

            <div class="mt-6 flex justify-end">
                <x-filament::button
                    type="submit"
                    size="lg"
                    icon="heroicon-o-check-circle"
                    style="background: linear-gradient(90deg, #0f4c3a 0%, #1a8f6a 100%);"
                    class="font-semibold shadow-md hover:shadow-lg transition-all"
                >
                    Enregistrer les modifications
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
