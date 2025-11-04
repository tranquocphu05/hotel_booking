<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Restructure dat_phong table to book by room type instead of specific room
     */
    public function up(): void
    {
        // Disable foreign key checks to avoid constraint errors
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Drop old dat_phong table (old bookings not needed)
        Schema::dropIfExists('dat_phong');

        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Recreate with new structure
        Schema::create('dat_phong', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')->nullable()->constrained('nguoi_dung')->onDelete('cascade');

            // PRIMARY: Book by room type
            $table->foreignId('loai_phong_id')->constrained('loai_phong')->onDelete('cascade');

            // SECONDARY: Auto-assigned specific room (nullable until assigned)
            $table->foreignId('phong_id')->nullable()->constrained('phong')->onDelete('set null');

            $table->dateTime('ngay_dat')->useCurrent();

            // Booking contact information
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('sdt')->nullable();
            $table->string('cccd')->nullable();

            $table->date('ngay_nhan');
            $table->date('ngay_tra');
            $table->integer('so_nguoi')->nullable();
            $table->decimal('tong_tien', 15, 2)->nullable();
            $table->unsignedBigInteger('voucher_id')->nullable();
            $table->enum('trang_thai', ['cho_xac_nhan', 'da_xac_nhan', 'da_huy', 'da_tra', 'tu_choi', 'thanh_toan_that_bai'])->default('cho_xac_nhan');
            $table->text('ly_do_huy')->nullable();
            $table->dateTime('ngay_huy')->nullable();

            $table->foreign('voucher_id')->references('id')->on('voucher')->nullOnDelete();

            // Index for performance
            $table->index(['loai_phong_id', 'ngay_nhan', 'ngay_tra']);
            $table->index('trang_thai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::dropIfExists('dat_phong');

        // Restore old structure
        Schema::create('dat_phong', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dung')->onDelete('cascade');
            $table->foreignId('phong_id')->constrained('phong')->onDelete('cascade');
            $table->dateTime('ngay_dat')->useCurrent();

            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('sdt')->nullable();
            $table->string('cccd')->nullable();

            $table->date('ngay_nhan');
            $table->date('ngay_tra');
            $table->integer('so_nguoi')->nullable();
            $table->decimal('tong_tien', 15, 2)->nullable();
            $table->unsignedBigInteger('voucher_id')->nullable();
            $table->enum('trang_thai', ['cho_xac_nhan','da_xac_nhan','da_huy','da_tra'])->default('cho_xac_nhan');

            $table->foreign('voucher_id')->references('id')->on('voucher')->nullOnDelete();
        });

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
