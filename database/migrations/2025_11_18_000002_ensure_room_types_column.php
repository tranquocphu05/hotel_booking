<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add room_types JSON column if it does not exist
        if (!Schema::hasColumn('dat_phong', 'room_types')) {
            Schema::table('dat_phong', function (Blueprint $table) {
                $table->json('room_types')->nullable()->after('loai_phong_id')->comment('Danh sách các loại phòng trong booking (JSON array)');
            });

            // If there's existing legacy data to backfill, we could add logic here.
            // For safety we keep it simple: leave null and let application write proper data.
            Log::info('Added missing column dat_phong.room_types via migration 2025_11_18_000002');
        } else {
            Log::info('Column dat_phong.room_types already exists; skipping migration 2025_11_18_000002');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('dat_phong', 'room_types')) {
            Schema::table('dat_phong', function (Blueprint $table) {
                $table->dropColumn('room_types');
            });
        }
    }
};
