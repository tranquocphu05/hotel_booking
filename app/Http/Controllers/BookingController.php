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
use App\Models\Phong;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class BookingController extends Controller
{
    /**
     * Show booking form for a room type using route model binding
     */
    public function showForm(Request $request, $loaiPhongId = null)
    {
        // Accept query params from the client detail page (checkin, checkout, guests)
        $checkin = $request->query('checkin');
        $checkout = $request->query('checkout');
        $guests = $request->query('guests');

        // Get all available room types for selection (Mường Thanh style - show all rooms)
        $allLoaiPhongs = LoaiPhong::where('trang_thai', 'hoat_dong')
            ->orderBy('ten_loai')
            ->get();

        // Tính số phòng trống cho từng loại phòng trong khoảng thời gian
        $checkinToUse = $checkin ?? now()->format('Y-m-d');
        $checkoutToUse = $checkout ?? now()->addDay()->format('Y-m-d');

        // Tính availability cho tất cả loại phòng
        $roomAvailabilityMap = [];
        foreach ($allLoaiPhongs as $loaiPhong) {
            try {
                $availableCount = Phong::countAvailableRooms($loaiPhong->id, $checkinToUse, $checkoutToUse);
                $roomAvailabilityMap[$loaiPhong->id] = $availableCount;
            } catch (\Exception $e) {
                $roomAvailabilityMap[$loaiPhong->id] = $loaiPhong->so_luong_phong;
            }
        }

        // Get selected room type (if provided) for backward compatibility
        $loaiPhong = $loaiPhongId ? LoaiPhong::find($loaiPhongId) : null;

        return view('client.booking.booking', [
            'loaiPhong' => $loaiPhong, // Selected room type (optional, for backward compatibility)
            'allLoaiPhongs' => $allLoaiPhongs, // All available room types
            'roomAvailabilityMap' => $roomAvailabilityMap, // Map of room type ID => available count
            'checkin' => $checkin,
            'checkout' => $checkout,
            'guests' => $guests,
            'checkinToUse' => $checkinToUse, // Ngày checkin để tính availability
            'checkoutToUse' => $checkoutToUse, // Ngày checkout để tính availability
        ]);
    }

    /**
     * API endpoint để lấy số phòng trống theo khoảng thời gian (AJAX)
     */
    public function getAvailableCount(Request $request)
    {
        $request->validate([
            'loai_phong_id' => 'required|exists:loai_phong,id',
            'checkin' => 'required|date',
            'checkout' => 'required|date|after:checkin',
        ]);

        try {
            $availableCount = Phong::countAvailableRooms(
                $request->loai_phong_id,
                $request->checkin,
                $request->checkout
            );

            return response()->json([
                'success' => true,
                'availableCount' => max(0, $availableCount),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tính số phòng trống: ' . $e->getMessage(),
            ], 500);
        }
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
            'rooms.*.so_nguoi' => 'nullable|integer|min:0|max:400',
            'first_name' => 'required|string|max:255|min:2',
            'email' => 'required|email:rfc,dns|max:255',
            'phone' => 'required|nullable|string|regex:/^0[0-9]{9}$/|max:20',
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
            'phone.required'=> 'số điện thoại không thể để trống',

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

        $totalPrice = 0;
        $roomDetails = [];
        $totalGuests = 0;
        $maxAdultsPerRoom = 2;
        $extraFeePercent = 0.1;

        foreach ($data['rooms'] as $room) {
            $loaiPhong = LoaiPhong::findOrFail($room['loai_phong_id']);

            // Check if room type is active
            if ($loaiPhong->trang_thai !== 'hoat_dong') {
                return back()->withErrors([
                    'error' => "Loại phòng '{$loaiPhong->ten_loai}' hiện không khả dụng."
                ])->withInput();
            }

            // Check if room type has price
            $pricePerNight = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban ?? 0;
            if ($pricePerNight <= 0) {
                return back()->withErrors([
                    'error' => "Loại phòng '{$loaiPhong->ten_loai}' chưa có giá. Vui lòng liên hệ quản trị viên."
                ])->withInput();
            }

            $roomBaseTotal = $pricePerNight * $nights * $room['so_luong'];
            $sumAdults = isset($room['so_nguoi']) ? (int)$room['so_nguoi'] : ($maxAdultsPerRoom * (int)$room['so_luong']);
            $capacity = (int)$room['so_luong'] * $maxAdultsPerRoom;
            $extraGuests = max(0, $sumAdults - $capacity);
            $extraFee = 0;
            if ($extraGuests > 0) {
                $extraFee = $extraGuests * $pricePerNight * $extraFeePercent * $nights;
            }

            $roomTotalWithSurcharge = $roomBaseTotal + $extraFee;
            $totalPrice += $roomTotalWithSurcharge;
            $totalGuests += max(0, $sumAdults);

            $roomDetails[] = [
                'loai_phong_id' => $loaiPhong->id,
                'loai_phong' => $loaiPhong,
                'so_luong' => $room['so_luong'],
                'price' => $roomTotalWithSurcharge,
            ];
        }

        // NOTE: Availability check moved inside transaction to prevent race conditions

        // Apply voucher and create bookings within transaction
        // Use lockForUpdate to prevent race conditions when checking availability
        $bookings = DB::transaction(function () use ($request, $data, $user, $totalPrice, $roomDetails, $nights, $totalGuests) {
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

            // Calculate price per room type (distribute voucher discount proportionally)
            $priceRatio = $totalPrice > 0 ? ($finalPrice / $totalPrice) : 1;

            // Tính tổng số lượng phòng
            $totalSoLuong = array_sum(array_column($roomDetails, 'so_luong'));

            // Lấy loại phòng đầu tiên làm loại phòng chính (cho backward compatibility)
            $firstLoaiPhongId = $roomDetails[0]['loai_phong_id'];

            // Chuẩn bị mảng room_types để lưu vào JSON
            $roomTypesArray = [];
            foreach ($roomDetails as $roomDetail) {
                $preDiscountPrice = $roomDetail['price']; // đã gồm phụ phí, trước voucher
                $roomPrice = $preDiscountPrice * $priceRatio; // sau voucher (phân bổ tỉ lệ)
                $roomTypesArray[] = [
                    'loai_phong_id' => $roomDetail['loai_phong_id'],
                    'so_luong' => $roomDetail['so_luong'],
                    'gia_truoc_giam' => $preDiscountPrice,
                    'gia_rieng' => $roomPrice,
                ];
            }

            // Tạo 1 booking duy nhất chứa tất cả các loại phòng
            $booking = DatPhong::create([
                'nguoi_dung_id' => $user?->id,
                'loai_phong_id' => $firstLoaiPhongId, // Loại phòng chính (cho backward compatibility)
                'room_types' => $roomTypesArray, // Lưu tất cả loại phòng vào JSON
                'so_luong_da_dat' => $totalSoLuong, // Tổng số lượng phòng
                'phong_id' => null, // Không gán phòng ở đây, sẽ dùng bảng trung gian
                'ngay_dat' => now(),
                'ngay_nhan' => $data['ngay_nhan'],
                'ngay_tra' => $data['ngay_tra'],
                'so_nguoi' => $totalGuests > 0 ? $totalGuests : ($data['so_nguoi'] ?? 1),
                'trang_thai' => 'cho_xac_nhan',
                'tong_tien' => $finalPrice, // Tổng tiền của tất cả loại phòng
                'voucher_id' => $voucherId,
                'username' => $username,
                'email' => $data['email'],
                'sdt' => $data['phone'] ?? null,
                'cccd' => $data['cccd'],
            ]);

            // Lưu tất cả phong_ids vào một mảng để merge, không ghi đè
            $allPhongIds = [];

            // Lưu từng loại phòng và gán phòng cụ thể
            foreach ($roomDetails as $roomDetail) {
                // Lock and re-check availability inside transaction to prevent race conditions
                $loaiPhong = LoaiPhong::lockForUpdate()->findOrFail($roomDetail['loai_phong_id']);

                // Tìm phòng trống TRƯỚC khi kiểm tra để đảm bảo có đủ phòng trong khoảng thời gian cụ thể
                // Exclude các phòng đã được gán trong các loop trước
                $availableRooms = Phong::findAvailableRooms(
                    $loaiPhong->id,
                    $data['ngay_nhan'],
                    $data['ngay_tra'],
                    $roomDetail['so_luong'], // Tìm đủ số lượng phòng cần thiết
                    $booking->id // Exclude booking hiện tại
                );

                // Filter out rooms that are already assigned in previous loops
                $availableRooms = $availableRooms->reject(function($phong) use ($allPhongIds) {
                    return in_array($phong->id, $allPhongIds);
                });

                // Kiểm tra xem có đủ phòng không (dựa trên conflict check thực tế)
                if ($availableRooms->count() < $roomDetail['so_luong']) {
                    // Re-check availability excluding already assigned rooms
                    $availableCount = $availableRooms->count();
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'error' => "Loại phòng '{$loaiPhong->ten_loai}' chỉ có {$availableCount} phòng trống trong khoảng thời gian từ " . date('d/m/Y', strtotime($data['ngay_nhan'])) . " đến " . date('d/m/Y', strtotime($data['ngay_tra'])) . ". Bạn đã chọn {$roomDetail['so_luong']} phòng."
                    ]);
                }

                // Lưu các phòng cụ thể vào phong_ids JSON
                // Đảm bảo gán đủ số lượng phòng cho loại phòng này
                $phongIds = [];
                $count = 0;
                foreach ($availableRooms as $phong) {
                    if ($count >= $roomDetail['so_luong']) {
                        break; // Đã gán đủ số lượng cho loại phòng này
                    }

                    // Skip nếu phòng đã được gán trong loop trước
                    if (in_array($phong->id, $allPhongIds)) {
                        continue;
                    }

                    // Lock Phong trước khi kiểm tra và update để tránh race condition
                    $phongLocked = Phong::lockForUpdate()->find($phong->id);
                    if (!$phongLocked) {
                        continue; // Phòng không tồn tại, skip
                    }

                    // Double-check availability before assigning (sau khi lock)
                    if ($phongLocked->isAvailableInPeriod($data['ngay_nhan'], $data['ngay_tra'], $booking->id)) {
                        $phongIds[] = $phongLocked->id;
                        $allPhongIds[] = $phongLocked->id;
                        $count++;

                        // KHÔNG set 'dang_thue' ở đây vì booking chỉ ở trạng thái 'cho_xac_nhan'
                        // Trạng thái phòng sẽ được cập nhật khi booking được xác nhận (trong DatPhong::boot())
                        // Chỉ đánh dấu phòng đã được gán qua phong_ids JSON
                    }
                }

                // Kiểm tra lại xem đã gán đủ phòng cho loại phòng này chưa
                if (count($phongIds) < $roomDetail['so_luong']) {
                    // Nếu không đủ, throw error để rollback transaction
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'error' => "Không thể gán đủ {$roomDetail['so_luong']} phòng cho loại phòng '{$loaiPhong->ten_loai}'. Chỉ gán được " . count($phongIds) . " phòng. Vui lòng thử lại."
                    ]);
                }
            }

            // Lưu tất cả phong_ids vào JSON column sau khi đã gán xong tất cả loại phòng
            $booking->phong_ids = $allPhongIds;
            $booking->save();

            // Cập nhật phong_id (legacy support) nếu chỉ có 1 phòng
            if (count($allPhongIds) == 1) {
                $booking->phong_id = $allPhongIds[0];
                $booking->save();
            }

            // Tạo invoice ngay với trạng thái chờ thanh toán
            // Tính breakdown: tien_phong, giam_gia
            $tienPhong = $totalPrice; // Giá gốc trước voucher
            $giamGia = $totalPrice - $finalPrice; // Số tiền giảm từ voucher
            
            Invoice::create([
                'dat_phong_id' => $booking->id,
                'tien_phong' => $tienPhong,
                'tien_dich_vu' => 0, // Chưa có dịch vụ khi mới tạo
                'giam_gia' => $giamGia,
                'tong_tien' => $finalPrice,
                'trang_thai' => 'cho_thanh_toan',
                'phuong_thuc' => null, // Will be set when user chooses payment method
            ]);

            // Booking sẽ được tự động hủy bởi AutoCancelExpiredBookings middleware
            // Không cần queue worker - tích hợp trực tiếp vào code

            return [$booking];
        });

        // Use the single booking for redirect
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
