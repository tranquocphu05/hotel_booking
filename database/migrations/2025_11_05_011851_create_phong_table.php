<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Create phong table to track individual rooms
     * Each room belongs to a room type (loai_phong_id)
     */
    public function up(): void
    {
        Schema::create('phong', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loai_phong_id')->constrained('loai_phong')->onDelete('cascade');
            $table->string('so_phong', 20)->unique()->comment('Room number, e.g., "101", "201A", "Suite-301"');
            $table->string('ten_phong', 255)->nullable()->comment('Room name, e.g., "Phòng Honeymoon", "Phòng View Biển"');
            $table->integer('tang')->nullable()->comment('Floor number');
            $table->enum('huong_cua_so', ['bien', 'nui', 'thanh_pho', 'san_vuon'])->nullable()->comment('Window direction/view');
            $table->enum('trang_thai', ['trong', 'da_dat', 'bao_tri', 'dang_ve_sinh'])->default('trong')->comment('Room status');
            $table->text('ghi_chu')->nullable()->comment('Special notes');
            $table->boolean('co_ban_cong')->default(false)->comment('Has balcony');
            $table->boolean('co_view_dep')->default(false)->comment('Has good view');
            $table->decimal('gia_bo_sung', 15, 2)->nullable()->comment('Additional price for special features (e.g., better view)');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['loai_phong_id', 'trang_thai']);
            $table->index('so_phong');
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
