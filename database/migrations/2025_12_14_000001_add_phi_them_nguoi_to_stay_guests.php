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
        if (Schema::hasTable('stay_guests') && !Schema::hasColumn('stay_guests', 'phi_them_nguoi')) {
            Schema::table('stay_guests', function (Blueprint $table) {
                // If legacy column 'phu_phi_them' exists, place after it; otherwise simply add column
                if (Schema::hasColumn('stay_guests', 'phu_phi_them')) {
                    $table->decimal('phi_them_nguoi', 10, 2)->default(0)->after('phu_phi_them')->comment('Phí thêm người (per-guest)')->nullable();
                } else {
                    $table->decimal('phi_them_nguoi', 10, 2)->default(0)->comment('Phí thêm người (per-guest)')->nullable();
                }
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
        if (Schema::hasTable('stay_guests') && Schema::hasColumn('stay_guests', 'phi_them_nguoi')) {
            Schema::table('stay_guests', function (Blueprint $table) {
                $table->dropColumn('phi_them_nguoi');
            });
        }
    }
};
