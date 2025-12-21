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
        Schema::create('booking_room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dat_phong_id')->constrained('dat_phong')->onDelete('cascade');
            $table->foreignId('loai_phong_id')->constrained('loai_phong')->onDelete('cascade');
            $table->integer('so_luong')->default(1);
            $table->decimal('gia_rieng', 15, 2);
            $table->integer('so_nguoi')->default(1);
            $table->integer('so_tre_em')->default(0);
            $table->integer('so_em_be')->default(0);
            $table->timestamps();
            
            // Indexes for performance
            $table->index('dat_phong_id');
            $table->index('loai_phong_id');
            
            // Prevent duplicate room type in same booking
            $table->unique(['dat_phong_id', 'loai_phong_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_room_types');
    }
};
