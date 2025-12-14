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
        Schema::table('yeu_cau_doi_phong', function (Blueprint $table) {
            $table->integer('so_nguoi_ban_dau')->nullable()->after('so_em_be_moi');
            $table->integer('so_tre_em_ban_dau')->nullable()->default(0)->after('so_nguoi_ban_dau');
            $table->integer('so_em_be_ban_dau')->nullable()->default(0)->after('so_tre_em_ban_dau');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('yeu_cau_doi_phong', function (Blueprint $table) {
            $table->dropColumn(['so_nguoi_ban_dau', 'so_tre_em_ban_dau', 'so_em_be_ban_dau']);
        });
    }
};
