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
    Schema::create('nguoi_dung', function (Blueprint $table) {
        $table->id();
        $table->string('username', 100)->unique();
        $table->string('password', 255);
        $table->string('email', 100)->unique();
        $table->string('ho_ten', 100)->nullable();
        $table->string('sdt', 20)->nullable();
        $table->string('dia_chi', 255)->nullable();
        $table->string('cccd', 20)->nullable();
        $table->string('img', 255)->nullable();
        $table->enum('vai_tro', ['admin', 'nhan_vien', 'khach_hang'])->default('khach_hang');
        $table->enum('trang_thai', ['hoat_dong', 'khoa'])->default('hoat_dong');
        $table->timestamp('ngay_tao')->useCurrent();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nguoi_dung');
    }
};
