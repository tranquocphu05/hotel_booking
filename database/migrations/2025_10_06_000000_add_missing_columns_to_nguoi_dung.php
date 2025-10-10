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
        $table = 'nguoi_dung';

        if (! Schema::hasTable($table)) {
            // If the table doesn't exist at all, create minimal structure
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->timestamps();
            });
        }

        // add columns only if they are missing
        if (! Schema::hasColumn($table, 'username')) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('username', 100)->unique()->nullable()->after('id');
            });
        }

        if (! Schema::hasColumn($table, 'password')) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('password', 255)->nullable()->after('username');
            });
        }

        if (! Schema::hasColumn($table, 'email')) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('email', 100)->unique()->nullable()->after('password');
            });
        }

        if (! Schema::hasColumn($table, 'ho_ten')) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('ho_ten', 100)->nullable()->after('email');
            });
        }

        if (! Schema::hasColumn($table, 'sdt')) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('sdt', 20)->nullable()->after('ho_ten');
            });
        }

        if (! Schema::hasColumn($table, 'dia_chi')) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('dia_chi', 255)->nullable()->after('sdt');
            });
        }

        if (! Schema::hasColumn($table, 'cccd')) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('cccd', 20)->nullable()->after('dia_chi');
            });
        }

        if (! Schema::hasColumn($table, 'img')) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('img', 255)->nullable()->after('cccd');
            });
        }

        if (! Schema::hasColumn($table, 'vai_tro')) {
            Schema::table($table, function (Blueprint $t) {
                $t->enum('vai_tro', ['admin', 'nhan_vien', 'khach_hang'])->default('khach_hang')->after('img');
            });
        }

        if (! Schema::hasColumn($table, 'trang_thai')) {
            Schema::table($table, function (Blueprint $t) {
                $t->enum('trang_thai', ['hoat_dong', 'khoa'])->default('hoat_dong')->after('vai_tro');
            });
        }

        if (! Schema::hasColumn($table, 'ngay_tao')) {
            Schema::table($table, function (Blueprint $t) {
                $t->timestamp('ngay_tao')->nullable()->useCurrent()->after('trang_thai');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $table = 'nguoi_dung';

        if (! Schema::hasTable($table)) {
            return;
        }

        // drop columns if they exist (non-destructive only for columns added above)
        Schema::table($table, function (Blueprint $t) use ($table) {
            if (Schema::hasColumn($table, 'ngay_tao')) {
                $t->dropColumn('ngay_tao');
            }
            if (Schema::hasColumn($table, 'trang_thai')) {
                $t->dropColumn('trang_thai');
            }
            if (Schema::hasColumn($table, 'vai_tro')) {
                $t->dropColumn('vai_tro');
            }
            if (Schema::hasColumn($table, 'img')) {
                $t->dropColumn('img');
            }
            if (Schema::hasColumn($table, 'cccd')) {
                $t->dropColumn('cccd');
            }
            if (Schema::hasColumn($table, 'dia_chi')) {
                $t->dropColumn('dia_chi');
            }
            if (Schema::hasColumn($table, 'sdt')) {
                $t->dropColumn('sdt');
            }
            if (Schema::hasColumn($table, 'ho_ten')) {
                $t->dropColumn('ho_ten');
            }
            if (Schema::hasColumn($table, 'email')) {
                $t->dropColumn('email');
            }
            if (Schema::hasColumn($table, 'password')) {
                $t->dropColumn('password');
            }
            if (Schema::hasColumn($table, 'username')) {
                $t->dropColumn('username');
            }
        });
    }
};
