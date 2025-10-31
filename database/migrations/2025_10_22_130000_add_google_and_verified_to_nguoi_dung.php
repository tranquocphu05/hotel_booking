<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('nguoi_dung')) {
            Schema::table('nguoi_dung', function (Blueprint $table) {
                if (!Schema::hasColumn('nguoi_dung', 'google_id')) {
                    $table->string('google_id', 191)->nullable()->after('email');
                }
                if (!Schema::hasColumn('nguoi_dung', 'email_verified_at')) {
                    $table->timestamp('email_verified_at')->nullable()->after('google_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('nguoi_dung')) {
            Schema::table('nguoi_dung', function (Blueprint $table) {
                if (Schema::hasColumn('nguoi_dung', 'email_verified_at')) {
                    $table->dropColumn('email_verified_at');
                }
                if (Schema::hasColumn('nguoi_dung', 'google_id')) {
                    $table->dropColumn('google_id');
                }
            });
        }
    }
};










