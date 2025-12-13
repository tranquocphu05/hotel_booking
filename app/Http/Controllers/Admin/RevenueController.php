<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\DatPhong;
use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\HasRolePermissions;
use Illuminate\Support\Facades\DB;

class RevenueController extends Controller
{
    use HasRolePermissions;

    public function index(Request $request)
    {

        // Nhân viên: không có quyền xem doanh thu
        if ($this->hasRole('nhan_vien')) {
            abort(403, 'Bạn không có quyền xem doanh thu.');
        }
        
        // Lễ tân: chỉ xem doanh thu ca của chính họ (ngày hiện tại)
        $isReceptionist = $this->hasRole('le_tan');
        if ($isReceptionist) {
            $this->authorizePermission('revenue.view_own_shift');
            // Lễ tân chỉ xem doanh thu của ngày hiện tại (ca làm việc của họ)
            $startDate = Carbon::today()->format('Y-m-d');
            $endDate = Carbon::today()->format('Y-m-d');
            $month = Carbon::now()->month; // Khởi tạo để tránh lỗi
            $year = Carbon::now()->year; // Khởi tạo để tránh lỗi
        } else {
            // Lấy tháng và năm từ request, mặc định là tháng hiện tại
            $month = $request->get('month', Carbon::now()->month);
            $year = $request->get('year', Carbon::now()->year);
            
            // Lấy ngày bắt đầu và kết thúc nếu có
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
        }
        
        // Nếu có ngày bắt đầu và kết thúc, ưu tiên lọc theo ngày
        if ($startDate && $endDate) {
            $startDateParsed = Carbon::parse($startDate)->startOfDay();
            $endDateParsed = Carbon::parse($endDate)->endOfDay();
            
            $paidBookings = DatPhong::with(['loaiPhong', 'invoice'])
                ->whereHas('invoice', function($query) {
                    $query->where('trang_thai', 'da_thanh_toan');
                })
                ->whereBetween('ngay_dat', [$startDateParsed, $endDateParsed])
                ->orderBy('ngay_dat', 'desc')
                ->paginate(10);
                
            // Tính toán doanh thu theo ngày
            $revenueData = $this->calculateDateRangeRevenue($startDateParsed, $endDateParsed);
            $roomTypeStats = $this->getDateRangeRoomTypeStats($startDateParsed, $endDateParsed);
            $dailyStats = $this->getDateRangeDailyStats($startDateParsed, $endDateParsed);
        } else {
            // Nếu không có ngày cụ thể, lấy theo tháng
            $month = $request->get('month', Carbon::now()->month);
            $year = $request->get('year', Carbon::now()->year);
            $startDateParsed = Carbon::create($year, $month, 1)->startOfMonth();
            $endDateParsed = Carbon::create($year, $month, 1)->endOfMonth();

            $paidBookings = DatPhong::with(['loaiPhong', 'invoice'])
                ->whereHas('invoice', function($query) {
                    $query->where('trang_thai', 'da_thanh_toan');
                })
                ->whereMonth('ngay_dat', $month)
                ->whereYear('ngay_dat', $year)
                ->orderBy('ngay_dat', 'desc')
                ->paginate(10);
                
            $revenueData = $this->calculateRevenueData($month, $year);
            $roomTypeStats = $this->getRoomTypeStats($month, $year);
            $dailyStats = $this->getDailyStats($month, $year);
        }
        
        // Lấy dữ liệu so sánh ngày hôm nay và hôm qua
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        
        $todayRevenue = DatPhong::whereHas('invoice', function($query) use ($today) {
                $query->where('trang_thai', 'da_thanh_toan')
                      ->whereDate('ngay_tao', $today);
            })
            ->sum('tong_tien');
            
        $yesterdayRevenue = DatPhong::whereHas('invoice', function($query) use ($yesterday) {
                $query->where('trang_thai', 'da_thanh_toan')
                      ->whereDate('ngay_tao', $yesterday);
            })
            ->sum('tong_tien');
            
        $revenueComparison = [
            'today' => $todayRevenue,
            'yesterday' => $yesterdayRevenue,
            'difference' => $todayRevenue - $yesterdayRevenue,
            'percentage' => $yesterdayRevenue > 0 ? 
                round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100, 2) : 0
        ];

        // Lấy doanh thu theo ngày
        $dailyRevenues = DatPhong::select(

                DB::raw('DATE(hoa_don.ngay_tao) as ngay'),
                DB::raw('SUM(dat_phong.tong_tien) as tong_tien'),
                DB::raw('COUNT(dat_phong.id) as so_luong')
            )
            ->join('hoa_don', 'dat_phong.id', '=', 'hoa_don.dat_phong_id')
            ->where('hoa_don.trang_thai', 'da_thanh_toan')
            ->whereBetween('hoa_don.ngay_tao', [$startDateParsed ?? $startDate, $endDateParsed ?? $endDate])

            ->groupBy('ngay')
            ->orderBy('ngay', 'desc')
            ->get();

        return view('admin.revenue.index', [
            'revenueData' => $revenueData,
            'paidBookings' => $paidBookings,
            'roomTypeStats' => $roomTypeStats,
            'dailyStats' => $dailyStats,
            'revenueComparison' => $revenueComparison,
            'month' => $month,
            'year' => $year,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'today' => $today->format('Y-m-d'),
            'yesterday' => $yesterday->format('Y-m-d'),
            'dailyRevenues' => $dailyRevenues,
            'isReceptionist' => $isReceptionist, // Truyền flag để view biết là lễ tân
        ]);
    }

    private function calculateDateRangeRevenue($startDate, $endDate)
    {
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        // Tổng doanh thu trong khoảng hiện tại
        $currentRevenue = DatPhong::whereHas('invoice', function($query) use ($startDate, $endDate) {
                $query->where('trang_thai', 'da_thanh_toan')
                      ->whereBetween('ngay_tao', [$startDate, $endDate]);
            })
            ->sum('tong_tien');

        // Xác định khoảng thời gian liền trước cùng độ dài để so sánh
        $daysInRange = $startDate->diffInDays($endDate) + 1;
        $previousEnd = $startDate->copy()->subDay();
        $previousStart = $previousEnd->copy()->subDays($daysInRange - 1)->startOfDay();

        $previousRevenue = DatPhong::whereHas('invoice', function($query) use ($previousStart, $previousEnd) {
                $query->where('trang_thai', 'da_thanh_toan')
                      ->whereBetween('ngay_tao', [$previousStart, $previousEnd]);
            })
            ->sum('tong_tien');

        // Tính phần trăm tăng trưởng giữa hai khoảng
        $growthRate = $previousRevenue > 0
            ? round((($currentRevenue - $previousRevenue) / $previousRevenue) * 100, 1)
            : 0;

        // Doanh thu theo từng ngày trong khoảng
        $dailyRevenue = [];
        $cursor = $startDate->copy();

        while ($cursor->lte($endDate)) {
            $dayStart = $cursor->copy()->startOfDay();
            $dayEnd = $cursor->copy()->endOfDay();

            $dayRevenue = DatPhong::whereHas('invoice', function($query) {
                    $query->where('trang_thai', 'da_thanh_toan');
                })
                ->whereBetween('ngay_dat', [$dayStart, $dayEnd])
                ->sum('tong_tien');

            $dailyRevenue[] = [
                'day' => (int) $cursor->format('d'),
                'revenue' => $dayRevenue,
            ];

            $cursor->addDay();
        }

        // Tổng số đơn trong khoảng
        $totalBookings = DatPhong::whereHas('invoice', function($query) use ($startDate, $endDate) {
                $query->where('trang_thai', 'da_thanh_toan')
                      ->whereBetween('ngay_tao', [$startDate, $endDate]);
            })
            ->count();

        $averageBookingValue = $totalBookings > 0
            ? round($currentRevenue / $totalBookings)
            : 0;

        return [
            'current_month' => $currentRevenue,
            'previous_month' => $previousRevenue,
            'growth_rate' => $growthRate,
            'daily_revenue' => $dailyRevenue,
            'total_bookings' => $totalBookings,
            'average_booking_value' => $averageBookingValue,
        ];
    }

    private function getDateRangeRoomTypeStats($startDate, $endDate)
    {
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        return DatPhong::with(['loaiPhong'])
            ->whereHas('invoice', function($query) use ($startDate, $endDate) {
                $query->where('trang_thai', 'da_thanh_toan')
                      ->whereBetween('ngay_tao', [$startDate, $endDate]);
            })
            ->whereBetween('ngay_dat', [$startDate, $endDate])
            ->get()
            ->groupBy('loaiPhong.ten_loai')
            ->map(function($bookings, $roomType) {
                return [
                    'room_type' => $roomType,
                    'bookings_count' => $bookings->count(),
                    'total_revenue' => $bookings->sum('tong_tien'),
                    'average_revenue' => $bookings->avg('tong_tien'),
                ];
            })
            ->values();
    }

    private function getDateRangeDailyStats($startDate, $endDate)
    {
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        $stats = [];
        $cursor = $startDate->copy();

        while ($cursor->lte($endDate)) {
            $dayStart = $cursor->copy()->startOfDay();
            $dayEnd = $cursor->copy()->endOfDay();

            $dayBookings = DatPhong::whereHas('invoice', function($query) {
                    $query->where('trang_thai', 'da_thanh_toan');
                })
                ->whereBetween('ngay_dat', [$dayStart, $dayEnd])
                ->get();

            $stats[] = [
                'day' => (int) $cursor->format('d'),
                'date' => $cursor->format('d/m/Y'),
                'bookings_count' => $dayBookings->count(),
                'revenue' => $dayBookings->sum('tong_tien'),
            ];

            $cursor->addDay();
        }

        return $stats;
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
        return DatPhong::with(['loaiPhong'])
            ->whereHas('invoice', function($query) {
                $query->where('trang_thai', 'da_thanh_toan');
            })
            ->whereMonth('ngay_dat', $month)
            ->whereYear('ngay_dat', $year)
            ->get()
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
}











