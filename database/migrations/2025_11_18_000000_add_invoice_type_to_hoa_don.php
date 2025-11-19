<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hoa_don', function (Blueprint $table) {
            // Use a varchar for flexibility; default to 'STANDARD'
            $table->string('invoice_type', 20)->default('STANDARD')->after('trang_thai');
        });

        // Backfill basic values: if phuong_thuc is not null and trang_thai == da_thanh_toan => PREPAID
        \Illuminate\Support\Facades\DB::table('hoa_don')
            ->whereNotNull('phuong_thuc')
            ->where('trang_thai', 'da_thanh_toan')
            ->update(['invoice_type' => 'PREPAID']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hoa_don', function (Blueprint $table) {
            $table->dropColumn('invoice_type');
        });
    }
};
