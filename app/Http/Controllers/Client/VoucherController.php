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
        // 1. Lấy tổng tiền hiện tại và ID loại phòng từ Frontend
        $currentCartTotal = (int) round($request->input('current_total', 0)); 
        // Lấy ID loại phòng từ yêu cầu (Chú ý: tên tham số trong JS là `loai_phong_id`)
        $roomTypeId = (int) $request->input('loai_phong_id', 0); // Đổi 'room_type_id' thành 'loai_phong_id'

        // 2. Lấy danh sách voucher đang hoạt động
        $vouchers = Voucher::where('trang_thai', 1)
            ->with('loaiPhong') 
            ->get(); 
        
        // 3. KIỂM TRA ĐIỀU KIỆN VÀ GÁN TRẠNG THÁI `isValid` CHO TỪNG VOUCHER
        $vouchers = $vouchers->map(function($voucher) use ($currentCartTotal, $roomTypeId) {
            $isValid = true;
            $reason = '';
            
            // Giả định model Voucher có các trường: min_total, loai_phong_id (ID loại phòng áp dụng)
            
            // A. Kiểm tra Giá trị đơn hàng tối thiểu
            if ($voucher->min_total && $voucher->min_total > $currentCartTotal) {
                $isValid = false;
                $reason = 'Đơn hàng tối thiểu: ' . number_format($voucher->min_total) . ' VNĐ';
            }
            
            // B. Kiểm tra Loại phòng áp dụng
            // Chỉ kiểm tra nếu voucher có quy định loại phòng cụ thể (loai_phong_id != null/0)
            if ($isValid && $voucher->loai_phong_id && $voucher->loai_phong_id != $roomTypeId) {
                 $isValid = false;
                 // Cần đảm bảo quan hệ loaiPhong tồn tại và có trường 'ten_loai'
                 $reason = 'Chỉ áp dụng cho loại phòng: ' . optional($voucher->loaiPhong)->ten_loai; 
            }
            
            // C. Kiểm tra ngày hết hạn, số lượng... (nếu có)

            $voucher->is_valid = $isValid; // Thuộc tính mới để truyền sang view
            $voucher->reason = $reason;
            return $voucher;
        });

        // 4. Truyền dữ liệu sang view
        return view('client.booking.voucher', [ 
            'vouchers' => $vouchers,
            'currentCartTotal' => $currentCartTotal,
            'roomTypeId' => $roomTypeId,
        ]);
    }
}