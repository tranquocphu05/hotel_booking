<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dat_phong', function (Blueprint $table) {
            $table->dropColumn(['tien_phong', 'tong_tien_dich_vu']);
        });
    }

    public function down(): void
    {
        Schema::table('dat_phong', function (Blueprint $table) {
            $table->decimal('tien_phong', 15, 2)->default(0)->after('tong_tien');
            $table->decimal('tong_tien_dich_vu', 15, 2)->default(0)->after('tien_phong');
        });
    }
};
