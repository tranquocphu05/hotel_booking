@extends('layouts.client')

@section('title', $phong->ten_phong ?? 'Đặt phòng')

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

        // Use promotional price if available
        $gia_mot_dem =
            $phong->co_khuyen_mai && !empty($phong->gia_khuyen_mai) && $phong->gia_khuyen_mai > 0
                ? $phong->gia_khuyen_mai
                : $phong->gia;
        $gia_mot_dem = $gia_mot_dem ?? 0;
        $tong_tien_initial = $gia_mot_dem * $so_dem; // Tổng tiền ban đầu tính bằng PHP

    @endphp
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 bg-white p-6 rounded shadow">
                @if (isset($phong->img) && $phong->img)
                    <img src="{{ asset($phong->img) }}" alt="room" class="w-full h-48 object-cover rounded mb-4">
                @else
                    <img src="/img/room/room-1.jpg" alt="room" class="w-full h-48 object-cover rounded mb-4">
                @endif

                <h3 class="text-lg font-semibold">{{ $phong->ten_phong ?? 'Room Title' }}</h3>
                <p class="text-sm text-gray-600">Loại: {{ optional($phong->loaiPhong)->ten_loai ?? '-' }}</p>

                <div class="mt-4 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="bg-green-500 text-white rounded px-2 py-1 text-xs">9.2</span>
                        <span class="text-sm text-gray-700">Tuyệt hảo · N/A đánh giá</span>
                    </div>
                    <div class="mt-4">
                        <h4 class="font-medium">Chi tiết đặt phòng của bạn</h4>
                        <p class="text-sm text-gray-700">Giá: {{ number_format($phong->gia ?? 0) }} VND</p>
                        <p class="text-sm text-gray-700" id="so-dem-luu-tru">Số đêm: *{{ $so_dem }} đêm*</p>
                    </div>

                    <a href="#" id="openVoucherLink"
                        class="inline-flex items-center space-x-2 text-indigo-600 hover:text-indigo-800 font-semibold text-sm mt-4 cursor-pointer transition">
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

                    <div id="voucherDisplay" class="text-sm text-green-600 font-medium mt-2 hidden"></div>

                    <input type="hidden" id="totalPriceBeforeDiscount" value="{{ $tong_tien_initial }}">

                    <div class="mt-4 pt-2 border-t border-gray-200">
                        <div id="totalBeforeDiscount" class="text-base hidden"></div>
                        <div id="totalAfterDiscount" class="text-xl font-bold text-red-600">
                            Tổng: {{ number_format($tong_tien_initial) }} VNĐ
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white p-6 rounded shadow">
                <h2 class="text-xl font-semibold mb-4">Nhập thông tin chi tiết của bạn</h2>

                @if (session('status'))
                    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('status') }}</div>
                @endif

                <form action="{{ route('booking.submit') }}" method="POST">
                    @csrf
                    <input type="hidden" name="phong_id" value="{{ $phong->id }}">
                    <input type="hidden" name="tong_tien_dat_phong" id="finalBookingPrice"
                        value="{{ $tong_tien_initial }}">

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
                            <input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}"
                                class="mt-1 block w-full border rounded p-2 @error('email') border-red-500 @enderror">
                            @error('email')
                                <div class="text-red-600 text-sm">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Số điện thoại</label>
                            <input type="text" name="phone" value="{{ old('phone', auth()->user()->sdt ?? '') }}"
                                class="mt-1 block w-full border rounded p-2">
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
                            <label class="block text-sm font-medium">Ngày nhận</label>
                            <input type="date" name="ngay_nhan"
                                value="{{ old('ngay_nhan', isset($checkin) ? $checkin : $ngay_nhan_carbon->format('Y-m-d')) }}"
                                class="mt-1 block w-full border rounded p-2" id="ngay_nhan_input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Ngày trả</label>
                            <input type="date" name="ngay_tra"
                                value="{{ old('ngay_tra', isset($checkout) ? $checkout : $ngay_tra_carbon->format('Y-m-d')) }}"
                                class="mt-1 block w-full border rounded p-2" id="ngay_tra_input">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium">Số người</label>
                            <input type="number" name="so_nguoi"
                                value="{{ old('so_nguoi', isset($guests) ? $guests : 1) }}" min="1"
                                class="mt-1 block w-1/6 border rounded p-2">
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
            const loaiPhongId = '{{ optional($phong->loaiPhong)->id ?? 0 }}';

            // THAY ĐỔI: openVoucherBtn -> openVoucherLink
            const openVoucherLink = document.getElementById('openVoucherLink');
            const voucherActionText = document.getElementById('voucherActionText');
            const voucherDisplayDiv = document.getElementById('voucherDisplay');
            const voucherCodeInput = document.getElementById('voucherCode');
            const discountValueInput = document.getElementById('discountValue');
            const checkinInput = document.getElementById('ngay_nhan_input');
            const checkoutInput = document.getElementById('ngay_tra_input');

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
                const {
                    soDem
                } = getDatesAndDays();
                const totalBeforeDiscountAmount = giaMotDem * soDem;
                const discountPercent = currentDiscountPercent;

                let discountAmount = 0;
                let totalAfterDiscount = totalBeforeDiscountAmount;

                if (discountPercent > 0) {
                    discountAmount = totalBeforeDiscountAmount * (discountPercent / 100);
                    totalAfterDiscount = Math.max(0, totalBeforeDiscountAmount - discountAmount);
                }

                // Cập nhật giao diện chính
                soDemLuuTruElement.textContent = `Số đêm: ${soDem} đêm`;
                totalAfterDiscountDiv.innerHTML = `Tổng: ${formatCurrency(totalAfterDiscount)}`;
                totalAfterDiscountDiv.classList.add('text-xl', 'font-bold', 'text-red-600');

                if (discountPercent > 0) {
                    const currentCode = voucherCodeInput.value || 'VOUCHER';

                    totalBeforeDiscountDiv.innerHTML =
                        `Giá gốc: <span class="line-through text-gray-500">${formatCurrency(totalBeforeDiscountAmount)}</span>`;
                    totalBeforeDiscountDiv.classList.remove('hidden');

                    // BẮT ĐẦU PHẦN ĐÃ CHỈNH SỬA MÀU: Cập nhật giao diện cho LINK TEXT
                    voucherActionText.textContent = `Đã áp dụng mã: ${currentCode}`;

                    // Vẫn giữ màu xanh nước biển (indigo) cho link hành động:
                    openVoucherLink.classList.remove('text-green-600', 'hover:text-green-800');
                    openVoucherLink.classList.add('text-indigo-600', 'hover:text-indigo-800');
                    // KẾT THÚC PHẦN ĐÃ CHỈNH SỬA MÀU

                    // Phần hiển thị chi tiết (voucherDisplayDiv) vẫn là màu xanh lá cây (green)
                    voucherDisplayDiv.innerHTML = `
                <p class="flex justify-between items-center text-green-600">
                    <span class="flex items-center font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg> 
                        Mã ${currentCode} (<span class="font-bold">- ${discountPercent}%</span>)
                    </span>
                    <button id="voucherClearLink" type="button" class="text-xs text-red-500 hover:text-red-700 font-semibold transition">
                        Hủy
                    </button>
                </p>
            `;
                    voucherDisplayDiv.classList.remove('hidden');

                    const clearLink = voucherDisplayDiv.querySelector('#voucherClearLink');
                    if (clearLink) {
                        clearLink.addEventListener('click', function() {
                            clearVoucher();
                        });
                    }

                } else {
                    totalBeforeDiscountDiv.classList.add('hidden');

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
                finalBookingForm.addEventListener('submit', function() {
                    isCompletingBooking = true;
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
            checkinInput.addEventListener('change', function() {
                clearVoucher();
                tinhTongTien();
            });
            checkoutInput.addEventListener('change', function() {
                clearVoucher();
                tinhTongTien();
            });

            // 4. Khởi tạo tính toán lần đầu (sau khi restore)
            tinhTongTien();
        });
    </script>
@endsection
