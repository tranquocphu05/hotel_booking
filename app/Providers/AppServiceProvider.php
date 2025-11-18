<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
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
        // Chia sẻ danh sách loại phòng cho menu client-nav (cached 1 giờ)
        View::composer('partials.client-nav', function ($view) {
            $menuLoaiPhongs = Cache::remember('menu_loai_phongs', 3600, function () {
                return LoaiPhong::where('trang_thai', 'hoat_dong')
                    ->orderBy('ten_loai')
                    ->get(['id','ten_loai']);
            });
            $view->with('menuLoaiPhongs', $menuLoaiPhongs);
        });

        // Chia sẻ số lượng đặt phòng chờ xác nhận cho header admin (cached 30 giây)
        View::composer('partials.admin.header', function ($view) {
            $cacheKey = 'admin_pending_bookings_' . auth()->id();
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
    }
}
