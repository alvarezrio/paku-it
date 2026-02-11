<?php

namespace App\Filament\Imports;

use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class UserImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nama')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('nip')
                ->label('NIP')
                ->requiredMapping()
                ->rules(['required', 'digits:9', 'unique:users,nip']),
            ImportColumn::make('phone_number')
                ->label('No. HP')
                ->rules(['nullable', 'max:20']),
            ImportColumn::make('email')
                ->label('Email')
                ->rules(['nullable', 'email', 'max:255']),
            ImportColumn::make('password')
                ->rules(['max:255']),
        ];
    }

    public function resolveRecord(): ?User
    {
        // return User::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new User();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Impor user telah selesai dan ' . number_format($import->successful_rows) . ' baris berhasil diimpor.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal diimpor.';
        }

        return $body;
    }
}
