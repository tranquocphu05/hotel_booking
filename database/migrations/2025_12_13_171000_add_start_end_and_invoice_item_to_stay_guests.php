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
        Schema::table('stay_guests', function (Blueprint $table) {
            if (!Schema::hasColumn('stay_guests', 'start_date')) {
                $table->date('start_date')->nullable()->after('ngay_them');
            }
            if (!Schema::hasColumn('stay_guests', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
            if (!Schema::hasColumn('stay_guests', 'invoice_item_id')) {
                $table->unsignedBigInteger('invoice_item_id')->nullable()->after('end_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stay_guests', function (Blueprint $table) {
            if (Schema::hasColumn('stay_guests', 'invoice_item_id')) {
                $table->dropColumn('invoice_item_id');
            }
            if (Schema::hasColumn('stay_guests', 'end_date')) {
                $table->dropColumn('end_date');
            }
            if (Schema::hasColumn('stay_guests', 'start_date')) {
                $table->dropColumn('start_date');
            }
        });
    }
};
