<?php

namespace App\Filament\Pages;

use App\Models\Ticket;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Settings\ModuleSettings;

class TicketReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string $view = 'filament.pages.ticket-report';

    protected static ?string $navigationGroup = 'IT Helpdesk';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Laporan Tiket';

    protected static ?string $title = 'Laporan Tiket IT Helpdesk';

    public ?string $period = 'monthly';
    public ?string $startDate = null;
    public ?string $endDate = null;

    public function mount(): void
    {
        $this->setPeriodDates();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Laporan')
                    ->schema([
                        Select::make('period')
                            ->label('Periode')
                            ->options([
                                'weekly' => 'Mingguan',
                                'monthly' => 'Bulanan',
                                'quarterly' => 'Triwulanan',
                                'yearly' => 'Tahunan',
                                'custom' => 'Kustom',
                            ])
                            ->default('monthly')
                            ->live()
                            ->afterStateUpdated(fn () => $this->setPeriodDates()),

                        DatePicker::make('startDate')
                            ->label('Tanggal Mulai')
                            ->visible(fn () => $this->period === 'custom')
                            ->live()
                            ->afterStateUpdated(fn () => $this->loadReport()),

                        DatePicker::make('endDate')
                            ->label('Tanggal Akhir')
                            ->visible(fn () => $this->period === 'custom')
                            ->live()
                            ->afterStateUpdated(fn () => $this->loadReport()),
                    ])
                    ->columns(3),
            ]);
    }

    public function setPeriodDates(): void
    {
        $now = Carbon::now();

        switch ($this->period) {
            case 'weekly':
                $this->startDate = $now->startOfWeek()->format('Y-m-d');
                $this->endDate = $now->endOfWeek()->format('Y-m-d');
                break;
            case 'monthly':
                $this->startDate = $now->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->endOfMonth()->format('Y-m-d');
                break;
            case 'quarterly':
                $this->startDate = $now->startOfQuarter()->format('Y-m-d');
                $this->endDate = $now->endOfQuarter()->format('Y-m-d');
                break;
            case 'yearly':
                $this->startDate = $now->startOfYear()->format('Y-m-d');
                $this->endDate = $now->endOfYear()->format('Y-m-d');
                break;
            case 'custom':
                // Keep existing dates or set defaults
                if (!$this->startDate) {
                    $this->startDate = $now->startOfMonth()->format('Y-m-d');
                }
                if (!$this->endDate) {
                    $this->endDate = $now->endOfMonth()->format('Y-m-d');
                }
                break;
        }
    }

    public function loadReport(): void
    {
        // Trigger re-render
    }

    public function getStatistics(): array
    {
        $query = Ticket::whereBetween('created_at', [
            Carbon::parse($this->startDate)->startOfDay(),
            Carbon::parse($this->endDate)->endOfDay(),
        ]);

        $total = (clone $query)->count();
        $open = (clone $query)->where('status', 'open')->count();
        $inProgress = (clone $query)->where('status', 'in_progress')->count();
        $waitingUser = (clone $query)->where('status', 'waiting_for_user')->count();
        $resolved = (clone $query)->where('status', 'resolved')->count();
        $closed = (clone $query)->where('status', 'closed')->count();

        $dateRange = [
            Carbon::parse($this->startDate)->startOfDay(),
            Carbon::parse($this->endDate)->endOfDay(),
        ];

        // Average resolution time (in days)
        $avgResolutionTime = Ticket::whereBetween('created_at', $dateRange)
            ->whereNotNull('resolved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(DAY, created_at, resolved_at)) as avg_days')
            ->value('avg_days');

        // Average first response time (in hours)
        $avgFirstResponseTime = Ticket::whereBetween('created_at', $dateRange)
            ->whereNotNull('first_responded_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, first_responded_at)) as avg_minutes')
            ->value('avg_minutes');

        return [
            'total'                    => $total,
            'open'                     => $open,
            'in_progress'              => $inProgress,
            'waiting_user'             => $waitingUser,
            'resolved'                 => $resolved,
            'closed'                   => $closed,
            'avg_resolution_time'      => $avgResolutionTime ? round($avgResolutionTime) : 0,
            'avg_first_response_time'  => $avgFirstResponseTime ? round($avgFirstResponseTime) : null,
            'resolution_rate'          => $total > 0 ? round((($resolved + $closed) / $total) * 100, 1) : 0,
        ];
    }

    public function getCategoryStatistics(): array
    {
        return Ticket::whereBetween('created_at', [
            Carbon::parse($this->startDate)->startOfDay(),
            Carbon::parse($this->endDate)->endOfDay(),
        ])
            ->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->category => $item->total])
            ->toArray();
    }

    public function getPriorityStatistics(): array
    {
        return Ticket::whereBetween('created_at', [
            Carbon::parse($this->startDate)->startOfDay(),
            Carbon::parse($this->endDate)->endOfDay(),
        ])
            ->select('priority', DB::raw('count(*) as total'))
            ->groupBy('priority')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->priority => $item->total])
            ->toArray();
    }

    public function getTopReporters(): array
    {
        return Ticket::whereBetween('tickets.created_at', [
            Carbon::parse($this->startDate)->startOfDay(),
            Carbon::parse($this->endDate)->endOfDay(),
        ])
            ->join('users', 'tickets.user_id', '=', 'users.id')
            ->select('users.name', DB::raw('count(*) as total'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function getTopHandlers(): array
    {
        return Ticket::whereBetween('tickets.created_at', [
            Carbon::parse($this->startDate)->startOfDay(),
            Carbon::parse($this->endDate)->endOfDay(),
        ])
            ->whereNotNull('assigned_to')
            ->join('users', 'tickets.assigned_to', '=', 'users.id')
            ->select('users.name', DB::raw('count(*) as total'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function getAgentWorkload(): \Illuminate\Database\Eloquent\Collection
    {
        return User::role('Admin')
            ->withCount(['assignedTickets as active_tickets' => fn($q) =>
                $q->whereIn('status', ['open', 'in_progress', 'waiting_for_user'])
            ])
            ->orderByDesc('active_tickets')
            ->get(['id', 'name']);
    }

    public function getTickets(): \Illuminate\Database\Eloquent\Collection
    {
        return Ticket::with(['user', 'assignedTo'])
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPeriodLabel(): string
    {
        $start = Carbon::parse($this->startDate)->translatedFormat('d M Y');
        $end = Carbon::parse($this->endDate)->translatedFormat('d M Y');

        return "{$start} - {$end}";
    }

    public function getCategoryLabel(string $category): string
    {
        return match ($category) {
            'incident_management' => 'Manajemen Insiden',
            'service_request'     => 'Permintaan Layanan',
            'user_support'        => 'Dukungan Pengguna',
            'access_management'   => 'Manajemen Akses',
            'asset_management'    => 'Manajemen Aset',
            'change_management'   => 'Manajemen Perubahan',
            'network_support'     => 'Dukungan Jaringan',
            'security_support'    => 'Dukungan Keamanan',
            'documentation_kb'    => 'Dokumentasi & Basis Pengetahuan',
            default => $category,
        };
    }

    public function getPriorityLabel(string $priority): string
    {
        return match ($priority) {
            'critical' => 'Kritis',
            'high' => 'Tinggi',
            'medium' => 'Sedang',
            'low' => 'Rendah',
            default => $priority,
        };
    }

    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            'open' => 'Dibuka',
            'in_progress' => 'Diproses',
            'waiting_for_user' => 'Menunggu User',
            'resolved' => 'Selesai',
            'closed' => 'Ditutup',
            default => $status,
        };
    }

    public function exportExcel()
    {
        $tickets = $this->getTickets();
        $filename = 'laporan-tiket-' . $this->startDate . '-to-' . $this->endDate . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($tickets) {
            $file = fopen('php://output', 'w');

            // Add BOM for Excel UTF-8 compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header
            fputcsv($file, [
                'No. Tiket',
                'Tanggal',
                'Pelapor',
                'Kategori',
                'Prioritas',
                'Subjek',
                'Status',
                'Ditugaskan Ke',
                'Waktu Penyelesaian',
            ]);

            // Data
            foreach ($tickets as $ticket) {
                fputcsv($file, [
                    $ticket->ticket_number,
                    $ticket->created_at->format('d/m/Y H:i'),
                    $ticket->user->name ?? '-',
                    $this->getCategoryLabel($ticket->category),
                    $this->getPriorityLabel($ticket->priority),
                    $ticket->subject,
                    $this->getStatusLabel($ticket->status),
                    $ticket->assignedTo->name ?? '-',
                    $ticket->resolved_at ? $ticket->resolved_at->format('d/m/Y H:i') : '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public static function canAccess(): bool
    {
        return app(ModuleSettings::class)->enable_helpdesk_tickets && auth()->user()->hasRole('super_admin');
    }
}
