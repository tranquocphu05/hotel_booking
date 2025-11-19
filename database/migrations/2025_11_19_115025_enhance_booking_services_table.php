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
        Schema::table('booking_services', function (Blueprint $table) {
            // Add ghi_chu column if not exists
            if (!Schema::hasColumn('booking_services', 'ghi_chu')) {
                $table->text('ghi_chu')->nullable()->after('note');
            }
            
            // Modify used_at to allow NULL
            $table->dateTime('used_at')->nullable()->change();
        });
        
        // Drop unique constraint if exists
        try {
            DB::statement('ALTER TABLE booking_services DROP INDEX booking_services_dat_phong_id_service_id_used_at_unique');
        } catch (\Exception $e) {
            // Index might not exist, ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_services', function (Blueprint $table) {
            $table->dropColumn('ghi_chu');
        });
        
        // Restore unique constraint
        DB::statement('ALTER TABLE booking_services ADD UNIQUE booking_services_dat_phong_id_service_id_used_at_unique (dat_phong_id, service_id, used_at)');
    }
};
