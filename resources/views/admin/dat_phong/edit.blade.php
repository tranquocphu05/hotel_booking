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
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Loại phòng</label>
                                                        <select name="room_types[{{ $index }}][loai_phong_id]" 
                                                            class="room-type-select mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                            onchange="handleRoomTypeChange({{ $index }}, this.value)"
                                                            required>
                                                            <option value="">-- Chọn loại phòng --</option>
                                                            @foreach($loaiPhongs as $lp)
                                                                <option value="{{ $lp->id }}" 
                                                                    {{ $roomType['loai_phong_id'] == $lp->id ? 'selected' : '' }}
                                                                    data-price="{{ $lp->gia_co_ban }}">
                                                                    {{ $lp->ten_loai }} - {{ number_format($lp->gia_co_ban, 0, ',', '.') }} VNĐ/đêm
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
                                                        <div class="mt-1 text-xs text-gray-600">
                                                            Giá: <span id="room_price_{{ $index }}" class="font-medium">{{ number_format($roomType['gia_rieng'] ?? 0, 0, ',', '.') }} VNĐ</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="room_types[{{ $index }}][gia_rieng]" 
                                                    id="room_gia_rieng_{{ $index }}" 
                                                    value="{{ $roomType['gia_rieng'] ?? 0 }}">
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
                                                                data-price="{{ $lp->gia_co_ban }}">
                                                                {{ $lp->ten_loai }} - {{ number_format($lp->gia_co_ban, 0, ',', '.') }} VNĐ/đêm
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
                                            <input type="hidden" name="room_types[0][gia_rieng]" 
                                                id="room_gia_rieng_0" 
                                                value="{{ $booking->loaiPhong->gia_co_ban ?? 0 }}">
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
        const allLoaiPhongs = @json($loaiPhongs);
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
                                    <option value="{{ $lp->id }}" data-price="{{ $lp->gia_co_ban }}">
                                        {{ $lp->ten_loai }} - {{ number_format($lp->gia_co_ban, 0, ',', '.') }} VNĐ/đêm
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
        }

        function removeRoom(index) {
            const roomItem = document.querySelector(`[data-room-index="${index}"]`);
            if (roomItem) {
                roomItem.remove();
            }
        }

        function handleRoomTypeChange(index, loaiPhongId) {
            const loaiPhong = allLoaiPhongs.find(lp => lp.id == loaiPhongId);
            if (loaiPhong) {
                const priceInput = document.getElementById(`room_gia_rieng_${index}`);
                const priceDisplay = document.getElementById(`room_price_${index}`);
                if (priceInput && priceDisplay) {
                    priceInput.value = loaiPhong.gia_co_ban;
                    priceDisplay.textContent = formatCurrency(loaiPhong.gia_co_ban);
                }
                updateRoomAvailability(index, loaiPhongId);
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
                    ngay_tra: ngayTra
                })
            })
            .then(response => response.json())
            .then(data => {
                if (availabilityText) {
                    const availableCount = data.available_count || 0;
                    availabilityText.textContent = `Còn ${availableCount} phòng trống`;
                    availabilityText.className = `mt-1 text-xs ${availableCount > 0 ? 'text-green-600' : 'text-red-600'}`;
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
                const price = parseFloat(priceInput.value) || 0;
                const subtotal = quantity * price;
                priceDisplay.textContent = formatCurrency(subtotal);
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
    </script>
    @endpush
@endsection
