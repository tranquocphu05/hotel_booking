@extends('layouts.admin')

@section('title', 'Đặt phòng mới')

@section('admin_content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Đặt phòng mới</h2>

                    <form action="{{ route('admin.dat_phong.store') }}" method="POST">
                        @csrf
                        <div class="space-y-6">
                            <!-- Chọn phòng -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Chọn phòng</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    @foreach($rooms as $room)
                                        <div class="relative">
                                            <input type="radio" name="phong_id" id="room_{{ $room->id }}" 
                                                value="{{ $room->id }}" class="hidden peer" required
                                                data-loai-phong-id="{{ $room->loai_phong_id }}">
                                            <label for="room_{{ $room->id }}" 
                                                class="block p-4 bg-white border rounded-lg cursor-pointer 
                                                    peer-checked:border-blue-500 peer-checked:ring-2 peer-checked:ring-blue-500
                                                    hover:bg-gray-50">
                                                <div class="space-y-2">
                                                    <img src="{{ asset('storage/' . $room->img) }}" 
                                                        alt="{{ $room->ten_phong }}"
                                                        class="w-full h-40 object-cover rounded-lg mb-2">
                                                    <h4 class="font-semibold text-gray-900">{{ $room->ten_phong }}</h4>
                                                    <p class="text-sm text-gray-600" data-loai-phong-id="{{ $room->loai_phong_id }}">{{ $room->loaiPhong->ten_loai }}</p>
                                                    <p class="text-sm font-medium text-blue-600">
                                                        {{ number_format($room->gia, 0, ',', '.') }} VNĐ/đêm
                                                    </p>
                                                    <div class="flex items-center space-x-2 text-sm">
                                                        <span class="px-2 py-1 rounded-full text-xs
                                                            @if($room->trang_thai === 'trong') bg-green-100 text-green-800
                                                            @elseif($room->trang_thai === 'da_dat') bg-red-100 text-red-800
                                                            @else bg-yellow-100 text-yellow-800 @endif">
                                                            {{ $room->trang_thai === 'trong' ? 'Còn trống' : 
                                                               ($room->trang_thai === 'da_dat' ? 'Đã đặt' : 'Bảo trì') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('phong_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Thông tin đặt phòng -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Thông tin đặt phòng</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="ngay_nhan" class="block text-sm font-medium text-gray-700">Ngày nhận phòng</label>
                                        <input type="date" name="ngay_nhan" id="ngay_nhan" 
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            required>
                                        @error('ngay_nhan')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="ngay_tra" class="block text-sm font-medium text-gray-700">Ngày trả phòng</label>
                                        <input type="date" name="ngay_tra" id="ngay_tra" 
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            required>
                                        @error('ngay_tra')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="so_nguoi" class="block text-sm font-medium text-gray-700">Số người</label>
                                        <input type="number" name="so_nguoi" id="so_nguoi" 
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            min="1" required>
                                        @error('so_nguoi')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Chọn mã giảm giá (nếu có)</label>
                                        <div class="grid grid-cols-2 gap-2 mb-2">
                                            @foreach($vouchers as $voucher)
                                                <div class="relative">
                                                    <input type="radio" name="voucher" id="voucher_{{ $voucher->id }}" 
                                                        value="{{ $voucher->ma_voucher }}" class="hidden peer voucher-radio"
                                                        data-value="{{ $voucher->gia_tri }}"
                                                        data-loai-phong="{{ $voucher->loai_phong_id }}"
                                                        disabled>
                                                    <label for="voucher_{{ $voucher->id }}" 
                                                        class="block p-3 bg-white border rounded-lg cursor-pointer relative
                                                            peer-checked:border-blue-500 peer-checked:ring-1 peer-checked:ring-blue-500
                                                            hover:bg-gray-50">
                                                        <div class="absolute inset-0 bg-gray-200 bg-opacity-50 flex items-center justify-center transition-opacity"
                                                             :class="{ 'opacity-100': document.getElementById('voucher_{{ $voucher->id }}').disabled, 'opacity-0': !document.getElementById('voucher_{{ $voucher->id }}').disabled }">
                                                            <span class="text-gray-500 text-sm font-medium">Không áp dụng</span>
                                                        </div>>
                                                        <div class="space-y-1">
                                                            <p class="text-sm font-medium text-blue-600">{{ $voucher->ma_voucher }}</p>
                                                            <p class="text-xs text-gray-600">
                                                                @if($voucher->gia_tri <= 100)
                                                                    Giảm {{ $voucher->gia_tri }}%
                                                                @else
                                                                    Giảm {{ number_format($voucher->gia_tri, 0, ',', '.') }} VNĐ
                                                                @endif
                                                            </p>
                                                            @if($voucher->dieu_kien)
                                                                <p class="text-xs text-gray-500">{{ $voucher->dieu_kien }}</p>
                                                            @endif
                                                            <p class="text-xs text-gray-500">Còn lại: {{ $voucher->so_luong }}</p>
                                                            <p class="text-xs text-gray-500">HSD: {{ date('d/m/Y', strtotime($voucher->ngay_het_han)) }}</p>
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
                                        <label for="username" class="block text-sm font-medium text-gray-700">Họ và tên</label>
                                        <input type="text" name="username" id="username" 
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            required>
                                        @error('username')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" name="email" id="email" 
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            required>
                                        @error('email')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="sdt" class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                                        <input type="text" name="sdt" id="sdt" 
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            required>
                                        @error('sdt')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="cccd" class="block text-sm font-medium text-gray-700">CCCD/CMND</label>
                                        <input type="text" name="cccd" id="cccd" 
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            required>
                                        @error('cccd')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
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
        document.addEventListener('DOMContentLoaded', function() {
            const ngayNhanInput = document.getElementById('ngay_nhan');
            const ngayTraInput = document.getElementById('ngay_tra');
            const roomInputs = document.querySelectorAll('input[name="phong_id"]');
            const voucherInputs = document.querySelectorAll('.voucher-radio');
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
                calculateTotal();
            });

            ngayTraInput.addEventListener('change', calculateTotal);

            roomInputs.forEach(input => {
                input.addEventListener('change', function() {
                    // Disable all vouchers first
                    voucherInputs.forEach(v => {
                        v.disabled = true;
                        v.checked = false; // Uncheck all vouchers
                    });

                    if (this.checked) {
                        // Get the room type directly from the radio input
                        const roomTypeId = this.dataset.loaiPhongId;
                        
                        // Enable only vouchers that match this room type or have no room type (general vouchers)
                        voucherInputs.forEach(v => {
                            const voucherRoomType = v.dataset.loaiPhong;
                            const voucherLabel = document.querySelector(`label[for="${v.id}"]`);
                            if (!voucherRoomType || voucherRoomType === roomTypeId) {
                                v.disabled = false;
                                voucherLabel.classList.remove('opacity-30');
                                voucherLabel.querySelector('.absolute').classList.add('hidden');
                            } else {
                                v.disabled = true;
                                voucherLabel.classList.add('opacity-30');
                                voucherLabel.querySelector('.absolute').classList.remove('hidden');
                            }
                        });
                    }
                    
                    calculateTotal();
                });
            });

            voucherInputs.forEach(input => {
                input.addEventListener('change', calculateTotal);
            });

            function calculateTotal() {
                const selectedRoom = document.querySelector('input[name="phong_id"]:checked');
                if (!selectedRoom || !ngayNhanInput.value || !ngayTraInput.value) {
                    totalPriceElement.textContent = formatCurrency(0);
                    discountInfoElement.classList.add('hidden');
                    return;
                }

                // Get the label element that contains the price
                const roomLabel = selectedRoom.parentElement.querySelector('label');
                if (!roomLabel) {
                    console.error('Room label not found');
                    return;
                }

                // Find the price element within the label
                const priceElement = roomLabel.querySelector('.text-blue-600');
                if (!priceElement) {
                    console.error('Price element not found');
                    return;
                }
                const roomPrice = parseFloat(priceElement.textContent.replace(/[^0-9]/g, ''));
                if (isNaN(roomPrice)) {
                    console.error('Invalid room price:', priceText);
                    return;
                }
                
                const startDate = new Date(ngayNhanInput.value);
                const endDate = new Date(ngayTraInput.value);
                const days = Math.max(1, Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)));

                const originalTotal = roomPrice * days;
                let finalTotal = originalTotal;

                const selectedVoucher = document.querySelector('.voucher-radio:checked');
                if (selectedVoucher) {
                    const voucherLabel = document.querySelector(`label[for="${selectedVoucher.id}"]`);
                    if (!voucherLabel) {
                        console.error('Voucher label not found');
                        return;
                    }

                    // Lấy giá trị giảm giá từ thuộc tính data
                    const discountValue = parseFloat(selectedVoucher.dataset.value);
                    if (isNaN(discountValue)) {
                        console.error('Invalid discount value');
                        return;
                    }

                    let discountAmount;
                    if (discountValue <= 100) {
                        // Giảm theo phần trăm
                        discountAmount = (originalTotal * discountValue) / 100;
                    } else {
                        // Giảm trực tiếp số tiền
                        discountAmount = discountValue;
                    }
                    
                    finalTotal = originalTotal - discountAmount;

                    // Hiển thị thông tin giảm giá
                    originalPriceElement.textContent = formatCurrency(originalTotal);
                    discountAmountElement.textContent = '-' + formatCurrency(discountAmount);
                    discountInfoElement.classList.remove('hidden');
                } else {
                    discountInfoElement.classList.add('hidden');
                }

                totalPriceElement.textContent = formatCurrency(finalTotal);
                document.getElementById('tong_tien_input').value = finalTotal;
            }

            function formatCurrency(amount) {
                return new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(amount).replace('₫', 'VNĐ');
            }

            // Tính toán ban đầu
            calculateTotal();
        });
    </script>
    @endpush
@endsection