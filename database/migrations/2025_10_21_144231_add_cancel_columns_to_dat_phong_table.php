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
        Schema::table('dat_phong', function (Blueprint $table) {
            $table->text('ly_do_huy')->nullable()->after('trang_thai');
            $table->timestamp('ngay_huy')->nullable()->after('ly_do_huy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_phong', function (Blueprint $table) {
            $table->dropColumn(['ly_do_huy', 'ngay_huy']);
        });
    }
};
