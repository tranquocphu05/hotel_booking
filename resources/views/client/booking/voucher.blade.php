<div id="voucherPopupContent"
    class="fixed inset-0 bg-black bg-opacity-30 flex justify-center items-center z-50 hidden custom-voucher-wrapper">
    <div
        class="custom-voucher-inner bg-white w-full max-w-xl rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-fadeIn font-inter">

        {{-- HEADER --}}
        <div
            class="relative px-6 py-3 border-b border-gray-200 flex items-center justify-center bg-gradient-to-r from-sky-500 to-blue-600">
            <h2 class="text-lg font-semibold text-white tracking-wide">🎫 Ưu đãi đặc biệt cho kỳ nghỉ của bạn</h2>
            <button id="closeVoucherPopup"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-white hover:text-sky-100 text-3xl font-light leading-none"
                aria-label="Đóng">&times;</button>
        </div>

        {{-- FORM TÌM VOUCHER --}}
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

            {{-- PHẦN HIỆN THÔNG BÁO (ĐÃ CĂN GIỮA & STYLED) --}}
            <div id="voucherAlertMessage" class="hidden text-center mt-2">
                {{-- Ví dụ: Success --}}
                <p class="text-sm py-2 px-3 rounded-lg inline-block bg-sky-100 text-sky-800 font-medium">
                    <i class="fa fa-check-circle mr-1"></i> Mã voucher đã được áp dụng thành công!
                </p>
                {{-- Ví dụ: Error (Nếu lỗi, bạn thay đổi class như sau) --}}
                {{--
                <p class="text-sm py-2 px-3 rounded-lg inline-block bg-red-100 text-red-700 font-medium">
                    <i class="fa fa-times-circle mr-1"></i> Mã voucher không hợp lệ hoặc đã hết hạn.
                </p>
                --}}
            </div>
        </div>

        <div class="p-4 space-y-3 overflow-y-auto custom-scrollbar bg-white" style="max-height: 350px;">
            @php
                use Carbon\Carbon;

                $selectedIds = collect($selectedRoomTypeIds ?? [])->map(fn($id) => (int) $id)->filter()->unique()->values();
                $cartTotal = (int) round($currentCartTotal ?? 0);
                $now = Carbon::now()->startOfDay();

                $vouchers = collect($vouchers)->sortByDesc(function ($voucher) use (
                    $selectedIds,
                    $cartTotal,
                    $now,
                ) {
                    $minCondition = (int) filter_var($voucher->dieu_kien, FILTER_SANITIZE_NUMBER_INT);
                    $isApplyToAll = empty($voucher->loai_phong_id) || $voucher->loai_phong_id == 0;
                    $voucherLoaiPhong = $voucher->loaiPhong;

                    if ($isApplyToAll) {
                        $isRoomTypeMatch = true;
                    } elseif ($voucherLoaiPhong) {
                        $isRoomTypeMatch = $selectedIds->contains((int) $voucher->loai_phong_id);
                    } else {
                        $isRoomTypeMatch = false;
                    }

                    $isMinConditionMet = $cartTotal >= $minCondition;
                    $endDate = Carbon::parse($voucher->ngay_ket_thuc)->endOfDay();
                    $isExpired = $now->greaterThan($endDate);

                    return $isMinConditionMet && $isRoomTypeMatch && !$isExpired; // true = khả dụng
                });
            @endphp

            @forelse ($vouchers as $voucher)
                @php
                    $minCondition = (int) filter_var($voucher->dieu_kien, FILTER_SANITIZE_NUMBER_INT);

                    $isApplyToAll = empty($voucher->loai_phong_id) || $voucher->loai_phong_id == 0;
                    $voucherLoaiPhong = $voucher->loaiPhong;

                    if ($isApplyToAll) {
                        $roomTypeText = 'Tất cả loại phòng';
                        $isRoomTypeMatch = true;
                    } elseif ($voucherLoaiPhong) {
                        $roomTypeText = $voucherLoaiPhong->ten_loai ?? 'Không xác định';
                        $isRoomTypeMatch = $selectedIds->contains((int) $voucher->loai_phong_id);
                    } else {
                        $roomTypeText = 'Không xác định (Lỗi dữ liệu)';
                        $isRoomTypeMatch = false;
                    }

                    $isMinConditionMet = $cartTotal >= $minCondition;
                    $endDate = Carbon::parse($voucher->ngay_ket_thuc)->endOfDay();
                    $isExpired = $now->greaterThan($endDate);

                    $daysRemaining = (int) floor(max(0, $now->diffInDays($endDate, false)));
                    $expiryText = $isExpired
                        ? 'Đã hết hạn'
                        : ($daysRemaining === 0
                            ? 'Hết hạn hôm nay'
                            : "Còn $daysRemaining ngày");

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

                <div class="custom-voucher-card flex border rounded-xl shadow-sm overflow-hidden transition hover:shadow-md {{ $isValid ? 'bg-sky-50 border-sky-600' : 'bg-gray-50 border-gray-200 opacity-70' }}"
                    data-voucher-code="{{ strtoupper($voucher->ma_voucher) }}"
                    data-voucher-percent="{{ intval($voucher->gia_tri) }}" data-min-condition="{{ $minCondition }}"
                    data-room-type-id="{{ $voucher->loai_phong_id ?? 0 }}"
                    data-is-valid="{{ $isValid ? 'true' : 'false' }}">

                    {{-- ICON --}}
                    <div
                        class="flex items-center justify-center w-20 bg-sky-600 {{ $isValid ? 'text-white' : 'bg-gray-100 text-gray-400' }} text-2xl font-semibold">
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
                                <p class="text-xs text-gray-500">
                                    Áp dụng cho:
                                    <span class="font-medium text-sky-700">{{ $roomTypeText }}</span>
                                </p>
                            </div>

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

        {{-- FOOTER (ĐÃ CĂN GIỮA) --}}
        <div class="p-3 flex items-center justify-center border-t border-gray-100 bg-gray-50">
            <p class="text-sm text-sky-800 font-medium">
                💡 Chọn mã phù hợp để tiết kiệm hơn cho kỳ nghỉ của bạn!
            </p>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #0ea5e9;
        /* Màu xanh nước biển cho scrollbar */
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #e0f2fe;
        /* Nền xanh nhạt cho track của scrollbar */
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
