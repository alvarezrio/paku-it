<?php

namespace App\Filament\Widgets;

use App\Models\VehicleBooking;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use App\Settings\ModuleSettings; // Import ModuleSettings

class MyActiveBookings extends BaseWidget
{
    protected static ?string $heading = 'Peminjaman Aktif Saya';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                VehicleBooking::query()
                    ->where('user_id', auth()->id())
                    ->whereIn('status', ['approved', 'in_use'])
                    ->orderBy('start_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('booking_number')
                    ->label('No. Peminjaman')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('vehicle.plate_number')
                    ->label('Kendaraan')
                    ->formatStateUsing(fn ($record) => "{$record->vehicle->plate_number} - {$record->vehicle->brand} {$record->vehicle->model}"),

                Tables\Columns\TextColumn::make('destination')
                    ->label('Tujuan')
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->destination),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tanggal')
                    ->formatStateUsing(fn ($record) => $record->start_date->format('d M') . ' - ' . $record->end_date->format('d M Y')),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'approved' => 'Disetujui',
                        'in_use' => 'Sedang Digunakan',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'info',
                        'in_use' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('needs_return')
                    ->label('Status')
                    ->boolean()
                    ->state(fn ($record) => $record->needsReturn())
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('danger')
                    ->falseIcon('heroicon-o-check-circle')
                    ->falseColor('success')
                    ->tooltip(fn ($record) => $record->needsReturn() ? 'Perlu segera dikembalikan!' : 'OK'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.vehicle-bookings.view', $record)),

                Tables\Actions\Action::make('return')
                    ->label('Kembalikan')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->url(fn ($record) => route('filament.admin.resources.vehicle-bookings.view', $record))
                    ->visible(fn ($record) => $record->canBeReturned()),
            ])
            ->emptyStateHeading('Tidak ada peminjaman aktif')
            ->emptyStateDescription('Anda belum memiliki peminjaman kendaraan yang aktif.')
            ->emptyStateIcon('heroicon-o-truck')
            ->paginated(false);
    }

    public static function canView(): bool
    {
        return app(ModuleSettings::class)->enable_vehicle_booking && VehicleBooking::where('user_id', auth()->id())
            ->whereIn('status', ['approved', 'in_use'])
            ->exists();
    }
}
