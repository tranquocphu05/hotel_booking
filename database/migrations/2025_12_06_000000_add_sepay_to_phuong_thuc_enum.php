<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add 'sepay' to phuong_thuc ENUM column in hoa_don table
     */
    public function up(): void
    {
        // MySQL: Modify ENUM to include 'sepay'
        DB::statement("ALTER TABLE hoa_don MODIFY COLUMN phuong_thuc ENUM('tien_mat', 'chuyen_khoan', 'momo', 'vnpay', 'sepay') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First update any 'sepay' values to 'chuyen_khoan' before removing the option
        DB::table('hoa_don')
            ->where('phuong_thuc', 'sepay')
            ->update(['phuong_thuc' => 'chuyen_khoan']);
            
        // Revert ENUM to original values
        DB::statement("ALTER TABLE hoa_don MODIFY COLUMN phuong_thuc ENUM('tien_mat', 'chuyen_khoan', 'momo', 'vnpay') NULL");
    }
};
