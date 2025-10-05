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
    Schema::create('dat_phong', function (Blueprint $table) {
        $table->id();
        $table->foreignId('nguoi_dung_id')->constrained('nguoi_dung')->onDelete('cascade');
        $table->foreignId('phong_id')->constrained('phong')->onDelete('cascade');
        $table->dateTime('ngay_dat')->useCurrent();
        $table->date('ngay_nhan');
        $table->date('ngay_tra');
        $table->integer('so_nguoi')->nullable();
        $table->decimal('tong_tien', 15, 2)->nullable();
        $table->unsignedBigInteger('voucher_id')->nullable();
        $table->enum('trang_thai', ['cho_xac_nhan','da_xac_nhan','da_huy','da_tra'])->default('cho_xac_nhan');

        $table->foreign('voucher_id')->references('id')->on('voucher')->nullOnDelete();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_phong');
    }
};
