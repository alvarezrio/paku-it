<x-filament-panels::page>
@php
    $stats       = $this->getStats();
    $warrantyExp = $this->getWarrantyExpiringSoon();
    $poorDevices = $this->getPoorConditionDevices();
    $unassigned  = $this->getUnassignedDevices();
    $total       = $stats['total'] ?: 1;

    $typeLabels = [
        'laptop'       => 'Laptop',
        'desktop'      => 'Desktop / PC',
        'all-in-one'   => 'All-in-One',
        'workstation'  => 'Workstation',
        'printer'      => 'Printer',
        'scanner'      => 'Scanner',
        'router'       => 'Router',
        'switch'       => 'Switch',
        'access-point' => 'Access Point',
    ];

    $conditionLabels = [
        'excellent' => 'Sangat Baik',
        'good'      => 'Baik',
        'fair'      => 'Cukup',
        'poor'      => 'Buruk',
        'broken'    => 'Rusak',
    ];
@endphp

{{-- ══════════════════════════════════════════════════════ --}}
{{-- STATS CARDS                                          --}}
{{-- ══════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">

    {{-- Total --}}
    <div class="rounded-xl p-4 ring-1 ring-gray-200 dark:ring-gray-700 bg-white dark:bg-gray-900 flex flex-col gap-2">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Perangkat</p>
        <p class="text-3xl font-bold text-primary-600 dark:text-primary-400">{{ $stats['total'] }}</p>
        <p class="text-xs text-gray-400">Semua status</p>
    </div>

    {{-- Aktif --}}
    <div class="rounded-xl p-4 ring-1 ring-gray-200 dark:ring-gray-700 bg-white dark:bg-gray-900 flex flex-col gap-2">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Aktif</p>
        <p class="text-3xl font-bold text-success-600 dark:text-success-400">{{ $stats['active'] }}</p>
        <p class="text-xs text-gray-400">Beroperasi</p>
    </div>

    {{-- Maintenance --}}
    <div class="rounded-xl p-4 ring-1 ring-gray-200 dark:ring-gray-700 bg-white dark:bg-gray-900 flex flex-col gap-2">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Maintenance</p>
        <p class="text-3xl font-bold text-warning-600 dark:text-warning-400">{{ $stats['maintenance'] }}</p>
        <p class="text-xs text-gray-400">Sedang servis</p>
    </div>

    {{-- Kondisi Buruk --}}
    <div class="rounded-xl p-4 ring-1 ring-gray-200 dark:ring-gray-700 bg-white dark:bg-gray-900 flex flex-col gap-2">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Kondisi Buruk</p>
        <p class="text-3xl font-bold text-danger-600 dark:text-danger-400">{{ $stats['poorCondition'] }}</p>
        <p class="text-xs text-gray-400">Buruk + Rusak</p>
    </div>

    {{-- Belum Ditugaskan --}}
    <div class="rounded-xl p-4 ring-1 ring-gray-200 dark:ring-gray-700 bg-white dark:bg-gray-900 flex flex-col gap-2">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Belum Ditugaskan</p>
        <p class="text-3xl font-bold text-gray-700 dark:text-gray-300">{{ $stats['unassigned'] }}</p>
        <p class="text-xs text-gray-400">Aktif, tanpa user</p>
    </div>

    {{-- Pensiun --}}
    <div class="rounded-xl p-4 ring-1 ring-gray-200 dark:ring-gray-700 bg-white dark:bg-gray-900 flex flex-col gap-2">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Pensiun</p>
        <p class="text-3xl font-bold text-gray-400 dark:text-gray-500">{{ $stats['retired'] }}</p>
        <p class="text-xs text-gray-400">Dinonaktifkan</p>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- DISTRIBUSI PER TIPE & KONDISI                        --}}
{{-- ══════════════════════════════════════════════════════ --}}
<div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">

    {{-- Per Tipe --}}
    <x-filament::section>
        <x-slot name="heading">Distribusi per Tipe Perangkat</x-slot>
        <div class="space-y-3 py-1">
            @forelse ($stats['byType'] as $type => $count)
                @php $pct = round($count / $total * 100); @endphp
                <div class="flex items-center gap-3">
                    <span class="w-28 shrink-0 text-xs text-gray-600 dark:text-gray-400 truncate">{{ $typeLabels[$type] ?? $type }}</span>
                    <div class="flex-1 rounded-full h-2" style="background-color: rgba(var(--gray-200), 1);">
                        <div class="h-2 rounded-full bg-primary-500" style="width:{{ max($pct, 2) }}%"></div>
                    </div>
                    <span class="w-16 shrink-0 text-right text-xs font-semibold text-gray-700 dark:text-gray-300">
                        {{ $count }} <span class="font-normal text-gray-400">({{ $pct }}%)</span>
                    </span>
                </div>
            @empty
                <p class="text-sm text-gray-400 italic py-4 text-center">Tidak ada data.</p>
            @endforelse
        </div>
    </x-filament::section>

    {{-- Per Kondisi --}}
    <x-filament::section>
        <x-slot name="heading">Distribusi per Kondisi</x-slot>
        <div class="space-y-3 py-1">
            @foreach ([
                'excellent' => ['label'=>'Sangat Baik', 'color'=>'bg-success-500'],
                'good'      => ['label'=>'Baik',        'color'=>'bg-success-400'],
                'fair'      => ['label'=>'Cukup',       'color'=>'bg-warning-500'],
                'poor'      => ['label'=>'Buruk',       'color'=>'bg-danger-400'],
                'broken'    => ['label'=>'Rusak',       'color'=>'bg-danger-600'],
            ] as $cond => $meta)
                @php
                    $count = $stats['byCondition'][$cond] ?? 0;
                    $pct   = round($count / $total * 100);
                @endphp
                <div class="flex items-center gap-3">
                    <span class="w-24 shrink-0 text-xs text-gray-600 dark:text-gray-400">{{ $meta['label'] }}</span>
                    <div class="flex-1 rounded-full h-2" style="background-color: rgba(var(--gray-200), 1);">
                        <div class="h-2 rounded-full {{ $meta['color'] }}" style="width:{{ $count > 0 ? max($pct, 2) : 0 }}%"></div>
                    </div>
                    <span class="w-16 shrink-0 text-right text-xs font-semibold text-gray-700 dark:text-gray-300">
                        {{ $count }} <span class="font-normal text-gray-400">({{ $pct }}%)</span>
                    </span>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</div>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- PER LANTAI & PER SEKSI                               --}}
{{-- ══════════════════════════════════════════════════════ --}}
<div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">

    {{-- Per Lantai --}}
    <x-filament::section>
        <x-slot name="heading">Perangkat per Lantai</x-slot>
        @if (count($stats['byLocation']) > 0)
            <div class="space-y-3 py-1">
                @foreach ($stats['byLocation'] as $location => $count)
                    @php $pct = round($count / $total * 100); @endphp
                    <div class="flex items-center gap-3">
                        <span class="w-28 shrink-0 text-xs text-gray-600 dark:text-gray-400 truncate">{{ $location }}</span>
                        <div class="flex-1 rounded-full h-2" style="background-color: rgba(var(--gray-200), 1);">
                            <div class="h-2 rounded-full bg-info-500" style="width:{{ max($pct, 2) }}%"></div>
                        </div>
                        <span class="w-16 shrink-0 text-right text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ $count }} <span class="font-normal text-gray-400">({{ $pct }}%)</span>
                        </span>
                    </div>
                @endforeach
            </div>
            @php $noLocation = $stats['total'] - array_sum($stats['byLocation']); @endphp
            @if ($noLocation > 0)
                <p class="mt-3 text-xs text-gray-400 italic">* {{ $noLocation }} perangkat belum diisi lokasi lantai.</p>
            @endif
        @else
            <p class="text-sm text-gray-400 italic text-center py-6">Belum ada data lokasi lantai.</p>
        @endif
    </x-filament::section>

    {{-- Per Seksi --}}
    <x-filament::section>
        <x-slot name="heading">Perangkat per Seksi / Ruangan</x-slot>
        @if (count($stats['bySection']) > 0)
            <div class="space-y-3 py-1">
                @foreach ($stats['bySection'] as $section => $count)
                    @php $pct = round($count / $total * 100); @endphp
                    <div class="flex items-center gap-3">
                        <span class="w-36 shrink-0 text-xs text-gray-600 dark:text-gray-400 truncate" title="{{ $section }}">{{ $section }}</span>
                        <div class="flex-1 rounded-full h-2" style="background-color: rgba(var(--gray-200), 1);">
                            <div class="h-2 rounded-full bg-warning-500" style="width:{{ max($pct, 2) }}%"></div>
                        </div>
                        <span class="w-10 shrink-0 text-right text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
            @php $noSection = $stats['total'] - array_sum($stats['bySection']); @endphp
            @if ($noSection > 0)
                <p class="mt-3 text-xs text-gray-400 italic">* {{ $noSection }} perangkat belum diisi seksi penanggung jawab.</p>
            @endif
        @else
            <p class="text-sm text-gray-400 italic text-center py-6">Belum ada data seksi penanggung jawab.</p>
        @endif
    </x-filament::section>
</div>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- TABEL PERHATIAN                                      --}}
{{-- ══════════════════════════════════════════════════════ --}}
<div class="mt-4 grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Kondisi Buruk/Rusak --}}
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2">
                <span class="text-danger-500">&#9888;</span>
                Kondisi Buruk / Rusak
                @if ($poorDevices->isNotEmpty())
                    <span class="ml-1 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-danger-100 text-danger-700 dark:bg-danger-900 dark:text-danger-300">{{ $poorDevices->count() }}</span>
                @endif
            </span>
        </x-slot>
        <div class="divide-y divide-gray-100 dark:divide-gray-800 -mx-6 -mb-6">
            @forelse ($poorDevices as $device)
                <div class="flex items-center justify-between px-6 py-3 gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $device->display_name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $device->user?->name ?? '— belum ditugaskan' }}</p>
                    </div>
                    @php
                        $condBadge = match($device->condition) {
                            'poor'   => 'bg-danger-100 text-danger-700 dark:bg-danger-900 dark:text-danger-300',
                            'broken' => 'bg-danger-100 text-danger-700 dark:bg-danger-900 dark:text-danger-300',
                            default  => 'bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300',
                        };
                        $condLabel = $conditionLabels[$device->condition] ?? $device->condition;
                    @endphp
                    <span class="shrink-0 inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $condBadge }}">{{ $condLabel }}</span>
                </div>
            @empty
                <p class="px-6 py-6 text-sm text-center text-gray-400 italic">Semua perangkat dalam kondisi baik.</p>
            @endforelse
        </div>
    </x-filament::section>

    {{-- Garansi Akan Habis --}}
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2">
                <span class="text-warning-500">&#128197;</span>
                Garansi Habis &le; 30 Hari
                @if ($warrantyExp->isNotEmpty())
                    <span class="ml-1 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300">{{ $warrantyExp->count() }}</span>
                @endif
            </span>
        </x-slot>
        <div class="divide-y divide-gray-100 dark:divide-gray-800 -mx-6 -mb-6">
            @forelse ($warrantyExp as $device)
                <div class="flex items-center justify-between px-6 py-3 gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $device->display_name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $device->user?->name ?? '— belum ditugaskan' }}</p>
                    </div>
                    <span class="shrink-0 text-xs font-semibold text-warning-600 dark:text-warning-400 whitespace-nowrap">
                        {{ $device->warranty_expiry->format('d M Y') }}
                    </span>
                </div>
            @empty
                <p class="px-6 py-6 text-sm text-center text-gray-400 italic">Tidak ada garansi yang akan segera habis.</p>
            @endforelse
        </div>
    </x-filament::section>

    {{-- Aktif, Belum Ditugaskan --}}
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2">
                Aktif, Belum Ditugaskan
                @if ($unassigned->isNotEmpty())
                    <span class="ml-1 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $unassigned->count() }}</span>
                @endif
            </span>
        </x-slot>
        <div class="divide-y divide-gray-100 dark:divide-gray-800 -mx-6 -mb-6">
            @forelse ($unassigned as $device)
                <div class="flex items-center justify-between px-6 py-3 gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $device->display_name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $device->location ?? 'Lokasi belum diisi' }}
                        </p>
                    </div>
                    <span class="shrink-0 inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        {{ $typeLabels[$device->type] ?? $device->type }}
                    </span>
                </div>
            @empty
                <p class="px-6 py-6 text-sm text-center text-gray-400 italic">Semua perangkat aktif sudah ditugaskan.</p>
            @endforelse
        </div>
    </x-filament::section>

</div>
</x-filament-panels::page>
