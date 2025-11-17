@extends('layouts.admin')

@section('title', 'Sửa đặt phòng')

@section('admin_content')
    <div class="py-6"> 
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    @php
                        $roomTypes = $booking->getRoomTypes();
                    @endphp
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                        Sửa thông tin đặt phòng 
                        @if(count($roomTypes) > 1)
                            <b>{{ count($roomTypes) }} loại phòng</b>
                        @else
                            <b>{{ $booking->loaiPhong->ten_loai ?? 'N/A' }}</b>
                        @endif
                    </h2>

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
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

                    <form id="bookingForm" action="{{ route('admin.dat_phong.update', $booking->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <!-- Thông tin phòng -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Thông tin phòng</h3>
                                
                                <div id="roomTypesContainer" class="space-y-4">
                                    @if(count($roomTypes) > 0)
                                        @foreach($roomTypes as $index => $roomType)
                                            @php
                                                $loaiPhong = \App\Models\LoaiPhong::find($roomType['loai_phong_id']);
                                            @endphp
                                            <div class="room-item border border-gray-200 rounded-lg p-4 bg-white" data-room-index="{{ $index }}">
                                                <div class="flex justify-between items-start mb-3">
                                                    <h4 class="text-sm font-medium text-gray-700">Loại phòng {{ $index + 1 }}</h4>
                                                    @if(count($roomTypes) > 1)
                                                        <button type="button" onclick="removeRoom({{ $index }})" 
                                                            class="text-red-600 hover:text-red-800 text-sm">
                                                            <i class="fas fa-times"></i> Xóa
                                                        </button>
                                                    @endif
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        
                                                        <select name="room_types[{{ $index }}][loai_phong_id]" 
                                                            class="room-type-select mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                            onchange="handleRoomTypeChange({{ $index }}, this.value)"
                                                            required>
                                                            <option value="">-- Chọn loại phòng --</option>
                                                            @foreach($loaiPhongs as $lp)
                                                                <option value="{{ $lp->id }}" 
                                                                    {{ $roomType['loai_phong_id'] == $lp->id ? 'selected' : '' }}
                                                                    data-price="{{ $lp->gia_khuyen_mai }}">
                                                                    {{ $lp->ten_loai }} - {{ number_format($lp->gia_khuyen_mai, 0, ',', '.') }} VNĐ/đêm
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div id="availability_text_{{ $index }}" class="mt-1 text-xs text-gray-500"></div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Số lượng</label>
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" onclick="decreaseQuantity({{ $index }})" 
                                                                class="px-3 py-1 border border-gray-300 rounded-md hover:bg-gray-50">-</button>
                                                            <input type="number" 
                                                                name="room_types[{{ $index }}][so_luong]" 
                                                                class="room-quantity-input quantity-input w-20 text-center border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                                value="{{ $roomType['so_luong'] ?? 1 }}" 
                                                                min="1"
                                                                max="10"
                                                                data-room-index="{{ $index }}"
                                                                onchange="updateRoomQuantity({{ $index }})"
                                                                required>
                                                            <button type="button" onclick="increaseQuantity({{ $index }})" 
                                                                class="px-3 py-1 border border-gray-300 rounded-md hover:bg-gray-50">+</button>
                                                        </div>
                                                        @php
                                                            $soLuongVal = $roomType['so_luong'] ?? 1;
                                                            $nightsForCalc = 1;
                                                            if($booking && $booking->ngay_nhan && $booking->ngay_tra) {
                                                                $nightsForCalc = \Carbon\Carbon::parse($booking->ngay_nhan)->diffInDays(\Carbon\Carbon::parse($booking->ngay_tra));
                                                                $nightsForCalc = max(1, $nightsForCalc);
                                                            }
                                                            $unitPerNight = $loaiPhong ? ($loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban ?? 0) : 0;
                                                            $subtotal = $unitPerNight * $nightsForCalc * $soLuongVal;
                                                        @endphp
                                                        <div class="mt-1 text-xs text-gray-600">
                                                            Giá: <span id="room_price_{{ $index }}" class="font-medium">{{ number_format($subtotal, 0, ',', '.') }} VNĐ</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="room_types[{{ $index }}][gia_rieng]" 
                                                    id="room_gia_rieng_{{ $index }}" 
                                                    data-unit-per-night="{{ $unitPerNight }}"
                                                    value="{{ $subtotal }}">
                                            </div>
                                        @endforeach
                                    @else
                                        {{-- Fallback cho booking cũ không có room_types --}}
                                        <div class="room-item border border-gray-200 rounded-lg p-4 bg-white" data-room-index="0">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Loại phòng</label>
                                                    <select name="room_types[0][loai_phong_id]" 
                                                        class="room-type-select mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                        onchange="handleRoomTypeChange(0, this.value)"
                                                        required>
                                                        <option value="">-- Chọn loại phòng --</option>
                                                        @foreach($loaiPhongs as $lp)
                                                            <option value="{{ $lp->id }}" 
                                                                {{ $booking->loai_phong_id == $lp->id ? 'selected' : '' }}
                                                                data-price="{{ $lp->gia_khuyen_mai }}">
                                                                {{ $lp->ten_loai }} - {{ number_format($lp->gia_khuyen_mai, 0, ',', '.') }} VNĐ/đêm
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div id="availability_text_0" class="mt-1 text-xs text-gray-500"></div>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Số lượng</label>
                                                    <div class="flex items-center gap-2">
                                                        <button type="button" onclick="decreaseQuantity(0)" 
                                                            class="px-3 py-1 border border-gray-300 rounded-md hover:bg-gray-50">-</button>
                                                        <input type="number" 
                                                            name="room_types[0][so_luong]" 
                                                            class="room-quantity-input quantity-input w-20 text-center border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                            value="{{ $booking->so_luong_da_dat ?? 1 }}" 
                                                            min="1"
                                                            max="10"
                                                            data-room-index="0"
                                                            onchange="updateRoomQuantity(0)"
                                                            required>
                                                        <button type="button" onclick="increaseQuantity(0)" 
                                                            class="px-3 py-1 border border-gray-300 rounded-md hover:bg-gray-50">+</button>
                                                    </div>
                                                    <div class="mt-1 text-xs text-gray-600">
                                                        Giá: <span id="room_price_0" class="font-medium">0 VNĐ</span>
                                                    </div>
                                                </div>
                                            </div>
                                            @php
                                                $fallbackUnit = $booking->loaiPhong ? ($booking->loaiPhong->gia_khuyen_mai ?? $booking->loaiPhong->gia_co_ban ?? 0) : 0;
                                                $fallbackQty = $booking->so_luong_da_dat ?? 1;
                                                $fallbackNights = 1;
                                                if($booking && $booking->ngay_nhan && $booking->ngay_tra) {
                                                    $fallbackNights = \Carbon\Carbon::parse($booking->ngay_nhan)->diffInDays(\Carbon\Carbon::parse($booking->ngay_tra));
                                                    $fallbackNights = max(1, $fallbackNights);
                                                }
                                                $fallbackSubtotal = $fallbackUnit * $fallbackNights * $fallbackQty;
                                            @endphp
                                            <input type="hidden" name="room_types[0][gia_rieng]" 
                                                id="room_gia_rieng_0" data-unit-per-night="{{ $fallbackUnit }}"
                                                value="{{ $fallbackSubtotal }}">
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-4">
                                    <button type="button" onclick="addRoom()" 
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        <i class="fas fa-plus mr-2"></i> Thêm loại phòng khác
                                    </button>
                                </div>

                                <!-- Phòng được gán -->
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Phòng được gán</label>
                                    
                                    @php
                                        $assignedPhongs = $booking->getAssignedPhongs();
                                        $assignedCount = $assignedPhongs->count();
                                        $totalRooms = array_sum(array_column($roomTypes, 'so_luong')) ?: ($booking->so_luong_da_dat ?? 0);
                                        $remainingCount = $totalRooms - $assignedCount;
                                    @endphp

                                    @if($assignedCount > 0)
                                        <div class="mt-2 mb-3">
                                            <p class="text-sm font-medium text-gray-700 mb-2">
                                                Phòng đã gán ({{ $assignedCount }}/{{ $totalRooms }}):
                                            </p>
                                            <div class="space-y-2">
                                                @foreach($assignedPhongs as $phong)
                                                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-md">
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
                                        <div class="mt-1 p-3 bg-blue-50 border border-blue-200 rounded-md mb-3">
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
                                        <p class="text-sm text-yellow-600 mb-2">
                                            <i class="fas fa-exclamation-triangle text-xs mr-1"></i>
                                            Chưa gán phòng cụ thể
                                        </p>
                                    @endif

                                    @if($remainingCount > 0 && isset($availableRooms) && $availableRooms->count() > 0)
                                        <div class="mt-3 pt-3 border-t border-gray-200">
                                            <label for="phong_id_assign" class="block text-xs text-gray-600 mb-1">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Còn thiếu {{ $remainingCount }} phòng. Chọn phòng để gán:
                                            </label>
                                            <form action="{{ route('admin.dat_phong.assign_room', $booking->id) }}" method="POST" class="mt-2">
                                                @csrf
                                                @method('PUT')
                                                <select name="phong_id" id="phong_id_assign" 
                                                    class="mt-1 w-full text-sm border-gray-300 rounded-md">
                                                    <option value="">-- Chọn phòng --</option>
                                                    @foreach($availableRooms as $room)
                                                        <option value="{{ $room->id }}">
                                                            {{ $room->so_phong }} 
                                                            @if($room->tang) (Tầng {{ $room->tang }}) @endif
                                                            @if($room->co_view_dep) - View đẹp @endif
                                                            @if($room->gia_rieng) - Giá riêng: {{ number_format($room->gia_rieng, 0, ',', '.') }} VNĐ @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" 
                                                    class="mt-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    Gán phòng
                                                </button>
                                            </form>
                                        </div>
                                    @elseif($remainingCount > 0)
                                        <p class="text-xs text-gray-500 mt-2">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Còn thiếu {{ $remainingCount }} phòng. Không có phòng trống trong khoảng thời gian từ {{ date('d/m/Y', strtotime($booking->ngay_nhan)) }} đến {{ date('d/m/Y', strtotime($booking->ngay_tra)) }}
                                        </p>
                                    @endif
                                    @error('phong_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mt-4">
                                    <div class="flex items-center gap-4">
                                        <label class="text-sm font-medium text-gray-700">Trạng thái hiện tại:</label>
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold
                                            @if ($booking->trang_thai === 'da_xac_nhan') bg-green-100 text-green-800
                                            @elseif($booking->trang_thai === 'cho_xac_nhan') bg-yellow-100 text-yellow-800
                                            @elseif($booking->trang_thai === 'da_huy') bg-red-100 text-red-800
                                            @else bg-blue-100 text-blue-800 @endif">
                                            {{ $booking->trang_thai === 'cho_xac_nhan' ? 'Chờ xác nhận' : 
                                               ($booking->trang_thai === 'da_xac_nhan' ? 'Đã xác nhận' : 
                                               ($booking->trang_thai === 'da_huy' ? 'Đã hủy' : 'Đã trả phòng')) }}
                                        </span>
                                        
                                        @if($booking->trang_thai === 'cho_xac_nhan')
                                            <button type="button" onclick="confirmBooking()"
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Xác nhận đặt phòng
                                            </button>
                                            <a href="{{ route('admin.dat_phong.cancel', $booking->id) }}"
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Hủy đặt phòng
                                            </a>
                                        @elseif($booking->trang_thai === 'da_xac_nhan')
                                            <button type="button" onclick="completeBooking()"
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                Xác nhận trả phòng
                                            </button>
                                        @endif
                                    </div>
                                    <input type="hidden" name="trang_thai" id="trang_thai_input" value="{{ $booking->trang_thai }}">
                                    @error('trang_thai')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Thông tin đặt phòng -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Thông tin đặt phòng</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label for="so_nguoi" class="block text-sm font-medium text-gray-700">Số người</label>
                                        <input type="number" name="so_nguoi" id="so_nguoi" 
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            value="{{ old('so_nguoi', $booking->so_nguoi) }}" required>
                                        @error('so_nguoi')
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
                                    <div class="text-sm font-medium text-right text-gray-900" id="total_service_price">0 VNĐ</div>

                                    <div class="text-sm text-gray-700">Tổng giá phòng (đã nhân số đêm)</div>
                                    <div class="text-sm font-medium text-right text-gray-900" id="total_room_price">0 VNĐ</div>

                                    <div class="text-sm text-gray-700">Tổng thanh toán</div>
                                    <div class="text-lg font-semibold text-right text-blue-600" id="total_price">0 VNĐ</div>
                                </div>
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
                                            <label for="sdt" class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                                            <input type="text" name="sdt" id="sdt" 
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                value="{{ old('sdt', $booking->sdt) }}" required>
                                            @error('sdt')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="cccd" class="block text-sm font-medium text-gray-700">CCCD/CMND</label>
                                            <input type="text" name="cccd" id="cccd" 
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                value="{{ old('cccd', $booking->cccd) }}" 
                                                placeholder="{{ $booking->cccd ? '' : 'Chưa cập nhật CCCD' }}"
                                                required>
                                            @if(!$booking->cccd)
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

                            @if($booking->voucher_id)
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Thông tin voucher</h3>
                                    <p class="text-sm text-gray-600">Mã voucher: <span class="font-medium">{{ $booking->voucher->ma_voucher }}</span></p>
                                    <p class="text-sm text-gray-600">Giảm giá: <span class="font-medium">{{ $booking->voucher->giam_gia }}%</span></p>
                                </div>
                            @endif

                            <div class="pt-5">
                                <div class="flex justify-between">
                                    <a href="{{ route('admin.dat_phong.index') }}" 
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                        </svg>
                                        Quay lại
                                    </a>
                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Cập nhật
                                    </button>
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
        // Existing booking services passed from server (grouped by service_id)
        const bookingServicesServer = {!! json_encode($bookingServices->map(function($b) use($booking) { return ['service_id' => $b->service_id, 'quantity' => $b->quantity, 'used_at' => $b->used_at ? date('Y-m-d', strtotime($b->used_at)) : date('Y-m-d', strtotime($booking->ngay_nhan))]; })->groupBy('service_id')->map(function($group){ return $group->map(function($item){ return ['ngay'=>$item['used_at'],'so_luong'=>$item['quantity']]; })->values(); })->toArray()) !!};
    const allLoaiPhongs = @json($loaiPhongs);
    const currentBookingId = {{ $booking->id ?? 'null' }};
    let roomIndex = {{ count($roomTypes) > 0 ? count($roomTypes) : 1 }};

        document.addEventListener('DOMContentLoaded', function() {
            const ngayNhanInput = document.getElementById('ngay_nhan');
            const ngayTraInput = document.getElementById('ngay_tra');

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
            // derive per-night unit prices for existing rows (edit page stores totals)
            initializeRoomUnitPrices();
            if (window.computeTotals) window.computeTotals();
        });

        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(amount).replace('₫', 'VNĐ');
        }

        function addRoom() {
            const container = document.getElementById('roomTypesContainer');
            const newRoomHtml = `
                <div class="room-item border border-gray-200 rounded-lg p-4 bg-white" data-room-index="${roomIndex}">
                    <div class="flex justify-between items-start mb-3">
                        <h4 class="text-sm font-medium text-gray-700">Loại phòng ${roomIndex + 1}</h4>
                        <button type="button" onclick="removeRoom(${roomIndex})" 
                            class="text-red-600 hover:text-red-800 text-sm">
                            <i class="fas fa-times"></i> Xóa
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Loại phòng</label>
                            <select name="room_types[${roomIndex}][loai_phong_id]" 
                                class="room-type-select mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                onchange="handleRoomTypeChange(${roomIndex}, this.value)"
                                required>
                                <option value="">-- Chọn loại phòng --</option>
                                @foreach($loaiPhongs as $lp)
                                    <option value="{{ $lp->id }}" data-price="{{ $lp->gia_khuyen_mai }}">
                                        {{ $lp->ten_loai }} - {{ number_format($lp->gia_khuyen_mai, 0, ',', '.') }} VNĐ/đêm
                                    </option>
                                @endforeach
                            </select>
                            <div id="availability_text_${roomIndex}" class="mt-1 text-xs text-gray-500"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Số lượng</label>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="decreaseQuantity(${roomIndex})" 
                                    class="px-3 py-1 border border-gray-300 rounded-md hover:bg-gray-50">-</button>
                                <input type="number" 
                                    name="room_types[${roomIndex}][so_luong]" 
                                    class="room-quantity-input quantity-input w-20 text-center border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    value="1" 
                                    min="1"
                                    max="10"
                                    data-room-index="${roomIndex}"
                                    onchange="updateRoomQuantity(${roomIndex})"
                                    required>
                                <button type="button" onclick="increaseQuantity(${roomIndex})" 
                                    class="px-3 py-1 border border-gray-300 rounded-md hover:bg-gray-50">+</button>
                            </div>
                            <div class="mt-1 text-xs text-gray-600">
                                Giá: <span id="room_price_${roomIndex}" class="font-medium">0 VNĐ</span>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="room_types[${roomIndex}][gia_rieng]" 
                        id="room_gia_rieng_${roomIndex}" 
                        value="0">
                </div>
            `;
            container.insertAdjacentHTML('beforeend', newRoomHtml);
            roomIndex++;
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
            const loaiPhong = allLoaiPhongs.find(lp => lp.id == loaiPhongId);
            if (loaiPhong) {
                const priceInput = document.getElementById(`room_gia_rieng_${index}`);
                const priceDisplay = document.getElementById(`room_price_${index}`);
                if (priceInput && priceDisplay) {
                    // set per-night unit price (prefer promotional price if available)
                    const unitPerNight = loaiPhong.gia_khuyen_mai ?? loaiPhong.gia_co_ban ?? 0;
                    priceInput.dataset.unitPerNight = unitPerNight;
                    const nights = getNights();
                    const qtyInput = document.querySelector(`input[data-room-index="${index}"]`);
                    const qty = qtyInput ? parseInt(qtyInput.value) || 1 : 1;
                    const subtotal = unitPerNight * nights * qty;
                    priceInput.value = subtotal;
                    priceDisplay.textContent = formatCurrency(subtotal);
                }
                updateRoomAvailability(index, loaiPhongId);
                if (window.computeTotals) window.computeTotals();
            }
        }

        function updateRoomAvailability(index, loaiPhongId) {
            const ngayNhan = document.getElementById('ngay_nhan').value;
            const ngayTra = document.getElementById('ngay_tra').value;
            const availabilityText = document.getElementById(`availability_text_${index}`);

            if (!ngayNhan || !ngayTra || !loaiPhongId) {
                if (availabilityText) availabilityText.textContent = '';
                return;
            }

            fetch('{{ route("admin.dat_phong.available_count") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    loai_phong_id: loaiPhongId,
                    ngay_nhan: ngayNhan,
                    ngay_tra: ngayTra,
                    booking_id: currentBookingId
                })
            })
            .then(response => response.json())
            .then(data => {
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
            })
            .catch(error => {
                console.error('Error:', error);
            });
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
        }

        function increaseQuantity(index) {
            const input = document.querySelector(`input[data-room-index="${index}"]`);
            if (input) {
                const currentValue = parseInt(input.value) || 1;
                const maxValue = parseInt(input.getAttribute('max')) || 10;
                if (currentValue < maxValue) {
                    input.value = currentValue + 1;
                    updateRoomQuantity(index);
                }
            }
        }

        function decreaseQuantity(index) {
            const input = document.querySelector(`input[data-room-index="${index}"]`);
            if (input) {
                const currentValue = parseInt(input.value) || 1;
                if (currentValue > 1) {
                    input.value = currentValue - 1;
                    updateRoomQuantity(index);
                }
            }
        }

        function updateRoomQuantity(index) {
            const input = document.querySelector(`input[data-room-index="${index}"]`);
            const priceInput = document.getElementById(`room_gia_rieng_${index}`);
            const priceDisplay = document.getElementById(`room_price_${index}`);
            
            if (input && priceInput && priceDisplay) {
                const quantity = parseInt(input.value) || 1;
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

        // compute totals: rooms (per-night * nights * qty) + services
        window.computeTotals = function() {
            // rooms
            const nights = getNights();
            let roomTotal = 0;
            document.querySelectorAll('.room-item').forEach(item => {
                const idx = item.getAttribute('data-room-index');
                const priceInput = document.getElementById(`room_gia_rieng_${idx}`);
                const qtyInput = item.querySelector('input[data-room-index]');
                const qty = qtyInput ? parseInt(qtyInput.value) || 0 : 0;
                let unitPerNight = 0;
                if (priceInput) {
                    // prefer explicit dataset unitPerNight (set on load or when changing type)
                    unitPerNight = parseFloat(priceInput.dataset.unitPerNight);
                    if (!unitPerNight || isNaN(unitPerNight) || unitPerNight <= 0) {
                        // fallback: if value looks like a stored subtotal, derive unit per night
                        const stored = parseFloat(priceInput.value) || 0;
                        unitPerNight = (qty > 0 && nights > 0) ? (stored / (qty * nights)) : 0;
                        // persist derived unit for future calculations
                        priceInput.dataset.unitPerNight = unitPerNight;
                    }
                }
                roomTotal += unitPerNight * qty * nights;
            });

            // services: sum all entry hidden so_luong * service price
            let serviceTotal = 0;
            // find all service cards
            document.querySelectorAll('#selected_services_list [data-service-id]').forEach(card => {
                const sid = card.getAttribute('data-service-id');
                const option = document.querySelector(`#services_select option[value="${sid}"]`);
                const price = option ? (parseFloat(option.dataset.price) || 0) : 0;
                // sum quantities for this service
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
                    } catch(e) { console.warn('Error preselecting services', e); }

                    function getRangeDates() {
                        const start = document.getElementById('ngay_nhan')?.value;
                        const end = document.getElementById('ngay_tra')?.value;
                        if (!start || !end) return [];
                        const a = [];
                        const s = new Date(start);
                        const e = new Date(end);
                        for (let d = new Date(s); d <= e; d.setDate(d.getDate()+1)) a.push(new Date(d).toISOString().split('T')[0]);
                        return a;
                    }

                    function renderSelectedServices(values) {
                        const container = document.getElementById('selected_services_list');
                        container.innerHTML = '';
                        const range = getRangeDates();
                        (values||[]).forEach(val=>{
                            const option = selectEl.querySelector('option[value="'+val+'"]'); if(!option) return;
                            const id = val;
                            const serviceName = option.textContent?.split(' - ')[0] || option.innerText;
                            const price = parseFloat(option.dataset.price||0)||0;
                            const unit = option.dataset.unit || 'cái';

                            const card = document.createElement('div'); card.className='service-card-custom'; card.setAttribute('data-service-id', id);
                            const header = document.createElement('div'); header.className='service-card-header';
                            const titleDiv = document.createElement('div');
                            titleDiv.className='service-title';
                            titleDiv.textContent = serviceName;
                            const priceDiv = document.createElement('div');
                            priceDiv.className='service-price';
                            priceDiv.textContent = `${new Intl.NumberFormat('vi-VN').format(price)}/${unit}`;
                            header.appendChild(titleDiv);
                            header.appendChild(priceDiv);
                            card.appendChild(header);

                            const rows = document.createElement('div'); rows.id = 'service_dates_'+id;
                            function buildRow(dv, qty){ 
                                const r=document.createElement('div'); r.className='service-date-row'; 
                                const di=document.createElement('input'); di.type='date'; di.value=dv||''; 
                                const rg=getRangeDates(); if(rg.length){ di.min=rg[0]; di.max=rg[rg.length-1]; } 
                                // store previous on focus
                                di.addEventListener('focus', function(){ this.dataset.prev = this.value || ''; });
                                // prevent duplicate dates for same service
                                di.addEventListener('change', function(){
                                    const val = this.value || '';
                                    if (!val) { syncHidden(id); return; }
                                    const others = Array.from(document.querySelectorAll('#service_dates_'+id+' input[type=date]')).filter(i=>i!==this).map(i=>i.value);
                                    if (others.includes(val)){
                                        this.value = this.dataset.prev || '';
                                        alert('Ngày này đã được chọn cho dịch vụ này. Vui lòng chọn ngày khác.');
                                        return;
                                    }
                                    syncHidden(id);
                                });
                                const qi=document.createElement('input'); qi.type='number'; qi.min=1; qi.value=(qty && qty>0)?qty:1; qi.className='w-24'; 
                                const rem=document.createElement('button'); rem.type='button'; rem.className='service-remove-btn ml-2'; rem.textContent='Xóa'; rem.onclick=()=>{ r.remove(); syncHidden(id); };
                                qi.onchange = ()=>syncHidden(id);
                                r.appendChild(di); r.appendChild(qi); r.appendChild(rem); return r; 
                            }

                            // If server has existing entries for this service, render them
                            const existing = bookingServicesServer && bookingServicesServer[id] ? bookingServicesServer[id] : null;
                            if (existing && existing.length) {
                                existing.forEach(e => {
                                    rows.appendChild(buildRow(e.ngay || (range.length? range[0] : ''), e.so_luong || 1));
                                });
                            } else {
                                // at least one row
                                const initialDate = (range.length? range[0] : '');
                                rows.appendChild(buildRow(initialDate, 1));
                            }

                            const addBtn = document.createElement('button'); addBtn.type='button'; addBtn.className='service-add-day mt-2'; addBtn.textContent='Thêm ngày'; addBtn.onclick=function(){ const used=Array.from(rows.querySelectorAll('input[type=date]')).map(i=>i.value); const avail=getRangeDates().find(d=>!used.includes(d)); if(avail) { rows.appendChild(buildRow(avail)); syncHidden(id); } };

                            card.appendChild(rows); card.appendChild(addBtn);

                            // hidden inputs
                            const cb = document.createElement('input'); cb.type='checkbox'; cb.name='services[]'; cb.value=id; cb.className='service-checkbox'; cb.style.display='none'; cb.checked=true;
                            const sum = document.createElement('input'); sum.type='hidden'; sum.name='services_data['+id+'][so_luong]'; sum.id='service_quantity_hidden_'+id; sum.value='1';
                            const dv = document.createElement('input'); dv.type='hidden'; dv.name='services_data['+id+'][dich_vu_id]'; dv.value=id;

                            container.appendChild(card); container.appendChild(cb); container.appendChild(sum); container.appendChild(dv);

                            function syncHidden(id){ const containerEl = document.getElementById('selected_services_list'); Array.from(containerEl.querySelectorAll('input.entry-hidden[data-service="'+id+'"]')).forEach(n=>n.remove()); const rowsNow = Array.from(document.querySelectorAll('#service_dates_'+id+' .service-date-row')); if(rowsNow.length===0){ try{ ts.removeItem(id); }catch(e){ const el=document.querySelector('[data-service-id="'+id+'"]'); if(el) el.remove(); } return; } let total=0; rowsNow.forEach((r,idx)=>{ const dateVal = r.querySelector('input[type=date]')?.value||''; const qty = parseInt(r.querySelector('input[type=number]')?.value||1); total += qty; const h1=document.createElement('input'); h1.type='hidden'; h1.name='services_data['+id+'][entries]['+idx+'][ngay]'; h1.value=dateVal; h1.className='entry-hidden'; h1.setAttribute('data-service', id); const h2=document.createElement('input'); h2.type='hidden'; h2.name='services_data['+id+'][entries]['+idx+'][so_luong]'; h2.value=qty; h2.className='entry-hidden'; h2.setAttribute('data-service', id); container.appendChild(h1); container.appendChild(h2); }); const sumEl = document.getElementById('service_quantity_hidden_'+id); if(sumEl) sumEl.value = total; if(window.computeTotals) window.computeTotals(); }

                            // Ensure hidden inputs match rendered rows
                            syncHidden(id);

                        });
                    }

                    ts.on('change', function(values){ renderSelectedServices(values || []); if(window.computeTotals) window.computeTotals(); });

                    // render initial
                    renderSelectedServices(ts.getValue() || []);
                    if(window.computeTotals) window.computeTotals();

                    // normalize on date change
                    const ngayNhanEl = document.getElementById('ngay_nhan');
                    const ngayTraEl = document.getElementById('ngay_tra');
                    if(ngayNhanEl && ngayTraEl){ ngayNhanEl.addEventListener('change', ()=>{ renderSelectedServices(ts.getValue()||[]); }); ngayTraEl.addEventListener('change', ()=>{ renderSelectedServices(ts.getValue()||[]); }); }
                } catch(e){ console.error('Services init error', e); }
            });
        });
    </script>
    @endpush
@endsection
