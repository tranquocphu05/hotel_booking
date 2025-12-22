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
        Schema::table('booking_room_types', function (Blueprint $table) {
            // Thêm các cột số khách nếu chưa tồn tại
            if (!Schema::hasColumn('booking_room_types', 'so_nguoi')) {
                $table->integer('so_nguoi')->default(1)->after('gia_rieng');
            }
            if (!Schema::hasColumn('booking_room_types', 'so_tre_em')) {
                $table->integer('so_tre_em')->default(0)->after('so_nguoi');
            }
            if (!Schema::hasColumn('booking_room_types', 'so_em_be')) {
                $table->integer('so_em_be')->default(0)->after('so_tre_em');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_room_types', function (Blueprint $table) {
            // Xóa các cột nếu cần rollback
            if (Schema::hasColumn('booking_room_types', 'so_em_be')) {
                $table->dropColumn('so_em_be');
            }
            if (Schema::hasColumn('booking_room_types', 'so_tre_em')) {
                $table->dropColumn('so_tre_em');
            }
            if (Schema::hasColumn('booking_room_types', 'so_nguoi')) {
                $table->dropColumn('so_nguoi');
            }
        });
    }
};
