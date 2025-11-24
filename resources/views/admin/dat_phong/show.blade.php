@extends('layouts.admin')

@section('title', 'Chi tiết đặt phòng')

@section('admin_content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- BREADCRUMB --}}
            <div class="mb-6">
                <a href="{{ route('admin.dat_phong.index') }}"
                   class="inline-flex items-center text-sm text-gray-600 hover:text-blue-600 transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Quay lại danh sách
                </a>
            </div>

            {{-- HEADER --}}
            <div class="mb-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-start justify-between">
                    <div>
                        @php
                            $roomTypes = $booking->getRoomTypes();
                        @endphp
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">
                            Chi tiết đặt phòng #{{ $booking->id }}
                        </h1>
                        <p class="text-gray-600">
                            @if(count($roomTypes) > 1)
                                {{ count($roomTypes) }} loại phòng • {{ $booking->so_luong_da_dat }} phòng
                            @else
                                {{ $booking->loaiPhong->ten_loai ?? 'N/A' }} • {{ $booking->so_luong_da_dat }} phòng
                            @endif
                        </p>
                    </div>

                    <span class="px-4 py-2 rounded-lg text-sm font-semibold border-2
                        @if ($booking->trang_thai === 'da_xac_nhan') bg-green-50 text-green-700 border-green-200
                        @elseif($booking->trang_thai === 'cho_xac_nhan') bg-yellow-50 text-yellow-700 border-yellow-200
                        @elseif($booking->trang_thai === 'da_huy') bg-red-50 text-red-700 border-red-200
                        @elseif($booking->trang_thai === 'da_tra') bg-blue-50 text-blue-700 border-blue-200
                        @else bg-gray-50 text-gray-700 border-gray-200 @endif">
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
            </div>

            {{-- TIMELINE --}}
            <div class="mb-8 bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Trạng thái đơn đặt phòng</h2>

                <div class="relative">
                    {{-- Timeline Line --}}
                    <div class="absolute top-8 left-0 w-full h-0.5 bg-gray-200" style="z-index: 0;"></div>

                    {{-- Timeline Steps --}}
                    <div class="relative grid grid-cols-5 gap-4" style="z-index: 1;">

                        {{-- Step 1: Đặt phòng --}}
                        @php
                            $step1Complete = true; // Luôn complete vì đã tạo booking
                            $step1Date = $booking->ngay_dat;
                        @endphp
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center mb-3 {{ $step1Complete ? 'bg-blue-600' : 'bg-gray-200' }}">
                                <svg class="w-8 h-8 {{ $step1Complete ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold {{ $step1Complete ? 'text-blue-600' : 'text-gray-400' }} mb-1">Đặt phòng</p>
                            @if($step1Complete && $step1Date)
                                <p class="text-xs text-gray-500">{{ date('d/m/Y H:i', strtotime($step1Date)) }}</p>
                            @endif
                        </div>

                        {{-- Step 2: Xác nhận --}}
                        @php
                            $step2Complete = in_array($booking->trang_thai, ['da_xac_nhan', 'da_tra']);
                            $step2Date = null; // Laravel không track ngày xác nhận mặc định
                        @endphp
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center mb-3 {{ $step2Complete ? 'bg-green-600' : ($booking->trang_thai === 'cho_xac_nhan' ? 'bg-yellow-400' : 'bg-gray-200') }}">
                                <svg class="w-8 h-8 {{ $step2Complete ? 'text-white' : ($booking->trang_thai === 'cho_xac_nhan' ? 'text-white' : 'text-gray-400') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold {{ $step2Complete ? 'text-green-600' : ($booking->trang_thai === 'cho_xac_nhan' ? 'text-yellow-600' : 'text-gray-400') }} mb-1">Xác nhận</p>
                            @if($step2Complete && $booking->invoice && $booking->invoice->ngay_thanh_toan)
                                <p class="text-xs text-gray-500">{{ date('d/m/Y H:i', strtotime($booking->invoice->ngay_thanh_toan)) }}</p>
                            @elseif($booking->trang_thai === 'cho_xac_nhan')
                                <p class="text-xs text-yellow-600">Đang chờ</p>
                            @endif
                        </div>

                        {{-- Step 3: Check-in --}}
                        @php
                            $step3Complete = $booking->thoi_gian_checkin !== null;
                            $step3Date = $booking->thoi_gian_checkin;
                        @endphp
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center mb-3 {{ $step3Complete ? 'bg-purple-600' : 'bg-gray-200' }}">
                                <svg class="w-8 h-8 {{ $step3Complete ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold {{ $step3Complete ? 'text-purple-600' : 'text-gray-400' }} mb-1">Check-in</p>
                            @if($step3Complete && $step3Date)
                                <p class="text-xs text-gray-500">{{ $step3Date->format('d/m/Y H:i') }}</p>
                                @if($booking->nguoi_checkin)
                                    <p class="text-xs text-gray-400">{{ $booking->nguoi_checkin }}</p>
                                @endif
                            @endif
                        </div>

                        {{-- Step 4: Check-out --}}
                        @php
                            $step4Complete = $booking->thoi_gian_checkout !== null;
                            $step4Date = $booking->thoi_gian_checkout;
                        @endphp
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center mb-3 {{ $step4Complete ? 'bg-indigo-600' : 'bg-gray-200' }}">
                                <svg class="w-8 h-8 {{ $step4Complete ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold {{ $step4Complete ? 'text-indigo-600' : 'text-gray-400' }} mb-1">Check-out</p>
                            @if($step4Complete && $step4Date)
                                <p class="text-xs text-gray-500">{{ $step4Date->format('d/m/Y H:i') }}</p>
                                @if($booking->nguoi_checkout)
                                    <p class="text-xs text-gray-400">{{ $booking->nguoi_checkout }}</p>
                                @endif
                            @endif
                        </div>

                        {{-- Step 5: Hoàn thành hoặc Hủy --}}
                        @php
                            $step5Complete = ($booking->trang_thai === 'da_tra') || ($booking->trang_thai === 'da_huy');
                            $step5Cancelled = $booking->trang_thai === 'da_huy';
                            $step5Date = $step5Cancelled ? $booking->ngay_huy : ($step4Complete ? $step4Date : null);
                        @endphp
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center mb-3 {{ $step5Complete ? ($step5Cancelled ? 'bg-red-600' : 'bg-green-600') : 'bg-gray-200' }}">
                                @if($step5Cancelled)
                                    <svg class="w-8 h-8 {{ $step5Complete ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                @else
                                    <svg class="w-8 h-8 {{ $step5Complete ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @endif
                            </div>
                            <p class="text-sm font-semibold {{ $step5Complete ? ($step5Cancelled ? 'text-red-600' : 'text-green-600') : 'text-gray-400' }} mb-1">
                                {{ $step5Cancelled ? 'Đã hủy' : 'Hoàn thành' }}
                            </p>
                            @if($step5Complete && $step5Date)
                                <p class="text-xs text-gray-500">{{ date('d/m/Y H:i', strtotime($step5Date)) }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Lý do hủy nếu có --}}
                @if($booking->trang_thai === 'da_huy' && $booking->ly_do_huy)
                    <div class="mt-8 p-4 bg-red-50 border-l-4 border-red-500 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-semibold text-red-800">Lý do hủy phòng</h3>
                                <p class="mt-1 text-sm text-red-700">{{ $booking->ly_do_huy }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- CANCELLATION POLICY --}}
            @if($booking->trang_thai === 'da_xac_nhan' && isset($cancellationPolicy))
                <div class="mb-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Chính sách hủy phòng
                    </h2>

                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        {{-- Time Info --}}
                        <div class="bg-blue-50 p-5 rounded-lg border border-blue-200">
                            <div class="space-y-3">
                                <div class="flex justify-between items-center pb-3 border-b border-blue-200">
                                    <span class="text-sm font-medium text-gray-700">Ngày nhận phòng</span>
                                    <span class="text-lg font-bold text-blue-600">{{ date('d/m/Y', strtotime($booking->ngay_nhan)) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Thời gian còn lại</span>
                                    <span class="text-2xl font-bold text-blue-600">
                                        @if($cancellationPolicy['days_until_checkin'] < 0)
                                            Đã qua ngày
                                        @else
                                            {{ max(0, (int)$cancellationPolicy['days_until_checkin']) }} ngày
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Refund Info --}}
                        <div class="bg-green-50 p-5 rounded-lg border border-green-200">
                            <p class="text-sm font-semibold text-gray-700 mb-3">Nếu hủy phòng ngay:</p>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Hoàn lại</span>
                                    <div class="text-right">
                                        <p class="text-xl font-bold text-green-600">
                                            {{ number_format($cancellationPolicy['refund_amount'], 0, ',', '.') }}₫
                                        </p>
                                        <p class="text-xs text-gray-500">({{ $cancellationPolicy['refund_percentage'] }}%)</p>
                                    </div>
                                </div>
                                @if($cancellationPolicy['penalty_amount'] > 0)
                                    <div class="flex justify-between items-center pt-3 border-t border-green-200">
                                        <span class="text-sm text-gray-600">Phí hủy</span>
                                        <p class="text-lg font-bold text-red-600">
                                            {{ number_format($cancellationPolicy['penalty_amount'], 0, ',', '.') }}₫
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Policy Table --}}
                    <div class="bg-gray-50 p-5 rounded-lg border border-gray-200 mb-6">
                        <p class="text-sm font-semibold text-gray-700 mb-4">Bảng chính sách hoàn tiền:</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div class="text-center p-3 bg-white rounded-lg border-2 border-green-200">
                                <p class="text-xs text-gray-600 mb-1">≥ 7 ngày</p>
                                <p class="text-xl font-bold text-green-600">100%</p>
                            </div>
                            <div class="text-center p-3 bg-white rounded-lg border-2 border-yellow-200">
                                <p class="text-xs text-gray-600 mb-1">3-6 ngày</p>
                                <p class="text-xl font-bold text-yellow-600">50%</p>
                            </div>
                            <div class="text-center p-3 bg-white rounded-lg border-2 border-orange-200">
                                <p class="text-xs text-gray-600 mb-1">1-2 ngày</p>
                                <p class="text-xl font-bold text-orange-600">25%</p>
                            </div>
                            <div class="text-center p-3 bg-white rounded-lg border-2 border-red-200">
                                <p class="text-xs text-gray-600 mb-1">Trong ngày</p>
                                <p class="text-xl font-bold text-red-600">0%</p>
                            </div>
                        </div>
                    </div>

                    @if($cancellationPolicy['can_cancel'])
                        <div class="flex justify-end">
                            <a href="{{ route('admin.dat_phong.cancel', $booking->id) }}"
                               class="inline-flex items-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow-sm transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Hủy đặt phòng
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            {{-- CHECK-IN / CHECK-OUT SECTION --}}
            @include('admin.dat_phong._checkin_checkout')

            {{-- BOOKING SERVICES SECTION --}}
            @include('admin.dat_phong._booking_services')

            {{-- MAIN CONTENT GRID --}}
            <div class="grid lg:grid-cols-3 gap-6">

                {{-- LEFT COLUMN --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Room Info --}}
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900">Thông tin phòng</h3>
                        </div>
                        <div class="p-6">
                            @php
                                $roomTypes = $booking->getRoomTypes();
                            @endphp

                            @if(count($roomTypes) > 1)
                                <div class="space-y-4">
                                    @foreach($roomTypes as $roomType)
                                        @php
                                            $loaiPhong = \App\Models\LoaiPhong::find($roomType['loai_phong_id']);
                                        @endphp
                                        @if($loaiPhong)
                                            <div class="flex gap-4 p-4 border border-gray-200 rounded-lg hover:border-blue-300 transition">
                                                <img src="{{ asset($loaiPhong->anh ?? 'img/room/room-1.jpg') }}"
                                                    alt="{{ $loaiPhong->ten_loai }}"
                                                    class="w-32 h-32 object-cover rounded-lg">
                                                <div class="flex-1">
                                                    <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ $loaiPhong->ten_loai }}</h4>
                                                    <p class="text-sm text-gray-600 mb-1">Số lượng: {{ $roomType['so_luong'] }} phòng</p>
                                                    @php
                                                        $giaCoBan = $loaiPhong->gia_co_ban ?? 0;
                                                        $giaKhuyenMai = $loaiPhong->gia_khuyen_mai ?? null;
                                                        $lpUnit = $giaKhuyenMai ?? $giaCoBan;
                                                        $soLuong = $roomType['so_luong'] ?? 1;
                                                        $nights = ($booking && $booking->ngay_nhan && $booking->ngay_tra) ? \Carbon\Carbon::parse($booking->ngay_nhan)->diffInDays(\Carbon\Carbon::parse($booking->ngay_tra)) : 1;
                                                        $nights = max(1, $nights);
                                                        $subtotal = $lpUnit * $nights * $soLuong;
                                                    @endphp
                                                    <div class="text-sm mb-2">
                                                        <span class="text-gray-600">Giá/đêm:</span>
                                                        @if($giaKhuyenMai && $giaKhuyenMai < $giaCoBan)
                                                            <span class="line-through text-gray-400 ml-1">{{ number_format($giaCoBan, 0, ',', '.') }}</span>
                                                            <span class="text-red-600 font-semibold ml-1">{{ number_format($giaKhuyenMai, 0, ',', '.') }} VNĐ</span>
                                                        @else
                                                            <span class="font-semibold text-gray-900 ml-1">{{ number_format($giaCoBan, 0, ',', '.') }} VNĐ</span>
                                                        @endif
                                                    </div>
                                                    <p class="text-sm font-semibold text-blue-600">Tổng: {{ number_format($subtotal, 0, ',', '.') }} VNĐ</p>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div class="flex gap-6 items-start">
                                    <img src="{{ asset($booking->loaiPhong->anh ?? 'img/room/room-1.jpg') }}"
                                        alt="{{ $booking->loaiPhong->ten_loai ?? 'N/A' }}"
                                        class="w-48 h-48 object-cover rounded-lg">
                                    <div class="flex-1">
                                        <h3 class="text-2xl font-semibold text-gray-900 mb-4">{{ $booking->loaiPhong->ten_loai ?? 'N/A' }}</h3>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-sm text-gray-500 mb-1">Số lượng phòng</p>
                                                <p class="text-xl font-semibold text-gray-900">{{ $booking->so_luong_da_dat ?? 1 }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-500 mb-1">Giá/đêm</p>
                                                @php
                                                    $loaiPhong = $booking->loaiPhong;
                                                    $giaCoBan = $loaiPhong->gia_co_ban ?? 0;
                                                    $giaKhuyenMai = $loaiPhong->gia_khuyen_mai ?? null;
                                                @endphp
                                                @if($giaKhuyenMai && $giaKhuyenMai < $giaCoBan)
                                                    <div>
                                                        <span class="text-sm text-gray-400 line-through">{{ number_format($giaCoBan, 0, ',', '.') }}</span>
                                                        <span class="text-lg font-semibold text-red-600 ml-1">{{ number_format($giaKhuyenMai, 0, ',', '.') }} VNĐ</span>
                                                    </div>
                                                @else
                                                    <p class="text-lg font-semibold text-gray-900">{{ number_format($giaCoBan, 0, ',', '.') }} VNĐ</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Assigned Rooms --}}
                            @php
                                $assignedPhongs = $booking->getAssignedPhongs();
                                $assignedCount = $assignedPhongs->count();
                                $remainingCount = max(0, ($booking->so_luong_da_dat ?? 0) - $assignedCount);
                            @endphp

                            @if($assignedCount > 0)
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <p class="text-sm font-semibold text-gray-900 mb-3">
                                        Phòng đã gán ({{ $assignedCount }}/{{ $booking->so_luong_da_dat }}):
                                    </p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @foreach($assignedPhongs as $phong)
                                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                                <p class="text-sm font-semibold text-blue-900">
                                                    Phòng {{ $phong->so_phong }}
                                                    @if($phong->ten_phong)
                                                        <span class="text-xs text-gray-600">({{ $phong->ten_phong }})</span>
                                                    @endif
                                                </p>
                                                <p class="text-xs text-gray-600 mt-1">
                                                    Tầng {{ $phong->tang ?? 'N/A' }} •
                                                    <span class="font-medium
                                                        @if($phong->trang_thai === 'trong') text-green-600
                                                        @elseif($phong->trang_thai === 'dang_thue') text-blue-600
                                                        @elseif($phong->trang_thai === 'dang_don') text-yellow-600
                                                        @else text-red-600 @endif">
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
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                        <p class="text-sm font-semibold text-blue-900">
                                            Phòng {{ $booking->phong->so_phong }}
                                        </p>
                                    </div>
                                </div>
                            @else
                                <p class="mt-6 pt-6 border-t border-gray-200 text-sm text-yellow-600">
                                    ⚠️ Chưa gán phòng cụ thể
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Booking Info --}}
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900">Thông tin đặt phòng</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Ngày đặt</p>
                                    <p class="font-semibold text-gray-900">{{ date('d/m/Y H:i', strtotime($booking->ngay_dat)) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Số người</p>
                                    <p class="font-semibold text-gray-900">{{ $booking->so_nguoi }} người</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Ngày nhận phòng</p>
                                    <p class="font-semibold text-gray-900">{{ date('d/m/Y', strtotime($booking->ngay_nhan)) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Ngày trả phòng</p>
                                    <p class="font-semibold text-gray-900">{{ date('d/m/Y', strtotime($booking->ngay_tra)) }}</p>
                                </div>
                            </div>
                            @if ($booking->ghi_chu)
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <p class="text-sm text-gray-500 mb-1">Ghi chú</p>
                                    <p class="text-sm text-gray-900">{{ $booking->ghi_chu }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Customer Info --}}
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900">Thông tin khách hàng</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Họ tên</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $booking->username }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Email</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $booking->email }}</span>
                                </div>
                                @if($booking->sdt)
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Số điện thoại</span>
                                        <span class="text-sm font-semibold text-gray-900">{{ $booking->sdt }}</span>
                                    </div>
                                @endif
                                @if($booking->cccd)
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">CCCD/CMND</span>
                                        <span class="text-sm font-semibold text-gray-900">{{ $booking->cccd }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN --}}
                <div class="lg:col-span-1">
                    <div class="sticky top-6 space-y-6">

                        {{-- Payment Card --}}
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                                <h3 class="text-lg font-semibold text-gray-900">Thanh toán</h3>
                            </div>
                            <div class="p-6">
                                @if($booking->voucher)
                                    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                                        <p class="text-xs text-gray-600 mb-1">Mã giảm giá</p>
                                        <p class="text-lg font-bold text-green-700">{{ $booking->voucher->ma_voucher }}</p>
                                        <p class="text-sm text-green-600">Giảm {{ $booking->voucher->gia_tri }}%</p>
                                    </div>
                                @endif

                                @php
                                    $invoice = $booking->invoice;

                                    if ($invoice && $invoice->tien_phong) {
                                        $tienPhong = $invoice->tien_phong;
                                        $tienDichVu = $invoice->tien_dich_vu ?? 0;
                                        $giamGia = $invoice->giam_gia ?? 0;
                                    } else {
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

                                        $tienDichVu = \App\Models\BookingService::where('dat_phong_id', $booking->id)
                                            ->sum(\DB::raw('quantity * unit_price'));

                                        $giamGia = 0;
                                        if($booking->voucher) {
                                            $giamGia = $tienPhong * ($booking->voucher->gia_tri / 100);
                                        }
                                    }
                                @endphp

                                <dl class="space-y-3">
                                    <div class="flex justify-between text-sm">
                                        <dt class="text-gray-600">Tiền phòng</dt>
                                        <dd class="font-semibold text-gray-900">{{ number_format($tienPhong, 0, ',', '.') }} VNĐ</dd>
                                    </div>

                                    @if($giamGia > 0)
                                        <div class="flex justify-between text-sm">
                                            <dt class="text-red-600">Giảm giá</dt>
                                            <dd class="font-semibold text-red-600">-{{ number_format($giamGia, 0, ',', '.') }} VNĐ</dd>
                                        </div>
                                    @endif

                                    <div class="pt-3 border-t-2 border-gray-200">
                                        <div class="flex justify-between">
                                            <dt class="text-base font-semibold text-gray-900">Tổng thanh toán</dt>
                                            <dd class="text-xl font-bold text-blue-600">{{ number_format($booking->tong_tien, 0, ',', '.') }} VNĐ</dd>
                                        </div>
                                    </div>
                                </dl>

                                @if($booking->invoice)
                                    <div class="mt-6 pt-6 border-t border-gray-200">
                                        <p class="text-sm text-gray-600 mb-2">Trạng thái</p>
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-semibold border-2
                                            @if($booking->invoice->trang_thai === 'da_thanh_toan') bg-green-50 text-green-700 border-green-200
                                            @elseif($booking->invoice->trang_thai === 'cho_thanh_toan') bg-yellow-50 text-yellow-700 border-yellow-200
                                            @else bg-red-50 text-red-700 border-red-200 @endif">
                                            @if($booking->invoice->trang_thai === 'da_thanh_toan')
                                                ✓ Đã thanh toán
                                            @elseif($booking->invoice->trang_thai === 'cho_thanh_toan')
                                                ⏳ Chờ thanh toán
                                            @else
                                                ↩️ Hoàn tiền
                                            @endif
                                        </span>

                                        @if($booking->invoice->phuong_thuc)
                                            <p class="text-sm text-gray-600 mt-3">
                                                Phương thức:
                                                <span class="font-semibold">
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
                                                class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition shadow-sm">
                                                Đánh dấu đã thanh toán
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                                <h3 class="text-lg font-semibold text-gray-900">Thao tác</h3>
                            </div>
                            <div class="p-6 space-y-3">
                                @if($booking->trang_thai === 'cho_xac_nhan')
                                    <a href="{{ route('admin.dat_phong.edit', $booking->id) }}"
                                        class="w-full inline-flex justify-center items-center px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition shadow-sm">
                                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Sửa thông tin
                                    </a>
                                    <a href="{{ route('admin.dat_phong.cancel', $booking->id) }}"
                                        class="w-full inline-flex justify-center items-center px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition shadow-sm">
                                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Hủy đặt phòng
                                    </a>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
