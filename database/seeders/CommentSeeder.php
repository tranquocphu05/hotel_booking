<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Comment;
use App\Models\LoaiPhong;
use Illuminate\Support\Carbon;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy tất cả loại phòng
        $loaiPhongs = LoaiPhong::all();
        
        if ($loaiPhongs->isEmpty()) {
            $this->command->info('Không có loại phòng nào trong database. Vui lòng tạo loại phòng trước.');
            return;
        }

        // Lấy hoặc tạo user mẫu
        $users = User::limit(5)->get();
        if ($users->isEmpty()) {
            $users = collect([
                User::create([
                    'ho_ten' => 'Nguyễn Văn An',
                    'email' => 'an@example.com',
                    'password' => bcrypt('123456'),
                    'vai_tro' => 'khach_hang',
                    'trang_thai' => 'hoat_dong'
                ]),
                User::create([
                    'ho_ten' => 'Trần Thị Bình',
                    'email' => 'binh@example.com', 
                    'password' => bcrypt('123456'),
                    'vai_tro' => 'khach_hang',
                    'trang_thai' => 'hoat_dong'
                ]),
                User::create([
                    'ho_ten' => 'Lê Minh Cường',
                    'email' => 'cuong@example.com',
                    'password' => bcrypt('123456'),
                    'vai_tro' => 'khach_hang', 
                    'trang_thai' => 'hoat_dong'
                ])
            ]);
        }

        $comments = [
            'Phòng rất đẹp và sạch sẽ, nhân viên phục vụ tận tình.',
            'Vị trí khách sạn thuận tiện, gần trung tâm thành phố.',
            'Giá cả hợp lý, chất lượng dịch vụ tốt.',
            'Phòng tắm hiện đại, đầy đủ tiện nghi.',
            'Tôi sẽ quay lại lần sau, rất hài lòng.',
            'Không gian thoáng mát, view đẹp từ cửa sổ.',
            'Đồ ăn sáng ngon, đa dạng món ăn.',
            'Wifi nhanh, phù hợp cho công việc.',
            'Giường ngủ êm ái, ngủ rất ngon.',
            'Dịch vụ lễ tân 24/7 rất tiện lợi.',
            'Bãi đậu xe rộng rãi, an toàn.',
            'Phòng gym đầy đủ thiết bị hiện đại.',
            'Hồ bơi sạch sẽ, thoáng mát.',
            'Spa thư giãn, massage chuyên nghiệp.',
            'Nhà hàng phục vụ món ăn ngon.',
        ];

        // Xóa đánh giá cũ trước khi tạo mới
        Comment::truncate();

        // Tạo đánh giá cho mỗi loại phòng
        foreach ($loaiPhongs as $loaiPhong) {
            // Tạo 3-8 đánh giá cho mỗi loại phòng
            $numReviews = rand(3, 8);
            
            for ($i = 0; $i < $numReviews; $i++) {
                $user = $users->random();
                $rating = rand(3, 5); // Chỉ tạo đánh giá từ 3-5 sao
                $comment = $comments[array_rand($comments)];
                
                Comment::create([
                    'nguoi_dung_id' => $user->id,
                    'loai_phong_id' => $loaiPhong->id,
                    'noi_dung' => $comment,
                    'so_sao' => $rating,
                    'img' => null, // Không tạo ảnh mẫu
                    'ngay_danh_gia' => Carbon::now()->subDays(rand(1, 60)),
                    'trang_thai' => 'hien_thi',
                ]);
            }
        }

        $this->command->info('Đã tạo thành công đánh giá mẫu cho tất cả loại phòng.');
    }
}
