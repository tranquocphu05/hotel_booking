<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Phong;
use App\Models\DatPhong;
use App\Models\Voucher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            'ngay_nhan' => 'required|date|after_or_equal:today',
            'ngay_tra' => 'required|date|after:ngay_nhan',
            'so_nguoi' => 'nullable|integer',
        ], [
            'cccd.required' => 'Vui lòng nhập số CCCD/CMND',
            'cccd.max' => 'Số CCCD/CMND không được quá 20 ký tự',
            'ngay_nhan.required' => 'Vui lòng chọn ngày nhận phòng.',
            'ngay_nhan.after_or_equal' => 'Ngày nhận phòng phải là hôm nay hoặc một ngày trong tương lai.',
            'ngay_tra.required' => 'Vui lòng chọn ngày trả phòng.',
            'ngay_tra.after' => 'Ngày trả phòng phải sau ngày nhận phòng.',
        ]);

        // Check for overlapping bookings
        $isBooked = DatPhong::where('phong_id', $data['phong_id'])
            ->where(function ($query) use ($data) {
                $query->where(function ($q) use ($data) {
                    $q->where('ngay_nhan', '<', $data['ngay_tra'])
                      ->where('ngay_tra', '>', $data['ngay_nhan']);
                });
            })
            // Bug #6 Fix: Also exclude bookings that are pending payment but not yet expired
            ->whereNotIn('trang_thai', ['da_huy', 'tu_choi', 'thanh_toan_that_bai'])
            ->exists();

        if ($isBooked) {
            return back()->withErrors(['error' => 'Phòng này đã được đặt trong khoảng thời gian bạn chọn. Vui lòng chọn ngày khác.'])->withInput();
        }

        $user = Auth::user();

        // Calculate number of nights
        $nights = $this->calculateNights($data['ngay_nhan'], $data['ngay_tra']);

        // Get room and calculate base price
        $phong = Phong::findOrFail($data['phong_id']);
        $pricePerNight = $phong->gia_hien_thi ?? $phong->gia ?? 0;
        $totalPrice = $pricePerNight * $nights;

        // Apply voucher and create booking within transaction
        $datPhong = DB::transaction(function () use ($request, $data, $user, $phong, $totalPrice) {
            $voucherId = null;
            $finalPrice = $totalPrice;

            // Apply voucher if provided
            if ($request->filled('voucherCode')) {
                // Bug #1 Fix: Lock the voucher row to prevent race conditions
                $voucher = Voucher::where('ma_voucher', $request->input('voucherCode'))
                    ->where('trang_thai', 'con_han')
                    ->where('so_luong', '>', 0)
                    ->whereDate('ngay_ket_thuc', '>=', now())
                    ->lockForUpdate()
                    ->first();

                if ($voucher) {
                    $discountPercent = $voucher->gia_tri;
                    if ($discountPercent > 0 && $discountPercent <= 100) {
                        $finalPrice = $totalPrice * (1 - $discountPercent / 100);
                        $voucherId = $voucher->id;
                        $voucher->decrement('so_luong');
                    }
                } else {
                    // Throw a validation exception if the voucher is invalid or already used up
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'voucherCode' => 'Mã voucher không hợp lệ hoặc đã hết lượt sử dụng.',
                    ]);
                }
            }

            // Determine username: prioritize form input, fallback to authenticated user
            $username = trim($data['first_name']) ?: ($user->ho_ten ?? null);

            // Create booking record
            return DatPhong::create([
                'nguoi_dung_id' => $user?->id,
                'phong_id' => $data['phong_id'],
                'ngay_dat' => now(),
                'ngay_nhan' => $data['ngay_nhan'] ?? null,
                'ngay_tra' => $data['ngay_tra'] ?? null,
                'so_nguoi' => $data['so_nguoi'] ?? 1,
                'trang_thai' => 'cho_xac_nhan',
                'tong_tien' => $finalPrice,
                'voucher_id' => $voucherId,
                'username' => $username,
                'email' => $data['email'],
                'sdt' => $data['phone'] ?? null,
                'cccd' => $data['cccd'],
            ]);
        });

        // Chỉ thông báo về trang thanh toán, chưa thông báo thành công
        return redirect()->route('client.thanh-toan.show', ['datPhong' => $datPhong->id]);
    }

    /**
     * Calculate number of nights between check-in and check-out dates
     *
     * @param string $checkIn
     * @param string $checkOut
     * @return int
     */
    private function calculateNights(string $checkIn, string $checkOut): int
    {
        try {
            $start = Carbon::parse($checkIn);
            $end = Carbon::parse($checkOut);
            return max(1, $start->diffInDays($end));
        } catch (\Exception $e) {
            // Log error or handle it, but for now, a default is safer
            return 1;
        }
    }

    /**
     * Find a valid voucher by code
     *
     * @param string $code
     * @return \App\Models\Voucher|null
     */
    private function findValidVoucher(string $code): ?Voucher
    {
        return Voucher::where('ma_voucher', $code)
            ->where('trang_thai', 'con_han')
            ->where('so_luong', '>', 0)
            ->whereDate('ngay_ket_thuc', '>=', now())
            ->first();
    }
}
