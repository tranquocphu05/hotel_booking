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
     * Tạo lại bảng phong để quản lý phòng cụ thể theo số
     * Hỗ trợ: gán phòng tự động, quản lý trạng thái, giá riêng theo phòng
     */
    public function up(): void
    {
        // Kiểm tra xem bảng đã tồn tại chưa (có thể đã được tạo bởi migration trước)
        if (Schema::hasTable('phong')) {
            // Bảng đã tồn tại, chỉ cần thêm các cột còn thiếu
            Schema::table('phong', function (Blueprint $table) {
                // Thêm các cột mới nếu chưa có
                if (!Schema::hasColumn('phong', 'gia_rieng')) {
                    $table->decimal('gia_rieng', 15, 2)->nullable()->after('gia_bo_sung')
                        ->comment('Giá riêng của phòng (nếu khác với loại phòng)');
                }
                
                // Cập nhật enum trang_thai nếu cần (thêm dang_thue, dang_don)
                // Note: MySQL không hỗ trợ modify enum trực tiếp, cần ALTER TABLE riêng
            });
            
            // Cập nhật enum trang_thai nếu cần
            DB::statement("ALTER TABLE phong MODIFY COLUMN trang_thai ENUM('trong', 'dang_thue', 'dang_don', 'bao_tri') DEFAULT 'trong'");
            
            return;
        }
        
        Schema::create('phong', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loai_phong_id')->constrained('loai_phong')->onDelete('cascade');
            
            // Số phòng (unique) - VD: "101", "201A", "Suite-301"
            $table->string('so_phong', 20)->unique()->comment('Số phòng cụ thể');
            
            // Tên phòng (optional) - VD: "Phòng Honeymoon", "Phòng View Biển"
            $table->string('ten_phong', 255)->nullable()->comment('Tên phòng (nếu có)');
            
            // Thông tin phòng
            $table->integer('tang')->nullable()->comment('Tầng');
            $table->enum('huong_cua_so', ['bien', 'nui', 'thanh_pho', 'san_vuon'])->nullable()->comment('Hướng cửa sổ');
            $table->boolean('co_ban_cong')->default(false)->comment('Có ban công');
            $table->boolean('co_view_dep')->default(false)->comment('Có view đẹp');
            
            // Trạng thái phòng
            $table->enum('trang_thai', [
                'trong',           // Trống, sẵn sàng
                'dang_thue',       // Đang được thuê
                'dang_don',        // Đang dọn dẹp
                'bao_tri'          // Đang bảo trì
            ])->default('trong')->comment('Trạng thái hiện tại của phòng');
            
            // Giá phòng
            $table->decimal('gia_rieng', 15, 2)->nullable()->comment('Giá riêng của phòng (nếu khác với loại phòng)');
            $table->decimal('gia_bo_sung', 15, 2)->nullable()->comment('Giá bổ sung (ví dụ: view đẹp +200k)');
            
            // Ghi chú
            $table->text('ghi_chu')->nullable()->comment('Ghi chú đặc biệt về phòng');
            
            $table->timestamps();
            
            // Indexes cho performance
            $table->index(['loai_phong_id', 'trang_thai']);
            $table->index('so_phong');
            $table->index('trang_thai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phong');
    }
};
