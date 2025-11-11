<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_services', function (Blueprint $table) {
            $table->id();

            // 🔹 Khóa ngoại tới bảng đặt phòng
            $table->foreignId('booking_id')
                ->constrained('bookings') // hoặc 'dat_phong' nếu bạn giữ nguyên tiếng Việt
                ->cascadeOnDelete();

            // 🔹 Khóa ngoại tới bảng dịch vụ
            $table->foreignId('service_id')
                ->constrained('services')
                ->cascadeOnDelete();

            // 🔹 Thông tin dịch vụ
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->date('used_at');
            $table->text('note')->nullable();

            $table->timestamps();

            // 🔸 Một dịch vụ không thể được ghi trùng cho cùng một booking cùng ngày
            $table->unique(['booking_id', 'service_id', 'used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_services');
    }
};
