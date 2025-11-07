@extends('layouts.admin')

@section('title', 'Đặt phòng mới')

@section('admin_content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Đặt phòng mới</h2>

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

                    <form action="{{ route('admin.dat_phong.store') }}" method="POST" id="bookingForm">
                        @csrf
                        <div class="space-y-6">
                            <!-- Chọn loại phòng -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Chọn loại phòng</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6" id="roomTypesContainer">
                                    @foreach($loaiPhongs as $loaiPhong)
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
                                                    <p class="text-xs text-gray-600 line-clamp-2">{{ $loaiPhong->mo_ta ?? '' }}</p>
                                                    <div class="flex items-center justify-between">
                                                        @if($loaiPhong->gia_khuyen_mai)
                                                            <div>
                                                                <p class="text-sm font-medium text-red-600">
                                                                    {{ number_format($loaiPhong->gia_khuyen_mai, 0, ',', '.') }} VNĐ/đêm
                                                                </p>
                                                                <p class="text-xs text-gray-500 line-through">
                                                                    {{ number_format($loaiPhong->gia_co_ban, 0, ',', '.') }} VNĐ
                                                                </p>
                                                            </div>
                                                        @else
                                                            <p class="text-sm font-medium text-blue-600">
                                                                {{ number_format($loaiPhong->gia_co_ban, 0, ',', '.') }} VNĐ/đêm
                                                            </p>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center space-x-2 text-sm">
                                                        <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">
                                                            {{ $loaiPhong->trang_thai === 'hoat_dong' ? 'Khả dụng' : 'Không khả dụng' }}
                                                        </span>
                                                        <span class="text-xs text-gray-600 availability-text" id="availability_text_{{ $loaiPhong->id }}" data-loai-phong-id="{{ $loaiPhong->id }}">
                                                            Còn {{ $loaiPhong->so_luong_trong }} phòng
                                                        </span>
                                                    </div>
                                                </div>
                                            </label>
                                            {{-- Hidden inputs luôn được submit --}}
                                            <input type="hidden" name="rooms[{{ $loaiPhong->id }}][so_luong]" id="quantity_hidden_{{ $loaiPhong->id }}" value="0">
                                            <input type="hidden" name="rooms[{{ $loaiPhong->id }}][loai_phong_id]" value="{{ $loaiPhong->id }}">
                                            
                                            {{-- Số lượng phòng với design mới (ẩn mặc định) --}}
                                            <div class="room-quantity-container mt-3 hidden" id="quantity_container_{{ $loaiPhong->id }}">
                                                <div class="flex items-center justify-between bg-white border border-gray-300 rounded-lg p-2 shadow-sm">
                                                    <label for="quantity_{{ $loaiPhong->id }}" class="text-sm font-medium text-gray-700 mr-3 whitespace-nowrap">
                                                        Số lượng:
                                                    </label>
                                                    <div class="flex items-center space-x-2 flex-1">
                                                        <button type="button"
                                                            class="quantity-btn-decrease w-8 h-8 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md transition-colors font-bold text-lg"
                                                            onclick="decreaseQuantity({{ $loaiPhong->id }})"
                                                            tabindex="-1">
                                                            −
                                                        </button>
                                                        <input type="text"
                                                            id="quantity_{{ $loaiPhong->id }}"
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
                                                        / <span id="max_available_{{ $loaiPhong->id }}" data-max="{{ $loaiPhong->so_luong_trong }}">{{ $loaiPhong->so_luong_trong }}</span> phòng
                                                    </span>
                                                </div>
                                                <p class="text-xs text-red-600 mt-1 hidden" id="quantity_error_{{ $loaiPhong->id }}">
                                                    Số lượng không được vượt quá <span id="max_available_error_{{ $loaiPhong->id }}">{{ $loaiPhong->so_luong_trong }}</span> phòng
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('room_types')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <div id="room_types_error" class="mt-2 text-sm text-red-600 hidden">
                                    Vui lòng chọn ít nhất một loại phòng
                                </div>
                            </div>

                            <!-- Thông tin đặt phòng -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Thông tin đặt phòng</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="ngay_nhan" class="block text-sm font-medium text-gray-700">Ngày nhận phòng</label>
                                        <input type="date" name="ngay_nhan" id="ngay_nhan"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            >
                                        @error('ngay_nhan')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="ngay_tra" class="block text-sm font-medium text-gray-700">Ngày trả phòng</label>
                                        <input type="date" name="ngay_tra" id="ngay_tra"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            >
                                        @error('ngay_tra')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="so_nguoi" class="block text-sm font-medium text-gray-700">Số người</label>
                                        <input type="text" name="so_nguoi" id="so_nguoi" value="{{ old('so_nguoi') }}"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            >
                                        @error('so_nguoi')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-3">Chọn mã giảm giá (nếu có)</label>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                                            @foreach($vouchers as $voucher)
                                                @php
                                                    $isDisabled = $voucher->status !== 'con_han';
                                                    $overlayText = $voucher->status === 'het_han' ? 'Hết hạn' : ($voucher->status === 'huy' ? 'Đã hủy' : '');
                                                @endphp

                                                <div class="relative">
                                                    <input type="radio" name="voucher" id="voucher_{{ $voucher->id }}"
                                                        value="{{ $voucher->ma_voucher }}"
                                                        class="sr-only peer voucher-radio"
                                                        data-value="{{ $voucher->gia_tri }}"
                                                        data-loai-phong="{{ $voucher->loai_phong_id }}"
                                                        {{ $isDisabled ? 'disabled' : '' }}>

                                                    <label for="voucher_{{ $voucher->id }}"
                                        class="block p-4 bg-gray-50 border-2 border-gray-200 rounded-xl cursor-pointer relative transition-all duration-300 ease-in-out
                                            peer-checked:bg-white peer-checked:border-green-500 peer-checked:ring-2 peer-checked:ring-green-400 peer-checked:shadow-lg
                                            hover:bg-gray-100 hover:border-gray-300 hover:shadow-md
                                            disabled:opacity-50 disabled:cursor-not-allowed z-0 peer-checked:z-10">



                    {{-- Overlay hiển thị trạng thái --}}
                    @if($isDisabled)
                        <div class="absolute inset-0 bg-opacity-70 flex items-center justify-center rounded-xl">
                            <span class="text-gray-700 text-sm font-medium">{{ $overlayText }}</span>
                        </div>
                    @endif

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-bold text-green-600">{{ $voucher->ma_voucher }}</p>
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                @if($voucher->gia_tri <= 100)
                                    {{ $voucher->gia_tri }}%
                                @else
                                    {{ number_format($voucher->gia_tri, 0, ',', '.') }}₫
                                @endif
                            </span>
                        </div>
                        <p class="text-xs text-gray-600">
                            @if($voucher->gia_tri <= 100)
                                Giảm {{ $voucher->gia_tri }}%
                            @else
                                Giảm {{ number_format($voucher->gia_tri, 0, ',', '.') }} VNĐ
                            @endif
                        </p>
                        @if($voucher->dieu_kien)
                            <p class="text-xs text-gray-500 bg-gray-100 p-2 rounded">{{ $voucher->dieu_kien }}</p>
                        @endif
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>Còn lại: {{ $voucher->so_luong }}</span>
                            <span>HSD: {{ date('d/m/Y', strtotime($voucher->ngay_ket_thuc)) }}</span>
                        </div>
                    </div>
                </label>
            </div>
        @endforeach
    </div>
</div>


                                    <input type="hidden" name="tong_tien" id="tong_tien_input" value="0">
                                    <!-- Hiển thị tổng tiền -->
                                    <div class="col-span-2">
                                        <div class="bg-gray-100 p-4 rounded-lg">
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-700">Tổng tiền:</span>
                                                <span id="total_price" class="text-lg font-semibold text-blue-600">0 VNĐ</span>
                                            </div>
                                            <div id="discount_info" class="text-sm text-gray-600 mt-1 hidden">
                                                <div class="flex justify-between">
                                                    <span>Giá gốc:</span>
                                                    <span id="original_price">0 VNĐ</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Giảm giá:</span>
                                                    <span id="discount_amount" class="text-green-600">-0 VNĐ</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Thông tin người đặt -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Thông tin người đặt</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Họ và tên</label>
                                        <input type="text" name="username" id="username"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            value="{{ old('username') }}"
                                            required>
                                        @error('username')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                        <input type="text" name="email" id="email"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            value="{{ old('email') }}"
                                            required>
                                        @error('email')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="sdt" class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại</label>
                                        <input type="text" name="sdt" id="sdt"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            value="{{ old('sdt') }}"
                                            required>
                                        @error('sdt')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="cccd" class="block text-sm font-medium text-gray-700 mb-2">CCCD/CMND</label>
                                        <input type="text" name="cccd" id="cccd"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            value="{{ old('cccd') }}"
                                            required>
                                        @error('cccd')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Thông báo lỗi validation -->
                                <div id="validation-errors" class="hidden mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
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
                            </div>

                            <div class="pt-5">
                                <div class="flex justify-end space-x-3">
                                    <a href="{{ route('admin.dat_phong.index') }}"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Hủy bỏ
                                    </a>
                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                        Đặt phòng
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
            } else {
                quantityContainer.classList.add('hidden');
                quantityInput.required = false;
                quantityInput.value = 1;
                // Đặt giá trị hidden về 0 khi bỏ chọn
                const hiddenInput = document.getElementById('quantity_hidden_' + roomTypeId);
                if (hiddenInput) {
                    hiddenInput.value = 0;
                }
            }

            updateVoucherAvailability();
            updateTotalPrice();
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

            if (value > maxAvailable && maxAvailable > 0) {
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

            updateTotalPrice();
        }

        function decreaseQuantity(roomTypeId) {
            const input = document.getElementById('quantity_' + roomTypeId);
            const currentValue = parseInt(input.value) || 1;
            if (currentValue > 1) {
                input.value = currentValue - 1;
                updateQuantityHidden(roomTypeId);
                validateQuantity(input, roomTypeId);
            }
        }

        function increaseQuantity(roomTypeId) {
            const input = document.getElementById('quantity_' + roomTypeId);
            const maxAvailable = getMaxAvailable(roomTypeId);
            const currentValue = parseInt(input.value) || 1;
            if (currentValue < maxAvailable) {
                input.value = currentValue + 1;
                updateQuantityHidden(roomTypeId);
                validateQuantity(input, roomTypeId);
            }
        }

        // Function to update availability for a single room type
        function updateRoomAvailability(loaiPhongId) {
            const checkin = document.getElementById('ngay_nhan').value;
            const checkout = document.getElementById('ngay_tra').value;

            if (!checkin || !checkout) {
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
                    checkin: checkin,
                    checkout: checkout
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
                    updateVoucherAvailability();
                });
            });

            // Voucher change listener
            document.querySelectorAll('.voucher-radio').forEach(v => {
                v.addEventListener('change', updateTotalPrice);
            });

            // Tính toán ban đầu
            updateTotalPrice();
            updateVoucherAvailability();
        });

        function updateVoucherAvailability() {
            const selectedRoomTypes = Array.from(document.querySelectorAll('.room-type-checkbox:checked')).map(cb => cb.value);
            const voucherInputs = document.querySelectorAll('.voucher-radio');

            // Disable all vouchers first
            voucherInputs.forEach(v => {
                v.disabled = true;
                v.checked = false;
            });

            if (selectedRoomTypes.length === 0) {
                // No rooms selected, keep all vouchers disabled
                return;
            }

            // Enable vouchers that match any selected room type or have no room type restriction
            voucherInputs.forEach(v => {
                const voucherRoomType = v.dataset.loaiPhong;
                const overlay = document.getElementById(`overlay_${v.id.split('_')[1]}`);

                // If voucher has no room type restriction or matches any selected room type
                if (!voucherRoomType || selectedRoomTypes.includes(voucherRoomType)) {
                    v.disabled = false;
                    if (overlay) overlay.classList.add('hidden');
                } else {
                    v.disabled = true;
                    if (overlay) overlay.classList.remove('hidden');
                }
            });
        }



        function updateTotalPrice() {
            const ngayNhan = document.getElementById('ngay_nhan').value;
            const ngayTra = document.getElementById('ngay_tra').value;

            if (!ngayNhan || !ngayTra) {
                document.getElementById('total_price').textContent = formatCurrency(0);
                return;
            }

            const startDate = new Date(ngayNhan);
            const endDate = new Date(ngayTra);
            const nights = Math.max(1, Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)));

            let totalPrice = 0;
            document.querySelectorAll('.room-type-checkbox:checked').forEach(checkbox => {
                const roomTypeId = checkbox.value;
                const quantityInput = document.getElementById('quantity_' + roomTypeId);
                const quantity = parseInt(quantityInput?.value || 1);
                const price = parseFloat(checkbox.dataset.price || 0);

                if (price > 0 && quantity > 0) {
                    totalPrice += price * nights * quantity;
                }
            });

            // Apply voucher discount if any
            const selectedVoucher = document.querySelector('.voucher-radio:checked');
            let finalTotal = totalPrice;

            if (selectedVoucher) {
                const discountValue = parseFloat(selectedVoucher.dataset.value || 0);
                let discountAmount = 0;

                if (discountValue <= 100) {
                    discountAmount = (totalPrice * discountValue) / 100;
                } else {
                    discountAmount = discountValue;
                }

                finalTotal = totalPrice - discountAmount;
                document.getElementById('original_price').textContent = formatCurrency(totalPrice);
                document.getElementById('discount_amount').textContent = '-' + formatCurrency(discountAmount);
                document.getElementById('discount_info').classList.remove('hidden');
            } else {
                document.getElementById('discount_info').classList.add('hidden');
            }

            document.getElementById('total_price').textContent = formatCurrency(finalTotal);
            document.getElementById('tong_tien_input').value = finalTotal;
        }

        // Form validation is handled by PHP Laravel - no client-side validation needed
        // All validation errors will be displayed by Laravel's error directives

        // Initial update
        updateVoucherAvailability();
    </script>
    @endpush
@endsection
