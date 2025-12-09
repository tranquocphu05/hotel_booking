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
            // Drop the foreign key that depends on the old unique index
            $table->dropForeign(['dat_phong_id']);
            
            // Drop the old unique index that includes invoice_id
            $table->dropUnique('booking_services_unique_with_invoice_id');
            
            // Create new unique index with phong_id instead of invoice_id
            // This allows multiple rows per service/date/invoice but different rooms
            $table->unique(['dat_phong_id', 'service_id', 'used_at', 'phong_id'], 'booking_services_unique_with_phong_id');
            
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
            // Drop the foreign key
            $table->dropForeign(['dat_phong_id']);
            
            // Drop the new unique index
            $table->dropUnique('booking_services_unique_with_phong_id');
            
            // Restore the old unique index
            $table->unique(['dat_phong_id', 'service_id', 'used_at', 'invoice_id'], 'booking_services_unique_with_invoice_id');
            
            // Recreate the foreign key
            $table->foreign('dat_phong_id')->references('id')->on('dat_phong')->onDelete('cascade');
        });
    }
};
