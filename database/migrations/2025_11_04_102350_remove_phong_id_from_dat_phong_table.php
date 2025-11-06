<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Remove phong_id from dat_phong as we no longer track specific room assignments
     * Only track room type (loai_phong_id)
     */
    public function up(): void
    {
        Schema::table('dat_phong', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['phong_id']);
            // Then drop the column
            $table->dropColumn('phong_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_phong', function (Blueprint $table) {
            // Restore phong_id column (nullable for old bookings)
            $table->foreignId('phong_id')->nullable()->after('loai_phong_id')
                ->constrained('phong')->onDelete('set null');
        });
    }
};
