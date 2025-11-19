@extends('layouts.admin')

@section('title', 'Chi tiết đặt phòng')

@section('admin_content') 
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-between items-center">
                @php
                    $roomTypes = $booking->getRoomTypes();
                @endphp
                @php
                    $serviceTotal = isset($bookingServices) ? $bookingServices->sum(function($s){ return ($s->unit_price * $s->quantity); }) : 0;
                @endphp
                <h2 class="text-2xl font-semibold text-gray-800">
                    Chi tiết đặt phòng 
                    @if(count($roomTypes) > 1)
                        <b>{{ count($roomTypes) }} loại phòng</b>
                    @else
                        <b>{{ $booking->loaiPhong->ten_loai ?? 'N/A' }}</b>
                    @endif
                </h2>
                <span class="px-3 py-1 rounded-full text-sm font-medium
                    @if ($booking->trang_thai === 'da_xac_nhan') bg-green-100 text-green-800
                    @elseif($booking->trang_thai === 'cho_xac_nhan') bg-yellow-100 text-yellow-800
                    @elseif($booking->trang_thai === 'da_huy') bg-red-100 text-red-800
                    @else bg-blue-100 text-blue-800 @endif">
                    @php
                        $statuses = [
                            'cho_xac_nhan' => 'Chờ xác nhận',
                            'da_xac_nhan' => 'Đã xác nhận',
                            'da_huy' => 'Đã hủy',
                            'da_tra' => 'Đã trả phòng',
                        ];
                    @endphp
                    {{ $statuses[$booking->trang_thai] ?? $booking->trang_thai }}
                </span>
            </div>

<<<<<<< HEAD
        {{-- CHÍNH SÁCH HỦY PHÒNG (CHỈ HIỂN THỊ KHI ĐÃ XÁC NHẬN) --}}
        @if($booking->trang_thai === 'da_xac_nhan' && isset($cancellationPolicy))
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 rounded-lg shadow-sm overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="text-xl font-bold text-gray-900 mb-4">
                                <i class="fas fa-info-circle"></i> Chính sách hủy phòng
                            </h3>
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                {{-- Thông tin thời gian --}}
                                <div class="bg-white p-5 rounded-lg border border-blue-200 shadow-sm">
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                                            <span class="text-sm font-medium text-gray-600">Ngày nhận phòng</span>
                                            <span class="text-lg font-bold text-gray-900">{{ date('d/m/Y', strtotime($booking->ngay_nhan)) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-medium text-gray-600">Thời gian còn lại</span>
                                            <span class="text-2xl font-bold text-blue-600">{{ number_format($cancellationPolicy['days_until_checkin'], 0) }} ngày</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Thông tin hoàn tiền --}}
                                <div class="bg-white p-5 rounded-lg border border-blue-200 shadow-sm">
                                    <p class="text-sm font-semibold text-gray-700 mb-3">
                                        <i class="fas fa-calculator"></i> Nếu hủy phòng ngay bây giờ:
                                    </p>
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Hoàn lại cho khách</span>
                                            <div class="text-right">
                                                <p class="text-xl font-bold text-green-600">
                                                    {{ number_format($cancellationPolicy['refund_amount'], 0, ',', '.') }}₫
                                                </p>
                                                <p class="text-xs text-gray-500">({{ $cancellationPolicy['refund_percentage'] }}%)</p>
                                            </div>
                                        </div>
                                        @if($cancellationPolicy['penalty_amount'] > 0)
                                            <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                                                <span class="text-sm text-gray-600">Phí hủy phòng</span>
                                                <p class="text-lg font-bold text-red-600">
                                                    {{ number_format($cancellationPolicy['penalty_amount'], 0, ',', '.') }}₫
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="mt-3 pt-3 border-t border-gray-200">
                                        <p class="text-xs text-gray-500 italic">
                                            <i class="fas fa-info-circle"></i> 
                                            Đây là số tiền đề xuất theo chính sách. Admin có thể điều chỉnh khi hủy.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Bảng chính sách chi tiết --}}
                            <div class="mt-6 bg-white p-5 rounded-lg border border-gray-200">
                                <p class="text-sm font-semibold text-gray-700 mb-3">
                                    <i class="fas fa-list-ul"></i> Bảng chính sách hoàn tiền:
                                </p>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <div class="text-center p-3 bg-green-50 rounded-lg border border-green-200">
                                        <p class="text-xs text-gray-600 mb-1">≥ 7 ngày</p>
                                        <p class="text-lg font-bold text-green-600">100%</p>
                                    </div>
                                    <div class="text-center p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                        <p class="text-xs text-gray-600 mb-1">3-6 ngày</p>
                                        <p class="text-lg font-bold text-yellow-600">50%</p>
                                    </div>
                                    <div class="text-center p-3 bg-orange-50 rounded-lg border border-orange-200">
                                        <p class="text-xs text-gray-600 mb-1">1-2 ngày</p>
                                        <p class="text-lg font-bold text-orange-600">25%</p>
                                    </div>
                                    <div class="text-center p-3 bg-red-50 rounded-lg border border-red-200">
                                        <p class="text-xs text-gray-600 mb-1">Trong ngày</p>
                                        <p class="text-lg font-bold text-red-600">0%</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Nút hủy phòng --}}
                            <div class="mt-6">
                                @if($cancellationPolicy['can_cancel'])
                                    <div class="flex justify-end">
                                        <a href="{{ route('admin.dat_phong.cancel', $booking->id) }}" 
                                           class="inline-flex items-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white text-base font-semibold rounded-lg shadow-md transition-all hover:shadow-lg">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Hủy đặt phòng
                                        </a>
                                    </div>
                                @else
                                    <div class="bg-red-50 border-l-4 border-red-400 p-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-red-700">
                                                    <strong>{{ $cancellationPolicy['message'] }}</strong>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- CHECK-IN / CHECK-OUT SECTION --}}
        @include('admin.dat_phong._checkin_checkout')

        {{-- BOOKING SERVICES SECTION --}}
        @include('admin.dat_phong._booking_services')

        {{-- MAIN CONTENT: 1 COLUMN LAYOUT WITH SIDEBAR --}}
        <div class="lg:grid lg:grid-cols-12 lg:gap-6">
            
            {{-- MAIN CONTENT (LEFT) --}}
            <div class="lg:col-span-8 space-y-6">

                {{-- THÔNG TIN PHÒNG --}}
=======
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Card Thông tin phòng -->
>>>>>>> f1858d0fc0a6aeab6ad720d431df0c46c45d345c
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Thông tin phòng</h3>
                    </div>
                    <div class="p-4">
                        <div class="space-y-3">
                            @php
                                $roomTypes = $booking->getRoomTypes();
                            @endphp
                            
                            @if(count($roomTypes) > 1)
                                {{-- Hiển thị nhiều loại phòng --}}
                                <div class="space-y-3">
                                    <p class="text-sm font-semibold text-gray-700">Các loại phòng:</p>
                                    @foreach($roomTypes as $roomType)
                                        @php
                                            $loaiPhong = \App\Models\LoaiPhong::find($roomType['loai_phong_id']);
                                        @endphp
                                        @if($loaiPhong)
                                            <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                                <img src="{{ asset($loaiPhong->anh ?? 'img/room/room-1.jpg') }}" 
                                                    alt="{{ $loaiPhong->ten_loai }}"
                                                    class="w-48 h-32 object-cover rounded-lg flex-shrink-0">
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium text-gray-900 mb-2">{{ $loaiPhong->ten_loai }}</p>
                                                    <p class="text-xs text-gray-600 mb-1">Số lượng: {{ $roomType['so_luong'] }} phòng</p>
                                                    @php
                                                        $giaCoBan = $loaiPhong->gia_co_ban ?? 0;
                                                        $giaKhuyenMai = $loaiPhong->gia_khuyen_mai ?? null;
                                                        $lpUnit = $giaKhuyenMai ?? $giaCoBan;
                                                        $soLuong = $roomType['so_luong'] ?? 1;
                                                        $nights = ($booking && $booking->ngay_nhan && $booking->ngay_tra) ? \Carbon\Carbon::parse($booking->ngay_nhan)->diffInDays(\Carbon\Carbon::parse($booking->ngay_tra)) : 1;
                                                        $nights = max(1, $nights);
                                                        $subtotal = $lpUnit * $nights * $soLuong;
                                                    @endphp
                                                    <div class="text-xs text-gray-600 mb-1">
                                                        Giá/đêm: 
                                                        @if($giaKhuyenMai && $giaKhuyenMai < $giaCoBan)
                                                            <span class="line-through text-gray-400">{{ number_format($giaCoBan, 0, ',', '.') }}</span>
                                                            <span class="text-red-600 font-semibold ml-1">{{ number_format($giaKhuyenMai, 0, ',', '.') }} VNĐ</span>
                                                        @else
                                                            <span class="font-semibold">{{ number_format($giaCoBan, 0, ',', '.') }} VNĐ</span>
                                                        @endif
                                                    </div>
                                                    <p class="text-xs text-gray-600">Tổng: {{ number_format($subtotal, 0, ',', '.') }} VNĐ</p>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                <p class="text-sm text-gray-600">Tổng số phòng: <span class="font-medium">{{ $booking->so_luong_da_dat ?? 1 }} phòng</span></p>
                            @else
                                {{-- Hiển thị 1 loại phòng (legacy) --}}
                                <img src="{{ asset($booking->loaiPhong->anh ?? 'img/room/room-1.jpg') }}" 
                                    alt="{{ $booking->loaiPhong->ten_loai ?? 'N/A' }}"
                                    class="w-32 h-32 object-cover rounded-lg flex-shrink-0">
                                <div class="flex-1">
                                    <h3 class="text-xl font-semibold text-gray-900">{{ $booking->loaiPhong->ten_loai ?? 'N/A' }}</h3>
                                    <div class="mt-3 grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-500">Số lượng phòng</p>
                                            <p class="text-lg font-semibold text-gray-900">{{ $booking->so_luong_da_dat ?? 1 }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Giá/đêm</p>
                                            @php
                                                $loaiPhong = $booking->loaiPhong;
                                                $giaCoBan = $loaiPhong->gia_co_ban ?? 0;
                                                $giaKhuyenMai = $loaiPhong->gia_khuyen_mai ?? null;
                                            @endphp
                                            @if($giaKhuyenMai && $giaKhuyenMai < $giaCoBan)
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm text-gray-400 line-through">{{ number_format($giaCoBan, 0, ',', '.') }}</span>
                                                    <span class="text-lg font-semibold text-red-600">{{ number_format($giaKhuyenMai, 0, ',', '.') }} VNĐ</span>
                                                </div>
                                            @else
                                                <p class="text-lg font-semibold text-gray-900">{{ number_format($giaCoBan, 0, ',', '.') }} VNĐ</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                            @if($assignedCount > 0)
                                <div class="mt-2">
                                    <p class="text-sm font-medium text-gray-700 mb-2">
                                        Phòng đã gán ({{ $assignedCount }}/{{ $booking->so_luong_da_dat }}):
                                    </p>
                                    <div class="space-y-2">
                                        @foreach($assignedPhongs as $phong)
                                            <div class="p-2 bg-blue-50 border border-blue-200 rounded-md">
                                                <p class="text-sm font-medium text-blue-900">
                                                    Phòng: {{ $phong->so_phong }}
                                                    @if($phong->ten_phong)
                                                        ({{ $phong->ten_phong }})
                                                    @endif
                                                </p>
                                                <p class="text-xs text-blue-700 mt-1">
                                                    Tầng: {{ $phong->tang ?? 'N/A' }} | 
                                                    Trạng thái: 
                                                    <span class="
                                                        @if($phong->trang_thai === 'trong') text-green-600
                                                        @elseif($phong->trang_thai === 'dang_thue') text-blue-600
                                                        @elseif($phong->trang_thai === 'dang_don') text-yellow-600
                                                        @else text-red-600 @endif
                                                    ">
                                                        {{ $phong->trang_thai === 'trong' ? 'Trống' : 
                                                           ($phong->trang_thai === 'dang_thue' ? 'Đang thuê' : 
                                                           ($phong->trang_thai === 'dang_don' ? 'Đang dọn' : 'Bảo trì')) }}
                                                    </span>
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif($booking->phong)
                                {{-- Legacy support: Hiển thị phòng từ phong_id --}}
                                <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-md">
                                    <p class="text-sm font-medium text-blue-900">
                                        Phòng: {{ $booking->phong->so_phong }}
                                        @if($booking->phong->ten_phong)
                                            ({{ $booking->phong->ten_phong }})
                                        @endif
                                    </p>
                                    <p class="text-xs text-blue-700 mt-1">
                                        Tầng: {{ $booking->phong->tang ?? 'N/A' }} | 
                                        Trạng thái: 
                                        <span class="
                                            @if($booking->phong->trang_thai === 'trong') text-green-600
                                            @elseif($booking->phong->trang_thai === 'dang_thue') text-blue-600
                                            @elseif($booking->phong->trang_thai === 'dang_don') text-yellow-600
                                            @else text-red-600 @endif
                                        ">
                                            {{ $booking->phong->trang_thai === 'trong' ? 'Trống' : 
                                               ($booking->phong->trang_thai === 'dang_thue' ? 'Đang thuê' : 
                                               ($booking->phong->trang_thai === 'dang_don' ? 'Đang dọn' : 'Bảo trì')) }}
                                        </span>
                                    </p>
                                </div>
                            @else
                                <p class="text-sm text-yellow-600 mt-2">
                                    <i class="fas fa-exclamation-triangle text-xs mr-1"></i>
                                    Chưa gán phòng cụ thể
                                </p>
                            @endif

                            @if($remainingCount > 0 && isset($availableRooms) && $availableRooms->count() > 0)
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <p class="text-xs text-gray-600 mb-2">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Còn thiếu {{ $remainingCount }} phòng. Chọn phòng để gán:
                                    </p>
                                    <form action="{{ route('admin.dat_phong.assign_room', $booking->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <select name="phong_id" class="mt-1 w-full text-xs border-gray-300 rounded-md">
                                            <option value="">-- Chọn phòng --</option>
                                            @foreach($availableRooms as $room)
                                                <option value="{{ $room->id }}">
                                                    {{ $room->so_phong }} 
                                                    @if($room->tang) (Tầng {{ $room->tang }}) @endif
                                                    @if($room->co_view_dep) - View đẹp @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" 
                                            class="mt-2 px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                                            Gán phòng
                                        </button>
                                    </form>
                                </div>
                            @elseif($remainingCount > 0)
                                <p class="text-xs text-gray-500 mt-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Còn thiếu {{ $remainingCount }} phòng. Không có phòng trống trong khoảng thời gian này.
                                </p>
                            @endif
                            @if(count($roomTypes) > 1)
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <p class="text-xs text-gray-600 mb-2">Tổng giá: <span class="font-medium">{{ number_format($booking->tong_tien, 0, ',', '.') }} VNĐ</span></p>
                                </div>
                            @else
                                <p class="text-sm text-gray-600">Giá phòng: <span class="font-medium">{{ number_format($booking->loaiPhong->gia_khuyen_mai ?? $booking->loaiPhong->gia_co_ban ?? 0, 0, ',', '.') }} VNĐ/đêm</span></p>
                                <p class="text-sm px-3 py-1 rounded-full text-sm font-medium
                                    @if ($booking->loaiPhong->trang_thai === 'hoat_dong') bg-green-100 text-green-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ $booking->loaiPhong->trang_thai === 'hoat_dong' ? 'Hoạt động' : 'Ngừng' }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Card Thông tin đặt phòng -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Thông tin đặt phòng</h3>
                    </div>
                    <div class="p-4">
                        <div class="space-y-3">
                            <p class="text-sm text-gray-600">Ngày đặt: <span class="font-medium">{{ date('d/m/Y H:i', strtotime($booking->ngay_dat)) }}</span></p>
                            <p class="text-sm text-gray-600">Số người: <span class="font-medium">{{ $booking->so_nguoi }} người</span></p>
                            <p class="text-sm text-gray-600">Ngày nhận phòng: <span class="font-medium">{{ date('d/m/Y', strtotime($booking->ngay_nhan)) }}</span></p>
                            <p class="text-sm text-gray-600">Ngày trả phòng: <span class="font-medium">{{ date('d/m/Y', strtotime($booking->ngay_tra)) }}</span></p>
                            @if ($booking->ghi_chu)
                                <p class="text-sm text-gray-600">Ghi chú: <span class="font-medium">{{ $booking->ghi_chu }}</span></p>
                            @endif

                            <!-- Dịch vụ đã đặt (bên trong card - dạng compact) -->
                            @if(isset($bookingServices) && $bookingServices->count() > 0)
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-2">
                                        <i class="fas fa-concierge-bell mr-2 text-teal-600"></i>Dịch vụ đã đặt
                                    </h4>
                                    <div class="text-xs space-y-1">
                                        @foreach($bookingServices as $bs)
                                            @php $line = ($bs->unit_price * $bs->quantity); @endphp
                                            <div class="flex justify-between items-center px-2 py-1 bg-teal-50 rounded border border-teal-100">
                                                <span class="text-teal-900 font-medium">{{ $bs->service->name ?? 'Dịch vụ' }}</span>
                                                <span class="text-teal-700">
                                                    @if($bs->used_at)
                                                        <span class="text-teal-600 font-medium">{{ date('d/m', strtotime($bs->used_at)) }}</span>
                                                    @endif
                                                    | {{ $bs->quantity }}× 
                                                    <span class="font-medium">{{ number_format($line,0,',','.') }} VNĐ</span>
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Card Thông tin khách hàng -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Thông tin khách hàng</h3>
                    </div>
                    <div class="p-4">
                        <div class="space-y-3">
                            <p class="text-sm text-gray-600">Tên khách: <span class="font-medium">{{ $booking->username }}</span></p>
                            <p class="text-sm text-gray-600">Email: <span class="font-medium">{{ $booking->email }}</span></p>
                            <p class="text-sm text-gray-600">Số điện thoại: <span class="font-medium">{{ $booking->sdt }}</span></p>
                            <p class="text-sm text-gray-600">CCCD/CMND: 
                                @if($booking->cccd)
                                    <span class="font-medium">{{ $booking->cccd }}</span>
                                @else
                                    <span class="text-yellow-600 italic">
                                        <i class="fas fa-exclamation-triangle text-xs mr-1"></i>
                                        Chưa cập nhật
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Card Thông tin thanh toán -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Thông tin thanh toán</h3>
                    </div>
                    <div class="p-4">
                        <div class="space-y-3">
                            @php $roomOnly = $booking->tong_tien - ($serviceTotal ?? 0); @endphp
                            <p class="text-sm text-gray-600">Tổng tiền phòng: <span class="font-medium">{{ number_format($roomOnly, 0, ',', '.') }} VNĐ</span></p>
                            @if(isset($serviceTotal) && $serviceTotal > 0)
                                <p class="text-sm text-gray-600">Tổng tiền dịch vụ: <span class="font-medium">{{ number_format($serviceTotal, 0, ',', '.') }} VNĐ</span></p>
                            @endif
                            <p class="text-sm text-gray-600">Tổng thanh toán: <span class="font-medium">{{ number_format($booking->tong_tien, 0, ',', '.') }} VNĐ</span></p>
                            @if($booking->voucher)
                                <p class="text-sm text-gray-600">Mã voucher: <span class="font-medium text-indigo-600">{{ $booking->voucher->ma_voucher }}</span></p>
                                <p class="text-sm text-gray-600">Giảm giá: <span class="font-medium">{{ $booking->voucher->giam_gia }}%</span></p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card thông tin hủy (nếu có) -->
            @if($booking->trang_thai === 'da_huy')
                <div class="mt-6">
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Thông tin hủy đặt phòng</h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-3">
                                <p class="text-sm text-gray-600">Ngày hủy: <span class="font-medium">{{ date('d/m/Y H:i', strtotime($booking->ngay_huy)) }}</span></p>
                                <p class="text-sm text-gray-600">Lý do hủy: <span class="font-medium">
                                    @php
                                        $reasons = [
                                            'thay_doi_lich_trinh' => 'Thay đổi lịch trình',
                                            'thay_doi_ke_hoach' => 'Thay đổi kế hoạch',
                                            'khong_phu_hop' => 'Không phù hợp với yêu cầu',
                                            'ly_do_khac' => 'Lý do khác'
                                        ];
                                    @endphp
                                    {{ $reasons[$booking->ly_do_huy] ?? $booking->ly_do_huy }}
                                </span></p>
                            </div>
                        </dl>

                        {{-- THÔNG TIN HOÀN TIỀN --}}
                        @if($booking->invoice && $booking->invoice->trang_thai === 'hoan_tien')
                            @php
                                // Lấy payment record hoàn tiền (số tiền âm)
                                $refundPayment = $booking->invoice->thanhToans()
                                    ->where('so_tien', '<=', 0)
                                    ->orderBy('ngay_thanh_toan', 'desc')
                                    ->first();
                            @endphp
                            
                            @if($refundPayment)
                                <div class="mt-4 pt-4 border-t border-red-300">
                                    <h4 class="text-base font-semibold text-red-900 mb-3 flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                        Thông tin hoàn tiền
                                    </h4>
                                    
                                    <div class="bg-white rounded-lg p-4 border border-red-200">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-sm font-medium text-gray-700">Số tiền hoàn:</span>
                                            <span class="text-xl font-bold {{ abs($refundPayment->so_tien) > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ abs($refundPayment->so_tien) > 0 ? number_format(abs($refundPayment->so_tien), 0, ',', '.') . ' VNĐ' : 'Không hoàn tiền' }}
                                            </span>
                                        </div>
                                        
                                        @if($refundPayment->ghi_chu)
                                            <div class="mt-3 pt-3 border-t border-gray-200">
                                                <p class="text-xs text-gray-600 font-medium mb-1">Chi tiết:</p>
                                                <p class="text-sm text-gray-700">{{ $refundPayment->ghi_chu }}</p>
                                            </div>
                                        @endif
                                        
                                        <div class="mt-3 pt-3 border-t border-gray-200">
                                            <div class="flex justify-between text-xs text-gray-600">
                                                <span>Ngày xử lý:</span>
                                                <span>{{ $refundPayment->ngay_thanh_toan ? date('d/m/Y H:i', strtotime($refundPayment->ngay_thanh_toan)) : 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                @endif
            </div>

            {{-- SIDEBAR (RIGHT) --}}
            <div class="lg:col-span-4 mt-6 lg:mt-0">
                <div class="sticky top-6 space-y-6">

                    {{-- THANH TOÁN --}}
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Thanh toán
                            </h2>
                        </div>
                        <div class="p-6">
                            @if($booking->voucher)
                                <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                                    <p class="text-sm font-medium text-green-900">Mã giảm giá</p>
                                    <p class="text-lg font-bold text-green-700">{{ $booking->voucher->ma_voucher }}</p>
                                    <p class="text-sm text-green-600 mt-1">Giảm {{ $booking->voucher->gia_tri }}%</p>
                                </div>
                            @endif

                            {{-- Danh sách dịch vụ --}}
                            @php
                                $bookingServices = \App\Models\BookingService::with('service')
                                    ->where('dat_phong_id', $booking->id)
                                    ->orderBy('used_at')
                                    ->get();
                            @endphp
                            
                            @if($bookingServices->count() > 0)
                                <div class="mb-4">
                                    <p class="text-sm font-medium text-gray-900 mb-3 flex items-center">
                                        <i class="fas fa-concierge-bell text-purple-600 mr-2"></i>
                                        Dịch vụ đã sử dụng
                                    </p>
                                    <div class="space-y-2">
                                        @foreach($bookingServices as $bs)
                                            <div class="flex justify-between items-center bg-purple-50 border border-purple-200 rounded-lg px-3 py-2">
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium text-gray-900">{{ $bs->service->name ?? 'N/A' }}</p>
                                                    <p class="text-xs text-gray-600">
                                                        {{ $bs->used_at ? \Carbon\Carbon::parse($bs->used_at)->format('d/m/Y') : 'N/A' }} 
                                                        • {{ $bs->quantity }} {{ $bs->service->unit ?? 'lần' }}
                                                    </p>
                                                </div>
                                                <span class="text-sm font-semibold text-purple-700">
                                                    {{ number_format($bs->quantity * $bs->unit_price, 0, ',', '.') }} VNĐ
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @php
                                // Lấy dữ liệu từ invoice nếu có, nếu không thì tính toán
                                $invoice = $booking->invoice;
                                
                                if ($invoice && $invoice->tien_phong) {
                                    // Sử dụng dữ liệu từ invoice
                                    $tienPhong = $invoice->tien_phong;
                                    $tienDichVu = $invoice->tien_dich_vu ?? 0;
                                    $giamGia = $invoice->giam_gia ?? 0;
                                } else {
                                    // Tính toán nếu chưa có invoice
                                    $checkin = new DateTime($booking->ngay_nhan);
                                    $checkout = new DateTime($booking->ngay_tra);
                                    $nights = $checkin->diff($checkout)->days;
                                    
                                    $tienPhong = 0;
                                    foreach($booking->getRoomTypes() as $rt) {
                                        $loaiPhong = \App\Models\LoaiPhong::find($rt['loai_phong_id']);
                                        if($loaiPhong) {
                                            $giaGoc = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban;
                                            $tienPhong += $giaGoc * $rt['so_luong'] * $nights;
                                        }
                                    }
                                    
                                    // Tính tiền dịch vụ
                                    $tienDichVu = \App\Models\BookingService::where('dat_phong_id', $booking->id)
                                        ->sum(\DB::raw('quantity * unit_price'));
                                    
                                    // Tính giảm giá (chỉ áp dụng cho tiền phòng)
                                    $giamGia = 0;
                                    if($booking->voucher) {
                                        $giamGia = $tienPhong * ($booking->voucher->gia_tri / 100);
                                    }
                                }
                            @endphp

                            <dl class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <dt class="text-gray-600">Tổng tiền phòng</dt>
                                    <dd class="font-medium text-gray-900">{{ number_format($tienPhong, 0, ',', '.') }} VNĐ</dd>
                                </div>
                                

                                
                                @if($giamGia > 0)
                                    <div class="flex justify-between text-sm text-red-600">
                                        <dt>Giảm giá @if($booking->voucher)({{ $booking->voucher->ma_voucher }} - {{ $booking->voucher->gia_tri }}%)@endif</dt>
                                        <dd class="font-medium">-{{ number_format($giamGia, 0, ',', '.') }} VNĐ</dd>
                                    </div>
                                @endif
                                
                                <div class="pt-3 border-t border-gray-200">
                                    <div class="flex justify-between">
                                        <dt class="text-base font-semibold text-gray-900">Tổng thanh toán</dt>
                                        <dd class="text-xl font-bold text-blue-600">{{ number_format($booking->tong_tien, 0, ',', '.') }} VNĐ</dd>
                                    </div>
                                </div>
                            </dl>

                            @if($booking->invoice)
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <p class="text-sm text-gray-600 mb-2">Trạng thái thanh toán</p>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                        @if($booking->invoice->trang_thai === 'da_thanh_toan') bg-green-100 text-green-800
                                        @elseif($booking->invoice->trang_thai === 'cho_thanh_toan') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        @if($booking->invoice->trang_thai === 'da_thanh_toan')
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            Đã thanh toán
                                        @elseif($booking->invoice->trang_thai === 'cho_thanh_toan')
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                            </svg>
                                            Chờ thanh toán
                                        @else
                                            Hoàn tiền
                                        @endif
                                    </span>
                                    
                                    @if($booking->invoice->phuong_thuc)
                                        <p class="text-sm text-gray-600 mt-2">
                                            Phương thức: 
                                            <span class="font-medium">
                                                @if($booking->invoice->phuong_thuc === 'vnpay') VNPay
                                                @elseif($booking->invoice->phuong_thuc === 'tien_mat') Tiền mặt
                                                @elseif($booking->invoice->phuong_thuc === 'chuyen_khoan') Chuyển khoản
                                                @else {{ $booking->invoice->phuong_thuc }}
                                                @endif
                                            </span>
                                        </p>
                                    @endif
                                </div>

                                @if($booking->invoice->trang_thai === 'cho_thanh_toan' && $booking->trang_thai === 'da_xac_nhan')
                                    <form action="{{ route('admin.dat_phong.mark_paid', $booking->id) }}" method="POST" class="mt-4">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" 
                                            class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                                            Đánh dấu đã thanh toán
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Nút thao tác -->
            <div class="mt-6 flex justify-between">
                <a href="{{ route('admin.dat_phong.index') }}" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Quay lại
                </a>
                @if($booking->trang_thai === 'cho_xac_nhan')
                    <div class="space-x-3">
                        <a href="{{ route('admin.dat_phong.edit', $booking->id) }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Sửa thông tin
                        </a>
                        <a href="{{ route('admin.dat_phong.cancel', $booking->id) }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Hủy đặt phòng
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

