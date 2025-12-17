<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refund_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('hoa_don_id');
            $table->unsignedBigInteger('dat_phong_id');

            $table->unsignedBigInteger('booking_service_id');
            $table->json('booking_room_ids');

            $table->decimal('total_refund', 10, 2)->default(0);

            // Use string columns to avoid SQLite ENUM issues in tests; values enforced at app level
            $table->string('refund_method', 32)->default('tien_mat');
            $table->string('refund_status', 32)->default('cho_xu_ly');

            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_account_name', 255)->nullable();
            $table->string('bank_name', 255)->nullable();

            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('hoa_don_id');
            $table->index('dat_phong_id');
            $table->index('booking_service_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_services');
    }
};