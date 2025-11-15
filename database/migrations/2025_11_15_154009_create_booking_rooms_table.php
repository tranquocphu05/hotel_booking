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
        Schema::create('booking_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dat_phong_id')->constrained('dat_phong')->onDelete('cascade');
            $table->foreignId('phong_id')->constrained('phong')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('dat_phong_id');
            $table->index('phong_id');
            
            // Prevent duplicate room in same booking
            $table->unique(['dat_phong_id', 'phong_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_rooms');
    }
};
