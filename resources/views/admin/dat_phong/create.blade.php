@extends('layouts.admin')

@section('title', 'Đặt phòng mới')

@section('admin_content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
                    <h1 class="text-2xl font-bold text-gray-900">Đặt phòng mới</h1>
                    <p class="mt-1 text-sm text-gray-600">Tạo một đặt phòng mới cho khách</p>
                </div>
                <div class="p-6">

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Có lỗi xảy ra:</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul class="list-disc list-inside space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-sm text-green-800">{{ session('success') }}</p>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-800">{{ session('error') }}</p>
                        </div>
                    @endif

                    <form action="{{ route('admin.dat_phong.store') }}" method="POST" id="bookingForm">
                        @csrf
                        <div class="space-y-8">
                            <!-- Chọn loại phòng -->
                            <section>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">Chọn loại phòng</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="roomTypesContainer">
                                    @foreach ($loaiPhongs as $loaiPhong)
                                        <div class="room-type-card relative">
                                            <input type="checkbox" name="room_types[]" id="loai_phong_{{ $loaiPhong->id }}"
                                                value="{{ $loaiPhong->id }}" class="sr-only peer room-type-checkbox"
                                                data-price="{{ $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban }}"
                                                data-base-price="{{ $loaiPhong->gia_co_ban }}"
                                                data-available="{{ $loaiPhong->so_luong_trong }}"
                                                onchange="toggleRoomType(this, {{ $loaiPhong->id }})">
                                            <label for="loai_phong_{{ $loaiPhong->id }}"
                                                class="block p-4 bg-white border-2 border-gray-200 rounded-xl cursor-pointer transition-all duration-300
                                                    peer-checked:border-blue-500 peer-checked:ring-2 peer-checked:ring-blue-500 peer-checked:bg-blue-50
                                                    hover:bg-gray-50 hover:border-gray-300 hover:shadow-md">
                                                <div class="space-y-2">
                                                    <img src="{{ asset($loaiPhong->anh ?? '/img/room/default.jpg') }}"
                                                        alt="{{ $loaiPhong->ten_loai }}"
                                                        class="w-full h-40 object-cover rounded-lg mb-2">
                                                    <h4 class="font-semibold text-gray-900">{{ $loaiPhong->ten_loai }}</h4>
                                                    <p class="text-xs text-gray-600 line-clamp-2">
                                                        {{ $loaiPhong->mo_ta ?? '' }}</p>
                                                    <div class="flex items-center justify-between">
                                                        @if ($loaiPhong->gia_khuyen_mai)
                                                            <div>
                                                                <p class="text-sm font-medium text-red-600">
                                                                    {{ number_format($loaiPhong->gia_khuyen_mai, 0, ',', '.') }}
                                                                    VNĐ/đêm
                                                                </p>
                                                                <p class="text-xs text-gray-500 line-through">
                                                                    {{ number_format($loaiPhong->gia_co_ban, 0, ',', '.') }}
                                                                    VNĐ
                                                                </p>
                                                            </div> 
                                                        @else
                                                            <p class="text-sm font-medium text-blue-600">
                                                                {{ number_format($loaiPhong->gia_co_ban, 0, ',', '.') }}
                                                                VNĐ/đêm
                                                            </p>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center space-x-2 text-sm">
                                                        <span
                                                            class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">
                                                            {{ $loaiPhong->trang_thai === 'hoat_dong' ? 'Khả dụng' : 'Không khả dụng' }}
                                                        </span>
                                                        <span class="text-xs text-gray-600 availability-text"
                                                            id="availability_text_{{ $loaiPhong->id }}"
                                                            data-loai-phong-id="{{ $loaiPhong->id }}">
                                                            Còn {{ $loaiPhong->so_luong_trong }} phòng
                                                        </span>
                                                    </div>
                                                </div>
                                            </label>
                                            {{-- Hidden inputs luôn được submit --}}
                                            <input type="hidden" name="rooms[{{ $loaiPhong->id }}][so_luong]"
                                                id="quantity_hidden_{{ $loaiPhong->id }}" value="0">
                                            <input type="hidden" name="rooms[{{ $loaiPhong->id }}][loai_phong_id]"
                                                value="{{ $loaiPhong->id }}">

                                            {{-- Số lượng phòng với design mới (ẩn mặc định) --}}
                                            <div class="room-quantity-container mt-3 hidden"
                                                id="quantity_container_{{ $loaiPhong->id }}">
                                                <div
                                                    class="flex items-center justify-between bg-white border border-gray-300 rounded-lg p-2 shadow-sm">
                                                    <label for="quantity_{{ $loaiPhong->id }}"
                                                        class="text-sm font-medium text-gray-700 mr-3 whitespace-nowrap">
                                                        Số lượng:
                                                    </label>
                                                    <div class="flex items-center space-x-2 flex-1">
                                                        <button type="button"
                                                            class="quantity-btn-decrease w-8 h-8 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md transition-colors font-bold text-lg"
                                                            onclick="decreaseQuantity({{ $loaiPhong->id }})"
                                                            tabindex="-1">
                                                            −
                                                        </button>
                                                        <input type="text" id="quantity_{{ $loaiPhong->id }}"
                                                            class="room-quantity-input w-16 text-center border-0 focus:ring-0 focus:outline-none text-sm font-semibold text-gray-900"
                                                            value="1"
                                                            onchange="updateQuantityHidden({{ $loaiPhong->id }})"
                                                            oninput="validateQuantity(this, {{ $loaiPhong->id }})"
                                                            readonly>
                                                        <button type="button"
                                                            class="quantity-btn-increase w-8 h-8 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md transition-colors font-bold text-lg"
                                                            onclick="increaseQuantity({{ $loaiPhong->id }})"
                                                            tabindex="-1">
                                                            +
                                                        </button>
                                                    </div>
                                                    <span class="text-xs text-gray-500 ml-2 whitespace-nowrap">
                                                        / <span id="max_available_{{ $loaiPhong->id }}"
                                                            data-max="{{ $loaiPhong->so_luong_trong }}">{{ $loaiPhong->so_luong_trong }}</span>
                                                        phòng
                                                    </span>
                                                </div>
                                                <p class="text-xs text-red-600 mt-1 hidden"
                                                    id="quantity_error_{{ $loaiPhong->id }}">
                                                    Số lượng không được vượt quá <span
                                                        id="max_available_error_{{ $loaiPhong->id }}">{{ $loaiPhong->so_luong_trong }}</span>
                                                    phòng
                                                </p>
                                            </div>
                                            {{-- Danh sách các phòng cụ thể có sẵn (sẽ được JS điền vào) --}}
                                            <div id="available_rooms_{{ $loaiPhong->id }}" class="available-rooms mt-3 hidden"></div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('room_types')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <div id="room_types_error" class="mt-2 text-sm text-red-600 hidden">
                                    Vui lòng chọn ít nhất một loại phòng
                                </div>
                            </section>

                            <!-- Thông tin đặt phòng -->
                            <section>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">Thông tin đặt phòng</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label for="ngay_nhan" class="block text-sm font-medium text-gray-700">Ngày nhận
                                            phòng</label>
                                        <input type="date" name="ngay_nhan" id="ngay_nhan"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        @error('ngay_nhan')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="ngay_tra" class="block text-sm font-medium text-gray-700">Ngày trả
                                            phòng</label>
                                        <input type="date" name="ngay_tra" id="ngay_tra"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        @error('ngay_tra')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="so_nguoi" class="block text-sm font-medium text-gray-700">Số
                                            người</label>
                                        <input type="text" name="so_nguoi" id="so_nguoi"
                                            value="{{ old('so_nguoi') }}"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        @error('so_nguoi')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </section>

                            <!-- Chọn mã giảm giá -->
                            <section>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">Chọn mã giảm giá (nếu có)</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                    @foreach ($vouchers as $voucher)
                                        @php
                                            $isDisabled = $voucher->status !== 'con_han';
                                            $overlayText =
                                                $voucher->status === 'het_han'
                                                    ? 'Hết hạn'
                                                    : ($voucher->status === 'huy'
                                                        ? 'Đã hủy'
                                                        : '');
                                        @endphp

                                        <div class="relative voucher-container h-full" data-voucher-id="{{ $voucher->id }}">
                                            <input type="radio" name="voucher_radio"
                                                id="voucher_{{ $voucher->id }}"
                                                value="{{ $voucher->ma_voucher }}"
                                                class="sr-only peer voucher-radio"
                                                data-value="{{ $voucher->gia_tri }}"
                                                data-loai-phong="{{ $voucher->loai_phong_id }}"
                                                data-start="{{ $voucher->ngay_bat_dau ? date('Y-m-d', strtotime($voucher->ngay_bat_dau)) : '' }}"
                                                data-end="{{ $voucher->ngay_ket_thuc ? date('Y-m-d', strtotime($voucher->ngay_ket_thuc)) : '' }}"
                                                {{ $isDisabled ? 'disabled' : '' }}>

                                            <label for="voucher_{{ $voucher->id }}"
                                                class="flex flex-col h-full p-4 bg-gray-50 border-2 border-gray-200 rounded-lg cursor-pointer relative transition-all duration-300 ease-in-out
                                        peer-checked:bg-white peer-checked:border-green-500 peer-checked:ring-2 peer-checked:ring-green-400 peer-checked:shadow-md
                                        hover:bg-gray-100 hover:border-gray-300 hover:shadow-sm
                                        z-0 peer-checked:z-10 voucher-label {{ $isDisabled ? 'opacity-50 cursor-not-allowed' : '' }}">

                                                {{-- Overlay hiển thị trạng thái --}}
                                                @if ($isDisabled)
                                                    <div
                                                        class="voucher-overlay-server absolute inset-0 bg-opacity-70 flex items-center justify-center rounded-lg pointer-events-none">
                                                        <span
                                                            class="text-gray-700 text-sm font-medium">{{ $overlayText }}</span>
                                                    </div>
                                                @endif

                                                <div class="space-y-2">
                                                    <div class="flex items-center justify-between">
                                                        <p class="text-sm font-bold text-green-600">
                                                            {{ $voucher->ma_voucher }}</p>
                                                        <span
                                                            class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                                            @if ($voucher->gia_tri <= 100)
                                                                {{ $voucher->gia_tri }}%
                                                            @else
                                                                {{ number_format($voucher->gia_tri, 0, ',', '.') }}₫
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <p class="text-xs text-gray-600">
                                                        @if ($voucher->gia_tri <= 100)
                                                            Giảm {{ $voucher->gia_tri }}%
                                                        @else
                                                            Giảm
                                                            {{ number_format($voucher->gia_tri, 0, ',', '.') }} VNĐ
                                                        @endif
                                                    </p>
                                                    @if ($voucher->dieu_kien)
                                                        <p class="text-xs text-gray-500 bg-gray-100 p-2 rounded">
                                                            {{ $voucher->dieu_kien }}</p>
                                                    @endif
                                                    <div class="flex justify-between text-xs text-gray-500">
                                                        <span>Còn lại: {{ $voucher->so_luong }}</span>
                                                        <span>HSD:
                                                            {{ date('d/m/Y', strtotime($voucher->ngay_ket_thuc)) }}</span>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </section>

                                <!-- Chọn dịch vụ -->
                                
                                <!-- Tom Select based multi-select for services -->
                                <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css" rel="stylesheet">
                                <!-- Service cards styling -->
                                <style>
                                    .service-card-custom{
                                        border-radius:10px;
                                        background: linear-gradient(135deg, #e0f2fe 0%, #bfdbfe 100%);
                                        border:1.5px solid #2563eb;
                                        padding:0.875rem;
                                        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.08);
                                    }
                                    .service-card-grid{display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:0.75rem}
                                    .service-card-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;padding-bottom:0.4rem;border-bottom:1.5px solid #bfdbfe}
                                    .service-card-header .service-title{color:#1e40af;font-weight:600;font-size:0.95rem}
                                    .service-card-header .service-price{color:#1e3a8a;font-weight:600;font-size:0.85rem}
                                    .service-date-row{display:flex;gap:0.5rem;align-items:center;margin-top:0.5rem;padding:0.4rem;background:#ffffff;border-radius:6px;border:1px solid #bfdbfe}
                                    .service-date-row input[type=date]{border:1px solid #93c5fd;padding:0.35rem 0.5rem;border-radius:5px;background:#eff6ff;font-size:0.85rem;flex:1}
                                    .service-date-row input[type=number]{border:1px solid #93c5fd;padding:0.35rem 0.5rem;border-radius:5px;background:#eff6ff;width:64px;text-align:center;font-size:0.85rem}
                                    .service-add-day{background:linear-gradient(135deg, #93c5fd 0%, #2563eb 100%);color:#08203a;padding:0.4rem 0.6rem;border-radius:6px;border:1.5px solid #60a5fa;cursor:pointer;font-weight:600;font-size:0.85rem;transition:all 0.2s}
                                    .service-add-day:hover{background:linear-gradient(135deg, #2563eb 0%, #1e40af 100%);box-shadow:0 4px 12px rgba(37, 99, 235, 0.12)}
                                    .service-remove-btn{background:#fee2e2;color:#991b1b;padding:0.3rem 0.5rem;border-radius:5px;border:1px solid #fecaca;cursor:pointer;font-weight:600;font-size:0.8rem;transition:all 0.2s}
                                    .service-remove-btn:hover{background:#fca5a5;box-shadow:0 3px 10px rgba(185,28,28,0.12)}
                                    #services_select + .ts-control{margin-top:.5rem;border-color:#2563eb}
                                </style>
                                <div>
                                    <label for="services_select" class="block text-sm font-medium text-gray-700 mb-2">Chọn dịch vụ kèm theo</label>
                                    <select id="services_select" placeholder="Chọn 1 hoặc nhiều dịch vụ..." multiple>
                                        @foreach ($services as $service)
                                            <option value="{{ $service->id }}" data-price="{{ $service->price }}" data-unit="{{ $service->unit ?? '' }}">{{ $service->name }} - {{ number_format($service->price,0,',','.') }} VNĐ</option>
                                        @endforeach
                                    </select>
                                    <div id="selected_services_list" class="service-card-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4"></div>
                                </div>
                            </section>


                                <input type="hidden" name="tong_tien" id="tong_tien_input" value="0">
                                <input type="hidden" name="voucher" id="voucher_input" value="">
                                <input type="hidden" name="voucher_id" id="voucher_id_input" value="">
                                
                                <!-- Hiển thị tổng tiền -->
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                                    <div class="flex justify-between items-center mb-3">
                                        <span class="text-gray-700 font-medium">Tổng tiền:</span>
                                        <span id="total_price" class="text-2xl font-bold text-blue-700">0 VNĐ</span>
                                    </div>
                                    <div id="discount_info" class="text-sm text-gray-700 hidden pt-3 border-t border-blue-200">
                                        <div class="flex justify-between mb-1">
                                            <span>Giá gốc:</span>
                                            <span id="original_price">0 VNĐ</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Giảm giá:</span>
                                            <span id="discount_amount" class="text-green-600 font-medium">-0 VNĐ</span>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Thông tin người đặt -->
                            <section>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">Thông tin người đặt</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Họ và
                                            tên</label>
                                        <input type="text" name="username" id="username"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            value="{{ old('username') }}" required>
                                        @error('username')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="email"
                                            class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                        <input type="text" name="email" id="email"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            value="{{ old('email') }}" required>
                                        @error('email')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="sdt" class="block text-sm font-medium text-gray-700 mb-2">Số điện
                                            thoại</label>
                                        <input type="text" name="sdt" id="sdt"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            value="{{ old('sdt') }}" required>
                                        @error('sdt')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="cccd"
                                            class="block text-sm font-medium text-gray-700 mb-2">CCCD/CMND</label>
                                        <input type="text" name="cccd" id="cccd"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            value="{{ old('cccd') }}" required>
                                        @error('cccd')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Thông báo lỗi validation -->
                                <div id="validation-errors"
                                    class="hidden p-4 bg-red-50 border border-red-200 rounded-lg">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-red-800">Thông tin không hợp lệ</h3>
                                            <div class="mt-2 text-sm text-red-700">
                                                <ul id="error-list" class="list-disc list-inside space-y-1">
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Form Actions -->
                            <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                                <a href="{{ route('admin.dat_phong.index') }}"
                                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition">
                                    Hủy bỏ
                                </a>
                                <button type="submit"
                                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition">
                                    Đặt phòng
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let roomIndex = 0;
            const allLoaiPhongs = @json($loaiPhongs);

            function formatCurrency(amount) {
                return new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(amount).replace('₫', 'VNĐ');
            }
            function toggleRoomType(checkbox, roomTypeId) {
                const quantityContainer = document.getElementById('quantity_container_' + roomTypeId);
                const quantityInput = document.getElementById('quantity_' + roomTypeId);

                if (checkbox.checked) {
                    quantityContainer.classList.remove('hidden');
                    quantityInput.required = true;
                    // Cập nhật hidden input khi checkbox được chọn
                    const hiddenInput = document.getElementById('quantity_hidden_' + roomTypeId);
                    if (hiddenInput) {
                        hiddenInput.value = quantityInput.value || 1;
                    }
                    // Show available specific rooms for this type
                    const roomsContainer = document.getElementById('available_rooms_' + roomTypeId);
                    if (roomsContainer) roomsContainer.classList.remove('hidden');
                    // Fetch latest availability and rooms immediately
                    try { updateRoomAvailability(roomTypeId); } catch(e){}
                } else {
                    quantityContainer.classList.add('hidden');
                    quantityInput.required = false;
                    quantityInput.value = 1;
                    // Đặt giá trị hidden về 0 khi bỏ chọn
                    const hiddenInput = document.getElementById('quantity_hidden_' + roomTypeId);
                    if (hiddenInput) {
                        hiddenInput.value = 0;
                    }
                    // hide and clear available rooms when unchecking type
                    const roomsContainer = document.getElementById('available_rooms_' + roomTypeId);
                    if (roomsContainer) { roomsContainer.classList.add('hidden'); roomsContainer.innerHTML = ''; }
                }

                updateVoucherAvailability();
                updateTotalPrice();
            }
            // Ensure available room checkboxes cannot exceed selected quantity for that room type
            function enforceRoomSelectionLimit(loaiPhongId) {
                const boxes = Array.from(document.querySelectorAll('#available_rooms_' + loaiPhongId + ' input.available-room-checkbox'));
                boxes.forEach(b => {
                    // assign onchange directly so we replace previous handlers
                    b.onchange = function(e) {
                        const wantNow = parseInt(document.getElementById('quantity_' + loaiPhongId)?.value || 0);
                        const boxesNow = Array.from(document.querySelectorAll('#available_rooms_' + loaiPhongId + ' input.available-room-checkbox'));
                        const checkedNow = boxesNow.filter(x=>x.checked).length;
                        if (checkedNow > wantNow) {
                            // revert this change
                            this.checked = false;
                            alert('Bạn chỉ được chọn tối đa ' + wantNow + ' phòng cho loại này');
                        }
                        // sync service room lists when selection changes
                        updateServiceRoomLists();
                    };
                });
            }
            // Update all service cards room-option lists to reflect currently selected booking rooms
            function updateServiceRoomLists() {
                const selectedRoomInputs = Array.from(document.querySelectorAll('.available-room-checkbox:checked'));
                // for each service card, rebuild its room checkbox UI (visual checkboxes inside room_checkboxes_{serviceId})
                document.querySelectorAll('[data-service-id]').forEach(card => {
                    const serviceId = card.getAttribute('data-service-id');
                    const roomCheckboxContainer = document.getElementById('room_checkboxes_' + serviceId);
                    if (roomCheckboxContainer) {
                        roomCheckboxContainer.innerHTML = '';
                        selectedRoomInputs.forEach(inp => {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'flex items-center gap-2 py-1';

                        const checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.className = 'service-room-checkbox';
                        checkbox.setAttribute('data-room-id', inp.value);
                        checkbox.value = inp.value;
                        checkbox.onchange = () => syncHiddenEntries(serviceId);

                        const label = document.createElement('label');
                        label.className = 'text-xs cursor-pointer';
                        label.textContent = inp.getAttribute('data-room-name') || ('Phòng ' + inp.value);

                        wrapper.appendChild(checkbox);
                            wrapper.appendChild(label);
                            roomCheckboxContainer.appendChild(wrapper);
                        });
                    }

                    // Also update per-entry room selectors inside each date row
                    const rows = card.querySelectorAll('.service-date-row');
                    // Determine if this service card is in specific mode
                    const specificRadio = card.querySelector('input[name="service_room_mode_' + serviceId + '"][value="specific"]');
                    const isSpecific = specificRadio ? specificRadio.checked : false;
                    rows.forEach((r, idx) => {
                        // find or create entry-room container
                        let entryRoomContainer = r.querySelector('.entry-room-container');
                        if (!entryRoomContainer) {
                            entryRoomContainer = document.createElement('div');
                            entryRoomContainer.className = 'entry-room-container mt-2 pl-2 border-l';
                            r.appendChild(entryRoomContainer);
                        }
                        entryRoomContainer.innerHTML = '';
                        // Only render per-entry room checkboxes when the service is set to 'specific'
                        if (!isSpecific) {
                            // hide container when global mode
                            entryRoomContainer.classList.add('hidden');
                            return;
                        } else {
                            entryRoomContainer.classList.remove('hidden');
                        }

                        selectedRoomInputs.forEach(inp => {
                            const ewrap = document.createElement('div'); ewrap.className = 'inline-flex items-center gap-1 mr-2';
                            const ecb = document.createElement('input'); ecb.type = 'checkbox'; ecb.className = 'entry-room-checkbox'; ecb.setAttribute('data-room-id', inp.value);
                            ecb.value = inp.value; ecb.checked = true; 
                            ecb.onchange = () => { 
                                const serviceId = card.getAttribute('data-service-id');
                                console.log('entry-room-checkbox changed, serviceId=', serviceId);
                                // Call via window to ensure function exists
                                setTimeout(() => {
                                    try { 
                                        if (typeof window.syncHiddenEntries === 'function') {
                                            window.syncHiddenEntries(serviceId);
                                        }
                                    } catch(e) { console.error('syncHiddenEntries error:', e); }
                                    try { updateTotalPrice(); } catch(e) { console.error('updateTotalPrice error:', e); }
                                }, 0);
                            };
                            const elbl = document.createElement('label'); elbl.className='text-xs'; elbl.textContent = inp.getAttribute('data-room-name') || ('Phòng ' + inp.value);
                            ewrap.appendChild(ecb); ewrap.appendChild(elbl);
                            entryRoomContainer.appendChild(ewrap);
                        });
                    });
                });
            }
            function updateQuantityHidden(roomTypeId) {
                const displayInput = document.getElementById('quantity_' + roomTypeId);
                const hiddenInput = document.getElementById('quantity_hidden_' + roomTypeId);
                if (displayInput && hiddenInput) {
                    hiddenInput.value = displayInput.value;
                }
            }
            function getMaxAvailable(roomTypeId) {
                const maxElement = document.getElementById('max_available_' + roomTypeId);
                return maxElement ? parseInt(maxElement.textContent) || 0 : 0;
            }
            function validateQuantity(input, roomTypeId) {
                // UI-only validation - just adjust value if out of bounds
                // Real validation is done by PHP Laravel
                const maxAvailable = getMaxAvailable(roomTypeId);
                const value = parseInt(input.value) || 0;
                const errorElement = document.getElementById('quantity_error_' + roomTypeId);
                const hiddenInput = document.getElementById('quantity_hidden_' + roomTypeId);
                const maxErrorElement = document.getElementById('max_available_error_' + roomTypeId);
                if (maxErrorElement) {
                    maxErrorElement.textContent = maxAvailable;
                }

                if (maxAvailable > 0 && value > maxAvailable) {
                    errorElement?.classList.remove('hidden');
                    input.value = maxAvailable;
                    if (hiddenInput) hiddenInput.value = maxAvailable;
                } else if (value < 1) {
                    errorElement?.classList.add('hidden');
                    input.value = 1;
                    if (hiddenInput) hiddenInput.value = 1;
                } else {
                    errorElement?.classList.add('hidden');
                    if (hiddenInput) hiddenInput.value = value;
                }

                // enforce room check limits and update services' room lists
                try { enforceRoomSelectionLimit(roomTypeId); } catch(e){}
                // auto pre-check additional boxes when quantity increases
                try {
                    const want = parseInt(document.getElementById('quantity_' + roomTypeId)?.value || 0);
                    const boxes = Array.from(document.querySelectorAll('#available_rooms_' + roomTypeId + ' input.available-room-checkbox'));
                    if (want > 0 && boxes.length > 0) {
                        for (let i = 0; i < Math.min(want, boxes.length); i++) boxes[i].checked = true;
                    }
                } catch(e){}
                try { updateServiceRoomLists(); } catch(e){}
                updateTotalPrice();
            }
            function decreaseQuantity(roomTypeId) {
                try {
                    const input = document.getElementById('quantity_' + roomTypeId);
                    if (!input) { console.debug('decreaseQuantity: input not found for', roomTypeId); return; }
                    const currentValue = parseInt(input.value) || 1;
                    if (currentValue > 1) {
                        input.value = currentValue - 1;
                        updateQuantityHidden(roomTypeId);
                        validateQuantity(input, roomTypeId);
                    }
                } catch (e) {
                    console.error('decreaseQuantity error for', roomTypeId, e);
                }
            }
            function increaseQuantity(roomTypeId) {
                try {
                    const input = document.getElementById('quantity_' + roomTypeId);
                    if (!input) { console.debug('increaseQuantity: input not found for', roomTypeId); return; }
                    // If server hasn't set maxAvailable yet (0), treat as unlimited so UI still increments
                    let maxAvailable = getMaxAvailable(roomTypeId);
                    if (!maxAvailable || maxAvailable <= 0) maxAvailable = Infinity;
                    const currentValue = parseInt(input.value) || 1;
                    if (currentValue < maxAvailable) {
                        input.value = currentValue + 1;
                        updateQuantityHidden(roomTypeId);
                        validateQuantity(input, roomTypeId);
                    }
                } catch (e) {
                    console.error('increaseQuantity error for', roomTypeId, e);
                }
            }
            // Function to update availability for a single room type
            function updateRoomAvailability(loaiPhongId) {
                const checkin = document.getElementById('ngay_nhan').value;
                const checkout = document.getElementById('ngay_tra').value;

                if (!checkin || !checkout) {
                    return;
                }

                fetch('{{ route('admin.dat_phong.available_count') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                                    loai_phong_id: loaiPhongId,
                                    checkin: checkin,
                                    checkout: checkout,
                                    include_rooms: 1
                                })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const availableCount = data.available_count || 0;
                            const maxElement = document.getElementById('max_available_' + loaiPhongId);
                            const availabilityText = document.getElementById('availability_text_' + loaiPhongId);
                            const quantityInput = document.getElementById('quantity_' + loaiPhongId);
                            const maxErrorElement = document.getElementById('max_available_error_' + loaiPhongId);

                            if (maxElement) {
                                maxElement.textContent = availableCount;
                                maxElement.setAttribute('data-max', availableCount);
                            }

                            if (availabilityText) {
                                if (availableCount > 0) {
                                    availabilityText.textContent = `Còn ${availableCount} phòng`;
                                    availabilityText.className = 'text-xs text-gray-600';
                                } else {
                                    availabilityText.textContent = 'Hết phòng';
                                    availabilityText.className = 'text-xs text-red-600 font-medium';
                                }
                            }

                            if (maxErrorElement) {
                                maxErrorElement.textContent = availableCount;
                            }

                            // Adjust quantity if it exceeds new max
                            if (quantityInput && parseInt(quantityInput.value) > availableCount && availableCount > 0) {
                                quantityInput.value = availableCount;
                                updateQuantityHidden(loaiPhongId);
                                validateQuantity(quantityInput, loaiPhongId);
                            } else if (availableCount === 0 && quantityInput) {
                                quantityInput.value = 0;
                                updateQuantityHidden(loaiPhongId);
                            }

                            // Render available specific rooms (if provided)
                            const roomsContainer = document.getElementById('available_rooms_' + loaiPhongId);
                            if (roomsContainer) {
                                roomsContainer.innerHTML = '';
                                if (Array.isArray(data.rooms) && data.rooms.length > 0 && document.getElementById('loai_phong_' + loaiPhongId)?.checked) {
                                    // only show room list when the room-type is checked by admin
                                    roomsContainer.classList.remove('hidden');
                                    // Render checkboxes for admins to select specific rooms
                                    data.rooms.forEach((r, idx) => {
                                        const wrap = document.createElement('div');
                                        wrap.className = 'flex items-center gap-2 py-1';

                                        const cb = document.createElement('input');
                                        cb.type = 'checkbox';
                                        cb.name = `rooms[${loaiPhongId}][phong_ids][]`;
                                        cb.value = r.id;
                                        cb.className = 'available-room-checkbox';
                                        cb.setAttribute('data-room-name', r.so_phong || r.ten_phong || r.name || r.id);

                                        const lbl = document.createElement('label');
                                        lbl.className = 'text-sm';
                                        lbl.textContent = r.so_phong ? ('Phòng ' + r.so_phong) : (r.ten_phong || ('Phòng ' + r.id));

                                        wrap.appendChild(cb);
                                        wrap.appendChild(lbl);
                                        roomsContainer.appendChild(wrap);
                                    });

                                    // Pre-check first N boxes according to selected quantity
                                    const want = parseInt(quantityInput?.value || 0);
                                    if (want > 0) {
                                        const boxes = Array.from(roomsContainer.querySelectorAll('input.available-room-checkbox'));
                                        for (let i = 0; i < Math.min(want, boxes.length); i++) boxes[i].checked = true;
                                    }
                                    // Enforce selection limit and attach listeners
                                    try { enforceRoomSelectionLimit(loaiPhongId); } catch(e){}
                                    // Sync service cards room lists to reflect available/checked rooms
                                    try { updateServiceRoomLists(); } catch(e){}
                                } else {
                                    roomsContainer.classList.add('hidden');
                                }
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error updating availability:', error);
                    });
            }
            // Function to update availability for all room types
            function updateAllRoomAvailability() {
                const checkin = document.getElementById('ngay_nhan').value;
                const checkout = document.getElementById('ngay_tra').value;

                if (!checkin || !checkout) {
                    return;
                }

                // Update availability for all room types
                document.querySelectorAll('.room-type-card').forEach(card => {
                    const checkbox = card.querySelector('.room-type-checkbox');
                    if (checkbox) {
                        const loaiPhongId = checkbox.value;
                        updateRoomAvailability(loaiPhongId);
                    }
                });
            }
            document.addEventListener('DOMContentLoaded', function() {
                const ngayNhanInput = document.getElementById('ngay_nhan');
                const ngayTraInput = document.getElementById('ngay_tra');
                const totalPriceElement = document.getElementById('total_price');
                const originalPriceElement = document.getElementById('original_price');
                const discountAmountElement = document.getElementById('discount_amount');
                const discountInfoElement = document.getElementById('discount_info');

                // Đặt ngày tối thiểu cho ngày nhận phòng là ngày hiện tại
                const today = new Date().toISOString().split('T')[0];
                ngayNhanInput.setAttribute('min', today);
                ngayNhanInput.value = today;

                // Đặt ngày trả mặc định là ngày mai
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                ngayTraInput.value = tomorrow.toISOString().split('T')[0];

                // Cập nhật ngày trả phòng tối thiểu khi ngày nhận thay đổi
                ngayNhanInput.addEventListener('change', function() {
                    ngayTraInput.setAttribute('min', this.value);
                    if (ngayTraInput.value && ngayTraInput.value < this.value) {
                        ngayTraInput.value = this.value;
                    }
                    updateTotalPrice();
                    updateAllRoomAvailability();
                });

                ngayTraInput.addEventListener('change', function() {
                    updateTotalPrice();
                    updateAllRoomAvailability();
                });

                // Cập nhật availability khi trang load (nếu có ngày)
                setTimeout(() => {
                    if (ngayNhanInput.value && ngayTraInput.value) {
                        updateAllRoomAvailability();
                    }
                }, 300);

                // Update voucher availability when room selection changes
                document.querySelectorAll('.room-type-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const loaiId = this.value;
                        // If room-type is unchecked, clear any specific-room selections for that type
                        if (!this.checked) {
                            try {
                                const boxes = Array.from(document.querySelectorAll('#available_rooms_' + loaiId + ' input.available-room-checkbox'));
                                boxes.forEach(b => { b.checked = false; });
                            } catch(e){}
                        }

                        // Re-run related UI syncs so service cards reflect the current booking-selected rooms
                        try { enforceRoomSelectionLimit(loaiId); } catch(e){}
                        try { updateServiceRoomLists(); } catch(e){}
                        try { updateVoucherAvailability(); } catch(e){}
                        try { updateTotalPrice(); } catch(e){}
                    });
                });

                // Also update voucher availability when dates change
                ngayNhanInput.addEventListener('change', updateVoucherAvailability);
                ngayTraInput.addEventListener('change', updateVoucherAvailability);

                // Setup voucher system and listeners
                try { setupVoucherEventSystem(); } catch(e) { console.error('setupVoucherEventSystem error', e); }

                // Tính toán ban đầu
                updateTotalPrice();
                updateVoucherAvailability();
            });
            // Setup unified voucher event system (DOMContentLoaded)
            function setupVoucherEventSystem() {
                // Attach change handler to radios so 'change' triggers totals update immediately
                document.querySelectorAll('.voucher-radio').forEach(radio => {
                    radio.addEventListener('change', handleVoucherChange);
                });

                // Label click handler (toggle behavior: select/deselect)
                document.querySelectorAll('.voucher-label').forEach(label => {
                    label.addEventListener('click', function(e) {
                        // Prevent native label toggle to avoid double-change events
                        e.preventDefault();
                        e.stopPropagation();
                        const radio = this.closest('.voucher-container').querySelector('.voucher-radio');
                        if (!radio) return;

                        // Don't allow interaction on disabled radios
                        if (radio.disabled) {
                            e.preventDefault();
                            return;
                        }

                        // Toggle selected state and dispatch change so handler runs
                        const newState = !radio.checked;
                        radio.checked = newState;
                        radio.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                });
            }

            // Unified handler for all voucher changes
            function handleVoucherChange() {
                const voucherIdInput = document.getElementById('voucher_id_input');
                const voucherMirror = document.getElementById('voucher_input');

                if (this.checked) {
                    // Voucher selected: update hidden input with voucher ID
                    const container = this.closest('.voucher-container');
                    const voucherId = container?.getAttribute('data-voucher-id') || this.value;
                        if (voucherIdInput) voucherIdInput.value = voucherId;
                        // Mirror the voucher code expected by backend (radio.value is ma_voucher)
                        if (voucherMirror) voucherMirror.value = this.value || voucherId;
                    console.log('Voucher selected:', voucherId);
                } else {
                    // Voucher deselected: clear hidden inputs
                    if (voucherIdInput) voucherIdInput.value = '';
                    if (voucherMirror) voucherMirror.value = '';
                    console.log('Voucher deselected');
                }

                // Recalculate totals immediately
                updateTotalPrice();

                // Also refresh service UI and hidden inputs so services remain consistent
                try {
                    if (typeof updateServiceRoomLists === 'function') {
                        updateServiceRoomLists();
                    }
                } catch (e) {
                    console.error('Error running updateServiceRoomLists after voucher change', e);
                }

                // Recreate hidden inputs for all service cards
                try {
                    document.querySelectorAll('#selected_services_list [data-service-id]').forEach(card => {
                        const sid = card.getAttribute('data-service-id');
                        if (sid && typeof syncHiddenEntries === 'function') {
                            try { syncHiddenEntries(sid); } catch (er) { console.error('syncHiddenEntries error', er); }
                        }
                    });
                } catch (e) {
                    console.error('Error syncing hidden service entries after voucher change', e);
                }

                // Re-run service card update (safe no-op if not present)
                if (typeof window.updateServiceCards === 'function') {
                    try { window.updateServiceCards(); } catch (e) { console.error('updateServiceCards error', e); }
                }
            }

            function updateVoucherAvailability() {
                const selectedRoomTypes = Array.from(document.querySelectorAll('.room-type-checkbox:checked')).map(cb => cb
                    .value);
                const voucherInputs = document.querySelectorAll('.voucher-radio');

                // Get check-in and check-out dates for date range filtering
                const checkinDate = document.getElementById('ngay_nhan')?.value || '';
                const checkoutDate = document.getElementById('ngay_tra')?.value || '';

                // If either date is missing, hide all voucher UI and clear selection
                if (!checkinDate || !checkoutDate) {
                    document.querySelectorAll('.voucher-container').forEach(container => {
                        container.style.display = 'none';
                        const radio = container.querySelector('.voucher-radio');
                        if (radio) {
                            radio.checked = false;
                            radio.disabled = true;
                        }
                    });
                    // clear mirrored hidden inputs as no voucher should be active
                    const voucherMirror = document.getElementById('voucher_input');
                    const voucherIdInput = document.getElementById('voucher_id_input');
                    if (voucherMirror) voucherMirror.value = '';
                    if (voucherIdInput) voucherIdInput.value = '';
                    return;
                }

                // show voucher containers when dates are set
                document.querySelectorAll('.voucher-container').forEach(container => container.style.display = '');

                // Disable all vouchers first
                voucherInputs.forEach(v => {
                    v.disabled = true;
                });

                // Enable/disable vouchers based on room type compatibility AND date range
                voucherInputs.forEach(radio => {
                    const voucherRoomType = radio.dataset.loaiPhong;
                    const vStart = radio.dataset.start || '';
                    const vEnd = radio.dataset.end || '';
                    const container = radio.closest('.voucher-container');

                    // Date check: check-in date must be within [start, end] inclusive
                    let dateOk = true;
                    if (checkinDate && vStart && vEnd) {
                        // Parse dates for proper comparison (YYYY-MM-DD format)
                        const checkin = new Date(checkinDate + 'T00:00:00Z');
                        const start = new Date(vStart + 'T00:00:00Z');
                        const end = new Date(vEnd + 'T00:00:00Z');
                        // Check if checkin is within [start, end]
                        if (checkin < start || checkin > end) {
                            dateOk = false;
                        }
                    }

                    // If date is not ok, hide the voucher completely
                    if (!dateOk) {
                        if (container) {
                            container.style.display = 'none';
                            radio.checked = false;
                            radio.disabled = true;
                        }
                        return;
                    } else {
                        // If date is ok, show the voucher
                        if (container) container.style.display = '';
                    }

                    // If voucher has no loai_phong_id restriction or matches selected room type
                    // Treat empty selectedRoomTypes as 'no restriction' so vouchers can be chosen before rooms are picked
                    const roomOk = (!voucherRoomType || voucherRoomType === 'null' || voucherRoomType === '') || (selectedRoomTypes.length === 0) || selectedRoomTypes.includes(voucherRoomType);

                    // Enable/disable based on room type only (no visible message)
                    radio.disabled = !roomOk;

                    if (container) {
                        const label = container.querySelector('.voucher-label');
                        // remove dimming classes if present (server-rendered or previous state)
                        if (label) label.classList.remove('opacity-50', 'cursor-not-allowed');
                        // Remove any overlays (both client-created and server-rendered)
                        const overlays = container.querySelectorAll('.voucher-overlay-client, .voucher-overlay-server');
                        overlays.forEach(o => o.remove());
                    }
                });
            }
            // updateTotalPrice sẽ được override sau (xem dưới)
            function toggleService(checkbox, serviceId) {
                const quantityContainer = document.getElementById('service_quantity_container_' + serviceId);
                const hiddenInput = document.getElementById('service_quantity_hidden_' + serviceId);
                const quantityInput = document.getElementById('service_quantity_' + serviceId);

                if (checkbox.checked) {
                    quantityContainer.classList.remove('hidden');
                    hiddenInput.value = quantityInput.value || 1;
                } else {
                    quantityContainer.classList.add('hidden');
                    hiddenInput.value = 0;
                    quantityInput.value = 1;
                }

                updateTotalPrice();
            }
            function increaseServiceQuantity(serviceId) {
                const input = document.getElementById('service_quantity_' + serviceId);
                input.value = parseInt(input.value) + 1;
                updateServiceQuantityHidden(serviceId);
                updateTotalPrice();
            }
            function decreaseServiceQuantity(serviceId) {
                const input = document.getElementById('service_quantity_' + serviceId);
                const newValue = Math.max(1, parseInt(input.value) - 1);
                input.value = newValue;
                updateServiceQuantityHidden(serviceId);
                updateTotalPrice();
            }
            function updateServiceQuantityHidden(serviceId) {
                const displayInput = document.getElementById('service_quantity_' + serviceId);
                const hiddenInput = document.getElementById('service_quantity_hidden_' + serviceId);
                if (hiddenInput && displayInput) {
                    hiddenInput.value = displayInput.value;
                }
                updateTotalPrice();
            }
            // Cập nhật tổng tiền bao gồm cả phòng + voucher + dịch vụ (tính lại từ đầu)
            var updateTotalPrice = function() {
                const ngayNhan = document.getElementById('ngay_nhan').value;
                const ngayTra = document.getElementById('ngay_tra').value;

                if (!ngayNhan || !ngayTra) {
                    document.getElementById('total_price').textContent = formatCurrency(0);
                    return;
                }

                const startDate = new Date(ngayNhan);
                const endDate = new Date(ngayTra);
                const nights = Math.max(1, Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)));

                // 1. Tính tiền phòng theo loại phòng
                let roomTotalByType = {}; // { loaiPhongId: totalPrice }
                let roomTotal = 0;
                document.querySelectorAll('.room-type-checkbox:checked').forEach(checkbox => {
                    const roomTypeId = checkbox.value;
                    const quantityInput = document.getElementById('quantity_' + roomTypeId);
                    const quantity = parseInt(quantityInput?.value || 1);
                    const price = parseFloat(checkbox.dataset.price || 0);

                    if (price > 0 && quantity > 0) {
                        const typeTotal = price * nights * quantity;
                        roomTotalByType[roomTypeId] = typeTotal;
                        roomTotal += typeTotal;
                    }
                });

                // 2. Tính discount từ voucher - chỉ áp dụng cho tiền phòng, respecting loai_phong_id filter
                const selectedVoucher = document.querySelector('.voucher-radio:checked');
                let discount = 0;
                let discountedRoomTotal = roomTotal;

                if (selectedVoucher) {
                    const discountValue = parseFloat(selectedVoucher.dataset.value || 0);
                    const voucherLoaiPhongId = selectedVoucher.dataset.loaiPhong;
                    
                    let applicableTotal = 0;
                    if (!voucherLoaiPhongId || voucherLoaiPhongId === 'null') {
                        // Voucher applies to all room types
                        applicableTotal = roomTotal;
                    } else {
                        // Voucher applies only to specific room type
                        applicableTotal = roomTotalByType[voucherLoaiPhongId] || 0;
                    }
                    
                    if (discountValue <= 100) {
                        // Discount is percentage
                        discount = (applicableTotal * discountValue) / 100;
                    } else {
                        // Discount is fixed amount
                        discount = Math.min(discountValue, applicableTotal);
                    }
                    
                    discountedRoomTotal = roomTotal - discount;
                }

                let roomNetTotal = discountedRoomTotal;

                // 3. Tính tiền dịch vụ (không bị ảnh hưởng bởi voucher)
                let totalServicePrice = 0;
                
                function getTotalBookedRooms() {
                    let total = 0;
                    document.querySelectorAll('.room-type-checkbox:checked').forEach(cb => {
                        const id = cb.value;
                        const hidden = document.getElementById('quantity_hidden_' + id);
                        total += parseInt(hidden?.value || 0) || 0;
                    });
                    if (total === 0) {
                        total = document.querySelectorAll('.available-room-checkbox:checked').length || 0;
                    }
                    return total;
                }

                document.querySelectorAll('.service-checkbox:checked').forEach(checkbox => {
                    const serviceId = checkbox.value;
                    const price = parseFloat(checkbox.dataset.price || 0) || 0;
                    const card = document.querySelector('[data-service-id="' + serviceId + '"]');
                    if (!card) return;

                    const mode = card.querySelector('input[name="service_room_mode_' + serviceId + '"]:checked')?.value || 'global';
                    const rows = Array.from(card.querySelectorAll('#service_dates_' + serviceId + ' .service-date-row'));

                    rows.forEach(r => {
                        const qty = parseInt(r.querySelector('input[type=number]')?.value || 1) || 0;
                        if (qty <= 0) return;

                        if (mode === 'global') {
                            const roomsCount = getTotalBookedRooms();
                            totalServicePrice += price * qty * roomsCount;
                            console.log('service', serviceId, 'global mode, qty=', qty, 'rooms=', roomsCount, 'add=', price * qty * roomsCount);
                        } else {
                            const entryRooms = r.querySelectorAll('.entry-room-checkbox:checked').length || 0;
                            totalServicePrice += price * qty * entryRooms;
                            console.log('service', serviceId, 'specific mode, qty=', qty, 'checked rooms=', entryRooms, 'add=', price * qty * entryRooms);
                        }
                    });
                });

                // 4. Cộng tất cả: (phòng - discount) + dịch vụ
                const finalTotal = roomNetTotal + totalServicePrice;

                // Update UI
                const totalPriceElement = document.getElementById('total_price');
                const tongTienInput = document.getElementById('tong_tien_input');
                const originalPriceElement = document.getElementById('original_price');
                const discountAmountElement = document.getElementById('discount_amount');
                const discountInfoElement = document.getElementById('discount_info');

                totalPriceElement.textContent = formatCurrency(finalTotal);
                tongTienInput.value = finalTotal;

                if (selectedVoucher) {
                    originalPriceElement.textContent = formatCurrency(roomTotal);
                    discountAmountElement.textContent = '-' + formatCurrency(discount);
                    discountInfoElement.classList.remove('hidden');
                } else {
                    discountInfoElement.classList.add('hidden');
                }

                console.log('updateTotalPrice: roomTotal=', roomTotal, 'discount=', discount, 'roomNet=', roomNetTotal, 'services=', totalServicePrice, 'final=', finalTotal);
            };
            // Form validation is handled by PHP Laravel - no client-side validation needed
            // All validation errors will be displayed by Laravel's error directives
            // Initialize Tom Select for services and helper functions
            function loadTomSelectAndInit(cb) {
                if (window.TomSelect) return cb();
                var s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js';
                s.onload = cb;
                document.head.appendChild(s);
            }
            document.addEventListener('DOMContentLoaded', function() {
                loadTomSelectAndInit(function() {
                    try {
                        const selectEl = document.getElementById('services_select');
                        if (!selectEl) return;

                        const ts = new TomSelect(selectEl, {
                            plugins: ['remove_button'],
                            persist: false,
                            create: false,
                            onChange: function(values) {
                                renderSelectedServices(values || []);
                            }
                        });

                        // Render selected services list and hidden inputs (with per-date rows and 'Thêm ngày')
                        function getRangeDates() {
                            const start = document.getElementById('ngay_nhan')?.value;
                            const end = document.getElementById('ngay_tra')?.value;
                            if (!start || !end) return [];
                            const a = [];
                            const s = new Date(start);
                            const e = new Date(end);
                            for (let d = new Date(s); d <= e; d.setDate(d.getDate() + 1)) a.push(new Date(d).toISOString().split('T')[0]);
                            return a;
                        }

                        // Normalize service dates to the current range: keep same number of rows where possible,
                        // but clamp dates to the new range and remove excess rows if range shrinks.
                        function normalizeServiceDates() {
                            const range = getRangeDates();
                            if (!range.length) return;
                            document.querySelectorAll('[data-service-id]').forEach(card => {
                                const id = card.getAttribute('data-service-id');
                                const rows = Array.from(card.querySelectorAll('.service-date-row'));
                                // if no rows (shouldn't happen) ensure at least one
                                if (rows.length === 0) {
                                    const rowsContainer = document.getElementById('service_dates_'+id);
                                    if (rowsContainer) {
                                        const first = rowsContainer.appendChild(document.createElement('div'));
                                    }
                                }
                                // remove extra rows if range shorter
                                if (rows.length > range.length) {
                                    for (let i = rows.length - 1; i >= range.length; i--) rows[i].remove();
                                }
                                // now set each remaining row's date to the corresponding date in range
                                const nowRows = Array.from(card.querySelectorAll('.service-date-row'));
                                nowRows.forEach((r, idx) => {
                                    const d = r.querySelector('input[type=date]');
                                    if (d) d.value = range[Math.min(idx, range.length-1)];
                                });
                                // sync hidden inputs
                                const syncFn = card.querySelector('#service_dates_'+id) ? null : null;
                                // call global syncHiddenEntries by triggering a sync via existing function if present
                                try { const ev = new Event('service_range_changed'); document.getElementById('service_dates_'+id)?.dispatchEvent(ev); } catch(e){}
                            });
                        }

                        function renderSelectedServices(values) {
                            const container = document.getElementById('selected_services_list');

                            // remember previous row counts per service to preserve number of date-rows when re-rendering
                            const prevCounts = {};
                            Array.from(container.querySelectorAll('[data-service-id]')).forEach(card => {
                                const id = card.getAttribute('data-service-id');
                                prevCounts[id] = card.querySelectorAll('.service-date-row')?.length || 0;
                            });

                            container.innerHTML = '';
                            const range = getRangeDates();

                            (values || []).forEach(val => {
                                const option = selectEl.querySelector('option[value="' + val + '"]');
                                if (!option) return;
                                const serviceId = val;
                                const serviceName = option.textContent || option.innerText;
                                const servicePrice = parseFloat(option.dataset.price || 0);
                                const unit = option.dataset.unit || 'cái';
                        

                                // card wrapper
                                const card = document.createElement('div');
                                card.className = 'service-card-custom';
                                card.setAttribute('data-service-id', serviceId);

                                // header
                                const header = document.createElement('div');
                                header.className = 'service-card-header';
                                const title = document.createElement('div');
                                title.innerHTML = `<div class="service-title">${serviceName.split(' - ')[0]}</div>`;
                                const price = document.createElement('div');
                                price.className = 'service-price';
                                price.innerHTML = `${new Intl.NumberFormat('vi-VN').format(servicePrice)}/${unit}`;
                                header.appendChild(title);
                                header.appendChild(price);
                                card.appendChild(header);

                                // Room selection section
                                const roomSection = document.createElement('div');
                                roomSection.className = 'bg-blue-50 p-3 rounded-lg mt-3 border border-blue-200';
                                roomSection.id = 'room_selection_' + serviceId;
                                
                                const roomToggle = document.createElement('div');
                                roomToggle.className = 'flex gap-2 mb-2';
                                
                                const globalRadio = document.createElement('input');
                                globalRadio.type = 'radio';
                                globalRadio.name = 'service_room_mode_' + serviceId;
                                globalRadio.value = 'global';
                                globalRadio.checked = true;
                                globalRadio.id = 'global_' + serviceId;
                                
                                const globalLabel = document.createElement('label');
                                globalLabel.htmlFor = 'global_' + serviceId;
                                globalLabel.className = 'text-sm flex items-center gap-2 cursor-pointer';
                                globalLabel.innerHTML = '<span>Áp dụng tất cả phòng</span>';
                                
                                const specificRadio = document.createElement('input');
                                specificRadio.type = 'radio';
                                specificRadio.name = 'service_room_mode_' + serviceId;
                                specificRadio.value = 'specific';
                                specificRadio.id = 'specific_' + serviceId;
                                
                                const specificLabel = document.createElement('label');
                                specificLabel.htmlFor = 'specific_' + serviceId;
                                specificLabel.className = 'text-sm flex items-center gap-2 cursor-pointer';
                                specificLabel.innerHTML = '<span>Chọn phòng riêng</span>';
                                
                                roomToggle.appendChild(globalRadio);
                                roomToggle.appendChild(globalLabel);
                                roomToggle.appendChild(specificRadio);
                                roomToggle.appendChild(specificLabel);
                                roomSection.appendChild(roomToggle);
                                
                                // Toggle visibility on radio change: show/hide per-entry room containers
                                globalRadio.onchange = () => {
                                    // hide all per-entry room containers and uncheck any entry-room-checkboxes
                                    card.querySelectorAll('.entry-room-container').forEach(c => {
                                        c.classList.add('hidden');
                                        Array.from(c.querySelectorAll('input[type=checkbox]')).forEach(cb => cb.checked = false);
                                    });
                                    try { updateServiceRoomLists(); } catch(e){}
                                    syncHiddenEntries(serviceId);
                                    try { updateTotalPrice(); } catch(e){}
                                };

                                specificRadio.onchange = () => {
                                    // show per-entry room containers (they will be populated by updateServiceRoomLists)
                                    card.querySelectorAll('.entry-room-container').forEach(c => c.classList.remove('hidden'));
                                    try { updateServiceRoomLists(); } catch(e){}
                                    syncHiddenEntries(serviceId);
                                    try { updateTotalPrice(); } catch(e){}
                                };
                                
                                roomSection.appendChild(document.createElement('div')); // spacer placeholder to keep layout
                                card.appendChild(roomSection);

                                // date rows container
                                const rows = document.createElement('div');
                                rows.id = 'service_dates_' + serviceId;

                                function buildDateRow(dateVal) {
                                    const r = document.createElement('div'); r.className = 'service-date-row';
                                    const d = document.createElement('input'); d.type = 'date'; d.className = 'border rounded p-1'; d.value = dateVal || '';
                                    const rg = getRangeDates(); if (rg.length) { d.min = rg[0]; d.max = rg[rg.length-1]; }
                                    // store previous value on focus to allow revert if duplicate chosen
                                    d.addEventListener('focus', function(){ this.dataset.prev = this.value || ''; });
                                    // prevent selecting a date already used by another row of the same service
                                    d.addEventListener('change', function(){
                                        const val = this.value || '';
                                        if (!val) { syncHiddenEntries(serviceId); return; }
                                        const others = Array.from(document.querySelectorAll('#service_dates_'+serviceId+' input[type=date]'))
                                            .filter(i=>i !== this)
                                            .map(i=>i.value);
                                        if (others.includes(val)) {
                                            // revert and notify
                                            const prev = this.dataset.prev || '';
                                            this.value = prev;
                                            // small inline feedback
                                            alert('Ngày này đã được chọn cho dịch vụ này. Vui lòng chọn ngày khác.');
                                            return;
                                        }
                                        syncHiddenEntries(serviceId);
                                    });
                                    const q = document.createElement('input'); q.type = 'number'; q.min = 1; q.value = 1; q.className = 'w-24 border rounded p-1 text-center'; q.onchange = () => syncHiddenEntries(serviceId);
                                    const rem = document.createElement('button'); rem.type='button'; rem.className='service-remove-btn ml-2'; rem.textContent='Xóa'; rem.onclick = ()=>{ r.remove(); syncHiddenEntries(serviceId); };
                                    r.appendChild(d); r.appendChild(q); r.appendChild(rem);
                                    // container for per-entry room checkboxes (populated by updateServiceRoomLists)
                                    const entryRoomPlaceholder = document.createElement('div'); entryRoomPlaceholder.className = 'entry-room-container mt-2 pl-2 border-l';
                                    r.appendChild(entryRoomPlaceholder);
                                    return r;
                                }

                                // determine how many date rows to render (preserve previous count)
                                const want = Math.max(1, prevCounts[serviceId] || 1);
                                const maxRows = Math.min(want, range.length || 1);
                                for (let i=0;i<maxRows;i++) {
                                    const dateVal = (range.length && range[i]) ? range[i] : '';
                                    rows.appendChild(buildDateRow(dateVal));
                                }

                                // add-day button
                                const addBtn = document.createElement('button'); addBtn.type='button'; addBtn.className='service-add-day mt-2'; addBtn.textContent='Thêm ngày';
                                addBtn.onclick = function(){
                                    const used = Array.from(rows.querySelectorAll('input[type="date"]')).map(i=>i.value);
                                    const avail = getRangeDates().find(d=>!used.includes(d));
                                    if (avail) {
                                        rows.appendChild(buildDateRow(avail));
                                        try { updateServiceRoomLists(); } catch(e){}
                                        syncHiddenEntries(serviceId);
                                    }
                                };

                                card.appendChild(rows);
                                card.appendChild(addBtn);

                                // hidden inputs container
                                const hcb = document.createElement('input'); hcb.type='checkbox'; hcb.className='service-checkbox'; hcb.name='services[]'; hcb.value=serviceId; hcb.setAttribute('data-price', servicePrice); hcb.style.display='none'; hcb.checked=true;
                                const hsum = document.createElement('input'); hsum.type='hidden'; hsum.name='services_data['+serviceId+'][so_luong]'; hsum.id='service_quantity_hidden_'+serviceId; hsum.value='1';
                                const hdv = document.createElement('input'); hdv.type='hidden'; hdv.name='services_data['+serviceId+'][dich_vu_id]'; hdv.value=serviceId;

                                container.appendChild(card);
                                container.appendChild(hcb);
                                container.appendChild(hsum);
                                container.appendChild(hdv);

                                // sync per-entry hidden inputs and add-button state
                                function syncHiddenEntries(id){
                                    // If no rows remain for this service, remove the service selection entirely
                                    const rowsNow = Array.from(document.querySelectorAll('#service_dates_'+id+' .service-date-row'));
                                    if (rowsNow.length === 0) {
                                        try { ts.removeItem(id); } catch(e){
                                            // fallback: remove DOM nodes
                                            const card = document.querySelector('[data-service-id="'+id+'"]'); if(card) card.remove();
                                            Array.from(document.querySelectorAll('input[name="services[]"][value="'+id+'"]')).forEach(n=>n.remove());
                                        }
                                        updateTotalPrice();
                                        return;
                                    }

                                    // remove existing entry-hidden inputs for this id
                                    Array.from(document.querySelectorAll('input.entry-hidden[data-service="'+id+'"]')).forEach(n=>n.remove());
                                    
                                    // remove existing phong_ids hidden inputs for this service
                                    Array.from(document.querySelectorAll('input[name="services_data['+id+'][entries][]][phong_ids][]"]')).forEach(n => {
                                        n.remove();
                                    });

                                    // Determine current mode
                                    const card = document.querySelector('[data-service-id="'+id+'"]');
                                    const mode = card?.querySelector('input[name="service_room_mode_' + id + '"]:checked')?.value || 'global';

                                    let total=0;
                                    rowsNow.forEach((r, idx)=>{
                                        const dateVal = r.querySelector('input[type=date]')?.value || '';
                                        const qty = parseInt(r.querySelector('input[type=number]')?.value || 1);
                                        
                                        // Collect per-entry selected rooms (from entry-room-checkboxes inside this row)
                                        const entryRoomChecks = Array.from(r.querySelectorAll('.entry-room-checkbox:checked'));
                                        
                                        // If in specific mode and no rooms checked, skip this entry entirely
                                        if (mode === 'specific' && entryRoomChecks.length === 0) {
                                            console.log('syncHiddenEntries service', id, 'entry', idx, 'specific mode but NO rooms checked - SKIP');
                                            return;
                                        }
                                        
                                        total += qty;
                                        
                                        // Create hidden inputs for this entry
                                        const hNgay = document.createElement('input'); hNgay.type='hidden'; hNgay.name='services_data['+id+'][entries]['+idx+'][ngay]'; hNgay.value=dateVal; hNgay.className='entry-hidden'; hNgay.setAttribute('data-service', id);
                                        const hSo = document.createElement('input'); hSo.type='hidden'; hSo.name='services_data['+id+'][entries]['+idx+'][so_luong]'; hSo.value=qty; hSo.className='entry-hidden'; hSo.setAttribute('data-service', id);
                                        container.appendChild(hNgay); container.appendChild(hSo);

                                        // Only add phong_ids if in specific mode
                                        if (mode === 'specific') {
                                            entryRoomChecks.forEach((erc) => {
                                                const hRoom = document.createElement('input');
                                                hRoom.type = 'hidden';
                                                hRoom.name = 'services_data['+id+'][entries]['+idx+'][phong_ids][]';
                                                hRoom.value = erc.getAttribute('data-room-id') || erc.value;
                                                hRoom.className = 'entry-hidden';
                                                hRoom.setAttribute('data-service', id);
                                                container.appendChild(hRoom);
                                            });
                                            console.log('syncHiddenEntries service', id, 'entry', idx, 'specific mode, checked rooms:', entryRoomChecks.length, entryRoomChecks.map(e => e.value));
                                        } else {
                                            console.log('syncHiddenEntries service', id, 'entry', idx, 'global mode - apply to all');
                                        }
                                    });
                                    const sumEl = document.getElementById('service_quantity_hidden_'+id); if(sumEl) sumEl.value = total;
                                    updateTotalPrice(); updateAddBtnState(id);
                                }

                                function updateAddBtnState(id){ const rowsNow = document.querySelectorAll('#service_dates_'+id+' .service-date-row'); const used = Array.from(rowsNow).map(r=>r.querySelector('input[type=date]').value); const avail = getRangeDates().find(d=>!used.includes(d)); addBtn.disabled = !avail; addBtn.style.opacity = addBtn.disabled? '0.6':'1'; }

                                // Export to window so it can be called from other closures
                                window.syncHiddenEntries = syncHiddenEntries;

                                // call sync initially
                                syncHiddenEntries(serviceId);
                                updateAddBtnState(serviceId);
                            });

                            // Ensure totals recalc
                            updateTotalPrice();
                        }

                        // allow global removal
                        window.removeServiceFromSelect = function(id) {
                            ts.removeItem(id);
                        };

                        

                        // When increase/decrease functions run, they already call updateServiceQuantityHidden
                        // but we must ensure hidden inputs exist - renderSelectedServices created them.

                        // Render current initial selection if any
                        renderSelectedServices(ts.getValue() || []);
                        // Ensure service cards know about currently selected rooms
                        try { updateServiceRoomLists(); } catch(e){}

                        // Re-render / normalize service date-rows when global date range changes
                        const ngayNhanEl = document.getElementById('ngay_nhan');
                        const ngayTraEl = document.getElementById('ngay_tra');
                        if (ngayNhanEl && ngayTraEl) {
                            ngayNhanEl.addEventListener('change', function(){ normalizeServiceDates(); renderSelectedServices(ts.getValue() || []); });
                            ngayTraEl.addEventListener('change', function(){ normalizeServiceDates(); renderSelectedServices(ts.getValue() || []); });
                        }

                        // Voucher label deselection handled centrally in setupVoucherEventSystem()

                    } catch (e) {
                        console.error('TomSelect init error', e);
                    }
                });
            });
            // Initial update
            updateVoucherAvailability();
        </script>
    @endpush
@endsection
