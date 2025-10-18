@extends('layouts.admin')

@section('title','Dashboard - OZIA Hotel')

@section('admin_content')
    <div class="p-6">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                    <p class="text-gray-600 mt-1">Chào mừng trở lại, {{ auth()->user()->ten ?? 'Admin' }}!</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Hôm nay</p>
                        <p class="text-lg font-semibold text-gray-900">{{ now()->format('d/m/Y') }}</p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar-day text-indigo-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Bookings Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Tổng đặt phòng</p>
                        <p class="text-3xl font-bold text-gray-900" data-target="{{ App\Models\DatPhong::count() }}">0</p>
                        <div class="flex items-center mt-2">
                            <span class="text-green-600 text-sm font-medium flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                +12%
                            </span>
                            <span class="text-gray-500 text-sm ml-2">so với tháng trước</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Revenue Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Doanh thu</p>
                        <p class="text-3xl font-bold text-gray-900" data-target="{{ App\Models\DatPhong::sum('tong_tien') ?? 0 }}">0</p>
                        <div class="flex items-center mt-2">
                            <span class="text-green-600 text-sm font-medium flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                +8%
                            </span>
                            <span class="text-gray-500 text-sm ml-2">so với tháng trước</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Rooms Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Tổng phòng</p>
                        <p class="text-3xl font-bold text-gray-900" data-target="{{ App\Models\Phong::count() }}">0</p>
                        <div class="flex items-center mt-2">
                            <a href="{{ route('admin.phong.available') }}" class="text-blue-600 text-sm font-medium flex items-center hover:text-blue-800 transition-colors">
                                <i class="fas fa-bed mr-1"></i>
                                @php
                                    $totalRooms = App\Models\Phong::where('trang_thai', 'hien')->count();
                                    $bookedRooms = App\Models\DatPhong::whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])->pluck('phong_id')->unique()->count();
                                    $availableRooms = max(0, $totalRooms - $bookedRooms);
                                @endphp
                                {{ $availableRooms }} trống
                                <i class="fas fa-external-link-alt ml-1 text-xs"></i>
                            </a>
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-purple-50 rounded-lg flex items-center justify-center">
                        <i class="fas fa-bed text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Users Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Khách hàng</p>
                        <p class="text-3xl font-bold text-gray-900" data-target="{{ App\Models\User::count() }}">0</p>
                        <div class="flex items-center mt-2">
                            <span class="text-indigo-600 text-sm font-medium flex items-center">
                                <i class="fas fa-users mr-1"></i>
                                {{ App\Models\User::where('vai_tro', 'user')->count() }} khách
                            </span>
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-orange-50 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Revenue Chart -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Doanh thu theo tháng</h3>
                    <div class="flex items-center space-x-2">
                        <button class="px-3 py-1 text-xs bg-indigo-100 text-indigo-600 rounded-full font-medium">2024</button>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Room Occupancy Chart -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Tỷ lệ lấp đầy phòng</h3>
                    <div class="text-right">
                        @php
                            $totalRooms = App\Models\Phong::where('trang_thai', 'hien')->count();
                            $bookedRooms = App\Models\DatPhong::whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])->pluck('phong_id')->unique()->count();
                            $occupancyRate = $totalRooms > 0 ? round(($bookedRooms / $totalRooms) * 100) : 0;
                            // Đảm bảo occupancy rate không vượt quá 100%
                            $occupancyRate = min($occupancyRate, 100);
                        @endphp
                        <p class="text-2xl font-bold text-green-600">{{ $occupancyRate }}%</p>
                        <p class="text-sm text-gray-500">Tháng này</p>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="occupancyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activity & Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Bookings -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Đặt phòng gần đây</h3>
                    <a href="{{ route('admin.dat_phong.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center">
                        Xem tất cả
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                <div class="space-y-4">
                    @forelse(App\Models\DatPhong::with('user', 'phong')->orderBy('ngay_dat', 'desc')->take(5)->get() as $booking)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-calendar text-indigo-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $booking->user->ten ?? 'Khách' }}</p>
                                    <p class="text-sm text-gray-500">Phòng {{ $booking->phong->so_phong ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">{{ number_format($booking->tong_tien ?? 0, 0, ',', '.') }} VNĐ</p>
                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($booking->ngay_dat)->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-calendar-times text-4xl mb-4"></i>
                            <p>Chưa có đặt phòng nào</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Thao tác nhanh</h3>
                <div class="space-y-3">
                    <a href="{{ route('admin.dat_phong.create') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors group">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                            <i class="fas fa-plus text-blue-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="font-medium text-gray-900">Tạo đặt phòng</p>
                            <p class="text-sm text-gray-500">Thêm đặt phòng mới</p>
                        </div>
                    </a>

                    <a href="{{ route('admin.phong.create') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors group">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors">
                            <i class="fas fa-bed text-green-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="font-medium text-gray-900">Thêm phòng</p>
                            <p class="text-sm text-gray-500">Tạo phòng mới</p>
                        </div>
                    </a>

                    <a href="{{ route('admin.users.index') }}" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors group">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                            <i class="fas fa-users text-purple-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="font-medium text-gray-900">Quản lý khách</p>
                            <p class="text-sm text-gray-500">Xem danh sách khách</p>
                        </div>
                    </a>

                    <a href="{{ route('admin.invoices.index') }}" class="flex items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors group">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                            <i class="fas fa-file-invoice text-orange-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="font-medium text-gray-900">Hóa đơn</p>
                            <p class="text-sm text-gray-500">Xem hóa đơn</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function(){
        // Revenue Chart - Beautiful gradient line chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueGradient = revenueCtx.createLinearGradient(0, 0, 0, 300);
        revenueGradient.addColorStop(0, 'rgba(99, 102, 241, 0.3)');
        revenueGradient.addColorStop(1, 'rgba(99, 102, 241, 0.05)');

        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: [{{ App\Models\DatPhong::whereMonth('ngay_dat', 1)->sum('tong_tien') }}, {{ App\Models\DatPhong::whereMonth('ngay_dat', 2)->sum('tong_tien') }}, {{ App\Models\DatPhong::whereMonth('ngay_dat', 3)->sum('tong_tien') }}, {{ App\Models\DatPhong::whereMonth('ngay_dat', 4)->sum('tong_tien') }}, {{ App\Models\DatPhong::whereMonth('ngay_dat', 5)->sum('tong_tien') }}, {{ App\Models\DatPhong::whereMonth('ngay_dat', 6)->sum('tong_tien') }}, {{ App\Models\DatPhong::whereMonth('ngay_dat', 7)->sum('tong_tien') }}, {{ App\Models\DatPhong::whereMonth('ngay_dat', 8)->sum('tong_tien') }}, {{ App\Models\DatPhong::whereMonth('ngay_dat', 9)->sum('tong_tien') }}, {{ App\Models\DatPhong::whereMonth('ngay_dat', 10)->sum('tong_tien') }}, {{ App\Models\DatPhong::whereMonth('ngay_dat', 11)->sum('tong_tien') }}, {{ App\Models\DatPhong::whereMonth('ngay_dat', 12)->sum('tong_tien') }}],
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: revenueGradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: 'rgb(99, 102, 241)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
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
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return (value / 1000000).toFixed(0) + 'M VNĐ';
                                } else if (value >= 1000) {
                                    return (value / 1000).toFixed(0) + 'K VNĐ';
                                } else {
                                    return value.toLocaleString('vi-VN') + ' VNĐ';
                                }
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });

        // Occupancy Chart - Doughnut chart
        const occupancyCtx = document.getElementById('occupancyChart').getContext('2d');
        new Chart(occupancyCtx, {
            type: 'doughnut',
            data: {
                labels: ['Đã đặt', 'Trống'],
                datasets: [{
                    data: [{{ $occupancyRate }}, {{ 100 - $occupancyRate }}],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(229, 231, 235, 0.8)'
                    ],
                    borderColor: [
                        'rgb(34, 197, 94)',
                        'rgb(229, 231, 235)'
                    ],
                    borderWidth: 2,
                    cutout: '70%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                },
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                }
            }
        });

        // Smooth count-up animation with easing
        function animateCount(el, to) {
            const start = 0;
            const duration = 2000;
            let startTime = null;
            
            function step(timestamp) {
                if (!startTime) startTime = timestamp;
                const progress = Math.min((timestamp - startTime) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3); // easeOutCubic
                const current = start + (to - start) * eased;
                
                // Check if this is the revenue element by looking for the dollar sign icon
                const isRevenueCard = el.closest('.bg-white').querySelector('.fa-dollar-sign');
                if (isRevenueCard) {
                    // Format as currency with proper Vietnamese formatting
                    el.textContent = Math.floor(current).toLocaleString('vi-VN') + ' VNĐ';
                } else if (String(el.getAttribute('data-target')).includes('.')) {
                    el.textContent = current.toFixed(2) + '%';
                } else {
                    el.textContent = Math.floor(current).toLocaleString('vi-VN');
                }
                
                if (progress < 1) {
                    requestAnimationFrame(step);
                }
            }
            requestAnimationFrame(step);
        }

        // Animate all counters with stagger effect
        document.querySelectorAll('[data-target]').forEach((el, index) => {
            setTimeout(() => {
                const target = parseFloat(el.getAttribute('data-target')) || 0;
            animateCount(el, target);
            }, index * 200);
        });

        // Add hover effects to cards
        const cards = document.querySelectorAll('.bg-white.rounded-xl');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px)';
                this.style.transition = 'all 0.3s ease';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
</script>
@endpush
