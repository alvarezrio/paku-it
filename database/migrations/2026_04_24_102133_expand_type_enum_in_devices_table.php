<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambah tipe perangkat baru: printer, scanner, router, switch, access-point, other
     * untuk mendukung pengelompokan tab di halaman Daftar Perangkat.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE devices MODIFY COLUMN type ENUM(
            'laptop',
            'desktop',
            'all-in-one',
            'workstation',
            'printer',
            'scanner',
            'router',
            'switch',
            'access-point',
            'other'
        ) NOT NULL DEFAULT 'desktop'");
    }

    public function down(): void
    {
        // Kembalikan tipe baru ke 'other' atau 'desktop' sebelum rollback enum
        DB::statement("UPDATE devices SET type = 'desktop'
            WHERE type IN ('printer','scanner','router','switch','access-point','other')");

        DB::statement("ALTER TABLE devices MODIFY COLUMN type ENUM(
            'laptop',
            'desktop',
            'all-in-one',
            'workstation'
        ) NOT NULL DEFAULT 'desktop'");
    }
};
