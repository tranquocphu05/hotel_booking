<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Normalize guest count and surcharge columns to prevent negative values
     * and ensure so_nguoi >= so_tre_em + so_em_be
     *
     * @return void
     */
    public function up()
    {
        // Clamp per-booking numeric columns to >= 0
        $sets = [];
        if (Schema::hasColumn('dat_phong', 'so_tre_em')) $sets[] = 'so_tre_em = GREATEST(so_tre_em, 0)';
        if (Schema::hasColumn('dat_phong', 'so_em_be')) $sets[] = 'so_em_be = GREATEST(so_em_be, 0)';
        if (Schema::hasColumn('dat_phong', 'so_nguoi')) $sets[] = 'so_nguoi = GREATEST(so_nguoi, 0)';
        if (!empty($sets)) {
            DB::statement('UPDATE dat_phong SET ' . implode(', ', $sets));
        }

        // Ensure so_nguoi is at least children+infants when columns exist
        if (Schema::hasColumn('dat_phong', 'so_nguoi') && Schema::hasColumn('dat_phong', 'so_tre_em') && Schema::hasColumn('dat_phong', 'so_em_be')) {
            DB::statement('UPDATE dat_phong SET so_nguoi = GREATEST(so_nguoi, COALESCE(so_tre_em,0) + COALESCE(so_em_be,0))');
        }

        // Clamp booking surcharge / money columns
        $moneySets = [];
        if (Schema::hasColumn('dat_phong', 'phu_phi_tre_em')) $moneySets[] = 'phu_phi_tre_em = GREATEST(phu_phi_tre_em, 0)';
        if (Schema::hasColumn('dat_phong', 'phu_phi_em_be')) $moneySets[] = 'phu_phi_em_be = GREATEST(phu_phi_em_be, 0)';
        if (Schema::hasColumn('dat_phong', 'phi_them_nguoi')) $moneySets[] = 'phi_them_nguoi = GREATEST(phi_them_nguoi, 0)';
        if (Schema::hasColumn('dat_phong', 'phi_phat_sinh')) $moneySets[] = 'phi_phat_sinh = GREATEST(phi_phat_sinh, 0)';
        if (Schema::hasColumn('dat_phong', 'tong_tien')) $moneySets[] = 'tong_tien = GREATEST(tong_tien, 0)';
        if (!empty($moneySets)) {
            DB::statement('UPDATE dat_phong SET ' . implode(', ', $moneySets));
        }

        // Clamp invoice columns for affected bookings
        $invoiceSets = [];
        if (Schema::hasColumn('hoa_don', 'phu_phi_tre_em')) $invoiceSets[] = 'phu_phi_tre_em = GREATEST(phu_phi_tre_em, 0)';
        if (Schema::hasColumn('hoa_don', 'phu_phi_em_be')) $invoiceSets[] = 'phu_phi_em_be = GREATEST(phu_phi_em_be, 0)';
        if (Schema::hasColumn('hoa_don', 'phi_them_nguoi')) $invoiceSets[] = 'phi_them_nguoi = GREATEST(phi_them_nguoi, 0)';
        if (Schema::hasColumn('hoa_don', 'phi_phat_sinh')) $invoiceSets[] = 'phi_phat_sinh = GREATEST(phi_phat_sinh, 0)';
        if (Schema::hasColumn('hoa_don', 'tong_tien')) $invoiceSets[] = 'tong_tien = GREATEST(tong_tien, 0)';
        if (!empty($invoiceSets)) {
            DB::statement('UPDATE hoa_don SET ' . implode(', ', $invoiceSets));
        }
    }

    /**
     * Reverse the migrations.
     * This migration is idempotent and safe; we don't revert data normalization.
     * @return void
     */
    public function down()
    {
        // no-op
    }
};
