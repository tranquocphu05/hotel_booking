<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoaiPhong;
use App\Models\DatPhong;
use App\Models\Voucher;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\AdminBookingEvent;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * Show booking form for a room type using route model binding
     */
    public function showForm($loaiPhongId, Request $request)
    {
        // Get room type (selected room type)
        $loaiPhong = LoaiPhong::findOrFail($loaiPhongId);

        // Get all available room types for selection
        $allLoaiPhongs = LoaiPhong::where('trang_thai', 'hoat_dong')
            ->where('so_luong_trong', '>', 0)
            ->orderBy('ten_loai')
            ->get();

        // Accept query params from the client detail page (checkin, checkout, guests)
        $checkin = $request->query('checkin');
        $checkout = $request->query('checkout');
        $guests = $request->query('guests');

        return view('client.booking.booking', [
            'loaiPhong' => $loaiPhong, // Selected room type (default)
            'allLoaiPhongs' => $allLoaiPhongs, // All available room types
            'checkin' => $checkin,
            'checkout' => $checkout,
            'guests' => $guests,
        ]);
    }

    /**
     * Handle booking submit and create a DatPhong record
     * NEW: Book by room type, auto-assign available room
     */
    public function submit(Request $request)
    {
        $data = $request->validate([
            'rooms' => 'required|array|min:1',
            'rooms.*.loai_phong_id' => 'required|integer|exists:loai_phong,id',
            'rooms.*.so_luong' => 'required|integer|min:1|max:100',
            'first_name' => 'required|string|max:255|min:2',
            'email' => 'required|email:rfc,dns|max:255',
            'phone' => 'nullable|string|regex:/^0[0-9]{9}$/|max:20',
            'cccd' => 'required|string|regex:/^[0-9]{12}$/|size:12',
            'ngay_nhan' => 'required|date|after_or_equal:today',
            'ngay_tra' => 'required|date|after:ngay_nhan',
            'so_nguoi' => 'nullable|integer|min:1|max:100',
            'voucherCode' => 'nullable|string|max:50',
            'discountValue' => 'nullable|numeric|min:0',
        ], [
            // Rooms validation messages
            'rooms.required' => 'Vui lòng chọn ít nhất một loại phòng.',
            'rooms.min' => 'Vui lòng chọn ít nhất một loại phòng.',
            'rooms.array' => 'Dữ liệu phòng không hợp lệ.',
            'rooms.*.loai_phong_id.required' => 'Vui lòng chọn loại phòng.',
            'rooms.*.loai_phong_id.integer' => 'ID loại phòng không hợp lệ.',
            'rooms.*.loai_phong_id.exists' => 'Loại phòng không tồn tại trong hệ thống.',
            'rooms.*.so_luong.required' => 'Vui lòng nhập số lượng phòng.',
            'rooms.*.so_luong.integer' => 'Số lượng phòng phải là số nguyên.',
            'rooms.*.so_luong.min' => 'Số lượng phòng phải lớn hơn 0.',
            'rooms.*.so_luong.max' => 'Số lượng phòng không được vượt quá 100.',

            // Personal information validation messages
            'first_name.required' => 'Vui lòng nhập họ và tên.',
            'first_name.string' => 'Họ và tên phải là chuỗi ký tự.',
            'first_name.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'first_name.max' => 'Họ và tên không được vượt quá 255 ký tự.',

            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'email.max' => 'Địa chỉ email không được vượt quá 255 ký tự.',

            'phone.string' => 'Số điện thoại phải là chuỗi ký tự.',
            'phone.regex' => 'Số điện thoại không đúng định dạng (phải bắt đầu bằng 0 và có 10 chữ số).',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',

            'cccd.required' => 'Vui lòng nhập số CCCD/CMND.',
            'cccd.string' => 'CCCD/CMND phải là chuỗi ký tự.',
            'cccd.regex' => 'CCCD/CMND phải gồm 12 chữ số.',
            'cccd.size' => 'CCCD/CMND phải có đúng 12 chữ số.',

            // Date validation messages
            'ngay_nhan.required' => 'Vui lòng chọn ngày nhận phòng.',
            'ngay_nhan.date' => 'Ngày nhận phòng không hợp lệ.',
            'ngay_nhan.after_or_equal' => 'Ngày nhận phòng phải là hôm nay hoặc một ngày trong tương lai.',

            'ngay_tra.required' => 'Vui lòng chọn ngày trả phòng.',
            'ngay_tra.date' => 'Ngày trả phòng không hợp lệ.',
            'ngay_tra.after' => 'Ngày trả phòng phải sau ngày nhận phòng.',

            // Number of guests validation messages
            'so_nguoi.integer' => 'Số người phải là số nguyên.',
            'so_nguoi.min' => 'Số người phải lớn hơn 0.',
            'so_nguoi.max' => 'Số người không được vượt quá 100.',

            // Voucher validation messages
            'voucherCode.string' => 'Mã voucher phải là chuỗi ký tự.',
            'voucherCode.max' => 'Mã voucher không được vượt quá 50 ký tự.',
            'discountValue.numeric' => 'Giá trị giảm giá phải là số.',
            'discountValue.min' => 'Giá trị giảm giá không được nhỏ hơn 0.',
        ]);

        $user = Auth::user();
        $nights = $this->calculateNights($data['ngay_nhan'], $data['ngay_tra']);

        // Check for duplicate room types
        $roomTypeIds = array_column($data['rooms'], 'loai_phong_id');
        if (count($roomTypeIds) !== count(array_unique($roomTypeIds))) {
            return back()->withErrors([
                'error' => 'Bạn đã chọn cùng một loại phòng nhiều lần. Vui lòng chọn số lượng phù hợp cho từng loại phòng.'
            ])->withInput();
        }

        // Validate each room type and check availability
        $totalPrice = 0;
        $roomDetails = [];

        foreach ($data['rooms'] as $room) {
            $loaiPhong = LoaiPhong::findOrFail($room['loai_phong_id']);

            // Check if room type is active
            if ($loaiPhong->trang_thai !== 'hoat_dong') {
                return back()->withErrors([
                    'error' => "Loại phòng '{$loaiPhong->ten_loai}' hiện không khả dụng."
                ])->withInput();
            }

            // Check if room type has available rooms (before transaction)
            if ($loaiPhong->so_luong_trong < $room['so_luong']) {
                return back()->withErrors([
                    'error' => "Loại phòng '{$loaiPhong->ten_loai}' chỉ còn {$loaiPhong->so_luong_trong} phòng trống. Bạn đã chọn {$room['so_luong']} phòng."
                ])->withInput();
            }

            // Check if room type has price
            $pricePerNight = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban ?? 0;
            if ($pricePerNight <= 0) {
                return back()->withErrors([
                    'error' => "Loại phòng '{$loaiPhong->ten_loai}' chưa có giá. Vui lòng liên hệ quản trị viên."
                ])->withInput();
            }

            // Use promotional price if available, otherwise use base price
            $roomTotal = $pricePerNight * $nights * $room['so_luong'];
            $totalPrice += $roomTotal;

            $roomDetails[] = [
                'loai_phong_id' => $loaiPhong->id,
                'loai_phong' => $loaiPhong,
                'so_luong' => $room['so_luong'],
                'price' => $roomTotal,
            ];
        }

        // Apply voucher and create bookings within transaction
        // Use lockForUpdate to prevent race conditions when checking availability
        $bookings = DB::transaction(function () use ($request, $data, $user, $totalPrice, $roomDetails, $nights) {
            $voucherId = null;
            $finalPrice = $totalPrice;

            // Apply voucher if provided
            if ($request->filled('voucherCode')) {
                $voucher = Voucher::where('ma_voucher', $request->input('voucherCode'))
                    ->where('trang_thai', 'con_han')
                    ->where('so_luong', '>', 0)
                    ->whereDate('ngay_ket_thuc', '>=', now())
                    ->whereDate('ngay_bat_dau', '<=', now())
                    ->lockForUpdate()
                    ->first();

                if ($voucher) {
                    // Check if voucher applies to specific room type
                    $roomTypeIds = array_column($roomDetails, 'loai_phong_id');
                    if ($voucher->loai_phong_id !== null && !in_array($voucher->loai_phong_id, $roomTypeIds)) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'voucherCode' => 'Mã voucher này chỉ áp dụng cho loại phòng khác. Vui lòng kiểm tra lại.',
                        ]);
                    }

                    // Check minimum order condition (dieu_kien may contain min total)
                    if ($voucher->dieu_kien) {
                        // Extract number from dieu_kien (e.g., "Đơn hàng tối thiểu: 1000000 VNĐ")
                        preg_match('/\d+/', $voucher->dieu_kien, $matches);
                        if (!empty($matches)) {
                            $minTotal = (int) $matches[0];
                            if ($totalPrice < $minTotal) {
                                throw \Illuminate\Validation\ValidationException::withMessages([
                                    'voucherCode' => "Mã voucher yêu cầu đơn hàng tối thiểu " . number_format($minTotal, 0, ',', '.') . " VNĐ. Tổng tiền hiện tại: " . number_format($totalPrice, 0, ',', '.') . " VNĐ.",
                                ]);
                            }
                        }
                    }

                    $discountPercent = $voucher->gia_tri;
                    if ($discountPercent > 0 && $discountPercent <= 100) {
                        $finalPrice = $totalPrice * (1 - $discountPercent / 100);
                        $voucherId = $voucher->id;
                        $voucher->decrement('so_luong');
                    } else {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'voucherCode' => 'Mã voucher có giá trị giảm giá không hợp lệ.',
                        ]);
                    }
                } else {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'voucherCode' => 'Mã voucher không hợp lệ, đã hết hạn hoặc đã hết lượt sử dụng.',
                    ]);
                }
            }

            // Determine username: prioritize form input, fallback to authenticated user
            $username = trim($data['first_name']) ?: ($user->ho_ten ?? null);

            // Calculate price per room (distribute voucher discount proportionally)
            $priceRatio = $finalPrice / $totalPrice;
            $bookings = [];

            // Create one booking record per room type with so_luong_da_dat
            foreach ($roomDetails as $roomDetail) {
                $roomPrice = $roomDetail['price'] * $priceRatio;

                // Lock and re-check availability inside transaction to prevent race conditions
                $loaiPhong = LoaiPhong::lockForUpdate()->findOrFail($roomDetail['loai_phong_id']);

                // Re-check availability after locking (may have changed due to concurrent requests)
                if ($loaiPhong->so_luong_trong < $roomDetail['so_luong']) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'error' => "Loại phòng '{$loaiPhong->ten_loai}' chỉ còn {$loaiPhong->so_luong_trong} phòng trống. Bạn đã chọn {$roomDetail['so_luong']} phòng."
                    ]);
                }

                // Create one booking per room type with quantity
                $booking = DatPhong::create([
                    'nguoi_dung_id' => $user?->id,
                    'loai_phong_id' => $loaiPhong->id,
                    'so_luong_da_dat' => $roomDetail['so_luong'], // Number of rooms booked
                    'ngay_dat' => now(),
                    'ngay_nhan' => $data['ngay_nhan'],
                    'ngay_tra' => $data['ngay_tra'],
                    'so_nguoi' => $data['so_nguoi'] ?? 1,
                    'trang_thai' => 'cho_xac_nhan',
                    'tong_tien' => $roomPrice, // Total price for all rooms of this type
                    'voucher_id' => $voucherId,
                    'username' => $username,
                    'email' => $data['email'],
                    'sdt' => $data['phone'] ?? null,
                    'cccd' => $data['cccd'],
                ]);

                // Update so_luong_trong immediately after booking creation (within transaction)
                $loaiPhong->decrement('so_luong_trong', $roomDetail['so_luong']);

                // Automatically create invoice with status "cho_thanh_toan" (waiting for payment)
                Invoice::create([
                    'dat_phong_id' => $booking->id,
                    'tong_tien' => $booking->tong_tien,
                    'trang_thai' => 'cho_thanh_toan',
                    'phuong_thuc' => null, // Will be set when user chooses payment method
                ]);

                $bookings[] = $booking;
            }

            return $bookings;
        });

        // Use first booking for redirect (or we can create a summary page)
        $datPhong = $bookings[0];

        // Send email to admin: new booking (waiting for confirmation)
        try {
            $adminEmails = \App\Models\User::where('vai_tro', 'admin')
                ->where('trang_thai', 'hoat_dong')
                ->pluck('email')
                ->filter()
                ->all();
            if (!empty($adminEmails)) {
                Mail::to($adminEmails)->send(new AdminBookingEvent($datPhong->load(['loaiPhong']), 'created'));
            }
        } catch (\Throwable $e) {
            Log::warning('Send admin booking created mail (client) failed: '.$e->getMessage());
        }

        // Redirect to payment page
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
