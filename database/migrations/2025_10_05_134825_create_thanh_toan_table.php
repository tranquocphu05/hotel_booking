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
    Schema::create('thanh_toan', function (Blueprint $table) {
        $table->id();
        $table->foreignId('hoa_don_id')->constrained('hoa_don')->onDelete('cascade');
        $table->decimal('so_tien', 15, 2)->nullable();
        $table->timestamp('ngay_thanh_toan')->useCurrent();
        $table->enum('trang_thai', ['pending','success','fail'])->default('pending');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thanh_toan');
    }
};
