<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stay_guests')) {
            return;
        }

        if (!Schema::hasColumn('stay_guests', 'phong_id')) {
            Schema::table('stay_guests', function (Blueprint $table) {
                $table->unsignedBigInteger('phong_id')->nullable()->after('dat_phong_id');
                $table->foreign('phong_id')->references('id')->on('phong')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('stay_guests')) return;

        if (Schema::hasColumn('stay_guests', 'phong_id')) {
            // Disable foreign key checks to safely drop column
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            Schema::table('stay_guests', function (Blueprint $table) {
                $table->dropColumn('phong_id');
            });
            
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
};
