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
        // Add indexes for frequently queried columns to improve performance
        
        // Indexes for dat_phong table
        Schema::table('dat_phong', function (Blueprint $table) {
            if (!$this->hasIndex('dat_phong', 'idx_trang_thai')) {
                $table->index('trang_thai', 'idx_trang_thai');
            }
            if (!$this->hasIndex('dat_phong', 'idx_ngay_dat')) {
                $table->index('ngay_dat', 'idx_ngay_dat');
            }
            if (!$this->hasIndex('dat_phong', 'idx_nguoi_dung_id')) {
                $table->index('nguoi_dung_id', 'idx_nguoi_dung_id');
            }
        });

        // Indexes for hoa_don table
        Schema::table('hoa_don', function (Blueprint $table) {
            if (!$this->hasIndex('hoa_don', 'idx_trang_thai')) {
                $table->index('trang_thai', 'idx_trang_thai');
            }
            if (!$this->hasIndex('hoa_don', 'idx_dat_phong_id')) {
                $table->index('dat_phong_id', 'idx_dat_phong_id');
            }
            if (!$this->hasIndex('hoa_don', 'idx_ngay_tao')) {
                $table->index('ngay_tao', 'idx_ngay_tao');
            }
        });

        // Indexes for booking_services table
        Schema::table('booking_services', function (Blueprint $table) {
            if (!$this->hasIndex('booking_services', 'idx_dat_phong_id')) {
                $table->index('dat_phong_id', 'idx_dat_phong_id');
            }
            if (!$this->hasIndex('booking_services', 'idx_invoice_id')) {
                $table->index('invoice_id', 'idx_invoice_id');
            }
            if (!$this->hasIndex('booking_services', 'idx_service_id')) {
                $table->index('service_id', 'idx_service_id');
            }
        });

        // Indexes for thanh_toan table
        Schema::table('thanh_toan', function (Blueprint $table) {
            if (!$this->hasIndex('thanh_toan', 'idx_hoa_don_id')) {
                $table->index('hoa_don_id', 'idx_hoa_don_id');
            }
            if (!$this->hasIndex('thanh_toan', 'idx_trang_thai')) {
                $table->index('trang_thai', 'idx_trang_thai');
            }
            if (!$this->hasIndex('thanh_toan', 'idx_ngay_thanh_toan')) {
                $table->index('ngay_thanh_toan', 'idx_ngay_thanh_toan');
            }
        });

        // Indexes for loai_phong table
        Schema::table('loai_phong', function (Blueprint $table) {
            if (!$this->hasIndex('loai_phong', 'idx_trang_thai')) {
                $table->index('trang_thai', 'idx_trang_thai');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_phong', function (Blueprint $table) {
            $table->dropIndex('idx_trang_thai');
            $table->dropIndex('idx_ngay_dat');
            $table->dropIndex('idx_nguoi_dung_id');
        });

        Schema::table('hoa_don', function (Blueprint $table) {
            $table->dropIndex('idx_trang_thai');
            $table->dropIndex('idx_dat_phong_id');
            $table->dropIndex('idx_ngay_tao');
        });

        Schema::table('booking_services', function (Blueprint $table) {
            $table->dropIndex('idx_dat_phong_id');
            $table->dropIndex('idx_invoice_id');
            $table->dropIndex('idx_service_id');
        });

        Schema::table('thanh_toan', function (Blueprint $table) {
            $table->dropIndex('idx_hoa_don_id');
            $table->dropIndex('idx_trang_thai');
            $table->dropIndex('idx_ngay_thanh_toan');
        });

        Schema::table('loai_phong', function (Blueprint $table) {
            $table->dropIndex('idx_trang_thai');
        });
    }

    /**
     * Check if index exists
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();
        
        $result = $connection->select(
            "SELECT COUNT(*) as count 
             FROM information_schema.statistics 
             WHERE table_schema = ? 
             AND table_name = ? 
             AND index_name = ?",
            [$databaseName, $table, $indexName]
        );
        
        return $result[0]->count > 0;
    }
};


