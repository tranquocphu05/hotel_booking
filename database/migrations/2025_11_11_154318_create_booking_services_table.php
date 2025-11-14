<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_services', function (Blueprint $table) {
            $table->id();

            // 🔹 Khóa ngoại tới bảng ĐẶT PHÒNG
            $table->foreignId('dat_phong_id')
                ->constrained('dat_phong')
                ->cascadeOnDelete();

            // 🔹 Khóa ngoại tới bảng DỊCH VỤ
            $table->foreignId('service_id')
                ->constrained('services')
                ->cascadeOnDelete();

            // 🔹 Thông tin chi tiết dịch vụ
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->date('used_at');
            $table->text('note')->nullable();

            $table->timestamps();

            // 🔸 Đảm bảo không ghi trùng cùng dịch vụ trong cùng booking, cùng ngày
            $table->unique(['dat_phong_id', 'service_id', 'used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_services');
    }
};
