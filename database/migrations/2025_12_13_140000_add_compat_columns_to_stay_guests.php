<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stay_guests')) return;

        Schema::table('stay_guests', function (Blueprint $table) {
            if (!Schema::hasColumn('stay_guests', 'full_name')) {
                $table->string('full_name')->nullable()->after('phong_id');
            }
            if (!Schema::hasColumn('stay_guests', 'dob')) {
                $table->date('dob')->nullable()->after('full_name');
            }
            if (!Schema::hasColumn('stay_guests', 'age')) {
                $table->integer('age')->nullable()->after('dob');
            }
            if (!Schema::hasColumn('stay_guests', 'extra_fee')) {
                $table->decimal('extra_fee', 15, 2)->default(0)->after('age');
            }
            if (!Schema::hasColumn('stay_guests', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('extra_fee');
            }
        });

        // Copy data from legacy columns if present
        if (Schema::hasColumn('stay_guests', 'ten_khach')) {
            DB::table('stay_guests')->whereNotNull('ten_khach')->update([
                'full_name' => DB::raw('ten_khach')
            ]);
        }
        if (Schema::hasColumn('stay_guests', 'phu_phi_them')) {
            DB::table('stay_guests')->whereNotNull('phu_phi_them')->update([
                'extra_fee' => DB::raw('phu_phi_them')
            ]);
        }
        if (Schema::hasColumn('stay_guests', 'nguoi_them')) {
            DB::table('stay_guests')->whereNotNull('nguoi_them')->update([
                'created_by' => DB::raw('nguoi_them')
            ]);
        }

    }

    public function down(): void
    {
        if (!Schema::hasTable('stay_guests')) return;

        Schema::table('stay_guests', function (Blueprint $table) {
            if (Schema::hasColumn('stay_guests', 'full_name')) {
                $table->dropColumn('full_name');
            }
            if (Schema::hasColumn('stay_guests', 'dob')) {
                $table->dropColumn('dob');
            }
            if (Schema::hasColumn('stay_guests', 'age')) {
                $table->dropColumn('age');
            }
            if (Schema::hasColumn('stay_guests', 'extra_fee')) {
                $table->dropColumn('extra_fee');
            }
            if (Schema::hasColumn('stay_guests', 'created_by')) {
                $table->dropColumn('created_by');
            }
        });
    }
};
