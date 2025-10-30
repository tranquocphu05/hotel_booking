<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\DatPhong;
use App\Models\Invoice;
use App\Models\ThanhToan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\InvoicePaid;
use App\Mail\AdminBookingEvent;

class ThanhToanController extends Controller
{
    public function show(DatPhong $datPhong)
    {
        // Eager load relationships for efficiency
        $datPhong->load('voucher', 'phong.loaiPhong', 'user');

        // Calculate original price and discount
        $nights = 1;
        if ($datPhong->ngay_nhan && $datPhong->ngay_tra) {
            $nights = max(1, $datPhong->ngay_nhan->diffInDays($datPhong->ngay_tra));
        }

        // Determine the correct base price per night (promo or standard)
        $phong = $datPhong->phong;
        $giaMotDem = ($phong->co_khuyen_mai && !empty($phong->gia_khuyen_mai) && $phong->gia_khuyen_mai > 0)
            ? $phong->gia_khuyen_mai
            : $phong->gia;
        
        // This is the total before voucher, but after potential room promotion
        $giaGoc = ($giaMotDem ?? 0) * $nights;
        $giamGia = $giaGoc - $datPhong->tong_tien;

        // Find or create the invoice
        $invoice = Invoice::firstOrCreate(
            ['dat_phong_id' => $datPhong->id],
            [
                'tong_tien' => $datPhong->tong_tien,
                'trang_thai' => 'cho_thanh_toan',
            ]
        );

        return view('client.thanh-toan.show', compact('datPhong', 'invoice', 'giaGoc', 'giamGia', 'nights'));
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

    public function create_vnpay_payment(Request $request, DatPhong $datPhong)
    {
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = route('client.vnpay_return');
        $vnp_TmnCode = env('VNPAY_TMN_CODE', 'XDZNQK7I'); // Website ID
        $vnp_HashSecret = env('VNPAY_HASH_SECRET', 'YJ3NE9YYQUWJ2L3N7BE6I1VD2FRDHGZ0'); // Secret Key

        $vnp_TxnRef = $datPhong->id; // Order ID
        $vnp_OrderInfo = "Thanh toan don hang {$datPhong->id}";
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $datPhong->tong_tien * 100;
        $vnp_Locale = 'vn';
        $vnp_BankCode = 'NCB';
        $vnp_IpAddr = $request->ip();

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        
        return redirect($vnp_Url);
    }

    public function vnpay_return(Request $request)
    {
        $vnp_HashSecret = env('VNPAY_HASH_SECRET', 'YJ3NE9YYQUWJ2L3N7BE6I1VD2FRDHGZ0');
        $inputData = $request->all();
        
        if (!isset($inputData['vnp_SecureHash'])) {
            return redirect()->route('client.dashboard')->with('error', 'Thanh toán không thành công hoặc có lỗi xảy ra (invalid response).');
        }

        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $hashData = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }
        
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash == $vnp_SecureHash) {
            $datPhongId = $inputData['vnp_TxnRef'];
            $datPhong = DatPhong::find($datPhongId);

            if ($datPhong && $datPhong->invoice) {
                $invoice = $datPhong->invoice;
                
                if ($request->vnp_ResponseCode == '00') {
                    // Update invoice status
                    if ($invoice->trang_thai !== 'da_thanh_toan') {
                        $invoice->update(['trang_thai' => 'da_thanh_toan']);
                    }

                    // Create payment record
                    ThanhToan::create([
                        'hoa_don_id' => $invoice->id,
                        'so_tien' => $inputData['vnp_Amount'] / 100,
                        'ngay_thanh_toan' => Carbon::now(),
                        'trang_thai' => 'success',
                    ]);

                    // Gửi email khách hàng xác nhận thanh toán thành công
                    try {
                        if ($datPhong->email) {
                            Mail::to($datPhong->email)->send(new InvoicePaid($datPhong->load(['phong'])));
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Send customer paid mail (vnpay) failed: '.$e->getMessage());
                    }

                    // Gửi email admin thông báo đã thanh toán
                    try {
                        $adminEmails = \App\Models\User::where('vai_tro', 'admin')
                            ->where('trang_thai', 'hoat_dong')
                            ->pluck('email')
                            ->filter()
                            ->all();
                        if (!empty($adminEmails)) {
                            Mail::to($adminEmails)->send(new AdminBookingEvent($datPhong->load(['phong.loaiPhong']), 'paid'));
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Send admin paid mail (vnpay) failed: '.$e->getMessage());
                    }

                    return redirect()->route('client.dashboard')->with('success', 'Thanh toán thành công cho đơn hàng #' . $datPhongId);
                } else {
                    // Payment failed
                    ThanhToan::create([
                        'hoa_don_id' => $invoice->id,
                        'so_tien' => $inputData['vnp_Amount'] / 100,
                        'ngay_thanh_toan' => Carbon::now(),
                        'trang_thai' => 'fail',
                    ]);
                }
            }
        }
        
        return redirect()->route('client.dashboard')->with('error', 'Thanh toán không thành công hoặc có lỗi xảy ra.');
    }
}
