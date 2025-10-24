<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Phong;
use App\Models\DatPhong;
use App\Models\Voucher;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Show booking form for a specific room using route model binding
     */
    public function showForm(Phong $phong, Request $request)
    {
        // Accept query params from the client detail page (checkin, checkout, guests)
        $checkin = $request->query('checkin');
        $checkout = $request->query('checkout');
        $guests = $request->query('guests');

        return view('client.booking.booking', [
            'phong' => $phong,
            'checkin' => $checkin,
            'checkout' => $checkout,
            'guests' => $guests,
        ]);
    }

    /**
     * Handle booking submit and create a DatPhong record
     */
    public function submit(Request $request)
    {
        $data = $request->validate([
            'phong_id' => 'required|integer|exists:phong,id',
            // first_name is used as full name (ho_ten)
            'first_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'cccd' => 'required|string|max:20',
            'ngay_nhan' => 'nullable|date',
            'ngay_tra' => 'nullable|date',
            'so_nguoi' => 'nullable|integer',
        ], [
            'cccd.required' => 'Vui lòng nhập số CCCD/CMND',
            'cccd.max' => 'Số CCCD/CMND không được quá 20 ký tự',
        ]);

        $user = Auth::user();

        // Compute number of nights and total price
        $nights = 1;
        if (!empty($data['ngay_nhan']) && !empty($data['ngay_tra'])) {
            try {
                $start = new \DateTime($data['ngay_nhan']);
                $end = new \DateTime($data['ngay_tra']);
                $interval = $start->diff($end);
                $nights = max(1, (int)$interval->days);
            } catch (\Exception $e) {
                $nights = 1;
            }
        }

        $phong = Phong::find($data['phong_id']);
        $total = 0;
        if ($phong) {
            $giaMotDem = ($phong->co_khuyen_mai && !empty($phong->gia_khuyen_mai) && $phong->gia_khuyen_mai > 0)
                ? $phong->gia_khuyen_mai
                : $phong->gia;
            $total = ($giaMotDem ?? 0) * $nights;
        }

        $voucherId = null;
        // Apply voucher if provided
        if ($request->filled('voucherCode')) {
            $code = $request->input('voucherCode');
            $voucher = Voucher::where('ma_voucher', $code)
                ->where('trang_thai', 'con_han')
                ->where('so_luong', '>', 0)
                ->whereDate('ngay_ket_thuc', '>=', now())
                ->first();

            if ($voucher) {
                // Voucher.gia_tri is treated as percent (1-100) in admin forms
                $discountPercent = $voucher->gia_tri;
                if ($discountPercent > 0 && $discountPercent <= 100) {
                    $total = $total * (1 - $discountPercent / 100);
                    $voucherId = $voucher->id;
                    // decrement quantity
                    $voucher->decrement('so_luong');
                }
            }
        }

        $username = isset($data['first_name']) ? trim($data['first_name']) : null;
        if (!$username && $user) {
            $username = $user->ho_ten ?? null;
        }

        $datPhong = DatPhong::create([
            'nguoi_dung_id' => $user ? $user->id : null,
            'phong_id' => $data['phong_id'],
            'ngay_dat' => now(),
            'ngay_nhan' => $data['ngay_nhan'] ?? null,
            'ngay_tra' => $data['ngay_tra'] ?? null,
            'so_nguoi' => $data['so_nguoi'] ?? 1,
            'trang_thai' => 'cho_xac_nhan',
            'tong_tien' => $total,
            'voucher_id' => $voucherId,
            'username' => $username,
            'email' => $data['email'],
            'sdt' => $data['phone'] ?? null,
            'cccd' => $data['cccd'],
        ]);

        // Chỉ thông báo về trang thanh toán, chưa thông báo thành công
        return redirect()->route('client.thanh-toan.show', ['datPhong' => $datPhong->id]);
    }
}
