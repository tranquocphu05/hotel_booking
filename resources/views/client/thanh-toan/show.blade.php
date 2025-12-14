@extends('layouts.client')

@section('title', 'Xác nhận Thanh toán')

@section('client_content')
    <div class="min-h-screen bg-gray-50">
        <div class="container mx-auto py-8 px-4">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Xác nhận Thanh toán</h1>
                <p class="text-gray-600">Hoàn tất đặt phòng của bạn</p>
            </div>

            <!-- Alert Messages -->
            @if(session('success') && session('booking_success'))
                <div class="max-w-4xl mx-auto mb-6">
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-300 text-green-800 px-6 py-4 rounded-xl shadow-lg">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center animate-pulse">
                                    <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-lg font-bold text-green-900 mb-1">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Đặt Phòng Thành Công!
                                </h3>
                                <p class="text-sm text-green-700 mb-2">
                                    {{ session('success') }}
                                </p>
                                <div class="bg-white/50 rounded-lg px-3 py-2 inline-block">
                                    <p class="text-xs text-green-800">
                                        <i class="fas fa-receipt mr-1"></i>
                                        Mã đặt phòng: <span class="font-bold text-green-900">#{{ session('booking_id') }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif(session('success'))
                <div class="max-w-4xl mx-auto mb-6">
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            {{ session('success') }}
                        </div>
                    </div>
                </div>
            @endif

        @if(session('error'))
                <div class="max-w-4xl mx-auto mb-6">
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            {{ session('error') }}
                        </div>
                    </div>
            </div>
        @endif

            <div class="max-w-6xl mx-auto">
                @if(isset($remainingSeconds) && $remainingSeconds > 0)
                    <div id="payment-countdown" class="mb-6 max-w-md mx-auto bg-red-50 border border-red-200 rounded-lg px-4 py-3 flex items-center justify-between shadow-sm">
                        <div class="text-sm text-red-800">
                            <span class="font-semibold">Lưu ý:</span>
                            <span class="ml-1">
                                Biên lai chỉ có hiệu lực trong <strong>5 phút</strong>. <br>
                                Vui lòng tiến hành thanh toán để hoàn tất đặt phòng.
                            </span>
                        </div>
                        <div id="countdown-timer" class="ml-4 text-lg font-bold text-red-600 whitespace-nowrap"></div>
                    </div>
                @endif
                <div class="flex flex-col lg:flex-row gap-8 lg:items-stretch">
                    <!-- Booking Details - Enhanced Card -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 h-full flex-1">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6 border-b pb-3 flex items-center">
                            <i class="fas fa-receipt text-blue-600 mr-2"></i>
                            Chi tiết đặt phòng
                        </h2>

                        <!-- User Info -->
                        <div class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-user text-blue-600 mr-2"></i>
                                Thông tin người đặt
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-user-circle text-gray-400 mr-2 text-sm"></i>
                                        Họ tên:
                                    </span>
                                    <span class="font-medium text-gray-900">{{ trim($datPhong->username ?? '') !== '' ? $datPhong->username : ($datPhong->user->ho_ten ?? 'Khách ẩn danh') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-envelope text-gray-400 mr-2 text-sm"></i>
                                        Email:
                                    </span>
                                    <span class="font-medium text-gray-900">{{ $datPhong->email ?? ($datPhong->user->email ?? 'Chưa cập nhật') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-phone text-gray-400 mr-2 text-sm"></i>
                                        SĐT:
                                    </span>
                                    <span class="font-medium text-gray-900">{{ $datPhong->sdt ?? ($datPhong->user->sdt ?? 'Chưa cập nhật') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-id-card text-gray-400 mr-2 text-sm"></i>
                                        CCCD:
                                    </span>
                                    <span class="font-medium text-gray-900">{{ $datPhong->cccd ?? ($datPhong->user->cccd ?? 'Chưa cập nhật') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Room Info -->
                        <div class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-bed text-blue-600 mr-2"></i>
                                Thông tin phòng
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-hashtag text-gray-400 mr-2 text-sm"></i>
                                        Mã đặt phòng:
                                    </span>
                                    <span class="font-semibold text-blue-600 text-lg">#{{ $datPhong->id }}</span>
                                </div>
                                @php
                                    $roomTypes = $datPhong->getRoomTypes();
                                @endphp

                                @if(count($roomTypes) > 1)
                                    {{-- Hiển thị nhiều loại phòng --}}
                                    <div class="flex flex-col gap-2">
                                        <span class="text-gray-600 flex items-center">
                                            <i class="fas fa-door-open text-gray-400 mr-2 text-sm"></i>
                                            Các loại phòng:
                                        </span>
                                        <div class="ml-6 space-y-2">
                                            @foreach($roomTypes as $roomType)
                                                @php
                                                    $loaiPhong = \App\Models\LoaiPhong::find($roomType['loai_phong_id']);
                                                @endphp
                                                @if($loaiPhong)
                                                    <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
                                                        <div class="flex justify-between items-center">
                                                            <span class="font-semibold text-gray-900">{{ $loaiPhong->ten_loai }}</span>
                                                            <span class="text-sm text-gray-600">{{ $roomType['so_luong'] }} phòng</span>
                                                        </div>
                                                        <div class="text-sm text-gray-600 mt-1">
                                                            Giá: {{ number_format($roomType['gia_rieng'] ?? 0, 0, ',', '.') }} VNĐ
                                                        </div>
                                                        @if(isset($surchargeMap[$roomType['loai_phong_id']]) && $surchargeMap[$roomType['loai_phong_id']] > 0)
                                                            <div class="mt-1 text-sm text-amber-700">
                                                                Phụ phí: +{{ number_format($surchargeMap[$roomType['loai_phong_id']], 0, ',', '.') }} VNĐ
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    {{-- Hiển thị 1 loại phòng (legacy hoặc single room) --}}
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 flex items-center">
                                            <i class="fas fa-door-open text-gray-400 mr-2 text-sm"></i>
                                            Loại phòng:
                                        </span>
                                        <span class="font-medium text-gray-900">{{ $datPhong->loaiPhong->ten_loai ?? 'N/A' }}</span>
                                    </div>
                                @endif
                                @php
                                    $assignedPhongs = $datPhong->getAssignedPhongs();
                                    $assignedCount = $assignedPhongs->count();
                                    $totalRooms = $roomTypes->sum('so_luong') ?: ($datPhong->so_luong_da_dat ?? 1);
                                @endphp

                                @if($assignedCount > 0)
                                    <div class="flex flex-col gap-2">
                                        <span class="text-gray-600 flex items-center">
                                            <i class="fas fa-key text-gray-400 mr-2 text-sm"></i>
                                            Phòng ({{ $assignedCount }}/{{ $totalRooms }}):
                                        </span>
                                        <div class="ml-6 space-y-1 flex flex-wrap gap-2">
                                            @foreach($assignedPhongs as $phong)
                                                <span class="inline-block font-semibold text-blue-600 bg-blue-50 px-3 py-1 rounded-md text-sm border border-blue-200">
                                                    {{ $phong->so_phong }}
                                                    @if($phong->tang)
                                                        <span class="text-gray-500">(Tầng {{ $phong->tang }})</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @elseif($datPhong->phong)
                                    {{-- Legacy support: Hiển thị phòng từ phong_id --}}
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 flex items-center">
                                            <i class="fas fa-key text-gray-400 mr-2 text-sm"></i>
                                            Số phòng:
                                        </span>
                                        <span class="font-semibold text-blue-600">
                                            {{ $datPhong->phong->so_phong }}
                                            @if($datPhong->phong->tang)
                                                <span class="text-gray-500 text-sm">(Tầng {{ $datPhong->phong->tang }})</span>
                                            @endif
                                        </span>
                                    </div>
                                @endif

                                @php
                                    $remainingCount = $totalRooms - $assignedCount;
                                @endphp

                                @if($remainingCount > 0 && isset($availableRooms) && $availableRooms->count() > 0 && $datPhong->trang_thai === 'cho_xac_nhan')
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-3">
                                            <p class="text-sm text-yellow-800 mb-2">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                <strong>Còn thiếu {{ $remainingCount }} phòng.</strong> Vui lòng liên hệ admin để được gán phòng cụ thể.
                                            </p>
                                        </div>
                                        <details class="text-sm">
                                            <summary class="cursor-pointer text-blue-600 hover:text-blue-800 font-medium">
                                                <i class="fas fa-eye mr-1"></i> Xem danh sách phòng trống
                                            </summary>
                                            <div class="mt-2 space-y-1 max-h-48 overflow-y-auto">
                                                @foreach($availableRooms as $room)
                                                    <div class="text-xs text-gray-600 bg-gray-50 px-2 py-1 rounded">
                                                        {{ $room->so_phong }}
                                                        @if($room->tang) (Tầng {{ $room->tang }}) @endif
                                                        - {{ \App\Models\LoaiPhong::find($room->loai_phong_id)->ten_loai ?? 'N/A' }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        </details>
                                    </div>
                                @elseif($remainingCount > 0 && $datPhong->trang_thai === 'cho_xac_nhan')
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                            <p class="text-sm text-yellow-800">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                <strong>Còn thiếu {{ $remainingCount }} phòng.</strong> Vui lòng liên hệ admin để được gán phòng cụ thể.
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                @if($datPhong->so_luong_da_dat && $datPhong->so_luong_da_dat > 1)
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 flex items-center">
                                            <i class="fas fa-bed text-gray-400 mr-2 text-sm"></i>
                                            Số lượng phòng:
                                        </span>
                                        <span class="font-medium text-gray-900">{{ $datPhong->so_luong_da_dat }} phòng</span>
                                    </div>
                                @endif
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-calendar-check text-gray-400 mr-2 text-sm"></i>
                                        Ngày nhận:
                                    </span>
                                    <span class="font-medium text-gray-900">{{ $datPhong->ngay_nhan->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-calendar-times text-gray-400 mr-2 text-sm"></i>
                                        Ngày trả:
                                    </span>
                                    <span class="font-medium text-gray-900">{{ $datPhong->ngay_tra->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-user text-gray-400 mr-2 text-sm"></i>
                                        Số người lớn:
                                    </span>
                                    <span class="font-medium text-gray-900">{{ $datPhong->so_nguoi ?? 1 }} người</span>
                                </div>
                                @if(($datPhong->so_tre_em ?? 0) > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-child text-gray-400 mr-2 text-sm"></i>
                                        Số trẻ em:
                                    </span>
                                    <span class="font-medium text-gray-900">{{ $datPhong->so_tre_em ?? 0 }} trẻ em</span>
                                </div>
                                @endif
                                @if(($datPhong->so_em_be ?? 0) > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-baby text-gray-400 mr-2 text-sm"></i>
                                        Số em bé:
                                    </span>
                                    <span class="font-medium text-gray-900">{{ $datPhong->so_em_be ?? 0 }} em bé</span>
                                </div>
                                @endif
                                @if(isset($datPhong->so_luong_da_dat) && $datPhong->so_luong_da_dat > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-layer-group text-gray-400 mr-2 text-sm"></i>
                                        Số lượng phòng:
                                    </span>
                                    <span class="font-medium text-gray-900">{{ $datPhong->so_luong_da_dat }} phòng</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Voucher Info Box (if applicable) -->
                        @if ($datPhong->voucher)
                            <div class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl shadow-md border-2 border-green-300 p-5">
                                <h3 class="text-base font-bold text-green-900 mb-2 flex items-center">
                                    <i class="fas fa-tag text-green-600 mr-2"></i>
                                    Mã giảm giá
                                </h3>
                                <div class="text-2xl font-bold text-green-700 mb-1">{{ $datPhong->voucher->ma_voucher }}</div>
                                <div class="text-green-600 font-semibold">Giảm {{ $datPhong->voucher->gia_tri }}%</div>
                            </div>
                        @endif

                        <!-- Total Price -->
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl shadow-md border-2 border-blue-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-5 flex items-center">
                                <i class="fas fa-calculator text-blue-600 mr-2"></i>
                                Tóm tắt hóa đơn
                            </h3>
                            <div class="space-y-4">
                                @php
                                    $roomTypes = $datPhong->getRoomTypes();
                                @endphp

                                @php
                                    // Sử dụng các biến đã tính từ controller
                                    $giaPhongGoc = $giaPhongGoc ?? 0;
                                    $phuPhiNgayLe = $phuPhiNgayLe ?? 0;
                                    $phuPhiNguoiLon = $phuPhiNguoiLon ?? 0;
                                    $phuPhiTreEm = $phuPhiTreEm ?? 0;
                                    $phuPhiEmBe = $phuPhiEmBe ?? 0;
                                    $tongTienPhong = $tongTienPhong ?? ($giaPhongGoc + $phuPhiNgayLe + $phuPhiNguoiLon + $phuPhiTreEm + $phuPhiEmBe);
                                @endphp
                                
                                {{-- Giá phòng gốc --}}
                                <div class="flex justify-between items-center bg-white/60 rounded-lg px-4 py-3">
                                    <span class="text-gray-700 font-medium">
                                        <i class="fas fa-bed text-blue-500 mr-2"></i>
                                        Giá phòng
                                        @if(($datPhong->so_luong_da_dat ?? 1) > 1)
                                            ({{ $nights }} đêm × {{ $datPhong->so_luong_da_dat }} phòng)
                                        @else
                                            ({{ $nights }} đêm)
                                        @endif
                                    </span>
                                    <span class="font-semibold text-gray-900 text-base">{{ number_format($giaPhongGoc, 0, ',', '.') }} VNĐ</span>
                                </div>
                                
                                {{-- Phụ phí ngày lễ/cuối tuần --}}
                                @if($phuPhiNgayLe > 0)
                                <div class="flex justify-between items-center bg-purple-50 rounded-md px-3 py-2 mt-2 text-sm">
                                    <span class="text-gray-700 flex items-center">
                                        <i class="fas fa-calendar-alt text-purple-600 mr-2"></i>
                                        Phụ phí ngày lễ/cuối tuần
                                    </span>
                                    <span class="font-semibold text-purple-700">+{{ number_format($phuPhiNgayLe, 0, ',', '.') }} VNĐ</span>
                                </div>
                                @endif
                                
                                {{-- Phụ phí người lớn --}}
                                @if($phuPhiNguoiLon > 0)
                                <div class="flex justify-between items-center bg-amber-50 rounded-md px-3 py-2 mt-2 text-sm">
                                    <span class="text-gray-700 flex items-center">
                                        <i class="fas fa-user-plus text-amber-600 mr-2"></i>
                                        Phụ phí thêm người lớn
                                    </span>
                                    <span class="font-semibold text-amber-700">+{{ number_format($phuPhiNguoiLon, 0, ',', '.') }} VNĐ</span>
                                </div>
                                @endif
                                
                                {{-- Phụ phí trẻ em --}}
                                @if(($datPhong->so_tre_em ?? 0) > 0)
                                <div class="flex justify-between items-center bg-green-50 rounded-md px-3 py-2 mt-2 text-sm">
                                    <span class="text-gray-700 flex items-center">
                                        <i class="fas fa-child text-green-600 mr-2"></i>
                                        Phụ phí trẻ em ({{ $datPhong->so_tre_em ?? 0 }} trẻ em)
                                    </span>
                                    <span class="font-semibold text-green-700">+{{ number_format($phuPhiTreEm, 0, ',', '.') }} VNĐ</span>
                                </div>
                                @endif
                                
                                {{-- Phụ phí em bé --}}
                                @if(($datPhong->so_em_be ?? 0) > 0)
                                <div class="flex justify-between items-center bg-pink-50 rounded-md px-3 py-2 mt-2 text-sm">
                                    <span class="text-gray-700 flex items-center">
                                        <i class="fas fa-baby text-pink-600 mr-2"></i>
                                        Phụ phí em bé ({{ $datPhong->so_em_be ?? 0 }} em bé)
                                    </span>
                                    <span class="font-semibold text-pink-700">+{{ number_format($phuPhiEmBe, 0, ',', '.') }} VNĐ</span>
                                </div>
                                @endif
                                
                                {{-- Tổng tiền phòng --}}
                                <div class="flex justify-between items-center bg-blue-50 rounded-lg px-4 py-3 mt-3 border border-blue-200">
                                    <span class="text-gray-900 font-semibold">
                                        <i class="fas fa-calculator text-blue-600 mr-2"></i>
                                        Tổng tiền phòng
                                    </span>
                                    <span class="font-bold text-blue-700 text-lg">{{ number_format($tongTienPhong, 0, ',', '.') }} VNĐ</span>
                                </div>

                                {{-- Dịch vụ đã sử dụng --}}
                                @php
                                    $services = \App\Models\BookingService::where('dat_phong_id', $datPhong->id)->get();
                                    $servicesTotal = $services->sum(function($s) { return $s->quantity * $s->unit_price; });
                                @endphp

                                @if($services->count() > 0)
                                    <div class="mt-4 pt-4 border-t border-blue-200">
                                        <h4 class="text-sm font-semibold text-purple-700 mb-2 flex items-center">
                                            <i class="fas fa-concierge-bell text-purple-600 mr-2"></i>
                                            Dịch vụ đã sử dụng
                                        </h4>
                                        @foreach($services as $service)
                                            <div class="flex justify-between items-center bg-purple-50 rounded-md px-3 py-2 mb-2 text-sm">
                                                <span class="text-gray-700">
                                                    {{ $service->service->name ?? $service->service_name }}
                                                    <span class="text-xs text-gray-500">
                                                        ({{ $service->used_at ? \Carbon\Carbon::parse($service->used_at)->format('d/m/Y') : 'N/A' }} • {{ $service->quantity }} Lần)
                                                    </span>
                                                </span>
                                                <span class="font-semibold text-purple-700">{{ number_format($service->quantity * $service->unit_price, 0, ',', '.') }} VNĐ</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Tổng kết --}}
                                <div class="border-t-2 border-blue-300 mt-4 pt-4 space-y-2">
                                    @if ($datPhong->voucher && $discountAmount > 0)
                                        <div class="flex justify-between items-center text-red-600">
                                            <span>Giảm giá ({{ $datPhong->voucher->ma_voucher }} - {{ $datPhong->voucher->gia_tri }}%):</span>
                                            <span class="font-semibold">-{{ number_format($discountAmount, 0, ',', '.') }} VNĐ</span>
                                        </div>
                                    @endif

                                    @if($servicesTotal > 0)
                                        <div class="flex justify-between items-center text-gray-700">
                                            <span>Tổng tiền dịch vụ:</span>
                                            <span class="font-semibold text-purple-600">{{ number_format($servicesTotal, 0, ',', '.') }} VNĐ</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="border-t-2 border-blue-400 mt-4 pt-4">
                                    <div class="flex justify-between items-center bg-white/80 rounded-lg px-4 py-4 shadow-sm">
                                        <span class="text-lg font-bold text-gray-900">Tổng thanh toán:</span>
                                        <span class="text-2xl font-bold text-blue-600">{{ number_format($tongThanhToan ?? $datPhong->tong_tien, 0, ',', '.') }} VNĐ</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Policy -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6 h-full flex-1">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6 border-b pb-3 flex items-center">
                            <i class="fas fa-file-contract text-blue-600 mr-2"></i>
                            Chính sách đặt phòng
                        </h2>

                        @php
                            $roomTypes = $datPhong->getRoomTypes();
                            $totalRooms = $roomTypes->sum('so_luong') ?: ($datPhong->so_luong_da_dat ?? 1);
                        @endphp

                        <!-- Room Type Policies -->
                        <div class="space-y-4 mb-6">
                            @if(count($roomTypes) > 1)
                                {{-- Hiển thị chính sách cho từng loại phòng --}}
                                @foreach($roomTypes as $index => $roomType)
                                    @php
                                        $loaiPhong = \App\Models\LoaiPhong::find($roomType['loai_phong_id']);
                                        $soLuong = $roomType['so_luong'] ?? 1;
                                    @endphp
                                    @if($loaiPhong)
                                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                            <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
                                                <i class="fas fa-bed text-blue-600 mr-2"></i>
                                                {{ $loaiPhong->ten_loai }} - {{ $soLuong }} phòng
                                            </h3>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                                <div>
                                                    <h4 class="font-medium text-gray-800 mb-2">✓ Bao gồm:</h4>
                                                    <ul class="text-gray-600 space-y-1">
                                                        <li>• Wi-Fi tốc độ cao</li>
                                                        <li>• Nhà hàng sang trọng</li>
                                                        <li>• Spa & Wellness</li>
                                                        <li>• Dịch vụ phòng 24/7</li>
                                                    </ul>
                                                </div>
                                                <div>
                                                    <h4 class="font-medium text-gray-800 mb-2">⚠️ Lưu ý:</h4>
                                                    <ul class="text-gray-600 space-y-1">
                                                        <li>• Check-in: 14:00</li>
                                                        <li>• Check-out: 12:00</li>
                                                        <li>• Không hút thuốc</li>
                                                        <li>• Không mang thú cưng</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                {{-- Hiển thị chính sách cho 1 loại phòng --}}
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
                                        <i class="fas fa-bed text-blue-600 mr-2"></i>
                                        {{ $datPhong->loaiPhong->ten_loai ?? 'Phòng' }} - {{ $totalRooms }} phòng
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <h4 class="font-medium text-gray-800 mb-2">✓ Bao gồm:</h4>
                                            <ul class="text-gray-600 space-y-1">
                                                <li>• Wi-Fi tốc độ cao</li>
                                                <li>• Nhà hàng sang trọng</li>
                                                <li>• Spa & Wellness</li>
                                                <li>• Dịch vụ phòng 24/7</li>
                                            </ul>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-800 mb-2">⚠️ Lưu ý:</h4>
                                            <ul class="text-gray-600 space-y-1">
                                                <li>• Check-in: 14:00</li>
                                                <li>• Check-out: 12:00</li>
                                                <li>• Không hút thuốc</li>
                                                <li>• Không mang thú cưng</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                            <!-- Simple Policy Text -->
                            <div class="mt-6 space-y-2 text-sm text-gray-700">
                                <p><strong>Hủy:</strong> Nếu hủy, thay đổi hoặc không đến, khách sẽ trả toàn bộ giá trị tiền đặt phòng.</p>
                                <p><strong>Thanh toán:</strong> Thanh toán toàn bộ giá trị tiền đặt phòng.</p>
                            </div>

                            <!-- Payment Methods -->
                            <div class="mt-8 border-t pt-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Chọn phương thức thanh toán</h3>

                                <form action="{{ route('client.thanh-toan.store', ['datPhong' => $datPhong->id]) }}" method="POST" id="payment-form">
                                    @csrf
                                    <input type="hidden" name="phuong_thuc" id="phuong_thuc" value="vnpay">

                                    <!-- Payment Options -->
                                    <div class="space-y-4 mb-6">
                                        <!-- VNPay Option -->
                                        <label class="payment-option block cursor-pointer" data-method="vnpay">
                                            <div class="border-2 border-blue-200 bg-blue-50 rounded-lg p-4 transition-all hover:border-blue-400 payment-card selected" id="vnpay-card">
                                                <div class="flex items-center">
                                                    <input type="radio" name="payment_method" value="vnpay" class="sr-only" checked>
                                                    <div class="w-6 h-6 rounded-full border-2 border-blue-500 mr-4 flex items-center justify-center payment-radio" id="vnpay-radio">
                                                        <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                                                    </div>
                                                    <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-red-600 rounded-lg flex items-center justify-center mr-4 shadow-md">
                                                        <span class="text-white font-bold text-lg">V</span>
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="font-semibold text-gray-900">VNPay</p>
                                                        <p class="text-sm text-gray-600">Thanh toán thẻ ATM/Visa/Mastercard</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full">Phổ biến</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>

                                        <!-- SePay Option -->
                                        <label class="payment-option block cursor-pointer" data-method="sepay">
                                            <div class="border-2 border-gray-200 bg-gray-50 rounded-lg p-4 transition-all hover:border-green-400 payment-card" id="sepay-card">
                                                <div class="flex items-center">
                                                    <input type="radio" name="payment_method" value="sepay" class="sr-only">
                                                    <div class="w-6 h-6 rounded-full border-2 border-gray-300 mr-4 flex items-center justify-center payment-radio" id="sepay-radio">
                                                        <div class="w-3 h-3 rounded-full bg-transparent"></div>
                                                    </div>
                                                    <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg flex items-center justify-center mr-4 shadow-md">
                                                        <span class="text-white font-bold text-lg">SE</span>
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="font-semibold text-gray-900">SePay</p>
                                                        <p class="text-sm text-gray-600">Chuyển khoản ngân hàng qua QR Code</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">Nhanh chóng</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    @error('phuong_thuc')
                                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-6">
                                            <div class="flex items-center">
                                                <i class="fas fa-exclamation-circle mr-2"></i>
                                                {{ $message }}
                                            </div>
                                        </div>
                                    @enderror

                                    <!-- Payment Notice -->
                                    <div id="vnpay-notice" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                                        <div class="flex items-start">
                                            <i class="fas fa-info-circle text-yellow-600 mr-2 mt-0.5"></i>
                                            <div class="text-sm text-yellow-800">
                                                <p class="font-medium mb-1">Lưu ý VNPay:</p>
                                                <p>Bạn sẽ được chuyển đến trang thanh toán VNPay để hoàn tất giao dịch. Vui lòng không đóng trình duyệt trong quá trình thanh toán.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="sepay-notice" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 hidden">
                                        <div class="flex items-start">
                                            <i class="fas fa-qrcode text-green-600 mr-2 mt-0.5"></i>
                                            <div class="text-sm text-green-800">
                                                <p class="font-medium mb-1">Thanh toán SePay:</p>
                                                <p>Bạn sẽ quét mã QR VietQR để chuyển khoản. Hệ thống sẽ tự động xác nhận thanh toán trong vòng 10 giây sau khi chuyển khoản thành công.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Submit Buttons -->
                                    <div class="mt-6">
                                        <button type="submit" id="vnpay-btn" class="w-full bg-gradient-to-r from-red-600 to-red-700 text-white font-semibold py-4 px-6 rounded-lg hover:from-red-700 hover:to-red-800 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center">
                                            <i class="fas fa-credit-card mr-2"></i>
                                            Thanh toán bằng VNPay
                                        </button>
                                        <button type="submit" id="sepay-btn" class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white font-semibold py-4 px-6 rounded-lg hover:from-green-700 hover:to-emerald-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center hidden">
                                            <i class="fas fa-qrcode mr-2"></i>
                                            Thanh toán bằng SePay (QR Code)
                                        </button>
                                        <p class="text-center text-xs text-gray-500 mt-3">
                                            <i class="fas fa-lock mr-1"></i>
                                            <span id="security-text">Giao dịch được bảo mật bởi VNPay</span>
                                        </p>
                                    </div>
                                </form>

                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const paymentOptions = document.querySelectorAll('.payment-option');
                                        const phuongThucInput = document.getElementById('phuong_thuc');
                                        const vnpayNotice = document.getElementById('vnpay-notice');
                                        const sepayNotice = document.getElementById('sepay-notice');
                                        const vnpayBtn = document.getElementById('vnpay-btn');
                                        const sepayBtn = document.getElementById('sepay-btn');
                                        const securityText = document.getElementById('security-text');
                                        const vnpayCard = document.getElementById('vnpay-card');
                                        const sepayCard = document.getElementById('sepay-card');
                                        const vnpayRadio = document.getElementById('vnpay-radio');
                                        const sepayRadio = document.getElementById('sepay-radio');

                                        paymentOptions.forEach(option => {
                                            option.addEventListener('click', function() {
                                                const method = this.dataset.method;
                                                phuongThucInput.value = method;

                                                // Update radio buttons visual
                                                if (method === 'vnpay') {
                                                    vnpayCard.classList.add('border-blue-400', 'bg-blue-50', 'selected');
                                                    vnpayCard.classList.remove('border-gray-200', 'bg-gray-50');
                                                    vnpayRadio.classList.add('border-blue-500');
                                                    vnpayRadio.classList.remove('border-gray-300');
                                                    vnpayRadio.querySelector('div').classList.add('bg-blue-500');
                                                    vnpayRadio.querySelector('div').classList.remove('bg-transparent');

                                                    sepayCard.classList.remove('border-green-400', 'bg-green-50', 'selected');
                                                    sepayCard.classList.add('border-gray-200', 'bg-gray-50');
                                                    sepayRadio.classList.remove('border-green-500');
                                                    sepayRadio.classList.add('border-gray-300');
                                                    sepayRadio.querySelector('div').classList.remove('bg-green-500');
                                                    sepayRadio.querySelector('div').classList.add('bg-transparent');

                                                    // Show/hide elements
                                                    vnpayNotice.classList.remove('hidden');
                                                    sepayNotice.classList.add('hidden');
                                                    vnpayBtn.classList.remove('hidden');
                                                    sepayBtn.classList.add('hidden');
                                                    securityText.textContent = 'Giao dịch được bảo mật bởi VNPay';
                                                } else {
                                                    sepayCard.classList.add('border-green-400', 'bg-green-50', 'selected');
                                                    sepayCard.classList.remove('border-gray-200', 'bg-gray-50');
                                                    sepayRadio.classList.add('border-green-500');
                                                    sepayRadio.classList.remove('border-gray-300');
                                                    sepayRadio.querySelector('div').classList.add('bg-green-500');
                                                    sepayRadio.querySelector('div').classList.remove('bg-transparent');

                                                    vnpayCard.classList.remove('border-blue-400', 'bg-blue-50', 'selected');
                                                    vnpayCard.classList.add('border-gray-200', 'bg-gray-50');
                                                    vnpayRadio.classList.remove('border-blue-500');
                                                    vnpayRadio.classList.add('border-gray-300');
                                                    vnpayRadio.querySelector('div').classList.remove('bg-blue-500');
                                                    vnpayRadio.querySelector('div').classList.add('bg-transparent');

                                                    // Show/hide elements
                                                    vnpayNotice.classList.add('hidden');
                                                    sepayNotice.classList.remove('hidden');
                                                    vnpayBtn.classList.add('hidden');
                                                    sepayBtn.classList.remove('hidden');
                                                    securityText.textContent = 'Thanh toán tự động xác nhận bởi SePay';
                                                }
                                            });
                                        });
                                    });
                                </script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        //countdown timer
        document.addEventListener('DOMContentLoaded', function() {
            var remaining = {{ isset($remainingSeconds) ? (int) $remainingSeconds : 0 }};
            var timerElement = document.getElementById('countdown-timer');

            if (remaining > 0 && timerElement) {
                var countdownContainer = document.getElementById('payment-countdown');

                var formatTime = function(seconds) {
                    var m = Math.floor(seconds / 60);
                    var s = seconds % 60;
                    var mm = m < 10 ? '0' + m : '' + m;
                    var ss = s < 10 ? '0' + s : '' + s;
                    return mm + ':' + ss;
                };

                var updateCountdown = function() {
                    if (remaining <= 0) {
                        timerElement.textContent = '00:00';
                        if (countdownContainer) {
                            countdownContainer.classList.add('opacity-60');
                        }
                        alert('Bạn đã quá thời gian thanh toán. Đơn đặt phòng của bạn đã bị hủy. Vui lòng đặt phòng lại.');
                        window.location.href = "{{ url('/booking') }}";
                        return;
                    }

                    timerElement.textContent = formatTime(remaining);
                    remaining -= 1;
                };

                updateCountdown();
                setInterval(updateCountdown, 1000);
            }

            @if(session('booking_success'))
                setTimeout(() => {
                    const successAlert = document.querySelector('.bg-gradient-to-r.from-green-50');
                    if (successAlert) {
                        successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });

                        successAlert.classList.add('animate-bounce-once');
                        setTimeout(() => {
                            successAlert.classList.remove('animate-bounce-once');
                        }, 1000);
                    }
                }, 300);
            @endif
        });
    </script>

    <style>
        @keyframes bounce-once {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .animate-bounce-once {
            animation: bounce-once 0.5s ease-in-out 2;
        }
    </style>
@endsection
