<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBookingServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_services', function (Blueprint $table) {
            // Drop the foreign key constraint that depends on the unique index
            $table->dropForeign(['dat_phong_id']);

            // Drop the existing unique index
            $table->dropUnique('booking_services_dat_phong_id_service_id_used_at_unique');

            // Add a new unique index including invoice_id
            $table->unique(['dat_phong_id', 'service_id', 'used_at', 'invoice_id'], 'booking_services_unique_with_invoice_id');

            // Recreate the foreign key constraint
            $table->foreign('dat_phong_id')->references('id')->on('dat_phong')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booking_services', function (Blueprint $table) {
            // Drop the new unique index
            $table->dropUnique('booking_services_unique_with_invoice_id');

            // Restore the original unique index
            $table->unique(['dat_phong_id', 'service_id', 'used_at'], 'booking_services_dat_phong_id_service_id_used_at_unique');
        });
    }
}