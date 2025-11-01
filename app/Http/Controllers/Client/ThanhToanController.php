<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\DatPhong;
use App\Models\Invoice;
use App\Models\ThanhToan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ThanhToanController extends Controller
{
    public function show(DatPhong $datPhong)
    {
        // Eager load relationships for efficiency
        $datPhong->load('voucher', 'phong.loaiPhong', 'user');

        // Calculate number of nights
        $nights = $this->calculateNights($datPhong->ngay_nhan, $datPhong->ngay_tra);

        // Calculate original price using model accessor
        $pricePerNight = $datPhong->phong->gia_hien_thi ?? $datPhong->phong->gia ?? 0;
        $originalPrice = $pricePerNight * $nights;

        // Calculate discount amount
        $discountAmount = $originalPrice - $datPhong->tong_tien;

        // Find or create the invoice
        $invoice = Invoice::firstOrCreate(
            ['dat_phong_id' => $datPhong->id],
            [
                'tong_tien' => $datPhong->tong_tien,
                'trang_thai' => 'cho_thanh_toan',
            ]
        );

        return view('client.thanh-toan.show', compact('datPhong', 'invoice', 'originalPrice', 'discountAmount', 'nights'));
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
            'phuong_thuc' => 'required|string|in:tien_mat,chuyen_khoan,momo,vnpay',
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
            ->with('room_name', $datPhong->phong->ten_phong ?? 'N/A')
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
            $datPhong = DatPhong::with('invoice')->findOrFail($bookingId);
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
                    ->route('client.thanh-toan.show', $bookingId)
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
        $invoice->datPhong()->update(['trang_thai' => 'da_xac_nhan']);

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
        // Create cancelled payment record
        ThanhToan::create([
            'hoa_don_id' => $invoice->id,
            'so_tien' => $amount,
            'ngay_thanh_toan' => Carbon::now(),
            'trang_thai' => 'fail',
            'ghi_chu' => 'User cancelled transaction (code 24)',
        ]);
    }

    /**
     * Handle failed VNPay payment
     *
     * @param Invoice $invoice
     * @param float $amount
     * @return void
     */
    private function handleFailedPayment(Invoice $invoice, float $amount, string $reason = null): void
    {
        // Create failed payment record
        ThanhToan::create([
            'hoa_don_id' => $invoice->id,
            'so_tien' => $amount,
            'ngay_thanh_toan' => Carbon::now(),
            'trang_thai' => 'fail',
            'ghi_chu' => $reason,
        ]);
    }
    private function getVnpayErrorMessage(string $responseCode): string
    {
        return match ($responseCode) {
            '00' => 'Thanh toán thành công.',
            '07' => 'Trừ tiền thành công.',
            '09' => 'Giao dịch đang xử lý.',
            '10' => 'Giao dịch được khởi tạo thành công.',
            '24' => 'Giao dịch bị hủy.',
            '51' => 'Giao dịch bị từ chối.',
            '65' => 'Giao dịch bị từ chối.',
            '75' => 'Giao dịch bị từ chối.',
            '79' => 'Giao dịch bị từ chối.',
            '85' => 'Giao dịch bị từ chối.',
            '99' => 'Giao dịch bị từ chối.',
            default => 'Giao dịch thất bại.',
        };
    }
}
