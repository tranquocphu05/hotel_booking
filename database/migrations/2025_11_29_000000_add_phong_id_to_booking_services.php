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
        Schema::table('booking_services', function (Blueprint $table) {
            // Add phong_id to link service to specific room
            // NULL = applies to all rooms of the booking
            if (!Schema::hasColumn('booking_services', 'phong_id')) {
                $table->unsignedBigInteger('phong_id')->nullable()->after('dat_phong_id');
                $table->foreign('phong_id')
                    ->references('id')
                    ->on('phong')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_services', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['phong_id']);
            $table->dropColumn('phong_id');
        });
    }
};
