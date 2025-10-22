<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Phong;
use App\Models\Voucher;
use App\Models\DatPhong;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DatPhongController extends Controller
{
    public function index(Request $request)
    {
        // Lấy tất cả đơn đặt phòng và sắp xếp theo ngày đặt mới nhất
        $query = DatPhong::with(['phong', 'phong.loaiPhong', 'voucher', 'invoice'])
            ->orderBy('ngay_dat', 'desc');

        // Áp dụng các bộ lọc
        if ($request->search) {
            $query->whereHas('phong', function ($q) use ($request) {
                $q->where('ten_phong', 'like', '%' . $request->search . '%');
            });
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

        // Phân trang, mỗi trang 9 đơn
        $bookings = $query->paginate(9);

        if ($request->ajax()) {
            return view('admin.dat_phong._bookings_list', compact('bookings'))->render();
        }

        return view('admin.dat_phong.index', compact('bookings', 'bookingCounts'));
    }

    public function showCancelForm($id)
    {
        $booking = DatPhong::with(['phong'])->findOrFail($id);

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
        $booking = DatPhong::with(['phong', 'phong.loaiPhong', 'voucher'])->findOrFail($id);
        return view('admin.dat_phong.show', compact('booking'));
    }

    public function edit($id)
    {
        $booking = DatPhong::with(['phong', 'phong.loaiPhong', 'voucher', 'user'])->findOrFail($id);

        // Lấy danh sách phòng để hiển thị trong form sửa
        $rooms = Phong::all();

        // Chỉ cho phép sửa đơn đang chờ xác nhận
        if ($booking->trang_thai !== 'cho_xac_nhan') {
            return redirect()->route('admin.dat_phong.show', $booking->id)
                ->with('error', 'Chỉ có thể sửa đơn đặt phòng đang chờ xác nhận');
        }

        // Tự động điền CCCD từ user nếu booking chưa có CCCD
        if (!$booking->cccd && $booking->user && $booking->user->cccd) {
            $booking->cccd = $booking->user->cccd;
        }

        return view('admin.dat_phong.edit', compact('booking', 'rooms'));
    }

    public function update(Request $request, $id)
    {
        $booking = DatPhong::findOrFail($id);

        if ($booking->trang_thai !== 'cho_xac_nhan') {
            return redirect()->route('admin.dat_phong.show', $booking->id)
                ->with('error', 'Chỉ có thể sửa đơn đặt phòng đang chờ xác nhận');
        }

        // Nếu chỉ thay đổi trạng thái, không validate ngày
        $validationRules = [
            'phong_id' => 'required|exists:phong,id',
            'trang_thai' => 'required|in:cho_xac_nhan,da_xac_nhan,da_huy,da_tra',
            'ngay_nhan' => 'required|date',
            'ngay_tra' => 'required|date|after_or_equal:ngay_nhan',
            'so_nguoi' => 'required|integer|min:1',
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'sdt' => 'required|string|max:20',
            'cccd' => 'nullable|string|max:20'
        ];

        $request->validate($validationRules, [
            'phong_id.required' => 'Vui lòng chọn phòng',
            'phong_id.exists' => 'Phòng không tồn tại',
            'trang_thai.required' => 'Vui lòng chọn trạng thái',
            'trang_thai.in' => 'Trạng thái không hợp lệ',
            'ngay_nhan.required' => 'Vui lòng chọn ngày nhận phòng',
            'ngay_tra.required' => 'Vui lòng chọn ngày trả phòng',
            'ngay_tra.after_or_equal' => 'Ngày trả phòng phải sau hoặc bằng ngày nhận phòng',
            'so_nguoi.required' => 'Vui lòng nhập số người',
            'so_nguoi.min' => 'Số người phải lớn hơn 0',
            'username.required' => 'Vui lòng nhập tên khách hàng',
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không hợp lệ',
            'sdt.required' => 'Vui lòng nhập số điện thoại'
        ]);

        $booking->update([
            'phong_id' => $request->phong_id,
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
        // Lấy tất cả phòng (không lọc theo trạng thái để admin có thể đặt bất kỳ phòng nào)
        $rooms = Phong::with('loaiPhong')->get();

        // Lấy danh sách voucher còn hiệu lực
        $vouchers = Voucher::where('trang_thai', 'con_han')
            ->where('so_luong', '>', 0)
            ->whereDate('ngay_ket_thuc', '>=', now())
            ->get();

        return view('admin.dat_phong.create', compact('rooms', 'vouchers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'phong_id' => 'required|exists:phong,id',
            'ngay_nhan' => 'required|date|after_or_equal:today',
            'ngay_tra' => 'required|date|after_or_equal:ngay_nhan',
            'so_nguoi' => 'required|integer|min:1',
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'sdt' => 'required|string|max:20',
            'cccd' => 'required|string|max:20',
            'voucher' => 'nullable|string|exists:voucher,ma_voucher'
        ], [
            'phong_id.required' => 'Vui lòng chọn phòng',
            'phong_id.exists' => 'Phòng không tồn tại',
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
            'cccd.required' => 'Vui lòng nhập CCCD/CMND'
        ]);

        // Kiểm tra phòng có tồn tại không
        $room = Phong::findOrFail($request->phong_id);
        
        // Admin có thể đặt bất kỳ phòng nào, không cần kiểm tra trạng thái

        // Lấy tổng tiền đã được tính toán ở client-side (đã bao gồm giảm giá nếu có)
        $tongTien = $request->tong_tien;

        // Xử lý voucher nếu có
        $voucherId = null;
        if ($request->voucher) {
            $voucher = Voucher::where('ma_voucher', $request->voucher)
                ->where('so_luong', '>', 0)
                ->whereDate('ngay_ket_thuc', '>=', now())
                ->first();

            if ($voucher) {
                $tongTien = $tongTien * (1 - $voucher->giam_gia / 100);
                $voucherId = $voucher->id;

                // Giảm số lượng voucher
                $voucher->decrement('so_luong');
            }
        }

        // Tạo đặt phòng mới
        $booking = DatPhong::create([
            'nguoi_dung_id' => Auth::id(),
            'phong_id' => $request->phong_id,
            'ngay_dat' => now(),
            'ngay_nhan' => $request->ngay_nhan,
            'ngay_tra' => $request->ngay_tra,
            'so_nguoi' => $request->so_nguoi,
            'trang_thai' => 'cho_xac_nhan',
            'tong_tien' => $tongTien,
            'voucher_id' => $voucherId,
            'username' => $request->username,
            'email' => $request->email,
            'sdt' => $request->sdt,
            'cccd' => $request->cccd
        ]);

        // Cập nhật trạng thái phòng (không cần thay đổi vì admin có thể đặt bất kỳ phòng nào)
        // $room->update(['trang_thai' => 'da_dat']); // Bỏ comment vì không cần thay đổi trạng thái phòng

        return redirect()->route('admin.dat_phong.index')
            ->with('success', 'Đặt phòng thành công!');
    }

    public function blockRoom($id)
    {
        $booking = DatPhong::findOrFail($id);
        
        // Chỉ cho phép chống phòng khi đã xác nhận
        if ($booking->trang_thai !== 'da_xac_nhan') {
            return redirect()->route('admin.dat_phong.index')
                ->with('error', 'Chỉ có thể chống phòng đã xác nhận');
        }
        
        // Cập nhật trạng thái phòng thành "chống"
        $booking->phong->update(['trang_thai' => 'chong']);
        
        // Cập nhật trạng thái đặt phòng thành "đã chống"
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

        return redirect()->route('admin.dat_phong.index')
            ->with('success', 'Đã đánh dấu thanh toán và đồng bộ hóa đơn thành công.');
    }
}
