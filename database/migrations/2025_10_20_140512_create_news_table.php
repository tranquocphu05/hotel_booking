<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('tieu_de');
            $table->string('slug')->unique();
            $table->text('tom_tat');
            $table->longText('noi_dung');
            $table->string('hinh_anh')->nullable();
            $table->enum('trang_thai', ['draft', 'published', 'archived'])->default('draft');
            $table->integer('luot_xem')->default(0);
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dung')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
