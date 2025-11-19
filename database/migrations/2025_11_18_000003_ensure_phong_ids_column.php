<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('dat_phong', 'phong_ids')) {
            Schema::table('dat_phong', function (Blueprint $table) {
                $table->json('phong_ids')->nullable()->after('room_types')->comment('Danh sách phòng được gán cho booking (JSON array)');
            });
            Log::info('Added missing column dat_phong.phong_ids via migration 2025_11_18_000003');
        } else {
            Log::info('Column dat_phong.phong_ids already exists; skipping migration 2025_11_18_000003');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('dat_phong', 'phong_ids')) {
            Schema::table('dat_phong', function (Blueprint $table) {
                $table->dropColumn('phong_ids');
            });
        }
    }
};
