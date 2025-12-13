<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DatPhong;
use App\Models\Invoice;
use App\Models\User;
use App\Models\LoaiPhong;
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
        
        // Tính tổng đặt phòng - lấy trực tiếp từ database
        $totalBookings = DB::table('dat_phong')
            ->whereNotIn('trang_thai', ['da_huy'])
            ->count();
        
        // Tính tổng đặt phòng tháng hiện tại và tháng trước để so sánh
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $previousMonth = Carbon::now()->subMonth();
        
        $currentMonthBookings = DB::table('dat_phong')
            ->whereNotIn('trang_thai', ['da_huy'])
            ->whereMonth('ngay_dat', $currentMonth)
            ->whereYear('ngay_dat', $currentYear)
            ->count();
        
        $previousMonthBookings = DB::table('dat_phong')
            ->whereNotIn('trang_thai', ['da_huy'])
            ->whereMonth('ngay_dat', $previousMonth->month)
            ->whereYear('ngay_dat', $previousMonth->year)
            ->count();
        
        $bookingGrowthRate = $previousMonthBookings > 0
            ? round((($currentMonthBookings - $previousMonthBookings) / $previousMonthBookings) * 100, 1)
            : ($currentMonthBookings > 0 ? 100 : 0);
        
        // Tính tổng phòng - lấy trực tiếp từ database
        $totalRooms = DB::table('loai_phong')
            ->sum('so_luong_phong');
        
        $availableRooms = DB::table('loai_phong')
            ->sum('so_luong_trong');
        
        $bookedRooms = $totalRooms - $availableRooms;
        
        // Tính tổng khách hàng - lấy trực tiếp từ database
        $totalCustomers = DB::table('nguoi_dung')
            ->where('vai_tro', 'khach_hang')
            ->where('trang_thai', 'hoat_dong')
            ->count();
        
        // Tính số khách hàng đang hoạt động (có đặt phòng trong tháng này)
        $activeCustomers = DB::table('dat_phong')
            ->join('nguoi_dung', 'dat_phong.nguoi_dung_id', '=', 'nguoi_dung.id')
            ->whereMonth('dat_phong.ngay_dat', $currentMonth)
            ->whereYear('dat_phong.ngay_dat', $currentYear)
            ->whereNotIn('dat_phong.trang_thai', ['da_huy'])
            ->where('nguoi_dung.vai_tro', 'khach_hang')
            ->where('nguoi_dung.trang_thai', 'hoat_dong')
            ->distinct()
            ->count('dat_phong.nguoi_dung_id');
        
        return view('admin.dashboard', [
            'revenueData' => $revenueData,
            'userRole' => $role,
            'totalBookings' => $totalBookings,
            'bookingGrowthRate' => $bookingGrowthRate,
            'totalRooms' => $totalRooms,
            'availableRooms' => $availableRooms,
            'bookedRooms' => $bookedRooms,
            'totalCustomers' => $totalCustomers,
            'activeCustomers' => $activeCustomers,
        ]);
    }
    
    private function calculateRevenueByRole($role)
    {
        $today = Carbon::today();
        
        if ($role === 'le_tan') {
            // Lễ tân: chỉ xem doanh thu ca hôm nay (các hóa đơn đã thanh toán được tạo hôm nay)
            // Lấy trực tiếp từ database
            $todayRevenue = DB::table('hoa_don')
                ->where('trang_thai', 'da_thanh_toan')
                ->whereDate('ngay_tao', $today)
                ->sum('tong_tien');
            
            return [
                'total' => $todayRevenue ?? 0,
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
            // Admin: xem tổng doanh thu và trend - lấy trực tiếp từ database
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;
            $previousMonth = Carbon::now()->subMonth();
            
            // Doanh thu tháng hiện tại: lấy từ dat_phong có invoice đã thanh toán
            $currentMonthRevenue = DB::table('dat_phong')
                ->join('hoa_don', 'dat_phong.id', '=', 'hoa_don.dat_phong_id')
                ->where('hoa_don.trang_thai', 'da_thanh_toan')
                ->whereMonth('dat_phong.ngay_dat', $currentMonth)
                ->whereYear('dat_phong.ngay_dat', $currentYear)
                ->sum('dat_phong.tong_tien');
            
            // Doanh thu tháng trước
            $previousMonthRevenue = DB::table('dat_phong')
                ->join('hoa_don', 'dat_phong.id', '=', 'hoa_don.dat_phong_id')
                ->where('hoa_don.trang_thai', 'da_thanh_toan')
                ->whereMonth('dat_phong.ngay_dat', $previousMonth->month)
                ->whereYear('dat_phong.ngay_dat', $previousMonth->year)
                ->sum('dat_phong.tong_tien');
            
            $growthRate = $previousMonthRevenue > 0
                ? round((($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100, 1)
                : 0;
            
            return [
                'total' => $currentMonthRevenue ?? 0,
                'show_trend' => true,
                'growth_rate' => $growthRate,
                'label' => 'Doanh thu',
            ];
        }
    }
}
