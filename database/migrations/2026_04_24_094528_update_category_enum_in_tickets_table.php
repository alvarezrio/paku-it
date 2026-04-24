<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengubah kolom category dari enum lama (hardware, software, network, printer, other)
     * ke 9 kategori layanan IT Helpdesk baru.
     */
    public function up(): void
    {
        // Ubah nilai data lama ke nilai default baru sebelum mengubah enum
        DB::statement("UPDATE tickets SET category = 'incident_management' WHERE category IN ('hardware', 'software', 'printer', 'other')");
        DB::statement("UPDATE tickets SET category = 'network_support' WHERE category = 'network'");

        // Ubah definisi kolom enum
        DB::statement("ALTER TABLE tickets MODIFY COLUMN category ENUM(
            'incident_management',
            'service_request',
            'user_support',
            'access_management',
            'asset_management',
            'change_management',
            'network_support',
            'security_support',
            'documentation_kb'
        ) NOT NULL DEFAULT 'incident_management'");
    }

    /**
     * Reverse the migrations.
     * Kembalikan ke enum lama jika rollback.
     */
    public function down(): void
    {
        // Kembalikan data ke nilai enum lama (gunakan 'other' sebagai fallback)
        DB::statement("UPDATE tickets SET category = 'network' WHERE category = 'network_support'");
        DB::statement("UPDATE tickets SET category = 'other' WHERE category IN (
            'incident_management', 'service_request', 'user_support',
            'access_management', 'asset_management', 'change_management',
            'security_support', 'documentation_kb'
        )");

        DB::statement("ALTER TABLE tickets MODIFY COLUMN category ENUM(
            'hardware',
            'software',
            'network',
            'printer',
            'other'
        ) NOT NULL DEFAULT 'hardware'");
    }
};
