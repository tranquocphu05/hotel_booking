<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

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
            ->with(['loaiPhong', 'phong'])
            ->orderBy('ngay_dat', 'desc')
            ->paginate(3);

        return view('client.profile.index', [
            'user' => $user,
            'bookings' => $bookings,
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

        // Find booking
        $booking = \App\Models\DatPhong::where('id', $id)
            ->where('nguoi_dung_id', $user->id)
            ->first();

        if (!$booking) {
            return Redirect::route('profile.edit')->with('error', 'Không tìm thấy đặt phòng!');
        }

        // Check if booking can be cancelled - CHỈ CHO PHÉP HủY PHÒNG CHỜ XÁC NHẬN
        if ($booking->trang_thai !== 'cho_xac_nhan') {
            return Redirect::route('profile.edit')->with('error', 'Chỉ có thể hủy đặt phòng đang chờ xác nhận!');
        }

        // Update booking status and free up rooms
        DB::transaction(function () use ($booking, $request) {
            // Load relationships
            $booking->load(['phong', 'loaiPhong']);

            // Update booking status
            $booking->trang_thai = 'da_huy';
            $booking->ly_do_huy = $request->ly_do_huy;
            $booking->ngay_huy = now();
            $booking->save();

            // Free up room via phong_id (legacy)
            if ($booking->phong_id && $booking->phong) {
                $booking->phong->update(['trang_thai' => 'trong']);
            }

            // Free up rooms via pivot table (getPhongIds reads from booking_rooms)
            $phongIds = $booking->getPhongIds();
            foreach ($phongIds as $phongId) {
                $phong = Phong::find($phongId);
                if ($phong) {
                    $phong->update(['trang_thai' => 'trong']);
                }
            }
            
            // CRITICAL FIX: Clear pivot table relationships instead of JSON field
            $booking->phongs()->detach();
            $booking->roomTypes()->detach();
            $booking->save();

            // Update so_luong_trong in loai_phong
            if ($booking->loaiPhong) {
                $trongCount = \App\Models\Phong::where('loai_phong_id', $booking->loai_phong_id)
                    ->where('trang_thai', 'trong')
                    ->count();
                $booking->loaiPhong->update(['so_luong_trong' => $trongCount]);
            }
        });

        return Redirect::route('profile.edit')->with('success', 'Hủy đặt phòng thành công!');
    }
}
