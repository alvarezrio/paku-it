<?php

namespace App\Filament\Pages;

use App\Models\Vehicle;
use App\Models\VehicleBooking;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use App\Settings\ModuleSettings; // Import ModuleSettings

class VehicleCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static string $view = 'filament.pages.vehicle-calendar';

    protected static ?string $navigationGroup = 'Kendaraan Dinas';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Kalender KDO';

    protected static ?string $title = 'Kalender Ketersediaan KDO';

    public static function shouldRegisterNavigation(): bool
    {
        return app(ModuleSettings::class)->enable_vehicle_booking;
    }

    public ?int $selectedVehicleId = null;

    public string $currentMonth;

    public function mount(): void
    {
        $this->currentMonth = now()->format('Y-m');
    }

    public function getVehicles(): array
    {
        return Vehicle::active()
            ->get()
            ->mapWithKeys(fn ($v) => [$v->id => "{$v->plate_number} - {$v->brand} {$v->model} ({$v->condition_label})"])
            ->toArray();
    }

    public function getAllVehiclesWithBookings(): array
    {
        $startOfMonth = Carbon::parse($this->currentMonth)->startOfMonth();
        $endOfMonth = Carbon::parse($this->currentMonth)->endOfMonth();

        $vehicles = Vehicle::active()->with(['bookings' => function ($q) use ($startOfMonth, $endOfMonth) {
            $q->whereIn('status', ['approved', 'in_use'])
                ->where(function ($q2) use ($startOfMonth, $endOfMonth) {
                    $q2->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                        ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                        ->orWhere(function ($q3) use ($startOfMonth, $endOfMonth) {
                            $q3->where('start_date', '<=', $startOfMonth)
                                ->where('end_date', '>=', $endOfMonth);
                        });
                })
                ->with('user');
        }])->get();

        return $vehicles->map(function ($vehicle) use ($startOfMonth, $endOfMonth) {
            $days = [];
            $current = $startOfMonth->copy();

            while ($current <= $endOfMonth) {
                $date = $current->copy();
                $booking = $vehicle->bookings->first(function ($b) use ($date) {
                    return $date->between($b->start_date, $b->end_date);
                });

                $days[$date->format('Y-m-d')] = [
                    'isBooked' => $booking !== null,
                    'booking' => $booking ? [
                        'id' => $booking->id,
                        'user' => $booking->user->name,
                        'destination' => $booking->destination,
                        'status' => $booking->status,
                        'start' => $booking->start_date->format('d M'),
                        'end' => $booking->end_date->format('d M'),
                    ] : null,
                ];

                $current->addDay();
            }

            return [
                'id' => $vehicle->id,
                'name' => "{$vehicle->plate_number}",
                'full_name' => "{$vehicle->brand} {$vehicle->model}",
                'condition' => $vehicle->condition,
                'status' => $vehicle->status,
                'days' => $days,
            ];
        })->toArray();
    }

    public function getDaysInMonth(): array
    {
        $startOfMonth = Carbon::parse($this->currentMonth)->startOfMonth();
        $endOfMonth = Carbon::parse($this->currentMonth)->endOfMonth();

        $days = [];
        $current = $startOfMonth->copy();

        while ($current <= $endOfMonth) {
            $days[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->day,
                'dayName' => $current->translatedFormat('D'),
                'isToday' => $current->isToday(),
                'isWeekend' => $current->isWeekend(),
            ];
            $current->addDay();
        }

        return $days;
    }

    public function previousMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth)->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth)->addMonth()->format('Y-m');
    }

    public function goToToday(): void
    {
        $this->currentMonth = now()->format('Y-m');
    }

    public function getCurrentMonthLabel(): string
    {
        return Carbon::parse($this->currentMonth)->translatedFormat('F Y');
    }
}
