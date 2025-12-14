<?php

namespace App\Services;

use App\Models\DatPhong;
use App\Models\Phong;
use Carbon\Carbon;

class RoomUpgradeFeeCalculator
{
    /**
     * Tính phí chênh lệch khi nâng cấp phòng
     * 
     * @param DatPhong $booking Booking hiện tại
     * @param Phong $phongCu Phòng hiện tại
     * @param Phong $phongMoi Phòng muốn nâng cấp
     * @param Carbon|null $changeAt Thời điểm đổi phòng (mặc định: thời điểm hiện tại)
     * @param float $vatPercent Phần trăm VAT (mặc định: 0)
     * @param float $serviceChargePercent Phần trăm phí dịch vụ (mặc định: 0)
     * @param array $extras Các phụ thu khác ['name' => amount, ...]
     * @param int|null $soNguoiMoi Số người lớn mới (nếu thêm người khi đổi phòng)
     * @param int|null $soTreEmMoi Số trẻ em mới
     * @param int|null $soEmBeMoi Số em bé mới
     * @return array Chi tiết tính toán
     */
    public static function calculate(
        DatPhong $booking,
        Phong $phongCu,
        Phong $phongMoi,
        ?Carbon $changeAt = null,
        float $vatPercent = 0,
        float $serviceChargePercent = 0,
        array $extras = [],
        ?int $soNguoiMoi = null,
        ?int $soTreEmMoi = null,
        ?int $soEmBeMoi = null
    ): array {
        // Thời điểm đổi phòng (mặc định: hiện tại)
        if (!$changeAt) {
            $changeAt = Carbon::now();
        }

        // Ngày checkout
        $checkOut = Carbon::parse($booking->ngay_tra)->setTime(12, 0); // 12:00 là giờ checkout chuẩn
        $checkIn = Carbon::parse($booking->ngay_nhan);

        // 1. Tính số đêm (toàn bộ số đêm từ ngày nhận đến ngày trả, giống logic client)
        $nights = max(1, $checkIn->diffInDays($checkOut));
        
        // Tính số đêm còn lại (dùng cho các tính toán khác)
        $nightsRemaining = self::calculateNightsRemaining($changeAt, $checkOut);
        $nightsExplanation = self::explainNightsRemaining($changeAt, $checkOut, $nightsRemaining);

        // 2. Lấy giá phòng
        $loaiPhongCu = $phongCu->loaiPhong;
        $loaiPhongMoi = $phongMoi->loaiPhong;
        
        $oldRate = $loaiPhongCu->gia_khuyen_mai ?? $loaiPhongCu->gia_co_ban ?? 0;
        $newRate = $loaiPhongMoi->gia_khuyen_mai ?? $loaiPhongMoi->gia_co_ban ?? 0;

        // 3. Tính chênh lệch giá phòng (dựa trên toàn bộ số đêm, giống client)
        $rateDiff = $newRate - $oldRate;
        $tongGiaPhongCu = $oldRate * $nights;
        $tongGiaPhongMoi = $newRate * $nights;
        
        // Chênh lệch giá (chỉ tính khi phòng mới đắt hơn, giống client)
        $chenhLechGia = max(0, $tongGiaPhongMoi - $tongGiaPhongCu);
        
        // 3.1. Tính phí đổi phòng
        // Phí đổi phòng: nếu chênh lệch giá <= 100K thì miễn phí, còn nếu > 100K thì tính theo chênh lệch giá
        $phiDoiPhongMacDinh = 100000; // 100K
        $phiDoiPhong = 0;
        
        if ($chenhLechGia <= $phiDoiPhongMacDinh) {
            // Chênh lệch giá bằng hoặc ít hơn 100K => miễn phí đổi phòng
            $phiDoiPhong = 0;
        } else {
            // Chênh lệch giá > 100K => tính theo chênh lệch giá
            $phiDoiPhong = $chenhLechGia;
        }
        
        // Tính room_extra cho các tính toán khác (dựa trên số đêm còn lại)
        $roomExtra = $rateDiff * $nightsRemaining;

        // 4. Tính phụ phí thêm người (nếu có)
        $extraGuestFee = 0;
        $extraChildFee = 0;
        $extraInfantFee = 0;
        $extraGuestsCount = 0;
        $extraChildrenCount = 0;
        $extraInfantsCount = 0;
        $maxAdultsPerRoom = 2;
        $extraFeePercent = 0.2; // 20% cho người lớn
        $childFeePercent = 0.1; // 10% cho trẻ em
        $infantFeePercent = 0.05; // 5% cho em bé
        
        // Tính từ thời điểm đổi phòng đến checkout
        $checkInForExtra = $changeAt->copy();
        $checkOutForExtra = Carbon::parse($booking->ngay_tra);
        
        // Phụ phí thêm người lớn
        if ($soNguoiMoi !== null) {
            $soNguoiHienTai = $booking->so_nguoi ?? $maxAdultsPerRoom;
            $extraGuestsCount = max(0, $soNguoiMoi - $soNguoiHienTai);
            
            if ($extraGuestsCount > 0) {
                $extraGuestFee = BookingPriceCalculator::calculateExtraGuestSurcharge(
                    $loaiPhongMoi,
                    $checkInForExtra,
                    $checkOutForExtra,
                    $extraGuestsCount,
                    $extraFeePercent
                );
            }
        }
        
        // Phụ phí thêm trẻ em
        if ($soTreEmMoi !== null) {
            $soTreEmHienTai = $booking->so_tre_em ?? 0;
            $extraChildrenCount = max(0, $soTreEmMoi - $soTreEmHienTai);
            
            if ($extraChildrenCount > 0) {
                $extraChildFee = BookingPriceCalculator::calculateChildSurcharge(
                    $loaiPhongMoi,
                    $checkInForExtra,
                    $checkOutForExtra,
                    $extraChildrenCount,
                    $childFeePercent
                );
            }
        }
        
        // Phụ phí thêm em bé
        if ($soEmBeMoi !== null) {
            $soEmBeHienTai = $booking->so_em_be ?? 0;
            $extraInfantsCount = max(0, $soEmBeMoi - $soEmBeHienTai);
            
            if ($extraInfantsCount > 0) {
                $extraInfantFee = BookingPriceCalculator::calculateInfantSurcharge(
                    $loaiPhongMoi,
                    $checkInForExtra,
                    $checkOutForExtra,
                    $extraInfantsCount,
                    $infantFeePercent
                );
            }
        }
        
        $totalExtraGuestFee = $extraGuestFee + $extraChildFee + $extraInfantFee;

        // 5. Tính tổng phụ thu khác
        $extrasTotal = array_sum($extras);

        // 6. Tính thuế/phí dịch vụ
        // Lưu ý: $phiDoiPhong đã bao gồm chênh lệch giá phòng (nếu > 100K), nên không cộng thêm $roomExtra
        // Áp dụng trên: phiDoiPhong + totalExtraGuestFee + extrasTotal
        // Tuy nhiên, để tính VAT và service charge chính xác, cần tính dựa trên chênh lệch giá thực tế
        // Nếu $phiDoiPhong = 0 (miễn phí) nhưng $roomExtra > 0 (phòng mới rẻ hơn), vẫn cần tính VAT trên $roomExtra
        $baseForTax = $phiDoiPhong + $totalExtraGuestFee + $extrasTotal;
        // Nếu phiDoiPhong = 0 nhưng có chênh lệch giá (roomExtra), vẫn cần tính VAT trên chênh lệch đó
        if ($phiDoiPhong == 0 && $roomExtra != 0) {
            // Trường hợp miễn phí đổi phòng nhưng có chênh lệch giá (phòng mới rẻ hơn)
            // Không tính VAT trên chênh lệch âm
            $baseForTax = max(0, $roomExtra) + $totalExtraGuestFee + $extrasTotal;
        }
        $serviceCharge = $baseForTax * ($serviceChargePercent / 100);
        $vat = ($baseForTax + $serviceCharge) * ($vatPercent / 100);

        // 7. Tổng tiền phải trả thêm
        // Lưu ý: $phiDoiPhong đã bao gồm chênh lệch giá phòng, nên không cộng thêm $roomExtra
        $totalExtra = $phiDoiPhong + $totalExtraGuestFee + $extrasTotal + $serviceCharge + $vat;

        // 8. Xử lý trường hợp rateDiff <= 0
        $refundPolicy = null;
        if ($rateDiff < 0) {
            $refundPolicy = [
                'option_a' => [
                    'name' => 'Không hoàn tiền',
                    'description' => 'Khách không được hoàn lại phần chênh lệch, chỉ đổi phòng với phí đổi phòng cố định.',
                    'recommended' => false,
                ],
                'option_b' => [
                    'name' => 'Hoàn tiền phần chênh lệch',
                    'description' => 'Hoàn lại ' . number_format(abs($roomExtra), 0, ',', '.') . ' VNĐ cho khách (trừ phí đổi phòng).',
                    'recommended' => true,
                ],
            ];
        }

        return [
            'nights_remaining' => $nightsRemaining,
            'nights_explanation' => $nightsExplanation,
            'old_rate' => $oldRate,
            'new_rate' => $newRate,
            'rate_diff' => $rateDiff,
            'room_extra' => $roomExtra,
            'phi_doi_phong' => $phiDoiPhong,
            'phi_doi_phong_co_dinh' => $phiDoiPhongMacDinh,
            'extra_guests_count' => $extraGuestsCount,
            'extra_children_count' => $extraChildrenCount,
            'extra_infants_count' => $extraInfantsCount,
            'extra_guest_fee' => $extraGuestFee,
            'extra_child_fee' => $extraChildFee,
            'extra_infant_fee' => $extraInfantFee,
            'total_extra_guest_fee' => $totalExtraGuestFee,
            'extras' => $extras,
            'extras_total' => $extrasTotal,
            'service_charge_percent' => $serviceChargePercent,
            'service_charge' => $serviceCharge,
            'vat_percent' => $vatPercent,
            'vat' => $vat,
            'total_extra' => $totalExtra,
            'refund_policy' => $refundPolicy,
            'change_at' => $changeAt,
            'check_out' => $checkOut,
            'so_nguoi_hien_tai' => $booking->so_nguoi ?? 2,
            'so_nguoi_moi' => $soNguoiMoi ?? ($booking->so_nguoi ?? 2),
        ];
    }

    /**
     * Tính số đêm còn lại
     */
    private static function calculateNightsRemaining(Carbon $changeAt, Carbon $checkOut): int
    {
        // Nếu changeAt đúng ngày checkout và sau giờ checkout => 0 đêm
        if ($changeAt->isSameDay($checkOut) && $changeAt->gte($checkOut)) {
            return 0;
        }

        // Nếu changeAt sau checkout => 0 đêm
        if ($changeAt->gte($checkOut)) {
            return 0;
        }

        // Tính số đêm từ changeAt đến checkout
        // Nếu changeAt trước 14:00 của ngày đó => tính 1 đêm cho ngày đó
        // Nếu changeAt sau 14:00 của ngày đó => vẫn tính 1 đêm cho ngày đó (vì dùng phòng trong ngày)
        $standardCheckin = $changeAt->copy()->setTime(14, 0);
        
        // Đếm số đêm từ changeAt đến checkout
        $nights = 0;
        $current = $changeAt->copy()->startOfDay();
        $end = $checkOut->copy()->startOfDay();

        while ($current->lt($end)) {
            $nights++;
            $current->addDay();
        }

        // Nếu changeAt trong cùng ngày với checkout và trước checkout => tính 1 đêm
        if ($changeAt->isSameDay($checkOut) && $changeAt->lt($checkOut)) {
            $nights = 1;
        }

        return max(0, $nights);
    }

    /**
     * Giải thích cách tính số đêm
     */
    private static function explainNightsRemaining(Carbon $changeAt, Carbon $checkOut, int $nights): string
    {
        if ($nights == 0) {
            return "Thời điểm đổi phòng (" . $changeAt->format('d/m/Y H:i') . ") đúng hoặc sau giờ checkout (" . $checkOut->format('d/m/Y H:i') . "), không còn đêm nào.";
        }

        $standardCheckin = $changeAt->copy()->setTime(14, 0);
        
        if ($changeAt->lt($standardCheckin)) {
            return "Thời điểm đổi phòng (" . $changeAt->format('d/m/Y H:i') . ") trước giờ check-in chuẩn (14:00), tính 1 đêm cho ngày đó. Tổng cộng: {$nights} đêm còn lại.";
        } else {
            return "Thời điểm đổi phòng (" . $changeAt->format('d/m/Y H:i') . ") sau giờ check-in chuẩn (14:00), vẫn tính 1 đêm cho ngày đó (vì dùng phòng trong ngày). Tổng cộng: {$nights} đêm còn lại.";
        }
    }
}

