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

        // 1. Tạo admin user
        $this->command->info('📌 1/8 Đang tạo Admin user...');
        $this->call(AdminUserSeeder::class);
        $this->command->info('   ✅ Hoàn thành Admin user');
        $this->command->newLine();

        // 2. Tạo loại phòng
        $this->command->info('📌 2/8 Đang tạo Loại phòng...');
        $this->call(LoaiPhongSeeder::class);
        $this->command->info('   ✅ Hoàn thành Loại phòng');
        $this->command->newLine();

        // 3. Tạo phòng
        $this->command->info('📌 3/8 Đang tạo Phòng...');
        $this->call(PhongSeeder::class);
        $this->command->info('   ✅ Hoàn thành Phòng');
        $this->command->newLine();

        // 4. Tạo thêm phòng cho 4 loại phòng (20 phòng mỗi loại)
        $this->command->info('📌 4/8 Đang tạo thêm phòng cho 4 loại phòng...');
        $this->call(FourTypesTwentyRoomsSeeder::class);
        $this->command->info('   ✅ Hoàn thành thêm phòng');
        $this->command->newLine();

        // 5. Tạo dịch vụ
        $this->command->info('📌 5/8 Đang tạo Dịch vụ...');
        $this->call(ServiceSeeder::class);
        $this->command->info('   ✅ Hoàn thành Dịch vụ');
        $this->command->newLine();

        // 6. Tạo Voucher
        $this->command->info('📌 6/8 Đang tạo Voucher...');
        $this->call(VoucherSeeder::class);
        $this->command->info('   ✅ Hoàn thành Voucher');
        $this->command->newLine();

        // 7. Tạo Tin tức
        $this->command->info('📌 7/8 Đang tạo Tin tức...');
        $this->call(NewsSeeder::class);
        $this->command->info('   ✅ Hoàn thành Tin tức');
        $this->command->newLine();

        // 8. Tạo Đánh giá
        $this->command->info('📌 8/9 Đang tạo Đánh giá...');
        $this->call(CommentSeeder::class);
        $this->command->info('   ✅ Hoàn thành Đánh giá');
        $this->command->newLine();

        // 9. Tạo Đặt phòng (bookings) - Cuối cùng vì phụ thuộc vào các dữ liệu trên
        $this->command->info('📌 9/9 Đang tạo Đặt phòng...');
        $this->call(BookingSeeder::class);
        $this->command->info('   ✅ Hoàn thành Đặt phòng');
        $this->command->newLine();

        $this->command->info('✅ ✅ ✅ Hoàn tất seed dữ liệu đầy đủ! ✅ ✅ ✅');
        $this->command->newLine();
        $this->command->info('📊 Tóm tắt dữ liệu đã tạo:');
        $this->command->info('   - Admin user: 1');
        $this->command->info('   - Loại phòng: ' . \App\Models\LoaiPhong::count());
        $this->command->info('   - Phòng: ' . \App\Models\Phong::count());
        $this->command->info('   - Dịch vụ: ' . \App\Models\Service::count());
        $this->command->info('   - Voucher: ' . \App\Models\Voucher::count());
        $this->command->info('   - Tin tức: ' . \App\Models\News::count());
        $this->command->info('   - Đánh giá: ' . \App\Models\Comment::count());
        $this->command->info('   - Đặt phòng: ' . \App\Models\DatPhong::count());
        $this->command->info('   - User (khách hàng): ' . \App\Models\User::where('vai_tro', 'khach_hang')->count());
        $this->command->newLine();
    }
}
