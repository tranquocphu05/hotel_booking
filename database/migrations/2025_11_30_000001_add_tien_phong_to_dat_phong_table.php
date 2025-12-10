<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dat_phong', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_phong', 'tien_phong')) {
                $table->decimal('tien_phong', 15, 0)->default(0)->comment('Tổng tiền phòng');
            }
            if (!Schema::hasColumn('dat_phong', 'tien_dich_vu')) {
                $table->decimal('tien_dich_vu', 15, 0)->default(0)->comment('Tổng tiền dịch vụ');
            }
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('dat_phong', 'tien_dich_vu')) {
            Schema::table('dat_phong', function (Blueprint $table) {
                $table->dropColumn('tien_dich_vu');
            });
        }
        if (Schema::hasColumn('dat_phong', 'tien_phong')) {
            Schema::table('dat_phong', function (Blueprint $table) {
                $table->dropColumn('tien_phong');
            });
        }
    }
};
