<x-filament::page>
    <form wire:submit.prevent="submit" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-end">
            <x-filament::button type="submit">
                Zapisz zmiany
            </x-filament::button>
        </div>
    </form>
</x-filament::page>
