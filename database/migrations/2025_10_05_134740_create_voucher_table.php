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
    Schema::create('voucher', function (Blueprint $table) {
        $table->id();
        $table->string('ma_voucher', 50)->unique();
        $table->decimal('gia_tri', 10, 2)->nullable();
        $table->date('ngay_bat_dau')->nullable();
        $table->date('ngay_ket_thuc')->nullable();
        $table->integer('so_luong')->nullable();
        $table->string('dieu_kien', 255)->nullable();
        $table->enum('trang_thai', ['con_han','het_han','huy'])->default('con_han');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher');
    }
};
