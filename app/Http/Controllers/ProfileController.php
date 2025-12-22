<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\Phong;
use App\Models\YeuCauDoiPhong;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        // Lấy lịch sử đặt phòng - Mỗi trang hiển thị 3 phòng
        $bookings = \App\Models\DatPhong::where('nguoi_dung_id', $user->id)
            ->with(['loaiPhong', 'phong', 'phongs', 'invoice', 'yeuCauDoiPhongs'])
            ->orderBy('ngay_dat', 'desc')
            ->paginate(3);


        // Tính toán chính sách hủy cho mỗi booking đã thanh toán (để hiển thị thông tin khi hủy)
        $cancellationPolicies = [];
        foreach ($bookings as $booking) {
            if (
                $booking->trang_thai === 'da_xac_nhan'
                && $booking->invoice
                && $booking->invoice->trang_thai === 'da_thanh_toan'
            ) {
                $cancellationPolicies[$booking->id] = $this->calculateCancellationPolicy($booking);
            }
        }

        return view('client.profile.index', [
            'user'                     => $user,
            'bookings'                 => $bookings,
            'cancellationPolicies'     => $cancellationPolicies,
        ]);
    }


    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Update user information
        $user->ho_ten = $request->ho_ten;
        $user->email = $request->email;
        $user->sdt = $request->sdt;
        $user->cccd = $request->cccd;
        $user->dia_chi = $request->dia_chi;

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('success', 'Cập nhật thông tin cá nhân thành công!');
    }

    /**
     * Update user avatar.
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();

        // Xóa ảnh cũ nếu có
        if ($user->img && file_exists(public_path($user->img))) {
            unlink(public_path($user->img));
        }

        // Upload ảnh mới
        if ($request->hasFile('avatar')) {
            $image = $request->file('avatar');
            $imageName = 'avatar_' . $user->id . '_' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/avatars'), $imageName);
            $user->img = 'uploads/avatars/' . $imageName;
            $user->save();
        }

        Cache::forget('dashboard_comments_5star');
        Cache::forget('gioi_thieu_comments');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật ảnh đại diện thành công!',
                'avatar_url' => asset($user->img)
            ]);
        }

        return Redirect::route('profile.edit')->with('success', 'Cập nhật ảnh đại diện thành công!');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Cancel a booking (client side).
     * Hiện tại CHỈ ghi nhận YÊU CẦU HỦY/HOÀN TIỀN, không hủy ngay.
     */
    public function cancelBooking(Request $request, $id): RedirectResponse
    {
        $request->validate([
                'ly_do_huy'          => 'required|string|min:10|max:500',
                'ten_chu_tai_khoan'  => [
                    'nullable',
                    'string',
                    'max:255',
                    'regex:/^[A-Za-z ]+$/u', // Chỉ cho chữ cái và khoảng trắng (không dấu)
                ],
                'so_tai_khoan'       => [
                    'nullable',
                    'regex:/^[0-9]+$/', // Chỉ cho phép chữ số
                    'max:50',
                ],
                'ten_ngan_hang'      => 'nullable|string|max:255',
                'ly_do_hoan'         => 'nullable|string|max:500',
                'so_tien_hoan'       => 'nullable|numeric|min:0',
            ], [
                'ly_do_huy.required' => 'Vui lòng nhập lý do hủy phòng',
                'ly_do_huy.min'      => 'Lý do hủy phải có ít nhất 10 ký tự',
                'ly_do_huy.max'      => 'Lý do hủy không được vượt quá 500 ký tự',

                'ten_chu_tai_khoan.regex' => 'Tên chủ tài khoản chỉ được chứa chữ cái không dấu và khoảng trắng',
                'so_tai_khoan.regex'      => 'Số tài khoản chỉ được chứa chữ số',
            ]);

        $user = $request->user();

        // Find booking - refresh để lấy dữ liệu mới nhất
        $booking = \App\Models\DatPhong::where('id', $id)
            ->where('nguoi_dung_id', $user->id)
            ->first();

        if (!$booking) {
            return Redirect::route('profile.edit')->with('error', 'Không tìm thấy đặt phòng!');
        }

        // Kiểm tra xem booking đã bị hủy chưa (tránh hủy 2 lần)
        if ($booking->trang_thai === 'da_huy') {
            return Redirect::route('profile.edit')->with('info', 'Đặt phòng này đã được hủy trước đó.');
        }

        // Kiểm tra không thể hủy booking đã check-in
        if ($booking->thoi_gian_checkin) {
            return Redirect::route('profile.edit')->with('error', 'Không thể hủy booking đã check-in. Vui lòng liên hệ quản trị viên để thực hiện check-out.');
        }

        // Cho phép hủy booking chờ xác nhận hoặc đã xác nhận (nhưng chưa check-in)
        // validateStatusTransition() sẽ kiểm tra không cho hủy nếu đã check-in
        if (!in_array($booking->trang_thai, ['cho_xac_nhan', 'da_xac_nhan'])) {
            return Redirect::route('profile.edit')->with('error', 'Chỉ có thể hủy đặt phòng đang chờ xác nhận hoặc đã xác nhận (chưa check-in).');
        }

        // Tính toán chính sách hoàn tiền nếu booking đã thanh toán (để hiển thị số tiền dự kiến)
        $refundInfo = null;
        $invoice = $booking->invoice;
        if ($invoice && $invoice->trang_thai === 'da_thanh_toan') {
            $refundInfo = $this->calculateCancellationPolicy($booking);

            // Nếu không được phép hủy theo policy (ví dụ: đã quá ngày nhận phòng hoặc đã check-in) thì chặn luôn
            if (!$refundInfo['can_cancel']) {
                return Redirect::route('profile.edit')->with('error', $refundInfo['message']);
            }
        }

        // CHỈ LƯU YÊU CẦU HỦY/HOÀN TIỀN, KHÔNG HỦY THỰC SỰ
        try {
            DB::transaction(function () use ($booking, $request, $refundInfo) {
                $bankInfoLines = [];
                if ($request->filled('ten_chu_tai_khoan')) {
                    $bankInfoLines[] = 'Chủ tài khoản: ' . $request->ten_chu_tai_khoan;
                }
                if ($request->filled('so_tai_khoan')) {
                    $bankInfoLines[] = 'Số tài khoản: ' . $request->so_tai_khoan;
                }
                if ($request->filled('ten_ngan_hang')) {
                    $bankInfoLines[] = 'Ngân hàng: ' . $request->ten_ngan_hang;
                }
                if ($request->filled('ly_do_hoan')) {
                    $bankInfoLines[] = 'Lý do hoàn: ' . $request->ly_do_hoan;
                }
                if ($refundInfo && isset($refundInfo['refund_amount'])) {
                    $bankInfoLines[] = 'Số tiền dự kiến hoàn theo chính sách: ' . number_format($refundInfo['refund_amount'], 0, ',', '.') . ' VNĐ (' . $refundInfo['refund_percentage'] . '%)';
                } elseif ($request->filled('so_tien_hoan')) {
                    $bankInfoLines[] = 'Số tiền dự kiến hoàn (client nhập): ' . number_format($request->so_tien_hoan, 0, ',', '.') . ' VNĐ';
                }

                $noiDungYeuCau = "YÊU CẦU HỦY ĐẶT PHÒNG TỪ KHÁCH HÀNG";
                $noiDungYeuCau .= "\nLý do hủy: " . $request->ly_do_huy;
                if (count($bankInfoLines)) {
                    $noiDungYeuCau .= "\n\nThông tin hoàn tiền khách cung cấp:\n" . implode("\n", $bankInfoLines);
                }

                // Lưu lý do hủy do khách nhập (để hiển thị lịch sử) nhưng KHÔNG đổi trạng thái booking
                $booking->ly_do_huy = $request->ly_do_huy;

                // Lưu chi tiết yêu cầu hoàn tiền vào ghi_chu_hoan_tien để admin xem và xử lý
                $booking->ghi_chu_hoan_tien = $noiDungYeuCau;
                $booking->save();
            });

        } catch (\Exception $e) {
            // Nếu có lỗi, log và redirect với error message
            \Illuminate\Support\Facades\Log::error('Cancel booking request error: ' . $e->getMessage(), [
                'booking_id' => $booking->id ?? null,
                'user_id' => $user->id ?? null,
                'exception' => $e
            ]);
            return Redirect::route('profile.edit')
                ->with('error', 'Có lỗi xảy ra khi gửi yêu cầu hủy đặt phòng. Vui lòng thử lại sau.');
        }

        return Redirect::route('profile.edit')->with('success', 'Đã gửi yêu cầu hủy/hoàn tiền. Bộ phận quản trị sẽ kiểm tra và xác nhận trong thời gian sớm nhất.');
    }

    /**
     * Tính toán chính sách hủy phòng (tương tự như Admin)
     */
    private function calculateCancellationPolicy($booking)
    {
        $now = \Carbon\Carbon::now();
        $checkinDate = \Carbon\Carbon::parse($booking->ngay_nhan);
        $daysUntilCheckin = $now->diffInDays($checkinDate, false);

        $policy = [
            'can_cancel' => true,
            'refund_percentage' => 0,
            'refund_amount' => 0,
            'penalty_amount' => 0,
            'message' => '',
            'days_until_checkin' => $daysUntilCheckin,
        ];

        // Kiểm tra xem khách đã check-in chưa (dựa vào thoi_gian_checkin, không phải ngày)
        // Nếu admin đã check-in cho khách thì không thể hủy
        if ($booking->thoi_gian_checkin) {
            // Use invoice amount if available, otherwise use booking amount
            $invoice = $booking->invoice;
            $totalPaid = $invoice ? $invoice->tong_tien : $booking->tong_tien;
            
            $policy['can_cancel'] = false;
            $policy['refund_percentage'] = 0;
            $policy['refund_amount'] = 0;
            $policy['penalty_amount'] = $totalPaid;
            $policy['message'] = 'Không thể hủy booking đã check-in. Vui lòng liên hệ quản trị viên để check-out.';
            return $policy;
        }

        // Use invoice amount if available (includes fees), otherwise use booking amount
        // BUG FIX #1: Consistent calculation with AdminDatPhongController
        $invoice = $booking->invoice;
        $totalPaid = $invoice ? $invoice->tong_tien : $booking->tong_tien;

        // Chính sách hoàn tiền theo số ngày trước khi nhận phòng
        if ($daysUntilCheckin >= 7) {
            // Hủy trước 7 ngày: Hoàn 100%
            $policy['refund_percentage'] = 100;
            $policy['message'] = 'Hoàn 100% tiền đã thanh toán';
        } elseif ($daysUntilCheckin >= 3) {
            // Hủy trước 3-6 ngày: Hoàn 50%
            $policy['refund_percentage'] = 50;
            $policy['message'] = 'Hoàn 50% tiền đã thanh toán (phí hủy 50%)';
        } elseif ($daysUntilCheckin >= 1) {
            // Hủy trước 1-2 ngày: Hoàn 25%
            $policy['refund_percentage'] = 25;
            $policy['message'] = 'Hoàn 25% tiền đã thanh toán (phí hủy 75%)';
        } else {
            // Hủy đúng ngày nhận phòng: Cho phép hủy nhưng không hoàn tiền
            $policy['refund_percentage'] = 0;
            $policy['message'] = 'Hủy trong ngày nhận phòng không được hoàn tiền';
        }

        // Calculate amounts after setting percentage (consistent with AdminDatPhongController)
        $policy['refund_amount'] = ($totalPaid * $policy['refund_percentage']) / 100;
        $policy['penalty_amount'] = $totalPaid - $policy['refund_amount'];

        return $policy;
    }
}
