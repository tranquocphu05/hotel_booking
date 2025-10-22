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
        Schema::table('loai_phong', function (Blueprint $table) {
            $table->decimal('diem_danh_gia', 3, 2)->default(0)->after('gia_co_ban')->comment('Điểm đánh giá từ 0-5 sao');
            $table->integer('so_luong_danh_gia')->default(0)->after('diem_danh_gia')->comment('Số lượng đánh giá');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loai_phong', function (Blueprint $table) {
            $table->dropColumn(['diem_danh_gia', 'so_luong_danh_gia']);
        });
    }
};
