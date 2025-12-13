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
        Schema::table('booking_rooms', function (Blueprint $table) {
            $table->dateTime('thoi_gian_checkin')->nullable()->after('phu_phi');
            $table->dateTime('thoi_gian_checkout')->nullable()->after('thoi_gian_checkin');
            $table->string('trang_thai_phong', 50)->nullable()->after('thoi_gian_checkout');
            $table->decimal('phi_phat_sinh_phong', 15, 2)->nullable()->after('trang_thai_phong');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_rooms', function (Blueprint $table) {
            $table->dropColumn([
                'thoi_gian_checkin',
                'thoi_gian_checkout',
                'trang_thai_phong',
                'phi_phat_sinh_phong',
            ]);
        });
    }
};
