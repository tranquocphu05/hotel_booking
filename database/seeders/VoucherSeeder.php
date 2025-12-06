<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Voucher;
use App\Models\LoaiPhong;
use Carbon\Carbon;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $loaiPhongs = LoaiPhong::all();

        $vouchers = [
            [
                'loai_phong_id' => null, // Áp dụng cho tất cả loại phòng
                'ma_voucher' => 'WELCOME2024',
                'gia_tri' => 10, // 10%
                'ngay_bat_dau' => Carbon::now()->subDays(30),
                'ngay_ket_thuc' => Carbon::now()->addDays(60),
                'so_luong' => 100,
                'dieu_kien' => 'Đơn hàng tối thiểu: 1000000 VNĐ',
                'trang_thai' => 'con_han',
            ],
            [
                'loai_phong_id' => null,
                'ma_voucher' => 'SUMMER2024',
                'gia_tri' => 15, // 15%
                'ngay_bat_dau' => Carbon::now()->subDays(15),
                'ngay_ket_thuc' => Carbon::now()->addDays(45),
                'so_luong' => 50,
                'dieu_kien' => 'Đơn hàng tối thiểu: 2000000 VNĐ',
                'trang_thai' => 'con_han',
            ],
            [
                'loai_phong_id' => $loaiPhongs->first()->id ?? null,
                'ma_voucher' => 'DELUXE20',
                'gia_tri' => 20, // 20%
                'ngay_bat_dau' => Carbon::now()->subDays(10),
                'ngay_ket_thuc' => Carbon::now()->addDays(30),
                'so_luong' => 30,
                'dieu_kien' => 'Chỉ áp dụng cho phòng Deluxe',
                'trang_thai' => 'con_han',
            ],
            [
                'loai_phong_id' => null,
                'ma_voucher' => 'WEEKEND25',
                'gia_tri' => 25, // 25%
                'ngay_bat_dau' => Carbon::now()->subDays(5),
                'ngay_ket_thuc' => Carbon::now()->addDays(20),
                'so_luong' => 20,
                'dieu_kien' => 'Đơn hàng tối thiểu: 3000000 VNĐ',
                'trang_thai' => 'con_han',
            ],
            [
                'loai_phong_id' => null,
                'ma_voucher' => 'EXPIRED2023',
                'gia_tri' => 30, // 30%
                'ngay_bat_dau' => Carbon::now()->subDays(90),
                'ngay_ket_thuc' => Carbon::now()->subDays(30),
                'so_luong' => 0,
                'dieu_kien' => 'Đã hết hạn',
                'trang_thai' => 'het_han',
            ],
        ];

        foreach ($vouchers as $voucher) {
            Voucher::create($voucher);
        }

        $this->command->info('✅ Created ' . count($vouchers) . ' vouchers');
    }
}
