<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Voucher; // Giả sử model Voucher đã tồn tại
use Illuminate\Http\Request;
use Carbon\Carbon;

class VoucherController extends Controller
{
    /**
     * Tải nội dung popup voucher, bao gồm danh sách voucher 
     * và tổng tiền hiện tại của giỏ hàng (currentCartTotal) để kiểm tra điều kiện.
     * @param Request $request Chứa tham số 'current_total' và 'loai_phong_id'
     * @return \Illuminate\View\View
     */
    public function getVoucher(Request $request)
    {
        // 1. Lấy tổng tiền hiện tại từ Frontend (đã tính theo các phòng đang chọn)
        $currentCartTotal = (int) round($request->input('current_total', 0));

        // 2. Lấy danh sách ID loại phòng người dùng đang chọn (CSV từ JS)
        $roomTypeIdsCsv = $request->input('room_type_ids', '');
        $selectedRoomTypeIds = collect(explode(',', $roomTypeIdsCsv))
            ->filter(function ($id) {
                return is_numeric($id);
            })
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->all();

        // 3. Lấy danh sách voucher đang hoạt động (để view tự đánh giá điều kiện)
        $vouchers = Voucher::where('trang_thai', 1)
            ->with('loaiPhong')
            ->get();

        // 4. Truyền dữ liệu sang view
        return view('client.booking.voucher', [
            'vouchers' => $vouchers,
            'currentCartTotal' => $currentCartTotal,
            'selectedRoomTypeIds' => $selectedRoomTypeIds,
        ]);
    }
}