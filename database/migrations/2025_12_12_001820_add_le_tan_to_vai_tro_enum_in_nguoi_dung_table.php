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
        // Thêm giá trị 'le_tan' vào ENUM vai_tro
        // MySQL yêu cầu định nghĩa lại toàn bộ ENUM với tất cả giá trị
        DB::statement("ALTER TABLE `nguoi_dung` MODIFY `vai_tro` ENUM('admin', 'nhan_vien', 'le_tan', 'khach_hang') DEFAULT 'khach_hang'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa giá trị 'le_tan' khỏi ENUM (chỉ giữ lại 3 giá trị ban đầu)
        // Lưu ý: Nếu có user nào đang dùng 'le_tan', cần chuyển về 'khach_hang' trước
        DB::statement("UPDATE `nguoi_dung` SET `vai_tro` = 'khach_hang' WHERE `vai_tro` = 'le_tan'");
        DB::statement("ALTER TABLE `nguoi_dung` MODIFY `vai_tro` ENUM('admin', 'nhan_vien', 'khach_hang') DEFAULT 'khach_hang'");
    }
};
