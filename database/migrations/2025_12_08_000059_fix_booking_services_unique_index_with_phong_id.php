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
        // If table doesn't exist, nothing to do
        if (!Schema::hasTable('booking_services')) {
            return;
        }

        // Safely drop old foreign key if exists
        $fkExists = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'booking_services' AND COLUMN_NAME = 'dat_phong_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
        if (!empty($fkExists)) {
            Schema::table('booking_services', function (Blueprint $table) {
                try {
                    $table->dropForeign(['dat_phong_id']);
                } catch (\Exception $e) {
                    // ignore if FK can't be dropped
                }
            });
        }

        // Safely drop old unique index if it exists
        $idx = DB::select("SHOW INDEX FROM booking_services WHERE Key_name = ?", ['booking_services_unique_with_invoice_id']);
        if (!empty($idx)) {
            Schema::table('booking_services', function (Blueprint $table) {
                try {
                    $table->dropUnique('booking_services_unique_with_invoice_id');
                } catch (\Exception $e) {
                    // ignore if can't drop
                }
            });
        }

        // Create new unique index with phong_id instead of invoice_id if not exists
        $idx2 = DB::select("SHOW INDEX FROM booking_services WHERE Key_name = ?", ['booking_services_unique_with_phong_id']);
        if (empty($idx2)) {
            Schema::table('booking_services', function (Blueprint $table) {
                $table->unique(['dat_phong_id', 'service_id', 'used_at', 'phong_id'], 'booking_services_unique_with_phong_id');
            });
        }

        // Recreate the foreign key if not present
        $fkNow = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'booking_services' AND COLUMN_NAME = 'dat_phong_id' AND REFERENCED_TABLE_NAME = 'dat_phong'");
        if (empty($fkNow)) {
            Schema::table('booking_services', function (Blueprint $table) {
                try {
                    $table->foreign('dat_phong_id')->references('id')->on('dat_phong')->onDelete('cascade');
                } catch (\Exception $e) {
                    // ignore if cannot create FK
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('booking_services')) {
            return;
        }

        // Safely drop the new unique index if it exists
        $idx = DB::select("SHOW INDEX FROM booking_services WHERE Key_name = ?", ['booking_services_unique_with_phong_id']);
        if (!empty($idx)) {
            Schema::table('booking_services', function (Blueprint $table) {
                try {
                    $table->dropUnique('booking_services_unique_with_phong_id');
                } catch (\Exception $e) {
                    // ignore
                }
            });
        }

        // Restore the old unique index if missing
        $idxOld = DB::select("SHOW INDEX FROM booking_services WHERE Key_name = ?", ['booking_services_unique_with_invoice_id']);
        if (empty($idxOld)) {
            Schema::table('booking_services', function (Blueprint $table) {
                $table->unique(['dat_phong_id', 'service_id', 'used_at', 'invoice_id'], 'booking_services_unique_with_invoice_id');
            });
        }

        // Ensure foreign key exists
        $fkNow = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'booking_services' AND COLUMN_NAME = 'dat_phong_id' AND REFERENCED_TABLE_NAME = 'dat_phong'");
        if (empty($fkNow)) {
            Schema::table('booking_services', function (Blueprint $table) {
                try {
                    $table->foreign('dat_phong_id')->references('id')->on('dat_phong')->onDelete('cascade');
                } catch (\Exception $e) {
                    // ignore
                }
            });
        }
    }
};
