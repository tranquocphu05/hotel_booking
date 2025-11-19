<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make this migration safe to run even if parts were applied previously
        Schema::table('booking_services', function (Blueprint $table) {
            // Add nullable invoice_id if it does not already exist
            if (!Schema::hasColumn('booking_services', 'invoice_id')) {
                $table->foreignId('invoice_id')->nullable()->constrained('hoa_don')->nullOnDelete();
            }

            // Do not attempt to modify existing unique indexes here to avoid FK/index dependency issues.
            // We're only adding a nullable `invoice_id` column to allow invoice-scoped service rows.
        });
    }

    public function down(): void
    {
        Schema::table('booking_services', function (Blueprint $table) {
            if (Schema::hasColumn('booking_services', 'invoice_id')) {
                try {
                    $table->dropConstrainedForeignId('invoice_id');
                } catch (\Throwable $e) {
                    // ignore
                }
                try {
                    $table->dropColumn('invoice_id');
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        });
    }
};
