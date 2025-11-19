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
        Schema::table('hoa_don', function (Blueprint $table) {
            $table->decimal('phi_phat_sinh', 10, 2)->default(0)->after('tien_dich_vu');
            $table->decimal('da_thanh_toan', 15, 2)->default(0)->after('tong_tien');
            $table->decimal('con_lai', 15, 2)->default(0)->after('da_thanh_toan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hoa_don', function (Blueprint $table) {
            $table->dropColumn(['phi_phat_sinh', 'da_thanh_toan', 'con_lai']);
        });
    }
};
