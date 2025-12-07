<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\DatPhong;
use App\Models\Invoice;
use App\Models\ThanhToan;
use App\Services\SePayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SePayController extends Controller
{
    protected SePayService $sePayService;

    public function __construct(SePayService $sePayService)
    {
        $this->sePayService = $sePayService;
    }

    /**
     * Show SePay QR payment page
     */
    public function showQR(DatPhong $datPhong)
    {
        // Authorization check
        if (\Illuminate\Support\Facades\Auth::check() && $datPhong->nguoi_dung_id && $datPhong->nguoi_dung_id !== \Illuminate\Support\Facades\Auth::id()) {
            abort(403, 'Bạn không có quyền xem đơn đặt phòng này.');
        }

        // Get or create invoice
        $invoice = Invoice::firstOrCreate(
            ['dat_phong_id' => $datPhong->id],
            [
                'tong_tien' => $datPhong->tong_tien,
                'trang_thai' => 'cho_thanh_toan',
            ]
        );

        // Check if already paid
        if ($invoice->trang_thai === 'da_thanh_toan') {
            return redirect()
                ->route('client.dashboard')
                ->with('success', "Đơn hàng #{$datPhong->id} đã được thanh toán trước đó.");
        }

        // Generate QR code data
        $qrData = $this->sePayService->generateQRCode(
            $datPhong->id,
            $datPhong->tong_tien
        );

        return view('client.thanh-toan.sepay-qr', compact('datPhong', 'invoice', 'qrData'));
    }

    /**
     * Check payment status via AJAX
     */
    public function checkStatus(DatPhong $datPhong)
    {
        $invoice = $datPhong->invoice;

        if (!$invoice) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hóa đơn không tồn tại',
            ], 404);
        }

        $isPaid = $invoice->trang_thai === 'da_thanh_toan';

        return response()->json([
            'status' => 'success',
            'is_paid' => $isPaid,
            'invoice_status' => $invoice->trang_thai,
            'booking_status' => $datPhong->trang_thai,
            'message' => $isPaid ? 'Thanh toán thành công!' : 'Đang chờ thanh toán...',
        ]);
    }

    /**
     * Handle SePay webhook callback
     * This endpoint will be called by SePay when a transaction occurs
     */
    public function webhook(Request $request)
    {
        // Handle GET request (for SePay ping test)
        if ($request->isMethod('get')) {
            return response()->json([
                'success' => true,
                'message' => 'SePay webhook endpoint is active',
                'timestamp' => now()->toIso8601String(),
            ]);
        }

        Log::info('SePay webhook received', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
        ]);

        // Verify webhook token
        $authHeader = $request->header('Authorization', '');
        
        if (!$this->sePayService->verifyWebhook($authHeader)) {
            Log::warning('SePay webhook verification failed', [
                'received_auth' => $authHeader,
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Parse webhook data
        $webhookData = $this->sePayService->parseWebhookData($request->all());

        Log::info('SePay webhook parsed', $webhookData);

        // Only process incoming transfers (money in)
        if ($webhookData['transfer_type'] !== 'in') {
            Log::info('SePay: Ignoring outgoing transfer', ['id' => $webhookData['id']]);
            return response()->json(['success' => true, 'message' => 'Ignored outgoing transfer']);
        }

        // Extract booking ID from content
        $content = $webhookData['content'] ?? $webhookData['description'] ?? '';
        $bookingId = $this->sePayService->extractBookingId($content);

        if (!$bookingId) {
            Log::warning('SePay: Could not extract booking ID from content', [
                'content' => $content,
                'transaction_id' => $webhookData['id'],
            ]);
            return response()->json(['success' => true, 'message' => 'No booking ID found']);
        }

        // Find booking
        $datPhong = DatPhong::with('invoice')->find($bookingId);

        if (!$datPhong) {
            Log::warning('SePay: Booking not found', ['booking_id' => $bookingId]);
            return response()->json(['success' => true, 'message' => 'Booking not found']);
        }

        $invoice = $datPhong->invoice;

        if (!$invoice) {
            // Create invoice if not exists
            $invoice = Invoice::create([
                'dat_phong_id' => $datPhong->id,
                'tong_tien' => $datPhong->tong_tien,
                'trang_thai' => 'cho_thanh_toan',
            ]);
        }

        // Check if already paid
        if ($invoice->trang_thai === 'da_thanh_toan') {
            Log::info('SePay: Invoice already paid', [
                'booking_id' => $bookingId,
                'transaction_id' => $webhookData['id'],
            ]);
            return response()->json(['success' => true, 'message' => 'Already paid']);
        }

        // Validate amount
        $receivedAmount = (float) $webhookData['transfer_amount'];
        $expectedAmount = (float) $invoice->tong_tien;

        // Allow small tolerance for amount (in case of rounding)
        if ($receivedAmount < $expectedAmount * 0.99) {
            Log::warning('SePay: Insufficient payment amount', [
                'booking_id' => $bookingId,
                'expected' => $expectedAmount,
                'received' => $receivedAmount,
                'transaction_id' => $webhookData['id'],
            ]);
            
            // Create partial payment record
            ThanhToan::create([
                'hoa_don_id' => $invoice->id,
                'so_tien' => $receivedAmount,
                'ngay_thanh_toan' => Carbon::now(),
                'trang_thai' => 'partial',
                'ghi_chu' => 'SePay - Thanh toán thiếu. TxnID: ' . $webhookData['id'],
            ]);

            return response()->json(['success' => true, 'message' => 'Partial payment recorded']);
        }

        // Process successful payment
        try {
            DB::transaction(function () use ($invoice, $datPhong, $webhookData, $receivedAmount) {
                // Update invoice status
                $invoice->update([
                    'trang_thai' => 'da_thanh_toan',
                    'phuong_thuc' => 'sepay',
                ]);

                // Update booking status
                if ($datPhong->trang_thai === 'cho_xac_nhan') {
                    $datPhong->validateStatusTransition('da_xac_nhan');
                    $datPhong->update(['trang_thai' => 'da_xac_nhan']);
                }

                // Create payment record
                ThanhToan::create([
                    'hoa_don_id' => $invoice->id,
                    'so_tien' => $receivedAmount,
                    'ngay_thanh_toan' => Carbon::now(),
                    'trang_thai' => 'success',
                    'ghi_chu' => 'SePay - TxnID: ' . ($webhookData['id'] ?? 'N/A') . ' - Ref: ' . ($webhookData['reference_code'] ?? 'N/A'),
                ]);
            });

            Log::info('SePay: Payment successful', [
                'booking_id' => $bookingId,
                'amount' => $receivedAmount,
                'transaction_id' => $webhookData['id'],
            ]);

            return response()->json(['success' => true, 'message' => 'Payment processed successfully']);

        } catch (\Exception $e) {
            Log::error('SePay: Error processing payment', [
                'booking_id' => $bookingId,
                'error' => $e->getMessage(),
                'transaction_id' => $webhookData['id'],
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Redirect to SePay payment (alternative flow)
     */
    public function redirect(DatPhong $datPhong)
    {
        return redirect()->route('client.sepay.qr', $datPhong);
    }
}
