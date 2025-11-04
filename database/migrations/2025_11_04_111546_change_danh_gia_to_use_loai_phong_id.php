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
     * Change danh_gia (Comment) table to use loai_phong_id instead of phong_id
     */
    public function up(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::table('danh_gia', function (Blueprint $table) {
            // Drop old foreign key and column
            $table->dropForeign(['phong_id']);
            $table->dropColumn('phong_id');

            // Add new loai_phong_id column
            $table->foreignId('loai_phong_id')->after('nguoi_dung_id')
                ->constrained('loai_phong')->onDelete('cascade');
        });

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::table('danh_gia', function (Blueprint $table) {
            $table->dropForeign(['loai_phong_id']);
            $table->dropColumn('loai_phong_id');

            // Restore phong_id
            $table->foreignId('phong_id')->after('nguoi_dung_id')
                ->constrained('phong')->onDelete('cascade');
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
