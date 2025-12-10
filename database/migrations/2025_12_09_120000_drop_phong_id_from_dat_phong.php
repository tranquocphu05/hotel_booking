<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Drops legacy `phong_id` column from `dat_phong` if present.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('dat_phong', 'phong_id')) {
            Schema::table('dat_phong', function (Blueprint $table) {
                // drop foreign key if exists (best-effort)
                try {
                    $table->dropForeign(['phong_id']);
                } catch (\Throwable $e) {
                    // ignore if no foreign key
                }
                $table->dropColumn('phong_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     * Re-adds `phong_id` as nullable unsignedBigInteger for rollback.
     * @return void
     */
    public function down()
    {
        if (!Schema::hasColumn('dat_phong', 'phong_id')) {
            Schema::table('dat_phong', function (Blueprint $table) {
                $table->unsignedBigInteger('phong_id')->nullable()->after('loai_phong_id');
                // you may want to recreate foreign key manually if needed
            });
        }
    }
};
