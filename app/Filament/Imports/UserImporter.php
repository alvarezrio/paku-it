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
                ->rules(['required', 'max:255'])
                ->validationMessages([
                    'required' => 'Kolom Nama wajib diisi.',
                    'max' => 'Nama tidak boleh lebih dari 255 karakter.',
                ]),
            ImportColumn::make('nip')
                ->label('NIP')
                ->requiredMapping()
                ->rules(['required', 'digits:9', 'unique:users,nip'])
                ->validationMessages([
                    'required' => 'Kolom NIP wajib diisi.',
                    'digits' => 'NIP harus tepat 9 digit angka.',
                    'unique' => 'NIP :input sudah terdaftar di sistem.',
                ]),
            ImportColumn::make('phone_number')
                ->label('No. HP')
                ->rules(['nullable', 'max:20'])
                ->validationMessages([
                    'max' => 'No. HP tidak boleh lebih dari 20 karakter.',
                ]),
            ImportColumn::make('email')
                ->label('Email')
                ->rules(['nullable', 'email', 'max:255', 'unique:users,email'])
                ->validationMessages([
                    'email' => 'Format email :input tidak valid.',
                    'max' => 'Email tidak boleh lebih dari 255 karakter.',
                    'unique' => 'Email :input sudah digunakan oleh user lain.',
                ]),
            ImportColumn::make('password')
                ->rules(['nullable', 'max:255'])
                ->validationMessages([
                    'max' => 'Password tidak boleh lebih dari 255 karakter.',
                ]),
        ];
    }

    public function resolveRecord(): ?User
    {
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
