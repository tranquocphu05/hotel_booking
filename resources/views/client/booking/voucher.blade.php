@php
    use Carbon\Carbon;

    // GIẢ ĐỊNH DỮ LIỆU ĐƯỢC TRUYỀN TỪ CONTROLLER
    $currentRoomTypeId = (int) ($roomTypeId ?? 0);
    $cartTotal = (int) round($currentCartTotal ?? 0);
    $now = Carbon::now()->startOfDay();

    $availableVouchers = collect();
    $unavailableVouchers = collect();

    // === PHÂN LOẠI VOUCHER VÀO 2 DANH SÁCH KHÁC NHAU ===
    foreach ($vouchers as $voucher) {
        $minCondition = (int) filter_var($voucher->dieu_kien, FILTER_SANITIZE_NUMBER_INT);

        // 1. KIỂM TRA LOẠI PHÒNG
        $isApplyToAll = empty($voucher->loai_phong_id) || $voucher->loai_phong_id == 0;
        $voucherLoaiPhong = $voucher->loaiPhong;

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

        // 2. KIỂM TRA ĐIỀU KIỆN TỔNG TIỀN
        $isMinConditionMet = $cartTotal >= $minCondition;

        // 3. KIỂM TRA HẠN SỬ DỤNG
        $endDate = Carbon::parse($voucher->ngay_ket_thuc)->endOfDay();
        $isExpired = $now->greaterThan($endDate);
        $daysRemaining = (int) floor(max(0, $now->diffInDays($endDate, false)));

        $expiryText = $isExpired
            ? 'Đã hết hạn'
            : ($daysRemaining === 0
                ? 'Hết hạn hôm nay'
                : "Còn $daysRemaining ngày");

        // TỔNG HỢP KẾT QUẢ
        $isValid = $isMinConditionMet && $isRoomTypeMatch && !$isExpired;

        $statusText = 'Chưa đủ ĐK';
        if ($isExpired) {
            $statusText = 'Hết hạn';
        } elseif (!$isRoomTypeMatch) {
            $statusText = 'Không áp dụng';
        } elseif (!$isMinConditionMet) {
            $statusText = 'Chưa đủ ĐK';
        }

        // Thêm các thông tin đã xử lý vào object voucher
        $voucher->roomTypeText = $roomTypeText;
        $voucher->minCondition = $minCondition;
        $voucher->expiryText = $expiryText;
        $voucher->statusText = $statusText;

        if ($isValid) {
            $availableVouchers->push($voucher);
        } else {
            $unavailableVouchers->push($voucher);
        }
    }
@endphp

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

        {{-- FORM TÌM VOUCHER (GIỮ NGUYÊN) --}}
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
            <div id="voucherAlertMessage" class="hidden px-4 mt-2">
                <p class="text-sm"></p>
            </div>
        </div>

        {{-- DANH SÁCH VOUCHER --}}
        <div class="p-4 space-y-4 overflow-y-auto custom-scrollbar bg-white" style="max-height: 350px;">

            {{-- === 1. VOUCHER ÁP DỤNG ĐƯỢC (AVAILABLE) === --}}
            @if ($availableVouchers->isNotEmpty())
                <h3 class="text-base font-bold text-green-600 border-b border-green-200 pb-2">✅ Ưu đãi khả dụng
                    ({{ $availableVouchers->count() }})</h3>
                <div class="space-y-3">
                    @foreach ($availableVouchers as $voucher)
                        <div class="custom-voucher-card flex border border-green-400 rounded-xl shadow-md overflow-hidden transition hover:shadow-lg bg-green-50"
                            data-voucher-code="{{ strtoupper($voucher->ma_voucher) }}"
                            data-voucher-percent="{{ intval($voucher->gia_tri) }}"
                            data-min-condition="{{ $voucher->minCondition }}"
                            data-room-type-id="{{ $voucher->loai_phong_id ?? 0 }}" data-is-valid="true">

                            {{-- ICON --}}
                            <div
                                class="flex items-center justify-center w-20 bg-green-200 text-green-700 text-2xl font-semibold">
                                <i class="fa fa-gift"></i>
                            </div>

                            {{-- NỘI DUNG --}}
                            <div class="flex-1 p-3 flex flex-col justify-between">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold text-green-800 text-base">
                                            Giảm {{ intval($voucher->gia_tri) }}%
                                        </p>
                                        <p class="text-xs text-gray-600 mt-1">
                                            Tổng giá phòng tối thiểu từ
                                            **{{ number_format($voucher->minCondition, 0, ',', '.') }}đ**
                                        </p>
                                        {{-- HIỂN THỊ TÊN LOẠI PHÒNG --}}
                                        <p class="text-xs text-gray-500">
                                            Áp dụng cho: <span
                                                class="font-medium text-green-700">{{ $voucher->roomTypeText }}</span>
                                        </p>
                                    </div>

                                    {{-- Nút --}}
                                    <button
                                        class="apply-voucher-btn bg-green-600 hover:bg-green-700 text-white text-xs px-4 py-2 rounded-md font-medium transition">
                                        Áp dụng
                                    </button>
                                </div>

                                {{-- Thời gian --}}
                                <div class="mt-2 text-xs text-gray-500 flex items-center">
                                    <i class="fa fa-clock mr-1 text-green-500"></i>
                                    <span>{{ $voucher->expiryText }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 italic py-2">Hiện không có ưu đãi nào phù hợp với đơn hàng của bạn.
                </p>
            @endif

            {{-- === 2. VOUCHER KHÔNG ÁP DỤNG ĐƯỢC (UNAVAILABLE) === --}}
            @if ($unavailableVouchers->isNotEmpty())
                <h3 class="text-base font-bold text-red-600 border-b border-red-200 pb-2 mt-6">❌ Ưu đãi không khả dụng
                    ({{ $unavailableVouchers->count() }})</h3>
                <div class="space-y-3 opacity-60">
                    @foreach ($unavailableVouchers as $voucher)
                        <div class="custom-voucher-card flex border border-gray-200 rounded-xl shadow-sm overflow-hidden bg-white"
                            data-voucher-code="{{ strtoupper($voucher->ma_voucher) }}"
                            data-voucher-percent="{{ intval($voucher->gia_tri) }}"
                            data-min-condition="{{ $voucher->minCondition }}"
                            data-room-type-id="{{ $voucher->loai_phong_id ?? 0 }}" data-is-valid="false">

                            {{-- ICON --}}
                            <div
                                class="flex items-center justify-center w-20 bg-gray-100 text-gray-400 text-2xl font-semibold">
                                <i class="fa fa-gift"></i>
                            </div>

                            {{-- NỘI DUNG --}}
                            <div class="flex-1 p-3 flex flex-col justify-between">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold text-gray-600 text-base">
                                            Giảm {{ intval($voucher->gia_tri) }}%
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Tổng giá phòng tối thiểu từ
                                            **{{ number_format($voucher->minCondition, 0, ',', '.') }}đ**
                                        </p>
                                        {{-- HIỂN THỊ TÊN LOẠI PHÒNG --}}
                                        <p class="text-xs text-gray-500">
                                            Áp dụng cho: <span
                                                class="font-medium text-gray-700">{{ $voucher->roomTypeText }}</span>
                                        </p>
                                    </div>

                                    {{-- Nút --}}
                                    <button
                                        class="bg-gray-100 text-red-500 text-xs px-4 py-2 rounded-md border border-red-300 cursor-not-allowed font-semibold">
                                        {{ $voucher->statusText }}
                                    </button>
                                </div>

                                {{-- Thời gian --}}
                                <div class="mt-2 text-xs text-gray-500 flex items-center">
                                    <i class="fa fa-clock mr-1 text-red-500"></i>
                                    <span>{{ $voucher->expiryText }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($availableVouchers->isEmpty() && $unavailableVouchers->isEmpty())
                <p class="text-center text-gray-500 italic py-4">Hiện chưa có ưu đãi nào khả dụng</p>
            @endif
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
