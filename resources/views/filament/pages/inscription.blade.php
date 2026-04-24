<x-filament-panels::page>
    <form wire:submit.prevent="register">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            @foreach ($this->getFormActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </form>
</x-filament-panels::page>
