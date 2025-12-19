<?php

// app/Http/Controllers/Admin/YeuCauDoiPhongController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DatPhong;
use App\Models\Phong;
use App\Models\YeuCauDoiPhong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\BookingPriceCalculator;
use App\Services\RoomUpgradeFeeCalculator;
use Carbon\Carbon;

use App\Traits\HasRolePermissions;

class YeuCauDoiPhongController extends Controller
{
    use HasRolePermissions;

    public function index(Request $request)
    {
        // Nhân viên: xem yêu cầu đổi phòng
        // Lễ tân: nhận yêu cầu đổi phòng từ khách
        if ($this->hasRole('nhan_vien')) {
            $this->authorizePermission('room_change.view');
        } elseif ($this->hasRole('le_tan')) {
            $this->authorizePermission('room_change.receive');
        }
        $status = $request->get('status', 'all');
        $search = $request->get('q');

        $query = YeuCauDoiPhong::with([
            'datPhong',
            'datPhong.user',
            'datPhong.loaiPhong',
            'phongCu',
            'phongMoi',
        ])->latest();

        if ($status && $status !== 'all') {
            $query->where('trang_thai', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('dat_phong_id', $search)
                    ->orWhereHas('datPhong', function ($sub) use ($search) {
                        $sub->where('ma_dat_phong', 'like', "%{$search}%")
                            ->orWhere('id', $search)
                            ->orWhereHas('user', function ($userQuery) use ($search) {
                                $userQuery->where('ten', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('phongCu', function ($sub) use ($search) {
                        $sub->where('ten_phong', 'like', "%{$search}%");
                    })
                    ->orWhereHas('phongMoi', function ($sub) use ($search) {
                        $sub->where('ten_phong', 'like', "%{$search}%");
                    });
            });
        }

        $yeuCau = $query->paginate(8)->withQueryString();

        $counts = YeuCauDoiPhong::selectRaw('trang_thai, COUNT(*) as total')
            ->groupBy('trang_thai')
            ->pluck('total', 'trang_thai');

        $total = YeuCauDoiPhong::count();

        return view('admin.yeu_cau_doi_phong.index', [
            'yeuCau' => $yeuCau,
            'counts' => [
                'all' => $total,
                'cho_duyet' => $counts['cho_duyet'] ?? 0,
                'da_duyet' => $counts['da_duyet'] ?? 0,
                'bi_tu_choi' => $counts['bi_tu_choi'] ?? 0,
            ],
            'activeStatus' => $status,
            'searchTerm' => $search,
        ]);
    }

    public function approve($id, Request $request)
    {
        // Nhân viên: xử lý yêu cầu đổi phòng
        $this->authorizePermission('room_change.process');

        $yeuCau = YeuCauDoiPhong::with(['datPhong', 'phongCu', 'phongMoi'])->findOrFail($id);

        if ($yeuCau->trang_thai !== 'cho_duyet') {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        DB::transaction(function () use ($yeuCau, $request) {
            $booking = $yeuCau->datPhong;
            $phongCu = $yeuCau->phongCu;
            $phongMoi = $yeuCau->phongMoi;

            if ($booking) {
                // Chỉ xử lý khi booking vẫn đang ở trạng thái đang ở
                if ($booking->trang_thai === 'da_xac_nhan' && $booking->thoi_gian_checkin && !$booking->thoi_gian_checkout) {
                    // Lấy thời điểm đổi phòng từ request hoặc dùng thời điểm hiện tại
                    $changeAt = $request->input('change_at') 
                        ? Carbon::parse($request->input('change_at')) 
                        : Carbon::now();

                    // Lấy các thông tin từ request (có thể để trống, dùng giá trị mặc định)
                    $vatPercent = (float)($request->input('vat_percent', 0));
                    $serviceChargePercent = (float)($request->input('service_charge_percent', 0));
                    
                    // Lấy các phụ thu khác từ request
                    $extras = [];
                    if ($request->has('extras')) {
                        foreach ($request->input('extras', []) as $extra) {
                            if (isset($extra['name']) && isset($extra['amount']) && $extra['amount'] > 0) {
                                $extras[$extra['name']] = (float)$extra['amount'];
                            }
                        }
                    }

                    // Tính phí chênh lệch chi tiết
                    $feeCalculation = RoomUpgradeFeeCalculator::calculate(
                        $booking,
                        $phongCu,
                        $phongMoi,
                        $changeAt,
                        $vatPercent,
                        $serviceChargePercent,
                        $extras,
                        null,
                        null,
                        null
                    );

                    // Cập nhật lại phòng trong pivot (nếu có)
                    if ($booking->phongs()->where('phong_id', $phongCu->id)->exists()) {
                        $booking->phongs()->detach($phongCu->id);
                        $booking->phongs()->attach($phongMoi->id);
                    } elseif ($booking->phong_id === $phongCu->id) {
                        // Legacy: booking có cột phong_id
                        $booking->phong_id = $phongMoi->id;
                    }

                    // Nếu phòng mới khác loại phòng, cập nhật loai_phong_id của booking
                    if ($phongMoi->loai_phong_id != $booking->loai_phong_id) {
                        $booking->loai_phong_id = $phongMoi->loai_phong_id;
                    }

                    // Phí đổi phòng cơ bản: SỬ DỤNG GIÁ TRỊ ĐÃ LƯU KHI CLIENT TẠO YÊU CẦU (không tính lại)
                    // Để đảm bảo tính nhất quán, sử dụng giá trị đã lưu trong yêu cầu thay vì tính lại
                    $phiDoiPhongCoBan = $yeuCau->phi_doi_phong ?? 0;
                    
                    // Nếu yêu cầu chưa có phi_doi_phong (trường hợp cũ), thì mới tính lại
                    if ($phiDoiPhongCoBan == 0 && !$yeuCau->phi_doi_phong) {
                        $phiDoiPhongCoBan = $feeCalculation['phi_doi_phong'];
                    }
                    
                    // Cập nhật phụ phí phát sinh (tổng phí chênh lệch) CHỈ gồm phí đổi phòng + extras
                    // Không tính phụ phí thêm người, không tính VAT/service charge trong luồng đổi phòng
                    $phiPhatSinhThem = $phiDoiPhongCoBan
                        + ($feeCalculation['extras_total'] ?? 0);

                    $booking->phi_phat_sinh = ($booking->phi_phat_sinh ?? 0) + $phiPhatSinhThem;
                    
                    // Không xử lý riêng phụ phí thêm người/trẻ em/em bé trong luồng đổi phòng nữa
                    $booking->save();

                    // KHÔNG cập nhật phi_doi_phong - giữ nguyên giá trị đã lưu khi client tạo yêu cầu
                    // Để đảm bảo tính nhất quán, không tính lại phí đổi phòng khi approve
                    // $yeuCau->phi_doi_phong đã được lưu khi client tạo yêu cầu, không cần cập nhật lại

                    // Tính lại tổng tiền booking (bao gồm phí đổi phòng)
                    BookingPriceCalculator::recalcTotal($booking);

                    // Cập nhật trạng thái phòng cơ bản
                    if ($phongCu) {
                        $phongCu->update(['trang_thai' => 'trong']);
                    }
                    if ($phongMoi) {
                        $phongMoi->update(['trang_thai' => 'dang_thue']);
                    }
                }
            }

            $yeuCau->update([
                'trang_thai'   => 'da_duyet',
                'nguoi_duyet'  => Auth::id(),
                'ghi_chu_admin'=> $request->input('ghi_chu_admin'),
            ]);
        });

        return back()->with('success', 'Đã duyệt yêu cầu đổi phòng thành công.');
    }

    public function reject($id, Request $request)
    {
        $yeuCau = YeuCauDoiPhong::findOrFail($id);

        if ($yeuCau->trang_thai !== 'cho_duyet') {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        $yeuCau->update([
            'trang_thai'    => 'bi_tu_choi',
            'nguoi_duyet'   => Auth::id(),
            'ghi_chu_admin' => $request->input('ghi_chu_admin', ''),
        ]);

        return back()->with('success', 'Đã từ chối yêu cầu đổi phòng.');
    }

    /**
     * API tính toán phí chênh lệch (AJAX)
     */
    public function calculateFee($id, Request $request)
    {
        $yeuCau = YeuCauDoiPhong::with(['datPhong', 'phongCu', 'phongMoi'])->findOrFail($id);
        
        $changeAt = $request->input('change_at') 
            ? Carbon::parse($request->input('change_at')) 
            : Carbon::now();
        
        $vatPercent = (float)($request->input('vat_percent', 0));
        $serviceChargePercent = (float)($request->input('service_charge_percent', 0));
        $soNguoiMoi = $request->input('so_nguoi_moi') 
            ? (int)$request->input('so_nguoi_moi') 
            : ($yeuCau->so_nguoi_moi ?? null);
        $soTreEmMoi = $request->input('so_tre_em_moi') 
            ? (int)$request->input('so_tre_em_moi') 
            : ($yeuCau->so_tre_em_moi ?? null);
        $soEmBeMoi = $request->input('so_em_be_moi') 
            ? (int)$request->input('so_em_be_moi') 
            : ($yeuCau->so_em_be_moi ?? null);
        
        $feeCalculation = RoomUpgradeFeeCalculator::calculate(
            $yeuCau->datPhong,
            $yeuCau->phongCu,
            $yeuCau->phongMoi,
            $changeAt,
            $vatPercent,
            $serviceChargePercent,
            [],
            $soNguoiMoi,
            $soTreEmMoi,
            $soEmBeMoi
        );
        
        return response()->json($feeCalculation);
    }
}