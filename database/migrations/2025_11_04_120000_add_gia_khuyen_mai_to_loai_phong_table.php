<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add promotional price field to loai_phong table
     */
    public function up(): void
    {
        Schema::table('loai_phong', function (Blueprint $table) {
            $table->decimal('gia_khuyen_mai', 15, 2)->nullable()->after('gia_co_ban')
                ->comment('Promotional price (if null, use gia_co_ban)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loai_phong', function (Blueprint $table) {
            $table->dropColumn('gia_khuyen_mai');
        });
    }
};

