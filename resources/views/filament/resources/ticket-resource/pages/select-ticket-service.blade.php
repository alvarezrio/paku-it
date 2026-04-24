<x-filament-panels::page>
    {{-- Intro --}}
    <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2 mb-4">
        Pilih jenis layanan yang sesuai dengan kebutuhan Anda untuk melanjutkan perekaman tiket.
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
        @foreach($this->getServices() as $service)
            @php
                // Definisi warna per layanan — ditulis eksplisit agar Tailwind JIT tidak purge
                $palette = match($service['color']) {
                    'danger'  => [
                        'icon_bg'   => '#fee2e2',   // red-100
                        'icon_text' => '#dc2626',   // red-600
                        'tag_bg'    => '#fef2f2',
                        'tag_text'  => '#dc2626',
                        'dot'       => '#ef4444',
                    ],
                    'info' => [
                        'icon_bg'   => '#dbeafe',   // blue-100
                        'icon_text' => '#2563eb',   // blue-600
                        'tag_bg'    => '#eff6ff',
                        'tag_text'  => '#2563eb',
                        'dot'       => '#3b82f6',
                    ],
                    'success' => [
                        'icon_bg'   => '#dcfce7',   // green-100
                        'icon_text' => '#16a34a',   // green-600
                        'tag_bg'    => '#f0fdf4',
                        'tag_text'  => '#16a34a',
                        'dot'       => '#22c55e',
                    ],
                    'warning' => [
                        'icon_bg'   => '#fef3c7',   // amber-100
                        'icon_text' => '#d97706',   // amber-600
                        'tag_bg'    => '#fffbeb',
                        'tag_text'  => '#d97706',
                        'dot'       => '#f59e0b',
                    ],
                    'primary' => [
                        'icon_bg'   => '#fef3c7',
                        'icon_text' => '#b45309',   // amber-700
                        'tag_bg'    => '#fffbeb',
                        'tag_text'  => '#b45309',
                        'dot'       => '#f59e0b',
                    ],
                    default => [
                        'icon_bg'   => '#f3f4f6',   // gray-100
                        'icon_text' => '#4b5563',   // gray-600
                        'tag_bg'    => '#f9fafb',
                        'tag_text'  => '#6b7280',
                        'dot'       => '#9ca3af',
                    ],
                };
            @endphp

            <a
                href="{{ $this->getCreateUrl($service['key']) }}"
                class="group flex flex-col gap-3 p-4 h-full
                       bg-white dark:bg-gray-800
                       border border-gray-200 dark:border-gray-700
                       rounded-xl shadow-sm
                       hover:shadow-md hover:border-primary-400 dark:hover:border-primary-500
                       transition-all duration-200"
            >
                {{-- Baris atas: ikon + label + panah --}}
                <div class="flex items-center gap-3">
                    {{-- Ikon --}}
                    <div
                        class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center"
                        style="background-color: {{ $palette['icon_bg'] }}; color: {{ $palette['icon_text'] }};"
                    >
                        <x-dynamic-component :component="$service['icon']" class="w-5 h-5" />
                    </div>

                    {{-- Label --}}
                    <span
                        class="flex-1 font-semibold text-sm leading-snug
                               text-gray-800 dark:text-gray-100
                               group-hover:text-primary-600 dark:group-hover:text-primary-400
                               transition-colors"
                    >
                        {{ $service['label'] }}
                    </span>

                    {{-- Panah --}}
                    <x-heroicon-o-chevron-right
                        class="w-4 h-4 flex-shrink-0 text-gray-300 dark:text-gray-600
                               group-hover:text-primary-500 group-hover:translate-x-0.5
                               transition-all"
                    />
                </div>

                {{-- Divider tipis --}}
                <div class="border-t border-gray-100 dark:border-gray-700"></div>

                {{-- Deskripsi --}}
                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed flex-1">
                    {{ $service['description'] }}
                </p>
            </a>
        @endforeach
    </div>
</x-filament-panels::page>
