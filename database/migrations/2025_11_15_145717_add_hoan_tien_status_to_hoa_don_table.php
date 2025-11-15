<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'hoan_tien' to trang_thai enum
        DB::statement("ALTER TABLE hoa_don MODIFY COLUMN trang_thai ENUM('cho_thanh_toan', 'da_thanh_toan', 'hoan_tien') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'hoan_tien' from enum
        DB::statement("ALTER TABLE hoa_don MODIFY COLUMN trang_thai ENUM('cho_thanh_toan', 'da_thanh_toan') NOT NULL");
    }
};
