<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First normalize/backfill existing values so the ALTER will not fail due to unknown values.
        try {
            // If there is clear evidence of online payment, mark PREPAID
            \Illuminate\Support\Facades\DB::table('hoa_don')
                ->whereNotNull('phuong_thuc')
                ->where('trang_thai', 'da_thanh_toan')
                ->update(['invoice_type' => 'PREPAID']);

            // Normalize any values that contain the word EXTRA to exactly 'EXTRA'
            \Illuminate\Support\Facades\DB::table('hoa_don')
                ->where('invoice_type', 'LIKE', '%EXTRA%')
                ->update(['invoice_type' => 'EXTRA']);

            // Any remaining invalid/unknown values -> set to PREPAID by default
            \Illuminate\Support\Facades\DB::statement(
                "UPDATE `hoa_don` SET `invoice_type` = 'PREPAID' WHERE `invoice_type` NOT IN ('PREPAID','EXTRA') OR `invoice_type` IS NULL;"
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Backfill/normalize before ALTER failed: ' . $e->getMessage());
            // Rethrow because if normalization fails, ALTER will likely fail too.
            throw $e;
        }

        // Use a direct statement to convert to ENUM after normalization
        try {
            \Illuminate\Support\Facades\DB::statement(
                "ALTER TABLE `hoa_don` MODIFY `invoice_type` ENUM('PREPAID','EXTRA') NOT NULL DEFAULT 'PREPAID' AFTER `trang_thai`;"
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to alter invoice_type to ENUM: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        try {
            // Revert to previous state: varchar(20) default 'STANDARD'
            \Illuminate\Support\Facades\DB::statement(
                "ALTER TABLE `hoa_don` MODIFY `invoice_type` VARCHAR(20) NOT NULL DEFAULT 'STANDARD' AFTER `trang_thai`;"
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to revert invoice_type to VARCHAR: ' . $e->getMessage());
            throw $e;
        }
    }
};
