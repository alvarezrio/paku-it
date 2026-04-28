<x-filament-panels::page>
    {{-- Filter Form --}}
    <div class="mb-6">
        {{ $this->form }}
    </div>

    {{-- Period Info & Export Buttons --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Periode: {{ $this->getPeriodLabel() }}
            </h3>
        </div>
        <div class="flex gap-2">
            <x-filament::button
                tag="a"
                href="{{ route('reports.tickets.excel', ['start_date' => $this->startDate, 'end_date' => $this->endDate]) }}"
                color="success"
                icon="heroicon-o-document-arrow-down"
            >
                Export Excel
            </x-filament::button>
            <x-filament::button
                tag="a"
                href="{{ route('reports.tickets.pdf', ['start_date' => $this->startDate, 'end_date' => $this->endDate]) }}"
                target="_blank"
                color="danger"
                icon="heroicon-o-document-text"
            >
                Export PDF
            </x-filament::button>
        </div>
    </div>

    @php
        $stats = $this->getStatistics();
        $categoryStats = $this->getCategoryStatistics();
        $priorityStats = $this->getPriorityStatistics();
        $topReporters = $this->getTopReporters();
        $topHandlers = $this->getTopHandlers();
        $agentWorkload = $this->getAgentWorkload();
        $tickets = $this->getTickets();
    @endphp

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <x-filament::section class="text-center">
            <div class="text-3xl font-bold text-primary-600">{{ $stats['total'] }}</div>
            <div class="text-sm text-gray-500">Total Tiket</div>
        </x-filament::section>

        <x-filament::section class="text-center">
            <div class="text-3xl font-bold text-danger-600">{{ $stats['open'] }}</div>
            <div class="text-sm text-gray-500">Dibuka</div>
        </x-filament::section>

        <x-filament::section class="text-center">
            <div class="text-3xl font-bold text-warning-600">{{ $stats['in_progress'] }}</div>
            <div class="text-sm text-gray-500">Diproses</div>
        </x-filament::section>

        <x-filament::section class="text-center">
            <div class="text-3xl font-bold text-success-600">{{ $stats['resolved'] + $stats['closed'] }}</div>
            <div class="text-sm text-gray-500">Selesai</div>
        </x-filament::section>
    </div>

    {{-- Additional Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <x-filament::section class="text-center">
            <div class="text-2xl font-bold text-info-600">{{ $stats['avg_resolution_time'] }} hari</div>
            <div class="text-sm text-gray-500">Rata-rata Waktu Penyelesaian</div>
        </x-filament::section>

        <x-filament::section class="text-center">
            @if($stats['avg_first_response_time'] !== null)
                @php
                    $minutes = $stats['avg_first_response_time'];
                    $firstResponseLabel = $minutes < 60
                        ? $minutes . ' menit'
                        : round($minutes / 60, 1) . ' jam';
                @endphp
                <div class="text-2xl font-bold text-warning-600">{{ $firstResponseLabel }}</div>
            @else
                <div class="text-2xl font-bold text-gray-400">-</div>
            @endif
            <div class="text-sm text-gray-500">Rata-rata First Response</div>
        </x-filament::section>

        <x-filament::section class="text-center">
            <div class="text-2xl font-bold text-success-600">{{ $stats['resolution_rate'] }}%</div>
            <div class="text-sm text-gray-500">Tingkat Penyelesaian</div>
        </x-filament::section>
    </div>

    {{-- Category & Priority Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Category Stats --}}
        <x-filament::section>
            <x-slot name="heading">Berdasarkan Kategori</x-slot>
            <div class="space-y-3">
                @forelse($categoryStats as $category => $count)
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700 dark:text-gray-300">{{ $this->getCategoryLabel($category) }}</span>
                        <span class="font-semibold">{{ $count }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $stats['total'] > 0 ? ($count / $stats['total']) * 100 : 0 }}%"></div>
                    </div>
                @empty
                    <p class="text-gray-500">Tidak ada data</p>
                @endforelse
            </div>
        </x-filament::section>

        {{-- Priority Stats --}}
        <x-filament::section>
            <x-slot name="heading">Berdasarkan Prioritas</x-slot>
            <div class="space-y-3">
                @forelse($priorityStats as $priority => $count)
                    @php
                        $priorityColor = match($priority) {
                            'critical' => 'bg-red-600',
                            'high' => 'bg-orange-500',
                            'medium' => 'bg-blue-500',
                            'low' => 'bg-gray-400',
                            default => 'bg-gray-400',
                        };
                    @endphp
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700 dark:text-gray-300">{{ $this->getPriorityLabel($priority) }}</span>
                        <span class="font-semibold">{{ $count }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="{{ $priorityColor }} h-2 rounded-full" style="width: {{ $stats['total'] > 0 ? ($count / $stats['total']) * 100 : 0 }}%"></div>
                    </div>
                @empty
                    <p class="text-gray-500">Tidak ada data</p>
                @endforelse
            </div>
        </x-filament::section>
    </div>

    {{-- Top Reporters & Handlers --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Top Reporters --}}
        <x-filament::section>
            <x-slot name="heading">Top 5 Pelapor</x-slot>
            <div class="space-y-2">
                @forelse($topReporters as $index => $reporter)
                    <div class="flex justify-between items-center py-2 border-b dark:border-gray-700 last:border-0">
                        <span class="text-gray-700 dark:text-gray-300">
                            <span class="font-medium">{{ $index + 1 }}.</span> {{ $reporter['name'] }}
                        </span>
                        <span class="font-semibold bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 px-2 py-1 rounded">
                            {{ $reporter['total'] }} tiket
                        </span>
                    </div>
                @empty
                    <p class="text-gray-500">Tidak ada data</p>
                @endforelse
            </div>
        </x-filament::section>

        {{-- Top Handlers --}}
        <x-filament::section>
            <x-slot name="heading">Top 5 Penangan</x-slot>
            <div class="space-y-2">
                @forelse($topHandlers as $index => $handler)
                    <div class="flex justify-between items-center py-2 border-b dark:border-gray-700 last:border-0">
                        <span class="text-gray-700 dark:text-gray-300">
                            <span class="font-medium">{{ $index + 1 }}.</span> {{ $handler['name'] }}
                        </span>
                        <span class="font-semibold bg-success-100 dark:bg-success-900 text-success-700 dark:text-success-300 px-2 py-1 rounded">
                            {{ $handler['total'] }} tiket
                        </span>
                    </div>
                @empty
                    <p class="text-gray-500">Tidak ada data</p>
                @endforelse
            </div>
        </x-filament::section>
    </div>

    {{-- Agent Workload --}}
    <x-filament::section class="mb-6">
        <x-slot name="heading">Beban Kerja Admin</x-slot>
        @if($agentWorkload->isEmpty())
            <p class="text-gray-500 text-sm">Tidak ada admin terdaftar.</p>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                @foreach($agentWorkload as $agent)
                    @php
                        $count = $agent->active_tickets;
                        $badgeClass = match(true) {
                            $count === 0    => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                            $count <= 3     => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                            $count <= 6     => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
                            default         => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                        };
                    @endphp
                    <div class="flex flex-col items-center p-3 rounded-lg border dark:border-gray-700 text-center">
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 truncate w-full" title="{{ $agent->name }}">
                            {{ $agent->name }}
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                            {{ $count }} aktif
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>

    {{-- Tickets Table --}}
    <x-filament::section>
        <x-slot name="heading">Daftar Tiket</x-slot>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3">No. Tiket</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Pelapor</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Prioritas</th>
                        <th class="px-4 py-3">Subjek</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Ditugaskan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        @php
                            $statusColor = match($ticket->status) {
                                'open' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                'in_progress' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                'waiting_for_user' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                'resolved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                'closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                default => 'bg-gray-100 text-gray-800',
                            };
                            $priorityColor = match($ticket->priority) {
                                'critical' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                'high' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
                                'medium' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                'low' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                default => 'bg-gray-100 text-gray-800',
                            };
                        @endphp
                        <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-4 py-3 font-medium">{{ $ticket->ticket_number }}</td>
                            <td class="px-4 py-3">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $ticket->user->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $this->getCategoryLabel($ticket->category) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded {{ $priorityColor }}">
                                    {{ $this->getPriorityLabel($ticket->priority) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 max-w-xs truncate" title="{{ $ticket->subject }}">{{ $ticket->subject }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded {{ $statusColor }}">
                                    {{ $this->getStatusLabel($ticket->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $ticket->assignedTo->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada tiket pada periode ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 text-sm text-gray-500">
            Total: {{ $tickets->count() }} tiket
        </div>
    </x-filament::section>

</x-filament-panels::page>
