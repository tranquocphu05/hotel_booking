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
        if (!Schema::hasTable('invoice_items')) {
            Schema::create('invoice_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained('hoa_don')->onDelete('cascade');
                $table->string('type')->nullable(); // e.g. room, extra_guest, service, adjustment
                $table->text('description')->nullable();
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->integer('days')->nullable();
                $table->decimal('amount', 12, 2)->default(0);
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->json('meta')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->text('reason')->nullable();
                $table->timestamps();

                $table->index(['invoice_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
