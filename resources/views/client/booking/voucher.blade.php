<div id="voucherPopupContent"
    class="fixed inset-0 bg-black bg-opacity-30 flex justify-center items-center z-50 hidden custom-voucher-wrapper">
    <div
        class="custom-voucher-inner bg-white w-full max-w-md md:max-w-lg rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-fadeIn font-inter">

        {{-- HEADER (GIỮ NGUYÊN) --}}
        <div
            class="relative px-6 py-3 border-b border-gray-200 flex items-center justify-center bg-gradient-to-r from-blue-100 to-sky-50">
            <h2 class="text-lg font-semibold text-gray-800 tracking-wide">🎫 Ưu đãi đặc biệt cho kỳ nghỉ của bạn</h2>
            <button id="closeVoucherPopup"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-600 hover:text-gray-900 text-3xl font-light leading-none"
                aria-label="Đóng">&times;</button>
        </div>

        {{-- FORM TÌM VOUCHER (ĐÃ KHÔI PHỤC HOÀN TOÀN VỀ TRẠNG THÁI GỐC) --}}
        <div class="p-4 bg-gray-50 border-b border-gray-100">
            <div
                class="flex bg-white border border-gray-300 rounded-full overflow-hidden shadow-sm focus-within:ring-2 focus-within:ring-sky-400 transition">
                <input type="text" id="popupVoucherCodeInput" placeholder="🔍 Nhập mã ưu đãi của bạn..."
                    class="flex-1 text-sm text-gray-700 bg-transparent px-4 py-2 focus:outline-none">
                <button id="searchVoucherBtn"
                    class="bg-sky-600 hover:bg-sky-700 text-white text-sm px-5 font-medium transition">
                    Tìm
                </button>
            </div>
            {{-- ĐỂ GIỐNG HÌNH BẠN CUNG CẤP, CẦN ĐẢM BẢO KHÔNG CÓ NÚT HỦY NÀO Ở ĐÂY SAU KHI ÁP DỤNG MÃ --}}
            {{-- Nút "Hủy áp dụng mã hiện tại" trong hình bạn gửi là do logic JS hoặc Blade khác tạo ra,
                 hiện tại tôi loại bỏ nó để giữ nguyên form tìm kiếm như ảnh ban đầu. --}}
        </div>

        {{-- THÔNG BÁO (GIỮ NGUYÊN) --}}
        <div id="voucherAlertMessage" class="hidden px-4 mt-2">
            <p class="text-sm"></p>
        </div>

        {{-- DANH SÁCH VOUCHER (GIỮ NGUYÊN HOÀN TOÀN LOGIC VÀ GIAO DIỆN GỐC CỦA BẠN) --}}
        <div class="p-4 space-y-3 overflow-y-auto custom-scrollbar bg-white" style="max-height: 350px;">
            @php
                use Carbon\Carbon;
                // GIẢ ĐỊNH $roomTypeId là ID loại phòng đang đặt, được truyền từ Controller.
                // $currentRoomTypeId đã được truyền từ controller
                $currentRoomTypeId = (int) ($roomTypeId ?? 0);
                $cartTotal = (int) round($currentCartTotal ?? 0);
                $now = Carbon::now()->startOfDay(); // Đặt thời gian hiện tại về 00:00:00 để so sánh chuẩn hơn
            @endphp

            @forelse ($vouchers as $voucher)
                @php
                    $minCondition = (int) filter_var($voucher->dieu_kien, FILTER_SANITIZE_NUMBER_INT);

                    // === 1. KIỂM TRA LOẠI PHÒNG ===
                    $isApplyToAll = empty($voucher->loai_phong_id) || $voucher->loai_phong_id == 0;
                    $voucherLoaiPhong = $voucher->loaiPhong; // Lấy Model LoaiPhong qua quan hệ

                    if ($isApplyToAll) {
                        $roomTypeText = 'Tất cả loại phòng';
                        $isRoomTypeMatch = true;
                    } elseif ($voucherLoaiPhong) {
                        $roomTypeText = $voucherLoaiPhong->ten_loai ?? 'Không xác định';
                        $isRoomTypeMatch = $voucher->loai_phong_id == $currentRoomTypeId;
                    } else {
                        $roomTypeText = 'Không xác định (Lỗi dữ liệu)';
                        $isRoomTypeMatch = false;
                    }

                    // === 2. KIỂM TRA ĐIỀU KIỆN TỔNG TIỀN ===
                    $isMinConditionMet = $cartTotal >= $minCondition;

                    // === 3. KIỂM TRA HẠN SỬ DỤNG ===
                    $endDate = Carbon::parse($voucher->ngay_ket_thuc)->endOfDay(); // So sánh đến cuối ngày
                    $isExpired = $now->greaterThan($endDate);

                    // Tính số ngày còn lại (chỉ lấy phần nguyên)
                    // LÀM RÕ: Sử dụng floor() để đảm bảo loại bỏ phần thập phân và ép kiểu thành (int).
                    $daysRemaining = (int) floor(max(0, $now->diffInDays($endDate, false)));

                    $expiryText = $isExpired
                        ? 'Đã hết hạn'
                        : ($daysRemaining === 0
                            ? 'Hết hạn hôm nay'
                            : "Còn $daysRemaining ngày");

                    // === 4. TỔNG HỢP KẾT QUẢ ===
                    $isValid = $isMinConditionMet && $isRoomTypeMatch && !$isExpired;

                    $statusText = 'Chưa đủ ĐK';
                    if ($isExpired) {
                        $statusText = 'Hết hạn';
                    } elseif (!$isRoomTypeMatch) {
                        $statusText = 'Không áp dụng';
                    } elseif (!$isMinConditionMet) {
                        $statusText = 'Chưa đủ ĐK';
                    }
                @endphp

                <div class="custom-voucher-card flex border border-gray-200 rounded-xl shadow-sm overflow-hidden transition hover:shadow-md {{ $isValid ? 'bg-white' : 'bg-gray-50 opacity-70' }}"
                    data-voucher-code="{{ strtoupper($voucher->ma_voucher) }}"
                    data-voucher-percent="{{ intval($voucher->gia_tri) }}" data-min-condition="{{ $minCondition }}"
                    data-room-type-id="{{ $voucher->loai_phong_id ?? 0 }}"
                    data-is-valid="{{ $isValid ? 'true' : 'false' }}">

                    {{-- ICON --}}
                    <div class="flex items-center justify-center w-20 bg-sky-100 text-sky-600 text-2xl font-semibold">
                        <i class="fa fa-gift"></i>
                    </div>

                    {{-- NỘI DUNG --}}
                    <div class="flex-1 p-3 flex flex-col justify-between">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-800 text-base">
                                    Giảm {{ intval($voucher->gia_tri) }}%
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Tổng giá phòng tối thiểu từ {{ number_format($minCondition, 0, ',', '.') }}đ
                                </p>
                                {{-- HIỂN THỊ TÊN LOẠI PHÒNG --}}
                                <p class="text-xs text-gray-500">
                                    Áp dụng cho: <span class="font-medium text-sky-700">{{ $roomTypeText }}</span>
                                </p>
                            </div>

                            {{-- Nút --}}
                            @if ($isValid)
                                <button
                                    class="apply-voucher-btn bg-sky-600 hover:bg-sky-700 text-white text-xs px-4 py-2 rounded-md font-medium transition">
                                    Áp dụng
                                </button>
                            @else
                                <button
                                    class="bg-gray-100 text-gray-400 text-xs px-4 py-2 rounded-md border border-gray-200 cursor-not-allowed">
                                    {{ $statusText }}
                                </button>
                            @endif
                        </div>

                        {{-- Thời gian + điều kiện --}}
                        <div class="mt-2 text-xs text-gray-500 flex items-center">
                            <i class="fa fa-clock mr-1 {{ $isExpired ? 'text-red-500' : 'text-sky-400' }}"></i>
                            <span>{{ $expiryText }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 italic py-4">Hiện chưa có ưu đãi nào khả dụng</p>
            @endforelse
        </div>
        
        {{-- FOOTER (GIỮ NGUYÊN) --}}
        <div class="p-3 flex items-center justify-between border-t border-gray-100 bg-gray-50">
            <p class="text-sm text-sky-600 font-medium">
                💡 Chọn mã phù hợp để tiết kiệm hơn cho kỳ nghỉ của bạn!
                </p>
            </div>
        
    </div>
</div>

{{-- CSS thêm (GIỮ NGUYÊN) --}}
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #d4d4d4;
        border-radius: 10px;
    }

    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px) scale(0.97);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
</style>
