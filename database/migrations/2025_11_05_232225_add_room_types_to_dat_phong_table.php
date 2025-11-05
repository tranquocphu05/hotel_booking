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
            // Thêm trường JSON để lưu danh sách các loại phòng trong booking
            // Format: [{"loai_phong_id": 1, "so_luong": 2, "gia_rieng": 1000000}, ...]
            $table->json('room_types')->nullable()->after('loai_phong_id')
                ->comment('Danh sách các loại phòng trong booking (JSON array)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_phong', function (Blueprint $table) {
            $table->dropColumn('room_types');
        });
    }
};
