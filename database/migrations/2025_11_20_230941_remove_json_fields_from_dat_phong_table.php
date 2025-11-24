<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Remove JSON fields (room_types and phong_ids) from dat_phong table
     * as we now use pivot tables (booking_room_types and booking_rooms)
     *
     * NOTE: This migration assumes pivot tables already exist from earlier migrations:
     * - 2025_11_15_153929_create_booking_room_types_table.php
     * - 2025_11_15_154009_create_booking_rooms_table.php
     */
    public function up(): void
    {
        // Verify pivot tables exist before dropping JSON columns
        if (!Schema::hasTable('booking_room_types')) {
            throw new \RuntimeException('Pivot table booking_room_types does not exist. Please run migrations in order.');
        }

        if (!Schema::hasTable('booking_rooms')) {
            throw new \RuntimeException('Pivot table booking_rooms does not exist. Please run migrations in order.');
        }

        Schema::table('dat_phong', function (Blueprint $table) {
            // Check if columns exist before dropping
            if (Schema::hasColumn('dat_phong', 'room_types')) {
                $table->dropColumn('room_types');
            }

            if (Schema::hasColumn('dat_phong', 'phong_ids')) {
                $table->dropColumn('phong_ids');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * Restore JSON fields if needed (for rollback)
     */
    public function down(): void
    {
        Schema::table('dat_phong', function (Blueprint $table) {
            // Restore JSON columns
            $table->json('room_types')->nullable()->after('loai_phong_id');
            $table->json('phong_ids')->nullable()->after('room_types');
        });
    }
};
