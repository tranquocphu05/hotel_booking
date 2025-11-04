<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Drop phong table completely as we no longer need to track individual rooms
     * We only track room quantities in loai_phong table
     */
    public function up(): void
    {
        // Disable foreign key checks to avoid constraint errors
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::dropIfExists('phong');

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     *
     * Recreate phong table structure (for rollback purposes)
     */
    public function down(): void
    {
        Schema::create('phong', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loai_phong_id')->constrained('loai_phong')->onDelete('cascade');
            $table->string('ten_phong', 100)->nullable();
            $table->text('mo_ta')->nullable();
            $table->decimal('gia', 15, 2)->nullable();
            $table->enum('trang_thai', ['trong', 'da_dat', 'bao_tri'])->default('trong');
            $table->string('img', 255)->nullable();
        });
    }
};
