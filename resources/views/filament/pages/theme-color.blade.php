<x-filament-panels::page>
    {{-- Warna Aksen --}}
    <x-filament::section>
        <x-slot name="heading">Warna Aksen</x-slot>
        <x-slot name="description">Tombol, link aktif, dan elemen interaktif.</x-slot>

        <div class="flex flex-wrap gap-3">
            @foreach($this->getPrimaryColors() as $key => $color)
                <button
                    wire:click="selectColor('{{ $key }}')"
                    wire:loading.attr="disabled"
                    title="{{ $color['label'] }}"
                    class="relative w-8 h-8 rounded-full shadow-sm transition-all duration-150 hover:scale-110 focus:outline-none
                        {{ $selectedColor === $key ? 'ring-2 ring-offset-2 ring-gray-500 dark:ring-gray-300 scale-110' : '' }}"
                    style="background-color: {{ $color['hex'] }}"
                >
                    @if($selectedColor === $key)
                        <span class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white drop-shadow" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                    @endif
                </button>
            @endforeach
        </div>

        <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">
            Aktif: <span class="font-medium text-gray-600 dark:text-gray-300">{{ $this->getPrimaryColors()[$selectedColor]['label'] ?? 'Amber' }}</span>
        </p>
    </x-filament::section>

    {{-- Tone Dasar --}}
    <x-filament::section>
        <x-slot name="heading">Tone Dasar</x-slot>
        <x-slot name="description">Nuansa background, sidebar, dan card.</x-slot>

        <div class="flex flex-wrap gap-3">
            @foreach($this->getGrayTones() as $key => $tone)
                <button
                    wire:click="selectGray('{{ $key }}')"
                    wire:loading.attr="disabled"
                    title="{{ $tone['label'] }}"
                    class="relative w-8 h-8 rounded-full shadow-sm transition-all duration-150 hover:scale-110 focus:outline-none
                        {{ $selectedGray === $key ? 'ring-2 ring-offset-2 ring-gray-500 dark:ring-gray-300 scale-110' : '' }}"
                    style="background-color: {{ $tone['shades'][$selectedLevel - 1] }}"
                >
                    @if($selectedGray === $key)
                        <span class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white drop-shadow" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                    @endif
                </button>
            @endforeach
        </div>

        <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">
            Aktif: <span class="font-medium text-gray-600 dark:text-gray-300">{{ $this->getGrayTones()[$selectedGray]['label'] ?? 'Slate' }}</span>
        </p>
    </x-filament::section>

    {{-- Intensitas --}}
    <x-filament::section>
        <x-slot name="heading">Intensitas</x-slot>
        <x-slot name="description">Tingkat kegelapan tone dasar.</x-slot>

        @php $toneShades = $this->getGrayTones()[$selectedGray]['shades'] ?? ['#cbd5e1','#64748b','#334155','#0f172a']; @endphp

        <div class="flex gap-3">
            @foreach($this->getIntensityLevels() as $level => $label)
                <button
                    wire:click="selectLevel({{ $level }})"
                    wire:loading.attr="disabled"
                    title="{{ $label }}"
                    class="flex flex-col items-center gap-1.5 focus:outline-none group"
                >
                    <span
                        class="w-8 h-8 rounded-full shadow-sm transition-all duration-150 group-hover:scale-110 flex items-center justify-center
                            {{ $selectedLevel === $level ? 'ring-2 ring-offset-2 ring-gray-500 dark:ring-gray-300 scale-110' : '' }}"
                        style="background-color: {{ $toneShades[$level - 1] }}"
                    >
                        @if($selectedLevel === $level)
                            <svg class="w-4 h-4 text-white drop-shadow" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        @endif
                    </span>
                    <span class="text-xs {{ $selectedLevel === $level ? 'font-semibold text-gray-700 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500' }}">
                        {{ $label }}
                    </span>
                </button>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-panels::page>
