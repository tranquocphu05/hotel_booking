@extends('layouts.client')

@section('title', $loaiPhong->ten_loai ?? 'Đặt phòng')

@section('client_content')
    @php
        use Carbon\Carbon;

        // Lấy ngày từ old() hoặc biến truyền vào, chuyển thành Carbon
        $ngay_nhan = old('ngay_nhan', isset($checkin) ? $checkin : now()->format('Y-m-d'));
        $ngay_tra = old('ngay_tra', isset($checkout) ? $checkout : now()->addDay()->format('Y-m-d'));

        // Đảm bảo $ngay_nhan và $ngay_tra là đối tượng Carbon
        try {
            $ngay_nhan_carbon = Carbon::parse($ngay_nhan);
            $ngay_tra_carbon = Carbon::parse($ngay_tra);
        } catch (\Exception $e) {
            $ngay_nhan_carbon = now();
            $ngay_tra_carbon = now()->addDay();
        }

        
        // Tính số đêm (chỉ tính > 0, mặc định là 1 nếu ngày trả <= ngày nhận)
        $so_dem = $ngay_tra_carbon->greaterThan($ngay_nhan_carbon)
            ? $ngay_nhan_carbon->diffInDays($ngay_tra_carbon)
            : 1;

        // Use promotional price if available, otherwise use base price
        $gia_mot_dem = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban ?? 0;
        $tong_tien_initial = $gia_mot_dem * $so_dem; // Tổng tiền ban đầu tính bằng PHP

    @endphp
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 bg-white p-6 rounded shadow">
                @if (isset($loaiPhong->anh) && $loaiPhong->anh)
                    <img src="{{ asset($loaiPhong->anh) }}" alt="room type" class="w-full h-48 object-cover rounded mb-4">
                @else
                    <img src="/img/room/room-1.jpg" alt="room type" class="w-full h-48 object-cover rounded mb-4">
                @endif

                <h3 class="text-lg font-semibold">{{ $loaiPhong->ten_loai ?? 'Loại phòng' }}</h3>
                <p class="text-sm text-gray-600 mt-2">{{ $loaiPhong->mo_ta ?? '' }}</p>

                <div class="mt-4 text-sm">
                    @if($loaiPhong->diem_danh_gia && $loaiPhong->so_luong_danh_gia > 0)
                    <div class="flex items-center gap-2">
                        <span class="bg-green-500 text-white rounded px-2 py-1 text-xs">{{ number_format($loaiPhong->diem_danh_gia, 1) }}</span>
                        <span class="text-sm text-gray-700">{{ $loaiPhong->rating_text }} · {{ $loaiPhong->so_luong_danh_gia }} đánh giá</span>
                    </div>
                    @endif
                    <div class="mt-4">
                        <h4 class="font-medium mb-3">Chi tiết đặt phòng của bạn</h4>

                        <div class="space-y-2 text-sm">
                            <p class="text-gray-700">
                                Giá:
                                @if($loaiPhong->gia_khuyen_mai)
                                    <span class="text-red-600 font-semibold">{{ number_format($loaiPhong->gia_khuyen_mai, 0, ',', '.') }}</span>
                                    <span class="text-gray-500 line-through text-xs ml-1">{{ number_format($loaiPhong->gia_co_ban, 0, ',', '.') }}</span>
                                @else
                                    {{ number_format($loaiPhong->gia_co_ban ?? 0, 0, ',', '.') }}
                                @endif
                                 VND / đêm
                            </p>
                            <p class="text-gray-700" id="so-dem-luu-tru">Số đêm: {{ $so_dem }} đêm</p>
                    </div>

                        {{-- Voucher Section --}}
                        <div class="mt-4 pt-3 border-t border-gray-200">
                            <a href="#" id="openVoucherLink"
                                class="inline-flex items-center space-x-2 text-indigo-600 hover:text-indigo-800 font-semibold text-sm cursor-pointer transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                                <span id="voucherActionText">
                                Chọn hoặc nhập mã giảm giá
                            </span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                            </a>

                            {{-- Voucher Display --}}
                            <div id="voucherDisplay" class="mt-2 hidden"></div>
                    </div>

                        <p class="text-xs text-gray-500 mt-3 italic">* Phòng cụ thể sẽ được tự động chọn khi đặt</p>
                    </div>

                    <input type="hidden" id="totalPriceBeforeDiscount" value="{{ $tong_tien_initial }}">

                        {{-- Tổng tiền --}}
                        <div class="mt-4 pt-3 border-t-2 border-gray-300">
                            <div id="totalBeforeDiscount" class="text-sm text-gray-600 mb-1 hidden"></div>
                            <div id="discountAmountDisplay" class="text-sm text-green-600 mb-1 hidden"></div>
                            <div id="totalAfterDiscount" class="text-xl font-bold text-red-600">
                                Tổng: {{ number_format($tong_tien_initial) }} VNĐ
                            </div>
                            {{-- Hiển thị danh sách các loại phòng đã chọn --}}
                            <div id="selectedRoomsSummary" class="mt-3 pt-3 border-t border-gray-200 text-sm">
                                <p class="font-medium text-gray-700 mb-2">Các loại phòng đã chọn:</p>
                                <div id="roomsSummaryList" class="space-y-1">
                                    <div class="text-gray-600">
                                        <span class="font-medium">{{ $loaiPhong->ten_loai }}</span>
                                        <span id="room_0_summary_quantity">x1</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white p-6 rounded shadow">
                <h2 class="text-xl font-semibold mb-4">Nhập thông tin chi tiết của bạn</h2>

                @if (session('status'))
                    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('status') }}</div>
                @endif

                {{-- Display general errors --}}
                @if ($errors->has('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <strong class="font-bold">Lỗi!</strong>
                        <span class="block sm:inline">{{ $errors->first('error') }}</span>
                    </div>
                @endif

                {{-- Display validation errors for rooms array --}}
                @if ($errors->has('rooms'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <strong class="font-bold">Lỗi!</strong>
                        <ul class="list-disc list-inside mt-1">
                            @foreach ($errors->get('rooms') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($errors->has('rooms.*.loai_phong_id'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <strong class="font-bold">Lỗi!</strong>
                        <ul class="list-disc list-inside mt-1">
                            @foreach ($errors->get('rooms.*.loai_phong_id') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($errors->has('rooms.*.so_luong'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <strong class="font-bold">Lỗi!</strong>
                        <ul class="list-disc list-inside mt-1">
                            @foreach ($errors->get('rooms.*.so_luong') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('booking.submit') }}" method="POST" id="finalBookingForm">
                    @csrf
                    <input type="hidden" name="tong_tien_dat_phong" id="finalBookingPrice"
                        value="{{ $tong_tien_initial }}">

                    {{-- Room Selection Section --}}
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold">Chọn phòng</h3>
                            <button type="button" id="addRoomBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-colors text-sm font-medium">
                                <i class="fas fa-plus mr-1"></i> Thêm loại phòng khác
                            </button>
                        </div>
                        <div id="roomsContainer">
                            {{-- First room (default selected) - Display only, no dropdown --}}
                            <div class="room-item mb-4 p-4 border-2 border-blue-200 rounded-lg bg-blue-50" data-room-index="0">
                                <div class="mb-3">
                                    <h4 class="font-medium text-gray-900">Loại phòng đã chọn</h4>
                                </div>

                                {{-- Room Type Info Card --}}
                                <div class="mb-4 bg-white rounded-lg border border-gray-200 overflow-hidden">
                                    <div class="flex flex-col md:flex-row">
                                        {{-- Room Image --}}
                                        @if($loaiPhong->anh)
                                            <div class="md:w-48 w-full h-48 md:h-auto flex-shrink-0">
                                                <img src="{{ asset($loaiPhong->anh) }}" alt="{{ $loaiPhong->ten_loai }}"
                                                     class="w-full h-full object-cover">
                                            </div>
                                        @else
                                            <div class="md:w-48 w-full h-48 md:h-auto flex-shrink-0 bg-gray-200 flex items-center justify-center">
                                                <i class="fas fa-image text-gray-400 text-4xl"></i>
                                            </div>
                                        @endif
                                        {{-- Room Info --}}
                                        <div class="flex-1 p-4">
                                            <h5 class="font-semibold text-lg text-gray-900 mb-2">{{ $loaiPhong->ten_loai }}</h5>
                                            <div class="flex items-center gap-2 mb-2">
                                                @php
                                                    $displayPrice = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban;
                                                    $basePrice = $loaiPhong->gia_co_ban;
                                                @endphp
                                                @if($loaiPhong->gia_khuyen_mai)
                                                    <span class="text-red-600 font-bold text-lg">{{ number_format($loaiPhong->gia_khuyen_mai, 0, ',', '.') }}</span>
                                                    <span class="text-gray-500 line-through text-sm">{{ number_format($loaiPhong->gia_co_ban, 0, ',', '.') }}</span>
                                                @else
                                                    <span class="text-blue-600 font-bold text-lg">{{ number_format($loaiPhong->gia_co_ban, 0, ',', '.') }}</span>
                                                @endif
                                                <span class="text-gray-600 text-sm">VNĐ/đêm</span>
                                            </div>
                                            <p class="text-sm text-gray-600" id="room_availability_0">
                                                <i class="fas fa-bed text-blue-500"></i>
                                                @if(isset($availableCount))
                                                    Còn {{ max(0, $availableCount) }} phòng trống
                                                    <span class="text-blue-500 text-xs">(từ {{ isset($checkinToUse) ? date('d/m/Y', strtotime($checkinToUse)) : '...' }} đến {{ isset($checkoutToUse) ? date('d/m/Y', strtotime($checkoutToUse)) : '...' }})</span>
                                                @else
                                                    Còn {{ max(0, $loaiPhong->so_luong_phong) }} phòng (vui lòng chọn ngày để xem số phòng trống)
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Quantity Selection --}}
                                <div>
                                    <label class="block text-sm font-medium mb-2">Số lượng phòng *</label>
                                    <div class="flex items-center gap-3">
                                        <button type="button"
                                            class="w-10 h-10 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md transition-colors font-bold text-lg"
                                            onclick="decreaseRoomQuantity(0)"
                                            tabindex="-1">
                                            −
                                        </button>
                                        <input type="text"
                                            name="rooms[0][so_luong]"
                                            id="room_quantity_0"
                                            value="{{ old('rooms.0.so_luong', 1) }}"
                                            data-max="{{ isset($availableCount) ? max(0, $availableCount) : max(0, $loaiPhong->so_luong_phong) }}"
                                            class="room-quantity w-20 text-center border-2 border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-semibold @error('rooms.0.so_luong') border-red-500 @enderror"
                                            onchange="updateRoomQuantity(0)">
                                        <button type="button"
                                            class="w-10 h-10 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md transition-colors font-bold text-lg"
                                            onclick="increaseRoomQuantity(0)"
                                            tabindex="-1">
                                            +
                                        </button>
                                <span class="text-sm text-gray-600 ml-2">
                                    / <span id="max_quantity_0">{{ isset($availableCount) ? max(0, $availableCount) : max(0, $loaiPhong->so_luong_phong) }}</span> phòng
                                </span>
                                    </div>
                                    @error('rooms.0.so_luong')
                                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                    @if($errors->has('rooms.*.so_luong'))
                                        @foreach($errors->get('rooms.*.so_luong') as $error)
                                            <div class="text-red-600 text-xs mt-1">{{ $error }}</div>
                                        @endforeach
                                    @endif
                                    <p class="text-xs text-red-600 mt-1 hidden" id="quantity_error_0">
                                        Số lượng không được vượt quá {{ isset($availableCount) ? max(0, $availableCount) : max(0, $loaiPhong->so_luong_phong) }} phòng
                                    </p>
                                </div>

                                <div class="mt-3 text-sm text-gray-700">
                                    <span class="room-subtotal font-medium">Giá: <span id="room_subtotal_0">0</span></span>
                                </div>

                                <input type="hidden" id="room_0_summary_name" value="{{ $loaiPhong->ten_loai }}">

                                {{-- Hidden input for room type ID --}}
                                <input type="hidden" name="rooms[0][loai_phong_id]" value="{{ $loaiPhong->id }}"
                                       data-price="{{ $displayPrice }}"
                                       data-base-price="{{ $basePrice }}"
                                       data-room-type-name="{{ $loaiPhong->ten_loai }}"
                                       class="room-type-select">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium">Họ Và Tên (tiếng Anh) *</label>
                            <input type="text" name="first_name"
                                value="{{ old('first_name', auth()->check() ? auth()->user()->ho_ten : '') }}"
                                class="mt-1 block w-full border rounded p-2 @error('first_name') border-red-500 @enderror">
                            @error('first_name')
                                <div class="text-red-600 text-sm">{{ $message }}</div>
                            @enderror
                        </div>


                        <div>
                            <label class="block text-sm font-medium">Địa chỉ email *</label>
                            <input type="text" name="email" value="{{ old('email', auth()->user()->email ?? '') }}"
                                class="mt-1 block w-full border rounded p-2 @error('email') border-red-500 @enderror">
                            @error('email')
                                <div class="text-red-600 text-sm">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Số điện thoại</label>
                            <input type="text" name="phone" value="{{ old('phone', auth()->user()->sdt ?? '') }}"
                                class="mt-1 block w-full border rounded p-2 @error('phone') border-red-500 @enderror">
                            @error('phone')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium">CCCD/CMND *</label>
                            <input type="text" name="cccd" value="{{ old('cccd', auth()->user()->cccd ?? '') }}"
                                class="mt-1 block w-full border rounded p-2 @error('cccd') border-red-500 @enderror"
                                placeholder="Nhập số CCCD/CMND">
                            @error('cccd')
                                <div class="text-red-600 text-sm">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Ngày nhận *</label>
                            <input type="date" name="ngay_nhan"
                                value="{{ old('ngay_nhan', isset($checkin) ? $checkin : $ngay_nhan_carbon->format('Y-m-d')) }}"
                                class="mt-1 block w-full border rounded p-2 @error('ngay_nhan') border-red-500 @enderror" id="ngay_nhan_input">
                            @error('ngay_nhan')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Ngày trả *</label>
                            <input type="date" name="ngay_tra"
                                value="{{ old('ngay_tra', isset($checkout) ? $checkout : $ngay_tra_carbon->format('Y-m-d')) }}"
                                class="mt-1 block w-full border rounded p-2 @error('ngay_tra') border-red-500 @enderror" id="ngay_tra_input">
                            @error('ngay_tra')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium">Số người</label>
                            <input type="text" name="so_nguoi"
                                value="{{ old('so_nguoi', isset($guests) ? $guests : 1) }}"
                                class="mt-1 block w-1/6 border rounded p-2 @error('so_nguoi') border-red-500 @enderror">
                            @error('so_nguoi')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <input type="hidden" name="voucherCode" id="voucherCode" value="">
                        <input type="hidden" name="discountValue" id="discountValue" value="0">
                    </div>

                    <div class="mt-6 flex items-center gap-3">
                        <button type="submit" class="bg-yellow-500 text-white px-4 py-2 hover:bg-yellow-600 rounded">Hoàn
                            tất đặt
                            phòng</button>
                        <a href="{{ url()->previous() }}" class="text-sm text-gray-600">Quay lại</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateY(0);
            }

            to {
                opacity: 0;
                transform: translateY(-10px);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.3s ease-out;
        }

        .fade-out {
            animation: fadeOut 0.3s ease-in forwards;
        }

        .custom-voucher-card {
            transition: all 0.2s ease;
        }

        /* Style cho nút Áp Dụng khi đủ điều kiện */
        .apply-voucher-btn.active {
            background-color: #d0cc05;
            color: white;
            border-color: #ded307;
        }

        /* Thêm style cho custom-scrollbar nếu chưa có */
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // === CÁC PHẦN TỬ CẦN THIẾT (DOM REFERENCES) ===
            const giaMotDem = parseFloat("{{ $gia_mot_dem ?? 0 }}");
            const loaiPhongId = '{{ $loaiPhong->id ?? 0 }}';

            // THAY ĐỔI: openVoucherBtn -> openVoucherLink
            const openVoucherLink = document.getElementById('openVoucherLink');
            const voucherActionText = document.getElementById('voucherActionText');
            const voucherDisplayDiv = document.getElementById('voucherDisplay');
            const voucherCodeInput = document.getElementById('voucherCode');
            const discountValueInput = document.getElementById('discountValue');
            const checkinInput = document.getElementById('ngay_nhan_input');
            const checkoutInput = document.getElementById('ngay_tra_input');

            // Gọi updateAvailableCount khi trang load để đảm bảo hiển thị đúng
            // Sẽ được gọi sau khi tất cả các hàm đã được định nghĩa

            const soDemLuuTruElement = document.getElementById('so-dem-luu-tru');
            const totalBeforeDiscountDiv = document.getElementById('totalBeforeDiscount');
            const totalAfterDiscountDiv = document.getElementById('totalAfterDiscount');
            const finalBookingPriceInput = document.getElementById('finalBookingPrice');

            // THÊM: Tham chiếu đến form đặt phòng
            const finalBookingForm = document.getElementById('finalBookingForm');

            let popupElement = null;
            let currentDiscountPercent = parseFloat(discountValueInput.value) || 0;
            let alertTimeout = null;

            // THÊM: Cờ kiểm tra đang gửi form đặt phòng
            let isCompletingBooking = false;

            // --- HÀM LƯU/KHÔI PHỤC TRẠNG THÁI (SỬ DỤNG sessionStorage) ---

            function saveVoucherState() {
                try {
                    sessionStorage.setItem('appliedVoucherCode', voucherCodeInput.value);
                    sessionStorage.setItem('appliedDiscountPercent', currentDiscountPercent.toString());
                    sessionStorage.setItem('appliedVoucherRoomId', loaiPhongId);
                } catch (e) {
                    console.warn('Không thể lưu trạng thái voucher vào sessionStorage:', e);
                }
            }

            function clearSavedVoucherState() {
                try {
                    sessionStorage.removeItem('appliedVoucherCode');
                    sessionStorage.removeItem('appliedDiscountPercent');
                    sessionStorage.removeItem('appliedVoucherRoomId');
                } catch (e) {
                    console.warn('Không thể xóa trạng thái voucher khỏi sessionStorage:', e);
                }
            }

            function restoreVoucherState() {
                try {
                    const savedCode = sessionStorage.getItem('appliedVoucherCode');
                    const savedPercent = parseFloat(sessionStorage.getItem('appliedDiscountPercent')) || 0;
                    const savedRoomId = sessionStorage.getItem('appliedVoucherRoomId');

                    if (savedCode && savedPercent > 0 && savedRoomId === loaiPhongId) {
                        currentDiscountPercent = savedPercent;
                        voucherCodeInput.value = savedCode;
                        discountValueInput.value = savedPercent.toString();
                        return true;
                    }
                } catch (e) {
                    console.warn('Không thể khôi phục trạng thái voucher từ sessionStorage:', e);
                }
                return false;
            }
            // --- END HÀM LƯU/KHÔI PHỤC TRẠNG THÁI ---


            // --- UTILITY FUNCTIONS ---
            function formatCurrency(number) {
                return Math.round(number).toLocaleString('vi-VN', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }) + ' VNĐ';
            }

            function getDatesAndDays() {
                const checkinValue = checkinInput.value;
                const checkoutValue = checkoutInput.value;

                const checkinDate = new Date(checkinValue);
                const checkoutDate = new Date(checkoutValue);

                let soDem;

                if (checkinDate instanceof Date && !isNaN(checkinDate) &&
                    checkoutDate instanceof Date && !isNaN(checkoutDate) &&
                    checkoutDate > checkinDate) {

                    const diffTime = Math.abs(checkoutDate - checkinDate);
                    soDem = Math.round(diffTime / (1000 * 60 * 60 * 24));
                } else {
                    soDem = 1;
                }

                return {
                    checkinValue,
                    checkoutValue,
                    soDem
                };
            }

            function getDiscountPercentFromCard(cardElement) {
                const discountElement = cardElement.querySelector('.font-semibold.text-gray-800.text-base');
                if (!discountElement) return 0;

                const text = discountElement.textContent || '';
                const match = text.match(/Giảm\s*(\d+)\s*%/i);

                if (match && match[1]) {
                    return parseFloat(match[1].trim());
                }
                return 0;
            }

            function clearVoucher() {
                currentDiscountPercent = 0;
                voucherCodeInput.value = '';
                discountValueInput.value = '0';
                clearSavedVoucherState();
                tinhTongTien();

                const popup = popupElement;
                if (popup) {
                    const searchInput = popup.querySelector('#popupVoucherCodeInput');
                    if (searchInput) searchInput.value = '';
                    displayAlert(popup, '');
                }
            }


            function tinhTongTien() {
                // Tính tổng giá từ tất cả các loại phòng được chọn
                const { soDem } = getDatesAndDays();
                let totalBeforeDiscountAmount = 0;

                // Tìm tất cả hidden input với class room-type-select (có thể là hidden input hoặc select)
                document.querySelectorAll('.room-type-select').forEach(function(selectElement) {
                    let price = 0;
                    let quantity = 1;

                    // Nếu là hidden input
                    if (selectElement.type === 'hidden') {
                        price = parseFloat(selectElement.getAttribute('data-price')) || 0;
                        // Tìm quantity input tương ứng (cùng container hoặc theo id)
                        const roomId = selectElement.name.match(/rooms\[(\d+)\]/);
                        if (roomId) {
                            const quantityInput = document.querySelector(`input[name="rooms[${roomId[1]}][so_luong]"]`);
                            if (quantityInput) {
                                quantity = parseInt(quantityInput.value) || 1;
                            }
                        }
                    }
                    // Nếu là select element (fallback cho trường hợp cũ)
                    else if (selectElement.tagName === 'SELECT' && selectElement.value) {
                        const selectedOption = selectElement.options[selectElement.selectedIndex];
                        price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                        // Tìm quantity trong cùng container
                        const roomItem = selectElement.closest('.room-item');
                        if (roomItem) {
                            const quantityInput = roomItem.querySelector('.room-quantity');
                            if (quantityInput) {
                                quantity = parseInt(quantityInput.value) || 1;
                            }
                        }
                    }

                    if (price > 0) {
                        totalBeforeDiscountAmount += price * quantity * soDem;
                    }
                });

                // Nếu không có phòng nào được chọn, sử dụng giá mặc định
                if (totalBeforeDiscountAmount === 0) {
                    totalBeforeDiscountAmount = giaMotDem * soDem;
                }
                const discountPercent = currentDiscountPercent;

                let discountAmount = 0;
                let totalAfterDiscount = totalBeforeDiscountAmount;

                if (discountPercent > 0) {
                    discountAmount = totalBeforeDiscountAmount * (discountPercent / 100);
                    totalAfterDiscount = Math.max(0, totalBeforeDiscountAmount - discountAmount);
                }

                // Cập nhật giao diện chính
                soDemLuuTruElement.textContent = `Số đêm: ${soDem} đêm`;

                // Tìm element hiển thị discount amount
                let discountAmountDisplay = document.getElementById('discountAmountDisplay');
                if (!discountAmountDisplay) {
                    discountAmountDisplay = document.createElement('div');
                    discountAmountDisplay.id = 'discountAmountDisplay';
                    discountAmountDisplay.className = 'text-sm text-green-600 mb-1 hidden';
                    totalAfterDiscountDiv.parentNode.insertBefore(discountAmountDisplay, totalAfterDiscountDiv);
                }

                if (discountPercent > 0) {
                    const currentCode = voucherCodeInput.value || 'VOUCHER';

                    // Hiển thị giá gốc (trước giảm giá)
                    totalBeforeDiscountDiv.innerHTML =
                        `<span class="text-gray-600">Giá gốc:</span> <span class="line-through text-gray-500">${formatCurrency(totalBeforeDiscountAmount)}</span>`;
                    totalBeforeDiscountDiv.classList.remove('hidden');

                    // Hiển thị số tiền giảm
                    discountAmountDisplay.innerHTML =
                        `<span class="text-green-600">Giảm giá:</span> <span class="font-semibold text-green-600">-${formatCurrency(discountAmount)}</span>`;
                    discountAmountDisplay.classList.remove('hidden');

                    // Cập nhật tổng tiền sau giảm giá
                    totalAfterDiscountDiv.innerHTML = `Tổng: ${formatCurrency(totalAfterDiscount)}`;
                    totalAfterDiscountDiv.classList.add('text-xl', 'font-bold', 'text-red-600');

                    // BẮT ĐẦU PHẦN ĐÃ CHỈNH SỬA MÀU: Cập nhật giao diện cho LINK TEXT
                    voucherActionText.textContent = `Đã áp dụng mã: ${currentCode}`;

                    // Vẫn giữ màu xanh nước biển (indigo) cho link hành động:
                    openVoucherLink.classList.remove('text-green-600', 'hover:text-green-800');
                    openVoucherLink.classList.add('text-indigo-600', 'hover:text-indigo-800');
                    // KẾT THÚC PHẦN ĐÃ CHỈNH SỬA MÀU

                    // Phần hiển thị chi tiết voucher (voucherDisplayDiv) với style đẹp hơn
                    voucherDisplayDiv.innerHTML = `
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3 mt-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-green-800">Mã ${currentCode}</p>
                                        <p class="text-xs text-green-600">Giảm ${discountPercent}%</p>
                                    </div>
                                </div>
                                <button id="voucherClearLink" type="button" class="text-xs text-red-600 hover:text-red-700 font-semibold transition hover:underline">
                    Hủy
                </button>
                            </div>
                        </div>
        `;
                    voucherDisplayDiv.classList.remove('hidden');

                    const clearLink = voucherDisplayDiv.querySelector('#voucherClearLink');
                    if (clearLink) {
                        // Remove old listener nếu có
                        const newClearLink = clearLink.cloneNode(true);
                        clearLink.parentNode.replaceChild(newClearLink, clearLink);
                        newClearLink.addEventListener('click', function() {
                            clearVoucher();
                        });
                    }

                } else {
                    // Không có voucher
                    totalBeforeDiscountDiv.classList.add('hidden');
                    discountAmountDisplay.classList.add('hidden');

                    // Cập nhật tổng tiền (không có giảm giá)
                    totalAfterDiscountDiv.innerHTML = `Tổng: ${formatCurrency(totalAfterDiscount)}`;
                    totalAfterDiscountDiv.classList.add('text-xl', 'font-bold', 'text-red-600');

                    // Khi KHÔNG có voucher, link hành động về màu xanh nước biển mặc định
                    voucherActionText.textContent = 'Chọn hoặc nhập mã giảm giá';

                    openVoucherLink.classList.remove('text-green-600', 'hover:text-green-800');
                    openVoucherLink.classList.add('text-indigo-600', 'hover:text-indigo-800');

                    voucherDisplayDiv.classList.add('hidden');
                }

                finalBookingPriceInput.value = Math.round(totalAfterDiscount);
                discountValueInput.value = currentDiscountPercent;

                return totalBeforeDiscountAmount;
            }

            function applyVoucher(code, percent, popup) {
                const numericPercent = parseFloat(percent) || 0;
                if (!code || numericPercent === 0) return;

                currentDiscountPercent = numericPercent;
                voucherCodeInput.value = code;
                discountValueInput.value = currentDiscountPercent;

                saveVoucherState();
                tinhTongTien();

                if (popup) {
                    const closeBtn = popup.querySelector('#closeVoucherPopup');
                    if (closeBtn) {
                        closeBtn.dispatchEvent(new Event('click'));
                    }
                }
            }

            function displayAlert(popup, message, isError = false) {
                const alertContainer = popup.querySelector('#voucherAlertMessage');
                const pTag = alertContainer ? alertContainer.querySelector('p') : null;
                if (!alertContainer || !pTag) return;

                if (alertTimeout) {
                    clearTimeout(alertTimeout);
                    alertTimeout = null;
                }

                if (!message) {
                    alertContainer.classList.add('hidden');
                    pTag.textContent = '';
                    return;
                }

                pTag.innerHTML = message;
                alertContainer.classList.remove('hidden');

                // LOẠI BỎ 'text-center'
                alertContainer.className = 'mt-2';

                // LOẠI BỎ 'inline-block'
                pTag.className = isError ?
                    'text-sm py-2 px-3 rounded-lg bg-red-100 text-red-700 font-medium' :
                    'text-sm py-2 px-3 rounded-lg bg-sky-100 text-sky-800 font-medium';

                alertTimeout = setTimeout(() => {
                    alertContainer.classList.add('hidden');
                    alertTimeout = null;
                }, 5000);
            }

            function setupPopupEvents(popup) {
                const searchInput = popup.querySelector('#popupVoucherCodeInput');
                if (searchInput) {
                    searchInput.value = '';
                }

                const closeBtn = popup.querySelector('#closeVoucherPopup');
                if (!closeBtn.hasEventListener) {
                    const closePopup = () => {
                        const popupContent = popup.querySelector('.custom-voucher-inner');
                        if (popupContent) {
                            popupContent.classList.remove('animate-fadeIn');
                            displayAlert(popup, '');
                        }
                        setTimeout(() => {
                            popup.classList.add('hidden');
                        }, 300);
                    };

                    closeBtn.addEventListener('click', closePopup);
                    popup.addEventListener('click', function(e) {
                        if (e.target === popup) closePopup();
                    });
                    closeBtn.hasEventListener = true;
                }

                const voucherListContainer = popup.querySelector('.custom-scrollbar');
                if (voucherListContainer && !voucherListContainer.hasEventListener) {
                    voucherListContainer.addEventListener('click', function(e) {
                        const applyBtn = e.target.closest('.apply-voucher-btn');

                        if (applyBtn) {
                            if (currentDiscountPercent > 0) {
                                const targetCard = applyBtn.closest('.custom-voucher-card');
                                const newCode = targetCard.dataset.voucherCode;
                                const currentCode = voucherCodeInput.value;

                                if (newCode === currentCode) {
                                    displayAlert(popup, `Mã ${newCode} đã được áp dụng rồi.`, false);
                                    return;
                                }

                                displayAlert(popup,
                                    `Bạn chỉ có thể áp dụng 1 mã voucher duy nhất. Vui lòng nhấn "Hủy" mã ${voucherCodeInput.value} ở bên dưới hoặc ngoài trang thanh toán trước.`,
                                    true);
                                return;
                            }

                            const targetCard = applyBtn.closest('.custom-voucher-card');
                            if (!targetCard) return;

                            const isValid = targetCard.dataset.isValid === 'true';
                            const code = targetCard.dataset.voucherCode;
                            const percent = getDiscountPercentFromCard(targetCard);

                            if (isValid) {
                                applyVoucher(code, percent, popup);
                            } else {
                                displayAlert(popup,
                                    'Voucher này chưa đủ điều kiện (giá trị đơn hàng tối thiểu hoặc không áp dụng cho loại phòng này).',
                                    true);
                            }
                        }
                    });
                    voucherListContainer.hasEventListener = true;
                }

                const searchBtn = popup.querySelector('#searchVoucherBtn');

                if (searchBtn && searchInput) {
                    // Reset event listener cũ
                    const newSearchBtn = searchBtn.cloneNode(true);
                    searchBtn.parentNode.replaceChild(newSearchBtn, searchBtn);

                    function handleSearch() {
                        const searchCode = searchInput.value.toUpperCase().trim();
                        displayAlert(popup, '');

                        if (!searchCode) {
                            displayAlert(popup, 'Vui lòng nhập mã voucher.', true);
                            return;
                        }

                        // === LOGIC TÌM KIẾM VÀ KIỂM TRA ÁP DỤNG ===
                        if (currentDiscountPercent > 0) {
                            const currentAppliedCode = voucherCodeInput.value;

                            // 1. Nếu mã nhập vào là mã đang áp dụng -> HỦY
                            if (searchCode === currentAppliedCode) {
                                clearVoucher(); // Hủy voucher
                                displayAlert(popup, `Mã ${searchCode} đã được hủy thành công.`, false);
                                return;
                            }

                            displayAlert(popup,
                                `Bạn chỉ có thể áp dụng 1 mã voucher duy nhất. Vui lòng nhấn "Hủy" mã ${currentAppliedCode} trước khi áp dụng mã mới.`,
                                true);
                            return;
                        }
                        // === END LOGIC KIỂM TRA ÁP DỤNG ===


                        const card = popup.querySelector(`[data-voucher-code="${searchCode}"]`);

                        if (card) {
                            const isValid = card.dataset.isValid === 'true';
                            const percent = getDiscountPercentFromCard(card);

                            if (isValid) {
                                applyVoucher(searchCode, percent, popup);
                            } else {
                                displayAlert(popup,
                                    `Mã "${searchCode}" không hợp lệ cho đơn hàng này (không đủ giá trị tối thiểu hoặc không áp dụng cho loại phòng này).`,
                                    true);
                            }
                        } else {
                            displayAlert(popup, `Mã "${searchCode}" không tồn tại.`, true);
                        }
                    }

                    newSearchBtn.addEventListener('click', handleSearch);
                    searchInput.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            handleSearch();
                        }
                    });
                }
            }

            // --- BỔ SUNG LOGIC XÓA VOUCHER KHI RỜI TRANG ---

            if (finalBookingForm) {
                finalBookingForm.addEventListener('submit', function(e) {
                    // Đặt cờ là đang gửi form (đang hoàn tất đặt phòng)
                    isCompletingBooking = true;
                    // Form sẽ submit bình thường, validation sẽ được xử lý bởi Laravel
                });
            }

            window.addEventListener('beforeunload', function() {
                if (!isCompletingBooking) {
                    clearSavedVoucherState();
                }
            });


            // --- MAIN LOGIC ---

            // 1. KHÔI PHỤC TRẠNG THÁI VOUCHER KHI TRANG TẢI LẠI
            restoreVoucherState();

            // 2. Sự kiện mở Popup (ĐÃ SỬA ID: openVoucherLink)
            openVoucherLink.addEventListener('click', function(e) {
                e.preventDefault(); // RẤT QUAN TRỌNG KHI SỬ DỤNG THẺ <a>
                const {
                    soDem
                } = getDatesAndDays();
                const currentTotal = giaMotDem * soDem;
                const fetchUrl =
                    `/client/voucher?current_total=${Math.round(currentTotal)}&loai_phong_id=${loaiPhongId}`;

                fetch(fetchUrl)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.text();
                    })
                    .then(html => {
                        if (!popupElement) {
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = html;
                            popupElement = tempDiv.firstChild;
                            document.body.appendChild(popupElement);
                        } else {
                            const innerContent = popupElement.querySelector('.custom-voucher-inner');
                            if (innerContent) {
                                const newDoc = new DOMParser().parseFromString(html, 'text/html');
                                const newVoucherList = newDoc.querySelector('.custom-scrollbar');
                                const oldVoucherList = innerContent.querySelector('.custom-scrollbar');
                                if (oldVoucherList && newVoucherList) {
                                    oldVoucherList.parentNode.replaceChild(newVoucherList,
                                        oldVoucherList);
                                }
                            }
                        }

                        setupPopupEvents(popupElement);

                        popupElement.classList.remove('hidden');

                        if (currentDiscountPercent > 0) {
                            displayAlert(popupElement,
                                `Mã ${voucherCodeInput.value} đang được áp dụng.`, false);
                        } else {
                            displayAlert(popupElement, '');
                        }
                    })
                    .catch(err => {
                        console.error('Lỗi khi tải voucher:', err);
                        if (!popupElement) {
                            alert(
                                'Không thể tải danh sách voucher. Vui lòng kiểm tra kết nối mạng hoặc server.'
                            );
                        } else {
                            const loadingMessage = popupElement.querySelector('#voucherAlertMessage p');
                            if (loadingMessage) {
                                loadingMessage.textContent =
                                    'Không thể tải danh sách voucher. Vui lòng thử lại sau. (Lỗi server/route)';
                                displayAlert(popupElement, loadingMessage.textContent, true);
                            }
                        }
                    });
            });

            // 3. Sự kiện thay đổi ngày nhận/trả phòng (luôn HỦY voucher và tính toán lại)
            // Hàm cập nhật số phòng trống cho một phòng cụ thể
            function updateRoomAvailability(roomIndex, loaiPhongId, checkin, checkout) {
                if (!checkin || !checkout) {
                    return;
                }

                // Validate ngày
                if (new Date(checkout) <= new Date(checkin)) {
                    return; // Ngày không hợp lệ, không update
                }

                // Format ngày để hiển thị
                const formatDate = (dateStr) => {
                    const date = new Date(dateStr);
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear();
                    return `${day}/${month}/${year}`;
                };

                fetch('{{ route("booking.available_count") }}', {
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
                        const availableCount = Math.max(0, data.availableCount);

                        // Cập nhật hiển thị số phòng trống
                        const availabilityEl = document.getElementById(`room_availability_${roomIndex}`);
                        if (availabilityEl) {
                            availabilityEl.innerHTML = `
                                <i class="fas fa-bed text-blue-500"></i> Còn ${availableCount} phòng trống
                                <span class="text-blue-500 text-xs">(từ ${formatDate(checkin)} đến ${formatDate(checkout)})</span>
                            `;
                        }

                        // Cập nhật data-max và max quantity
                        const quantityInput = document.getElementById(`room_quantity_${roomIndex}`);
                        if (quantityInput) {
                            quantityInput.setAttribute('data-max', availableCount);
                            const maxQuantityEl = document.getElementById(`max_quantity_${roomIndex}`);
                            if (maxQuantityEl) {
                                // Cập nhật chỉ số, không thêm "phòng" vì đã có trong HTML template
                                maxQuantityEl.textContent = `${availableCount}`;
                            }
                            // Reset quantity nếu vượt quá max
                            const currentQuantity = parseInt(quantityInput.value) || 1;
                            if (currentQuantity > availableCount && availableCount > 0) {
                                quantityInput.value = availableCount;
                                updateRoomQuantity(roomIndex);
                            }
                        }
                    } else {
                        console.error('Error:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error updating available count:', error);
                });
            }

            // Hàm cập nhật số phòng trống khi ngày thay đổi (cho tất cả các phòng)
            function updateAvailableCount() {
                const checkin = document.getElementById('ngay_nhan_input')?.value;
                const checkout = document.getElementById('ngay_tra_input')?.value;

                if (!checkin || !checkout) {
                    // Nếu chưa có ngày, hiển thị thông báo cho tất cả các phòng
                    document.querySelectorAll('.room-item').forEach(function(roomItem) {
                        const roomIndex = roomItem.getAttribute('data-room-index');
                        const availabilityEl = document.getElementById(`room_availability_${roomIndex}`);
                        if (availabilityEl) {
                            // Lấy số phòng mặc định từ data attribute hoặc từ select
                            const select = roomItem.querySelector('.room-type-select');
                            let defaultCount = 0;
                            if (select && select.tagName === 'SELECT' && select.value) {
                                const selectedOption = select.options[select.selectedIndex];
                                defaultCount = parseInt(selectedOption.getAttribute('data-so-luong-trong')) || 0;
                            } else if (roomIndex === '0') {
                                defaultCount = {{ $loaiPhong->so_luong_phong }};
                            }
                            availabilityEl.innerHTML = `<i class="fas fa-bed text-blue-500"></i> Còn ${defaultCount} phòng (vui lòng chọn ngày để xem số phòng trống)`;
                        }
                    });
                    return;
                }

                // Validate ngày
                if (new Date(checkout) <= new Date(checkin)) {
                    return; // Ngày không hợp lệ, không update
                }

                // Cập nhật cho tất cả các phòng đã chọn
                document.querySelectorAll('.room-item').forEach(function(roomItem) {
                    const roomIndex = roomItem.getAttribute('data-room-index');
                    const select = roomItem.querySelector('.room-type-select');

                    if (select && select.value) {
                        let loaiPhongId = null;

                        if (select.tagName === 'SELECT') {
                            loaiPhongId = select.value;
                        } else if (select.type === 'hidden') {
                            loaiPhongId = select.value;
                        }

                        if (loaiPhongId) {
                            updateRoomAvailability(roomIndex, loaiPhongId, checkin, checkout);
                        }
                    }
                });
            }

            checkinInput.addEventListener('change', function() {
                clearVoucher();
                tinhTongTien();
                updateAvailableCount(); // Cập nhật số phòng trống cho tất cả các phòng
                // Cập nhật giá cho tất cả các phòng
                document.querySelectorAll('.room-type-select').forEach(function(select) {
                    if (select.value && typeof updateRoomPrice === 'function') {
                        updateRoomPrice(select);
                    }
                });
            });
            checkoutInput.addEventListener('change', function() {
                clearVoucher();
                tinhTongTien();
                updateAvailableCount(); // Cập nhật số phòng trống cho tất cả các phòng
                // Cập nhật giá cho tất cả các phòng
                document.querySelectorAll('.room-type-select').forEach(function(select) {
                    if (select.value && typeof updateRoomPrice === 'function') {
                        updateRoomPrice(select);
                    }
                });
            });

            // 4. Hàm cập nhật giá khi thay đổi loại phòng hoặc số lượng
            window.updateRoomPrice = function(selectElement) {
                // selectElement có thể là select hoặc hidden input
                let price = 0;
                let roomItem = null;
                let selectedOption = null;

                if (selectElement.tagName === 'SELECT') {
                    selectedOption = selectElement.options[selectElement.selectedIndex];
                    if (!selectedOption.value) {
                        // Nếu chưa chọn phòng, ẩn các phần hiển thị
                        const roomItem = selectElement.closest('.room-item');
                        const roomIndex = roomItem.getAttribute('data-room-index');
                        document.querySelector(`#room_name_${roomIndex}`).closest('.selected-room-details').classList.add('hidden');
                        document.querySelector(`#room_quantity_${roomIndex}`).closest('.quantity-section').classList.add('hidden');
                        document.getElementById(`subtotal_section_${roomIndex}`).classList.add('hidden');
                        return;
                    }
                    price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                    roomItem = selectElement.closest('.room-item');

                    // Hiển thị thông tin phòng đã chọn
                    const roomIndex = roomItem.getAttribute('data-room-index');
                    const tenLoai = selectedOption.getAttribute('data-ten-loai');
                    const anh = selectedOption.getAttribute('data-anh');
                    const soLuongTrong = parseInt(selectedOption.getAttribute('data-so-luong-trong')) || 0;
                    const giaKhuyenMai = parseFloat(selectedOption.getAttribute('data-gia-khuyen-mai')) || 0;
                    const giaCoBan = parseFloat(selectedOption.getAttribute('data-gia-co-ban')) || 0;

                    // Cập nhật tên phòng
                    document.getElementById(`room_name_${roomIndex}`).textContent = tenLoai;

                    // Cập nhật giá
                    const priceDiv = document.getElementById(`room_price_${roomIndex}`);
                    const formatNumber = (num) => Math.round(num).toLocaleString('vi-VN');

                    if (giaKhuyenMai > 0) {
                        priceDiv.innerHTML = `
                            <span class="text-red-600 font-bold text-lg">${formatNumber(giaKhuyenMai)}</span>
                            <span class="text-gray-500 line-through text-sm">${formatNumber(giaCoBan)}</span>
                            <span class="text-gray-600 text-sm">VNĐ/đêm</span>
                        `;
                    } else {
                        priceDiv.innerHTML = `
                            <span class="text-blue-600 font-bold text-lg">${formatNumber(giaCoBan)}</span>
                            <span class="text-gray-600 text-sm">VNĐ/đêm</span>
                        `;
                    }

                    // Cập nhật số lượng còn lại chỉ khi chưa có thông tin từ API (chưa có ngày)
                    const availabilityEl = document.getElementById(`room_availability_${roomIndex}`);
                    const checkin = checkinInput?.value;
                    const checkout = checkoutInput?.value;
                    // Chỉ cập nhật nếu chưa có ngày hoặc thông tin chưa được cập nhật từ API
                    if (availabilityEl && (!checkin || !checkout || !availabilityEl.innerHTML.includes('(từ'))) {
                        availabilityEl.innerHTML = `
                            <i class="fas fa-bed text-blue-500"></i> Còn ${soLuongTrong} phòng trống
                        `;
                    }

                    // Cập nhật ảnh
                    const imageDiv = document.getElementById(`room_image_${roomIndex}`);
                    if (anh && anh.trim() !== '') {
                        // Đảm bảo đường dẫn ảnh đúng (nếu chưa có / thì thêm)
                        const imagePath = anh.startsWith('/') ? anh : '/' + anh;
                        imageDiv.innerHTML = `<img src="${imagePath}" alt="${tenLoai}" class="w-20 h-20 object-cover rounded-lg ml-3">`;
                    } else {
                        imageDiv.innerHTML = '';
                    }

                    // Hiển thị card thông tin phòng
                    document.querySelector(`#room_name_${roomIndex}`).closest('.selected-room-details').classList.remove('hidden');

                    // Cập nhật số lượng tối đa và hiển thị phần quantity
                    const quantityInput = document.getElementById(`room_quantity_${roomIndex}`);
                    if (quantityInput) {
                        quantityInput.setAttribute('data-max', soLuongTrong);
                        quantityInput.setAttribute('oninput', `validateRoomQuantity(this, ${roomIndex})`);
                        const maxQuantityEl = document.getElementById(`max_quantity_${roomIndex}`);
                        if (maxQuantityEl) {
                            // Cập nhật chỉ số, không thêm "phòng" vì đã có trong HTML template
                            maxQuantityEl.textContent = `${soLuongTrong}`;
                        }
                        const quantityErrorEl = document.getElementById(`quantity_error_${roomIndex}`);
                        if (quantityErrorEl) {
                            quantityErrorEl.textContent = `Số lượng không được vượt quá ${soLuongTrong} phòng`;
                        }
                        quantityInput.closest('.quantity-section').classList.remove('hidden');
                        const subtotalSection = document.getElementById(`subtotal_section_${roomIndex}`);
                        if (subtotalSection) {
                            subtotalSection.classList.remove('hidden');
                        }
                        // Cập nhật giá ngay lập tức
                        if (typeof updateRoomPrice === 'function') {
                            updateRoomPrice(selectElement);
                        }
                    }

                    // Cập nhật các hàm onclick với maxQuantity mới
                    const decreaseBtn = quantityInput.closest('.quantity-section').querySelector('button[onclick*="decreaseRoomQuantity"]');
                    const increaseBtn = quantityInput.closest('.quantity-section').querySelector('button[onclick*="increaseRoomQuantity"]');
                    decreaseBtn.setAttribute('onclick', `decreaseRoomQuantity(${roomIndex})`);
                    increaseBtn.setAttribute('onclick', `increaseRoomQuantity(${roomIndex})`);

                } else if (selectElement.tagName === 'INPUT' && selectElement.type === 'hidden') {
                    price = parseFloat(selectElement.getAttribute('data-price')) || 0;
                    roomItem = selectElement.closest('.room-item');
                }

                if (!roomItem) return;

                const quantityInput = roomItem.querySelector('.room-quantity');
                const roomIndex = roomItem.getAttribute('data-room-index');
                const subtotalSpan = document.getElementById('room_subtotal_' + roomIndex);

                if (quantityInput && subtotalSpan) {
                    const quantity = parseInt(quantityInput.value) || 1;
                    const { soDem } = getDatesAndDays();
                    const subtotal = price * quantity * soDem;
                    // Format số và thêm VNĐ
                    const formattedSubtotal = Math.round(subtotal).toLocaleString('vi-VN');
                    // Đảm bảo không lặp VNĐ
                    subtotalSpan.textContent = formattedSubtotal + ' VNĐ';
                }

                updateTotalPrice();
            };

            // 4.5. Hàm tăng số lượng phòng
            window.increaseRoomQuantity = function(roomIndex) {
                const quantityInput = document.getElementById('room_quantity_' + roomIndex);
                if (!quantityInput) return;

                const maxQuantity = parseInt(quantityInput.getAttribute('data-max')) || 0;
                const currentValue = parseInt(quantityInput.value) || 1;

                if (maxQuantity > 0 && currentValue < maxQuantity) {
                    quantityInput.value = currentValue + 1;
                    // Trigger change event để cập nhật tất cả
                    quantityInput.dispatchEvent(new Event('change'));
                    // Gọi hàm cập nhật
                    if (typeof updateRoomQuantity === 'function') {
                        updateRoomQuantity(roomIndex);
                    }
                } else if (maxQuantity === 0) {
                    // Nếu chưa có max (chưa load xong), vẫn cho phép tăng
                    quantityInput.value = currentValue + 1;
                    if (typeof updateRoomQuantity === 'function') {
                        updateRoomQuantity(roomIndex);
                    }
                }
            };

            // 4.6. Hàm giảm số lượng phòng
            window.decreaseRoomQuantity = function(roomIndex) {
                const quantityInput = document.getElementById('room_quantity_' + roomIndex);
                if (!quantityInput) return;

                const currentValue = parseInt(quantityInput.value) || 1;
                if (currentValue > 1) {
                    quantityInput.value = currentValue - 1;
                    // Trigger change event để cập nhật tất cả
                    quantityInput.dispatchEvent(new Event('change'));
                    // Gọi hàm cập nhật
                    if (typeof updateRoomQuantity === 'function') {
                        updateRoomQuantity(roomIndex);
                    }
                }
            };


            // 4.8. Hàm cập nhật khi thay đổi số lượng (cho input onchange)
            window.updateRoomQuantity = function(roomIndex) {
                const quantityInput = document.getElementById('room_quantity_' + roomIndex);
                if (!quantityInput) return;

                // Kiểm tra và giới hạn số lượng theo max
                const maxQuantity = parseInt(quantityInput.getAttribute('data-max')) || 0;
                let currentQuantity = parseInt(quantityInput.value) || 1;

                // Nếu vượt quá max và max > 0, điều chỉnh về max
                if (maxQuantity > 0 && currentQuantity > maxQuantity) {
                    quantityInput.value = maxQuantity;
                    currentQuantity = maxQuantity;
                }

                // Nếu nhỏ hơn 1, điều chỉnh về 1
                if (currentQuantity < 1) {
                    quantityInput.value = 1;
                    currentQuantity = 1;
                }

                const roomItem = quantityInput.closest('.room-item');
                const select = roomItem.querySelector('.room-type-select');

                if (!select || !select.value) {
                    // Nếu không có select, thử lấy giá từ subtotal hiện tại hoặc từ data attribute
                    const subtotalSpan = document.getElementById('room_subtotal_' + roomIndex);
                    if (subtotalSpan && subtotalSpan.textContent && subtotalSpan.textContent !== '0') {
                        // Nếu đã có giá, tính lại dựa trên số lượng mới
                        const currentPriceText = subtotalSpan.textContent.replace(/[^\d]/g, '');
                        const currentPrice = parseInt(currentPriceText) || 0;
                        if (currentPrice > 0) {
                            const { soDem } = getDatesAndDays();
                            // Tính giá mỗi phòng mỗi đêm từ tổng hiện tại
                            const pricePerNight = currentPrice / (parseInt(quantityInput.value) || 1) / soDem;
                            const newSubtotal = pricePerNight * currentQuantity * soDem;
                            const formattedSubtotal = Math.round(newSubtotal).toLocaleString('vi-VN');
                            subtotalSpan.textContent = formattedSubtotal + ' VNĐ';
                        }
                    }
                    return;
                }

                // Lấy giá từ select hoặc hidden input
                let price = 0;
                if (select.tagName === 'SELECT') {
                    const selectedOption = select.options[select.selectedIndex];
                    if (selectedOption) {
                        price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                    }
                } else if (select.type === 'hidden') {
                    price = parseFloat(select.getAttribute('data-price')) || 0;
                }

                // Cập nhật giá cho phòng này (KHÔNG gọi updateRoomPrice hay updateRoomAvailability để giữ nguyên thông tin phòng trống)
                if (price > 0) {
                    const subtotalSpan = document.getElementById('room_subtotal_' + roomIndex);
                    if (subtotalSpan) {
                        const { soDem } = getDatesAndDays();
                        const subtotal = price * currentQuantity * soDem;
                        const formattedSubtotal = Math.round(subtotal).toLocaleString('vi-VN');
                        subtotalSpan.textContent = formattedSubtotal + ' VNĐ';
                    }
                }

                // Cập nhật summary trong sidebar
                const summaryEl = document.getElementById(`room_${roomIndex}_summary_quantity`);
                if (summaryEl) {
                    summaryEl.textContent = `x${currentQuantity}`;
                }

                // Cập nhật tổng tiền
                if (typeof updateTotalPrice === 'function') {
                    updateTotalPrice();
                }
                if (typeof tinhTongTien === 'function') {
                    tinhTongTien();
                }
            };

            // 6. Hàm tính tổng giá từ tất cả các loại phòng được chọn
            window.updateTotalPrice = function() {
                const { soDem } = getDatesAndDays();
                let totalBeforeDiscount = 0;
                const roomsSummary = [];

                // Tính tổng giá từ tất cả các loại phòng
                document.querySelectorAll('.room-item').forEach(function(roomItem) {
                    const select = roomItem.querySelector('.room-type-select');
                    const quantityInput = roomItem.querySelector('.room-quantity');

                    if (select && select.value && quantityInput) {
                        let price = 0;
                        let roomName = '';

                        // Nếu là select dropdown
                        if (select.tagName === 'SELECT') {
                            const selectedOption = select.options[select.selectedIndex];
                            price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                            roomName = selectedOption.getAttribute('data-ten-loai') || '';
                        }
                        // Nếu là hidden input
                        else if (select.tagName === 'INPUT' && select.type === 'hidden') {
                            price = parseFloat(select.getAttribute('data-price')) || 0;
                            roomName = select.getAttribute('data-room-type-name') || '';
                        }

                        const quantity = parseInt(quantityInput.value) || 1;
                        totalBeforeDiscount += price * quantity * soDem;

                        if (roomName) {
                            roomsSummary.push({
                                name: roomName,
                                quantity: quantity
                            });
                        }
                    }
                });

                // Cập nhật danh sách phòng đã chọn
                const roomsSummaryList = document.getElementById('roomsSummaryList');
                if (roomsSummaryList) {
                    if (roomsSummary.length > 0) {
                        roomsSummaryList.innerHTML = roomsSummary.map(room =>
                            `<div class="text-gray-600"><span class="font-medium">${room.name}</span> x${room.quantity}</div>`
                        ).join('');
                        document.getElementById('selectedRoomsSummary').classList.remove('hidden');
                    } else {
                        document.getElementById('selectedRoomsSummary').classList.add('hidden');
                    }
                }

                // Áp dụng voucher discount nếu có
                const discountPercent = currentDiscountPercent;
                let discountAmount = 0;
                let totalAfterDiscount = totalBeforeDiscount;

                if (discountPercent > 0) {
                    discountAmount = totalBeforeDiscount * (discountPercent / 100);
                    totalAfterDiscount = Math.max(0, totalBeforeDiscount - discountAmount);
                }

                // Cập nhật giao diện
                soDemLuuTruElement.textContent = `Số đêm: ${soDem} đêm`;
                totalAfterDiscountDiv.innerHTML = `Tổng: ${formatCurrency(totalAfterDiscount)}`;
                totalAfterDiscountDiv.classList.add('text-xl', 'font-bold', 'text-red-600');

                if (discountPercent > 0) {
                    totalBeforeDiscountDiv.innerHTML =
                        `Giá gốc: <span class="line-through text-gray-500">${formatCurrency(totalBeforeDiscount)}</span>`;
                    totalBeforeDiscountDiv.classList.remove('hidden');
                } else {
                    totalBeforeDiscountDiv.classList.add('hidden');
                }

                finalBookingPriceInput.value = Math.round(totalAfterDiscount);
            };

            // 7. Khởi tạo tính toán lần đầu (sau khi tất cả hàm đã được định nghĩa)
            tinhTongTien();

            // 10. Cập nhật giá cho phòng mặc định khi page load
            setTimeout(function() {
                // Cập nhật số phòng trống cho tất cả các phòng nếu có ngày
                if (checkinInput && checkoutInput && checkinInput.value && checkoutInput.value) {
                    updateAvailableCount();
                }

                // Cập nhật giá cho tất cả các phòng
                const defaultRoomSelect = document.querySelector('.room-type-select');
                if (defaultRoomSelect && defaultRoomSelect.value) {
                    updateRoomPrice(defaultRoomSelect);
                }
                // Cập nhật subtotal cho tất cả các phòng
                document.querySelectorAll('.room-item').forEach(function(roomItem) {
                    const hiddenInput = roomItem.querySelector('.room-type-select[type="hidden"]');
                    if (hiddenInput) {
                        updateRoomPrice(hiddenInput);
                    }
                });
            }, 100);

            // 11. Thêm loại phòng mới
            const addRoomBtn = document.getElementById('addRoomBtn');
            const roomsContainer = document.getElementById('roomsContainer');
            let roomIndex = 1; // Bắt đầu từ 1 vì phòng đầu tiên là index 0

            // Lấy danh sách loại phòng từ PHP
            const allLoaiPhongs = @json($allLoaiPhongs);

            addRoomBtn.addEventListener('click', function() {
                // Tạo HTML cho phòng mới
                const newRoomHtml = `
                    <div class="room-item mb-4 p-4 border-2 border-gray-200 rounded-lg bg-gray-50" data-room-index="${roomIndex}">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium text-gray-900">Loại phòng ${roomIndex + 1}</h4>
                            <button type="button" onclick="removeRoom(${roomIndex})" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                <i class="fas fa-times mr-1"></i> Xóa
                            </button>
                        </div>

                        {{-- Dropdown chọn loại phòng --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">Chọn loại phòng *</label>
                            <select name="rooms[${roomIndex}][loai_phong_id]"
                                    id="room_type_select_${roomIndex}"
                                    class="room-type-select w-full border-2 border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('rooms.${roomIndex}.loai_phong_id') border-red-500 @enderror"
                                    onchange="handleRoomTypeChange(${roomIndex})">
                                <option value="">-- Chọn loại phòng --</option>
                                @foreach($allLoaiPhongs as $lp)
                                    @php
                                        $lpDisplayPrice = $lp->gia_khuyen_mai ?? $lp->gia_co_ban ?? 0;
                                    @endphp
                                    <option value="{{ $lp->id }}"
                                            data-price="{{ $lpDisplayPrice }}"
                                            data-base-price="{{ $lp->gia_co_ban ?? 0 }}"
                                            data-ten-loai="{{ $lp->ten_loai }}"
                                            data-anh="{{ $lp->anh ?? '' }}"
                                            data-so-luong-trong="{{ $lp->so_luong_phong }}"
                                            data-gia-khuyen-mai="{{ $lp->gia_khuyen_mai ?? 0 }}"
                                            data-gia-co-ban="{{ $lp->gia_co_ban ?? 0 }}">
                                        {{ $lp->ten_loai }} - {{ number_format($lpDisplayPrice, 0, ',', '.') }} VNĐ/đêm
                                    </option>
                                @endforeach
                            </select>
                            @error('rooms.${roomIndex}.loai_phong_id')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Room Type Info Card (ẩn ban đầu) --}}
                        <div class="mb-4 bg-white rounded-lg border border-gray-200 overflow-hidden hidden selected-room-details" id="room_details_${roomIndex}">
                            <div class="flex flex-col md:flex-row">
                                {{-- Room Image --}}
                                <div class="md:w-48 w-full h-48 md:h-auto flex-shrink-0" id="room_image_${roomIndex}">
                                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400 text-4xl"></i>
                                    </div>
                                </div>
                                {{-- Room Info --}}
                                <div class="flex-1 p-4">
                                    <h5 class="font-semibold text-lg text-gray-900 mb-2" id="room_name_${roomIndex}"></h5>
                                    <div class="flex items-center gap-2 mb-2" id="room_price_${roomIndex}"></div>
                                    <p class="text-sm text-gray-600" id="room_availability_${roomIndex}">
                                        <i class="fas fa-bed text-blue-500"></i> Đang tải...
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Quantity Selection (ẩn ban đầu) --}}
                        <div class="quantity-section hidden">
                            <label class="block text-sm font-medium mb-2">Số lượng phòng *</label>
                            <div class="flex items-center gap-3">
                                <button type="button"
                                    class="w-10 h-10 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md transition-colors font-bold text-lg"
                                    onclick="decreaseRoomQuantity(${roomIndex})">
                                    −
                                </button>
                                <input type="text"
                                    name="rooms[${roomIndex}][so_luong]"
                                    id="room_quantity_${roomIndex}"
                                    value="1"
                                    data-max="0"
                                    class="room-quantity w-20 text-center border-2 border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-semibold"
                                    onchange="updateRoomQuantity(${roomIndex})">
                                <button type="button"
                                    class="w-10 h-10 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md transition-colors font-bold text-lg"
                                    onclick="increaseRoomQuantity(${roomIndex})">
                                    +
                                </button>
                                <span class="text-sm text-gray-600 ml-2">
                                    / <span id="max_quantity_${roomIndex}">0</span> phòng
                                </span>
                            </div>
                            <p class="text-xs text-red-600 mt-1 hidden" id="quantity_error_${roomIndex}">
                                Số lượng không được vượt quá 0 phòng
                            </p>
                        </div>

                        {{-- Subtotal (ẩn ban đầu) --}}
                        <div class="mt-3 text-sm text-gray-700 hidden" id="subtotal_section_${roomIndex}">
                            <span class="room-subtotal font-medium">Giá: <span id="room_subtotal_${roomIndex}">0</span></span>
                        </div>
                    </div>
                `;

                // Thêm phòng mới vào container
                roomsContainer.insertAdjacentHTML('beforeend', newRoomHtml);
                roomIndex++;
            });

            // 12. Xóa phòng
            window.removeRoom = function(index) {
                const roomItem = document.querySelector(`.room-item[data-room-index="${index}"]`);
                if (roomItem) {
                    roomItem.remove();
                    updateTotalPrice();
                    tinhTongTien();
                }
            };

            // 13. Xử lý khi thay đổi loại phòng
            window.handleRoomTypeChange = function(index) {
                const select = document.getElementById(`room_type_select_${index}`);
                if (!select || !select.value) return;

                const selectedOption = select.options[select.selectedIndex];
                const loaiPhongId = selectedOption.value;
                const tenLoai = selectedOption.getAttribute('data-ten-loai');
                const anh = selectedOption.getAttribute('data-anh');
                const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                const basePrice = parseFloat(selectedOption.getAttribute('data-base-price')) || 0;
                const giaKhuyenMai = parseFloat(selectedOption.getAttribute('data-gia-khuyen-mai')) || 0;
                const giaCoBan = parseFloat(selectedOption.getAttribute('data-gia-co-ban')) || 0;

                // Cập nhật hiển thị thông tin phòng
                document.getElementById(`room_name_${index}`).textContent = tenLoai;

                // Cập nhật giá
                const priceDiv = document.getElementById(`room_price_${index}`);
                const formatNumber = (num) => Math.round(num).toLocaleString('vi-VN');
                if (giaKhuyenMai > 0) {
                    priceDiv.innerHTML = `
                        <span class="text-red-600 font-bold text-lg">${formatNumber(giaKhuyenMai)}</span>
                        <span class="text-gray-500 line-through text-sm">${formatNumber(giaCoBan)}</span>
                        <span class="text-gray-600 text-sm">VNĐ/đêm</span>
                    `;
                } else {
                    priceDiv.innerHTML = `
                        <span class="text-blue-600 font-bold text-lg">${formatNumber(giaCoBan)}</span>
                        <span class="text-gray-600 text-sm">VNĐ/đêm</span>
                    `;
                }

                // Cập nhật ảnh
                const imageDiv = document.getElementById(`room_image_${index}`);
                if (anh && anh.trim() !== '') {
                    const imagePath = anh.startsWith('/') ? anh : '/' + anh;
                    imageDiv.innerHTML = `<img src="${imagePath}" alt="${tenLoai}" class="w-full h-full object-cover">`;
                } else {
                    imageDiv.innerHTML = `<div class="w-full h-full bg-gray-200 flex items-center justify-center"><i class="fas fa-image text-gray-400 text-4xl"></i></div>`;
                }

                // Hiển thị card thông tin phòng
                document.getElementById(`room_details_${index}`).classList.remove('hidden');

                // Hiển thị phần quantity trước
                const quantityInput = document.getElementById(`room_quantity_${index}`);
                if (quantityInput) {
                    quantityInput.closest('.quantity-section').classList.remove('hidden');
                    document.getElementById(`subtotal_section_${index}`).classList.remove('hidden');
                }

                // Lấy số phòng trống theo ngày (sử dụng hàm chung)
                const checkin = checkinInput?.value;
                const checkout = checkoutInput?.value;
                if (checkin && checkout) {
                    // Sử dụng hàm chung để cập nhật availability
                    if (typeof updateRoomAvailability === 'function') {
                        updateRoomAvailability(index, loaiPhongId, checkin, checkout);
                    }
                } else {
                    // Nếu chưa có ngày, dùng số lượng phòng tổng
                    const soLuongTrong = parseInt(selectedOption.getAttribute('data-so-luong-trong')) || 0;
                    const availabilityEl = document.getElementById(`room_availability_${index}`);
                    if (availabilityEl) {
                        availabilityEl.innerHTML = `
                            <i class="fas fa-bed text-blue-500"></i> Còn ${soLuongTrong} phòng (vui lòng chọn ngày để xem số phòng trống)
                        `;
                    }
                    if (quantityInput) {
                        quantityInput.setAttribute('data-max', soLuongTrong);
                        const maxQuantityEl = document.getElementById(`max_quantity_${index}`);
                        if (maxQuantityEl) {
                            // Cập nhật chỉ số, không thêm "phòng" vì đã có trong HTML template
                            maxQuantityEl.textContent = `${soLuongTrong}`;
                        }
                        const quantityErrorEl = document.getElementById(`quantity_error_${index}`);
                        if (quantityErrorEl) {
                            quantityErrorEl.textContent = `Số lượng không được vượt quá ${soLuongTrong} phòng`;
                        }
                    }
                }

                // Tính lại giá
                if (typeof updateRoomPrice === 'function') {
                    updateRoomPrice(select);
                }

                // Cập nhật tổng tiền
                updateTotalPrice();
                tinhTongTien();
            };
        });
    </script>
@endsection
