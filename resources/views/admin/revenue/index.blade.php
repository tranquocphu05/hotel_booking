@extends('layouts.admin')

@section('title', 'Chi tiết doanh thu')

@section('admin_content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Chi tiết doanh thu</h1>
            <p class="text-gray-600">Phân tích chi tiết doanh thu theo tháng</p>
        </div>
        
        <!-- Date Range Filter -->
        <div class="flex items-center gap-4">
            <form method="GET" class="flex flex-wrap items-center gap-2">
                <!-- Date range filter -->
                <input
                    type="date"
                    name="start_date"
                    value="{{ $startDate ? \Carbon\Carbon::parse($startDate)->format('Y-m-d') : '' }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
                >
                <span class="text-gray-500 text-sm">đến</span>
                <input
                    type="date"
                    name="end_date"
                    value="{{ $endDate ? \Carbon\Carbon::parse($endDate)->format('Y-m-d') : '' }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
                >

                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Xem
                </button>
            </form>
        </div>
    </div>

    <!-- Revenue Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Revenue -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Doanh thu tháng này</p>
                    <p class="text-2xl font-bold text-gray-900" data-target="{{ $revenueData['current_month'] }}">
                        {{ number_format($revenueData['current_month'], 0, ',', '.') }} VNĐ
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
            </div>
            @if($revenueData['growth_rate'] != 0)
                <div class="mt-4 flex items-center">
                    @if($revenueData['growth_rate'] > 0)
                        <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                        <span class="text-green-600 font-medium text-sm">+{{ $revenueData['growth_rate'] }}%</span>
                    @else
                        <i class="fas fa-arrow-down text-red-500 mr-1"></i>
                        <span class="text-red-600 font-medium text-sm">{{ $revenueData['growth_rate'] }}%</span>
                    @endif
                    <span class="text-gray-500 text-sm ml-1">so với tháng trước</span>
                </div>
            @endif
        </div>

        <!-- Total Bookings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Tổng đặt phòng</p>
                    <p class="text-2xl font-bold text-gray-900" data-target="{{ $revenueData['total_bookings'] }}">
                        {{ $revenueData['total_bookings'] }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Average Booking Value -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Giá trị trung bình</p>
                    <p class="text-2xl font-bold text-gray-900" data-target="{{ $revenueData['average_booking_value'] }}">
                        {{ number_format($revenueData['average_booking_value'], 0, ',', '.') }} VNĐ
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Previous Month Revenue -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Tháng trước</p>
                    <p class="text-2xl font-bold text-gray-900" data-target="{{ $revenueData['previous_month'] }}">
                        {{ number_format($revenueData['previous_month'], 0, ',', '.') }} VNĐ
                    </p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-history text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Daily Revenue Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Doanh thu theo ngày</h3>
            <div class="h-64">
                <canvas id="dailyRevenueChart"></canvas>
            </div>
        </div>

        <!-- Room Type Revenue Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Doanh thu theo loại phòng</h3>
            <div class="h-64">
                <canvas id="roomTypeChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Room Type Statistics -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Thống kê theo loại phòng</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-medium text-gray-600">Loại phòng</th>
                        <th class="text-right py-3 px-4 font-medium text-gray-600">Số đặt phòng</th>
                        <th class="text-right py-3 px-4 font-medium text-gray-600">Doanh thu</th>
                        <th class="text-right py-3 px-4 font-medium text-gray-600">Trung bình/đơn</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roomTypeStats as $stat)
                        <tr class="border-b border-gray-100">
                            <td class="py-3 px-4 font-medium text-gray-900">{{ $stat['room_type'] }}</td>
                            <td class="py-3 px-4 text-right text-gray-600">{{ $stat['bookings_count'] }}</td>
                            <td class="py-3 px-4 text-right font-medium text-gray-900">
                                {{ number_format($stat['total_revenue'], 0, ',', '.') }} VNĐ
                            </td>
                            <td class="py-3 px-4 text-right text-gray-600">
                                {{ number_format($stat['average_revenue'], 0, ',', '.') }} VNĐ
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-gray-500">
                                Không có dữ liệu cho tháng này
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Paid Bookings -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Đặt phòng đã thanh toán gần đây</h3>
        <div class="space-y-4">
            @forelse($paidBookings as $booking)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-hotel text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">{{ $booking->loaiPhong->ten_loai ?? 'N/A' }}</h4>
                            <p class="text-sm text-gray-600">
                                {{ $booking->so_luong_da_dat ?? 1 }} phòng • 
                                {{ \Carbon\Carbon::parse($booking->ngay_dat)->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900">
                            {{ number_format($booking->tong_tien, 0, ',', '.') }} VNĐ
                        </p>
                        <p class="text-sm text-gray-600">{{ $booking->so_nguoi }} người</p>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500">
                    Không có đặt phòng nào đã thanh toán trong tháng này
                </div>
            @endforelse
        </div>
        
        @if($paidBookings->hasPages())
            <div class="mt-6">
                {{ $paidBookings->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Báo cáo doanh thu theo ngày -->
<div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Báo cáo doanh thu theo ngày</h3>
        <p class="text-sm text-gray-500">Chi tiết doanh thu từng ngày trong khoảng thời gian đã chọn</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left py-3 px-6 font-medium text-gray-600">Ngày</th>
                    <th class="text-right py-3 px-6 font-medium text-gray-600">Số đơn</th>
                    <th class="text-right py-3 px-6 font-medium text-gray-600">Doanh thu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dailyRevenues as $revenue)
                <tr class="border-b border-gray-100">
                    <td class="py-3 px-6 font-medium text-gray-900">
                        {{ \Carbon\Carbon::parse($revenue->ngay)->format('d/m/Y') }}
                    </td>
                    <td class="py-3 px-6 text-right text-gray-600">
                        {{ $revenue->so_luong }}
                    </td>
                    <td class="py-3 px-6 text-right font-medium text-gray-900">
                        {{ number_format($revenue->tong_tien, 0, ',', '.') }} VNĐ
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="py-8 text-center text-gray-500">
                        Không có dữ liệu doanh thu
                    </td>
                </tr>
                @endforelse
                
                @if($dailyRevenues->isNotEmpty())
                <tr class="bg-gray-50 font-semibold">
                    <td class="py-3 px-6 text-gray-900">Tổng cộng</td>
                    <td class="py-3 px-6 text-right text-gray-900">
                        {{ $dailyRevenues->sum('so_luong') }}
                    </td>
                    <td class="py-3 px-6 text-right text-gray-900">
                        {{ number_format($dailyRevenues->sum('tong_tien'), 0, ',', '.') }} VNĐ
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Daily Revenue Chart
    const dailyCtx = document.getElementById('dailyRevenueChart').getContext('2d');
    const dailyData = @json($revenueData['daily_revenue']);
    
    new Chart(dailyCtx, {
        type: 'bar',
        data: {
            labels: dailyData.map(item => item.day),
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: dailyData.map(item => item.revenue),
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderColor: 'rgb(99, 102, 241)',
                borderWidth: 2,
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('vi-VN') + ' VNĐ';
                        }
                    }
                }
            }
        }
    });

    // Room Type Chart
    const roomTypeCtx = document.getElementById('roomTypeChart').getContext('2d');
    const roomTypeData = @json($roomTypeStats);
    
    new Chart(roomTypeCtx, {
        type: 'doughnut',
        data: {
            labels: roomTypeData.map(item => item.room_type),
            datasets: [{
                data: roomTypeData.map(item => item.total_revenue),
                backgroundColor: [
                    'rgba(99, 102, 241, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(139, 92, 246, 0.8)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Animate counters
    function animateCount(el, to) {
        const start = 0;
        const duration = 2000;
        let startTime = null;
        
        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            const progress = Math.min((timestamp - startTime) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = start + (to - start) * eased;
            
            if (el.textContent.includes('VNĐ')) {
                el.textContent = Math.floor(current).toLocaleString('vi-VN') + ' VNĐ';
            } else {
                el.textContent = Math.floor(current).toLocaleString('vi-VN');
            }
            
            if (progress < 1) {
                requestAnimationFrame(step);
            }
        }
        requestAnimationFrame(step);
    }

    // Animate all counters
    document.querySelectorAll('[data-target]').forEach((el, index) => {
        setTimeout(() => {
            const target = parseFloat(el.getAttribute('data-target')) || 0;
            animateCount(el, target);
        }, index * 200);
    });
});
</script>
@endpush
@endsection











