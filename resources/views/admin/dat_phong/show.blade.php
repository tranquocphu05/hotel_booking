@extends('layouts.admin')

@section('title', 'Chi tiết đặt phòng #' . $booking->id)

@section('admin_content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- HEADER WITH QUICK ACTIONS --}}
        <div class="bg-white rounded-lg shadow-sm mb-6 p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                {{-- Left: Title & Status --}}
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.dat_phong.index') }}" 
                        class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            Đặt phòng #{{ $booking->id }}
                        </h1>
                        <p class="text-sm text-gray-500 mt-1">
                            Đặt lúc {{ date('d/m/Y H:i', strtotime($booking->ngay_dat)) }}
                        </p>
                    </div>
                    <span class="px-4 py-2 rounded-full text-sm font-semibold
                        @if ($booking->trang_thai === 'da_xac_nhan') bg-green-100 text-green-800
                        @elseif($booking->trang_thai === 'cho_xac_nhan') bg-yellow-100 text-yellow-800
                        @elseif($booking->trang_thai === 'da_huy') bg-red-100 text-red-800
                        @elseif($booking->trang_thai === 'tu_choi') bg-red-100 text-red-800
                        @elseif($booking->trang_thai === 'da_tra') bg-blue-100 text-blue-800
                        @else bg-gray-100 text-gray-800 @endif">
                        @php
                            $statuses = [
                                'cho_xac_nhan' => 'Chờ xác nhận',
                                'da_xac_nhan' => 'Đã xác nhận',
                                'da_huy' => 'Đã hủy',
                                'tu_choi' => 'Từ chối',
                                'da_tra' => 'Đã trả phòng',
                                'thanh_toan_that_bai' => 'Thanh toán thất bại',
                            ];
                        @endphp
                        {{ $statuses[$booking->trang_thai] ?? $booking->trang_thai }}
                    </span>
                </div>

                {{-- Right: Quick Actions --}}
                <div class="flex flex-wrap gap-2">
                    @if($booking->trang_thai === 'cho_xac_nhan')
                        <form action="{{ route('admin.dat_phong.confirm', $booking->id) }}" method="POST" class="inline">
                            @csrf
                            @method('PUT')
                            <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Xác nhận
                            </button>
                        </form>
                        <a href="{{ route('admin.dat_phong.cancel', $booking->id) }}"
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Hủy
                        </a>
                    @endif
                    
                    @if($booking->trang_thai === 'cho_xac_nhan')
                        <a href="{{ route('admin.dat_phong.edit', $booking->id) }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Sửa
                        </a>
                    @endif

                    <button onclick="window.print()" 
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        In
                    </button>
                </div>
            </div>
        </div>

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

        {{-- MAIN CONTENT: 1 COLUMN LAYOUT WITH SIDEBAR --}}
        <div class="lg:grid lg:grid-cols-12 lg:gap-6">
            
            {{-- MAIN CONTENT (LEFT) --}}
            <div class="lg:col-span-8 space-y-6">

                {{-- THÔNG TIN PHÒNG --}}
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Thông tin phòng
                        </h2>
                    </div>
                    <div class="p-6">
                        @php
                            $roomTypes = $booking->getRoomTypes();
                            $assignedPhongs = $booking->getAssignedPhongs();
                        @endphp

                        @if(count($roomTypes) > 1)
                            {{-- Multi-room booking --}}
                            <div class="space-y-4">
                                @foreach($roomTypes as $index => $roomType)
                                    @php
                                        $loaiPhong = \App\Models\LoaiPhong::find($roomType['loai_phong_id']);
                                    @endphp
                                    @if($loaiPhong)
                                        <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition">
                                            <div class="flex gap-4">
                                                <img src="{{ asset($loaiPhong->anh ?? 'img/room/room-1.jpg') }}" 
                                                    alt="{{ $loaiPhong->ten_loai }}"
                                                    class="w-full h-32 object-cover rounded-lg mb-2">
                                                <p class="text-sm font-medium text-gray-900">{{ $loaiPhong->ten_loai }}</p>
                                                <p class="text-xs text-gray-600">Số lượng: {{ $roomType['so_luong'] }} phòng</p>
                                                @php
                                                    $lpUnit = $loaiPhong ? ($loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban ?? 0) : 0;
                                                    $soLuong = $roomType['so_luong'] ?? 1;
                                                    $nights = ($booking && $booking->ngay_nhan && $booking->ngay_tra) ? \Carbon\Carbon::parse($booking->ngay_nhan)->diffInDays(\Carbon\Carbon::parse($booking->ngay_tra)) : 1;
                                                    $nights = max(1, $nights);
                                                    $subtotal = $lpUnit * $nights * $soLuong;
                                                @endphp
                                                <p class="text-xs text-gray-600">Giá: {{ number_format($subtotal, 0, ',', '.') }} VNĐ</p>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            {{-- Single room type --}}
                            <div class="flex gap-6">
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
                                            <p class="text-lg font-semibold text-gray-900">{{ number_format($booking->loaiPhong->gia_co_ban ?? 0, 0, ',', '.') }} VNĐ</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Assigned Rooms --}}
                        @if($assignedPhongs->count() > 0)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="font-semibold text-gray-900 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Phòng đã sắp xếp
                                    </h4>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">
                                        {{ $assignedPhongs->count() }}/{{ $booking->so_luong_da_dat }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($assignedPhongs as $phong)
                                        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-lg hover:shadow-md transition">
                                            <div class="flex-1">
                                                <p class="font-bold text-blue-900 text-lg">
                                                    Phòng {{ $phong->so_phong }}
                                                </p>
                                                @if($phong->ten_phong)
                                                    <p class="text-sm text-blue-700 mt-0.5">{{ $phong->ten_phong }}</p>
                                                @endif
                                                <div class="flex items-center gap-3 mt-2 text-xs">
                                                    <span class="text-blue-600">
                                                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                                        </svg>
                                                        Tầng {{ $phong->tang ?? 'N/A' }}
                                                    </span>
                                                    <span class="px-2 py-0.5 rounded-full font-medium
                                                        @if($phong->trang_thai === 'trong') bg-green-100 text-green-700
                                                        @elseif($phong->trang_thai === 'dang_thue') bg-blue-100 text-blue-700
                                                        @elseif($phong->trang_thai === 'dang_don') bg-yellow-100 text-yellow-700
                                                        @else bg-red-100 text-red-700 @endif
                                                    ">
                                                        {{ $phong->trang_thai === 'trong' ? 'Trống' : 
                                                           ($phong->trang_thai === 'dang_thue' ? 'Đang thuê' : 
                                                           ($phong->trang_thai === 'dang_don' ? 'Đang dọn' : 'Bảo trì')) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Assign Room Form --}}
                        @php
                            $remainingCount = $booking->so_luong_da_dat - $assignedPhongs->count();
                        @endphp
                        @if($remainingCount > 0 && isset($availableRooms) && $availableRooms->count() > 0)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <p class="text-sm text-yellow-800 mb-3">
                                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        Còn thiếu {{ $remainingCount }} phòng cần gán
                                    </p>
                                    <form action="{{ route('admin.dat_phong.assign_room', $booking->id) }}" method="POST" class="flex gap-2">
                                        @csrf
                                        @method('PUT')
                                        <select name="phong_id" class="flex-1 border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">-- Chọn phòng --</option>
                                            @foreach($availableRooms as $room)
                                                <option value="{{ $room->id }}">
                                                    Phòng {{ $room->so_phong }} 
                                                    @if($room->tang) (Tầng {{ $room->tang }}) @endif
                                                    @if($room->co_view_dep) - View đẹp @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" 
                                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
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

                {{-- THÔNG TIN ĐẶT PHÒNG --}}
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Chi tiết đặt phòng
                        </h2>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Ngày nhận phòng</dt>
                                <dd class="mt-1 text-lg font-semibold text-gray-900">{{ date('d/m/Y', strtotime($booking->ngay_nhan)) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Ngày trả phòng</dt>
                                <dd class="mt-1 text-lg font-semibold text-gray-900">{{ date('d/m/Y', strtotime($booking->ngay_tra)) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Số đêm</dt>
                                <dd class="mt-1 text-lg font-semibold text-gray-900">
                                    @php
                                        $checkin = new DateTime($booking->ngay_nhan);
                                        $checkout = new DateTime($booking->ngay_tra);
                                        $soDem = $checkin->diff($checkout)->days;
                                    @endphp
                                    {{ $soDem }} đêm
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Số người</dt>
                                <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $booking->so_nguoi }} người</dd>
                            </div>
                            @if($booking->ghi_chu)
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Ghi chú</dt>
                                    <dd class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $booking->ghi_chu }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                {{-- THÔNG TIN KHÁCH HÀNG --}}
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Thông tin khách hàng
                        </h2>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Họ tên</dt>
                                <dd class="mt-1 text-base font-semibold text-gray-900">{{ $booking->username }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="mt-1 text-base text-gray-900">
                                    <a href="mailto:{{ $booking->email }}" class="text-blue-600 hover:text-blue-800">
                                        {{ $booking->email }}
                                    </a>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Số điện thoại</dt>
                                <dd class="mt-1 text-base text-gray-900">
                                    <a href="tel:{{ $booking->sdt }}" class="text-blue-600 hover:text-blue-800">
                                        {{ $booking->sdt }}
                                    </a>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">CCCD/CMND</dt>
                                <dd class="mt-1 text-base font-mono text-gray-900">
                                    @if($booking->cccd)
                                        {{ $booking->cccd }}
                                    @else
                                        <span class="text-yellow-600 italic text-sm">
                                            <svg class="w-4 h-4 inline" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                            Chưa cập nhật
                                        </span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- THÔNG TIN HỦY (if cancelled) --}}
                @if($booking->trang_thai === 'da_huy' && $booking->ly_do_huy)
                    <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-red-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            Thông tin hủy đặt phòng
                        </h3>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-red-700">Ngày hủy</dt>
                                <dd class="mt-1 text-base text-red-900">{{ $booking->ngay_huy ? date('d/m/Y H:i', strtotime($booking->ngay_huy)) : 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-red-700">Lý do</dt>
                                <dd class="mt-1 text-base text-red-900">
                                    @php
                                        $reasons = [
                                            'thay_doi_lich_trinh' => 'Thay đổi lịch trình',
                                            'thay_doi_ke_hoach' => 'Thay đổi kế hoạch',
                                            'khong_phu_hop' => 'Không phù hợp với yêu cầu',
                                            'ly_do_khac' => 'Lý do khác'
                                        ];
                                    @endphp
                                    {{ $reasons[$booking->ly_do_huy] ?? $booking->ly_do_huy }}
                                </dd>
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

                            @php
                                // Tính tổng tiền gốc từ LoaiPhong (giá chưa giảm)
                                $checkin = new DateTime($booking->ngay_nhan);
                                $checkout = new DateTime($booking->ngay_tra);
                                $nights = $checkin->diff($checkout)->days;
                                
                                $subtotal = 0;
                                foreach($booking->getRoomTypes() as $rt) {
                                    $loaiPhong = \App\Models\LoaiPhong::find($rt['loai_phong_id']);
                                    if($loaiPhong) {
                                        $giaGoc = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban;
                                        $subtotal += $giaGoc * $rt['so_luong'] * $nights;
                                    }
                                }
                                
                                // Tính giảm giá nếu có voucher
                                $discount = 0;
                                if($booking->voucher) {
                                    $discount = $subtotal * ($booking->voucher->gia_tri / 100);
                                }
                            @endphp

                            <dl class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <dt class="text-gray-600">Tổng tiền phòng</dt>
                                    <dd class="font-medium text-gray-900">{{ number_format($subtotal, 0, ',', '.') }} VNĐ</dd>
                                </div>
                                
                                @if($booking->voucher)
                                    <div class="flex justify-between text-sm text-green-600">
                                        <dt>Giảm giá ({{ $booking->voucher->ma_voucher }} - {{ $booking->voucher->gia_tri }}%)</dt>
                                        <dd class="font-medium">-{{ number_format($discount, 0, ',', '.') }} VNĐ</dd>
                                    </div>
                                    <div class="pt-3 border-t border-gray-200">
                                        <div class="flex justify-between">
                                            <dt class="text-base font-semibold text-gray-900">Tổng thanh toán</dt>
                                            <dd class="text-xl font-bold text-blue-600">{{ number_format($booking->tong_tien, 0, ',', '.') }} VNĐ</dd>
                                        </div>
                                    </div>
                                @else
                                    <div class="pt-3 border-t border-gray-200">
                                        <div class="flex justify-between">
                                            <dt class="text-base font-semibold text-gray-900">Tổng thanh toán</dt>
                                            <dd class="text-xl font-bold text-blue-600">{{ number_format($booking->tong_tien, 0, ',', '.') }} VNĐ</dd>
                                        </div>
                                    </div>
                                @endif
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

                    {{-- TIMELINE --}}
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Lịch sử
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="flow-root">
                                <ul class="-mb-8">
                                    {{-- Booking Created --}}
                                    <li>
                                        <div class="relative pb-8">
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-900 font-medium">Đặt phòng được tạo</p>
                                                        <p class="text-xs text-gray-500 mt-0.5">Bởi {{ $booking->username }}</p>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        <time>{{ date('d/m/Y H:i', strtotime($booking->ngay_dat)) }}</time>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    {{-- Status Changes --}}
                                    @if($booking->trang_thai === 'da_xac_nhan')
                                        <li>
                                            <div class="relative pb-8">
                                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                        <div>
                                                            <p class="text-sm text-gray-900 font-medium">Đã xác nhận</p>
                                                            <p class="text-xs text-gray-500 mt-0.5">Booking đã được xác nhận</p>
                                                        </div>
                                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                            <time>{{ $booking->updated_at ? date('d/m/Y H:i', strtotime($booking->updated_at)) : 'N/A' }}</time>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endif

                                    @if($booking->trang_thai === 'da_huy')
                                        <li>
                                            <div class="relative pb-8">
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                        <div>
                                                            <p class="text-sm text-gray-900 font-medium">Đã hủy</p>
                                                            <p class="text-xs text-gray-500 mt-0.5">{{ $booking->ly_do_huy ?? 'Không có lý do' }}</p>
                                                        </div>
                                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                            <time>{{ $booking->ngay_huy ? date('d/m/Y H:i', strtotime($booking->ngay_huy)) : 'N/A' }}</time>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endif

                                    @if($booking->trang_thai === 'da_tra')
                                        <li>
                                            <div class="relative pb-8">
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                        <div>
                                                            <p class="text-sm text-gray-900 font-medium">Đã trả phòng</p>
                                                            <p class="text-xs text-gray-500 mt-0.5">Khách đã check-out</p>
                                                        </div>
                                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                            <time>{{ $booking->updated_at ? date('d/m/Y H:i', strtotime($booking->updated_at)) : 'N/A' }}</time>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
