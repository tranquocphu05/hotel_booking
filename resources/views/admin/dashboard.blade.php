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
        <div class="grid grid-cols-1 md:grid-cols-2 {{ $userRole === 'nhan_vien' ? 'lg:grid-cols-3' : 'lg:grid-cols-4' }} gap-6 mb-8">
            <!-- Total Bookings Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Tổng đặt phòng</p>
                        <p class="text-3xl font-bold text-gray-900" data-target="{{ $totalBookings ?? 0 }}">0</p>
                        <div class="flex items-center mt-2">
                            <span class="text-green-600 text-sm font-medium flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                @if(($bookingGrowthRate ?? 0) > 0)
                                    +{{ $bookingGrowthRate }}%
                                @elseif(($bookingGrowthRate ?? 0) < 0)
                                    {{ $bookingGrowthRate }}%
                                @else
                                    0%
                                @endif
                            </span>
                            <span class="text-gray-500 text-sm ml-2">so với tháng trước</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Revenue Card: Chỉ hiển thị cho Admin và Lễ tân -->
            @if($userRole !== 'nhan_vien' && isset($revenueData['label']) && $revenueData['label'] !== null)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">{{ $revenueData['label'] }}</p>
                        <p class="text-3xl font-bold text-gray-900" data-target="{{ $revenueData['total'] ?? 0 }}">0</p>
                        @if($revenueData['show_trend'] ?? false)
                        <div class="flex items-center mt-2">
                            <span class="text-green-600 text-sm font-medium flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                @if(($revenueData['growth_rate'] ?? 0) > 0)
                                    +{{ $revenueData['growth_rate'] }}%
                                @elseif(($revenueData['growth_rate'] ?? 0) < 0)
                                    {{ $revenueData['growth_rate'] }}%
                                @else
                                    0%
                                @endif
                            </span>
                            <span class="text-gray-500 text-sm ml-2">so với tháng trước</span>
                        </div>
                        @elseif($userRole === 'le_tan')
                        <div class="flex items-center mt-2">
                            <span class="text-blue-600 text-sm font-medium flex items-center">
                                <i class="fas fa-calendar-day mr-1"></i>
                                {{ \Carbon\Carbon::today()->format('d/m/Y') }}
                            </span>
                        </div>
                        @endif
                    </div>
                    <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            @endif

            <!-- Total Rooms Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Tổng phòng</p>
                        <p class="text-3xl font-bold text-gray-900" data-target="{{ $totalRooms ?? 0 }}">0</p>
                        <div class="flex items-center mt-2">
                            <a href="{{ route('admin.loai_phong.index') }}" class="text-blue-600 text-sm font-medium flex items-center hover:text-blue-800 transition-colors">
                                <i class="fas fa-bed mr-1"></i>
                                {{ $availableRooms ?? 0 }} trống
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
                        <p class="text-3xl font-bold text-gray-900" data-target="{{ $totalCustomers ?? 0 }}">0</p>
                        <div class="flex items-center mt-2">
                            <span class="text-indigo-600 text-sm font-medium flex items-center">
                                <i class="fas fa-users mr-1"></i>
                                {{ $activeCustomers ?? 0 }} khách
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
        <div class="grid grid-cols-1 {{ $userRole === 'nhan_vien' || $userRole === 'le_tan' ? 'lg:grid-cols-1' : 'lg:grid-cols-2' }} gap-6 mb-8">
            <!-- Revenue Chart: Chỉ hiển thị cho Admin -->
            @if($userRole === 'admin')
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Doanh thu theo tháng</h3>
                        <div class="flex items-center space-x-2">
                            <button class="px-3 py-1 text-xs bg-indigo-100 text-indigo-600 rounded-full font-medium">{{ \Carbon\Carbon::now()->year }}</button>
                        </div>
                    </div>
                <div class="h-64">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            @endif

            <!-- Room Occupancy Chart -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Tỷ lệ lấp đầy phòng</h3>
                    <div class="text-right">
                        @php
                            $totalRooms = App\Models\LoaiPhong::sum('so_luong_phong');
                            $bookedRooms = App\Models\LoaiPhong::sum('so_luong_phong') - App\Models\LoaiPhong::sum('so_luong_trong');
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
                    @forelse(App\Models\DatPhong::with('user', 'loaiPhong')->orderBy('ngay_dat', 'desc')->take(5)->get() as $booking)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-calendar text-indigo-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $booking->username ?? ($booking->user->ten ?? ($booking->user->ho_ten ?? 'Khách')) }}</p>
                                    @php
                                        $roomTypes = $booking->getRoomTypes();
                                    @endphp
                                    <p class="text-sm text-gray-500">
                                        @if(count($roomTypes) > 1)
                                            Loại phòng: <span class="font-medium text-gray-800">{{ count($roomTypes) }} loại phòng</span>
                                        @else
                                            Loại phòng: <span class="font-medium text-gray-800">{{ $booking->loaiPhong->ten_loai ?? 'N/A' }}</span>
                                        @endif
                                    </p>
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

                    <a href="{{ route('admin.loai_phong.create') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors group">
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function(){
        // Revenue Chart - Beautiful gradient bar chart (chỉ cho Admin)
        @if($userRole === 'admin')
        const revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx) {
        const revenueCtx2d = revenueCtx.getContext('2d');
        
        // Dữ liệu doanh thu theo tháng - chỉ tính các đơn đã thanh toán
        @php
        $currentYear = \Carbon\Carbon::now()->year;
        $monthlyRevenue = [];
        for ($month = 1; $month <= 12; $month++) {
            $revenue = \Illuminate\Support\Facades\DB::table('dat_phong')
                ->join('hoa_don', 'dat_phong.id', '=', 'hoa_don.dat_phong_id')
                ->where('hoa_don.trang_thai', 'da_thanh_toan')
                ->whereMonth('dat_phong.ngay_dat', $month)
                ->whereYear('dat_phong.ngay_dat', $currentYear)
                ->sum('dat_phong.tong_tien');
            $monthlyRevenue[] = $revenue ?? 0;
        }
        @endphp
        const revenueData = [{{ implode(', ', $monthlyRevenue) }}];
        
        // Tính toán max và stepSize cho trục Y
        const maxRevenue = Math.max(...revenueData, 0);
        let suggestedMax = 1000000; // Mặc định 1M
        let stepSize = 200000; // Mặc định 200K
        
        if (maxRevenue > 0) {
            if (maxRevenue >= 10000000) {
                suggestedMax = Math.ceil(maxRevenue / 2000000) * 2000000;
                stepSize = 2000000; // 2M steps
            } else if (maxRevenue >= 5000000) {
                suggestedMax = Math.ceil(maxRevenue / 1000000) * 1000000;
                stepSize = 1000000; // 1M steps
            } else if (maxRevenue >= 1000000) {
                suggestedMax = Math.ceil(maxRevenue / 500000) * 500000;
                stepSize = 500000; // 500K steps
            } else {
                suggestedMax = Math.ceil(maxRevenue / 100000) * 100000;
                stepSize = 100000; // 100K steps
            }
        }

        // Polyfill cho roundRect
        if (!CanvasRenderingContext2D.prototype.roundRect) {
            CanvasRenderingContext2D.prototype.roundRect = function(x, y, width, height, radius) {
                this.beginPath();
                this.moveTo(x + radius, y);
                this.lineTo(x + width - radius, y);
                this.quadraticCurveTo(x + width, y, x + width, y + radius);
                this.lineTo(x + width, y + height - radius);
                this.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
                this.lineTo(x + radius, y + height);
                this.quadraticCurveTo(x, y + height, x, y + height - radius);
                this.lineTo(x, y + radius);
                this.quadraticCurveTo(x, y, x + radius, y);
                this.closePath();
            };
        }

        // Không cần plugin vẽ label trên đầu bar vì đã có tooltip chi tiết

        const revenueChart = new Chart(revenueCtx2d, {
            type: 'bar',
            data: {
                labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: revenueData,
                    backgroundColor: (context) => {
                        const chart = context.chart;
                        const {ctx, chartArea} = chart;
                        if (!chartArea) {
                            return 'rgba(99, 102, 241, 0.7)';
                        }
                        
                        const meta = chart.getDatasetMeta(context.datasetIndex);
                        const bar = meta.data[context.dataIndex];
                        if (!bar || !bar.y) {
                            return 'rgba(99, 102, 241, 0.7)';
                        }
                        
                        // Tạo gradient từ dưới lên trên
                        const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, bar.y);
                        gradient.addColorStop(0, 'rgba(99, 102, 241, 0.9)'); // Indigo-500
                        gradient.addColorStop(0.5, 'rgba(139, 92, 246, 0.85)'); // Purple-500
                        gradient.addColorStop(1, 'rgba(79, 70, 229, 0.95)'); // Indigo-600
                        return gradient;
                    },
                    borderColor: 'rgba(99, 102, 241, 0.9)',
                    borderWidth: 2,
                    borderRadius: {
                        topLeft: 8,
                        topRight: 8,
                        bottomLeft: 0,
                        bottomRight: 0
                    },
                    borderSkipped: false,
                    barThickness: 'flex',
                    maxBarThickness: 50,
                    categoryPercentage: 0.8,
                    barPercentage: 0.65
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1800,
                    easing: 'easeOutQuart',
                    onComplete: function() {
                        // Animation hoàn thành
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(15, 23, 42, 0.98)',
                        padding: {
                            top: 14,
                            right: 18,
                            bottom: 14,
                            left: 18
                        },
                        titleFont: {
                            size: 15,
                            weight: 'bold',
                            family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif'
                        },
                        bodyFont: {
                            size: 13,
                            weight: '500',
                            family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif'
                        },
                        borderColor: 'rgba(99, 102, 241, 0.6)',
                        borderWidth: 1.5,
                        cornerRadius: 12,
                        displayColors: false,
                        titleSpacing: 8,
                        bodySpacing: 6,
                        titleMarginBottom: 10,
                        titleAlign: 'center',
                        bodyAlign: 'left',
                        xAlign: 'center',
                        yAlign: 'bottom',
                        caretSize: 6,
                        caretPadding: 8,
                        animation: {
                            duration: 200
                        },
                        callbacks: {
                            title: function(context) {
                                const monthNames = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 
                                                   'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];
                                return monthNames[context[0].dataIndex] + ' {{ \Carbon\Carbon::now()->year }}';
                            },
                            label: function(context) {
                                const value = context.parsed.y;
                                const formattedValue = value.toLocaleString('vi-VN');
                                
                                const lines = [
                                    '💰 Doanh thu: ' + formattedValue + ' VNĐ'
                                ];
                                
                                if (value >= 1000000) {
                                    lines.push('📊 ' + (value / 1000000).toFixed(2) + ' triệu VNĐ');
                                } else if (value >= 1000) {
                                    lines.push('📊 ' + (value / 1000).toFixed(0) + ' nghìn VNĐ');
                                }
                                
                                return lines;
                            },
                            afterBody: function(context) {
                                const value = context[0].parsed.y;
                                if (value === 0) return '';
                                
                                // Tính phần trăm so với tổng doanh thu năm
                                const totalRevenue = revenueData.reduce((a, b) => a + b, 0);
                                if (totalRevenue === 0) return '';
                                
                                const percentage = ((value / totalRevenue) * 100).toFixed(1);
                                return '📈 Tỷ trọng: ' + percentage + '% tổng doanh thu năm';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: suggestedMax,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false,
                            lineWidth: 1
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                size: 11,
                                family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif'
                            },
                            padding: 8,
                            stepSize: stepSize,
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return (value / 1000000).toFixed(0) + 'M';
                                } else if (value >= 1000) {
                                    return (value / 1000).toFixed(0) + 'K';
                                } else {
                                    return value;
                                }
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                size: 12,
                                weight: '600',
                                family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif'
                            },
                            padding: 10
                        }
                    }
                },
                events: ['mousemove', 'mouseout', 'click', 'touchstart', 'touchmove'],
                interaction: {
                    intersect: true,
                    mode: 'point',
                    axis: 'x'
                },
                onHover: (event, activeElements) => {
                    const chart = event.chart || this.chart;
                    const canvas = chart.canvas;
                    if (activeElements && activeElements.length > 0) {
                        canvas.style.cursor = 'pointer';
                    } else {
                        canvas.style.cursor = 'default';
                    }
                },
                elements: {
                    bar: {
                        hoverBackgroundColor: function(context) {
                            const chart = context.chart;
                            const {ctx, chartArea} = chart;
                            if (!chartArea) {
                                return 'rgba(79, 70, 229, 0.9)';
                            }
                            
                            const meta = chart.getDatasetMeta(context.datasetIndex);
                            const bar = meta.data[context.dataIndex];
                            if (!bar || !bar.y) {
                                return 'rgba(79, 70, 229, 0.9)';
                            }
                            
                            // Gradient đậm hơn khi hover
                            const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, bar.y);
                            gradient.addColorStop(0, 'rgba(79, 70, 229, 0.95)');
                            gradient.addColorStop(0.5, 'rgba(99, 102, 241, 0.95)');
                            gradient.addColorStop(1, 'rgba(67, 56, 202, 1)');
                            return gradient;
                        },
                        hoverBorderColor: 'rgba(67, 56, 202, 1)',
                        hoverBorderWidth: 3
                    }
                }
            }
        });
        
        // Thêm event listener để đảm bảo tooltip ẩn khi mouse ra khỏi chart
        revenueCtx.addEventListener('mouseleave', function() {
            if (revenueChart.tooltip) {
                revenueChart.tooltip.setActiveElements([], {x: 0, y: 0});
                revenueChart.update('none');
            }
        });
        }
        @endif

        // Occupancy Chart - Beautiful Doughnut chart with gradient
        const occupancyCtx = document.getElementById('occupancyChart').getContext('2d');
        
        // Tạo gradient đẹp cho phần đã đặt
        const bookedGradient = occupancyCtx.createLinearGradient(0, 0, 0, 300);
        bookedGradient.addColorStop(0, 'rgba(34, 197, 94, 1)');
        bookedGradient.addColorStop(0.5, 'rgba(59, 130, 246, 0.9)');
        bookedGradient.addColorStop(1, 'rgba(34, 197, 94, 0.8)');
        
        // Tạo gradient cho phần trống
        const emptyGradient = occupancyCtx.createLinearGradient(0, 0, 0, 300);
        emptyGradient.addColorStop(0, 'rgba(229, 231, 235, 0.6)');
        emptyGradient.addColorStop(1, 'rgba(209, 213, 219, 0.8)');
        
        const occupancyChart = new Chart(occupancyCtx, {
            type: 'doughnut',
            data: {
                labels: ['Đã đặt', 'Trống'],
                datasets: [{
                    data: [{{ $occupancyRate }}, {{ 100 - $occupancyRate }}],
                    backgroundColor: [
                        bookedGradient,
                        emptyGradient
                    ],
                    borderColor: [
                        'rgba(255, 255, 255, 1)',
                        'rgba(255, 255, 255, 1)'
                    ],
                    borderWidth: 4,
                    cutout: '75%',
                    spacing: 2,
                    hoverOffset: 8
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
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                size: 13,
                                weight: '500'
                            },
                            color: '#374151',
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const dataset = data.datasets[0];
                                        const value = dataset.data[i];
                                        const backgroundColor = dataset.backgroundColor[i];
                                        return {
                                            text: `${label}: ${value}%`,
                                            fillStyle: backgroundColor,
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                return `${label}: ${value}%`;
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 2000,
                    easing: 'easeOutQuart',
                    onComplete: function() {
                        const chart = this.chart;
                        const ctx = chart.ctx;
                        const centerX = chart.chartArea.left + (chart.chartArea.right - chart.chartArea.left) / 2;
                        const centerY = chart.chartArea.top + (chart.chartArea.bottom - chart.chartArea.top) / 2;
                        
                        ctx.save();
                        ctx.fillStyle = '#1f2937';
                        ctx.font = 'bold 32px Arial';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText('{{ $occupancyRate }}%', centerX, centerY - 10);
                        
                        ctx.fillStyle = '#6b7280';
                        ctx.font = '500 14px Arial';
                        ctx.fillText('Tỷ lệ lấp đầy', centerX, centerY + 20);
                        ctx.restore();
                    }
                },
                elements: {
                    arc: {
                        borderRadius: 10
                    }
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
