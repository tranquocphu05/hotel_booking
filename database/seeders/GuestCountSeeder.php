<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DatPhong;
use App\Models\LoaiPhong;
use App\Models\Phong;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GuestCountSeeder extends Seeder
{
    /**
     * Tạo booking loại phòng Family với:
     * - 4 người lớn
     * - 3 trẻ em (6-12 tuổi)
     * - 2 em bé (0-5 tuổi)
     */
    public function run(): void
    {
        $user = User::where('vai_tro', 'khach_hang')->first();
        
        if (!$user) {
            $this->command->warn('⚠️  Không tìm thấy khách hàng. Tạo user test...');
            $user = User::create([
                'username' => 'guest_test',
                'email' => 'guest_test@example.com',
                'password' => bcrypt('password'),
                'ho_ten' => 'Nguyễn Văn Test',
                'sdt' => '0987654321',
                'dia_chi' => 'TP. Hồ Chí Minh',
                'cccd' => '079123456789',
                'vai_tro' => 'khach_hang',
                'trang_thai' => 'hoat_dong',
            ]);
        }

        // Lấy loại phòng Family
        $loaiPhong = LoaiPhong::where('trang_thai', 'hoat_dong')
            ->where('ten_loai', 'like', '%Family%')
            ->first();
        
        if (!$loaiPhong) {
            $loaiPhong = LoaiPhong::where('trang_thai', 'hoat_dong')->first();
            $this->command->warn('⚠️  Không tìm thấy phòng Family, dùng: ' . ($loaiPhong->ten_loai ?? 'N/A'));
        }
        
        if (!$loaiPhong) {
            $this->command->error('❌ Không tìm thấy loại phòng.');
            return;
        }

        // Số lượng khách: 4 người lớn, 3 trẻ em, 2 em bé
        $soNguoiLon = 4;
        $soTreEm = 3;
        $soEmBe = 2;
        $soLuongPhong = 2;

        $ngayNhan = Carbon::now()->addDays(7);
        $ngayTra = Carbon::now()->addDays(10);
        $soNgay = $ngayNhan->diffInDays($ngayTra);

        $giaPhong = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban;
        $tongTienPhong = $giaPhong * $soNgay * $soLuongPhong;
        $phuPhiTreEm = $soTreEm * 200000 * $soNgay;
        $phuPhiEmBe = $soEmBe * 100000 * $soNgay;
        $tongTien = $tongTienPhong + $phuPhiTreEm + $phuPhiEmBe;

        DB::transaction(function () use (
            $user, $loaiPhong, $soLuongPhong,
            $soNguoiLon, $soTreEm, $soEmBe,
            $ngayNhan, $ngayTra,
            $tongTienPhong, $phuPhiTreEm, $phuPhiEmBe, $tongTien
        ) {
            $booking = DatPhong::create([
                'nguoi_dung_id' => $user->id,
                'loai_phong_id' => $loaiPhong->id,
                'so_luong_da_dat' => $soLuongPhong,
                'ngay_dat' => Carbon::now(),
                'ngay_nhan' => $ngayNhan,
                'ngay_tra' => $ngayTra,
                'so_nguoi' => $soNguoiLon,
                'so_tre_em' => $soTreEm,
                'so_em_be' => $soEmBe,
                'phu_phi_tre_em' => $phuPhiTreEm,
                'phu_phi_em_be' => $phuPhiEmBe,
                'tong_tien_phong' => $tongTienPhong,
                'tong_tien' => $tongTien,
                'trang_thai' => 'da_xac_nhan',
                'username' => $user->ho_ten,
                'email' => $user->email,
                'sdt' => $user->sdt,
                'cccd' => $user->cccd,
                'ghi_chu' => 'Booking Family: 4 người lớn, 3 trẻ em, 2 em bé',
            ]);

            $booking->syncRoomTypes([
                $loaiPhong->id => [
                    'so_luong' => $soLuongPhong,
                    'gia_rieng' => $tongTienPhong,
                    'so_nguoi' => $soNguoiLon,
                    'so_tre_em' => $soTreEm,
                    'so_em_be' => $soEmBe,
                ],
            ]);

            $rooms = Phong::where('loai_phong_id', $loaiPhong->id)
                ->where('trang_thai', 'trong')
                ->take($soLuongPhong)
                ->get();

            $roomIds = $rooms->pluck('id')->toArray();
            
            if (!empty($roomIds)) {
                // Phân bổ: Phòng 1: 2 người lớn, 2 trẻ em, 1 em bé
                //          Phòng 2: 2 người lớn, 1 trẻ em, 1 em bé
                $guestDistribution = [
                    0 => ['so_nguoi_lon' => 2, 'so_tre_em' => 2, 'so_em_be' => 1],
                    1 => ['so_nguoi_lon' => 2, 'so_tre_em' => 1, 'so_em_be' => 1],
                ];

                foreach ($roomIds as $index => $roomId) {
                    $dist = $guestDistribution[$index] ?? ['so_nguoi_lon' => 0, 'so_tre_em' => 0, 'so_em_be' => 0];
                    
                    DB::table('booking_rooms')->updateOrInsert(
                        ['dat_phong_id' => $booking->id, 'phong_id' => $roomId],
                        [
                            'so_nguoi_lon' => $dist['so_nguoi_lon'],
                            'so_tre_em' => $dist['so_tre_em'],
                            'so_em_be' => $dist['so_em_be'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );

                    Phong::where('id', $roomId)->update(['trang_thai' => 'dang_thue']);
                }
            }

            Invoice::create([
                'dat_phong_id' => $booking->id,
                'tien_phong' => $tongTienPhong,
                'tien_dich_vu' => $phuPhiTreEm + $phuPhiEmBe,
                'giam_gia' => 0,
                'tong_tien' => $tongTien,
                'trang_thai' => 'da_thanh_toan',
                'phuong_thuc' => 'vnpay',
            ]);

            $this->command->info("✅ Tạo booking #{$booking->id} - Family");
            $this->command->info("   Người lớn: {$soNguoiLon} | Trẻ em: {$soTreEm} | Em bé: {$soEmBe}");
            $this->command->info("   Tổng tiền: " . number_format($tongTien, 0, ',', '.') . " VNĐ");
        });
    }
}
