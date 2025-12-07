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
        // Drop the existing unique index that doesn't include phong_id
        try {
            DB::statement('ALTER TABLE booking_services DROP INDEX booking_services_unique_with_invoice_id');
        } catch (\Exception $e) {
            // Index might not exist, continue
        }

        // Add new unique index that includes phong_id
        // This allows multiple entries for same service/date but different rooms
        DB::statement('ALTER TABLE booking_services ADD UNIQUE booking_services_unique_with_phong_id (dat_phong_id, service_id, used_at, invoice_id, phong_id)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the new unique index
        try {
            DB::statement('ALTER TABLE booking_services DROP INDEX booking_services_unique_with_phong_id');
        } catch (\Exception $e) {
            // Index might not exist, continue
        }

        // Restore the old unique index
        DB::statement('ALTER TABLE booking_services ADD UNIQUE booking_services_unique_with_invoice_id (dat_phong_id, service_id, used_at, invoice_id)');
    }
};
