<?php

namespace App\Services;

use App\Models\DatPhong;
use App\Models\LoaiPhong;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class BookingPriceCalculator
{
    public static function recalcTotal(DatPhong $booking): void
    {
        // 1️⃣ Tổng tiền dịch vụ phát sinh (Chỉ tính dịch vụ của Main Invoice hoặc chưa gán)
        $mainInvoice = $booking->invoice;
        $mainInvoiceId = $mainInvoice ? $mainInvoice->id : null;

        $query = $booking->services();
        if ($mainInvoiceId) {
            $query->where(function($q) use ($mainInvoiceId) {
                $q->where('invoice_id', $mainInvoiceId)
                  ->orWhereNull('invoice_id');
            });
        } else {
            $query->whereNull('invoice_id');
        }

        $totalServices = $query->select(DB::raw('SUM(quantity * unit_price) as total'))
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

        // 4️⃣ Nếu có voucher thì tính giảm giá (CHỈ áp dụng cho tiền phòng)
        // Nếu voucher gán cho 1 loại phòng cụ thể (loai_phong_id), thì chỉ áp dụng phần tương ứng của loại phòng đó
        $giamGia = 0;
        if ($booking->voucher_id && $booking->voucher) {
            $voucher = $booking->voucher;
            if ($voucher->gia_tri) {
                $percent = $voucher->gia_tri / 100;
                if (!empty($voucher->loai_phong_id)) {
                    // Compute subtotal only for room types matching voucher->loai_phong_id
                    $applicableTotal = 0;
                    $roomTypes = $booking->getRoomTypes();
                    foreach ($roomTypes as $rt) {
                        $lpId = $rt['loai_phong_id'] ?? null;
                        if ($lpId && $lpId == $voucher->loai_phong_id) {
                            $soLuong = (int) ($rt['so_luong'] ?? 1);
                            $loaiPhong = LoaiPhong::find($lpId);
                            $unit = 0;
                            if ($loaiPhong) {
                                $unit = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban ?? 0;
                            }
                            $applicableTotal += $soLuong * $unit * $soDem;
                        }
                    }
                    $giamGia = round($applicableTotal * $percent, 0);
                } else {
                    // Voucher applies to full room total
                    $giamGia = round($tongTienPhong * $percent, 0);
                }
            }
        }

        // 5️⃣ Lấy phụ phí phát sinh (nếu có)
        $phiPhatSinh = $booking->phi_phat_sinh ?? 0;

        // 6️⃣ Tổng cuối cùng: (Tiền phòng - Giảm giá) + Tiền dịch vụ + Phụ phí
        $tongCong = max(0, $tongTienPhong - $giamGia + $totalServices + $phiPhatSinh);

        // 7️⃣ Cập nhật booking
        // Note: tien_phong stores the FULL room price (before voucher discount)
        // The voucher discount is only applied to the final tong_tien
        $bookingUpdate = [
            'tong_tien' => $tongCong,
        ];

        if (Schema::hasColumn('dat_phong', 'tien_phong')) {
            $bookingUpdate['tien_phong'] = $tongTienPhong;
        }
        if (Schema::hasColumn('dat_phong', 'tien_dich_vu')) {
            $bookingUpdate['tien_dich_vu'] = $totalServices;
        }

        $booking->update($bookingUpdate);

        // 8️⃣ Cập nhật invoice nếu có
        if ($booking->invoice) {
            // Tính số tiền còn lại = Tổng tiền - Đã thanh toán
            $daThanhtoan = $booking->invoice->da_thanh_toan ?? 0;
            $conLai = max(0, $tongCong - $daThanhtoan);

            $booking->invoice->update([
                'tien_phong' => $tongTienPhong,
                'tien_dich_vu' => $totalServices,
                'phi_phat_sinh' => $phiPhatSinh,
                'giam_gia' => $giamGia,
                'tong_tien' => $tongCong,
                'con_lai' => $conLai,
            ]);
        }
    }
} 
