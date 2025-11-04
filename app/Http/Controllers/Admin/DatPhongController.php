<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Voucher;
use App\Models\DatPhong;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\BookingConfirmed;
use App\Mail\InvoicePaid;
use App\Mail\AdminBookingEvent;

class DatPhongController extends Controller
{
    public function index(Request $request)
    {
        // Lấy tất cả đơn đặt phòng và sắp xếp theo ngày đặt mới nhất
        $query = DatPhong::with(['loaiPhong', 'voucher', 'invoice'])
            ->orderBy('ngay_dat', 'desc');

        // Áp dụng các bộ lọc
        if ($request->search) {
            $query->whereHas('loaiPhong', function ($q) use ($request) {
                $q->where('ten_loai', 'like', '%' . $request->search . '%');
            })
            ->orWhere('username', 'like', '%' . $request->search . '%')
            ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        if ($request->status) {
            $query->where('trang_thai', $request->status);
        }

        if ($request->from_date) {
            $query->whereDate('ngay_dat', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('ngay_dat', '<=', $request->to_date);
        }

        $today = Carbon::today();

        $bookingCounts = [
            'cho_xac_nhan' => DatPhong::where('trang_thai', 'cho_xac_nhan')->whereDate('ngay_dat', $today)->count(),
            'da_xac_nhan'  => DatPhong::where('trang_thai', 'da_xac_nhan')->whereDate('ngay_dat', $today)->count(),
            'da_huy'       => DatPhong::where('trang_thai', 'da_huy')->whereDate('ngay_dat', $today)->count(),
            'da_tra'       => DatPhong::where('trang_thai', 'da_tra')->whereDate('ngay_dat', $today)->count(),
        ];

        // Phân trang, mỗi trang 5 đơn
        $bookings = $query->paginate(5);

        if ($request->ajax()) {
            return view('admin.dat_phong._bookings_list', compact('bookings'))->render();
        }

        return view('admin.dat_phong.index', compact('bookings', 'bookingCounts'));
    }

    public function showCancelForm($id)
    {
        $booking = DatPhong::with(['loaiPhong'])->findOrFail($id);

        // Kiểm tra nếu không phải trạng thái chờ xác nhận thì không cho hủy
        if ($booking->trang_thai !== 'cho_xac_nhan') {
            return redirect()->route('admin.dat_phong.index')
                ->with('error', 'Chỉ có thể hủy đơn đặt phòng đang chờ xác nhận');
        }

        return view('admin.dat_phong.cancel', compact('booking'));
    }

    public function submitCancel(Request $request, $id)
    {
        $booking = DatPhong::findOrFail($id);

        // Validate
        $request->validate([
            'ly_do' => 'required|in:thay_doi_lich_trinh,thay_doi_ke_hoach,khong_phu_hop,ly_do_khac'
        ], [
            'ly_do.required' => 'Vui lòng chọn lý do hủy đặt phòng',
            'ly_do.in' => 'Lý do không hợp lệ'
        ]);

        // Cập nhật trạng thái và lý do hủy
        $booking->update([
            'trang_thai' => 'da_huy',
            // 'ly_do_huy' => $request->ly_do,
            // 'ngay_huy' => now()
        ]);

        return redirect()->route('admin.dat_phong.index')
            ->with('success', 'Đã hủy đặt phòng thành công');
    }

    public function show($id)
    {
        $booking = DatPhong::with(['loaiPhong', 'voucher'])->findOrFail($id);
        return view('admin.dat_phong.show', compact('booking'));
    }

    public function edit($id)
    {
        $booking = DatPhong::with(['loaiPhong', 'voucher', 'user'])->findOrFail($id);

        // Lấy danh sách loại phòng để hiển thị trong form sửa
        $loaiPhongs = \App\Models\LoaiPhong::where('trang_thai', 'hoat_dong')->get();

        // Chỉ cho phép sửa đơn đang chờ xác nhận
        if ($booking->trang_thai !== 'cho_xac_nhan') {
            return redirect()->route('admin.dat_phong.show', $booking->id)
                ->with('error', 'Chỉ có thể sửa đơn đặt phòng đang chờ xác nhận');
        }

        // Tự động điền CCCD từ user nếu booking chưa có CCCD
        if (!$booking->cccd && $booking->user && $booking->user->cccd) {
            $booking->cccd = $booking->user->cccd;
        }

        return view('admin.dat_phong.edit', compact('booking', 'loaiPhongs'));
    }

    public function update(Request $request, $id)
    {
        $booking = DatPhong::findOrFail($id);

        if ($booking->trang_thai !== 'cho_xac_nhan') {
            return redirect()->route('admin.dat_phong.show', $booking->id)
                ->with('error', 'Chỉ có thể sửa đơn đặt phòng đang chờ xác nhận');
        }

        // Nếu chỉ thay đổi trạng thái, không validate ngày
        // $validationRules = [
        //     'phong_id' => 'required|exists:phong,id',
        //     'trang_thai' => 'required|in:cho_xac_nhan,da_xac_nhan,da_huy,da_tra',
        //     'ngay_nhan' => 'required|date',
        //     'ngay_tra' => 'required|date|after_or_equal:ngay_nhan',
        //     'so_nguoi' => 'required|integer|min:1',
        //     'username' => 'required|string|max:255',
        //     'email' => 'required|email|max:255',
        //     'sdt' => 'required|string|max:20',
        //     'cccd' => 'nullable|string|max:20'
        // ];

        // $request->validate($validationRules, [
        //     'phong_id.required' => 'Vui lòng chọn phòng',
        //     'phong_id.exists' => 'Phòng không tồn tại',
        //     'trang_thai.required' => 'Vui lòng chọn trạng thái',
        //     'trang_thai.in' => 'Trạng thái không hợp lệ',
        //     'ngay_nhan.required' => 'Vui lòng chọn ngày nhận phòng',
        //     'ngay_tra.required' => 'Vui lòng chọn ngày trả phòng',
        //     'ngay_tra.after_or_equal' => 'Ngày trả phòng phải sau hoặc bằng ngày nhận phòng',
        //     'so_nguoi.required' => 'Vui lòng nhập số người',
        //     'so_nguoi.min' => 'Số người phải lớn hơn 0',
        //     'username.required' => 'Vui lòng nhập tên khách hàng',
        //     'email.required' => 'Vui lòng nhập email',
        //     'email.email' => 'Email không hợp lệ',
        //     'sdt.required' => 'Vui lòng nhập số điện thoại'
        // ]);

        $request->validate([
            'loai_phong_id' => 'required|exists:loai_phong,id',
            'so_luong_da_dat' => 'nullable|integer|min:1',
            'ngay_nhan' => 'required|date|after_or_equal:today',
            'ngay_tra' => 'required|date|after_or_equal:ngay_nhan',
            'so_nguoi' => 'required|integer|min:1',
            'username' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns|max:255',
            'sdt' => 'required|regex:/^0[0-9]{9}$/',
            'cccd' => 'required|regex:/^[0-9]{12}$/',
            'voucher' => 'nullable|string|exists:voucher,ma_voucher'
        ], [
            'loai_phong_id.required' => 'Vui lòng chọn loại phòng',
            'loai_phong_id.exists' => 'Loại phòng không tồn tại',
            'so_luong_da_dat.min' => 'Số lượng phòng phải lớn hơn 0',
            'ngay_nhan.required' => 'Vui lòng chọn ngày nhận phòng',
            'ngay_nhan.after_or_equal' => 'Ngày nhận phòng phải từ hôm nay trở đi',
            'ngay_tra.required' => 'Vui lòng chọn ngày trả phòng',
            'ngay_tra.after_or_equal' => 'Ngày trả phòng phải sau hoặc bằng ngày nhận phòng',
            'so_nguoi.required' => 'Vui lòng nhập số người',
            'so_nguoi.min' => 'Số người phải lớn hơn 0',
            'username.required' => 'Vui lòng nhập họ tên',
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không hợp lệ',
            'sdt.required' => 'Vui lòng nhập số điện thoại',
            'sdt.regex' => 'Số điện thoại không đúng định dạng',
            'cccd.required' => 'Vui lòng nhập CCCD/CMND',
            'cccd.regex' => 'CCCD/CMND phải gồm 12 chữ số',
        ]);


        // Kiểm tra loại phòng mới có khả dụng không
        $loaiPhong = \App\Models\LoaiPhong::find($request->loai_phong_id);
        if (!$loaiPhong || $loaiPhong->trang_thai !== 'hoat_dong') {
            return back()->withErrors(['loai_phong_id' => 'Loại phòng không khả dụng.'])->withInput();
        }

        // Nếu đổi loại phòng, kiểm tra còn phòng trống không
        if ($booking->loai_phong_id != $request->loai_phong_id) {
            if (!$loaiPhong->hasAvailableRooms()) {
                return back()->withErrors(['loai_phong_id' => 'Loại phòng này đã hết.'])->withInput();
            }
        }

        $booking->update([
            'loai_phong_id' => $request->loai_phong_id,
            'so_luong_da_dat' => $request->so_luong_da_dat ?? 1,
            'trang_thai' => $request->trang_thai,
            'ngay_nhan' => $request->ngay_nhan,
            'ngay_tra' => $request->ngay_tra,
            'so_nguoi' => $request->so_nguoi,
            'username' => $request->username,
            'email' => $request->email,
            'sdt' => $request->sdt,
            'cccd' => $request->cccd
        ]);

        return redirect()->route('admin.dat_phong.index')
            ->with('success', 'Cập nhật thông tin đặt phòng thành công');
    }

    public function create()
    {
        // Lấy danh sách loại phòng thay vì phòng cụ thể
        $loaiPhongs = \App\Models\LoaiPhong::where('trang_thai', 'hoat_dong')->get();

        // Lấy danh sách voucher còn hiệu lực
        $vouchers = Voucher::where('trang_thai', 'con_han')
            ->where('so_luong', '>', 0)
            ->whereDate('ngay_ket_thuc', '>=', now())
            ->get();

        return view('admin.dat_phong.create', compact('loaiPhongs', 'vouchers'));
    }

    public function store(Request $request)
    {
        // Validate room_types array first
        $request->validate([
            'room_types' => 'required|array|min:1',
            'room_types.*' => 'required|integer|exists:loai_phong,id',
            'rooms' => 'required|array|min:1',
            'rooms.*.loai_phong_id' => 'required|integer|exists:loai_phong,id',
            'rooms.*.so_luong' => 'required|integer|min:1',
            'ngay_nhan' => 'required|date|after_or_equal:today',
            'ngay_tra' => 'required|date|after:ngay_nhan',
            'so_nguoi' => 'required|integer|min:1',
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'sdt' => 'required|regex:/^0[0-9]{9}$/',
            'cccd' => 'required|regex:/^[0-9]{12}$/',
            'voucher' => 'nullable|string|exists:voucher,ma_voucher'
        ], [
            'room_types.required' => 'Vui lòng chọn ít nhất một loại phòng',
            'room_types.min' => 'Vui lòng chọn ít nhất một loại phòng',
            'room_types.*.exists' => 'Loại phòng không tồn tại',
            'rooms.required' => 'Vui lòng chọn ít nhất một loại phòng',
            'rooms.min' => 'Vui lòng chọn ít nhất một loại phòng',
            'rooms.*.loai_phong_id.required' => 'Vui lòng chọn loại phòng',
            'rooms.*.loai_phong_id.exists' => 'Loại phòng không tồn tại',
            'rooms.*.so_luong.required' => 'Vui lòng nhập số lượng phòng',
            'rooms.*.so_luong.min' => 'Số lượng phòng phải lớn hơn 0',
            'ngay_nhan.required' => 'Vui lòng chọn ngày nhận phòng',
            'ngay_nhan.after_or_equal' => 'Ngày nhận phòng phải từ hôm nay trở đi',
            'ngay_tra.required' => 'Vui lòng chọn ngày trả phòng',
            'ngay_tra.after' => 'Ngày trả phòng phải sau ngày nhận phòng',
            'so_nguoi.required' => 'Vui lòng nhập số người',
            'so_nguoi.min' => 'Số người phải lớn hơn 0',
            'username.required' => 'Vui lòng nhập họ tên',
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không hợp lệ',
            'sdt.required' => 'Vui lòng nhập số điện thoại',
            'sdt.regex' => 'Số điện thoại không đúng định dạng (phải bắt đầu bằng 0 và có 10 chữ số)',
            'cccd.required' => 'Vui lòng nhập CCCD/CMND',
            'cccd.regex' => 'CCCD/CMND phải gồm 12 chữ số',
        ]);

        $nights = Carbon::parse($request->ngay_nhan)->diffInDays(Carbon::parse($request->ngay_tra));
        $nights = max(1, $nights);
        
        // Validate each room type and check availability
        $totalPrice = 0;
        $roomDetails = [];
        $errors = [];
        
        foreach ($request->rooms as $roomTypeId => $room) {
            // Additional validation: check if room_type_id matches
            if ($room['loai_phong_id'] != $roomTypeId) {
                $errors[] = "Dữ liệu không hợp lệ cho loại phòng ID: {$roomTypeId}";
                continue;
            }
            
            $loaiPhong = \App\Models\LoaiPhong::find($room['loai_phong_id']);
            
            if (!$loaiPhong) {
                $errors[] = "Loại phòng ID {$room['loai_phong_id']} không tồn tại";
                continue;
            }
            
            // Check if room type is active
            if ($loaiPhong->trang_thai !== 'hoat_dong') {
                $errors[] = "Loại phòng '{$loaiPhong->ten_loai}' hiện không khả dụng";
                continue;
            }
            
            // Validate quantity is positive
            if ($room['so_luong'] < 1) {
                $errors[] = "Số lượng phòng cho loại phòng '{$loaiPhong->ten_loai}' phải lớn hơn 0";
                continue;
            }
            
            // Check if there are enough available rooms
            if ($loaiPhong->so_luong_trong < $room['so_luong']) {
                $errors[] = "Loại phòng '{$loaiPhong->ten_loai}' chỉ còn {$loaiPhong->so_luong_trong} phòng trống. Bạn đã chọn {$room['so_luong']} phòng";
                continue;
            }
            
            // Use promotional price if available, otherwise use base price
            $pricePerNight = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban ?? 0;
            $roomTotal = $pricePerNight * $nights * $room['so_luong'];
            $totalPrice += $roomTotal;
            
            $roomDetails[] = [
                'loai_phong' => $loaiPhong,
                'so_luong' => $room['so_luong'],
                'price' => $roomTotal,
            ];
        }
        
        // Return errors if any validation failed
        if (!empty($errors)) {
            return back()->withErrors(['error' => implode('. ', $errors)])->withInput();
        }
        
        // Additional validation: at least one room must be selected
        if (empty($roomDetails)) {
            return back()->withErrors(['room_types' => 'Vui lòng chọn ít nhất một loại phòng'])->withInput();
        }

        // Xử lý voucher nếu có
        $voucherId = null;
        $finalPrice = $totalPrice;
        if ($request->voucher) {
            $voucher = Voucher::where('ma_voucher', $request->voucher)
                ->where('so_luong', '>', 0)
                ->where('trang_thai', 'con_han')
                ->whereDate('ngay_ket_thuc', '>=', now())
                ->first();

            if ($voucher) {
                $discountPercent = $voucher->gia_tri ?? 0;
                if ($discountPercent > 0 && $discountPercent <= 100) {
                    $finalPrice = $totalPrice * (1 - $discountPercent / 100);
                    $voucherId = $voucher->id;
                    $voucher->decrement('so_luong');
                }
            }
        }

        // Calculate price per room (distribute voucher discount proportionally)
        $priceRatio = $finalPrice / $totalPrice;
        
        // Create one booking record per room type with so_luong_da_dat
        $bookings = [];
        foreach ($roomDetails as $roomDetail) {
            $roomPrice = $roomDetail['price'] * $priceRatio;
            $loaiPhong = $roomDetail['loai_phong'];
            
            // Create one booking per room type with quantity
            $booking = DatPhong::create([
                'nguoi_dung_id' => Auth::id(),
                'loai_phong_id' => $loaiPhong->id,
                'so_luong_da_dat' => $roomDetail['so_luong'],
                'ngay_dat' => now(),
                'ngay_nhan' => $request->ngay_nhan,
                'ngay_tra' => $request->ngay_tra,
                'so_nguoi' => $request->so_nguoi,
                'trang_thai' => 'cho_xac_nhan',
                'tong_tien' => $roomPrice,
                'voucher_id' => $voucherId,
                'username' => $request->username,
                'email' => $request->email,
                'sdt' => $request->sdt,
                'cccd' => $request->cccd
            ]);

            // Automatically create invoice with status "cho_thanh_toan" (waiting for payment)
            \App\Models\Invoice::create([
                'dat_phong_id' => $booking->id,
                'tong_tien' => $booking->tong_tien,
                'trang_thai' => 'cho_thanh_toan',
                'phuong_thuc' => null,
            ]);
            
            $bookings[] = $booking;
        }

        // Gửi mail cho admin: đơn đặt phòng mới (trạng thái chờ xác nhận)
        try {
            $adminEmails = \App\Models\User::where('vai_tro', 'admin')
                ->where('trang_thai', 'hoat_dong')
                ->pluck('email')
                ->filter()
                ->all();
            if (!empty($adminEmails)) {
                foreach ($bookings as $booking) {
                    Mail::to($adminEmails)->send(new AdminBookingEvent($booking->load(['loaiPhong']), 'created'));
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Send admin booking created mail failed: '.$e->getMessage());
        }

        $roomTypes = implode(', ', array_map(fn($rd) => $rd['loai_phong']->ten_loai, $roomDetails));
        return redirect()->route('admin.dat_phong.index')
            ->with('success', 'Đặt phòng thành công! Loại phòng: ' . $roomTypes);
    }

    public function blockRoom($id)
    {
        $booking = DatPhong::findOrFail($id);

        // Chỉ cho phép chống phòng khi đã xác nhận
        if ($booking->trang_thai !== 'da_xac_nhan') {
            return redirect()->route('admin.dat_phong.index')
                ->with('error', 'Chỉ có thể chống phòng đã xác nhận');
        }

        // Cập nhật trạng thái đặt phòng thành "đã chống"
        // Note: Không còn phòng riêng lẻ, chỉ cập nhật booking status
        $booking->update(['trang_thai' => 'da_chong']);

        return redirect()->route('admin.dat_phong.index')
            ->with('success', 'Đã chống phòng thành công! Phòng không thể đặt được cho đến khi hủy chống.');
    }

    /**
     * Quick confirm booking from index card
     */
    public function quickConfirm($id)
    {
        $booking = DatPhong::findOrFail($id);

        if ($booking->trang_thai !== 'cho_xac_nhan') {
            return redirect()->route('admin.dat_phong.index')
                ->with('error', 'Chỉ xác nhận được đơn đang chờ xác nhận');
        }

        // Allow confirming even if dates are in the past
        $booking->trang_thai = 'da_xac_nhan';
        $booking->save();

        // Gửi mail xác nhận đặt phòng
        if ($booking->email) {
            try {
                Mail::to($booking->email)->send(new BookingConfirmed($booking->load('loaiPhong')));
            } catch (\Throwable $e) {
                // log but don't break flow
                Log::warning('Send booking confirmed mail failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.dat_phong.index')
            ->with('success', 'Phòng đã được xác nhận thành công!');
    }

    /**
     * Mark booking as paid: create (or update) invoice to 'da_thanh_toan'
     */
    public function markPaid($id)
    {
        $booking = DatPhong::with('invoice')->findOrFail($id);

        // Create invoice if missing
        $invoice = $booking->invoice;
        if (!$invoice) {
            $invoice = \App\Models\Invoice::create([
                'dat_phong_id' => $booking->id,
                'tong_tien' => $booking->tong_tien,
                'phuong_thuc' => 'tien_mat',
                'trang_thai' => 'da_thanh_toan',
            ]);
        } else {
            $invoice->update([
                'trang_thai' => 'da_thanh_toan',
            ]);
        }

        // Optionally confirm booking if still pending
        if ($booking->trang_thai === 'cho_xac_nhan') {
            $booking->trang_thai = 'da_xac_nhan';
            $booking->save();
        }

        // Gửi mail hóa đơn đã thanh toán (khách hàng)
        if ($booking->email) {
            try {
                Mail::to($booking->email)->send(new InvoicePaid($booking->load(['loaiPhong'])));
            } catch (\Throwable $e) {
                Log::warning('Send invoice mail failed: ' . $e->getMessage());
            }
        }

        // Gửi mail cho admin: đơn đã thanh toán
        try {
            $adminEmails = \App\Models\User::where('vai_tro', 'admin')
                ->where('trang_thai', 'hoat_dong')
                ->pluck('email')
                ->filter()
                ->all();
            if (!empty($adminEmails)) {
                Mail::to($adminEmails)->send(new AdminBookingEvent($booking->load(['loaiPhong']), 'paid'));
            }
        } catch (\Throwable $e) {
            Log::warning('Send admin paid mail failed: '.$e->getMessage());
        }

        return redirect()->route('admin.dat_phong.index')
            ->with('success', 'Đã đánh dấu thanh toán và đồng bộ hóa đơn thành công.');
    }
}
