<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Re-adds `phong_id` column to `dat_phong` table for legacy support.
     * This column supports the old single-room booking system alongside the new pivot table system.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('dat_phong', 'phong_id')) {
            Schema::table('dat_phong', function (Blueprint $table) {
                // Add phong_id after so_luong_da_dat (since loai_phong_id doesn't exist)
                $table->unsignedBigInteger('phong_id')->nullable()->after('so_luong_da_dat');
                $table->foreign('phong_id')->references('id')->on('phong')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     * Removes the `phong_id` column from `dat_phong` table.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('dat_phong', 'phong_id')) {
            Schema::table('dat_phong', function (Blueprint $table) {
                // Drop foreign key if exists
                try {
                    $table->dropForeign(['phong_id']);
                } catch (\Throwable $e) {
                    // ignore if no foreign key
                }
                $table->dropColumn('phong_id');
            });
        }
    }
};
