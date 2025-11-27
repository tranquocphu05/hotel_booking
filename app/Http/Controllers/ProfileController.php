<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\Phong;

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
            ->with(['loaiPhong', 'phong', 'invoice'])
            ->orderBy('ngay_dat', 'desc')
            ->paginate(3);

        // Tính toán chính sách hủy cho mỗi booking đã thanh toán (để hiển thị thông tin khi hủy)
        $cancellationPolicies = [];
        foreach ($bookings as $booking) {
            if ($booking->trang_thai === 'da_xac_nhan' && $booking->invoice && $booking->invoice->trang_thai === 'da_thanh_toan') {
                $cancellationPolicies[$booking->id] = $this->calculateCancellationPolicy($booking);
            }
        }

        return view('client.profile.index', [
            'user' => $user,
            'bookings' => $bookings,
            'cancellationPolicies' => $cancellationPolicies,
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
     * Cancel a booking.
     * Cho phép hủy booking đã thanh toán theo chính sách hủy
     */
    public function cancelBooking(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'ly_do_huy' => 'required|string|min:10|max:500',
        ], [
            'ly_do_huy.required' => 'Vui lòng nhập lý do hủy phòng',
            'ly_do_huy.min' => 'Lý do hủy phải có ít nhất 10 ký tự',
            'ly_do_huy.max' => 'Lý do hủy không được vượt quá 500 ký tự',
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

        // Tính toán chính sách hoàn tiền nếu booking đã thanh toán
        $refundInfo = null;
        $invoice = $booking->invoice;
        if ($invoice && $invoice->trang_thai === 'da_thanh_toan') {
            $refundInfo = $this->calculateCancellationPolicy($booking);

            // Kiểm tra xem có thể hủy không (ví dụ: đã quá ngày nhận phòng hoặc đã check-in)
            if (!$refundInfo['can_cancel']) {
                return Redirect::route('profile.edit')->with('error', $refundInfo['message']);
            }
        }

        // Update booking status and free up rooms
        try {
            DB::transaction(function () use ($booking, $request, $refundInfo) {
                // Load relationships
                $booking->load(['phong', 'loaiPhong', 'invoice']);

                // Validate status transition
                try {
                    $booking->validateStatusTransition('da_huy');
                } catch (\Illuminate\Validation\ValidationException $e) {
                    // Nếu validation fail, throw lại để transaction rollback
                    throw $e;
                }

            // BUG FIX: Use fresh $booking->invoice instead of stale $invoice from outer scope
            $invoice = $booking->invoice;

            // Xử lý hoàn tiền nếu booking đã thanh toán
            $ghiChuHoanTien = null;
            if ($invoice && $invoice->trang_thai === 'da_thanh_toan' && $refundInfo && $refundInfo['refund_amount'] > 0) {
                // Cập nhật invoice status thành hoàn tiền
                // con_lai = số tiền đã thanh toán - số tiền hoàn lại
                // Nếu hoàn đủ hoặc nhiều hơn, thì con_lai = 0 (khách không còn nợ)
                $daThanhToan = $invoice->da_thanh_toan ?? 0;
                $conLai = max(0, $daThanhToan - $refundInfo['refund_amount']);
                
                $invoice->update([
                    'trang_thai' => 'hoan_tien',
                    'con_lai' => $conLai, // Số tiền còn lại sau khi hoàn (0 nếu đã hoàn đủ)
                ]);

                // Ghi chú về hoàn tiền
                $ghiChuHoanTien = sprintf(
                    "Hoàn tiền: %s%% (%s VNĐ). %s",
                    $refundInfo['refund_percentage'],
                    number_format($refundInfo['refund_amount'], 0, ',', '.'),
                    $refundInfo['message']
                );

                // Tạo bản ghi thanh toán cho hoàn tiền
                \App\Models\ThanhToan::create([
                    'hoa_don_id' => $invoice->id,
                    'so_tien' => -$refundInfo['refund_amount'], // Số âm để thể hiện hoàn tiền
                    'ngay_thanh_toan' => now(),
                    'trang_thai' => 'success', // Sử dụng 'success' vì enum chỉ có ['pending','success','fail']
                    'ghi_chu' => $ghiChuHoanTien,
                ]);
            }

            // Update booking status
            $booking->trang_thai = 'da_huy';
            $booking->ly_do_huy = $request->ly_do_huy;
            $booking->ngay_huy = now();
            $booking->ghi_chu_hoan_tien = $ghiChuHoanTien;
            $booking->save();

            // Free up room via phong_id (legacy)
            if ($booking->phong_id && $booking->phong) {
                // Kiểm tra xem phòng có đang được đặt cho booking khác không
                $hasOtherBooking = \App\Models\DatPhong::where('id', '!=', $booking->id)
                    ->whereHas('phongs', function($q) use ($booking) {
                        $q->where('phong_id', $booking->phong_id);
                    })
                    ->where(function($q) use ($booking) {
                        $q->where('ngay_tra', '>', $booking->ngay_nhan)
                          ->where('ngay_nhan', '<', $booking->ngay_tra);
                    })
                    ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                    ->exists();

                if (!$hasOtherBooking) {
                    $booking->phong->update(['trang_thai' => 'trong']);
                }
            }

            // Free up rooms via pivot table
            $phongIds = $booking->getPhongIds();
            foreach ($phongIds as $phongId) {
                $phong = Phong::find($phongId);
                if ($phong) {
                    // Kiểm tra xem phòng có đang được đặt cho booking khác không
                    $hasOtherBooking = \App\Models\DatPhong::where('id', '!=', $booking->id)
                        ->whereHas('phongs', function($q) use ($phongId) {
                            $q->where('phong_id', $phongId);
                        })
                        ->where(function($q) use ($booking) {
                            $q->where('ngay_tra', '>', $booking->ngay_nhan)
                              ->where('ngay_nhan', '<', $booking->ngay_tra);
                        })
                        ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                        ->exists();

                    if (!$hasOtherBooking) {
                        $phong->update(['trang_thai' => 'trong']);
                    }
                }
            }

            // CRITICAL FIX: Get room types BEFORE detaching (to preserve data for recalculation)
            $roomTypes = $booking->getRoomTypes();

            // Clear pivot table relationships
            $booking->phongs()->detach();
            $booking->roomTypes()->detach();

            // Update so_luong_trong cho tất cả loại phòng trong booking
            $loaiPhongIdsToUpdate = [];
            foreach ($roomTypes as $roomType) {
                if (isset($roomType['loai_phong_id'])) {
                    $loaiPhongIdsToUpdate[] = $roomType['loai_phong_id'];
                }
            }
            if ($booking->loai_phong_id && !in_array($booking->loai_phong_id, $loaiPhongIdsToUpdate)) {
                $loaiPhongIdsToUpdate[] = $booking->loai_phong_id;
            }

            // BUG FIX #3: Recalculate so_luong_trong considering dang_don rooms (consistent with DatPhong model)
            foreach (array_unique($loaiPhongIdsToUpdate) as $loaiPhongId) {
                // Đếm phòng 'trong'
                $trongCount = Phong::where('loai_phong_id', $loaiPhongId)
                    ->where('trang_thai', 'trong')
                    ->count();

                // Đếm phòng 'dang_don' không có booking conflict trong 7 ngày tới
                $today = \Carbon\Carbon::today();
                $futureDate = $today->copy()->addDays(7);

                $dangDonAvailable = Phong::where('loai_phong_id', $loaiPhongId)
                    ->where('trang_thai', 'dang_don')
                    ->get()
                    ->filter(function($phong) use ($today, $futureDate) {
                        // Kiểm tra xem phòng có booking conflict trong 7 ngày tới không
                        $hasConflict = \App\Models\DatPhong::whereHas('phongs', function($q) use ($phong) {
                                $q->where('phong_id', $phong->id);
                            })
                            ->where(function($q) use ($today, $futureDate) {
                                $q->where('ngay_tra', '>', $today)
                                  ->where('ngay_nhan', '<', $futureDate);
                            })
                            ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                            ->exists();

                        // Phòng 'dang_don' được tính nếu không có conflict
                        return !$hasConflict;
                    })
                    ->count();

                // Tổng số phòng available = trong + dang_don (không conflict)
                $totalAvailable = $trongCount + $dangDonAvailable;

                \App\Models\LoaiPhong::where('id', $loaiPhongId)
                    ->update(['so_luong_trong' => $totalAvailable]);
            }

            // Hoàn trả voucher nếu có
            if ($booking->voucher_id) {
                $voucher = \App\Models\Voucher::find($booking->voucher_id);
                if ($voucher) {
                    $voucher->increment('so_luong');
                }
            }
            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Nếu validation fail, redirect với error message
            return Redirect::route('profile.edit')
                ->with('error', $e->getMessage() ?? 'Không thể hủy đặt phòng. Vui lòng kiểm tra lại.');
        } catch (\Exception $e) {
            // Nếu có lỗi khác, log và redirect với error message
            \Illuminate\Support\Facades\Log::error('Cancel booking error: ' . $e->getMessage(), [
                'booking_id' => $booking->id ?? null,
                'user_id' => $user->id ?? null,
                'exception' => $e
            ]);
            return Redirect::route('profile.edit')
                ->with('error', 'Có lỗi xảy ra khi hủy đặt phòng. Vui lòng thử lại sau.');
        }

        // Gửi email thông báo hủy booking với thông tin hoàn tiền
        if ($booking->email) {
            try {
                \Illuminate\Support\Facades\Mail::to($booking->email)
                    ->send(new \App\Mail\BookingCancelled($booking->fresh()->load(['loaiPhong']), $refundInfo));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Send booking cancelled mail failed: ' . $e->getMessage());
            }
        }

        // Tạo thông báo thành công với thông tin hoàn tiền
        $message = 'Hủy đặt phòng thành công!';
        if ($refundInfo && $refundInfo['refund_amount'] > 0) {
            $message .= ' Số tiền hoàn lại: ' . number_format($refundInfo['refund_amount'], 0, ',', '.') . ' VNĐ (' . $refundInfo['refund_percentage'] . '%). ' . $refundInfo['message'];
        } elseif ($refundInfo && $refundInfo['refund_amount'] == 0) {
            $message .= ' ' . $refundInfo['message'];
        }

        return Redirect::route('profile.edit')->with('success', $message);
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
            // Hủy trong ngày: Không hoàn tiền
            $policy['refund_percentage'] = 0;
            $policy['message'] = 'Hủy trong ngày nhận phòng không được hoàn tiền';
        }

        // Calculate amounts after setting percentage (consistent with AdminDatPhongController)
        $policy['refund_amount'] = ($totalPaid * $policy['refund_percentage']) / 100;
        $policy['penalty_amount'] = $totalPaid - $policy['refund_amount'];

        return $policy;
    }
}
