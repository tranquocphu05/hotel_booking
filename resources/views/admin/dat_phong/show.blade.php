@extends('layouts.admin')

@section('title', 'Chi tiết đặt phòng')

@section('admin_content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-800">Chi tiết đặt phòng <b>{{ $booking->phong->ten_phong }}</b></h2>
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
                            <img src="{{ asset( $booking->phong->img) }}" 
                                alt="{{ $booking->phong->ten_phong }}"
                                class="w-full h-48 object-cover rounded-lg">
                            <p class="text-sm text-gray-600">Tên phòng: <span class="font-medium">{{ $booking->phong->ten_phong }}</span></p>
                            <p class="text-sm text-gray-600">Loại phòng: <span class="font-medium">{{ $booking->phong->loaiPhong->ten_loai }}</span></p>
                            <p class="text-sm text-gray-600">Giá phòng: <span class="font-medium">{{ number_format($booking->phong->gia, 0, ',', '.') }} VNĐ</span></p>
                            <p class="text-sm px-3 py-1 rounded-full text-sm font-medium
                                @if ($booking->phong->trang_thai === 'hien') bg-green-100 text-green-800
                                @elseif($booking->phong->trang_thai === 'an') bg-yellow-100 text-yellow-800
                                @elseif($booking->phong->trang_thai === 'bao_tri') bg-red-100 text-red-800
                                @else bg-blue-100 text-blue-800 @endif">
                                {{ $booking->phong->trang_thai === 'hien' ? 'Hiện' : ($booking->phong->trang_thai === 'an' ? 'Ẩn' : 'Bảo trì') }}
                            </p>
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
                            <p class="text-sm text-gray-600">CCCD/CMND: <span class="font-medium">{{ $booking->cccd }}</span></p>
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
                            <p class="text-sm text-gray-600">Tổng tiền: <span class="font-medium">{{ number_format($booking->tong_tien, 0, ',', '.') }} VNĐ</span></p>
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
