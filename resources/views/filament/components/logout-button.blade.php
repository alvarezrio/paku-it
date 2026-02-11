<div class="flex items-center gap-4">
    <form action="{{ filament()->getLogoutUrl() }}" method="post" class="flex items-center">
        @csrf
        <x-filament::button 
            type="submit" 
            color="gray" 
            icon="heroicon-m-arrow-right-start-on-rectangle"
            labeled-from="sm"
            tag="button"
        >
            Keluar
        </x-filament::button>
    </form>
</div>
