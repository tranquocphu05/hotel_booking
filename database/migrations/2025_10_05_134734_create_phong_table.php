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
    // If the table already exists (for example because it was created manually), skip creation
    if (Schema::hasTable('phong')) {
        return;
    }

    Schema::create('phong', function (Blueprint $table) {
        $table->id();
        $table->foreignId('loai_phong_id')->constrained('loai_phong')->onDelete('cascade');
        $table->string('ten_phong', 100)->nullable();
        $table->text('mo_ta')->nullable();
        $table->decimal('gia', 15, 2)->nullable();
        $table->enum('trang_thai', ['hien', 'an', 'Bảo Trì'])->default('hien');
        $table->string('img', 255)->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phong');
    }
};
