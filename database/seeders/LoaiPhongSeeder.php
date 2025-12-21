<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LoaiPhong;
use Illuminate\Support\Facades\DB;

class LoaiPhongSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Quy định sức chứa:
        // - suc_chua: Số người lớn tiêu chuẩn (tối đa 2 người lớn/phòng theo quy định client)
        // - suc_chua_tre_em: Số trẻ em tối đa (6-12 tuổi)
        // - suc_chua_em_be: Số em bé tối đa (0-5 tuổi)
        // - phi_tre_em: Phụ phí trẻ em/đêm (VNĐ)
        // - phi_em_be: Phụ phí em bé/đêm (VNĐ)
        
        $loaiPhongs = [
            [
                'ten_loai' => 'Phòng Standard',
                'mo_ta' => 'Phòng tiêu chuẩn với đầy đủ tiện nghi cơ bản, phù hợp cho khách du lịch cá nhân hoặc cặp đôi. Phòng được trang bị giường đôi, phòng tắm riêng, TV và wifi miễn phí.',
                'suc_chua' => 3,              // 3 người lớn tối đa
                'suc_chua_tre_em' => 2,       // Tối đa 2 trẻ em
                'suc_chua_em_be' => 1,        // Tối đa 1 em bé
                'phi_tre_em' => 100000,       // 100k/trẻ em/đêm
                'phi_em_be' => 0,             // Miễn phí em bé
                'gia_co_ban' => 500000,
                'gia_khuyen_mai' => 450000,
                'so_luong_phong' => 10,
                'so_luong_trong' => 8,
                'diem_danh_gia' => 4.2,
                'so_luong_danh_gia' => 35,
                'trang_thai' => 'hoat_dong',
                'anh' => 'img/room/room-1.jpg',
            ],
            [
                'ten_loai' => 'Phòng Deluxe',
                'mo_ta' => 'Phòng Deluxe rộng rãi và sang trọng hơn, có view đẹp, ban công riêng. Phù hợp cho gia đình nhỏ hoặc khách hàng muốn không gian thoải mái hơn.',
                'suc_chua' => 3,              // 3 người lớn tối đa
                'suc_chua_tre_em' => 2,       // Tối đa 2 trẻ em
                'suc_chua_em_be' => 1,        // Tối đa 1 em bé
                'phi_tre_em' => 150000,       // 150k/trẻ em/đêm
                'phi_em_be' => 0,             // Miễn phí em bé
                'gia_co_ban' => 800000,
                'gia_khuyen_mai' => null,
                'so_luong_phong' => 8,
                'so_luong_trong' => 6,
                'diem_danh_gia' => 4.5,
                'so_luong_danh_gia' => 28,
                'trang_thai' => 'hoat_dong',
                'anh' => 'img/room/room-2.jpg',
            ],
            [
                'ten_loai' => 'Phòng Suite',
                'mo_ta' => 'Phòng Suite cao cấp với phòng khách riêng, phòng ngủ rộng rãi, mini bar và dịch vụ đặc biệt. Lý tưởng cho các dịp đặc biệt hoặc khách hàng VIP.',
                'suc_chua' => 3,              // 3 người lớn tối đa
                'suc_chua_tre_em' => 2,       // Tối đa 2 trẻ em
                'suc_chua_em_be' => 2,        // Tối đa 2 em bé
                'phi_tre_em' => 200000,       // 200k/trẻ em/đêm
                'phi_em_be' => 50000,         // 50k/em bé/đêm
                'gia_co_ban' => 1500000,
                'gia_khuyen_mai' => 1200000,
                'so_luong_phong' => 5,
                'so_luong_trong' => 3,
                'diem_danh_gia' => 4.8,
                'so_luong_danh_gia' => 15,
                'trang_thai' => 'hoat_dong',
                'anh' => 'img/room/room-3.jpg',
            ],
            [
                'ten_loai' => 'Phòng Family',
                'mo_ta' => 'Phòng gia đình rộng rãi với 2 phòng ngủ, phù hợp cho gia đình có trẻ em. Có bếp nấu ăn nhỏ và không gian vui chơi cho trẻ em.',
                'suc_chua' => 4,              // 4 người lớn tối đa (phòng gia đình lớn hơn)
                'suc_chua_tre_em' => 3,       // Tối đa 3 trẻ em
                'suc_chua_em_be' => 2,        // Tối đa 2 em bé
                'phi_tre_em' => 120000,       // 120k/trẻ em/đêm
                'phi_em_be' => 0,             // Miễn phí em bé
                'gia_co_ban' => 1200000,
                'gia_khuyen_mai' => null,
                'so_luong_phong' => 6,
                'so_luong_trong' => 5,
                'diem_danh_gia' => 4.6,
                'so_luong_danh_gia' => 22,
                'trang_thai' => 'hoat_dong',
                'anh' => 'img/room/room-4.jpg',
            ],
            [
                'ten_loai' => 'Phòng Executive',
                'mo_ta' => 'Phòng Executive với không gian làm việc riêng, bàn làm việc rộng, wifi tốc độ cao. Phù hợp cho khách công tác hoặc doanh nhân.',
                'suc_chua' => 3,              // 3 người lớn tối đa
                'suc_chua_tre_em' => 2,       // Tối đa 2 trẻ em
                'suc_chua_em_be' => 1,        // Tối đa 1 em bé
                'phi_tre_em' => 180000,       // 180k/trẻ em/đêm
                'phi_em_be' => 0,             // Miễn phí em bé
                'gia_co_ban' => 1000000,
                'gia_khuyen_mai' => 900000,
                'so_luong_phong' => 7,
                'so_luong_trong' => 4,
                'diem_danh_gia' => 4.4,
                'so_luong_danh_gia' => 18,
                'trang_thai' => 'hoat_dong',
                'anh' => 'img/room/room-5.jpg',
            ],
            [
                'ten_loai' => 'Phòng Ocean View',
                'mo_ta' => 'Phòng có view biển tuyệt đẹp, ban công rộng hướng ra biển. Phù hợp cho khách hàng muốn tận hưởng cảnh biển bình minh hoặc hoàng hôn.',
                'suc_chua' => 3,              // 3 người lớn tối đa
                'suc_chua_tre_em' => 2,       // Tối đa 2 trẻ em
                'suc_chua_em_be' => 1,        // Tối đa 1 em bé
                'phi_tre_em' => 250000,       // 250k/trẻ em/đêm
                'phi_em_be' => 50000,         // 50k/em bé/đêm
                'gia_co_ban' => 1800000,
                'gia_khuyen_mai' => null,
                'so_luong_phong' => 4,
                'so_luong_trong' => 2,
                'diem_danh_gia' => 4.9,
                'so_luong_danh_gia' => 12,
                'trang_thai' => 'hoat_dong',
                'anh' => 'img/room/room-6.jpg',
            ],
        ];

        foreach ($loaiPhongs as $loaiPhong) {
            LoaiPhong::updateOrCreate(
                ['ten_loai' => $loaiPhong['ten_loai']],
                $loaiPhong
            );
        }

        $this->command->info('Đã seed ' . count($loaiPhongs) . ' loại phòng thành công!');
    }
}

