<?php

namespace App\Services;

use App\Models\DatPhong;
use App\Models\LoaiPhong;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
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
            $query->where(function ($q) use ($mainInvoiceId) {
                $q->where('invoice_id', $mainInvoiceId)
                    ->orWhereNull('invoice_id');
            });
        } else {
            $query->whereNull('invoice_id');
        }

        $totalServices = $query->select(DB::raw('SUM(quantity * unit_price) as total'))
            ->value('total') ?? 0;

        // 2️⃣ Lấy ngày nhận/trả dạng Carbon (có thể null)
        $ngayNhan = $booking->ngay_nhan ? Carbon::parse($booking->ngay_nhan) : null;
        $ngayTra = $booking->ngay_tra ? Carbon::parse($booking->ngay_tra) : null;

        // 3️⃣ Lấy thông tin các loại phòng (giá riêng hoặc giá mặc định)
        //    và tính tiền phòng theo từng ngày (ngày thường/cuối tuần/lễ)
        $tongTienPhong = 0;
        $roomTypes = $booking->getRoomTypes(); // Phương thức custom, giả sử trả mảng

        foreach ($roomTypes as $rt) {
            $soLuong = (int) ($rt['so_luong'] ?? 1);
            $loaiPhongId = (int) ($rt['loai_phong_id'] ?? 0);

            $loaiPhong = LoaiPhong::find($loaiPhongId);
            if (!$loaiPhong || !$ngayNhan || !$ngayTra || !$ngayTra->greaterThan($ngayNhan)) {
                continue;
            }

            $tongTienPhong += self::calculateRoomTypePriceByDateRange(
                $loaiPhong,
                $ngayNhan,
                $ngayTra,
                $soLuong
            );
        }

        // 4️⃣ Nếu có voucher thì tính giảm giá (CHỈ áp dụng cho tiền phòng)
        $giamGia = 0;
        if ($booking->voucher_id && $booking->voucher) {
            $voucher = $booking->voucher;
            if ($voucher->gia_tri) {
                // Voucher chỉ áp dụng cho tiền phòng (không giảm giá dịch vụ)
                $giamGia = round($tongTienPhong * ($voucher->gia_tri / 100), 0);
            }
        }

        // 5️⃣ Lấy phụ phí phát sinh (nếu có)
        $phiPhatSinh = $booking->phi_phat_sinh ?? 0;
        
        // 5️⃣.1 Lấy phụ phí trẻ em và em bé (tương tự như phụ phí thêm người lớn)
        $phuPhiTreEm = $booking->phu_phi_tre_em ?? 0;
        $phuPhiEmBe = $booking->phu_phi_em_be ?? 0;
        
        // Thêm phụ phí trẻ em và em bé vào tổng tiền phòng (giống như phụ phí thêm người lớn)
        $tongTienPhong += $phuPhiTreEm + $phuPhiEmBe;

        // 6️⃣ Tổng cuối cùng: (Tiền phòng - Giảm giá) + Tiền dịch vụ + Phụ phí
        $tongCong = max(0, $tongTienPhong - $giamGia + $totalServices + $phiPhatSinh);

        // 7️⃣ Cập nhật booking
        $booking->update([
            'tong_tien' => $tongCong,
        ]);

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

    /**
     * Tính hệ số giá theo ngày (ngày thường / cuối tuần / ngày lễ)
     */
    public static function getMultiplierForDate(Carbon $date): float
    {
        if (self::isHoliday($date)) {
            return 1.25; // Ngày lễ: +25%
        }

        if ($date->isWeekend()) {
            return 1.15; // Cuối tuần (thứ 7, CN): +15%
        }

        return 1.0; // Ngày thường
    }

    /**
     * Kiểm tra ngày lễ (tạm thởi chỉ dùng các ngày dương lịch cố định)
     * 01/01, 30/04, 01/05, 02/09
     */
    private static function isHoliday(Carbon $date): bool
    {
        $year = $date->year;

        $holidays = [
            Carbon::create($year, 1, 1)->toDateString(),  // 01/01
            Carbon::create($year, 4, 30)->toDateString(), // 30/04
            Carbon::create($year, 5, 1)->toDateString(),  // 01/05
            Carbon::create($year, 9, 2)->toDateString(),  // 02/09
        ];

        return in_array($date->toDateString(), $holidays, true);
    }

    /**
     * Tính tổng tiền phòng cho 1 loại phòng trong khoảng ngày nhận/trả,
     * áp dụng hệ số theo từng ngày (ngày thường/cuối tuần/lễ)
     */
    public static function calculateRoomTypePriceByDateRange(
        LoaiPhong $loaiPhong,
        Carbon $checkIn,
        Carbon $checkOut,
        int $soLuong
    ): float {
        $total = 0.0;

        $current = $checkIn->copy();
        $end = $checkOut->copy();

        while ($current->lt($end)) {
            $base = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban ?? 0;

            if ($base <= 0) {
                $current->addDay();
                continue;
            }

            $multiplier = self::getMultiplierForDate($current);

            $total += $base * $multiplier * $soLuong;

            $current->addDay();
        }

        return $total;
    }

    /**
     * Tính phụ phí khách thêm theo từng ngày, cũng áp dụng multiplier theo ngày
     */
    public static function calculateExtraGuestSurcharge(
        LoaiPhong $loaiPhong,
        Carbon $checkIn,
        Carbon $checkOut,
        int $extraGuests,
        float $extraFeePercent
    ): float {
        if ($extraGuests <= 0 || $extraFeePercent <= 0) {
            return 0.0;
        }

        $total = 0.0;

        $current = $checkIn->copy();
        $end = $checkOut->copy();

        while ($current->lt($end)) {
            $base = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban ?? 0;

            if ($base <= 0) {
                $current->addDay();
                continue;
            }

            $multiplier = self::getMultiplierForDate($current);

            $priceForDay = $base * $multiplier;
            $total += $extraGuests * $priceForDay * $extraFeePercent;

            $current->addDay();
        }

        return $total;
    }

    /**
     * Tính phụ phí trẻ em theo từng ngày, áp dụng multiplier theo ngày
     */
    public static function calculateChildSurcharge(
        LoaiPhong $loaiPhong,
        Carbon $checkIn,
        Carbon $checkOut,
        int $childrenCount,
        float $childFeePercent
    ): float {
        if ($childrenCount <= 0 || $childFeePercent <= 0) {
            return 0.0;
        }

        $total = 0.0;

        $current = $checkIn->copy();
        $end = $checkOut->copy();

        while ($current->lt($end)) {
            $base = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban ?? 0;

            if ($base <= 0) {
                $current->addDay();
                continue;
            }

            $multiplier = self::getMultiplierForDate($current);

            $priceForDay = $base * $multiplier;
            $total += $childrenCount * $priceForDay * $childFeePercent;

            $current->addDay();
        }

        return $total;
    }

    /**
     * Tính phụ phí em bé theo từng ngày, áp dụng multiplier theo ngày
     */
    public static function calculateInfantSurcharge(
        LoaiPhong $loaiPhong,
        Carbon $checkIn,
        Carbon $checkOut,
        int $infantsCount,
        float $infantFeePercent
    ): float {
        if ($infantsCount <= 0 || $infantFeePercent <= 0) {
            return 0.0;
        }

        $total = 0.0;

        $current = $checkIn->copy();
        $end = $checkOut->copy();

        while ($current->lt($end)) {
            $base = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban ?? 0;

            if ($base <= 0) {
                $current->addDay();
                continue;
            }

            $multiplier = self::getMultiplierForDate($current);

            $priceForDay = $base * $multiplier;
            $total += $infantsCount * $priceForDay * $infantFeePercent;

            $current->addDay();
        }

        return $total;
    }
}
