<div id="ajaxVoucherContentWrapper">
{{-- KHỐI FORM TÌM VOUCHER --}}
<div class="p-4 flex space-x-2 border-b border-gray-200" style="background-color: #f7f7f7;">
<div class="flex-1 flex items-center bg-white border rounded-lg shadow-sm px-3 py-1"
style="border-color: #ff660040;">
<span class="mr-2" style="color: #ff6600;">&#127871;</span>
{{-- CHÚ Ý ID MỚI: inlineVoucherCodeInput --}}
<input type="text" id="inlineVoucherCodeInput" placeholder="Nhập mã voucher để tìm kiếm hoặc áp dụng"
class="flex-1 outline-none text-gray-700 text-sm bg-transparent"
style="border: none; outline: none; height: 32px; width: 100%;">
</div>

    {{-- Nút Tìm kiếm --}}
    <button id="searchVoucherBtn"
        class="text-white text-sm px-4 py-2 rounded-lg hover:opacity-90 transition font-semibold flex-shrink-0 flex items-center justify-center"
        style="background-color: #ff6600; border: none; height: 40px; cursor: pointer; position: relative; z-index: 10;">
        Tìm kiếm
    </button>
</div>

{{-- KHỐI HIỂN THỊ ALERT (Cần có ID mới) --}}
<div id="voucherAlertMessage" class="px-4 pt-2 pb-0 hidden">
    <p class="text-sm font-medium p-2 rounded-lg border"></p>
</div>

{{-- Danh sách Voucher: Giới hạn chiều cao và bật cuộn --}}
{{-- CHÚ Ý ID MỚI: voucherListContainer --}}
<div id="voucherListContainer" class="p-4 space-y-3 custom-scrollbar"
    style="background-color: #f7f7f7; overflow-y: auto; max-height: 250px;">
    @php
        // Lấy biến đã được Controller truyền vào
        $cartTotal = $currentCartTotal ?? 0;
        $vouchers = $vouchers ?? [];
    @endphp

    @forelse ($vouchers as $voucher)
        @php
            // Logic tính toán điều kiện voucher
            $minCondition = (int) filter_var($voucher->dieu_kien, FILTER_SANITIZE_NUMBER_INT);
            $isMinConditionMet = $cartTotal >= $minCondition;
            $endDate = \Carbon\Carbon::parse($voucher->ngay_ket_thuc);
            $now = \Carbon\Carbon::now();
            $isExpired = $now->greaterThan($endDate);
            $daysRemaining = max(0, ceil($now->diffInDays($endDate, false)));

            $expiryText = $isExpired
                ? 'Đã hết hạn'
                : ($daysRemaining === 0
                    ? 'Hết hạn hôm nay'
                    : "Hết hạn trong $daysRemaining ngày");
            $isValid = $isMinConditionMet && !$isExpired;
            $disabledClass = $isValid ? '' : 'disabled-voucher';
            $maxDiscountText = isset($voucher->gioi_han_toi_da)
                ? '(tối đa ' . number_format($voucher->gioi_han_toi_da, 0, ',', '.') . 'đ)'
                : '';
            $voucherSourceText = isset($voucher->ma_voucher)
                ? strtoupper(substr($voucher->ma_voucher, 0, 8))
                : 'Voucher';
        @endphp

        <div class="bg-white rounded-lg p-0 shadow-sm border overflow-hidden relative custom-voucher-card {{ $disabledClass }}"
            data-voucher-code="{{ $voucher->ma_voucher }}" data-is-valid="{{ $isValid ? 'true' : 'false' }}"
            style="border-color: #e5e7eb; border-radius: 8px;">
            <div class="flex">
                <div class="flex-shrink-0 w-24 text-white flex flex-col items-center justify-center p-2 relative"
                    style="background-color: #ff6600;">
                    <span class="text-2xl mt-1">&#127873;</span>
                    <p class="text-xs mt-0.5 font-semibold text-center leading-tight">{{ $voucherSourceText }}
                    </p>
                    {{-- Đường đứt nét --}}
                    <div class="absolute w-full border-b border-dashed border-white bottom-0 left-0"></div>
                    <div class="absolute w-5 h-5 rounded-full -left-2.5 top-0 -translate-y-1/2"
                        style="background-color: #f7f7f7;"></div>
                    <div class="absolute w-5 h-5 rounded-full -left-2.5 bottom-0 translate-y-1/2"
                        style="background-color: #f7f7f7;"></div>
                </div>
                <div class="flex-grow p-3">
                    <div class="flex justify-between items-start mb-1">
                        <div>
                            <p class="font-bold text-gray-800 text-md">Giảm {{ intval($voucher->gia_tri) }}%
                                {{ $maxDiscountText }}</p>
                            <p class="text-gray-600 text-xs mt-1">Tổng tiền phòng tối thiểu
                                {{ number_format($minCondition, 0, ',', '.') }}đ</p>
                        </div>
                        {{-- LOGIC CHO NÚT ÁP DỤNG --}}
                        @if ($isValid)
                            <button
                                class="apply-voucher-btn text-white text-xs px-4 py-2 rounded-md hover:opacity-90 transition font-semibold flex-shrink-0"
                                style="background-color: #ff6600; border: none; height: 32px;">
                                Dùng ngay
                            </button>
                        @else
                            @if ($isExpired)
                                <button
                                    class="text-gray-400 text-xs px-4 py-2 rounded-md border border-gray-300 cursor-not-allowed flex-shrink-0"
                                    style="background-color: #e5e7eb; border: 1px solid #d1d5db; height: 32px;">
                                    Hết hạn
                                </button>
                            @else
                                <button
                                    class="text-gray-400 text-xs px-4 py-2 rounded-md border border-gray-300 cursor-not-allowed flex-shrink-0"
                                    style="background-color: #e5e7eb; border: 1px solid #d1d5db; height: 32px;">
                                    Áp dụng
                                </button>
                            @endif
                        @endif
                    </div>
                    <div class="text-gray-500 text-xs mt-2 flex items-center">
                        <span class="font-semibold" style="color: #3b82f6; cursor: pointer;">Điều kiện</span>
                        <span class="mx-2">|</span>
                        <span class="{{ $isExpired ? 'font-semibold' : '' }}"
                            style="color: {{ $isExpired ? '#ef4444' : '#6b7280' }};">{{ $expiryText }}</span>
                        @if (!$isMinConditionMet && !$isExpired)
                            <span class="text-xs ml-2 font-semibold" style="color: #ef4444;"> (Chưa đủ điều
                                kiện)</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <p class="text-center text-gray-500 italic py-4">Không có mã giảm giá nào khả dụng</p>
    @endforelse
</div>

{{-- Footer: Thông báo nhỏ gọn --}}
<div class="p-3 text-center border-t border-gray-200" style="background-color: #ffffff;">
    <p class="text-sm font-medium" style="color: #ff6600;">
        &#128073; Nhấn vào mã hoặc nút "Dùng ngay" để áp dụng
    </p>
</div>


</div>