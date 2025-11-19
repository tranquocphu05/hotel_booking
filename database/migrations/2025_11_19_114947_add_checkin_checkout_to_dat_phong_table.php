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
        Schema::table('dat_phong', function (Blueprint $table) {
            $table->dateTime('thoi_gian_checkin')->nullable()->after('tong_tien');
            $table->dateTime('thoi_gian_checkout')->nullable()->after('thoi_gian_checkin');
            $table->string('nguoi_checkin')->nullable()->after('thoi_gian_checkout');
            $table->string('nguoi_checkout')->nullable()->after('nguoi_checkin');
            $table->decimal('phi_phat_sinh', 10, 2)->default(0)->after('nguoi_checkout');
            $table->text('ghi_chu_checkin')->nullable()->after('phi_phat_sinh');
            $table->text('ghi_chu_checkout')->nullable()->after('ghi_chu_checkin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_phong', function (Blueprint $table) {
            $table->dropColumn([
                'thoi_gian_checkin',
                'thoi_gian_checkout',
                'nguoi_checkin',
                'nguoi_checkout',
                'phi_phat_sinh',
                'ghi_chu_checkin',
                'ghi_chu_checkout',
            ]);
        });
    }
};
