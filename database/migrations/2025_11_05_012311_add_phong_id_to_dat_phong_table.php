<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Thêm lại phong_id vào dat_phong để link booking với phòng cụ thể
     * Nullable: có thể gán sau khi booking được confirm
     */
    public function up(): void
    {
        // Kiểm tra xem cột đã tồn tại chưa (để tránh lỗi nếu chạy lại migration)
        if (!Schema::hasColumn('dat_phong', 'phong_id')) {
        Schema::table('dat_phong', function (Blueprint $table) {
                $table->foreignId('phong_id')->nullable()->after('so_luong_da_dat')
                    ->constrained('phong')->onDelete('set null')
                    ->comment('Phòng cụ thể được gán cho booking (nullable, có thể gán sau)');
                
                // Index cho performance khi query theo phòng
                $table->index('phong_id');
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_phong', function (Blueprint $table) {
            $table->dropForeign(['phong_id']);
            $table->dropIndex(['phong_id']);
            $table->dropColumn('phong_id');
        });
    }
};
