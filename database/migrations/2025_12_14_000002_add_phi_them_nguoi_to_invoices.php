<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('hoa_don') && !Schema::hasColumn('hoa_don', 'phi_them_nguoi')) {
            Schema::table('hoa_don', function (Blueprint $table) {
                $table->decimal('phi_them_nguoi', 10, 2)->default(0)->after('phi_phat_sinh')->nullable()->comment('Tổng phí thêm người (trên hóa đơn)');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('hoa_don') && Schema::hasColumn('hoa_don', 'phi_them_nguoi')) {
            Schema::table('hoa_don', function (Blueprint $table) {
                $table->dropColumn('phi_them_nguoi');
            });
        }
    }
};
