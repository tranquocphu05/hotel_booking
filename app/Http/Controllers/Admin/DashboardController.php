<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DatPhong;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $role = $user ? ($user->vai_tro ?? 'admin') : 'admin';
        
        // Tính toán doanh thu theo vai trò
        $revenueData = $this->calculateRevenueByRole($role);
        
        return view('admin.dashboard', [
            'revenueData' => $revenueData,
            'userRole' => $role,
        ]);
    }
    
    private function calculateRevenueByRole($role)
    {
        $today = Carbon::today();
        
        if ($role === 'le_tan') {
            // Lễ tân: chỉ xem doanh thu ca hôm nay (các hóa đơn đã thanh toán được tạo hôm nay)
            $todayRevenue = Invoice::where('trang_thai', 'da_thanh_toan')
                ->whereDate('ngay_tao', $today)
                ->sum('tong_tien');
            
            return [
                'total' => $todayRevenue,
                'show_trend' => false, // Không hiển thị trend
                'label' => 'Doanh thu ca hôm nay',
            ];
        } elseif ($role === 'nhan_vien') {
            // Nhân viên: không xem doanh thu
            return [
                'total' => 0,
                'show_trend' => false,
                'label' => null, // Ẩn card
            ];
        } else {
            // Admin: xem tổng doanh thu và trend
            $currentMonthRevenue = DatPhong::whereHas('invoice', function($query) {
                    $query->where('trang_thai', 'da_thanh_toan');
                })
                ->whereMonth('ngay_dat', Carbon::now()->month)
                ->whereYear('ngay_dat', Carbon::now()->year)
                ->sum('tong_tien');
            
            $previousMonth = Carbon::now()->subMonth();
            $previousMonthRevenue = DatPhong::whereHas('invoice', function($query) {
                    $query->where('trang_thai', 'da_thanh_toan');
                })
                ->whereMonth('ngay_dat', $previousMonth->month)
                ->whereYear('ngay_dat', $previousMonth->year)
                ->sum('tong_tien');
            
            $growthRate = $previousMonthRevenue > 0
                ? round((($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100, 1)
                : 0;
            
            return [
                'total' => $currentMonthRevenue,
                'show_trend' => true,
                'growth_rate' => $growthRate,
                'label' => 'Doanh thu',
            ];
        }
    }
}
