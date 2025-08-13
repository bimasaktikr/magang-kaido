{{-- resources/views/filament/pages/application-documents.blade.php --}}
<x-filament::page>
    <div class="space-y-6">
        {{ $this->form }}

        <div>
            <x-filament::button wire:click="save" icon="heroicon-o-check-circle">
                Simpan Dokumen
            </x-filament::button>
        </div>
    </div>
</x-filament::page>
