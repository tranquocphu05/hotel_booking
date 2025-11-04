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
     * Fix danh_gia table - ensure loai_phong_id exists
     */
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Check if loai_phong_id column exists
        $columns = DB::select("SHOW COLUMNS FROM danh_gia LIKE 'loai_phong_id'");

        if (empty($columns)) {
            // Check if phong_id still exists
            $phongColumns = DB::select("SHOW COLUMNS FROM danh_gia LIKE 'phong_id'");

            if (!empty($phongColumns)) {
                // Drop phong_id if exists
                try {
                    DB::statement('ALTER TABLE danh_gia DROP FOREIGN KEY danh_gia_phong_id_foreign');
                } catch (\Exception $e) {
                    // Foreign key might not exist or have different name
                }
                Schema::table('danh_gia', function (Blueprint $table) {
                    $table->dropColumn('phong_id');
                });
            }

            // Add loai_phong_id
            Schema::table('danh_gia', function (Blueprint $table) {
                $table->foreignId('loai_phong_id')->after('nguoi_dung_id')
                    ->constrained('loai_phong')->onDelete('cascade');
            });
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not needed for rollback
    }
};
