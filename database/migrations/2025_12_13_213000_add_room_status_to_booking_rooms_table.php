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
        if (!Schema::hasColumn('booking_rooms', 'thoi_gian_checkin')) {
            Schema::table('booking_rooms', function (Blueprint $table) {
                $table->dateTime('thoi_gian_checkin')->nullable()->after('phu_phi');
            });
        }
        
        if (!Schema::hasColumn('booking_rooms', 'thoi_gian_checkout')) {
            Schema::table('booking_rooms', function (Blueprint $table) {
                $table->dateTime('thoi_gian_checkout')->nullable()->after('thoi_gian_checkin');
            });
        }
        
        if (!Schema::hasColumn('booking_rooms', 'trang_thai_phong')) {
            Schema::table('booking_rooms', function (Blueprint $table) {
                $table->string('trang_thai_phong', 50)->nullable()->after('thoi_gian_checkout');
            });
        }
        
        if (!Schema::hasColumn('booking_rooms', 'phi_phat_sinh_phong')) {
            Schema::table('booking_rooms', function (Blueprint $table) {
                $table->decimal('phi_phat_sinh_phong', 15, 2)->nullable()->after('trang_thai_phong');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_rooms', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('booking_rooms', 'thoi_gian_checkin')) {
                $columnsToDrop[] = 'thoi_gian_checkin';
            }
            if (Schema::hasColumn('booking_rooms', 'thoi_gian_checkout')) {
                $columnsToDrop[] = 'thoi_gian_checkout';
            }
            if (Schema::hasColumn('booking_rooms', 'trang_thai_phong')) {
                $columnsToDrop[] = 'trang_thai_phong';
            }
            if (Schema::hasColumn('booking_rooms', 'phi_phat_sinh_phong')) {
                $columnsToDrop[] = 'phi_phat_sinh_phong';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
