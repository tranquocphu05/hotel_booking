<?php

namespace App\Services;

use App\Models\DatPhong;
use Carbon\Carbon;

class CheckinCheckoutFeeCalculator
{
    /**
     * Tính giá 1 đêm của booking
     * Lấy từ tong_tien_phong / số đêm hoặc tính lại từ room types
     */
    public static function getRoomPricePerNight(DatPhong $booking): float
    {
        // Tính số đêm
        $ngayNhan = $booking->ngay_nhan ? Carbon::parse($booking->ngay_nhan) : null;
        $ngayTra = $booking->ngay_tra ? Carbon::parse($booking->ngay_tra) : null;
        $nights = 1;
        
        if ($ngayNhan && $ngayTra && $ngayTra->greaterThan($ngayNhan)) {
            $nights = max(1, $ngayNhan->diffInDays($ngayTra));
        }

        // Nếu có tong_tien_phong, dùng nó chia cho số đêm
        if ($booking->tong_tien_phong && $booking->tong_tien_phong > 0) {
            return (float)($booking->tong_tien_phong / $nights);
        }

        // Nếu có tien_phong, dùng nó chia cho số đêm
        if ($booking->tien_phong && $booking->tien_phong > 0) {
            return (float)($booking->tien_phong / $nights);
        }

        // Tính lại từ room types
        $tongTienPhong = 0;
        $roomTypes = $booking->getRoomTypes();
        
        foreach ($roomTypes as $rt) {
            $soLuong = (int)($rt['so_luong'] ?? 1);
            $loaiPhongId = (int)($rt['loai_phong_id'] ?? 0);
            $loaiPhong = \App\Models\LoaiPhong::find($loaiPhongId);
            
            if ($loaiPhong) {
                $unit = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban ?? 0;
                $tongTienPhong += $soLuong * $unit;
            }
        }

        return $tongTienPhong > 0 ? (float)$tongTienPhong : 0;
    }

    /**
     * Tính phụ phí check-in sớm
     *
     * @param DatPhong $booking
     * @param Carbon $actualCheckinTime Thời gian check-in thực tế
     * @return float Phụ phí tính được
     */
    public static function calculateEarlyCheckinFee(DatPhong $booking, Carbon $actualCheckinTime): float
    {
        // Giờ check-in chuẩn: 14:00 ngày nhận phòng
        $standardCheckinTime = Carbon::parse($booking->ngay_nhan)->setTime(14, 0);
        
        // Nếu check-in sau 14:00 ngày nhận phòng thì không có phụ phí
        if ($actualCheckinTime->gte($standardCheckinTime)) {
            return 0;
        }

        // Sử dụng diffInMinutes() với absolute=true để đảm bảo giá trị dương
        // Sau đó chia 60 để tính chính xác số giờ
        $diffInMinutes = abs($standardCheckinTime->diffInMinutes($actualCheckinTime, true));
        $earlyHours = (int)floor($diffInMinutes / 60);
        
        // Debug logging
        \Illuminate\Support\Facades\Log::info('Early checkin fee calculation', [
            'booking_id' => $booking->id,
            'ngay_nhan' => $booking->ngay_nhan,
            'standard_time' => $standardCheckinTime->toDateTimeString(),
            'actual_time' => $actualCheckinTime->toDateTimeString(),
            'diff_minutes' => $diffInMinutes,
            'early_hours' => $earlyHours,
        ]);
        
        // Lấy giá 1 đêm
        $roomPricePerNight = self::getRoomPricePerNight($booking);
        
        if ($roomPricePerNight <= 0) {
            return 0;
        }

        // Áp dụng bảng phụ thu dựa trên số giờ sớm
        if ($earlyHours <= 2) {
            // Đến trong khung 12:00–14:00 → phụ thu 10%
            $rate = 0.10;
        } elseif ($earlyHours <= 4) {
            // Đến trong khung 10:00–12:00 → phụ thu 30%
            $rate = 0.30;
        } elseif ($earlyHours <= 8) {
            // Đến trong khung 06:00–10:00 → phụ thu 50%
            $rate = 0.50;
        } else {
            // Đến trước 06:00 hoặc trước ngày nhận phòng → tính thêm 100% (coi như thêm 1 đêm)
            $rate = 1.00;
        }
        
        \Illuminate\Support\Facades\Log::info('Early checkin fee result', [
            'booking_id' => $booking->id,
            'early_hours' => $earlyHours,
            'rate' => $rate,
            'room_price_per_night' => $roomPricePerNight,
            'fee' => round($roomPricePerNight * $rate, 0),
        ]);
        
        return round($roomPricePerNight * $rate, 0);
    }

    /**
     * Tính phụ phí check-out trễ
     * 
     * @param DatPhong $booking
     * @param Carbon $actualCheckoutTime Thời gian check-out thực tế
     * @return float Phụ phí tính được
     */
    public static function calculateLateCheckoutFee(DatPhong $booking, Carbon $actualCheckoutTime): float
    {
        // Giờ check-out chuẩn: 12:00 ngày trả phòng
        $standardCheckoutTime = Carbon::parse($booking->ngay_tra)->setTime(12, 0);
        
        // Nếu check-out trước 12:00 thì không có phụ phí
        if ($actualCheckoutTime->lte($standardCheckoutTime)) {
            return 0;
        }

        // Tính số giờ trễ sử dụng diffInMinutes để chính xác
        $diffInMinutes = abs($actualCheckoutTime->diffInMinutes($standardCheckoutTime, true));
        $lateHours = (int)floor($diffInMinutes / 60);
        
        // Lấy giá 1 đêm
        $roomPricePerNight = self::getRoomPricePerNight($booking);
        
        if ($roomPricePerNight <= 0) {
            return 0;
        }

        // Áp dụng bảng phụ thu
        if ($lateHours <= 2) {
            // 12:00–14:00 → phụ thu 10%
            $rate = 0.10;
        } elseif ($lateHours <= 4) {
            // 14:00–16:00 → phụ thu 30%
            $rate = 0.30;
        } elseif ($lateHours <= 6) {
            // 16:00–18:00 → phụ thu 50%
            $rate = 0.50;
        } else {
            // Sau 18:00 → tính thêm 100% (1 đêm)
            $rate = 1.00;
        }
        
        \Illuminate\Support\Facades\Log::info('Late checkout fee result', [
            'booking_id' => $booking->id,
            'late_hours' => $lateHours,
            'rate' => $rate,
            'room_price_per_night' => $roomPricePerNight,
            'fee' => round($roomPricePerNight * $rate, 0),
        ]);
        
        return round($roomPricePerNight * $rate, 0);
    }
}

