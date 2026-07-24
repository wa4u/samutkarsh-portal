<x-filament-panels::page>
    <form wire:submit="send" class="space-y-6">
        {{ $this->form }}

        <div>
            <x-filament::button type="submit" wire:confirm="Send this reminder email now?">
                Send reminder
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
