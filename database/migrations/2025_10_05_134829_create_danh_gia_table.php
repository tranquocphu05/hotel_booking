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
    Schema::create('danh_gia', function (Blueprint $table) {
        $table->id();
        $table->foreignId('nguoi_dung_id')->constrained('nguoi_dung')->onDelete('cascade');
        $table->foreignId('phong_id')->constrained('phong')->onDelete('cascade');
        $table->text('noi_dung')->nullable();
        $table->integer('so_sao')->check('so_sao between 1 and 5')->nullable();
        $table->string('img', 255)->nullable();
        $table->timestamp('ngay_danh_gia')->useCurrent();
        $table->enum('trang_thai', ['hien_thi','an'])->default('hien_thi');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('danh_gia');
    }
};
