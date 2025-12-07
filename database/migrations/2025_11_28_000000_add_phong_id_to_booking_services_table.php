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
        if (!Schema::hasTable('booking_services')) {
            return;
        }

        Schema::table('booking_services', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_services', 'phong_id')) {
                // Add as nullable unsigned big integer to match common id type
                $table->unsignedBigInteger('phong_id')->nullable()->after('dat_phong_id');
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
        if (!Schema::hasTable('booking_services')) {
            return;
        }

        Schema::table('booking_services', function (Blueprint $table) {
            if (Schema::hasColumn('booking_services', 'phong_id')) {
                $table->dropColumn('phong_id');
            }
        });
    }
};
