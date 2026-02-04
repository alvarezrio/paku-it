<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Check if index exists
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Index untuk tabel devices
        Schema::table('devices', function (Blueprint $table) {
            if (!$this->indexExists('devices', 'devices_status_index')) {
                $table->index('status');
            }
            if (!$this->indexExists('devices', 'devices_condition_index')) {
                $table->index('condition');
            }
            if (!$this->indexExists('devices', 'devices_type_index')) {
                $table->index('type');
            }
            if (!$this->indexExists('devices', 'devices_created_at_index')) {
                $table->index('created_at');
            }
            if (!$this->indexExists('devices', 'devices_created_at_id_index')) {
                $table->index(['created_at', 'id'], 'devices_created_at_id_index');
            }
        });

        // Index untuk tabel tickets
        Schema::table('tickets', function (Blueprint $table) {
            if (!$this->indexExists('tickets', 'tickets_assigned_to_index')) {
                $table->index('assigned_to');
            }
            if (!$this->indexExists('tickets', 'tickets_status_index')) {
                $table->index('status');
            }
            if (!$this->indexExists('tickets', 'tickets_priority_index')) {
                $table->index('priority');
            }
            if (!$this->indexExists('tickets', 'tickets_category_index')) {
                $table->index('category');
            }
            if (!$this->indexExists('tickets', 'tickets_created_at_index')) {
                $table->index('created_at');
            }
            if (!$this->indexExists('tickets', 'tickets_status_user_id_index')) {
                $table->index(['status', 'user_id'], 'tickets_status_user_id_index');
            }
        });

        // Index untuk tabel vehicle_bookings
        Schema::table('vehicle_bookings', function (Blueprint $table) {
            if (!$this->indexExists('vehicle_bookings', 'vehicle_bookings_status_index')) {
                $table->index('status');
            }
            if (!$this->indexExists('vehicle_bookings', 'vehicle_bookings_dates_index')) {
                $table->index(['start_date', 'end_date'], 'vehicle_bookings_dates_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            if ($this->indexExists('devices', 'devices_status_index')) {
                $table->dropIndex('devices_status_index');
            }
            if ($this->indexExists('devices', 'devices_condition_index')) {
                $table->dropIndex('devices_condition_index');
            }
            if ($this->indexExists('devices', 'devices_type_index')) {
                $table->dropIndex('devices_type_index');
            }
            if ($this->indexExists('devices', 'devices_created_at_index')) {
                $table->dropIndex('devices_created_at_index');
            }
            if ($this->indexExists('devices', 'devices_created_at_id_index')) {
                $table->dropIndex('devices_created_at_id_index');
            }
        });

        Schema::table('tickets', function (Blueprint $table) {
            if ($this->indexExists('tickets', 'tickets_assigned_to_index')) {
                $table->dropIndex('tickets_assigned_to_index');
            }
            if ($this->indexExists('tickets', 'tickets_status_index')) {
                $table->dropIndex('tickets_status_index');
            }
            if ($this->indexExists('tickets', 'tickets_priority_index')) {
                $table->dropIndex('tickets_priority_index');
            }
            if ($this->indexExists('tickets', 'tickets_category_index')) {
                $table->dropIndex('tickets_category_index');
            }
            if ($this->indexExists('tickets', 'tickets_created_at_index')) {
                $table->dropIndex('tickets_created_at_index');
            }
            if ($this->indexExists('tickets', 'tickets_status_user_id_index')) {
                $table->dropIndex('tickets_status_user_id_index');
            }
        });

        Schema::table('vehicle_bookings', function (Blueprint $table) {
            if ($this->indexExists('vehicle_bookings', 'vehicle_bookings_status_index')) {
                $table->dropIndex('vehicle_bookings_status_index');
            }
            if ($this->indexExists('vehicle_bookings', 'vehicle_bookings_dates_index')) {
                $table->dropIndex('vehicle_bookings_dates_index');
            }
        });
    }
};
