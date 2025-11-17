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
            $soDem = max(1, $ngayTra->diffInDays($ngayNhan));
        }

        // 3️⃣ Lấy thông tin các loại phòng (giá riêng hoặc giá mặc định)
        $tongTienPhong = 0;
        $roomTypes = $booking->getRoomTypes(); // Phương thức custom, giả sử trả mảng

        foreach ($roomTypes as $rt) {
            $soLuong = (int) ($rt['so_luong'] ?? 1);
            $giaRieng = isset($rt['gia_rieng']) ? (float) $rt['gia_rieng'] : null;
            $loaiPhongId = (int) ($rt['loai_phong_id'] ?? 0);

            // Historical behavior: some code stores 'gia_rieng' as the subtotal (unit * nights * so_luong).
            // To be robust, if 'gia_rieng' is present we treat it as subtotal. Otherwise compute from LoaiPhong.
            if (!is_null($giaRieng) && $giaRieng > 0) {
                // assume subtotal already
                $tongTienPhong += $giaRieng;
            } else {
                $unit = LoaiPhong::find($loaiPhongId)?->gia ?? 0;
                $tongTienPhong += $soLuong * $unit * $soDem;
            }
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
            'giam_gia' => $giamGia,
            'tong_tien' => $tongCong,
        ]);
    }
}
