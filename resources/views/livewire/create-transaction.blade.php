<div style="width: 30%; padding: 0 10px">
    <form wire:submit="create">
        {{ $this->form }}
    </form>
    
    <x-filament-actions::modals />
</div>