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
        // Drop pivot table dat_phong_phong (đã chuyển sang dùng phong_ids JSON trong dat_phong)
        Schema::dropIfExists('dat_phong_phong');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate pivot table if needed (for rollback)
        Schema::create('dat_phong_phong', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dat_phong_id')->constrained('dat_phong')->onDelete('cascade');
            $table->foreignId('phong_id')->constrained('phong')->onDelete('cascade');
            $table->timestamps();
            
            // Prevent duplicate assignments
            $table->unique(['dat_phong_id', 'phong_id']);
        });
    }
};
