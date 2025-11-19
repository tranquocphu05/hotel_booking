<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hoa_don', function (Blueprint $table) {
            $table->decimal('tien_phong', 15, 2)->default(0)->after('tong_tien')->comment('Room total before discount');
            $table->decimal('tien_dich_vu', 15, 2)->default(0)->after('tien_phong')->comment('Service total');
            $table->decimal('giam_gia', 15, 2)->default(0)->after('tien_dich_vu')->comment('Discount amount from voucher');
        });
    }

    public function down(): void
    {
        Schema::table('hoa_don', function (Blueprint $table) {
            $table->dropColumn(['tien_phong', 'tien_dich_vu', 'giam_gia']);
        });
    }
};
