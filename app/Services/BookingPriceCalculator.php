<?php

namespace App\Services;

use App\Models\DatPhong;
use App\Models\LoaiPhong;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingPriceCalculator
{
    public static function recalcTotal(DatPhong $booking): void
    {
        // 1️⃣ Tổng tiền dịch vụ phát sinh
        $totalServices = $booking->services()
            ->select(DB::raw('SUM(quantity * unit_price) as total'))
            ->value('total') ?? 0;

        // 2️⃣ Tính số đêm (đảm bảo ít nhất là 1 đêm)
        $ngayNhan = $booking->ngay_nhan ? Carbon::parse($booking->ngay_nhan) : null;
        $ngayTra = $booking->ngay_tra ? Carbon::parse($booking->ngay_tra) : null;
        $soDem = 1;

        if ($ngayNhan && $ngayTra && $ngayTra->greaterThan($ngayNhan)) {
            $soDem = max(1, $ngayNhan->diffInDays($ngayTra));
        }

        // 3️⃣ Lấy thông tin các loại phòng (giá riêng hoặc giá mặc định)
        $tongTienPhong = 0;
        $roomTypes = $booking->getRoomTypes(); // Phương thức custom, giả sử trả mảng

        foreach ($roomTypes as $rt) {
            $soLuong = (int) ($rt['so_luong'] ?? 1);
            $loaiPhongId = (int) ($rt['loai_phong_id'] ?? 0);

            // Always use LoaiPhong promotional price (gia_khuyen_mai) if available,
            // otherwise fall back to base price (gia_co_ban).
            $loaiPhong = LoaiPhong::find($loaiPhongId);
            $unit = 0;
            if ($loaiPhong) {
                $unit = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban ?? 0;
            }

            $tongTienPhong += $soLuong * $unit * $soDem;
        }

        // 4️⃣ Nếu có voucher thì tính giảm giá
        $giamGia = 0;
        if ($booking->voucher_id && $booking->voucher) {
            $voucher = $booking->voucher;
            if ($voucher->kieu === 'phan_tram') {
                $giamGia = ($tongTienPhong + $totalServices) * ($voucher->gia_tri / 100);
            } elseif ($voucher->kieu === 'tien_mat') {
                $giamGia = min($voucher->gia_tri, $tongTienPhong + $totalServices);
            }
        }

        // 5️⃣ Tổng cuối cùng (không âm)
        $tongCong = max(0, $tongTienPhong + $totalServices - $giamGia);

        // 6️⃣ Cập nhật lại dữ liệu
        $booking->update([
            'tien_phong' => $tongTienPhong,
            'tong_tien_dich_vu' => $totalServices,
            'tong_tien' => $tongCong,
        ]);
    }
}
