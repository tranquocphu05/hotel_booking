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
    Schema::create('hoa_don', function (Blueprint $table) {
        $table->id();
        $table->foreignId('dat_phong_id')->constrained('dat_phong')->onDelete('cascade');
        $table->timestamp('ngay_tao')->useCurrent();
        $table->decimal('tong_tien', 15, 2)->nullable();
        $table->enum('phuong_thuc', ['tien_mat', 'chuyen_khoan', 'momo', 'vnpay'])->nullable();
        $table->enum('trang_thai', ['cho_thanh_toan', 'da_thanh_toan', 'hoan_tien'])->default('cho_thanh_toan');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hoa_don');
    }
};
