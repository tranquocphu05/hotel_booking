<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\DatPhong;
use App\Models\Phong;
use App\Models\YeuCauDoiPhong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class YeuCauDoiPhongController extends Controller
{
    /**
     * Hiển thị form tạo yêu cầu đổi phòng cho khách (GET)
     * Route: /client/profile/booking/{booking}/doi-phong
     */
    public function create(Request $request, DatPhong $booking)
    {
        $user = $request->user();

        // 1. Booking phải thuộc user
        if ($booking->nguoi_dung_id !== $user->id) {
            abort(403, 'Bạn không có quyền thao tác với đặt phòng này.');
        }

        // 2. Chỉ khi:
        // - đã xác nhận
        // - đã checkin
        // - chưa checkout
        if (
            $booking->trang_thai !== 'da_xac_nhan' ||
            !$booking->thoi_gian_checkin ||
            $booking->thoi_gian_checkout
        ) {
            return redirect()->route('profile.edit')
                ->with('error', 'Chỉ có thể yêu cầu đổi phòng khi đặt phòng đã được xác nhận, đã check-in và chưa check-out.');
        }

        // 3. Không cho gửi nếu đã có yêu cầu đổi phòng "chờ duyệt"
        $daCoYeuCauChoDuyet = $booking->yeuCauDoiPhongs()
            ->where('trang_thai', 'cho_duyet')
            ->exists();

        if ($daCoYeuCauChoDuyet) {
            return redirect()->route('profile.edit')
                ->with('error', 'Bạn đã gửi một yêu cầu đổi phòng và đang chờ admin duyệt.');
        }

        // Load phòng hiện tại
        $booking->load(['phongs', 'loaiPhong']);

        $phongHienTai = $booking->phongs->first(); // nếu nhiều phòng, bạn tự sửa logic tùy ý

        // 4. Danh sách phòng trống TẤT CẢ loại trong khoảng ngày booking
        $availableRooms = Phong::query()
            ->with('loaiPhong')
            ->whereIn('trang_thai', ['trong', 'dang_don'])
            ->whereDoesntHave('datPhongs', function ($q) use ($booking) {
                $q->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                    ->where('ngay_tra', '>', $booking->ngay_nhan)
                    ->where('ngay_nhan', '<', $booking->ngay_tra);
            })
            ->orderBy('loai_phong_id')
            ->orderBy('ten_phong')
            ->get();

        // Tính số đêm
        $nights = max(1, \Carbon\Carbon::parse($booking->ngay_nhan)->diffInDays(\Carbon\Carbon::parse($booking->ngay_tra)));

        return view('client.yeu_cau_doi_phong.create', compact(
            'booking',
            'phongHienTai',
            'availableRooms',
            'nights'
        ));
    }

    /**
     * Lưu yêu cầu đổi phòng từ phía khách (POST)
     * Route: /client/profile/booking/{booking}/doi-phong
     */
    public function store(Request $request, DatPhong $booking)
    {
        $user = $request->user();

        // 1. Booking phải thuộc user
        if ($booking->nguoi_dung_id !== $user->id) {
            abort(403, 'Bạn không có quyền thao tác với đặt phòng này.');
        }

        // 2. Chỉ khi đã xác nhận + đã checkin + chưa checkout
        if (
            $booking->trang_thai !== 'da_xac_nhan' ||
            !$booking->thoi_gian_checkin ||
            $booking->thoi_gian_checkout
        ) {
            return redirect()->route('profile.edit')
                ->with('error', 'Chỉ có thể yêu cầu đổi phòng khi đặt phòng đã được xác nhận, đã check-in và chưa check-out.');
        }

        // 3. Đã có yêu cầu chờ duyệt?
        $daCoYeuCauChoDuyet = $booking->yeuCauDoiPhongs()
            ->where('trang_thai', 'cho_duyet')
            ->exists();

        if ($daCoYeuCauChoDuyet) {
            return redirect()->route('profile.edit')
                ->with('error', 'Bạn đã gửi một yêu cầu đổi phòng và đang chờ admin duyệt.');
        }

        // 4. Validate input
        $request->validate([
            'phong_cu_id'  => 'required|exists:phong,id',
            'phong_moi_id' => 'required|exists:phong,id|different:phong_cu_id',
            'so_nguoi_lon_moi' => 'nullable|integer|min:' . ($booking->so_nguoi ?? 2) . '|max:4',
            'so_tre_em_moi' => 'nullable|integer|min:' . ($booking->so_tre_em ?? 0) . '|max:4',
            'so_em_be_moi' => 'nullable|integer|min:' . ($booking->so_em_be ?? 0) . '|max:4',
            'ly_do'        => [
                'required',
                'string',
                'min:10',
                'max:500',
                function ($attribute, $value, $fail) {
                    $trimmed = trim($value);

                    if (preg_match('/^[^A-Za-zÀ-ỹ0-9]/u', $trimmed)) {
                        $fail('Lý do đổi phòng không được bắt đầu bằng ký tự đặc biệt.');
                        return;
                    }

                    if (preg_match('/^[0-9\s]+$/u', $trimmed)) {
                        $fail('Lý do đổi phòng không thể chỉ gồm chữ số.');
                        return;
                    }

                    if (preg_match('/^[\p{P}\p{S}\s]+$/u', $trimmed)) {
                        $fail('Lý do đổi phòng không thể chỉ gồm ký tự đặc biệt.');
                        return;
                    }

                    if (!preg_match('/\p{L}/u', $trimmed)) {
                        $fail('Lý do đổi phòng phải chứa ít nhất một ký tự chữ.');
                    }
                },
            ],
        ], [
            'phong_cu_id.required'  => 'Vui lòng chọn phòng hiện tại.',
            'phong_moi_id.required' => 'Vui lòng chọn phòng muốn đổi sang.',
            'phong_moi_id.different'=> 'Phòng mới phải khác phòng hiện tại.',
            'ly_do.required'        => 'Vui lòng nhập lý do đổi phòng.',
            'ly_do.min'             => 'Lý do đổi phòng phải có ít nhất 10 ký tự.',
        ]);

        // 5. Kiểm tra phòng cũ có thuộc booking không
        $booking->load('phongs');

        if (!$booking->phongs->contains('id', $request->phong_cu_id)) {
            return back()
                ->with('error', 'Phòng hiện tại không thuộc đặt phòng này.')
                ->withInput();
        }

        try {
            DB::transaction(function () use ($request, $booking, $user) {

                // Lock record phòng mới để tránh race condition
                $phongMoi = Phong::with('loaiPhong')->where('id', $request->phong_moi_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Lock record phòng cũ để lấy thông tin
                $phongCu = Phong::with('loaiPhong')->where('id', $request->phong_cu_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // a) Cho phép đổi sang TẤT CẢ loại phòng
                // Tính phí đổi phòng 10% dựa trên chênh lệch giá
                $nights = max(1, \Carbon\Carbon::parse($booking->ngay_nhan)->diffInDays(\Carbon\Carbon::parse($booking->ngay_tra)));
                
                // Giá phòng cũ (1 đêm)
                $giaPhongCu = $phongCu->loaiPhong->gia_khuyen_mai ?? $phongCu->loaiPhong->gia_co_ban ?? 0;
                $tongGiaPhongCu = $giaPhongCu * $nights;
                
                // Giá phòng mới (1 đêm)
                $giaPhongMoi = $phongMoi->loaiPhong->gia_khuyen_mai ?? $phongMoi->loaiPhong->gia_co_ban ?? 0;
                $tongGiaPhongMoi = $giaPhongMoi * $nights;
                
                // Chênh lệch giá (nếu phòng mới đắt hơn thì có phí, nếu rẻ hơn thì không có phí)
                $chenhLechGia = max(0, $tongGiaPhongMoi - $tongGiaPhongCu);
                
                // Phí đổi phòng: nếu chênh lệch giá <= 100K thì miễn phí, còn nếu > 100K thì tính theo chênh lệch giá
                $phiDoiPhongMacDinh = 100000; // 100K
                if ($chenhLechGia <= $phiDoiPhongMacDinh) {
                    // Chênh lệch giá bằng hoặc ít hơn 100K => miễn phí đổi phòng
                    $phiDoiPhong = 0;
                } else {
                    // Chênh lệch giá > 100K => tính theo chênh lệch giá
                    $phiDoiPhong = $chenhLechGia;
                }

                // b) Kiểm tra lại 1 lần nữa xem phòng còn trống trong khoảng ngày không
                $hasConflict = DatPhong::whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                    ->whereHas('phongs', function ($q) use ($phongMoi) {
                        $q->where('phong_id', $phongMoi->id);
                    })
                    ->where('ngay_tra', '>', $booking->ngay_nhan)
                    ->where('ngay_nhan', '<', $booking->ngay_tra)
                    ->exists();

                if ($hasConflict) {
                    // Nếu có booking conflict => quăng exception để rollback
                    throw new \RuntimeException('Phòng mới bạn chọn đã có khách khác đặt trong khoảng thời gian này, vui lòng chọn phòng khác.');
                }

                // c) Kiểm tra lại vẫn chưa có yêu cầu chờ duyệt nào khác (trong cùng transaction)
                $daCoYeuCauChoDuyet = $booking->yeuCauDoiPhongs()
                    ->where('trang_thai', 'cho_duyet')
                    ->lockForUpdate()
                    ->exists();

                if ($daCoYeuCauChoDuyet) {
                    throw new \RuntimeException('Bạn đã gửi một yêu cầu đổi phòng và đang chờ admin duyệt.');
                }

                // d) Lấy số người mới (tính từ người lớn, trẻ em, em bé)
                $soNguoiLonMoi = $request->input('so_nguoi_lon_moi') 
                    ? (int)$request->input('so_nguoi_lon_moi') 
                    : ($booking->so_nguoi ?? 2);
                $soTreEmMoi = $request->input('so_tre_em_moi') 
                    ? (int)$request->input('so_tre_em_moi') 
                    : ($booking->so_tre_em ?? 0);
                $soEmBeMoi = $request->input('so_em_be_moi') 
                    ? (int)$request->input('so_em_be_moi') 
                    : ($booking->so_em_be ?? 0);
                
                // Tổng số người mới = số người lớn mới
                $soNguoiMoi = $soNguoiLonMoi;

                // d) Tạo yêu cầu mới
                $yeuCauData = [
                    'dat_phong_id' => $booking->id,
                    'phong_cu_id'  => $request->phong_cu_id,
                    'phong_moi_id' => $phongMoi->id,
                    'ly_do'        => $request->ly_do,
                    'phi_doi_phong' => $phiDoiPhong,
                    'so_nguoi_moi' => $soNguoiMoi,
                    'so_tre_em_moi' => $soTreEmMoi,
                    'so_em_be_moi' => $soEmBeMoi,
                    'so_nguoi_ban_dau' => $booking->so_nguoi ?? 2, // Lưu số người ban đầu
                    'so_tre_em_ban_dau' => $booking->so_tre_em ?? 0, // Lưu số trẻ em ban đầu
                    'so_em_be_ban_dau' => $booking->so_em_be ?? 0, // Lưu số em bé ban đầu
                    'trang_thai'   => 'cho_duyet',
                ];
                
                YeuCauDoiPhong::create($yeuCauData);
            });

        } catch (\RuntimeException $e) {
            // Lỗi validate business (race condition, phòng bận, ...)
            return back()
                ->with('error', $e->getMessage())
                ->withInput();
        } catch (\Throwable $e) {
            // Lỗi hệ thống
            \Log::error('Lỗi tạo yêu cầu đổi phòng', [
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->with('error', 'Có lỗi xảy ra khi gửi yêu cầu đổi phòng. Vui lòng thử lại sau.')
                ->withInput();
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Yêu cầu đổi phòng đã được gửi. Vui lòng chờ admin duyệt.');
    }
}
