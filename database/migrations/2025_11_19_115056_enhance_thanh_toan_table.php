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
        Schema::table('thanh_toan', function (Blueprint $table) {
            $table->enum('loai', ['dat_coc', 'tien_phong', 'dich_vu', 'phi_phat_sinh', 'hoan_tien'])
                ->default('tien_phong')
                ->after('hoa_don_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('thanh_toan', function (Blueprint $table) {
            $table->dropColumn('loai');
        });
    }
};
