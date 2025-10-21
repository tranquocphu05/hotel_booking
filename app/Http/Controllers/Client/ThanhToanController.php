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


        return redirect()->route('client.dashboard')->with('success', 'Đã xác nhận phương thức thanh toán. Vui lòng hoàn tất thanh toán để hoàn tất đặt phòng.');
    }
}
