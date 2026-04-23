<?php

namespace App\Filament\Widgets;

use App\Models\Vehicle;
use App\Models\VehicleBooking;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use App\Settings\ModuleSettings; // Import ModuleSettings

class VehicleAvailabilityCalendar extends Widget
{
    protected static string $view = 'filament.widgets.vehicle-availability-calendar';

    protected static ?string $heading = 'Kalender Ketersediaan Kendaraan';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    // Implement canView method
    public static function canView(): bool
    {
        return app(ModuleSettings::class)->enable_vehicle_booking;
    }

    public ?int $selectedVehicleId = null;

    public string $currentMonth;

    public function mount(): void
    {
        $this->currentMonth = now()->format('Y-m');
        $this->selectedVehicleId = Vehicle::available()->first()?->id;
    }

    public function getVehicles(): array
    {
        return Vehicle::active()
            ->get()
            ->mapWithKeys(fn ($v) => [$v->id => "{$v->plate_number} - {$v->brand} {$v->model}"])
            ->toArray();
    }

    public function getCalendarData(): array
    {
        $startOfMonth = Carbon::parse($this->currentMonth)->startOfMonth();
        $endOfMonth = Carbon::parse($this->currentMonth)->endOfMonth();

        $bookings = [];

        if ($this->selectedVehicleId) {
            $bookings = VehicleBooking::where('vehicle_id', $this->selectedVehicleId)
                ->whereIn('status', ['approved', 'in_use'])
                ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                        ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                        ->orWhere(function ($q2) use ($startOfMonth, $endOfMonth) {
                            $q2->where('start_date', '<=', $startOfMonth)
                                ->where('end_date', '>=', $endOfMonth);
                        });
                })
                ->with('user')
                ->get();
        }

        // Build calendar grid
        $calendar = [];
        $current = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $endOfCalendar = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        while ($current <= $endOfCalendar) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $date = $current->copy();
                $isCurrentMonth = $date->month === $startOfMonth->month;
                $isToday = $date->isToday();
                $isPast = $date->isPast() && !$isToday;

                // Check if date has booking
                $dayBookings = $bookings->filter(function ($booking) use ($date) {
                    return $date->between($booking->start_date, $booking->end_date);
                });

                $week[] = [
                    'date' => $date->format('Y-m-d'),
                    'day' => $date->day,
                    'isCurrentMonth' => $isCurrentMonth,
                    'isToday' => $isToday,
                    'isPast' => $isPast,
                    'isBooked' => $dayBookings->isNotEmpty(),
                    'bookings' => $dayBookings->map(fn ($b) => [
                        'id' => $b->id,
                        'user' => $b->user->name,
                        'destination' => $b->destination,
                        'status' => $b->status,
                    ])->values()->toArray(),
                ];

                $current->addDay();
            }
            $calendar[] = $week;
        }

        return $calendar;
    }

    public function previousMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth)->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth)->addMonth()->format('Y-m');
    }

    public function getCurrentMonthLabel(): string
    {
        return Carbon::parse($this->currentMonth)->translatedFormat('F Y');
    }

    public function updatedSelectedVehicleId(): void
    {
        // Refresh calendar when vehicle changes
    }
}
