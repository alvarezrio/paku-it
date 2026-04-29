<?php

namespace App\Filament\Pages;

use App\Models\Device;
use App\Settings\ModuleSettings;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class DeviceDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string $view = 'filament.pages.device-dashboard';

    protected static ?string $navigationGroup = 'Inventaris';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Dashboard Perangkat';

    protected static ?string $title = 'Dashboard Perangkat';

    public static function shouldRegisterNavigation(): bool
    {
        return app(ModuleSettings::class)->enable_inventory
            && auth()->user()->hasAnyRole(['super_admin', 'Admin']);
    }

    public function getStats(): array
    {
        return Cache::remember('device_dashboard_stats', now()->addMinutes(2), function () {
            $base = Device::query();

            $byStatus = (clone $base)
                ->selectRaw('`status`, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $byCondition = (clone $base)
                ->selectRaw('`condition`, COUNT(*) as total')
                ->groupBy('condition')
                ->pluck('total', 'condition')
                ->toArray();

            $byType = (clone $base)
                ->selectRaw('`type`, COUNT(*) as total')
                ->groupBy('type')
                ->orderByRaw('total DESC')
                ->pluck('total', 'type')
                ->toArray();

            $byLocation = (clone $base)
                ->selectRaw('`location`, COUNT(*) as total')
                ->whereNotNull('location')
                ->groupBy('location')
                ->orderBy('location')
                ->pluck('total', 'location')
                ->toArray();

            $bySection = (clone $base)
                ->selectRaw('`responsible_section`, COUNT(*) as total')
                ->whereNotNull('responsible_section')
                ->groupBy('responsible_section')
                ->orderByRaw('total DESC')
                ->pluck('total', 'responsible_section')
                ->toArray();

            $total = (clone $base)->count();

            return [
                'total'         => $total,
                'active'        => $byStatus['active']      ?? 0,
                'maintenance'   => $byStatus['maintenance'] ?? 0,
                'inactive'      => $byStatus['inactive']    ?? 0,
                'retired'       => $byStatus['retired']     ?? 0,
                'poorCondition' => ($byCondition['poor']    ?? 0) + ($byCondition['broken'] ?? 0),
                'unassigned'    => (clone $base)->whereNull('user_id')->where('status', 'active')->count(),
                'byCondition'   => $byCondition,
                'byType'        => $byType,
                'byLocation'    => $byLocation,
                'bySection'     => $bySection,
            ];
        });
    }

    public function getWarrantyExpiringSoon(): \Illuminate\Database\Eloquent\Collection
    {
        return Device::whereNotNull('warranty_expiry')
            ->where('warranty_expiry', '>=', now())
            ->where('warranty_expiry', '<=', now()->addDays(30))
            ->where('status', '!=', 'retired')
            ->with('user')
            ->orderBy('warranty_expiry')
            ->limit(8)
            ->get();
    }

    public function getPoorConditionDevices(): \Illuminate\Database\Eloquent\Collection
    {
        return Device::whereIn('condition', ['poor', 'broken'])
            ->where('status', '!=', 'retired')
            ->with('user')
            ->orderByRaw("FIELD(`condition`, 'broken', 'poor')")
            ->limit(8)
            ->get();
    }

    public function getUnassignedDevices(): \Illuminate\Database\Eloquent\Collection
    {
        return Device::whereNull('user_id')
            ->where('status', 'active')
            ->orderBy('type')
            ->limit(8)
            ->get();
    }
}
