<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom khusus printer/scanner:
     * - printer_connection: jenis koneksi (USB, Network, Wireless, Bluetooth)
     * - printer_function: fungsi yang didukung (Print, Scan, Copy, Fax)
     */
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->enum('printer_connection', ['USB', 'Network', 'Wireless', 'Bluetooth'])
                ->nullable()
                ->after('storage_capacity');

            $table->string('printer_function')->nullable()->after('printer_connection');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn(['printer_connection', 'printer_function']);
        });
    }
};
