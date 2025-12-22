<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            // Ăn uống
            [
                'name' => 'Bữa sáng buffet',
                'price' => 150000,
                'unit' => 'người',
                'loai' => 'an_uong',
                'describe' => 'Buffet sáng phong phú với món Á - Âu',
                'status' => 'hoat_dong',
            ],
            [
                'name' => 'Bữa trưa/tối',
                'price' => 250000,
                'unit' => 'người',
                'loai' => 'an_uong',
                'describe' => 'Set menu trưa/tối theo yêu cầu',
                'status' => 'hoat_dong',
            ],
            [
                'name' => 'Đồ uống phòng',
                'price' => 50000,
                'unit' => 'ly',
                'loai' => 'an_uong',
                'describe' => 'Nước ngọt, bia, rượu... giao tận phòng',
                'status' => 'hoat_dong',
            ],
            [
                'name' => 'Minibar',
                'price' => 200000,
                'unit' => 'lần',
                'loai' => 'an_uong',
                'describe' => 'Bổ sung minibar trong phòng',
                'status' => 'hoat_dong',
            ],

            // Giặt ủi
            [
                'name' => 'Giặt ủi quần áo',
                'price' => 30000,
                'unit' => 'kg',
                'loai' => 'giat_ui',
                'describe' => 'Dịch vụ giặt ủi nhanh trong ngày',
                'status' => 'hoat_dong',
            ],
            [
                'name' => 'Giặt khô',
                'price' => 80000,
                'unit' => 'bộ',
                'loai' => 'giat_ui',
                'describe' => 'Giặt khô cho vest, áo dài...',
                'status' => 'hoat_dong',
            ],

            // Spa & Massage
            [
                'name' => 'Massage body',
                'price' => 300000,
                'unit' => 'giờ',
                'loai' => 'spa',
                'describe' => 'Massage toàn thân thư giãn',
                'status' => 'hoat_dong',
            ],
            [
                'name' => 'Massage chân',
                'price' => 150000,
                'unit' => 'giờ',
                'loai' => 'spa',
                'describe' => 'Massage chân giảm mỏi',
                'status' => 'hoat_dong',
            ],

            // Vận chuyển
            [
                'name' => 'Đưa đón sân bay',
                'price' => 500000,
                'unit' => 'chuyến',
                'loai' => 'van_chuyen',
                'describe' => 'Xe 4-7 chỗ đưa đón sân bay',
                'status' => 'hoat_dong',
            ],
            [
                'name' => 'Thuê xe du lịch',
                'price' => 1500000,
                'unit' => 'ngày',
                'loai' => 'van_chuyen',
                'describe' => 'Thuê xe 16 chỗ có tài xế',
                'status' => 'hoat_dong',
            ],

            // Khác
            [
                'name' => 'Dọn phòng thêm',
                'price' => 100000,
                'unit' => 'lần',
                'loai' => 'khac',
                'describe' => 'Dọn phòng ngoài giờ quy định',
                'status' => 'hoat_dong',
            ],
            [
                'name' => 'Giường phụ',
                'price' => 200000,
                'unit' => 'đêm',
                'loai' => 'khac',
                'describe' => 'Thêm giường phụ trong phòng',
                'status' => 'hoat_dong',
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['name' => $service['name']],
                $service
            );
        }
    }
}
