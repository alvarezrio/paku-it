<?php

namespace App\Filament\Exports;

use App\Models\Device;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class DeviceExporter extends Exporter
{
    protected static ?string $model = Device::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('user.name')
                ->label('Pengguna'),
            ExportColumn::make('type')
                ->label('Tipe'),
            ExportColumn::make('hostname'),
            ExportColumn::make('ip_address')
                ->label('Alamat IP'),
            ExportColumn::make('mac_address')
                ->label('Alamat MAC'),
            ExportColumn::make('brand')
                ->label('Merek'),
            ExportColumn::make('model')
                ->label('Model'),
            ExportColumn::make('serial_number')
                ->label('Nomor Seri'),
            ExportColumn::make('asset_tag')
                ->label('Tag Aset'),
            ExportColumn::make('os')
                ->label('Sistem Operasi'),
            ExportColumn::make('os_version')
                ->label('Versi OS'),
            ExportColumn::make('processor')
                ->label('Prosesor'),
            ExportColumn::make('ram')
                ->label('RAM'),
            ExportColumn::make('storage_type')
                ->label('Tipe Penyimpanan'),
            ExportColumn::make('storage_capacity')
                ->label('Kapasitas Penyimpanan'),
            ExportColumn::make('condition')
                ->label('Kondisi'),
            ExportColumn::make('status')
                ->label('Status'),
            ExportColumn::make('location')
                ->label('Lokasi'),
            ExportColumn::make('purchase_date')
                ->label('Tanggal Pembelian'),
            ExportColumn::make('warranty_expiry')
                ->label('Masa Garansi Habis'),
            ExportColumn::make('notes')
                ->label('Catatan'),
            ExportColumn::make('created_at')
                ->label('Dibuat Pada'),
            ExportColumn::make('updated_at')
                ->label('Diperbarui Pada'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor perangkat telah selesai dan ' . number_format($export->successful_rows) . ' baris berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal diekspor.';
        }

        return $body;
    }
}
