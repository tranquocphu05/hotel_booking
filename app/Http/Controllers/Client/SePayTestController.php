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

/**
 * Test Controller for SePay webhook simulation (localhost only)
 * 
 * This controller simulates SePay webhook for testing purposes.
 * DELETE THIS IN PRODUCTION!
 */
class SePayTestController extends Controller
{
    /**
     * Show test page with booking list
     */
    public function index()
    {
        $pendingBookings = DatPhong::with(['invoice', 'loaiPhong', 'user'])
            ->whereHas('invoice', function ($query) {
                $query->where('trang_thai', 'cho_thanh_toan');
            })
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        return view('client.thanh-toan.sepay-test', compact('pendingBookings'));
    }

    /**
     * Simulate successful payment for a booking
     */
    public function simulatePayment(Request $request, DatPhong $datPhong)
    {
        $invoice = $datPhong->invoice;

        if (!$invoice) {
            return redirect()->back()->with('error', 'Không tìm thấy hóa đơn.');
        }

        if ($invoice->trang_thai === 'da_thanh_toan') {
            return redirect()->back()->with('warning', "Đơn #{$datPhong->id} đã được thanh toán trước đó.");
        }

        try {
            DB::transaction(function () use ($invoice, $datPhong) {
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
                    'so_tien' => $invoice->tong_tien,
                    'ngay_thanh_toan' => Carbon::now(),
                    'trang_thai' => 'success',
                    'ghi_chu' => 'SePay TEST - Simulated payment',
                ]);
            });

            Log::info('SePay TEST: Payment simulated', [
                'booking_id' => $datPhong->id,
                'amount' => $invoice->tong_tien,
            ]);

            return redirect()->back()->with('success', "✅ Đã simulate thanh toán thành công cho đơn #{$datPhong->id}!");

        } catch (\Exception $e) {
            Log::error('SePay TEST: Simulation failed', [
                'booking_id' => $datPhong->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint to check if payment was simulated (for QR page polling)
     */
    public function checkPaymentStatus(DatPhong $datPhong)
    {
        $invoice = $datPhong->invoice;

        if (!$invoice) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invoice not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'is_paid' => $invoice->trang_thai === 'da_thanh_toan',
            'invoice_status' => $invoice->trang_thai,
            'booking_status' => $datPhong->trang_thai,
        ]);
    }
}
