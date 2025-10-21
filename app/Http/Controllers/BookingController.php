<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Phong;
use App\Models\DatPhong;
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
        if ($phong && $phong->gia) {
            $total = $phong->gia * $nights;
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
            'username' => $data['first_name'],
            'email' => $data['email'],
            'sdt' => $data['phone'] ?? null,
            'cccd' => $data['cccd'],
        ]);

        // Chỉ thông báo về trang thanh toán, chưa thông báo thành công
        return redirect()->route('client.thanh-toan.show', ['datPhong' => $datPhong->id]);
    }
}
