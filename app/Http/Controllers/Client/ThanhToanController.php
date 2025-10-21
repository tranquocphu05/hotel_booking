<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\DatPhong;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ThanhToanController extends Controller
{
    public function show(DatPhong $datPhong)
    {


        // Find or create the invoice
        $invoice = Invoice::firstOrCreate(
            ['dat_phong_id' => $datPhong->id],
            [
                'tong_tien' => $datPhong->tong_tien,
                'trang_thai' => 'cho_thanh_toan',
            ]
        );

        return view('client.thanh-toan.show', compact('datPhong', 'invoice'));
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

        // Hiển thị thông báo đặt phòng thành công sau khi xác nhận thanh toán
        return redirect()
            ->route('client.dashboard')
            ->with('booking_success', true)
            ->with('booking_id', $datPhong->id)
            ->with('room_name', $datPhong->phong->ten_phong ?? 'N/A')
            ->with('success', 'Đặt phòng thành công! Mã đặt phòng #' . $datPhong->id . '. Vui lòng hoàn tất thanh toán để xác nhận đặt phòng.');
    }
}
