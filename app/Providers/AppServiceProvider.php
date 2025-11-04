<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
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
        // Chia sẻ danh sách loại phòng cho menu client-nav
        View::composer('partials.client-nav', function ($view) {
            $menuLoaiPhongs = LoaiPhong::where('trang_thai', 'hoat_dong')
                ->orderBy('ten_loai')
                ->get(['id','ten_loai']);
            $view->with('menuLoaiPhongs', $menuLoaiPhongs);
        });

        // Chia sẻ số lượng đặt phòng chờ xác nhận cho header admin
        View::composer('partials.admin.header', function ($view) {
            $pendingQuery = DatPhong::with(['loaiPhong'])
                ->where('trang_thai', 'cho_xac_nhan')
                ->orderBy('ngay_dat', 'desc');
            $pendingBookingCount = (clone $pendingQuery)->count();
            $pendingRecentBookings = (clone $pendingQuery)->take(5)->get();

            $view->with('pendingBookingCount', $pendingBookingCount)
                 ->with('pendingRecentBookings', $pendingRecentBookings);
        });
    }
}
