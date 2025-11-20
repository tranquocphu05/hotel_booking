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
     */
    public function up(): void
    {
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
