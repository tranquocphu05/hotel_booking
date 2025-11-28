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
        Schema::table('danh_gia', function (Blueprint $table) {
            $table->text('reply')->nullable()->after('trang_thai');
            $table->timestamp('reply_at')->nullable()->after('reply');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('danh_gia', function (Blueprint $table) {
            $table->dropColumn(['reply', 'reply_at']);
        });
    }
};
