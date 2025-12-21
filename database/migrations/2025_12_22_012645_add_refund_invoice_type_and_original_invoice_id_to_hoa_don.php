<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, add the original_invoice_id column
        Schema::table('hoa_don', function (Blueprint $table) {
            $table->unsignedBigInteger('original_invoice_id')->nullable()->after('invoice_type');
            $table->foreign('original_invoice_id')->references('id')->on('hoa_don')->onDelete('set null');
        });

        // Then modify the enum to include REFUND
        try {
            DB::statement(
                "ALTER TABLE `hoa_don` MODIFY `invoice_type` ENUM('PREPAID','EXTRA','REFUND') NOT NULL DEFAULT 'PREPAID' AFTER `trang_thai`;"
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to add REFUND to invoice_type enum: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove REFUND from enum first
        try {
            DB::statement(
                "ALTER TABLE `hoa_don` MODIFY `invoice_type` ENUM('PREPAID','EXTRA') NOT NULL DEFAULT 'PREPAID' AFTER `trang_thai`;"
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to remove REFUND from invoice_type enum: ' . $e->getMessage());
        }

        // Then drop the original_invoice_id column
        Schema::table('hoa_don', function (Blueprint $table) {
            $table->dropForeign(['original_invoice_id']);
            $table->dropColumn('original_invoice_id');
        });
    }
};
