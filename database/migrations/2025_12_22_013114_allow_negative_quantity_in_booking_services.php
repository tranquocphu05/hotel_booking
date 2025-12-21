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
        // Change quantity column from UNSIGNED INTEGER to INTEGER (signed) to allow negative values for refunds
        DB::statement('ALTER TABLE `booking_services` MODIFY `quantity` INT NOT NULL DEFAULT 1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to UNSIGNED INTEGER
        // Note: This will fail if there are negative values in the database
        DB::statement('ALTER TABLE `booking_services` MODIFY `quantity` INT UNSIGNED NOT NULL DEFAULT 1');
    }
};
