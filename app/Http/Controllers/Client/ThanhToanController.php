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
            $totalRooms = $roomTypes->sum('so_luong') ?: ($datPhong->so_luong_da_dat ?? 1);
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
                        return in_array($room->id, $assignedPhongIds);
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
        // originalPrice: tổng theo từng loại phòng đã lưu (gia_rieng pivot - đã bao gồm phụ phí)
        // basePrice: tổng giá "chuẩn" theo LoaiPhong (chưa tính phụ phí)
        // surchargeMap: phụ phí thêm khách cho từng loai_phong_id
        $originalPrice = 0;
        $basePrice = 0;
        $surchargeMap = [];
        if (!empty($roomTypes)) {
            foreach ($roomTypes as $roomType) {
                $soLuong = $roomType['so_luong'] ?? 1;
                $lp = \App\Models\LoaiPhong::find($roomType['loai_phong_id']);
                if ($lp) {
                    // Giá chuẩn 1 đêm của loại phòng (không phụ phí)
                    $pricePerNight = $lp->gia_khuyen_mai ?? $lp->gia_co_ban ?? 0;
                    $baseForType = $pricePerNight * $nights * $soLuong;

                    // Tổng tiền đã lưu cho loại phòng này (trước voucher, đã gồm phụ phí)
                    $storedTotalForType = $roomType['gia_rieng'] ?? $baseForType;

                    // Phụ phí = chênh lệch giữa giá lưu và giá chuẩn
                    $surchargeForType = max(0, $storedTotalForType - $baseForType);

                    // Cộng dồn
                    $originalPrice += $storedTotalForType;
                    $basePrice += $baseForType;
                    $surchargeMap[$roomType['loai_phong_id']] = $surchargeForType;
                }
            }
        } else {
            // Fallback: Calculate using loaiPhong (legacy support)
            $soLuongPhong = $datPhong->so_luong_da_dat ?? 1;
            $pricePerNight = $datPhong->loaiPhong->gia_khuyen_mai ?? $datPhong->loaiPhong->gia_co_ban ?? 0;
            $originalPrice = $pricePerNight * $nights * $soLuongPhong;
            $basePrice = $originalPrice;
        }

        // Calculate discount amount from voucher (only applies to room price, not services)
        $discountAmount = 0;
        if ($datPhong->voucher_id && $datPhong->voucher) {
            $voucher = $datPhong->voucher;
            if ($voucher->gia_tri) {
                // Voucher only applies to room price (originalPrice), not services
                $discountAmount = $originalPrice * ($voucher->gia_tri / 100);
            }
        }
        $surchargeAmount = max(0, $originalPrice - $basePrice);

        // Find or create the invoice
        $invoice = Invoice::firstOrCreate(
            ['dat_phong_id' => $datPhong->id],
            [
                'tong_tien' => $datPhong->tong_tien,
                'trang_thai' => 'cho_thanh_toan',
            ]
        );

        return view('client.thanh-toan.show', compact('datPhong', 'invoice', 'originalPrice', 'discountAmount', 'surchargeAmount', 'nights', 'roomTypes', 'availableRooms', 'surchargeMap'));
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
            'phuong_thuc' => 'required|string|in:vnpay',
        ], [
            'phuong_thuc.required' => 'Vui lòng chọn phương thức thanh toán.',
            'phuong_thuc.in' => 'Phương thức thanh toán không hợp lệ. Chỉ hỗ trợ thanh toán qua VNPay.',
        ]);

        $invoice = $datPhong->invoice;

        // Update invoice with payment method
        $invoice->update([
            'phuong_thuc' => $request->phuong_thuc,
        ]);

        if ($request->phuong_thuc === 'vnpay') {
            return redirect()->route('client.vnpay_payment', ['datPhong' => $datPhong->id]);
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

