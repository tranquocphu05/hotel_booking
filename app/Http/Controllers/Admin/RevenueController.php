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
        $viewType = $request->get('view', 'month'); // 'month' or 'week'

        // Tính toán doanh thu theo các khoảng thời gian
        $revenueData = $this->calculateRevenueData($month, $year);

        // Lấy danh sách đặt phòng đã thanh toán
        $paidBookingsQuery = DatPhong::with(['loaiPhong', 'invoice'])
            ->whereHas('invoice', function($query) {
                $query->where('trang_thai', 'da_thanh_toan');
            });

        if ($viewType === 'week') {
            // Lấy tuần hiện tại
            $weekNumber = $request->get('week', Carbon::now()->week);
            $paidBookingsQuery->whereYear('ngay_dat', $year)
                ->whereBetween('ngay_dat', [
                    Carbon::now()->setISODate($year, $weekNumber)->startOfWeek(),
                    Carbon::now()->setISODate($year, $weekNumber)->endOfWeek()
                ]);
        } else {
            $paidBookingsQuery->whereMonth('ngay_dat', $month)
                ->whereYear('ngay_dat', $year);
        }

        $paidBookings = $paidBookingsQuery->orderBy('ngay_dat', 'desc')->paginate(10);

        // Thống kê theo loại phòng
        $roomTypeStats = $this->getRoomTypeStats($month, $year, $viewType, $request->get('week'));

        // Thống kê theo ngày/tuần
        if ($viewType === 'week') {
            $weekNumber = $request->get('week', Carbon::now()->week);
            $dailyStats = $this->getWeeklyStats($year, $weekNumber);
            $weeklyStats = $this->getWeeklySummary($year, $month);
            // Tính revenue data cho tuần
            $revenueData = $this->calculateWeeklyRevenueData($year, $weekNumber);
        } else {
            $dailyStats = $this->getDailyStats($month, $year);
            $weeklyStats = $this->getWeeklySummary($year, $month);
            // Tính revenue data cho tháng
            $revenueData = $this->calculateRevenueData($month, $year);
        }

        $weekNumber = $viewType === 'week' ? $request->get('week', Carbon::now()->week) : null;

        return view('admin.revenue.index', compact(
            'revenueData',
            'paidBookings',
            'roomTypeStats',
            'dailyStats',
            'weeklyStats',
            'month',
            'year',
            'viewType',
            'weekNumber'
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

    private function getRoomTypeStats($month, $year, $viewType = 'month', $weekNumber = null)
    {
        $query = DatPhong::with(['loaiPhong'])
            ->whereHas('invoice', function($query) {
                $query->where('trang_thai', 'da_thanh_toan');
            });

        if ($viewType === 'week' && $weekNumber) {
            $query->whereYear('ngay_dat', $year)
                ->whereBetween('ngay_dat', [
                    Carbon::now()->setISODate($year, $weekNumber)->startOfWeek(),
                    Carbon::now()->setISODate($year, $weekNumber)->endOfWeek()
                ]);
        } else {
            $query->whereMonth('ngay_dat', $month)
                ->whereYear('ngay_dat', $year);
        }

        return $query->get()
            ->groupBy('loaiPhong.ten_loai')
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

    /**
     * Tính toán doanh thu cho tuần cụ thể
     */
    private function calculateWeeklyRevenueData($year, $weekNumber)
    {
        $startOfWeek = Carbon::now()->setISODate($year, $weekNumber)->startOfWeek();
        $endOfWeek = Carbon::now()->setISODate($year, $weekNumber)->endOfWeek();

        // Doanh thu tuần này
        $currentWeekRevenue = DatPhong::whereHas('invoice', function($query) {
            $query->where('trang_thai', 'da_thanh_toan');
        })
        ->whereBetween('ngay_dat', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
        ->sum('tong_tien');

        // Doanh thu tuần trước
        $previousWeekNumber = $weekNumber - 1;
        $previousYear = $year;
        if ($previousWeekNumber < 1) {
            $previousWeekNumber = 52;
            $previousYear = $year - 1;
        }

        $previousWeekStart = Carbon::now()->setISODate($previousYear, $previousWeekNumber)->startOfWeek();
        $previousWeekEnd = Carbon::now()->setISODate($previousYear, $previousWeekNumber)->endOfWeek();

        $previousWeekRevenue = DatPhong::whereHas('invoice', function($query) {
            $query->where('trang_thai', 'da_thanh_toan');
        })
        ->whereBetween('ngay_dat', [$previousWeekStart->toDateString(), $previousWeekEnd->toDateString()])
        ->sum('tong_tien');

        // Tính phần trăm tăng trưởng
        $growthRate = $previousWeekRevenue > 0
            ? round((($currentWeekRevenue - $previousWeekRevenue) / $previousWeekRevenue) * 100, 1)
            : 0;

        // Doanh thu theo ngày trong tuần
        $dailyRevenue = [];
        for ($i = 0; $i < 7; $i++) {
            $currentDay = $startOfWeek->copy()->addDays($i);
            
            $dayRevenue = DatPhong::whereHas('invoice', function($query) {
                $query->where('trang_thai', 'da_thanh_toan');
            })
            ->whereDate('ngay_dat', $currentDay->toDateString())
            ->sum('tong_tien');

            $dailyRevenue[] = [
                'day' => $currentDay->day,
                'revenue' => $dayRevenue
            ];
        }

        // Tổng số booking trong tuần
        $totalBookings = DatPhong::whereHas('invoice', function($query) {
            $query->where('trang_thai', 'da_thanh_toan');
        })
        ->whereBetween('ngay_dat', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
        ->count();

        return [
            'current_month' => $currentWeekRevenue, // Đổi tên nhưng giữ key để view không bị lỗi
            'previous_month' => $previousWeekRevenue,
            'growth_rate' => $growthRate,
            'daily_revenue' => $dailyRevenue,
            'total_bookings' => $totalBookings,
            'average_booking_value' => $totalBookings > 0
                ? round($currentWeekRevenue / $totalBookings)
                : 0,
            'period_label' => 'tuần này',
            'previous_period_label' => 'tuần trước',
        ];
    }

    /**
     * Lấy thống kê doanh thu theo tuần
     */
    private function getWeeklyStats($year, $weekNumber)
    {
        $stats = [];
        $startOfWeek = Carbon::now()->setISODate($year, $weekNumber)->startOfWeek();
        $endOfWeek = Carbon::now()->setISODate($year, $weekNumber)->endOfWeek();

        // Lặp qua 7 ngày trong tuần
        for ($i = 0; $i < 7; $i++) {
            $currentDay = $startOfWeek->copy()->addDays($i);
            
            $dayBookings = DatPhong::whereHas('invoice', function($query) {
                $query->where('trang_thai', 'da_thanh_toan');
            })
            ->whereDate('ngay_dat', $currentDay->toDateString())
            ->get();

            $stats[] = [
                'day' => $currentDay->day,
                'day_name' => $currentDay->locale('vi')->dayName,
                'date' => $currentDay->format('d/m/Y'),
                'bookings_count' => $dayBookings->count(),
                'revenue' => $dayBookings->sum('tong_tien')
            ];
        }

        return $stats;
    }

    /**
     * Lấy tổng hợp doanh thu theo tuần trong tháng
     */
    private function getWeeklySummary($year, $month)
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();
        
        $weeks = [];
        $currentWeekStart = $startOfMonth->copy()->startOfWeek();
        
        while ($currentWeekStart->lte($endOfMonth)) {
            $currentWeekEnd = $currentWeekStart->copy()->endOfWeek();
            
            // Chỉ lấy các ngày trong tháng hiện tại
            $weekStart = $currentWeekStart->lt($startOfMonth) ? $startOfMonth->copy() : $currentWeekStart->copy();
            $weekEnd = $currentWeekEnd->gt($endOfMonth) ? $endOfMonth->copy() : $currentWeekEnd->copy();
            
            $weekBookings = DatPhong::whereHas('invoice', function($query) {
                $query->where('trang_thai', 'da_thanh_toan');
            })
            ->whereBetween('ngay_dat', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get();

            $weeks[] = [
                'week_number' => $currentWeekStart->week,
                'start_date' => $weekStart->format('d/m'),
                'end_date' => $weekEnd->format('d/m'),
                'full_range' => $weekStart->format('d/m/Y') . ' - ' . $weekEnd->format('d/m/Y'),
                'bookings_count' => $weekBookings->count(),
                'revenue' => $weekBookings->sum('tong_tien')
            ];
            
            $currentWeekStart->addWeek();
        }

        return $weeks;
    }
}











