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
        Schema::create('loai_phong', function (Blueprint $table) {
            $table->id();
            $table->string('ten_loai', 100);
            $table->text('mo_ta')->nullable();
            $table->decimal('gia_co_ban', 15, 2);
            $table->enum('trang_thai', ['hoat_dong', 'ngung'])->default('hoat_dong');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loai_phong', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};
