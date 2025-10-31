<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\DatPhong;
use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RevenueController extends Controller
{
    public function index(Request $request)
    {
        // Lấy tháng và năm từ request, mặc định là tháng hiện tại
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        
        // Tính toán doanh thu theo các khoảng thời gian
        $revenueData = $this->calculateRevenueData($month, $year);
        
        // Lấy danh sách đặt phòng đã thanh toán trong tháng
        $paidBookings = DatPhong::with(['phong', 'phong.loaiPhong', 'invoice'])
            ->whereHas('invoice', function($query) {
                $query->where('trang_thai', 'da_thanh_toan');
            })
            ->whereMonth('ngay_dat', $month)
            ->whereYear('ngay_dat', $year)
            ->orderBy('ngay_dat', 'desc')
            ->paginate(10);
        
        // Thống kê theo loại phòng
        $roomTypeStats = $this->getRoomTypeStats($month, $year);
        
        // Thống kê theo ngày trong tháng
        $dailyStats = $this->getDailyStats($month, $year);
        
        return view('admin.revenue.index', compact(
            'revenueData', 
            'paidBookings', 
            'roomTypeStats', 
            'dailyStats',
            'month',
            'year'
        ));
    }
    
    private function calculateRevenueData($month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        // Doanh thu tháng này
        $currentMonthRevenue = DatPhong::whereHas('invoice', function($query) {
            $query->where('trang_thai', 'da_thanh_toan');
        })
        ->whereMonth('ngay_dat', $month)
        ->whereYear('ngay_dat', $year)
        ->sum('tong_tien');
        
        // Doanh thu tháng trước
        $previousMonth = $month == 1 ? 12 : $month - 1;
        $previousYear = $month == 1 ? $year - 1 : $year;
        
        $previousMonthRevenue = DatPhong::whereHas('invoice', function($query) {
            $query->where('trang_thai', 'da_thanh_toan');
        })
        ->whereMonth('ngay_dat', $previousMonth)
        ->whereYear('ngay_dat', $previousYear)
        ->sum('tong_tien');
        
        // Tính phần trăm tăng trưởng
        $growthRate = $previousMonthRevenue > 0 
            ? round((($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100, 1)
            : 0;
        
        // Doanh thu theo ngày trong tháng
        $dailyRevenue = [];
        for ($day = 1; $day <= $endDate->day; $day++) {
            $dayRevenue = DatPhong::whereHas('invoice', function($query) {
                $query->where('trang_thai', 'da_thanh_toan');
            })
            ->whereDay('ngay_dat', $day)
            ->whereMonth('ngay_dat', $month)
            ->whereYear('ngay_dat', $year)
            ->sum('tong_tien');
            
            $dailyRevenue[] = [
                'day' => $day,
                'revenue' => $dayRevenue
            ];
        }
        
        return [
            'current_month' => $currentMonthRevenue,
            'previous_month' => $previousMonthRevenue,
            'growth_rate' => $growthRate,
            'daily_revenue' => $dailyRevenue,
            'total_bookings' => DatPhong::whereHas('invoice', function($query) {
                $query->where('trang_thai', 'da_thanh_toan');
            })
            ->whereMonth('ngay_dat', $month)
            ->whereYear('ngay_dat', $year)
            ->count(),
            'average_booking_value' => $currentMonthRevenue > 0 
                ? round($currentMonthRevenue / DatPhong::whereHas('invoice', function($query) {
                    $query->where('trang_thai', 'da_thanh_toan');
                })
                ->whereMonth('ngay_dat', $month)
                ->whereYear('ngay_dat', $year)
                ->count())
                : 0
        ];
    }
    
    private function getRoomTypeStats($month, $year)
    {
        return DatPhong::with(['phong.loaiPhong'])
            ->whereHas('invoice', function($query) {
                $query->where('trang_thai', 'da_thanh_toan');
            })
            ->whereMonth('ngay_dat', $month)
            ->whereYear('ngay_dat', $year)
            ->get()
            ->groupBy('phong.loaiPhong.ten_loai')
            ->map(function($bookings, $roomType) {
                return [
                    'room_type' => $roomType,
                    'bookings_count' => $bookings->count(),
                    'total_revenue' => $bookings->sum('tong_tien'),
                    'average_revenue' => $bookings->avg('tong_tien')
                ];
            })
            ->values();
    }
    
    private function getDailyStats($month, $year)
    {
        $stats = [];
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        for ($day = 1; $day <= $endDate->day; $day++) {
            $dayBookings = DatPhong::whereHas('invoice', function($query) {
                $query->where('trang_thai', 'da_thanh_toan');
            })
            ->whereDay('ngay_dat', $day)
            ->whereMonth('ngay_dat', $month)
            ->whereYear('ngay_dat', $year)
            ->get();
            
            $stats[] = [
                'day' => $day,
                'date' => Carbon::create($year, $month, $day)->format('d/m/Y'),
                'bookings_count' => $dayBookings->count(),
                'revenue' => $dayBookings->sum('tong_tien')
            ];
        }
        
        return $stats;
    }
}









