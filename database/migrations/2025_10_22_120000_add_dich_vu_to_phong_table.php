<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('phong') && !Schema::hasColumn('phong', 'dich_vu')) {
            Schema::table('phong', function (Blueprint $table) {
                $table->string('dich_vu', 255)->nullable()->after('img');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('phong') && Schema::hasColumn('phong', 'dich_vu')) {
            Schema::table('phong', function (Blueprint $table) {
                $table->dropColumn('dich_vu');
            });
        }
    }
};


