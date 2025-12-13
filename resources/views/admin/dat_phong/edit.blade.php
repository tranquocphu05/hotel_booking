@extends('layouts.admin')

@section('title', 'Sửa đặt phòng')

@section('admin_content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
                    <h1 class="text-2xl font-bold text-gray-900">Sửa đặt phòng #{{ $booking->id }}</h1>
                    <p class="mt-1 text-sm text-gray-600">Chỉnh sửa thông tin đặt phòng của khách</p>
                </div>
                <div class="p-6">
                    @php
                        $roomTypes = $booking->getRoomTypes();
                    @endphp

                    <form id="bookingForm" method="POST" action="{{ route('admin.dat_phong.update', $booking->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Display success message -->
                        @if (session()->has('success'))
                            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                                <div class="text-sm font-semibold text-green-900">✓ {{ session('success') }}</div>
                            </div>
                        @endif

                        <!-- Display validation errors from session -->
                        @if (session()->has('errors') && session('errors')->any())
                            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                                <h4 class="text-sm font-semibold text-red-900 mb-2">Lỗi xác thực:</h4>
                                <ul class="text-sm text-red-700 space-y-1">
                                    @foreach (session('errors')->all() as $error)
                                        <li>• {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="space-y-8">


                        <!-- Quản lý loại phòng -->
                        <section>
                            <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Loại phòng được đặt</h3>
                                <button type="button" onclick="addRoom()"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                                    + Thêm loại phòng
                                </button>
                            </div>
                            <div id="roomTypesContainer" class="space-y-3">
                                @foreach ($roomTypes as $rt)
                                    @php
                                        $rtLoaiPhongId = $rt['loai_phong_id'] ?? null;
                                        $rtSoLuong = $rt['so_luong'] ?? 1;
                                        $rtLoaiPhong = $loaiPhongs->firstWhere('id', $rtLoaiPhongId);
                                        $rtTen = $rtLoaiPhong ? $rtLoaiPhong->ten_loai : 'Loại phòng';
                                        $rtGia = $rtLoaiPhong ? $rtLoaiPhong->gia_khuyen_mai : 0;
                                    @endphp
                                    <div class="room-item bg-white border border-gray-200 rounded-lg p-4" data-room-index="{{ $loop->index }}">
                                        <div class="flex justify-between items-start mb-4">
                                            <div>
                                                <h4 class="font-semibold text-gray-900">{{ $rtTen }}</h4>
                                                <p class="text-sm text-gray-600">{{ $rtSoLuong }} phòng</p>
                                            </div>
                                            <button type="button" onclick="removeRoom({{ $loop->index }})"
                                                class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                <i class="fas fa-trash-alt"></i> Xóa
                                            </button>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Loại phòng</label>
                                                <select name="room_types[{{ $loop->index }}][loai_phong_id]"
                                                    class="room-type-select w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                                    onchange="handleRoomTypeChange({{ $loop->index }}, this.value)"
                                                    required>
                                                    <option value="">-- Chọn loại phòng --</option>
                                                    @foreach ($loaiPhongs as $lp)
                                                        <option value="{{ $lp->id }}"
                                                            data-price="{{ $lp->gia_khuyen_mai }}"
                                                            {{ $rtLoaiPhongId == $lp->id ? 'selected' : '' }}>
                                                            {{ $lp->ten_loai }} - {{ number_format($lp->gia_khuyen_mai, 0, ',', '.') }} VNĐ/đêm
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div id="availability_text_{{ $loop->index }}" class="mt-1 text-xs text-gray-500"></div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Số lượng</label>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" onclick="decreaseQuantity({{ $loop->index }})"
                                                        class="w-8 h-8 border border-gray-300 rounded-md hover:bg-gray-50 text-gray-700 font-semibold">−</button>
                                                    <input type="number" name="room_types[{{ $loop->index }}][so_luong]"
                                                        class="room-quantity-input quantity-input flex-1 text-center px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                                        value="{{ $rtSoLuong }}" min="1" max="10"
                                                        data-room-index="{{ $loop->index }}"
                                                        onchange="updateRoomQuantity({{ $loop->index }})" required>
                                                    <button type="button" onclick="increaseQuantity({{ $loop->index }})"
                                                        class="w-8 h-8 border border-gray-300 rounded-md hover:bg-gray-50 text-gray-700 font-semibold">+</button>
                                                </div>
                                                <div class="mt-2 text-xs text-gray-600">
                                                    Giá: <span id="room_price_{{ $loop->index }}" class="font-semibold">{{ number_format($rtSoLuong * $rtGia, 0, ',', '.') }} VNĐ</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Chọn phòng cụ thể (nếu có) -->
                                        <div id="available_rooms_{{ $loop->index }}" class="mt-4 hidden">
                                            <div class="flex items-center justify-between">
                                                <label class="text-sm font-medium text-gray-700 mb-2 block">Chọn phòng cụ thể</label>
                                                <button type="button" onclick="loadAvailableRooms({{ $loop->index }})" class="ml-2 px-3 py-1 text-xs bg-blue-600 text-white rounded">Tải danh sách phòng</button>
                                            </div>
                                            <div class="space-y-2 pl-4 border-l-2 border-gray-300">
                                                <!-- Phòng sẽ được render bằng JavaScript -->
                                            </div>
                                            <p id="availability_text_{{ $loop->index }}" class="mt-2 text-xs text-gray-600"></p>
                                        </div>

                                        <input type="hidden" name="room_types[{{ $loop->index }}][gia_rieng]"
                                            id="room_gia_rieng_{{ $loop->index }}" value="{{ $rtSoLuong * $rtGia }}">
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        <!-- Ngày nhận, ngày trả, số người -->
                        <section>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">Thông tin đặt phòng</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="ngay_nhan" class="block text-sm font-medium text-gray-700 mb-2">Ngày nhận phòng</label>
                                    <input type="date" name="ngay_nhan" id="ngay_nhan"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                        value="{{ old('ngay_nhan', $booking->ngay_nhan ? date('Y-m-d', strtotime($booking->ngay_nhan)) : '') }}"
                                        required>
                                    @error('ngay_nhan')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="ngay_tra" class="block text-sm font-medium text-gray-700 mb-2">Ngày trả phòng</label>
                                    <input type="date" name="ngay_tra" id="ngay_tra"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                        value="{{ old('ngay_tra', $booking->ngay_tra ? date('Y-m-d', strtotime($booking->ngay_tra)) : '') }}"
                                        required>
                                    @error('ngay_tra')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="so_nguoi" class="block text-sm font-medium text-gray-700 mb-2">Số người</label>
                                    <input type="number" name="so_nguoi" id="so_nguoi"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                        value="{{ old('so_nguoi', $booking->so_nguoi ?? 1) }}" min="1" max="20" required>
                                    @error('so_nguoi')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        <!-- Chọn dịch vụ -->
                        <section>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">Chọn dịch vụ</h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
                                @foreach ($services as $service)
                                    @php
                                        $sid = $service->id;
                                        // Determine inclusion from actual booking services (BookingService records)
                                        // This is more reliable than checking the JS-friendly array which may be malformed.
                                        $isIncluded = isset($bookingServices) && $bookingServices->pluck('service_id')->contains($sid);
                                    @endphp
                                    <div
                                        class="flex flex-col items-start gap-1 p-2 bg-white rounded border border-blue-100">
                                        <input type="checkbox" id="service_select_{{ $sid }}"
                                            name="services_select[]" value="{{ $sid }}"
                                            {{ $isIncluded ? 'checked' : '' }} class="cursor-pointer" />
                                        <label for="service_select_{{ $sid }}" class="cursor-pointer">
                                            <div class="font-medium text-gray-900 text-xs">{{ $service->name }}</div>
                                            <div class="text-xs text-gray-600">
                                                {{ number_format($service->price, 0, ',', '.') }} VNĐ</div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <style>
                            .service-card-custom {
                                border-radius: 10px;
                                background: linear-gradient(135deg, #e0f2fe 0%, #bfdbfe 100%);
                                border: 1.5px solid #2563eb;
                                /* blue-600 */
                                padding: 0.875rem;
                                box-shadow: 0 6px 18px rgba(37, 99, 235, 0.06);
                            }

                            /* responsive grid for cards */
                            .service-card-grid {
                                display: grid;
                                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                                gap: 0.75rem
                            }

                            .service-card-header {
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                margin-bottom: 0.5rem;
                                padding-bottom: 0.4rem;
                                border-bottom: 1.5px solid #bfdbfe
                            }

                            .service-card-header .service-title {
                                color: #1e40af;
                                font-weight: 600;
                                font-size: 0.95rem
                            }

                            .service-card-header .service-price {
                                color: #1e3a8a;
                                font-weight: 600;
                                font-size: 0.85rem
                            }

                            .service-date-row {
                                display: flex;
                                gap: 0.5rem;
                                align-items: center;
                                margin-top: 0.5rem;
                                padding: 0.4rem;
                                background: #ffffff;
                                border-radius: 6px;
                                border: 1px solid #bfdbfe
                            }

                            .service-date-row input[type=date] {
                                border: 1px solid #93c5fd;
                                padding: 0.35rem 0.5rem;
                                border-radius: 5px;
                                background: #eff6ff;
                                font-size: 0.85rem;
                                flex: 1
                            }

                            .service-date-row input[type=number] {
                                border: 1px solid #93c5fd;
                                padding: 0.35rem 0.5rem;
                                border-radius: 5px;
                                background: #eff6ff;
                                width: 64px;
                                text-align: center;
                                font-size: 0.85rem
                            }

                            .service-add-day {
                                background: linear-gradient(135deg, #93c5fd 0%, #2563eb 100%);
                                color: #08203a;
                                padding: 0.4rem 0.6rem;
                                border-radius: 6px;
                                border: 1.5px solid #60a5fa;
                                cursor: pointer;
                                font-weight: 600;
                                font-size: 0.85rem
                            }

                            .service-add-day:hover {
                                background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
                                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.12)
                            }

                            .service-remove-btn {
                                background: #fee2e2;
                                color: #991b1b;
                                padding: 0.3rem 0.5rem;
                                border-radius: 5px;
                                border: 1px solid #fecaca;
                                cursor: pointer;
                                font-weight: 600;
                                font-size: 0.8rem
                            }

                            .service-remove-btn:hover {
                                background: #fca5a5;
                                box-shadow: 0 3px 10px rgba(185, 28, 28, 0.12)
                            }

                            /* selected list item hover */
                            #selected_services_list .service-card-custom {
                                transition: all .18s ease
                            }

                            #selected_services_list .service-card-custom:hover {
                                transform: translateY(-4px);
                                box-shadow: 0 10px 26px rgba(37, 99, 235, 0.12)
                            }

                            /* Room mode selection */
                            .service-room-mode {
                                display: flex;
                                gap: 0.75rem;
                                margin-top: 0.75rem;
                                padding: 0.75rem;
                                background: #f0fdf4;
                                border: 1.5px solid #86efac;
                                border-radius: 8px
                            }

                            .service-room-mode label {
                                display: flex;
                                align-items: center;
                                gap: 0.4rem;
                                font-size: 0.85rem;
                                color: #166534;
                                font-weight: 600;
                                cursor: pointer
                            }

                            .service-room-mode input[type=radio] {
                                cursor: pointer
                            }

                            /* Room container for per-entry selection */
                            .entry-room-container {
                                margin-top: 0.75rem;
                                padding: 0.75rem;
                                background: #f8f4ff;
                                border: 1.5px solid #d8b4fe;
                                border-radius: 6px
                            }

                            .entry-room-container .checkbox-group {
                                display: flex;
                                flex-wrap: wrap;
                                gap: 0.5rem
                            }

                            .entry-room-container .checkbox-group label {
                                display: flex;
                                align-items: center;
                                gap: 0.35rem;
                                background: white;
                                padding: 0.3rem 0.5rem;
                                border: 1px solid #d8b4fe;
                                border-radius: 5px;
                                font-size: 0.8rem;
                                cursor: pointer;
                                transition: all 0.15s ease
                            }

                            .entry-room-container .checkbox-group label:hover {
                                background: #faf5ff;
                                border-color: #c084fc
                            }

                            .entry-room-container .checkbox-group input[type=checkbox] {
                                cursor: pointer;
                                width: 14px;
                                height: 14px
                            }

                            .entry-room-container .checkbox-group input[type=checkbox]:checked+span {
                                color: #7e22ce;
                                font-weight: 600
                            }

                            .entry-room-container .checkbox-group span {
                                color: #6b21a8;
                                font-size: 0.8rem;
                                font-weight: 500
                            }
                        </style>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Chi tiết dịch vụ đã chọn</label>
                            <div class="service-card-grid grid grid-cols-1 md:grid-cols-3 gap-6 mt-4"
                                id="services_details_container">
                                {{-- Service cards will be rendered here by JavaScript based on selected services --}}
                            </div>
                        </div>

                        @if ($bookingServices->count() > 0)
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                                <h4 class="text-base font-semibold text-gray-900 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 17v-2h6v2m-7 4h8a2 2 0 002-2v-4a2 2 0 00-2-2H8a2 2 0 00-2 2v4a2 2 0 002 2zm3-13h4l1 2h3a1 1 0 011 1v3h-2v-2h-3.382l-.724-1.447A1 1 0 0013.943 8H12v2H9V8h3z" />
                                    </svg>
                                    Dịch vụ hiện có
                                </h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left font-medium text-gray-600">Dịch vụ</th>
                                                <th class="px-4 py-2 text-center font-medium text-gray-600">Ngày dùng</th>
                                                <th class="px-4 py-2 text-center font-medium text-gray-600">Số phòng</th>
                                                <th class="px-4 py-2 text-center font-medium text-gray-600">Số lượng</th>
                                                <th class="px-4 py-2 text-center font-medium text-gray-600">Đơn giá</th>
                                                <th class="px-4 py-2 text-right font-medium text-gray-600">Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @php
                                                // Reload booking services từ DB để lấy dữ liệu mới nhất (không phải cached data)
                                                $freshBookingServices = \App\Models\BookingService::with([
                                                    'service',
                                                    'phong',
                                                ])
                                                    ->where('dat_phong_id', $booking->id)
                                                    ->orderBy('used_at', 'asc')
                                                    ->get();
                                            @endphp
                                            @forelse ($freshBookingServices as $serviceLine)
                                                @php
                                                    $name =
                                                        $serviceLine->service->name ??
                                                        ($serviceLine->service_name ?? 'Dịch vụ');
                                                    $usedAt = $serviceLine->used_at
                                                        ? \Carbon\Carbon::parse($serviceLine->used_at)->format('d/m/Y')
                                                        : '-';
                                                    $phongNum = $serviceLine->phong
                                                        ? $serviceLine->phong->so_phong ?? $serviceLine->phong->id
                                                        : '-';
                                                    $qty = $serviceLine->quantity ?? 0;
                                                    $unit = $serviceLine->unit_price ?? 0;
                                                    $subtotal = $qty * $unit;
                                                @endphp
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-2 text-gray-900">{{ $name }}</td>
                                                    <td class="px-4 py-2 text-center text-gray-600">{{ $usedAt }}
                                                    </td>
                                                    <td class="px-4 py-2 text-center text-gray-600">{{ $phongNum }}
                                                    </td>
                                                    <td class="px-4 py-2 text-center text-gray-600">{{ $qty }}
                                                    </td>
                                                    <td class="px-4 py-2 text-center text-gray-600">
                                                        {{ number_format($unit, 0, ',', '.') }} VNĐ</td>
                                                    <td class="px-4 py-2 text-right font-semibold text-gray-900">
                                                        {{ number_format($subtotal, 0, ',', '.') }} VNĐ</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="px-4 py-2 text-center text-gray-500">Chưa có
                                                        dịch vụ nào</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3 pt-3 border-t border-gray-200 flex justify-between text-sm font-medium">
                                    <span class="text-gray-700">Tổng tiền dịch vụ hiện tại</span>
                                    <span class="text-purple-600">
                                        {{ number_format($bookingServices->sum(function ($s) {return ($s->quantity ?? 0) * ($s->unit_price ?? 0);}),0,',','.') }}
                                        VNĐ
                                    </span>
                                </div>
                            </div>
                        @endif

                        <!-- Voucher Selection -->
                        <div class="bg-purple-50 p-4 rounded-lg my-6 border border-purple-200">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Chọn mã giảm giá (nếu có)</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
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

                                    <div class="relative voucher-container" data-voucher-id="{{ $voucher->id }}">
                                        <input type="radio" name="voucher_radio" id="voucher_{{ $voucher->id }}"
                                            value="{{ $voucher->ma_voucher }}" class="sr-only peer voucher-radio"
                                            data-value="{{ $voucher->gia_tri }}"
                                            data-loai-phong="{{ $voucher->loai_phong_id }}"
                                            data-start="{{ $voucher->ngay_bat_dau ? date('Y-m-d', strtotime($voucher->ngay_bat_dau)) : '' }}"
                                            data-end="{{ $voucher->ngay_ket_thuc ? date('Y-m-d', strtotime($voucher->ngay_ket_thuc)) : '' }}"
                                            {{ $booking->voucher_id === $voucher->id ? 'checked' : '' }}> <label
                                            for="voucher_{{ $voucher->id }}"
                                            class="block p-4 bg-gray-50 border-2 border-gray-200 rounded-xl cursor-pointer relative transition-all duration-300 ease-in-out
                                            peer-checked:bg-white peer-checked:border-green-500 peer-checked:ring-2 peer-checked:ring-green-400 peer-checked:shadow-lg
                                            hover:bg-gray-100 hover:border-gray-300 hover:shadow-md
                                            z-0 peer-checked:z-10 voucher-label {{ $isDisabled ? 'opacity-50 cursor-not-allowed' : '' }}">

                                            {{-- Overlay hiển thị trạng thái --}}
                                            @if ($isDisabled)
                                                <div
                                                    class="voucher-overlay-server absolute inset-0 bg-opacity-70 flex items-center justify-center rounded-xl pointer-events-none">
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
                                                        {{ number_format($voucher->gia_tri, 0, ',', '.') }}₫
                                                    @endif
                                                </p>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Tổng tiền dịch vụ & tổng thanh toán -->
                        <div class="bg-gray-50 p-4 rounded-lg mt-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Tổng tiền</h3>
                            @php
                                $roomTotal = $booking->tong_tien_phong
                                    ?? (($booking->tong_tien ?? 0) - ($booking->tien_dich_vu ?? 0));
                                $roomTotal = max(0, $roomTotal);
                                $serviceTotal = $booking->tien_dich_vu ?? 0;

                                // Tính giảm giá từ voucher hiện tại (chỉ áp dụng trên tiền phòng)
                                $discount = 0;
                                if ($booking->voucher) {
                                    $giaTri = $booking->voucher->gia_tri ?? 0;
                                    if ($giaTri > 0) {
                                        if ($giaTri <= 100) {
                                            // giảm theo %
                                            $discount = ($roomTotal * $giaTri) / 100;
                                        } else {
                                            // giảm số tiền cố định, không vượt quá roomTotal
                                            $discount = min($giaTri, $roomTotal);
                                        }
                                    }
                                }
                                $roomAfter = max(0, $roomTotal - $discount);
                                $grandTotal = $booking->tong_tien ?? ($roomAfter + $serviceTotal);
                            @endphp

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="text-sm text-gray-700">Tổng giá phòng (đã nhân số đêm)</div>
                                <div class="text-sm font-medium text-right text-gray-900" id="total_room_price">
                                    {{ number_format($roomTotal, 0, ',', '.') }} VNĐ
                                </div>

                                <div class="text-sm text-gray-700">Mã giảm giá</div>
                                <div class="text-sm font-medium text-right text-red-600" id="discount_amount">
                                    {{ number_format($discount, 0, ',', '.') }} VNĐ
                                </div>

                                <div class="text-sm text-gray-700">Tổng phòng (sau giảm)</div>
                                <div class="text-sm font-medium text-right text-gray-900" id="room_after_discount">
                                    {{ number_format($roomAfter, 0, ',', '.') }} VNĐ
                                </div>

                                <div class="text-sm text-gray-700">Tổng giá dịch vụ</div>
                                <div class="text-sm font-medium text-right text-gray-900" id="total_service_price">
                                    {{ number_format($serviceTotal, 0, ',', '.') }} VNĐ
                                </div>

                                <div class="border-t-2 border-gray-300 pt-2 text-base font-semibold text-gray-900">Tổng
                                    thanh toán</div>
                                <div class="border-t-2 border-gray-300 pt-2 text-lg font-semibold text-right text-blue-600"
                                    id="total_price">{{ number_format($grandTotal, 0, ',', '.') }} VNĐ</div>
                            </div>
                            <input type="hidden" name="tong_tien" id="tong_tien_input"
                                value="{{ old('tong_tien', $grandTotal) }}">
                            <input type="hidden" name="voucher_id" id="voucher_id_input"
                                value="{{ old('voucher_id', $booking->voucher_id ?? '') }}">
                            <!-- Mirror field name expected by backend: 'voucher' -->
                            <input type="hidden" name="voucher" id="voucher_input"
                                value="{{ old('voucher', optional($booking->voucher)->ma_voucher ?? '') }}">
                        </div>

                        <!-- Thông tin khách hàng -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Thông tin khách hàng</h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="username" class="block text-sm font-medium text-gray-700">Tên khách
                                        hàng</label>
                                    <input type="text" name="username" id="username"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        value="{{ old('username', $booking->username) }}" required>
                                    @error('username')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" name="email" id="email"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        value="{{ old('email', $booking->email) }}" required>
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="sdt" class="block text-sm font-medium text-gray-700">Số điện
                                            thoại</label>
                                        <input type="text" name="sdt" id="sdt"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            value="{{ old('sdt', $booking->sdt) }}" required>
                                        @error('sdt')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="ngay_nhan" class="block text-sm font-medium text-gray-700">Ngày nhận phòng</label>
                                            <input type="date" name="ngay_nhan" id="ngay_nhan"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                value="{{ old('ngay_nhan', date('Y-m-d', strtotime($booking->ngay_nhan))) }}" required>
                                            @error('ngay_nhan')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="ngay_tra" class="block text-sm font-medium text-gray-700">Ngày trả phòng</label>
                                            <input type="date" name="ngay_tra" id="ngay_tra"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                value="{{ old('ngay_tra', date('Y-m-d', strtotime($booking->ngay_tra))) }}" required>
                                            @error('ngay_tra')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Chọn dịch vụ (giống trang tạo) -->
                            <!-- Tom Select based multi-select for services -->
                            <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css" rel="stylesheet">
                            <style>
                                .service-card-custom{border-radius:12px;background:linear-gradient(135deg, #f0fdfc 0%, #ccfbf1 100%);border:2px solid #99f6e4;padding:1.25rem;box-shadow:0 10px 25px rgba(16, 185, 129, 0.08);}
                                .service-card-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1.25rem}
                                .service-card-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;padding-bottom:.5rem;border-bottom:2px solid #d1fae5}
                                .service-card-header .service-title{color:#0d9488;font-weight:700;font-size:1.1rem}
                                .service-card-header .service-price{color:#0f766e;font-weight:600;font-size:0.95rem}
                                .service-date-row{display:flex;gap:.75rem;align-items:center;margin-top:.75rem;padding:.5rem;background:#ffffff;border-radius:8px;border:1px solid #d1fae5}
                                .service-date-row input[type=date]{border:1px solid #a7f3d0;padding:.45rem .6rem;border-radius:6px;background:#f0fdfc;font-size:0.9rem;flex:1}
                                .service-date-row input[type=number]{border:1px solid #a7f3d0;padding:.45rem .6rem;border-radius:6px;background:#f0fdfc;width:80px;text-align:center}
                                .service-add-day{background:linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);color:#0d7377;padding:.5rem .75rem;border-radius:8px;border:1.5px solid #6ee7b7;cursor:pointer;font-weight:600;font-size:0.9rem}
                                .service-add-day:hover{background:linear-gradient(135deg, #a7f3d0 0%, #6ee7b7 100%);box-shadow:0 4px 12px rgba(13, 148, 136, 0.2)}
                                .service-remove-btn{background:#fecaca;color:#991b1b;padding:.4rem .6rem;border-radius:6px;border:1px solid #fca5a5;cursor:pointer;font-weight:600;font-size:0.85rem}
                                .service-remove-btn:hover{background:#f87171;box-shadow:0 4px 12px rgba(185, 28, 28, 0.15)}
                                #services_select + .ts-control{margin-top:.5rem;border-color:#99f6e4}
                                #selected_services_list .service-card-custom{transition:all .2s ease}
                                #selected_services_list .service-card-custom:hover{transform:translateY(-6px);box-shadow:0 15px 35px rgba(16, 185, 129, 0.15)}
                            </style>
                            </style>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label for="services_select" class="block text-sm font-medium text-gray-700 mb-2">Chọn dịch vụ kèm theo</label>
                                <select id="services_select" placeholder="Chọn 1 hoặc nhiều dịch vụ..." multiple>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->id }}" data-price="{{ $service->price }}" data-unit="{{ $service->unit ?? 'cái' }}">{{ $service->name }} - {{ number_format($service->price,0,',','.') }} VNĐ</option>
                                    @endforeach
                                </select>
                                <div id="selected_services_list" class="service-card-grid grid grid-cols-1 md:grid-cols-3 gap-6 mt-4"></div>
                            </div>

                            <!-- Tổng tiền dịch vụ & tổng thanh toán -->
                            <div class="bg-gray-50 p-4 rounded-lg mt-4">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Tổng tiền</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="text-sm text-gray-700">Tổng giá dịch vụ</div>
                                    <div class="text-sm font-medium text-right text-gray-900" id="total_service_price">
                                        {{ number_format($booking->tien_dich_vu ?? 0, 0, ',', '.') }} VNĐ
                                    </div>

                                    <div class="text-sm text-gray-700">Tổng giá phòng (áp dụng ngày thường/cuối tuần/ngày lễ)</div>
                                    <div class="text-sm font-medium text-right text-gray-900" id="total_room_price">
                                        @php
                                            $roomTotalInit = $booking->tong_tien_phong
                                                ?? (($booking->tong_tien ?? 0) - ($booking->tien_dich_vu ?? 0));
                                        @endphp
                                        {{ number_format(max(0, $roomTotalInit), 0, ',', '.') }} VNĐ
                                    </div>

                                    <div class="text-sm text-gray-700">Tổng thanh toán</div>
                                    <div class="text-lg font-semibold text-right text-blue-600" id="total_price">
                                        {{ number_format($booking->tong_tien ?? 0, 0, ',', '.') }} VNĐ
                                    </div>
                                </div>
                                <div id="pricing_multiplier_info" class="mt-2 text-xs text-gray-500"></div>
                                <input type="hidden" name="tong_tien" id="tong_tien_input" value="{{ old('tong_tien', $booking->tong_tien ?? 0) }}">
                            </div>

                            <!-- Thông tin khách hàng -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Thông tin khách hàng</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label for="username" class="block text-sm font-medium text-gray-700">Tên khách hàng</label>
                                        <input type="text" name="username" id="username"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            value="{{ old('username', $booking->username) }}" required>
                                        @error('username')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="cccd"
                                            class="block text-sm font-medium text-gray-700">CCCD/CMND</label>
                                        <input type="text" name="cccd" id="cccd"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            value="{{ old('cccd', $booking->cccd) }}"
                                            placeholder="{{ $booking->cccd ? '' : 'Chưa cập nhật CCCD' }}" required>
                                        @if (!$booking->cccd)
                                            <p class="mt-1 text-xs text-yellow-600">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                Đặt phòng cũ chưa có CCCD, vui lòng cập nhật
                                            </p>
                                        @endif
                                        @error('cccd')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>



                        <input type="hidden" name="trang_thai" id="trang_thai_input" value="{{ old('trang_thai', $booking->trang_thai ?? '') }}">

                        <div class="pt-5">
    <div class="flex justify-between items-center gap-3">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.dat_phong.index') }}"
                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Quay lại
            </a>
        </div>

        <!-- Group buttons -->
        <div class="flex items-center">

            <!-- Confirm -->
            <form action="{{ route('admin.dat_phong.confirm', $booking->id) }}"
                  method="POST" class="inline mx-1"
                  onsubmit="return confirm('Xác nhận đặt phòng #{{ $booking->id }}?')">
                @csrf
                @method('PUT')
                <button type="submit" title="Xác nhận"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Xác nhận
                </button>
            </form>

            <!-- Cancel: Chỉ Admin -->
            @hasRole('admin')
            <a href="{{ route('admin.dat_phong.cancel', $booking->id) }}" title="Hủy"
                class="inline-flex items-center px-4 py-2 mx-1 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700"
                onclick="if(!confirm('Bạn có chắc chắn muốn hủy đặt phòng #{{ $booking->id }}?')) return false;">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Hủy đặt phòng
            </a>
            @endhasRole

            <!-- Update -->
            <button type="submit"
                class="inline-flex items-center px-4 py-2 mx-1 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 13l4 4L19 7" />
                </svg>
                Cập nhật
            </button>

        </div>
    </div>
</div>

                </div>
                </form>
            </div>
        </div>
    </div>
    </div>

    @push('scripts')
        <script>
            // // Định dạng tiền tệ VND (tương tự trang tạo mới)
            // function formatCurrency(amount) {
            //     amount = Number(amount) || 0;
            //     return new Intl.NumberFormat('vi-VN', {
            //         style: 'currency',
            //         currency: 'VND',
            //     }).format(amount).replace('₫', 'VNĐ');
            // }

            // Get booking dates
            function getRangeDates() {
                const start = document.getElementById('ngay_nhan')?.value;
                const end = document.getElementById('ngay_tra')?.value;
                if (!start || !end) return [];
                const dates = [];
                const s = new Date(start);
                const e = new Date(end);
                for (let d = new Date(s); d <= e; d.setDate(d.getDate() + 1)) {
                    dates.push(new Date(d).toISOString().split('T')[0]);
                }
                return dates;
            }

            // Initialize service rendering
            const allServices = @json($services ?? []);
            const bookingServicesData = @json($bookingServicesServer ?? []);
            // make these mutable so client-side changes reflect immediately
            let assignedPhongIds = @json($assignedPhongIds ?? []);
            let roomMapData = @json($roomMap ?? []);
            // (flag removed) previously used to distinguish user-triggered updates

            // Sync assignedPhongIds and roomMapData from current room checkboxes
            function syncAssignedPhongIdsFromCheckboxes() {
                try {
                    const checked = Array.from(document.querySelectorAll('input.available-room-checkbox:checked'))
                        .map(cb => parseInt(cb.value)).filter(Boolean);
                    assignedPhongIds = Array.from(new Set(checked));

                    // Ensure roomMapData has labels for any known checkboxes
                    Array.from(document.querySelectorAll('input.available-room-checkbox')).forEach(cb => {
                        const id = parseInt(cb.value);
                        const name = cb.getAttribute('data-room-name') || cb.getAttribute('data-room-id') || id;
                        if (id && !(id in roomMapData)) {
                            roomMapData[id] = name;
                        }
                    });

                    // Re-render service room lists to reflect updated assigned rooms
                    updateServiceRoomLists();

                    // Auto-switch service room mode: if any rooms selected -> specific, if none -> global
                    document.querySelectorAll('[data-service-id]').forEach(card => {
                        const sid = card.getAttribute('data-service-id');
                        const globalRadio = card.querySelector('input[name="service_room_mode_' + sid + '"][value="global"]');
                        const specificRadio = card.querySelector('input[name="service_room_mode_' + sid + '"][value="specific"]');
                        if (assignedPhongIds && assignedPhongIds.length > 0) {
                            if (specificRadio && !specificRadio.checked) {
                                specificRadio.checked = true;
                                if (typeof specificRadio.onchange === 'function') specificRadio.onchange();
                            }
                        } else {
                            if (globalRadio && !globalRadio.checked) {
                                globalRadio.checked = true;
                                if (typeof globalRadio.onchange === 'function') globalRadio.onchange();
                            }
                        }
                    });
                } catch (e) {
                    console.error('syncAssignedPhongIdsFromCheckboxes error', e);
                }
            }

            function buildDateRow(serviceId, dateVal = '') {
                const idx = 'new_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                const r = document.createElement('div');
                r.className = 'service-date-row';

                const d = document.createElement('input');
                d.type = 'date';
                d.className = 'border rounded p-1';
                d.value = dateVal || '';
                const rg = getRangeDates();
                if (rg.length) {
                    d.min = rg[0];
                    d.max = rg[rg.length - 1];
                }
                d.addEventListener('focus', function() {
                    this.dataset.prev = this.value || '';
                });
                d.addEventListener('change', function() {
                    const val = this.value || '';
                    if (!val) {
                        syncHiddenEntries(serviceId);
                        return;
                    }
                    const others = Array.from(document.querySelectorAll('#service_dates_' + serviceId +
                            ' input[type=date]'))
                        .filter(i => i !== this)
                        .map(i => i.value);
                    if (others.includes(val)) {
                        const prev = this.dataset.prev || '';
                        this.value = prev;
                        alert('Ngày này đã được chọn cho dịch vụ này. Vui lòng chọn ngày khác.');
                        return;
                    }
                    syncHiddenEntries(serviceId);
                });

                const q = document.createElement('input');
                q.type = 'number';
                q.min = 1;
                q.value = 1;
                q.className = 'w-24 border rounded p-1 text-center';
                q.onchange = () => syncHiddenEntries(serviceId);

                const rem = document.createElement('button');
                rem.type = 'button';
                rem.className = 'service-remove-btn ml-2';
                rem.textContent = 'Xóa';
                rem.onclick = () => {
                    r.remove();
                    syncHiddenEntries(serviceId);
                    window.computeTotals && window.computeTotals();
                };

                r.appendChild(d);
                r.appendChild(q);
                r.appendChild(rem);

                // Entry room container for per-room selection - INLINE
                const entryRoomContainer = document.createElement('div');
                entryRoomContainer.className = 'entry-room-container';
                entryRoomContainer.style.display = 'none';
                r.appendChild(entryRoomContainer);

                return r;
            }

            // Update room lists in service cards - render room checkboxes inline
            function updateServiceRoomLists(filterServiceId) {
                const selectedRoomInputs = Array.from(document.querySelectorAll('.available-room-checkbox:checked'));

                document.querySelectorAll('[data-service-id]').forEach(card => {
                    const serviceId = card.getAttribute('data-service-id');

                    // If filterServiceId is provided, only update that service
                    if (filterServiceId && parseInt(serviceId) !== parseInt(filterServiceId)) {
                        return;
                    }

                    const specificRadio = card.querySelector('input[name="service_room_mode_' + serviceId +
                        '"][value="specific"]');
                    const isSpecific = specificRadio ? specificRadio.checked : false;
                    console.log('updateServiceRoomLists: service', serviceId, 'isSpecific=', isSpecific,
                        'assignedPhongIds=', assignedPhongIds);

                    const rows = card.querySelectorAll('.service-date-row');
                    rows.forEach((r) => {
                        const dateValRow = r.querySelector('input[type=date]')?.value || '';
                        let entryRoomContainer = r.querySelector('.entry-room-container');
                        if (!entryRoomContainer) {
                            entryRoomContainer = document.createElement('div');
                            entryRoomContainer.className = 'entry-room-container';
                            r.appendChild(entryRoomContainer);
                        }
                        entryRoomContainer.innerHTML = '';

                        if (!isSpecific) {
                            entryRoomContainer.style.display = 'none';
                            return;
                        } else {
                            entryRoomContainer.style.display = '';
                        }

                        // Get union of assigned rooms + all rooms from service data (to show even unassigned rooms that have service data)
                        const roomsToShow = new Set(assignedPhongIds);

                        // Build entriesByDate for this service so we can pre-check boxes per row date
                        const entriesByDate = {};
                        if (bookingServicesData[serviceId]) {
                            bookingServicesData[serviceId]['entries'].forEach(entry => {
                                const day = entry['ngay'] || '';
                                if (!entriesByDate[day]) entriesByDate[day] = {
                                    phong_ids: []
                                };
                                if (entry['phong_ids'] && Array.isArray(entry['phong_ids'])) {
                                    entriesByDate[day].phong_ids = Array.from(new Set([
                                        ...entriesByDate[day].phong_ids,
                                        ...entry['phong_ids']
                                    ]));
                                    entry['phong_ids'].forEach(pid => roomsToShow.add(pid));
                                }
                            });
                        }

                        // Sort room IDs for consistent display
                        const roomsArray = Array.from(roomsToShow).sort((a, b) => a - b);
                        console.log('updateServiceRoomLists: service', serviceId, 'roomsArray=', roomsArray,
                            'rowDate=', dateValRow);

                        roomsArray.forEach(pid => {
                            const pidInt = parseInt(pid);
                            const label = roomMapData[pid] || pid;
                            const ewrap = document.createElement('div');
                            ewrap.className = 'inline-flex items-center gap-1 mr-2';

                            const ecb = document.createElement('input');
                            ecb.type = 'checkbox';
                            ecb.className = 'entry-room-checkbox';
                            ecb.setAttribute('data-room-id', pid);
                            ecb.value = pid;

                            ecb.onchange = () => {
                                syncHiddenEntries(serviceId);
                                window.computeTotals && window.computeTotals();
                            };

                            // Pre-check if bookingServicesData has this room for this row date
                            try {
                                const phongIdsForDate = (entriesByDate[dateValRow]?.phong_ids || [])
                                    .map(p => parseInt(p));
                                // Only pre-check if this service entry explicitly contains this room
                                // (i.e. the room was saved for this service in DB). Do NOT auto-check
                                // when admin merely selects rooms in the room list — user should
                                // manually choose service rooms.
                                if (phongIdsForDate.includes(pidInt)) {
                                    ecb.checked = true;
                                    console.log('Pre-checked service', serviceId, 'date', dateValRow,
                                        'room', pidInt);
                                }
                            } catch (e) {
                                // ignore parsing issues
                            }

                            const elbl = document.createElement('label');
                            elbl.className = 'text-xs cursor-pointer';
                            elbl.textContent = label;

                            ewrap.appendChild(ecb);
                            ewrap.appendChild(elbl);
                            entryRoomContainer.appendChild(ewrap);
                            // debug marker
                            //console.log('Appended checkbox for service', serviceId, 'room', pid, 'row date', r.querySelector('input[type=date]')?.value);
                        });
                    });

                    // After rebuilding entry-room-checkboxes for this service, ensure hidden inputs reflect current state
                    try {
                        syncHiddenEntries(serviceId);
                        window.computeTotals && window.computeTotals();
                    } catch (e) {
                        // ignore
                    }
                });
            }

            function renderServiceCard(service) {
                const sid = service.id;
                const existing = bookingServicesData[sid] ? bookingServicesData[sid]['entries'] || [] : [];
                const hasSpecific = existing.some(e => e['phong_ids'] && e['phong_ids'].length > 0);

                // Card wrapper
                const card = document.createElement('div');
                card.className = 'service-card-custom';
                card.setAttribute('data-service-id', sid);

                // Header
                const header = document.createElement('div');
                header.className = 'service-card-header';
                const title = document.createElement('div');
                title.innerHTML = `<div class="service-title">${service.name}</div>`;
                const price = document.createElement('div');
                price.className = 'service-price';
                price.innerHTML = `${new Intl.NumberFormat('vi-VN').format(service.price)}/${service.unit || 'cái'}`;
                header.appendChild(title);
                header.appendChild(price);
                card.appendChild(header);

                // Room selection radios
                const roomSection = document.createElement('div');
                roomSection.className = 'service-room-mode';

                const globalRadio = document.createElement('input');
                globalRadio.type = 'radio';
                globalRadio.name = 'service_room_mode_' + sid;
                globalRadio.value = 'global';
                globalRadio.checked = !hasSpecific;
                globalRadio.id = 'global_' + sid;

                const globalLabel = document.createElement('label');
                globalLabel.htmlFor = 'global_' + sid;
                globalLabel.className = 'text-sm flex items-center gap-2 cursor-pointer';
                globalLabel.innerHTML = '<span>Áp dụng tất cả phòng</span>';

                const specificRadio = document.createElement('input');
                specificRadio.type = 'radio';
                specificRadio.name = 'service_room_mode_' + sid;
                specificRadio.value = 'specific';
                specificRadio.checked = hasSpecific;
                specificRadio.id = 'specific_' + sid;

                const specificLabel = document.createElement('label');
                specificLabel.htmlFor = 'specific_' + sid;
                specificLabel.className = 'text-sm flex items-center gap-2 cursor-pointer';
                specificLabel.innerHTML = '<span>Chọn phòng riêng</span>';

                roomSection.appendChild(globalRadio);
                roomSection.appendChild(globalLabel);
                roomSection.appendChild(specificRadio);
                roomSection.appendChild(specificLabel);

                globalRadio.onchange = () => {
                    card.querySelectorAll('.entry-room-container').forEach(c => {
                        c.style.display = 'none';
                        Array.from(c.querySelectorAll('input[type=checkbox]')).forEach(cb => cb.checked = false);
                    });
                    updateServiceRoomLists();
                    syncHiddenEntries(sid);
                    window.computeTotals && window.computeTotals();
                };

                specificRadio.onchange = () => {
                    updateServiceRoomLists();
                    syncHiddenEntries(sid);
                    window.computeTotals && window.computeTotals();
                };

                card.appendChild(roomSection);

                // Date rows container
                const rows = document.createElement('div');
                rows.id = 'service_dates_' + sid;

                // Render existing entries - GROUP BY DATE to avoid duplicates
                if (existing.length > 0) {
                    const entriesByDate = {};
                    existing.forEach((entry, idx) => {
                        const day = entry['ngay'] || '';
                        if (!entriesByDate[day]) {
                            entriesByDate[day] = {
                                so_luong: entry['so_luong'] || 1,
                                phong_ids: entry['phong_ids'] || [],
                                first_idx: idx
                            };
                        } else {
                            // Merge phong_ids if multiple entries for same date
                            entriesByDate[day].phong_ids = Array.from(new Set([
                                ...entriesByDate[day].phong_ids,
                                ...(entry['phong_ids'] || [])
                            ]));
                        }
                    });

                    Object.entries(entriesByDate).forEach(([day, data]) => {
                        const row = buildDateRow(sid, day);
                        // Set quantity
                        row.querySelector('input[type=number]').value = data.so_luong || 1;
                        rows.appendChild(row);
                    });
                }

                card.appendChild(rows);

                // Populate checkboxes and let updateServiceRoomLists handle pre-checking
                updateServiceRoomLists();

                // Add day button
                const addBtn = document.createElement('button');
                addBtn.type = 'button';
                addBtn.className = 'service-add-day mt-3';
                addBtn.textContent = '+ Thêm ngày';
                addBtn.onclick = () => {
                    const used = Array.from(rows.querySelectorAll('input[type="date"]')).map(i => i.value);
                    const avail = getRangeDates().find(d => !used.includes(d));
                    if (avail) {
                        const newRow = buildDateRow(sid, avail);
                        rows.appendChild(newRow);
                        updateServiceRoomLists();
                        syncHiddenEntries(sid);
                        updateAddBtnState();
                        window.computeTotals && window.computeTotals();
                    } else {
                        alert('Đã chọn đủ ngày. Không thể thêm ngày nữa.');
                    }
                };

                card.appendChild(addBtn);

                // Hidden service marker
                const hcb = document.createElement('input');
                hcb.type = 'checkbox';
                hcb.className = 'service-checkbox';
                hcb.name = 'services[]';
                hcb.value = sid;
                hcb.setAttribute('data-price', service.price);
                hcb.style.display = 'none';
                hcb.checked = true;
                card.appendChild(hcb);

                // Hidden total quantity
                const hsum = document.createElement('input');
                hsum.type = 'hidden';
                hsum.name = 'services_data[' + sid + '][so_luong]';
                hsum.id = 'service_quantity_hidden_' + sid;
                hsum.value = '1';
                card.appendChild(hsum);

                // Hidden service ID
                const hdv = document.createElement('input');
                hdv.type = 'hidden';
                hdv.name = 'services_data[' + sid + '][dich_vu_id]';
                hdv.value = sid;
                card.appendChild(hdv);

                return card;
            }

            function syncHiddenEntries(serviceId) {
                const container = document.getElementById('services_details_container');

                // Get all entry rows
                const rowsNow = Array.from(document.querySelectorAll('#service_dates_' + serviceId + ' .service-date-row'));

                // Remove existing hidden entry inputs for this service
                Array.from(document.querySelectorAll('input.entry-hidden[data-service="' + serviceId + '"]')).forEach(n => {
                    n.remove();
                });

                // Determine current mode
                const card = document.querySelector('[data-service-id="' + serviceId + '"]');
                const mode = card?.querySelector('input[name="service_room_mode_' + serviceId + '"]:checked')?.value ||
                    'global';

                let total = 0;
                rowsNow.forEach((r, idx) => {
                    const dateVal = r.querySelector('input[type=date]')?.value || '';
                    const qty = parseInt(r.querySelector('input[type=number]')?.value || 1);

                    // Collect per-entry selected rooms
                    const entryRoomChecks = Array.from(r.querySelectorAll('.entry-room-checkbox:checked'));

                    // If specific mode but no rooms checked, skip this entry
                    if (mode === 'specific' && entryRoomChecks.length === 0) {
                        return;
                    }

                    total += qty;

                    // Create hidden inputs for this entry
                    const hNgay = document.createElement('input');
                    hNgay.type = 'hidden';
                    hNgay.name = 'services_data[' + serviceId + '][entries][' + idx + '][ngay]';
                    hNgay.value = dateVal;
                    hNgay.className = 'entry-hidden';
                    hNgay.setAttribute('data-service', serviceId);
                    container.appendChild(hNgay);

                    const hSo = document.createElement('input');
                    hSo.type = 'hidden';
                    hSo.name = 'services_data[' + serviceId + '][entries][' + idx + '][so_luong]';
                    hSo.value = qty;
                    hSo.className = 'entry-hidden';
                    hSo.setAttribute('data-service', serviceId);
                    container.appendChild(hSo);

                    // Add room IDs if specific mode
                    if (mode === 'specific') {
                        entryRoomChecks.forEach((erc) => {
                            const hRoom = document.createElement('input');
                            hRoom.type = 'hidden';
                            hRoom.name = 'services_data[' + serviceId + '][entries][' + idx + '][phong_ids][]';
                            hRoom.value = erc.getAttribute('data-room-id') || erc.value;
                            hRoom.className = 'entry-hidden';
                            hRoom.setAttribute('data-service', serviceId);
                            container.appendChild(hRoom);
                        });
                    }
                });

                const sumEl = document.getElementById('service_quantity_hidden_' + serviceId);
                if (sumEl) sumEl.value = total;
            }

            document.addEventListener('DOMContentLoaded', function() {
                const container = document.getElementById('services_details_container');
                const serviceSelects = document.querySelectorAll('input[name="services_select[]"]');

                function updateServiceCards() {
                    const selectedServiceIds = Array.from(serviceSelects)
                        .filter(cb => cb.checked)
                        .map(cb => cb.value);

                    // Remove cards for unselected services
                    container.querySelectorAll('.service-card-container').forEach(card => {
                        const sid = card.getAttribute('data-service-id');
                        if (!selectedServiceIds.includes(sid)) {
                            card.remove();
                        }
                    });

                    // Add cards for newly selected services
                    selectedServiceIds.forEach(sid => {
                        if (!container.querySelector(`[data-service-id="${sid}"]`)) {
                            const service = allServices.find(s => s.id == sid);
                            if (service) {
                                const card = renderServiceCard(service);
                                card.className = 'service-card-custom service-card-container';
                                container.appendChild(card);
                                syncHiddenEntries(sid);
                                updateServiceRoomLists();
                            }
                        }
                    });

                    window.computeTotals && window.computeTotals();
                }

                serviceSelects.forEach(checkbox => {
                    checkbox.addEventListener('change', updateServiceCards);
                });

                // Initial render
                updateServiceCards();
                updateServiceRoomLists();
            });
            @if (config('app.debug'))
                // Debug: print booking services data on load
                console.log('DEBUG bookingServicesServer:', @json($bookingServicesServer ?? []));
                console.log('DEBUG assignedPhongIds:', @json($assignedPhongIds ?? []));
                console.log('DEBUG roomMap:', @json($roomMap ?? []));
                console.log('DEBUG booking.room_types:', @json($booking->room_types ?? []));
                console.log('DEBUG booking.phong_ids:', @json($booking->phong_ids ?? []));
            @endif
            // Per-night booking calculations
            const allLoaiPhongs = @json($loaiPhongs);
            const currentBookingId = {{ $booking->id ?? 'null' }};
            const selectedRoomsByLoaiPhong = @json($selectedRoomsByLoaiPhong ?? []);
            let roomIndex = {{ count($roomTypes) > 0 ? count($roomTypes) : 1 }};

            document.addEventListener('DOMContentLoaded', function() {
                const ngayNhanInput = document.getElementById('ngay_nhan');
                const ngayTraInput = document.getElementById('ngay_tra');

                // Only set up date listeners if inputs exist
                if (ngayNhanInput && ngayTraInput) {
                    const today = new Date().toISOString().split('T')[0];
                    ngayNhanInput.setAttribute('min', today);

                    ngayNhanInput.addEventListener('change', function() {
                        ngayTraInput.setAttribute('min', this.value);
                        if (ngayTraInput.value && ngayTraInput.value < this.value) {
                            ngayTraInput.value = this.value;
                        }
                        updateAllRoomAvailability();
                    });

                    ngayTraInput.addEventListener('change', function() {
                        updateAllRoomAvailability();
                    });

                    // Initialize prices and availability for existing rooms
                    updateAllRoomAvailability();
                }
                // derive per-night unit prices for existing rows (edit page stores totals)
                initializeRoomUnitPrices();

                // Auto-load available rooms for existing rows when page loads
                try {
                    const ngayNhanVal = document.getElementById('ngay_nhan')?.value;
                    const ngayTraVal = document.getElementById('ngay_tra')?.value;
                    if (ngayNhanVal && ngayTraVal) {
                        document.querySelectorAll('.room-item').forEach(item => {
                            const idx = item.getAttribute('data-room-index');
                            const sel = item.querySelector('.room-type-select');
                            if (sel && sel.value) {
                                // load rooms for this row
                                loadAvailableRooms(idx);
                            }
                        });
                    }
                } catch (e) {
                    console.error('Auto-load rooms error', e);
                }

                // Setup unified voucher event system
                setupVoucherEventSystem();
                updateVoucherAvailability();

                if (window.computeTotals) window.computeTotals();
            });

            function formatCurrency(amount) {
                return new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(amount).replace('₫', 'VNĐ');
            }

            function addRoom() {
                // Check if no room type is selected before adding - prevent adding empty room rows
                const selects = document.querySelectorAll('.room-type-select');
                let hasEmpty = false;
                selects.forEach(sel => {
                    if (!sel.value) hasEmpty = true;
                });
                if (hasEmpty) {
                    alert('Vui lòng chọn loại phòng cho các dòng trống trước khi thêm dòng mới');
                    return;
                }

                const container = document.getElementById('roomTypesContainer');

                // Build select options from allLoaiPhongs
                let selectOptions = '<option value="">-- Chọn loại phòng --</option>';
                allLoaiPhongs.forEach(lp => {
                    const formattedPrice = new Intl.NumberFormat('vi-VN').format(lp.gia_khuyen_mai);
                    selectOptions += `<option value="${lp.id}" data-price="${lp.gia_khuyen_mai}">${lp.ten_loai} - ${formattedPrice} VNĐ/đêm</option>`;
                });

                const newRoomHtml = `
                <div class="room-item border border-gray-200 rounded-lg p-4 bg-white" data-room-index="${roomIndex}">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h4 class="font-semibold text-gray-900">Loại phòng ${roomIndex + 1}</h4>
                            <p class="text-sm text-gray-600 quantity-text">0 phòng</p>
                        </div>
                        <button type="button" onclick="removeRoom(${roomIndex})"
                            class="text-red-600 hover:text-red-800 text-sm font-medium">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Loại phòng</label>
                            <select name="room_types[${roomIndex}][loai_phong_id]"
                                class="room-type-select w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                onchange="handleRoomTypeChange(${roomIndex}, this.value)"
                                required>
                                ${selectOptions}
                            </select>
                            <div id="availability_text_${roomIndex}" class="mt-1 text-xs text-gray-500"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Số lượng</label>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="decreaseQuantity(${roomIndex})"
                                    class="w-8 h-8 border border-gray-300 rounded-md hover:bg-gray-50 text-gray-700 font-semibold">−</button>
                                <input type="number" name="room_types[${roomIndex}][so_luong]"
                                    class="room-quantity-input quantity-input flex-1 text-center px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                    value="1" min="1" max="10"
                                    data-room-index="${roomIndex}"
                                    onchange="updateRoomQuantity(${roomIndex})" required>
                                <button type="button" onclick="increaseQuantity(${roomIndex})"
                                    class="w-8 h-8 border border-gray-300 rounded-md hover:bg-gray-50 text-gray-700 font-semibold">+</button>
                            </div>
                            <div class="mt-2 text-xs text-gray-600">
                                Giá: <span id="room_price_${roomIndex}" class="font-semibold">0 VNĐ</span>
                            </div>
                        </div>
                    </div>

                    <!-- Chọn phòng cụ thể -->
                    <div id="available_rooms_${roomIndex}" class="mt-4">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-medium text-gray-700 mb-3 block">
                                <span class="text-red-600">*</span> Chọn phòng cụ thể
                            </label>
                            <button type="button" onclick="loadAvailableRooms(${roomIndex})" class="ml-2 px-3 py-1 text-xs bg-blue-600 text-white rounded">Tải danh sách phòng</button>
                        </div>
                        <div id="room_list_${roomIndex}" class="space-y-2 pl-4 border-l-2 border-blue-300 max-h-48 overflow-y-auto bg-gray-50 p-3 rounded">
                            <p class="text-xs text-gray-500 italic">Vui lòng chọn loại phòng trước</p>
                        </div>
                        <p id="availability_rooms_text_${roomIndex}" class="mt-2 text-xs text-gray-600"></p>
                    </div>

                    <input type="hidden" name="room_types[${roomIndex}][gia_rieng]"
                        id="room_gia_rieng_${roomIndex}" value="0">
                </div>
                `;
                container.insertAdjacentHTML('beforeend', newRoomHtml);
                roomIndex++;
                updateAllRoomAvailability();
                if (window.computeTotals) window.computeTotals();
            }

            function removeRoom(index) {
                const roomItem = document.querySelector(`[data-room-index="${index}"]`);
                if (roomItem) {
                    roomItem.remove();
                    if (window.computeTotals) window.computeTotals();
                }
            }

            function handleRoomTypeChange(index, loaiPhongId) {
                // Check for duplicate room types
                if (loaiPhongId) {
                    const allSelects = document.querySelectorAll('.room-type-select');
                    let duplicateCount = 0;
                    allSelects.forEach(sel => {
                        if (sel.value === loaiPhongId) duplicateCount++;
                    });
                    if (duplicateCount > 1) {
                        alert('Loại phòng này đã được chọn. Vui lòng không chọn trùng lặp.');
                        event.target.value = '';
                        return;
                    }

                    // expose for external callers (e.g. voucher change handler)
                    try {
                        window.updateServiceCards = updateServiceCards;
                    } catch (e) {
                        // updateServiceCards not yet defined, will be available after DOMContentLoaded
                    }
                }

                const loaiPhong = allLoaiPhongs.find(lp => lp.id == loaiPhongId);
                if (loaiPhong) {
                    const priceInput = document.getElementById(`room_gia_rieng_${index}`);
                    const priceDisplay = document.getElementById(`room_price_${index}`);
                    const qtyInput = document.querySelector(`input[data-room-index="${index}"]`);
                    const quantityText = document.querySelector(`.room-item[data-room-index="${index}"] .quantity-text`);
                    const inputQty = qtyInput ? parseInt(qtyInput.value) || 1 : 1;

                    // Update quantity text display
                    if (quantityText) {
                        quantityText.textContent = `${inputQty} phòng`;
                    }

                    if (priceInput && priceDisplay) {
                        // derive unitPerNight from dataset or option price
                        let unitPerNight = parseFloat(priceInput.dataset.unitPerNight) || 0;
                        const sel = document.querySelector(`.room-item[data-room-index="${index}"] .room-type-select`);
                        if (sel) {
                            const opt = sel.options[sel.selectedIndex];
                            const optPrice = opt ? parseFloat(opt.dataset.price || 0) : 0;
                            if (optPrice && optPrice > 0) {
                                unitPerNight = optPrice;
                            }
                        }

                        console.log(`handleRoomTypeChange: index=${index}, loaiPhongId=${loaiPhongId}, unitPerNight=${unitPerNight}, nights=${getNights()}, qty=${inputQty}`);
                        console.log(`handleRoomTypeChange: index=${index}, loaiPhongId=${loaiPhongId}, unitPerNight=${unitPerNight}, nights=${getNights()}, qty=${inputQty}`);

                        const nights = getNights();
                        const subtotal = unitPerNight * nights * inputQty;
                        priceInput.dataset.unitPerNight = unitPerNight;
                        priceInput.value = subtotal;
                        priceDisplay.textContent = formatCurrency(subtotal);

                        if (window.computeTotals) window.computeTotals();
                        // Re-render service room lists so they reflect current selection
                        document.querySelectorAll('[data-service-id]').forEach(card => {
                            const sid = card.getAttribute('data-service-id');
                            if (sid) window.updateServiceRoomLists && window.updateServiceRoomLists(sid);
                        });
                        // Update voucher availability based on new room type selection
                        try {
                            updateVoucherAvailability();
                        } catch (e) {
                            // updateVoucherAvailability not yet defined
                        }
                    }
                }

                // fetch availability for this room type and update UI
                fetch('{{ route('admin.dat_phong.available_count') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            loai_phong_id: loaiPhongId,
                            ngay_nhan: document.getElementById('ngay_nhan')?.value,
                            ngay_tra: document.getElementById('ngay_tra')?.value,
                            booking_id: currentBookingId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        const availabilityText = document.getElementById(`availability_text_${index}`);
                        if (availabilityText) {
                            const availableCount = data.available_count || 0;
                            // determine requested qty for this room row
                            const rowEl = document.querySelector(`[data-room-index="${index}"]`);
                            const qtyInput = rowEl ? rowEl.querySelector('input[data-room-index]') : null;
                            const requested = qtyInput ? (parseInt(qtyInput.value) || 1) : 1;

                            let msg = '';
                            let colorClass = 'text-gray-500';
                            if (availableCount <= 0) {
                                msg = 'Hết phòng';
                                colorClass = 'text-red-600';
                            } else if (availableCount < requested) {
                                msg = `Còn ${availableCount} phòng trống (yêu cầu ${requested})`;
                                colorClass = 'text-red-600';
                            } else {
                                msg = `Còn ${availableCount} phòng trống`;
                                colorClass = 'text-green-600';
                            }

                            availabilityText.textContent = msg;
                            availabilityText.className = `mt-1 text-xs ${colorClass}`;
                        }

                        // If controller returned specific room list, render checkboxes for admin selection
                        if (data.rooms && Array.isArray(data.rooms) && data.rooms.length > 0) {
                            const roomsContainer = document.getElementById(`available_rooms_${index}`);
                            if (roomsContainer) {
                                roomsContainer.classList.remove('hidden');
                                // Find the div inside that will hold the checkboxes
                                const checkboxContainer = roomsContainer.querySelector('div.space-y-2');
                                if (checkboxContainer) {
                                    checkboxContainer.innerHTML = '';
                                    data.rooms.forEach((r, idx) => {
                                        const wrap = document.createElement('div');
                                        wrap.className = 'flex items-center gap-2';

                                        const cb = document.createElement('input');
                                        cb.type = 'checkbox';
                                        cb.name = `rooms[${loaiPhongId}][phong_ids][]`;
                                        cb.value = r.id;
                                        cb.id = `room_checkbox_${loaiPhongId}_${r.id}`;
                                        cb.className = 'available-room-checkbox';
                                        cb.setAttribute('data-room-name', r.so_phong || r.ten_phong || r.id);
                                        cb.setAttribute('data-room-id', r.id);

                                        const lbl = document.createElement('label');
                                        lbl.className = 'text-sm cursor-pointer';
                                        lbl.htmlFor = cb.id;
                                        lbl.textContent = r.so_phong ? ('Phòng ' + r.so_phong) : (r.ten_phong || ('Phòng ' + r.id));

                                        wrap.appendChild(cb);
                                        wrap.appendChild(lbl);
                                        checkboxContainer.appendChild(wrap);
                                        // attach change listener so services update when admin toggles rooms
                                        cb.addEventListener('change', syncAssignedPhongIdsFromCheckboxes);
                                    });

                                    // Pre-check boxes: prefer previously selected room IDs for this booking, fall back to first N
                                    const want = parseInt(document.querySelector(`input[data-room-index="${index}"]`)?.value || 0);
                                    const boxes = checkboxContainer.querySelectorAll('input.available-room-checkbox');
                                    if (boxes && boxes.length > 0) {
                                        // Normalize preselected ids to numbers
                                        const pre = (selectedRoomsByLoaiPhong && selectedRoomsByLoaiPhong[loaiPhongId]) ? selectedRoomsByLoaiPhong[loaiPhongId].map(x => parseInt(x)) : [];
                                        let checkedCount = 0;
                                        boxes.forEach(cb => {
                                            const id = parseInt(cb.value);
                                            if (pre && pre.includes(id)) { cb.checked = true; checkedCount++; }
                                            // attach listener to newly created boxes
                                            cb.addEventListener('change', syncAssignedPhongIdsFromCheckboxes);
                                        });

                                        // If still need more to reach want, check the first unchecked boxes
                                        for (let i = 0; i < boxes.length && checkedCount < want; i++) {
                                            if (!boxes[i].checked) { boxes[i].checked = true; checkedCount++; }
                                        }

                                        // After pre-checking, sync the assigned list so services reflect the current state
                                        syncAssignedPhongIdsFromCheckboxes();
                                    }
                                }
                            }
                        } else {
                            const roomsContainer = document.getElementById(`available_rooms_${index}`);
                            if (roomsContainer) {
                                roomsContainer.classList.add('hidden');
                                const checkboxContainer = roomsContainer.querySelector('div.space-y-2');
                                if (checkboxContainer) checkboxContainer.innerHTML = '';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }

            // Fetch availability and rooms for a room row
            function updateRoomAvailability(index, loaiPhongId) {
                if (!loaiPhongId) return;
                const ngay_nhan = document.getElementById('ngay_nhan')?.value;
                const ngay_tra = document.getElementById('ngay_tra')?.value;
                const availabilityText = document.getElementById(`availability_text_${index}`);
                if (!ngay_nhan || !ngay_tra) {
                    if (availabilityText) {
                        availabilityText.textContent = 'Vui lòng chọn ngày nhận/trả để kiểm tra phòng trống';
                        availabilityText.className = 'mt-1 text-xs text-gray-500';
                    }
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
                            ngay_nhan: ngay_nhan,
                            ngay_tra: ngay_tra,
                            booking_id: currentBookingId,
                            include_rooms: true
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (availabilityText) {
                            const availableCount = data.available_count || 0;
                            availabilityText.textContent = availableCount > 0 ? `Còn ${availableCount} phòng trống` :
                                'Hết phòng';
                            availabilityText.className =
                                `mt-1 text-xs ${availableCount > 0 ? 'text-green-600' : 'text-red-600'}`;
                        }

                        // Always show the rooms container (so admin can pick or see 'no rooms')
                        const roomsContainer = document.getElementById(`available_rooms_${index}`);
                        if (roomsContainer) {
                            roomsContainer.classList.remove('hidden');
                            const checkboxContainer = roomsContainer.querySelector('div.space-y-2');
                            if (checkboxContainer) {
                                checkboxContainer.innerHTML = '';
                                if (data.rooms && Array.isArray(data.rooms) && data.rooms.length > 0) {
                                    // Render a checkbox per room
                                    data.rooms.forEach((r) => {
                                        const wrap = document.createElement('div');
                                        wrap.className = 'flex items-center gap-2';

                                        const cb = document.createElement('input');
                                        cb.type = 'checkbox';
                                        cb.name = `rooms[${loaiPhongId}][phong_ids][]`;
                                        cb.value = r.id;
                                        cb.className = 'available-room-checkbox';
                                        cb.id = `room_checkbox_${loaiPhongId}_${r.id}`;
                                        cb.setAttribute('data-room-name', r.so_phong || r.ten_phong || r.id);
                                        cb.setAttribute('data-room-id', r.id);

                                        // ensure toggle updates assigned list and service cards
                                        cb.addEventListener('change', syncAssignedPhongIdsFromCheckboxes);

                                        const lbl = document.createElement('label');
                                        lbl.className = 'text-sm cursor-pointer';
                                        lbl.htmlFor = cb.id;
                                        lbl.textContent = r.so_phong ? ('Phòng ' + r.so_phong) : (r.ten_phong || ('Phòng ' + r.id));

                                        wrap.appendChild(cb);
                                        wrap.appendChild(lbl);
                                        checkboxContainer.appendChild(wrap);
                                    });

                                    // Pre-check previously selected rooms for this loai, else auto-fill first N
                                    const want = parseInt(document.querySelector(`input[data-room-index=\"${index}\"]`)?.value || 0);
                                    const boxes = checkboxContainer.querySelectorAll('input.available-room-checkbox');
                                    const pre = (selectedRoomsByLoaiPhong && selectedRoomsByLoaiPhong[loaiPhongId]) ? selectedRoomsByLoaiPhong[loaiPhongId].map(x => parseInt(x)) : [];
                                    let checkedCount = 0;
                                    boxes.forEach(cb => {
                                        const id = parseInt(cb.value);
                                        if (pre && pre.includes(id)) { cb.checked = true; checkedCount++; }
                                    });
                                    for (let i = 0; i < boxes.length && checkedCount < want; i++) {
                                        if (!boxes[i].checked) { boxes[i].checked = true; checkedCount++; }
                                    }
                                    // attach listeners to all boxes so future toggles update services
                                    boxes.forEach(cb => cb.addEventListener('change', syncAssignedPhongIdsFromCheckboxes));

                                    // After pre-checking, sync the assigned list so services reflect the current state
                                    syncAssignedPhongIdsFromCheckboxes();
                                } else {
                                    // No rooms available: show informative message
                                    const p = document.createElement('div');
                                    p.className = 'text-xs text-gray-500 italic';
                                    p.textContent = 'Không có phòng trống cho loại phòng này trong khoảng thời gian đã chọn.';
                                    checkboxContainer.appendChild(p);
                                }
                            }
                        }

                        // Backward-compatible assignSelect population (if present)
                        if (data.rooms && Array.isArray(data.rooms)) {
                            const assignSelect = document.getElementById('phong_id_assign');
                            if (assignSelect) {
                                const placeholder = document.createElement('option');
                                placeholder.value = '';
                                placeholder.textContent = '-- Chọn phòng --';
                                assignSelect.innerHTML = '';
                                assignSelect.appendChild(placeholder);
                                data.rooms.forEach(r => {
                                    const opt = document.createElement('option');
                                    opt.value = r.id;
                                    opt.textContent = r.so_phong + (r.ten_phong ? (' (' + r.ten_phong + ')') : '');
                                    assignSelect.appendChild(opt);
                                });
                            }
                        }
                    })
                    .catch(e => console.error('availability error', e));
            }

            // Helper to load available rooms for a row by reading the selected loai_phong
            function loadAvailableRooms(index) {
                try {
                    const row = document.querySelector(`.room-item[data-room-index="${index}"]`);
                    if (!row) return;
                    const sel = row.querySelector('.room-type-select');
                    if (!sel || !sel.value) {
                        alert('Vui lòng chọn loại phòng trước khi tải danh sách phòng.');
                        return;
                    }
                    updateRoomAvailability(index, sel.value);
                } catch (e) {
                    console.error('loadAvailableRooms error', e);
                }
            }

            function updateAllRoomAvailability() {
                const roomItems = document.querySelectorAll('.room-item');
                roomItems.forEach(item => {
                    const index = item.getAttribute('data-room-index');
                    const select = item.querySelector('.room-type-select');
                    if (select && select.value) {
                        updateRoomAvailability(index, select.value);
                    }
                });
                // Update voucher availability when room selection changes
                updateVoucherAvailability();
            }

            function increaseQuantity(index) {
                const input = document.querySelector(`input[data-room-index="${index}"]`);
                if (input) {
                    const currentValue = parseInt(input.value) || 1;
                    const maxValue = parseInt(input.getAttribute('max')) || 10;
                    const select = document.querySelector(`.room-item[data-room-index="${index}"] .room-type-select`);
                    const loaiPhongId = select ? select.value : null;

                    if (!loaiPhongId) {
                        alert('Vui lòng chọn loại phòng trước');
                        return;
                    }

                    // Get available count for this room type
                    fetch('{{ route('admin.dat_phong.available_count') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            loai_phong_id: loaiPhongId,
                            ngay_nhan: document.getElementById('ngay_nhan')?.value,
                            ngay_tra: document.getElementById('ngay_tra')?.value,
                            booking_id: currentBookingId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        const availableCount = data.available_count || 0;
                        const newValue = currentValue + 1;

                        if (newValue > availableCount) {
                            alert(`Không thể tăng số lượng! Chỉ còn ${availableCount} phòng trống.`);
                            return;
                        }

                        if (newValue <= maxValue) {
                            input.value = newValue;
                            updateRoomQuantity(index);
                            // refresh availability and room list for this row
                            if (select && select.value) updateRoomAvailability(index, select.value);
                        }
                    })
                    .catch(error => console.error('Error checking availability:', error));
                }
            }

            function decreaseQuantity(index) {
                const input = document.querySelector(`input[data-room-index="${index}"]`);
                if (input) {
                    const currentValue = parseInt(input.value) || 1;
                    if (currentValue > 1) {
                        input.value = currentValue - 1;
                        updateRoomQuantity(index);
                        const select = document.querySelector(`.room-item[data-room-index="${index}"] .room-type-select`);
                        if (select && select.value) updateRoomAvailability(index, select.value);
                    }
                }
            }

            function updateRoomQuantity(index) {
                const input = document.querySelector(`input[data-room-index="${index}"]`);
                const priceInput = document.getElementById(`room_gia_rieng_${index}`);
                const priceDisplay = document.getElementById(`room_price_${index}`);
                const quantityText = document.querySelector(`.room-item[data-room-index="${index}"] .quantity-text`);

                if (input && priceInput && priceDisplay) {
                    const quantity = parseInt(input.value) || 1;

                    // Update quantity text
                    if (quantityText) {
                        quantityText.textContent = `${quantity} phòng`;
                    }

                    const nights = getNights();
                    // use per-night unit stored on dataset (derived on load or set when room type changes)
                    const unitPerNight = parseFloat(priceInput.dataset.unitPerNight) || 0;
                    const subtotal = unitPerNight * nights * quantity;
                    // update hidden stored total for this row
                    priceInput.value = subtotal;
                    priceDisplay.textContent = formatCurrency(subtotal);
                    // recalc global totals
                    if (window.computeTotals) window.computeTotals();
                    // Khi thay đổi số lượng phòng, cập nhật lại selectedRoomsByLoaiPhong
                    // Tìm loai_phong_id
                    const roomItem = input.closest('.room-item');
                    const select = roomItem ? roomItem.querySelector('.room-type-select') : null;
                    const loaiPhongId = select ? select.value : null;
                    // Lấy danh sách phòng đang chọn cho loại phòng này
                    let phongIds = [];
                    // Nếu có checkbox phòng, lấy các phòng đang được tích
                    // (nếu có logic chọn phòng riêng)
                    // Nếu không, chỉ cập nhật số lượng (giả sử phòng sẽ được chọn tự động)
                    if (loaiPhongId) {
                        // Cập nhật số lượng và refresh availability
                        updateRoomAvailability(index, loaiPhongId);
                    }
                }
            }

            function initializeRoomUnitPrices() {
                const nights = getNights();
                // use per-night unit stored on dataset (derived on load or set when room type changes)
                const unitPerNight = parseFloat(priceInput.dataset.unitPerNight) || 0;
                const subtotal = unitPerNight * nights * quantity;
                // update hidden stored total for this row
                priceInput.value = subtotal;
                priceDisplay.textContent = formatCurrency(subtotal);
                // recalc global totals
                if (window.computeTotals) window.computeTotals();
            }
        }

        function initializeRoomUnitPrices() {
            const nights = getNights();
            document.querySelectorAll('.room-item').forEach(item => {
                const idx = item.getAttribute('data-room-index');
                const priceInput = document.getElementById(`room_gia_rieng_${idx}`);
                const qtyInput = item.querySelector('input[data-room-index]');
                const priceDisplay = document.getElementById(`room_price_${idx}`);
                const qty = qtyInput ? parseInt(qtyInput.value) || 1 : 1;
                if (priceInput) {
                    // Prefer explicit unit set on dataset or selected loai_phong option price (promotional price)
                    let unitPerNight = parseFloat(priceInput.dataset.unitPerNight) || 0;
                    const selectEl = item.querySelector('.room-type-select');
                    if (selectEl) {
                        const opt = selectEl.options[selectEl.selectedIndex];
                        const optPrice = opt ? parseFloat(opt.dataset.price || 0) : 0;
                        if (optPrice && optPrice > 0) unitPerNight = optPrice;
                    }
                    if (!unitPerNight || unitPerNight <= 0) {
                        const storedTotal = parseFloat(priceInput.value) || 0;
                        unitPerNight = (qty > 0 && nights > 0) ? (storedTotal / (qty * nights)) : 0;
                    }
                    priceInput.dataset.unitPerNight = unitPerNight;
                    const subtotal = unitPerNight * nights * qty;
                    if (priceDisplay) priceDisplay.textContent = formatCurrency(subtotal);
                    priceInput.value = subtotal;
                }
            });
        }

        function getNights() {
            const start = document.getElementById('ngay_nhan')?.value;
            const end = document.getElementById('ngay_tra')?.value;
            if (!start || !end) return 1;
            const s = new Date(start);
            const e = new Date(end);
            const diff = Math.ceil((e - s) / (1000 * 60 * 60 * 24));
            return Math.max(1, diff);
        }

        // Helpers để tính multiplier giống phía create/client
        function isHolidayJS(date) {
            const d = new Date(date.getTime());
            const year = d.getFullYear();
            const holidays = [
                new Date(year, 0, 1),   // 01/01
                new Date(year, 3, 30),  // 30/04
                new Date(year, 4, 1),   // 01/05
                new Date(year, 8, 2),   // 02/09
            ];
            return holidays.some(h => h.getDate() === d.getDate() && h.getMonth() === d.getMonth());
        }

        function getMultiplierForDateJS(date) {
            if (isHolidayJS(date)) return 1.25; // ngày lễ
            const day = date.getDay(); // 0 CN, 6 T7
            if (day === 0 || day === 6) return 1.15; // cuối tuần
            return 1.0; // ngày thường
        }

        // compute totals: rooms (per-day * multiplier * qty) + services
        window.computeTotals = function() {
            const startVal = document.getElementById('ngay_nhan')?.value;
            const endVal = document.getElementById('ngay_tra')?.value;
            const pricingInfoDiv = document.getElementById('pricing_multiplier_info');
            if (pricingInfoDiv) pricingInfoDiv.textContent = '';

            let roomTotal = 0;
            let weekdayNights = 0;
            let weekendNights = 0;
            let holidayNights = 0;

            let startDate = null;
            let endDate = null;
            if (startVal && endVal) {
                startDate = new Date(startVal);
                endDate = new Date(endVal);
            }

            // rooms với per-day multiplier
            if (startDate && endDate && !isNaN(startDate.getTime()) && !isNaN(endDate.getTime()) && endDate > startDate) {
                document.querySelectorAll('.room-item').forEach(item => {
                    const idx = item.getAttribute('data-room-index');
                    const priceInput = document.getElementById(`room_gia_rieng_${idx}`);
                    const qtyInput = item.querySelector('input[data-room-index]');
                    const qty = qtyInput ? parseInt(qtyInput.value) || 0 : 0;
                    if (!priceInput || qty <= 0) return;

                    let unitPerNight = parseFloat(priceInput.dataset.unitPerNight);
                    if (!unitPerNight || isNaN(unitPerNight) || unitPerNight <= 0) {
                        // cố gắng lấy từ option loại phòng
                        const selectEl = item.querySelector('.room-type-select');
                        if (selectEl) {
                            const opt = selectEl.options[selectEl.selectedIndex];
                            const optPrice = opt ? parseFloat(opt.dataset.price || 0) : 0;
                            if (optPrice && optPrice > 0) unitPerNight = optPrice;
                        }
                    }
                    if (!unitPerNight || isNaN(unitPerNight) || unitPerNight <= 0) return;

                    let current = new Date(startDate.getTime());
                    while (current < endDate) {
                        const multiplier = getMultiplierForDateJS(current);
                        roomTotal += unitPerNight * multiplier * qty;

                        if (isHolidayJS(current)) {
                            holidayNights += 1;
                        } else {
                            const day = current.getDay();
                            if (day === 0 || day === 6) weekendNights += 1;
                            else weekdayNights += 1;
                        }

                        current.setDate(current.getDate() + 1);
                    }
                });
            }

            // services: sum all entry hidden so_luong * service price
            let serviceTotal = 0;
            document.querySelectorAll('#selected_services_list [data-service-id]').forEach(card => {
                const sid = card.getAttribute('data-service-id');
                const option = document.querySelector(`#services_select option[value="${sid}"]`);
                const price = option ? (parseFloat(option.dataset.price) || 0) : 0;
                const qtyInputs = Array.from(document.querySelectorAll(`#service_dates_${sid} .service-date-row input[type=number]`));
                qtyInputs.forEach(qi => {
                    const q = parseInt(qi.value) || 0;
                    serviceTotal += q * price;
                });
            });

            const total = roomTotal + serviceTotal;
            const roomEl = document.getElementById('total_room_price');
            const svcEl = document.getElementById('total_service_price');
            const totalEl = document.getElementById('total_price');
            const hidden = document.getElementById('tong_tien_input');
            if (roomEl) roomEl.textContent = formatCurrency(roomTotal);
            if (svcEl) svcEl.textContent = formatCurrency(serviceTotal);
            if (totalEl) totalEl.textContent = formatCurrency(total);
            if (hidden) hidden.value = total;

            if (pricingInfoDiv && (weekdayNights + weekendNights + holidayNights) > 0) {
                const parts = [];
                if (weekdayNights > 0) parts.push(weekdayNights + ' đêm ngày thường (x1.0)');
                if (weekendNights > 0) parts.push(weekendNights + ' đêm cuối tuần (x1.15)');
                if (holidayNights > 0) parts.push(holidayNights + ' đêm ngày lễ (x1.25)');
                pricingInfoDiv.textContent = 'Chi tiết: ' + parts.join(' · ');
            }
        }

        function confirmBooking() {
            if (confirm('Bạn có chắc chắn muốn xác nhận đặt phòng này không?')) {
                const form = document.getElementById('bookingForm');
                const statusInput = document.getElementById('trang_thai_input');
                if (form && statusInput) {
                    statusInput.value = 'da_xac_nhan';
                    form.submit();
                } else {
                    alert('Có lỗi xảy ra. Vui lòng thử lại!');
                }
            }
        }

        function completeBooking() {
            if (confirm('Bạn có chắc chắn muốn xác nhận trả phòng này không?')) {
                const form = document.getElementById('bookingForm');
                const statusInput = document.getElementById('trang_thai_input');
                if (form && statusInput) {
                    statusInput.value = 'da_tra';
                    form.submit();
                } else {
                    alert('Có lỗi xảy ra. Vui lòng thử lại!');
                }
            }
        }
        // --- Services Tom Select (init and rendering) ---
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
                    const ts = new TomSelect(selectEl, {plugins:['remove_button'], persist:false, create:false,});
                    // If booking has existing services, pre-select them
                    try {
                        const initialServiceIds = Object.keys(bookingServicesServer || {});
                        if (initialServiceIds && initialServiceIds.length) {
                            ts.setValue(initialServiceIds);
                        }
                        if (!unitPerNight || unitPerNight <= 0) {
                            const storedTotal = parseFloat(priceInput.value) || 0;
                            unitPerNight = (qty > 0 && nights > 0) ? (storedTotal / (qty * nights)) : 0;
                        }
                        priceInput.dataset.unitPerNight = unitPerNight;
                        const subtotal = unitPerNight * nights * qty;
                        if (priceDisplay) priceDisplay.textContent = formatCurrency(subtotal);
                        priceInput.value = subtotal;
                    }
                });
            }

            function getNights() {
                const start = document.getElementById('ngay_nhan')?.value;
                const end = document.getElementById('ngay_tra')?.value;
                if (!start || !end) return 1;
                const s = new Date(start);
                const e = new Date(end);
                const diff = Math.ceil((e - s) / (1000 * 60 * 60 * 24));
                const result = Math.max(1, diff);
                return result;
            }

            // ========================================
            // VOUCHER SYSTEM - UNIFIED EVENT HANDLER
            // ========================================

            function updateVoucherAvailability() {
                const selectedRoomTypes = Array.from(document.querySelectorAll('.room-type-select')).map(s => s.value).filter(v => v);
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
                    const voucherMirror = document.getElementById('voucher_input');
                    const voucherIdInput = document.getElementById('voucher_id_input');
                    if (voucherMirror) voucherMirror.value = '';
                    if (voucherIdInput) voucherIdInput.value = '';
                    return;
                }

                // show voucher containers when dates are set
                document.querySelectorAll('.voucher-container').forEach(container => container.style.display = '');

                // Disable all vouchers first
                voucherInputs.forEach(v => v.disabled = true);

                // Enable/disable vouchers based on room type compatibility AND date range
                voucherInputs.forEach(radio => {
                    const voucherRoomType = radio.dataset.loaiPhong;
                    const vStart = radio.dataset.start || '';
                    const vEnd = radio.dataset.end || '';
                    const container = radio.closest('.voucher-container');

                    // Date check: check-in date must be within [start, end] inclusive
                    let dateOk = true;
                    if (checkinDate && vStart && vEnd) {
                        const checkin = new Date(checkinDate + 'T00:00:00Z');
                        const start = new Date(vStart + 'T00:00:00Z');
                        const end = new Date(vEnd + 'T00:00:00Z');
                        if (checkin < start || checkin > end) dateOk = false;
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
                        if (container) container.style.display = '';
                    }

                    // If voucher has no loai_phong_id restriction or matches selected room type
                    const roomOk = (!voucherRoomType || voucherRoomType === 'null' || voucherRoomType === '') || (selectedRoomTypes.length === 0) || selectedRoomTypes.includes(voucherRoomType);

                    // Enable/disable based on room type only (no visible message)
                    radio.disabled = !roomOk;

                    if (container) {
                        const label = container.querySelector('.voucher-label');
                        if (label) label.classList.remove('opacity-50', 'cursor-not-allowed');
                        const overlays = container.querySelectorAll('.voucher-overlay-client, .voucher-overlay-server');
                        overlays.forEach(o => o.remove());
                    }
                });
            }

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
                    document.querySelectorAll('#services_details_container [data-service-id]').forEach(card => {
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
                document.querySelectorAll('.room-item').forEach(item => {
                    const idx = item.getAttribute('data-room-index');
                    const select = item.querySelector('.room-type-select');
                    const qtyInput = item.querySelector('input[data-room-index]');

                    if (!select || !select.value) return;

                    const roomTypeId = select.value;
                    const quantity = parseInt(qtyInput?.value || 1);

                    // Get price from price input (already calculated as total for this row)
                    const priceInput = document.getElementById(`room_gia_rieng_${idx}`);
                    let price = 0;

                    if (priceInput) {
                        // priceInput stores total price (already multiplied by nights * qty)
                        price = parseFloat(priceInput.value || 0);
                        // But we need per-night price for recalculation, stored in dataset
                        const unitPerNight = parseFloat(priceInput.dataset.unitPerNight || 0);
                        if (unitPerNight > 0) {
                            price = unitPerNight * nights * quantity;
                        }
                    }

                    if (price > 0 && quantity > 0) {
                        roomTotalByType[roomTypeId] = price;
                        roomTotal += price;
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
                    if (!voucherLoaiPhongId || voucherLoaiPhongId === 'null' || voucherLoaiPhongId === '') {
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
                    document.querySelectorAll('.room-item').forEach(item => {
                        const idx = item.getAttribute('data-room-index');
                        const qtyInput = item.querySelector('input[data-room-index]');
                        total += parseInt(qtyInput?.value || 0) || 0;
                    });
                    if (total === 0) {
                        total = Array.isArray(assignedPhongIds) ? assignedPhongIds.length : 0;
                    }
                    return total;
                }

                document.querySelectorAll('#services_details_container .service-card-custom').forEach(card => {
                    const serviceId = card.getAttribute('data-service-id');
                    const priceElem = card.querySelector('.service-price');
                    let price = 0;
                    if (priceElem) {
                        const priceText = priceElem.textContent || '';
                        const match = priceText.match(/[\d,.]+/);
                        if (match) {
                            price = parseFloat(match[0].replace(/[,.]/g, '')) || 0;
                        }
                    }

                    const mode = card.querySelector(`input[name="service_room_mode_${serviceId}"]:checked`)
                        ?.value || 'global';
                    const rows = Array.from(card.querySelectorAll(`.service-date-row`));

                    rows.forEach(r => {
                        const qty = parseInt(r.querySelector('input[type=number]')?.value || 1) || 0;
                        if (qty <= 0) return;

                        if (mode === 'global') {
                            const roomsCount = getTotalBookedRooms();
                            totalServicePrice += price * qty * roomsCount;
                            console.log('service', serviceId, 'global mode, qty=', qty, 'rooms=',
                                roomsCount, 'add=', price * qty * roomsCount);
                        } else {
                            const entryRooms = r.querySelectorAll('.entry-room-checkbox:checked').length ||
                                0;
                            totalServicePrice += price * qty * entryRooms;
                            console.log('service', serviceId, 'specific mode, qty=', qty, 'checked rooms=',
                                entryRooms, 'add=', price * qty * entryRooms);
                        }
                    });
                });

                // 4. Cộng tất cả: (phòng - discount) + dịch vụ
                const finalTotal = roomNetTotal + totalServicePrice;

                // Update UI
                const totalPriceElement = document.getElementById('total_price');
                const tongTienInput = document.getElementById('tong_tien_input');
                const roomPriceElement = document.getElementById('total_room_price');
                const discountAmountElement = document.getElementById('discount_amount');
                const roomAfterElement = document.getElementById('room_after_discount');
                const servicePriceElement = document.getElementById('total_service_price');

                if (roomPriceElement) roomPriceElement.textContent = formatCurrency(roomTotal);
                if (discountAmountElement) discountAmountElement.textContent = formatCurrency(discount);
                if (roomAfterElement) roomAfterElement.textContent = formatCurrency(roomNetTotal);
                if (servicePriceElement) servicePriceElement.textContent = formatCurrency(totalServicePrice);
                totalPriceElement.textContent = formatCurrency(finalTotal);
                tongTienInput.value = finalTotal;
            };

            // Window alias for compatibility
            window.computeTotals = updateTotalPrice;
            window.updateServiceRoomLists = updateServiceRoomLists;

            // Note: Confirm/Cancel actions use dedicated routes (form/link) like the index page.

            function completeBooking() {
                if (confirm('Bạn có chắc chắn muốn xác nhận trả phòng này không?')) {
                    const form = document.getElementById('bookingForm');
                    const statusInput = document.getElementById('trang_thai_input');
                    if (form && statusInput) {
                        statusInput.value = 'da_tra';
                        form.submit();
                    } else {
                        alert('Có lỗi xảy ra. Vui lòng thử lại!');
                    }
                }
            }
            // Services are fully server-rendered as of this refactor.
            // Per-service checkboxes and date rows are rendered by Blade templates with proper form names.
            // Form submission automatically captures all service_data[serviceId][entries][idx][...] inputs.
            // Minimal client-side behavior: only date validation and room mode radio toggle effects.

            // Enhanced diagnostic: intercept form submit, capture the request, log the response
            document.addEventListener('DOMContentLoaded', function() {
                try {
                    const form = document.getElementById('bookingForm');
                    const submitBtn = form ? form.querySelector('button[type="submit"]') : null;

                    if (form && submitBtn) {
                        // Override form submission to intercept with fetch
                        form.addEventListener('submit', function(ev) {
                            console.log('DEBUG [submit event]: form submit triggered for bookingForm');

                            // Create a temporary message div
                            const statusMsg = document.createElement('div');
                            statusMsg.style.position = 'fixed';
                            statusMsg.style.top = '20px';
                            statusMsg.style.right = '20px';
                            statusMsg.style.zIndex = '9999';
                            statusMsg.style.padding = '12px 16px';
                            statusMsg.style.backgroundColor = '#fbbf24';
                            statusMsg.style.color = '#1f2937';
                            statusMsg.style.borderRadius = '6px';
                            statusMsg.style.fontSize = '14px';
                            statusMsg.style.fontWeight = 'bold';
                            statusMsg.textContent = 'Đang gửi yêu cầu...';
                            document.body.appendChild(statusMsg);

                            // Prevent default form submission
                            ev.preventDefault();

                            // Collect form data
                            const formData = new FormData(form);
                            const action = form.getAttribute('action');
                            const method = form.getAttribute('method') || 'POST';

                            console.log('DEBUG [form data]: ', {
                                action: action,
                                method: method,
                                formDataEntries: Array.from(formData.entries()).slice(0,
                                    5) // log first 5 entries
                            });

                            // Send via fetch
                            fetch(action, {
                                    method: method,
                                    body: formData,
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                })
                                .then(async response => {
                                    console.log('DEBUG [fetch response]:', {
                                        status: response.status,
                                        statusText: response.statusText,
                                        headers: Object.fromEntries(response.headers.entries())
                                    });

                                    const text = await response.text();
                                    console.log('DEBUG [fetch response body (first 500 chars)]:', text
                                        .substring(0, 500));

                                    statusMsg.textContent =
                                        `Response: ${response.status} ${response.statusText}`;
                                    statusMsg.style.backgroundColor = response.ok ? '#86efac' :
                                        '#f87171';

                                    // If successful, redirect after a short delay
                                    if (response.ok) {
                                        setTimeout(() => {
                                            window.location.href = response.url || window
                                                .location.href;
                                        }, 1500);
                                    } else {
                                        // If error, show the response for inspection
                                        console.error('DEBUG [error response body]:', text);
                                        setTimeout(() => statusMsg.remove(), 5000);
                                    }
                                })
                                .catch(err => {
                                    console.error('DEBUG [fetch error]:', err);
                                    statusMsg.textContent = `Error: ${err.message}`;
                                    statusMsg.style.backgroundColor = '#f87171';
                                    setTimeout(() => statusMsg.remove(), 5000);
                                });
                        }, false);

                        // Also log click on submit button
                        submitBtn.addEventListener('click', function(ev) {
                            console.log('DEBUG [submit click]:', {
                                disabled: submitBtn.disabled,
                                eventType: ev.type
                            });
                        }, false);
                    }
                } catch (e) {
                    console.error('DEBUG [setup error]:', e);
                }
            });

            // Function to submit confirm form (separate from bookingForm)
            window.submitConfirmForm = function(event) {
                event.preventDefault();

                // Show confirmation dialog
                if (!confirm('Xác nhận đặt phòng #{{ $booking->id }}?')) {
                    return false;
                }

                // If user confirms, submit form using standard form.submit()
                const form = document.getElementById('confirmForm');
                if (form) {
                    form.submit();
                }
            };

            // Client-side validation: ensure admin selected exact rooms per room-type before submit
            function validateBookingForm(event) {
                // Remove previous inline errors
                document.querySelectorAll('.room-error').forEach(el => el.remove());

                const roomItems = document.querySelectorAll('.room-item');
                let valid = true;
                let firstInvalid = null;

                roomItems.forEach(item => {
                    const idx = item.getAttribute('data-room-index');
                    const qtyInput = item.querySelector('input[data-room-index]');
                    const required = qtyInput ? (parseInt(qtyInput.value) || 1) : 1;

                    const checkboxContainer = item.querySelector(`#room_list_${idx}`) || item.querySelector('.space-y-2');
                    const checkboxes = item.querySelectorAll('input.available-room-checkbox');

                    if (!checkboxes || checkboxes.length === 0) {
                        // If there are no checkboxes rendered, require admin to load/select rooms
                        valid = false;
                        const err = document.createElement('div');
                        err.className = 'room-error text-sm text-red-600 mt-2';
                        err.textContent = `Vui lòng tải danh sách phòng và chọn ${required} phòng cho loại này.`;
                        (checkboxContainer || item).appendChild(err);
                        if (!firstInvalid) firstInvalid = item;
                        return;
                    }

                    const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
                    if (checkedCount !== required) {
                        valid = false;
                        const err = document.createElement('div');
                        err.className = 'room-error text-sm text-red-600 mt-2';
                        err.textContent = `Vui lòng chọn đúng ${required} phòng (đã chọn ${checkedCount}).`;
                        (checkboxContainer || item).appendChild(err);
                        if (!firstInvalid) firstInvalid = item;
                    }
                });

                if (!valid) {
                    event.preventDefault();
                    if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return false;
                }
                return true;
            }

            // Attach validator to booking form submit
            try {
                const bookingForm = document.getElementById('bookingForm');
                if (bookingForm) bookingForm.addEventListener('submit', validateBookingForm, { capture: true });
            } catch (e) {
                console.error('Failed to attach booking form validator', e);
            }
        </script>
    @endpush
@endsection
