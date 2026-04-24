<x-filament-widgets::widget>
    <x-filament::section>
        <form wire:submit="sendNotification">
            {{ $this->form }}

            <div class="mt-4 flex justify-end">
                <x-filament::button type="submit">
                    Envoyer la Notification
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-widgets::widget>
