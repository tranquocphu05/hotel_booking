<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('yeu_cau_doi_phong', function (Blueprint $table) {
            $table->decimal('phi_doi_phong', 15, 2)->default(0)->after('ly_do');
            $table->integer('so_nguoi_moi')->nullable()->after('phi_doi_phong');
            $table->integer('so_tre_em_moi')->nullable()->default(0)->after('so_nguoi_moi');
            $table->integer('so_em_be_moi')->nullable()->default(0)->after('so_tre_em_moi');
        });
    }

    public function down(): void
    {
        Schema::table('yeu_cau_doi_phong', function (Blueprint $table) {
            $table->dropColumn(['phi_doi_phong', 'so_nguoi_moi', 'so_tre_em_moi', 'so_em_be_moi']);
        });
    }
};

