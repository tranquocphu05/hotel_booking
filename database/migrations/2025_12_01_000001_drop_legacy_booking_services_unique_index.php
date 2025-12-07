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
     * @return void
     */
    public function up()
    {
        // Ensure the legacy index that doesn't include phong_id is removed.
        try {
            DB::statement('ALTER TABLE booking_services DROP INDEX booking_services_unique_with_invoice_id');
        } catch (\Exception $e) {
            // If it doesn't exist or cannot be dropped, log and continue.
            // We intentionally avoid stopping deployment here.
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Recreate the legacy index if rolling back this migration.
        DB::statement('ALTER TABLE booking_services ADD UNIQUE booking_services_unique_with_invoice_id (dat_phong_id, service_id, used_at, invoice_id)');
    }
};
