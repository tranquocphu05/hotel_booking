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

class YeuCauDoiPhongController extends Controller
{
    public function index(Request $request)
    {
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
                    // Cập nhật lại phòng trong pivot (nếu có)
                    if ($booking->phongs()->where('phong_id', $phongCu->id)->exists()) {
                        $booking->phongs()->detach($phongCu->id);
                        $booking->phongs()->attach($phongMoi->id);
                    } elseif ($booking->phong_id === $phongCu->id) {
                        // Legacy: booking có cột phong_id
                        $booking->phong_id = $phongMoi->id;
                        $booking->save();
                    }

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
        $request->validate([
            'ghi_chu_admin' => 'required|string|min:5|max:500',
        ], [
            'ghi_chu_admin.required' => 'Vui lòng nhập lý do từ chối.',
            'ghi_chu_admin.min' => 'Lý do từ chối phải có ít nhất 5 ký tự.',
            'ghi_chu_admin.max' => 'Lý do từ chối không được vượt quá 500 ký tự.',
        ]);

        $yeuCau = YeuCauDoiPhong::findOrFail($id);

        if ($yeuCau->trang_thai !== 'cho_duyet') {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        $yeuCau->update([
            'trang_thai'    => 'bi_tu_choi',
            'nguoi_duyet'   => Auth::id(),
            'ghi_chu_admin' => $request->input('ghi_chu_admin'),
        ]);

        return back()->with('success', 'Đã từ chối yêu cầu đổi phòng.');
    }
}