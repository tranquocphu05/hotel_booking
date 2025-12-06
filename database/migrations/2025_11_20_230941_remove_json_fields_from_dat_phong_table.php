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
    Schema::table('dat_phong', function (Blueprint $table) {
        if (!Schema::hasColumn('dat_phong', 'room_types')) {
            $table->json('room_types')->nullable()->after('loai_phong_id');
        }

        if (!Schema::hasColumn('dat_phong', 'phong_ids')) {
            $table->json('phong_ids')->nullable()->after('room_types');
        }
    });
}

public function down(): void
{
    Schema::table('dat_phong', function (Blueprint $table) {
        $table->dropColumn('room_types');
        $table->dropColumn('phong_ids');
    });
}

};
