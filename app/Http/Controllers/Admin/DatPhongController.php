<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\DatPhong;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DatPhongController extends Controller
{
    public function index(Request $request)
    {
        // Lấy tất cả đơn đặt phòng và sắp xếp theo ngày đặt mới nhất
        $query = DatPhong::with(['phong', 'phong.loaiPhong', 'voucher'])
            ->orderBy('ngay_dat', 'desc');

        // Áp dụng các bộ lọc
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
}
