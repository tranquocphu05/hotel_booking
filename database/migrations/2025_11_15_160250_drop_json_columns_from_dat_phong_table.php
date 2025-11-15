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
        Schema::table('dat_phong', function (Blueprint $table) {
            // Drop JSON columns - now using pivot tables
            $table->dropColumn(['room_types', 'phong_ids']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_phong', function (Blueprint $table) {
            // Restore JSON columns if needed to rollback
            $table->longText('room_types')->nullable()->after('loai_phong_id');
            $table->longText('phong_ids')->nullable()->after('room_types');
        });
    }
};
