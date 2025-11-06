<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add quantity field to dat_phong table
     * - so_luong_da_dat: Number of rooms booked in this booking
     */
    public function up(): void
    {
        Schema::table('dat_phong', function (Blueprint $table) {
            $table->integer('so_luong_da_dat')->default(1)->after('loai_phong_id')
                ->comment('Number of rooms booked in this booking');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_phong', function (Blueprint $table) {
            $table->dropColumn('so_luong_da_dat');
        });
    }
};

