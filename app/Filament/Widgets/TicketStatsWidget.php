<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\Widget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use App\Settings\ModuleSettings;
use Illuminate\Support\Facades\Auth; // Import ModuleSettings

class TicketStatsWidget extends Widget
{
    protected static string $view = 'filament.widgets.ticket-stats-widget';

    protected static ?string $pollingInterval = '60s';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Auth::user();

        // Always check for module enablement first
        if (!app(ModuleSettings::class)->enable_helpdesk_tickets) {
            return false;
        }
        
        // Return false if there is no authenticated user or if the user has the 'Member' role.
        if (!$user || $user->hasRole('Member')) {
            return false;
        }

        return true;
    }

    public function getStats(): array
    {
        $user = auth()->user();
        $isAdmin = $user->hasAnyRole(['super_admin', 'Admin']);
        $cacheKey = 'ticket_stats_widget_' . ($isAdmin ? 'admin' : 'user_' . $user->id);

        $stats = Cache::remember($cacheKey, now()->addMinute(), function () use ($user, $isAdmin) {
            $baseQuery = Ticket::query();

            // Filter berdasarkan user jika bukan admin
            if (!$isAdmin) {
                $baseQuery->where('user_id', $user->id);
            }

            return [
                'newTickets' => (clone $baseQuery)->where('status', 'open')->count(),
                'inProgressTickets' => (clone $baseQuery)->where('status', 'in_progress')->count(),
                'waitingUserTickets' => (clone $baseQuery)->where('status', 'waiting_for_user')->count(),
                'unassignedTickets' => (clone $baseQuery)->whereIn('status', ['open', 'in_progress'])->whereNull('assigned_to')->count(),
                'highPriorityOpen' => (clone $baseQuery)->whereIn('status', ['open', 'in_progress', 'waiting_for_user'])->whereIn('priority', ['high', 'critical'])->count(),
                'resolvedThisMonth' => (clone $baseQuery)->whereIn('status', ['resolved', 'closed'])->whereMonth('resolved_at', now()->month)->whereYear('resolved_at', now()->year)->count(),
                'todayTickets' => (clone $baseQuery)->whereDate('created_at', today())->count(),
                'monthlyTickets' => (clone $baseQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            ];
        });

        return [
            Stat::make('Tiket Baru', $stats['newTickets'])
                ->description("{$stats['todayTickets']} tiket masuk hari ini")
                ->descriptionIcon('heroicon-m-inbox-arrow-down')
                ->color($stats['newTickets'] > 5 ? 'danger' : ($stats['newTickets'] > 0 ? 'warning' : 'success'))
                ->chart([3, 4, 2, 5, 3, $stats['newTickets']]),

            Stat::make('Dalam Proses', $stats['inProgressTickets'])
                ->description('Sedang ditangani')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color($stats['inProgressTickets'] > 0 ? 'info' : 'gray')
                ->chart([2, 3, 4, 3, 5, $stats['inProgressTickets']]),

            Stat::make('Menunggu User', $stats['waitingUserTickets'])
                ->description('Perlu respons user')
                ->descriptionIcon('heroicon-m-clock')
                ->color($stats['waitingUserTickets'] > 0 ? 'warning' : 'success')
                ->chart([1, 2, 1, 3, 2, $stats['waitingUserTickets']]),

            Stat::make('Belum Ditugaskan', $stats['unassignedTickets'])
                ->description('Perlu segera ditugaskan')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color($stats['unassignedTickets'] > 0 ? 'danger' : 'success')
                ->chart([1, 0, 2, 1, 1, $stats['unassignedTickets']]),

            Stat::make('Prioritas Tinggi', $stats['highPriorityOpen'])
                ->description('High & Critical terbuka')
                ->descriptionIcon('heroicon-m-fire')
                ->color($stats['highPriorityOpen'] > 0 ? 'danger' : 'success')
                ->chart([2, 1, 3, 2, 1, $stats['highPriorityOpen']]),

            Stat::make('Selesai Bulan Ini', $stats['resolvedThisMonth'])
                ->description("{$stats['monthlyTickets']} total tiket " . now()->translatedFormat('F'))
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->chart([4, 6, 5, 7, 8, $stats['resolvedThisMonth']]),
        ];
    }
}
