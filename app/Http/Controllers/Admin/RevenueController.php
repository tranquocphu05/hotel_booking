<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\DatPhong;
use App\Models\Invoice;
use App\Models\ThanhToan;
use App\Models\RefundService;
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
                
            // Tính toán doanh thu theo ngày + dòng tiền thu/hoàn
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
                
            // Tính toán doanh thu theo tháng + dòng tiền thu/hoàn
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

        // Lấy doanh thu theo ngày với công thức mới: Tổng tiền thu = Tổng tiền hoàn + Doanh thu ròng
        $dateRangeStart = $startDateParsed ?? ($startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth());
        $dateRangeEnd = $endDateParsed ?? ($endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfMonth());
        
        // Tạo danh sách các ngày trong khoảng
        $allDates = [];
        $cursor = $dateRangeStart->copy();
        while ($cursor->lte($dateRangeEnd)) {
            $allDates[] = $cursor->format('Y-m-d');
            $cursor->addDay();
        }
        
        // Tính toán cho từng ngày
        $dailyRevenues = collect($allDates)->map(function($dateStr) {
            $date = Carbon::parse($dateStr);
            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();
            
            // Tổng tiền thu trong ngày (từ invoices đã thanh toán - theo giao dịch)
            // Ưu tiên lấy từ invoices, nếu không có thì lấy từ thanh_toan
            $tongTienThuFromInvoices = Invoice::where('trang_thai', 'da_thanh_toan')
                ->whereBetween('ngay_tao', [$dayStart, $dayEnd])
                ->sum('tong_tien');
            
            $tongTienThuFromThanhToan = ThanhToan::where('trang_thai', 'success')
                ->whereBetween('ngay_thanh_toan', [$dayStart, $dayEnd])
                ->where('so_tien', '>', 0)
                ->sum('so_tien');
            
            $tongTienThu = $tongTienThuFromInvoices > 0 ? $tongTienThuFromInvoices : $tongTienThuFromThanhToan;
            
            // Tổng tiền hoàn trong ngày (từ invoices có trạng thái hoan_tien)
            $tongTienHoanFromInvoices = Invoice::where('trang_thai', 'hoan_tien')
                ->whereBetween('ngay_tao', [$dayStart, $dayEnd])
                ->sum('tong_tien');
            
            // Cũng lấy từ thanh_toan với số tiền âm
            $tongTienHoanFromPayments = abs(ThanhToan::where('trang_thai', 'success')
                ->whereBetween('ngay_thanh_toan', [$dayStart, $dayEnd])
                ->where('so_tien', '<', 0)
                ->sum('so_tien'));
            
            $tongTienHoan = $tongTienHoanFromInvoices > 0 ? $tongTienHoanFromInvoices : $tongTienHoanFromPayments;
            
            // Doanh thu ròng = Tổng tiền thu - Tổng tiền hoàn
            $doanhThuRong = $tongTienThu - $tongTienHoan;
            
            // Tổng tiền thu (theo công thức mới) = Tổng tiền hoàn + Doanh thu ròng
            $tongTienThuMoi = $tongTienHoan + $doanhThuRong;
            
            // Số lượng đơn đã thanh toán trong ngày
            $soLuong = Invoice::where('trang_thai', 'da_thanh_toan')
                ->whereBetween('ngay_tao', [$dayStart, $dayEnd])
                ->count();
            
            return (object)[
                'ngay' => $dateStr,
                'tong_tien' => $tongTienThuMoi,
                'so_luong' => $soLuong
            ];
        })->filter(function($item) {
            // Chỉ giữ lại những ngày có dữ liệu
            return $item->tong_tien > 0 || $item->so_luong > 0;
        })->values();

        // Lấy các booking bị hủy trong khoảng thời gian
        $cancelledBookings = DatPhong::with(['loaiPhong', 'user'])
            ->where('trang_thai', 'da_huy')
            ->whereBetween('ngay_dat', [$startDateParsed ?? $startDate, $endDateParsed ?? $endDate])
            ->orderBy('ngay_dat', 'desc')
            ->paginate(10, ['*'], 'cancelled_page');

        // Tính tổng doanh thu tiềm năng bị mất do hủy
        $totalCancelledRevenue = DatPhong::where('trang_thai', 'da_huy')
            ->whereBetween('ngay_dat', [$startDateParsed ?? $startDate, $endDateParsed ?? $endDate])
            ->sum('tong_tien');

        // Số lượng booking bị hủy
        $cancelledCount = DatPhong::where('trang_thai', 'da_huy')
            ->whereBetween('ngay_dat', [$startDateParsed ?? $startDate, $endDateParsed ?? $endDate])
            ->count();

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
            'isReceptionist' => $isReceptionist,
            'cancelledBookings' => $cancelledBookings,
            'totalCancelledRevenue' => $totalCancelledRevenue,
            'cancelledCount' => $cancelledCount,
        ]);
    }

    private function calculateDateRangeRevenue($startDate, $endDate)
    {
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        // Tổng doanh thu trong khoảng hiện tại (theo giá trị booking/tong_tien)
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

        // Optimize: Load all daily data at once instead of querying per day
        $cacheKeyDaily = 'daily_revenue_chart_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d');
        $dailyRevenue = \Illuminate\Support\Facades\Cache::remember($cacheKeyDaily, 300, function () use ($startDate, $endDate) {
            // Load all payments grouped by day
            $allPayments = ThanhToan::where('trang_thai', 'success')
                ->whereBetween('ngay_thanh_toan', [$startDate, $endDate])
                ->where('so_tien', '>', 0)
                ->selectRaw('DAY(ngay_thanh_toan) as day, SUM(so_tien) as total')
                ->groupBy('day')
                ->pluck('total', 'day');
            
            // Load all refunds grouped by day
            $allRefundsServices = RefundService::whereHas('invoice', function($query) use ($startDate, $endDate) {
                    $query->whereBetween('ngay_tao', [$startDate, $endDate]);
                })
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DAY(created_at) as day, SUM(total_refund) as total')
                ->groupBy('day')
                ->pluck('total', 'day');
            
            $allRefundsRooms = Invoice::where('trang_thai', 'hoan_tien')
                ->whereBetween('ngay_tao', [$startDate, $endDate])
                ->selectRaw('DAY(ngay_tao) as day, SUM(tong_tien) as total')
                ->groupBy('day')
                ->pluck('total', 'day');
            
            $allRefundsPayments = ThanhToan::where('trang_thai', 'success')
                ->whereBetween('ngay_thanh_toan', [$startDate, $endDate])
                ->where('so_tien', '<', 0)
                ->selectRaw('DAY(ngay_thanh_toan) as day, ABS(SUM(so_tien)) as total')
                ->groupBy('day')
                ->pluck('total', 'day');
            
            // Build daily revenue array
            $dailyRevenue = [];
            $cursor = $startDate->copy();
            while ($cursor->lte($endDate)) {
                $day = (int) $cursor->format('d');
                $dayPayments = $allPayments[$day] ?? 0;
                $dayRefundsServices = $allRefundsServices[$day] ?? 0;
                $dayRefundsRooms = $allRefundsRooms[$day] ?? 0;
                $dayRefundsPayments = $allRefundsPayments[$day] ?? 0;
                
                $dayRefunds = $dayRefundsServices + ($dayRefundsRooms > 0 ? $dayRefundsRooms : $dayRefundsPayments);
                $dayNetRevenue = $dayPayments - $dayRefunds;
                $dayTotalRevenue = $dayRefunds + $dayNetRevenue;
                
                $dailyRevenue[] = [
                    'day' => $day,
                    'revenue' => $dayTotalRevenue,
                ];
                
                $cursor->addDay();
            }
            
            return $dailyRevenue;
        });

        // Tổng tiền thu: Tính theo ngày trong khoảng (tổng tất cả các ngày)
        // Lấy từ invoices đã thanh toán (theo giao dịch) - tính theo ngày tạo invoice
        // Nếu không có từ invoices, fallback sang thanh_toan
        $totalPaymentsFromInvoices = Invoice::where('trang_thai', 'da_thanh_toan')
            ->whereBetween('ngay_tao', [$startDate, $endDate])
            ->sum('tong_tien');
        
        $totalPaymentsFromThanhToan = ThanhToan::where('trang_thai', 'success')
            ->whereBetween('ngay_thanh_toan', [$startDate, $endDate])
            ->where('so_tien', '>', 0)
            ->sum('so_tien');
        
        // Ưu tiên lấy từ invoices (theo giao dịch), nếu không có thì lấy từ thanh_toan
        $totalPayments = $totalPaymentsFromInvoices > 0 ? $totalPaymentsFromInvoices : $totalPaymentsFromThanhToan;

        // Tổng tiền hoàn: Số tiền hoàn lại cho khách trong khoảng ngày/tháng đó
        // Bao gồm:
        // 1. Tiền hoàn từ dịch vụ (từ refund_services) - khi khách không dùng hết dịch vụ
        $totalRefundsFromServices = RefundService::whereHas('invoice', function($query) use ($startDate, $endDate) {
                $query->whereBetween('ngay_tao', [$startDate, $endDate]);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_refund');
        
        // 2. Tiền hoàn từ phòng (từ invoices có trạng thái 'hoan_tien') - khi khách hủy phòng đã thanh toán nhưng không ở
        $totalRefundsFromRooms = Invoice::where('trang_thai', 'hoan_tien')
            ->whereBetween('ngay_tao', [$startDate, $endDate])
            ->sum('tong_tien');
        
        // 3. Cũng lấy từ thanh_toan với số tiền âm (refund) để đảm bảo đầy đủ
        $totalRefundsFromPayments = abs(ThanhToan::where('trang_thai', 'success')
            ->whereBetween('ngay_thanh_toan', [$startDate, $endDate])
            ->where('so_tien', '<', 0)
            ->sum('so_tien'));
        
        // Tổng tiền hoàn = Tiền hoàn dịch vụ + Tiền hoàn phòng
        // Ưu tiên lấy từ invoices nếu có, nếu không thì lấy từ thanh_toan
        $totalRefundsAbs = $totalRefundsFromServices + ($totalRefundsFromRooms > 0 ? $totalRefundsFromRooms : $totalRefundsFromPayments);
        
        // Doanh thu ròng = Tổng tiền thu - Tổng tiền hoàn
        $netRevenue = $totalPayments - $totalRefundsAbs;
        
        // Tổng tiền thu (theo công thức mới) = Tổng tiền hoàn + Doanh thu ròng
        // Hoặc đơn giản = Tổng tiền thu từ thanh_toan (đã tính ở trên)
        $totalRevenue = $totalPayments;

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
            'current_month'        => $currentRevenue,
            'previous_month'       => $previousRevenue,
            'growth_rate'          => $growthRate,
            'daily_revenue'        => $dailyRevenue,
            'total_bookings'       => $totalBookings,
            'average_booking_value'=> $averageBookingValue,
            'total_payments'       => $totalPayments,
            'total_refunds'        => $totalRefundsAbs,
            'net_revenue'          => $netRevenue,
            'total_revenue'        => $totalRevenue, // Tổng tiền thu = Tổng tiền hoàn + Doanh thu ròng
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

        // Doanh thu tháng này (theo tong_tien booking)
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

        // Tổng tiền thu: Tính theo ngày trong tháng (tổng tất cả các ngày)
        // Lấy từ invoices đã thanh toán (theo giao dịch) - tính theo ngày tạo invoice
        // Nếu không có từ invoices, fallback sang thanh_toan
        $totalPaymentsFromInvoices = Invoice::where('trang_thai', 'da_thanh_toan')
            ->whereBetween('ngay_tao', [$startDate, $endDate])
            ->sum('tong_tien');
        
        $totalPaymentsFromThanhToan = ThanhToan::where('trang_thai', 'success')
            ->whereBetween('ngay_thanh_toan', [$startDate, $endDate])
            ->where('so_tien', '>', 0)
            ->sum('so_tien');
        
        // Ưu tiên lấy từ invoices (theo giao dịch), nếu không có thì lấy từ thanh_toan
        $totalPayments = $totalPaymentsFromInvoices > 0 ? $totalPaymentsFromInvoices : $totalPaymentsFromThanhToan;

        // Tổng tiền hoàn: Số tiền hoàn lại cho khách trong tháng đó
        // Bao gồm:
        // 1. Tiền hoàn từ dịch vụ (từ refund_services) - khi khách không dùng hết dịch vụ
        $totalRefundsFromServices = RefundService::whereHas('invoice', function($query) use ($startDate, $endDate) {
                $query->whereBetween('ngay_tao', [$startDate, $endDate]);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_refund');
        
        // 2. Tiền hoàn từ phòng (từ invoices có trạng thái 'hoan_tien') - khi khách hủy phòng đã thanh toán nhưng không ở
        $totalRefundsFromRooms = Invoice::where('trang_thai', 'hoan_tien')
            ->whereBetween('ngay_tao', [$startDate, $endDate])
            ->sum('tong_tien');
        
        // 3. Cũng lấy từ thanh_toan với số tiền âm (refund) để đảm bảo đầy đủ
        $totalRefundsFromPayments = abs(ThanhToan::where('trang_thai', 'success')
            ->whereBetween('ngay_thanh_toan', [$startDate, $endDate])
            ->where('so_tien', '<', 0)
            ->sum('so_tien'));
        
        // Tổng tiền hoàn = Tiền hoàn dịch vụ + Tiền hoàn phòng
        // Ưu tiên lấy từ invoices nếu có, nếu không thì lấy từ thanh_toan
        $totalRefundsAbs = $totalRefundsFromServices + ($totalRefundsFromRooms > 0 ? $totalRefundsFromRooms : $totalRefundsFromPayments);
        
        // Doanh thu ròng = Tổng tiền thu - Tổng tiền hoàn
        $netRevenue = $totalPayments - $totalRefundsAbs;
        
        // Tổng tiền thu = Tổng tiền thu từ thanh_toan (đã tính ở trên)
        $totalRevenue = $totalPayments;

        $totalBookings = DatPhong::whereHas('invoice', function($query) {
                $query->where('trang_thai', 'da_thanh_toan');
            })
            ->whereMonth('ngay_dat', $month)
            ->whereYear('ngay_dat', $year)
            ->count();

        $averageBookingValue = $currentMonthRevenue > 0 && $totalBookings > 0
            ? round($currentMonthRevenue / $totalBookings)
            : 0;

        return [
            'current_month'         => $currentMonthRevenue,
            'previous_month'        => $previousMonthRevenue,
            'growth_rate'           => $growthRate,
            'daily_revenue'         => $dailyRevenue,
            'total_bookings'        => $totalBookings,
            'average_booking_value' => $averageBookingValue,
            'total_payments'        => $totalPayments,
            'total_refunds'         => $totalRefundsAbs,
            'net_revenue'           => $netRevenue,
            'total_revenue'         => $totalRevenue, // Tổng tiền thu = Tổng tiền hoàn + Doanh thu ròng
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











