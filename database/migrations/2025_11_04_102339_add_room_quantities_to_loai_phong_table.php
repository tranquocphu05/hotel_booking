<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add room quantity tracking to loai_phong table
     * - so_luong_phong: Total number of rooms for this room type
     * - so_luong_trong: Number of available rooms (decreases when booked)
     */
    public function up(): void
    {
        Schema::table('loai_phong', function (Blueprint $table) {
            $table->integer('so_luong_phong')->default(0)->after('gia_co_ban')
                ->comment('Total number of rooms for this type');
            $table->integer('so_luong_trong')->default(0)->after('so_luong_phong')
                ->comment('Number of available/unbooked rooms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loai_phong', function (Blueprint $table) {
            $table->dropColumn(['so_luong_phong', 'so_luong_trong']);
        });
    }
};
