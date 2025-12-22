<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\LoaiPhongSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Chạy seeder này để tạo đầy đủ dữ liệu mẫu cho hệ thống:
     * - Admin user
     * - Loại phòng (room types)
     * - Phòng (rooms)
     * - Dịch vụ (services)
     * - Voucher
     * - Tin tức (news)
     * - Đánh giá (comments/reviews)
     * - Đặt phòng (bookings) với các trạng thái khác nhau
     */
    public function run(): void
    {
        $this->command->info('🚀 Bắt đầu seed dữ liệu đầy đủ cho hệ thống...');
        $this->command->newLine();

        // 1. Tạo admin và khách hàng mẫu
        $this->command->info('📌 1/7 Đang tạo Người dùng (Admin & Customers)...');
        $this->call(AdminUserSeeder::class);
        $this->command->info('   ✅ Hoàn thành Người dùng');
        $this->command->newLine();

        // 2. Tạo loại phòng
        $this->command->info('📌 2/7 Đang tạo Loại phòng...');
        $this->call(LoaiPhongSeeder::class);
        $this->command->info('   ✅ Hoàn thành Loại phòng');
        $this->command->newLine();

        // 3. Tạo phòng cụ thể
        $this->command->info('📌 3/7 Đang tạo Phòng...');
        $this->call(PhongSeeder::class);
        $this->command->info('   ✅ Hoàn thành Phòng');
        $this->command->newLine();

        // 4. Tạo dịch vụ và Voucher
        $this->command->info('📌 4/7 Đang tạo Dịch vụ & Voucher...');
        $this->call(ServiceSeeder::class);
        $this->call(VoucherSeeder::class);
        $this->command->info('   ✅ Hoàn thành Dịch vụ & Voucher');
        $this->command->newLine();

        // 5. Tạo Tin tức (News)
        $this->command->info('📌 5/7 Đang tạo Tin tức...');
        $this->call(NewsSeeder::class);
        $this->command->info('   ✅ Hoàn thành Tin tức');
        $this->command->newLine();

        // 6. Tạo Đánh giá (Comments)
        $this->command->info('📌 6/7 Đang tạo Đánh giá...');
        $this->call(CommentSeeder::class);
        $this->command->info('   ✅ Hoàn thành Đánh giá');
        $this->command->newLine();

        // 7. Tạo Đặt phòng (Bookings) - Bao gồm Invoices và StayGuests
        $this->command->info('📌 7/7 Đang tạo Đặt phòng & Dữ liệu vận hành...');
        $this->call(BookingSeeder::class);
        $this->command->info('   ✅ Hoàn thành Đặt phòng');
        $this->command->newLine();

        $this->command->info('✅ ✅ ✅ Hoàn tất seed dữ liệu đầy đủ cho hệ thống! ✅ ✅ ✅');
        $this->command->newLine();
        
        $this->command->info('📊 Tóm tắt dữ liệu:');
        $this->command->info('   - Người dùng: ' . \App\Models\User::count());
        $this->command->info('   - Loại phòng: ' . \App\Models\LoaiPhong::count());
        $this->command->info('   - Tổng số phòng: ' . \App\Models\Phong::count());
        $this->command->info('   - Dịch vụ: ' . \App\Models\Service::count());
        $this->command->info('   - Voucher active: ' . \App\Models\Voucher::where('trang_thai', 'con_han')->count());
        $this->command->info('   - Tin tức: ' . \App\Models\News::count());
        $this->command->info('   - Đặt phòng: ' . \App\Models\DatPhong::count());
        $this->command->newLine();
    }

}
