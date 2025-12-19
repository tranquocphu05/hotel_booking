<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use App\Models\LoaiPhong;
use App\Models\DatPhong;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Chia sẻ danh sách loại phòng cho menu client-nav và header search form (cached 1 giờ)
        View::composer(['partials.client-nav', 'client.header.header'], function ($view) {
            $menuLoaiPhongs = Cache::remember('menu_loai_phongs', 3600, function () {
                return LoaiPhong::where('trang_thai', 'hoat_dong')
                    ->orderBy('ten_loai')
                    ->get(['id','ten_loai']);
            });
            $view->with('menuLoaiPhongs', $menuLoaiPhongs);
        });

        // Chia sẻ số lượng đặt phòng chờ xác nhận cho header admin (cached 30 giây)
        View::composer('partials.admin.header', function ($view) {
            $userId = Auth::check() ? Auth::id() : 0;
            $cacheKey = 'admin_pending_bookings_' . $userId;
            $data = Cache::remember($cacheKey, 30, function () {
                $pendingQuery = DatPhong::with(['loaiPhong'])
                    ->where('trang_thai', 'cho_xac_nhan')
                    ->orderBy('ngay_dat', 'desc');
                return [
                    'count' => (clone $pendingQuery)->count(),
                    'recent' => (clone $pendingQuery)->take(5)->get(),
                ];
            });

            $view->with('pendingBookingCount', $data['count'])
                 ->with('pendingRecentBookings', $data['recent']);
        });

        // Helper function để kiểm tra quyền trong views
        Blade::if('hasPermission', function ($action) {
            $user = Auth::user();
            if (!$user) {
                return false;
            }

            $role = $user->vai_tro;

            // Admin có tất cả quyền
            if ($role === 'admin') {
                return true;
            }

            // Kiểm tra quyền theo vai trò
            $permissions = [
                'nhan_vien' => [
                    'loai_phong.view' => true,
                    'loai_phong.edit' => false,
                    'loai_phong.delete' => false,
                    'loai_phong.create' => false,
                    'phong.view' => true,
                    'phong.update_status' => true,
                    'phong.create' => false,
                    'phong.edit' => false,
                    'phong.delete' => false,
                    'service.view' => true,
                    'service.create' => false,
                    'service.edit' => false,
                    'service.delete' => false,
                    'booking.create' => true,
                    'invoice.create' => true,
                    'invoice.edit' => false,
                    'revenue.view_own' => false, // Nhân viên không xem doanh thu
                    'revenue.view_total' => false,
                    'customer.view' => true,
                    'customer.create' => false,
                    'customer.edit' => false,
                    'customer.delete' => false,
                    'review.view' => true,
                    'review.reply' => true,
                    'review.toggle' => true,
                    'voucher.view' => true,
                    'voucher.create' => false,
                    'voucher.edit' => false,
                    'voucher.delete' => false,
                    'room_change.view' => true,
                    'room_change.process' => true,
                ],
                'le_tan' => [
                    'loai_phong.view' => true,
                    'loai_phong.edit' => false,
                    'loai_phong.delete' => false,
                    'loai_phong.create' => false,
                    'phong.view' => true,
                    'phong.update_status' => false,
                    'phong.create' => false,
                    'phong.edit' => false,
                    'phong.delete' => false,
                    'phong.checkin' => true,
                    'phong.checkout' => true,
                    'service.view' => true,
                    'service.create' => false,
                    'service.edit' => false,
                    'service.delete' => false,
                    'booking.create_direct' => true,
                    'invoice.create_at_checkout' => true,
                    'invoice.edit' => false,
                    'revenue.view' => false,
                    'revenue.view_own_shift' => true, // Lễ tân xem doanh thu ca của chính họ
                    'revenue.view_total' => false, // Không xem báo cáo tổng
                    'customer.view_paying' => true,
                    'customer.create' => false,
                    'customer.edit' => false,
                    'customer.delete' => false,
                    'review.view' => true,
                    'review.reply' => false,
                    'review.toggle' => false,
                    'voucher.view' => true,
                    'voucher.create' => false,
                    'voucher.edit' => false,
                    'voucher.delete' => false,
                    'room_change.receive' => true,
                ],
            ];

            $rolePermissions = $permissions[$role] ?? [];
            return $rolePermissions[$action] ?? false;
        });

        // Helper function để kiểm tra vai trò
        Blade::if('hasRole', function ($roles) {
            $user = Auth::user();
            if (!$user) {
                return false;
            }

            $roles = is_array($roles) ? $roles : [$roles];
            return in_array($user->vai_tro, $roles);
        });
    }
}
