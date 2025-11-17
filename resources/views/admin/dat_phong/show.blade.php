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

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Card Thông tin phòng -->
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
                                        @endif
                                    @endforeach
                                </div>
                                <p class="text-sm text-gray-600">Tổng số phòng: <span class="font-medium">{{ $booking->so_luong_da_dat ?? 1 }} phòng</span></p>
                            @else
                                {{-- Hiển thị 1 loại phòng (legacy) --}}
                                <img src="{{ asset($booking->loaiPhong->anh ?? 'img/room/room-1.jpg') }}" 
                                    alt="{{ $booking->loaiPhong->ten_loai ?? 'N/A' }}"
                                    class="w-full h-48 object-cover rounded-lg">
                                <p class="text-sm text-gray-600">Loại phòng: <span class="font-medium">{{ $booking->loaiPhong->ten_loai ?? 'N/A' }}</span></p>
                                <p class="text-sm text-gray-600">Số lượng phòng: <span class="font-medium">{{ $booking->so_luong_da_dat ?? 1 }} phòng</span></p>
                            @endif
                            
                            @php
                                $assignedPhongs = $booking->getAssignedPhongs();
                                $assignedCount = $assignedPhongs->count();
                                $remainingCount = $booking->so_luong_da_dat - $assignedCount;
                            @endphp

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

