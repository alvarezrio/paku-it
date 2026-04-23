<?php

namespace App\Filament\Widgets;

use App\Models\Vehicle;
use App\Models\VehicleBooking;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use App\Settings\ModuleSettings; // Import ModuleSettings

class VehicleBookingStats extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';

    protected static ?int $sort = 1;

    // Implement canView method
    public static function canView(): bool
    {
        return app(ModuleSettings::class)->enable_vehicle_booking;
    }

    protected function getStats(): array
    {
        $isAdmin = auth()->user()->hasRole('super_admin');
        $userId = auth()->id();
        $cacheKey = "vehicle_booking_stats_{$userId}_" . ($isAdmin ? 'admin' : 'user');

        $stats = Cache::remember($cacheKey, now()->addMinute(), function () use ($isAdmin, $userId) {
            $bookingQuery = VehicleBooking::query();
            
            if (!$isAdmin) {
                $bookingQuery->where('user_id', $userId);
            }

            $activeBookings = (clone $bookingQuery)->whereIn('status', ['approved', 'in_use'])->count();
            $needsReturn = (clone $bookingQuery)->needsReturn()->count();
            $monthlyBookings = (clone $bookingQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

            $availableVehicles = 0;
            $totalVehicles = 0;
            
            if ($isAdmin) {
                $availableVehicles = Vehicle::where('status', 'available')->count();
                $totalVehicles = Vehicle::whereIn('status', ['available', 'in_use'])->count();
            }

            return [
                'activeBookings' => $activeBookings,
                'needsReturn' => $needsReturn,
                'monthlyBookings' => $monthlyBookings,
                'availableVehicles' => $availableVehicles,
                'totalVehicles' => $totalVehicles,
            ];
        });

        $result = [
            Stat::make('Peminjaman Aktif', $stats['activeBookings'])
                ->description($isAdmin ? 'Total peminjaman aktif' : 'Peminjaman aktif Anda')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color($stats['activeBookings'] > 0 ? 'warning' : 'success')
                ->chart([7, 3, 4, 5, 6, $stats['activeBookings']]),

            Stat::make('Perlu Dikembalikan', $stats['needsReturn'])
                ->description('Melewati tanggal selesai')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($stats['needsReturn'] > 0 ? 'danger' : 'success')
                ->chart([2, 1, 3, 2, 1, $stats['needsReturn']]),
        ];

        if ($isAdmin) {
            $result[] = Stat::make('Kendaraan Tersedia', "{$stats['availableVehicles']}/{$stats['totalVehicles']}")
                ->description('Siap digunakan hari ini')
                ->descriptionIcon('heroicon-m-truck')
                ->color($stats['availableVehicles'] > 0 ? 'success' : 'danger')
                ->chart([4, 5, 3, 4, 5, $stats['availableVehicles']]);
        }

        $result[] = Stat::make('Peminjaman Bulan Ini', $stats['monthlyBookings'])
            ->description(now()->translatedFormat('F Y'))
            ->descriptionIcon('heroicon-m-calendar')
            ->color('info')
            ->chart([3, 5, 4, 6, 5, $stats['monthlyBookings']]);

        return $result;
    }
}
