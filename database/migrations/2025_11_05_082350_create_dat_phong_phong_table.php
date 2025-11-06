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
        Schema::create('dat_phong_phong', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dat_phong_id')->constrained('dat_phong')->onDelete('cascade')
                ->comment('Booking ID');
            $table->foreignId('phong_id')->constrained('phong')->onDelete('cascade')
                ->comment('Phòng cụ thể được gán cho booking');
            $table->timestamps();
            
            // Đảm bảo không gán trùng phòng cho cùng 1 booking
            $table->unique(['dat_phong_id', 'phong_id'], 'unique_booking_room');
            
            // Index để tăng performance khi query
            $table->index('dat_phong_id');
            $table->index('phong_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_phong_phong');
    }
};
