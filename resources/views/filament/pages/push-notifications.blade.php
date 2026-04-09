<x-filament-panels::page>
    <x-filament-panels::form wire:submit="sendNotification">
        {{ $this->form }}

        <x-filament::button type="submit" color="primary">
            Envoyer la Notification
        </x-filament::button>
    </x-filament-panels::form>
</x-filament-panels::page>
