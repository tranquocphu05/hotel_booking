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

        // 4. Danh sách phòng trống cùng loại trong khoảng ngày booking
        $availableRooms = Phong::query()
            ->where('loai_phong_id', $booking->loai_phong_id)
            ->whereIn('trang_thai', ['trong', 'dang_don'])
            ->whereDoesntHave('datPhongs', function ($q) use ($booking) {
                $q->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                    ->where('ngay_tra', '>', $booking->ngay_nhan)
                    ->where('ngay_nhan', '<', $booking->ngay_tra);
            })
            ->orderBy('ten_phong')
            ->get();

        return view('client.yeu_cau_doi_phong.create', compact(
            'booking',
            'phongHienTai',
            'availableRooms'
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
                $phongMoi = Phong::where('id', $request->phong_moi_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // a) Chỉ cho phép đổi sang phòng cùng loại (nếu bạn muốn)
                if ($phongMoi->loai_phong_id != $booking->loai_phong_id) {
                    throw new \RuntimeException('Phòng mới phải cùng loại với phòng hiện tại.');
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

                // d) Tạo yêu cầu mới
                YeuCauDoiPhong::create([
                    'dat_phong_id' => $booking->id,
                    'phong_cu_id'  => $request->phong_cu_id,
                    'phong_moi_id' => $phongMoi->id,
                    'ly_do'        => $request->ly_do,
                    'trang_thai'   => 'cho_duyet',
                ]);
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
