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
        Schema::table('booking_rooms', function (Blueprint $table) {
            $table->decimal('phu_phi', 15, 2)->default(0)->after('phong_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_rooms', function (Blueprint $table) {
            $table->dropColumn('phu_phi');
        });
    }
};
