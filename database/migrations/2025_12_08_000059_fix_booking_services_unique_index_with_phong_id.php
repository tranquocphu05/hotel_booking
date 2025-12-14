<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Check if a foreign key exists on a table
     */
    private function foreignKeyExists(string $table, string $column): bool
    {
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$table, $column]);
        return count($foreignKeys) > 0;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('booking_services', function (Blueprint $table) {
            // Drop the foreign key that depends on the old unique index (if exists)
            if ($this->foreignKeyExists('booking_services', 'dat_phong_id')) {
                $table->dropForeign(['dat_phong_id']);
            }
        });

        Schema::table('booking_services', function (Blueprint $table) {
            // Drop the old unique index that includes invoice_id (if exists)
            if ($this->indexExists('booking_services', 'booking_services_unique_with_invoice_id')) {
                $table->dropUnique('booking_services_unique_with_invoice_id');
            }
            
            // Create new unique index with phong_id instead of invoice_id (if not exists)
            // This allows multiple rows per service/date/invoice but different rooms
            if (!$this->indexExists('booking_services', 'booking_services_unique_with_phong_id')) {
                $table->unique(['dat_phong_id', 'service_id', 'used_at', 'phong_id'], 'booking_services_unique_with_phong_id');
            }
        });

        Schema::table('booking_services', function (Blueprint $table) {
            // Recreate the foreign key (if not exists)
            if (!$this->foreignKeyExists('booking_services', 'dat_phong_id')) {
                $table->foreign('dat_phong_id')->references('id')->on('dat_phong')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_services', function (Blueprint $table) {
            // Drop the foreign key (if exists)
            if ($this->foreignKeyExists('booking_services', 'dat_phong_id')) {
                $table->dropForeign(['dat_phong_id']);
            }
        });

        Schema::table('booking_services', function (Blueprint $table) {
            // Drop the new unique index (if exists)
            if ($this->indexExists('booking_services', 'booking_services_unique_with_phong_id')) {
                $table->dropUnique('booking_services_unique_with_phong_id');
            }
            
            // Restore the old unique index (if not exists)
            if (!$this->indexExists('booking_services', 'booking_services_unique_with_invoice_id')) {
                $table->unique(['dat_phong_id', 'service_id', 'used_at', 'invoice_id'], 'booking_services_unique_with_invoice_id');
            }
        });

        Schema::table('booking_services', function (Blueprint $table) {
            // Recreate the foreign key (if not exists)
            if (!$this->foreignKeyExists('booking_services', 'dat_phong_id')) {
                $table->foreign('dat_phong_id')->references('id')->on('dat_phong')->onDelete('cascade');
            }
        });
    }
};
