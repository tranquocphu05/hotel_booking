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
        Schema::table('booking_services', function (Blueprint $table) {
            // Drop the foreign key first (it references the unique index)
            $table->dropForeign(['dat_phong_id']);
            
            // Drop the old unique index that does NOT include invoice_id
            $table->dropUnique('booking_services_unique_with_phong_id');
            
            // Create new unique index WITH invoice_id so:
            // - Same booking + service + date + room + invoice_id => same row (can merge)
            // - Same booking + service + date + room + different invoice_id => different rows (allows EXTRA invoices)
            $table->unique(['dat_phong_id', 'service_id', 'used_at', 'phong_id', 'invoice_id'], 'booking_services_unique_with_invoice');
            
            // Recreate the foreign key
            $table->foreign('dat_phong_id')->references('id')->on('dat_phong')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_services', function (Blueprint $table) {
            // Drop the foreign key first
            $table->dropForeign(['dat_phong_id']);
            
            // Drop the new unique index
            $table->dropUnique('booking_services_unique_with_invoice');
            
            // Restore the old unique index (without invoice_id)
            $table->unique(['dat_phong_id', 'service_id', 'used_at', 'phong_id'], 'booking_services_unique_with_phong_id');
            
            // Recreate the foreign key
            $table->foreign('dat_phong_id')->references('id')->on('dat_phong')->onDelete('cascade');
        });
    }
};
