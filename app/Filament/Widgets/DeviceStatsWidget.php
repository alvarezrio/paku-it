<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use Filament\Widgets\Widget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use App\Settings\ModuleSettings; // Import ModuleSettings

class DeviceStatsWidget extends Widget
{
    protected static string $view = 'filament.widgets.device-stats-widget';

    protected static ?string $pollingInterval = '120s';

    protected static bool $isLazy = true;

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return app(ModuleSettings::class)->enable_inventory && auth()->user()->hasRole('super_admin');
    }

    public function getStats(): array
    {
        $stats = Cache::remember('device_stats_widget', now()->addMinutes(2), function () {
            $baseQuery = Device::query();
            
            return [
                'totalDevices' => (clone $baseQuery)->count(),
                'activeDevices' => (clone $baseQuery)->where('status', 'active')->count(),
                'maintenanceDevices' => (clone $baseQuery)->where('status', 'maintenance')->count(),
                'retiredDevices' => (clone $baseQuery)->where('status', 'retired')->count(),
                'poorConditionDevices' => (clone $baseQuery)->whereIn('condition', ['poor', 'broken'])->count(),
                'unassignedDevices' => (clone $baseQuery)->whereNull('user_id')->where('status', 'active')->count(),
                'noIpAddress' => (clone $baseQuery)->whereNull('ip_address')->where('status', '!=', 'retired')->count(),
            ];
        });

        return [
            Stat::make('Total Perangkat', $stats['totalDevices'])
                ->description('Semua perangkat komputer')
                ->descriptionIcon('heroicon-m-computer-desktop')
                ->color('primary')
                ->chart([7, 5, 8, 6, 9, $stats['totalDevices']]),

            Stat::make('Perangkat Aktif', $stats['activeDevices'])
                ->description("{$stats['unassignedDevices']} belum di-assign")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([4, 5, 6, 5, 7, $stats['activeDevices']]),

            Stat::make('Dalam Perbaikan', $stats['maintenanceDevices'])
                ->description('Sedang maintenance')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color($stats['maintenanceDevices'] > 0 ? 'warning' : 'success')
                ->chart([2, 3, 2, 4, 3, $stats['maintenanceDevices']]),

            Stat::make('Kondisi Buruk', $stats['poorConditionDevices'])
                ->description('Perlu perhatian')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($stats['poorConditionDevices'] > 0 ? 'danger' : 'success')
                ->chart([1, 2, 1, 3, 2, $stats['poorConditionDevices']]),

            Stat::make('Belum Ada IP', $stats['noIpAddress'])
                ->description('Perangkat tanpa IP Address')
                ->descriptionIcon('heroicon-m-signal-slash')
                ->color($stats['noIpAddress'] > 0 ? 'danger' : 'success')
                ->chart([3, 4, 5, 4, 6, $stats['noIpAddress']]),

            Stat::make('Non-Aktif/Retired', $stats['retiredDevices'])
                ->description('Perangkat tidak digunakan')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('gray')
                ->chart([1, 1, 2, 1, 1, $stats['retiredDevices']]),
        ];
    }
}
