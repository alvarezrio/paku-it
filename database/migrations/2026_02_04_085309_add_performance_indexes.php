<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Index untuk tabel devices
        Schema::table('devices', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('status');
            $table->index('condition');
            $table->index('type');
            $table->index('created_at');
            $table->index(['created_at', 'id']); // Composite untuk pagination
        });

        // Index untuk tabel tickets
        Schema::table('tickets', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('assigned_to');
            $table->index('status');
            $table->index('priority');
            $table->index('category');
            $table->index('created_at');
            $table->index(['status', 'user_id']); // Composite untuk filter user
        });

        // Index untuk tabel vehicle_bookings
        Schema::table('vehicle_bookings', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('vehicle_id');
            $table->index('status');
            $table->index(['start_date', 'end_date']); // Composite untuk cek bentrok
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['condition']);
            $table->dropIndex(['type']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['created_at', 'id']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['assigned_to']);
            $table->dropIndex(['status']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['category']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'user_id']);
        });

        Schema::table('vehicle_bookings', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['vehicle_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['start_date', 'end_date']);
        });
    }
};
