<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('dat_phong', 'phong_ids')) {
            Schema::table('dat_phong', function (Blueprint $table) {
                $table->json('phong_ids')->nullable()->after('room_types');
            });
        }

        // Migrate dữ liệu từ dat_phong_phong sang dat_phong.phong_ids
        $bookings = DB::table('dat_phong')->get();
        
        foreach ($bookings as $booking) {
            $phongIds = DB::table('dat_phong_phong')
                ->where('dat_phong_id', $booking->id)
                ->pluck('phong_id')
                ->toArray();
            
            if (!empty($phongIds)) {
                DB::table('dat_phong')
                    ->where('id', $booking->id)
                    ->update(['phong_ids' => json_encode($phongIds)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_phong', function (Blueprint $table) {
            $table->dropColumn('phong_ids');
        });
    }
};
