<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('hoa_don', 'ghi_chu')) {
            Schema::table('hoa_don', function (Blueprint $table) {
                $table->text('ghi_chu')->nullable()->after('trang_thai');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hoa_don', 'ghi_chu')) {
            Schema::table('hoa_don', function (Blueprint $table) {
                $table->dropColumn('ghi_chu');
            });
        }
    }
};
