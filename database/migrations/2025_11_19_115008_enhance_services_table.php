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
        Schema::table('services', function (Blueprint $table) {
            $table->enum('loai', ['an_uong', 'giat_ui', 'spa', 'van_chuyen', 'khac'])
                ->default('khac')
                ->after('unit');
            $table->string('anh')->nullable()->after('loai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['loai', 'anh']);
        });
    }
};
