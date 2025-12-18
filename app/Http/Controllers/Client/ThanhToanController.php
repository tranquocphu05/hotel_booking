<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\DatPhong;
use App\Models\Invoice;
use App\Models\ThanhToan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ThanhToanController extends Controller
{
    public function show(DatPhong $datPhong)
    {
        // Authorization: User can only view their own bookings
        if (\Illuminate\Support\Facades\Auth::check() && $datPhong->nguoi_dung_id && $datPhong->nguoi_dung_id !== \Illuminate\Support\Facades\Auth::id()) {
            abort(403, 'Bạn không có quyền xem đơn đặt phòng này.');
        }

        // Eager load relationships for efficiency
        $datPhong->load('voucher', 'loaiPhong', 'user', 'phong', 'services');

        // Get available rooms for assignment if needed
        $availableRooms = null;
        if ($datPhong->ngay_nhan && $datPhong->ngay_tra) {
            $roomTypes = $datPhong->getRoomTypes();
            $assignedPhongIds = $datPhong->getPhongIds();
            $totalRooms = $roomTypes->sum(function($item) { return $item['so_luong'] ?? 1; }) ?: ($datPhong->so_luong_da_dat ?? 1);
            $assignedCount = count($assignedPhongIds);
            $remainingCount = $totalRooms - $assignedCount;

            // If rooms are missing, get available rooms for all room types
            if ($remainingCount > 0) {
                $availableRooms = collect();
                foreach ($roomTypes as $roomType) {
                    $rooms = \App\Models\Phong::findAvailableRooms(
                        $roomType['loai_phong_id'],
                        $datPhong->ngay_nhan,
                        $datPhong->ngay_tra,
                        20
                    );
                    // Filter out already assigned rooms
                    $rooms = $rooms->reject(function($room) use ($assignedPhongIds) {
                        return $room instanceof \App\Models\Phong && in_array($room->id, $assignedPhongIds);
                    });
                    $availableRooms = $availableRooms->merge($rooms);
                }
                $availableRooms = $availableRooms->unique('id')->values();
            }
        }

        // Calculate number of nights
        $nights = $this->calculateNights($datPhong->ngay_nhan, $datPhong->ngay_tra);

        // Get room types from JSON or fallback to single room type
        $roomTypes = $datPhong->getRoomTypes();

        // Tính giá gốc và phụ phí
        // Logic: Tính lại từ gia_rieng trong pivot table
        // gia_rieng = (basePrice + extraFee + childFee + infantFee) * priceRatio (nếu có voucher)
        // Để tính lại đúng, ta cần:
        // 1. Tính giá phòng gốc (basePrice) từ LoaiPhong
        // 2. Tính tổng phụ phí từ gia_rieng: surcharge = gia_rieng - basePrice (sau khi chia lại priceRatio nếu có)
        // 3. Phụ phí trẻ em và em bé đã có trong database (tổng cho tất cả loại phòng)
        // 4. Phụ phí thêm người lớn = tổng phụ phí - phụ phí trẻ em - phụ phí em bé
        
        $giaPhongGoc = 0;
        $phuPhiNguoiLon = 0;
        $phuPhiTreEm = 0;
        $phuPhiEmBe = 0;
        $surchargeMap = [];
        
        // Tính priceRatio nếu có voucher
        $priceRatio = 1;
        if ($datPhong->voucher_id && $datPhong->voucher) {
            $voucher = $datPhong->voucher;
            if ($voucher->gia_tri && $voucher->gia_tri > 0 && $voucher->gia_tri <= 100) {
                $priceRatio = 1 - ($voucher->gia_tri / 100);
            }
        }
        
        // Tính lại phụ phí trẻ em và em bé (ước tính nếu thiếu dữ liệu trong DB)
        // Chính sách mới: Trẻ em = 150,000 VNĐ / người / đêm, Em bé = miễn phí
        // Ở đây chỉ dùng để ước lượng trong trường hợp booking cũ chưa có phu_phi_tre_em / phu_phi_em_be.
        $phuPhiTreEm = 0;
        $phuPhiEmBe = 0;
        
        if (!empty($roomTypes) && $roomTypes->isNotEmpty()) {
            // Tính phụ phí từ từng loại phòng
            foreach ($roomTypes as $roomType) {
                $soLuong = $roomType['so_luong'] ?? 1;
                $lp = \App\Models\LoaiPhong::find($roomType['loai_phong_id']);
                if ($lp) {
                    // Áp dụng giá cố định cho phụ phí thay vì % theo giá phòng
                    $childFeeRate = 150000; // 150K / trẻ em / đêm
                    $infantFeeRate = 0;     // Em bé miễn phí
                    
                    // Phân bổ số trẻ em và em bé cho loại phòng này (giả sử phân bổ đều)
                    $totalRooms = $roomTypes->sum(function($item) { return $item['so_luong'] ?? 1; });
                    $childrenForType = $totalRooms > 0 ? (($datPhong->so_tre_em ?? 0) * $soLuong / $totalRooms) : 0;
                    $infantsForType = $totalRooms > 0 ? (($datPhong->so_em_be ?? 0) * $soLuong / $totalRooms) : 0;
                    
                    $phuPhiTreEm += $childrenForType * $childFeeRate * $nights;
                    $phuPhiEmBe += $infantsForType * $infantFeeRate * $nights;
                }
            }
        } else {
            // Fallback: Tính từ loại phòng chính
            $lp = $datPhong->loaiPhong;
            if ($lp) {
                // Áp dụng giá cố định: 150K/trẻ em/đêm, em bé miễn phí
                $childFeeRate = 150000;
                $infantFeeRate = 0;
                
                $phuPhiTreEm = ($datPhong->so_tre_em ?? 0) * $childFeeRate * $nights;
                $phuPhiEmBe = ($datPhong->so_em_be ?? 0) * $infantFeeRate * $nights;
            }
        }
        
        // Nếu đã có giá trong database và > 0, sử dụng giá đó (để đảm bảo tính nhất quán cho booking cũ)
        if (($datPhong->phu_phi_tre_em ?? 0) > 0) {
            $phuPhiTreEm = $datPhong->phu_phi_tre_em;
        }
        if (($datPhong->phu_phi_em_be ?? 0) > 0) {
            $phuPhiEmBe = $datPhong->phu_phi_em_be;
        }
        
        if (!empty($roomTypes) && $roomTypes->isNotEmpty()) {
            // Tính giá phòng với multiplier (ngày lễ/cuối tuần) và không có multiplier
            $checkIn = Carbon::parse($datPhong->ngay_nhan);
            $checkOut = Carbon::parse($datPhong->ngay_tra);
            
            $totalBasePriceWithMultiplier = 0; // Giá phòng CÓ multiplier (ngày lễ/cuối tuần)
            $totalBasePriceWithoutMultiplier = 0; // Giá phòng KHÔNG có multiplier
            $totalPreDiscountPrice = 0;
            $phuPhiNguoiLon = 0;
            
            $maxAdultsPerRoom = 2;
            $extraFeePercent = 0.2; // 20% cho người lớn
            
            foreach ($roomTypes as $roomType) {
                $soLuong = $roomType['so_luong'] ?? 1;
                $lp = \App\Models\LoaiPhong::find($roomType['loai_phong_id']);
                if ($lp) {
                    $pricePerNight = $lp->gia_khuyen_mai ?? $lp->gia_co_ban ?? 0;
                    
                    // Giá phòng KHÔNG có multiplier (giá cơ bản)
                    $baseWithoutMultiplier = $pricePerNight * $nights * $soLuong;
                    $totalBasePriceWithoutMultiplier += $baseWithoutMultiplier;
                    
                    // Giá phòng CÓ multiplier (ngày lễ/cuối tuần)
                    $baseWithMultiplier = \App\Services\BookingPriceCalculator::calculateRoomTypePriceByDateRange(
                        $lp,
                        $checkIn,
                        $checkOut,
                        $soLuong
                    );
                    $totalBasePriceWithMultiplier += $baseWithMultiplier;
                    
                    // Tính lại giá trước voucher từ gia_rieng
                    $storedTotalForType = $roomType['gia_rieng'] ?? $baseWithMultiplier;
                    $preDiscountTotalForType = $priceRatio > 0 ? ($storedTotalForType / $priceRatio) : $storedTotalForType;
                    $totalPreDiscountPrice += $preDiscountTotalForType;
                    
                    // Tính phụ phí thêm người lớn sẽ được tính sau khi có tổng capacity
                }
            }
            
            // Phụ phí ngày lễ/cuối tuần = giá phòng có multiplier - giá phòng không multiplier
            $phuPhiNgayLe = max(0, $totalBasePriceWithMultiplier - $totalBasePriceWithoutMultiplier);
            
            // Tính phụ phí thêm người lớn (từ tổng số người lớn và tổng capacity)
            $totalRooms = $roomTypes->sum(function($item) { return $item['so_luong'] ?? 1; });
            $totalCapacity = $totalRooms * $maxAdultsPerRoom;
            $totalAdults = $datPhong->so_nguoi ?? 0;
            $totalExtraGuests = max(0, $totalAdults - $totalCapacity);
            
            if ($totalExtraGuests > 0) {
                // Phân bổ số người lớn vượt cho từng loại phòng theo tỷ lệ giá trị
                // Tính tổng giá trị để phân bổ
                $totalValueForAllocation = 0;
                $roomTypeValues = [];
                
                foreach ($roomTypes as $roomType) {
                    $soLuong = $roomType['so_luong'] ?? 1;
                    $lp = \App\Models\LoaiPhong::find($roomType['loai_phong_id']);
                    if ($lp) {
                        // Giá trị = giá phòng có multiplier cho số lượng phòng này
                        $value = \App\Services\BookingPriceCalculator::calculateRoomTypePriceByDateRange(
                            $lp,
                            $checkIn,
                            $checkOut,
                            $soLuong
                        );
                        $roomTypeValues[$roomType['loai_phong_id']] = $value;
                        $totalValueForAllocation += $value;
                    }
                }
                
                // Phân bổ phụ phí người lớn theo tỷ lệ giá trị
                foreach ($roomTypes as $roomType) {
                    $lp = \App\Models\LoaiPhong::find($roomType['loai_phong_id']);
                    if ($lp && isset($roomTypeValues[$roomType['loai_phong_id']])) {
                        $ratio = $totalValueForAllocation > 0 ? ($roomTypeValues[$roomType['loai_phong_id']] / $totalValueForAllocation) : 0;
                        $extraGuestsForType = $totalExtraGuests * $ratio;
                        
                        if ($extraGuestsForType > 0) {
                            $extraFeeForType = \App\Services\BookingPriceCalculator::calculateExtraGuestSurcharge(
                                $lp,
                                $checkIn,
                                $checkOut,
                                $extraGuestsForType,
                                $extraFeePercent
                            );
                            $phuPhiNguoiLon += $extraFeeForType;
                        }
                    }
                }
            }
            
            // Giá phòng gốc = giá phòng không multiplier (để hiển thị)
            $giaPhongGoc = $totalBasePriceWithoutMultiplier;
            
            // Tính lại phụ phí người lớn từ totalPreDiscountPrice để đảm bảo khớp với giá đã lưu
            // totalPreDiscountPrice = giá phòng có multiplier + phụ phí người lớn + phụ phí trẻ em + phụ phí em bé (trước voucher)
            // Phụ phí người lớn = totalPreDiscountPrice - giá phòng có multiplier - phụ phí trẻ em - phụ phí em bé
            $phuPhiNguoiLon = max(0, $totalPreDiscountPrice - $totalBasePriceWithMultiplier - $phuPhiTreEm - $phuPhiEmBe);
        } else {
            // Fallback: Calculate using loaiPhong (legacy support)
            $soLuongPhong = $datPhong->so_luong_da_dat ?? 1;
            $lp = $datPhong->loaiPhong;
            if ($lp) {
                $pricePerNight = $lp->gia_khuyen_mai ?? $lp->gia_co_ban ?? 0;
                
                // Giá phòng KHÔNG có multiplier
                $giaPhongGoc = $pricePerNight * $nights * $soLuongPhong;
                
                // Giá phòng CÓ multiplier (ngày lễ/cuối tuần)
                $checkIn = Carbon::parse($datPhong->ngay_nhan);
                $checkOut = Carbon::parse($datPhong->ngay_tra);
                $giaPhongWithMultiplier = \App\Services\BookingPriceCalculator::calculateRoomTypePriceByDateRange(
                    $lp,
                    $checkIn,
                    $checkOut,
                    $soLuongPhong
                );
                
                // Phụ phí ngày lễ/cuối tuần
                $phuPhiNgayLe = max(0, $giaPhongWithMultiplier - $giaPhongGoc);
                
                // Tính phụ phí thêm người lớn
                $maxAdultsPerRoom = 2;
                $extraFeePercent = 0.2; // 20% cho người lớn
                $sumAdults = $datPhong->so_nguoi ?? ($maxAdultsPerRoom * $soLuongPhong);
                $capacity = $soLuongPhong * $maxAdultsPerRoom;
                $extraGuests = max(0, $sumAdults - $capacity);
                if ($extraGuests > 0) {
                    $phuPhiNguoiLon = \App\Services\BookingPriceCalculator::calculateExtraGuestSurcharge(
                        $lp,
                        $checkIn,
                        $checkOut,
                        $extraGuests,
                        $extraFeePercent
                    );
                } else {
                    // Nếu không có extra guests, tính từ tong_tien
                    $tongTienPhong = $datPhong->tong_tien ?? 0;
                    $servicesTotal = \App\Models\BookingService::where('dat_phong_id', $datPhong->id)
                        ->sum(\DB::raw('quantity * unit_price'));
                    $tongTienPhongTruDichVu = $tongTienPhong - $servicesTotal;
                    
                    // Nếu có voucher, tính lại giá trước voucher
                    if ($datPhong->voucher_id && $datPhong->voucher && $datPhong->voucher->gia_tri) {
                        $discountPercent = $datPhong->voucher->gia_tri;
                        $tongTienPhongTruDichVu = $tongTienPhongTruDichVu / (1 - $discountPercent / 100);
                    }
                    
                    $phuPhiNguoiLon = max(0, $tongTienPhongTruDichVu - $giaPhongGoc - $phuPhiNgayLe - $phuPhiTreEm - $phuPhiEmBe);
                }
            }
        }
        
        // Tổng tiền phòng = giá gốc + phụ phí ngày lễ/cuối tuần + phụ phí thêm người lớn + phụ phí trẻ em + phụ phí em bé
        $phuPhiNgayLe = $phuPhiNgayLe ?? 0; // Khởi tạo nếu chưa có (fallback case)
        $tongTienPhong = $giaPhongGoc + $phuPhiNgayLe + $phuPhiNguoiLon + $phuPhiTreEm + $phuPhiEmBe;

        // Calculate discount amount from voucher (only applies to room price, not services)
        // Note: tong_tien trong database đã bao gồm voucher discount rồi
        $discountAmount = 0;
        if ($datPhong->voucher_id && $datPhong->voucher) {
            $voucher = $datPhong->voucher;
            if ($voucher->gia_tri) {
                // Voucher only applies to room price (tongTienPhong), not services
                // Tính discount từ giá trước voucher để hiển thị
                $discountAmount = $tongTienPhong * ($voucher->gia_tri / 100);
            }
        }
        
        // Tính tổng tiền dịch vụ
        $servicesTotal = \App\Models\BookingService::where('dat_phong_id', $datPhong->id)
            ->sum(\DB::raw('quantity * unit_price'));
        
        // Tính tổng thanh toán cuối cùng
        // tong_tien trong database đã bao gồm voucher discount, nhưng chưa bao gồm dịch vụ
        // Nếu có dịch vụ, cần cộng thêm
        if ($datPhong->tong_tien) {
            // Sử dụng tong_tien từ database (đã bao gồm voucher) + dịch vụ
            $tongThanhToan = $datPhong->tong_tien + $servicesTotal;
        } else {
            // Fallback: Tính từ tongTienPhong - discount + dịch vụ
            $tongThanhToan = $tongTienPhong - $discountAmount + $servicesTotal;
        }

        // Find or create the invoice
        $invoice = Invoice::firstOrCreate(
            ['dat_phong_id' => $datPhong->id],
            [
                'tong_tien' => $datPhong->tong_tien,
                'trang_thai' => 'cho_thanh_toan',
            ]
        );

        // Tính thời gian còn lại trước khi booking bị auto hủy (5 phút kể từ lúc đặt)
        // Nếu booking đã quá 5 phút hoặc đã bị hủy/xác nhận thì remainingSeconds sẽ = 0
        $remainingSeconds = 0;
        if ($datPhong->ngay_dat) {
            $bookingTime = Carbon::parse($datPhong->ngay_dat);
            $expireTime = $bookingTime->copy()->addSeconds(300); // 5 phút = 300 giây
            $now = Carbon::now();

            if ($now->lessThan($expireTime)
                && $datPhong->trang_thai === 'cho_xac_nhan'
                && $invoice->trang_thai === 'cho_thanh_toan') {
                $remainingSeconds = $now->diffInSeconds($expireTime);
            }
        }

        return view('client.thanh-toan.show', compact('datPhong', 'invoice', 'giaPhongGoc', 'phuPhiNgayLe', 'phuPhiNguoiLon', 'phuPhiTreEm', 'phuPhiEmBe', 'tongTienPhong', 'discountAmount', 'nights', 'roomTypes', 'availableRooms', 'surchargeMap', 'remainingSeconds', 'servicesTotal', 'tongThanhToan'));
    }

    /**
     * Calculate number of nights between check-in and check-out dates
     *
     * @param \Carbon\Carbon|null $checkIn
     * @param \Carbon\Carbon|null $checkOut
     * @return int
     */
    private function calculateNights($checkIn, $checkOut): int
    {
        if (!$checkIn || !$checkOut) {
            return 1;
        }

        return max(1, $checkIn->diffInDays($checkOut));
    }

    public function store(Request $request, DatPhong $datPhong)
    {
        $request->validate([
            'phuong_thuc' => 'required|string|in:vnpay,sepay',
        ], [
            'phuong_thuc.required' => 'Vui lòng chọn phương thức thanh toán.',
            'phuong_thuc.in' => 'Phương thức thanh toán không hợp lệ. Chỉ hỗ trợ VNPay hoặc SePay.',
        ]);

        $invoice = $datPhong->invoice;

        // Update invoice with payment method
        $invoice->update([
            'phuong_thuc' => $request->phuong_thuc,
        ]);

        if ($request->phuong_thuc === 'vnpay') {
            return redirect()->route('client.vnpay_payment', ['datPhong' => $datPhong->id]);
        }

        if ($request->phuong_thuc === 'sepay') {
            return redirect()->route('client.sepay.qr', ['datPhong' => $datPhong->id]);
        }

        // Hiển thị thông báo đặt phòng thành công sau khi xác nhận thanh toán
        return redirect()
            ->route('client.dashboard')
            ->with('booking_success', true)
            ->with('booking_id', $datPhong->id)
            ->with('room_name', $datPhong->loaiPhong->ten_loai ?? 'N/A')
            ->with('success', 'Đặt phòng thành công! Mã đặt phòng #' . $datPhong->id . '. Vui lòng hoàn tất thanh toán để xác nhận đặt phòng.');
    }

    /**
     * Create VNPay payment URL and redirect to VNPay gateway
     *
     * @param Request $request
     * @param DatPhong $datPhong
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create_vnpay_payment(Request $request, DatPhong $datPhong)
    {
        // VNPay configuration
        $vnpayUrl = config('services.vnpay.url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
        $tmnCode = config('services.vnpay.tmn_code', env('VNPAY_TMN_CODE', 'XDZNQK7I'));
        $hashSecret = config('services.vnpay.hash_secret', env('VNPAY_HASH_SECRET', 'YJ3NE9YYQUWJ2L3N7BE6I1VD2FRDHGZ0'));

        // Build payment data
        $paymentData = [
            'vnp_Version' => '2.1.0',
            'vnp_TmnCode' => $tmnCode,
            'vnp_Amount' => $datPhong->tong_tien * 100,
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => Carbon::now()->format('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => $request->ip(),
            'vnp_Locale' => 'vn',
            'vnp_OrderInfo' => "Thanh toan don hang {$datPhong->id}",
            'vnp_OrderType' => 'billpayment',
            'vnp_ReturnUrl' => route('client.vnpay_return'),
            'vnp_TxnRef' => $datPhong->id,
            'vnp_BankCode' => 'NCB',
        ];

        // Sort data and build secure hash
        ksort($paymentData);
        $hashData = $this->buildVnpayHashData($paymentData);
        $secureHash = hash_hmac('sha512', $hashData, $hashSecret);

        // Build payment URL
        $queryString = http_build_query($paymentData);
        $paymentUrl = "{$vnpayUrl}?{$queryString}&vnp_SecureHash={$secureHash}";

        return redirect($paymentUrl);
    }

    /**
     * Handle VNPay payment return callback
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function vnpay_return(Request $request)
    {
        // Get VNPay hash secret
        $hashSecret = config('services.vnpay.hash_secret', env('VNPAY_HASH_SECRET', 'YJ3NE9YYQUWJ2L3N7BE6I1VD2FRDHGZ0'));

        // Validate response has secure hash
        if (!$request->has('vnp_SecureHash')) {
            return redirect()
                ->route('client.dashboard')
                ->with('error', 'Thanh toán không thành công hoặc có lỗi xảy ra (invalid response).');
        }

        // Verify signature
        if (!$this->verifyVnpaySignature($request, $hashSecret)) {
            return redirect()
                ->route('client.dashboard')
                ->with('error', 'Thanh toán không thành công hoặc có lỗi xảy ra.');
        }

        // Process payment result
        return $this->processVnpayPayment($request);
    }

    /**
     * Build hash data string for VNPay signature
     *
     * @param array $data
     * @return string
     */
    private function buildVnpayHashData(array $data): string
    {
        $hashParts = [];

        foreach ($data as $key => $value) {
            if (!empty($value)) {
                $hashParts[] = urlencode($key) . '=' . urlencode($value);
            }
        }

        return implode('&', $hashParts);
    }

    /**
     * Verify VNPay payment signature
     *
     * @param Request $request
     * @param string $hashSecret
     * @return bool
     */
    private function verifyVnpaySignature(Request $request, string $hashSecret): bool
    {
        $vnpayData = $request->except('vnp_SecureHash');
        $receivedHash = $request->input('vnp_SecureHash');

        ksort($vnpayData);
        $hashData = $this->buildVnpayHashData($vnpayData);
        $calculatedHash = hash_hmac('sha512', $hashData, $hashSecret);

        return hash_equals($calculatedHash, $receivedHash);
    }

    /**
     * Process VNPay payment result and update database
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    private function processVnpayPayment(Request $request)
    {
        $bookingId = $request->input('vnp_TxnRef');
        $responseCode = $request->input('vnp_ResponseCode');
        $amount = $request->input('vnp_Amount', 0) / 100;

        try {
            $datPhong = DatPhong::with(['invoice', 'phong', 'loaiPhong'])->findOrFail($bookingId);
            $invoice = $datPhong->invoice;

            // Bug #3 Fix: Check if invoice is already paid
            if ($invoice->trang_thai === 'da_thanh_toan') {
                return redirect()
                    ->route('client.dashboard')
                    ->with('success', "Đơn hàng #{$bookingId} đã được thanh toán trước đó.");
            }

            // Bug #2 Fix: Validate payment amount
            if ($responseCode === '00' && (float)$amount !== (float)$invoice->tong_tien) {
                Log::warning('VNPay amount mismatch detected', [
                    'booking_id' => $bookingId,
                    'invoice_amount' => $invoice->tong_tien,
                    'vnpay_amount' => $amount,
                ]);
                $this->handleFailedPayment($invoice, $amount, 'Amount mismatch');
                return redirect()
                    ->route('client.thanh-toan.show', $datPhong->id)
                    ->with('error', 'Số tiền thanh toán không khớp. Giao dịch đã bị hủy.');
            }


            return DB::transaction(function () use ($invoice, $datPhong, $responseCode, $amount) {
                // Case 1: Payment successful
                if ($responseCode === '00') {
                    $this->handleSuccessfulPayment($invoice, $amount);

                    return redirect()
                        ->route('client.dashboard')
                        ->with('success', "Thanh toán thành công cho đơn hàng #{$datPhong->id}");
                }

                // Case 2: Payment cancelled by user
                elseif ($responseCode === '24') {
                    $this->handleCancelledPayment($invoice, $amount);

                    return redirect()
                        ->route('client.thanh-toan.show', $datPhong->id)
                        ->with('warning', 'Bạn đã hủy giao dịch thanh toán. Vui lòng thử lại nếu muốn tiếp tục đặt phòng.');
                }

                // Case 3: Payment failed (other errors)
                else {
                    $this->handleFailedPayment($invoice, $amount);
                    $errorMessage = $this->getVnpayErrorMessage($responseCode);
                    return redirect()
                        ->route('client.thanh-toan.show', $datPhong->id)
                        ->with('error', $errorMessage);
                }
            });
        } catch (ModelNotFoundException $e) {
            Log::error('VNPay callback error: Booking not found', ['booking_id' => $bookingId]);
            return redirect()->route('client.dashboard')->with('error', 'Không tìm thấy đơn đặt phòng tương ứng.');
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('VNPay payment processing error', [
                'booking_id' => $bookingId,
                'response_code' => $responseCode,
                'amount' => $amount,
                'error_message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('client.dashboard')
                ->with('error', 'Thanh toán không thành công hoặc có lỗi xảy ra. Vui lòng liên hệ bộ phận hỗ trợ.');
        }
    }

    /**
     * Handle successful VNPay payment
     *
     * @param Invoice $invoice
     * @param float $amount
     * @return void
     */
    private function handleSuccessfulPayment(Invoice $invoice, float $amount): void
    {
        // Update invoice and booking status
        $invoice->update(['trang_thai' => 'da_thanh_toan']);

        // Bug #5 Fix: Update booking status
        $booking = $invoice->datPhong;
        if ($booking && $booking->trang_thai === 'cho_xac_nhan') {
            $booking->validateStatusTransition('da_xac_nhan');
            $booking->update(['trang_thai' => 'da_xac_nhan']);
        }

        // Create payment record
        ThanhToan::create([
            'hoa_don_id' => $invoice->id,
            'so_tien' => $amount,
            'ngay_thanh_toan' => Carbon::now(),
            'trang_thai' => 'success',
        ]);
    }

    /**
     * Handle cancelled VNPay payment
     *
     * @param Invoice $invoice
     * @param float $amount
     * @return void
     */
    private function handleCancelledPayment(Invoice $invoice, float $amount): void
    {
        ThanhToan::create([
            'hoa_don_id' => $invoice->id,
            'so_tien' => $amount,
            'ngay_thanh_toan' => Carbon::now(),
            'trang_thai' => 'cancelled',
        ]);
    }

    /**
     * Handle failed VNPay payment
     *
     * @param Invoice $invoice
     * @param float $amount
     * @param string|null $reason
     * @return void
     */
    private function handleFailedPayment(Invoice $invoice, float $amount, ?string $reason = null): void
    {
        ThanhToan::create([
            'hoa_don_id' => $invoice->id,
            'so_tien' => $amount,
            'ngay_thanh_toan' => Carbon::now(),
            'trang_thai' => 'fail',
            'ghi_chu' => $reason,
        ]);
    }

    /**
     * Get error message for VNPay response code
     *
     * @param string $responseCode
     * @return string
     */
    private function getVnpayErrorMessage(string $responseCode): string
    {
        $errorMessages = [
            '07' => 'Trừ tiền thành công. Giao dịch bị nghi ngờ (liên quan tới lừa đảo, giao dịch bất thường).',
            '09' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng chưa đăng ký dịch vụ InternetBanking tại ngân hàng.',
            '10' => 'Giao dịch không thành công do: Khách hàng xác thực thông tin thẻ/tài khoản không đúng quá 3 lần.',
            '11' => 'Giao dịch không thành công do: Đã hết hạn chờ thanh toán.',
            '12' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng bị khóa.',
            '13' => 'Giao dịch không thành công do Quý khách nhập sai mật khẩu xác thực giao dịch (OTP).',
            '51' => 'Giao dịch không thành công do: Tài khoản của quý khách không đủ số dư để thực hiện giao dịch.',
            '65' => 'Giao dịch không thành công do: Tài khoản của Quý khách đã vượt quá hạn mức giao dịch trong ngày.',
            '75' => 'Ngân hàng thanh toán đang bảo trì.',
            '79' => 'Giao dịch không thành công do: KH nhập sai mật khẩu thanh toán quá số lần quy định.',
            '99' => 'Các lỗi khác (lỗi còn lại, không có trong danh sách mã lỗi đã liệt kê).',
        ];

        return $errorMessages[$responseCode] ?? 'Giao dịch không thành công. Vui lòng thử lại sau.';
    }
}


