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
        Schema::table('phong', function (Blueprint $table) {
            $table->decimal('gia_goc', 15, 2)->nullable()->after('gia')->comment('Giá gốc của phòng');
            $table->decimal('gia_khuyen_mai', 15, 2)->nullable()->after('gia_goc')->comment('Giá khuyến mãi của phòng');
            $table->boolean('co_khuyen_mai')->default(false)->after('gia_khuyen_mai')->comment('Có khuyến mãi hay không');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phong', function (Blueprint $table) {
            $table->dropColumn(['gia_goc', 'gia_khuyen_mai', 'co_khuyen_mai']);
        });
    }
};
