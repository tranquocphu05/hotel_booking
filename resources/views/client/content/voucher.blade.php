<div id="voucherPopupContent"
    class="fixed inset-0 bg-black bg-opacity-30 flex justify-center items-center z-50 hidden custom-voucher-wrapper">
    <div
        class="custom-voucher-inner bg-white w-full max-w-md md:max-w-lg rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-fadeIn font-inter">

        {{-- HEADER --}}
        <div class="relative px-6 py-3 border-b border-gray-200 flex items-center justify-center bg-gradient-to-r from-blue-100 to-sky-50">
            <h2 class="text-lg font-semibold text-gray-800 tracking-wide">🎫 Ưu đãi đặc biệt cho kỳ nghỉ của bạn</h2>
            <button id="closeVoucherPopup"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-600 hover:text-gray-900 text-3xl font-light leading-none"
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
        </div>

        {{-- THÔNG BÁO --}}
        <div id="voucherAlertMessage" class="hidden px-4 mt-2">
            <p class="text-sm"></p>
        </div>

        {{-- DANH SÁCH VOUCHER --}}
        <div class="p-4 space-y-3 overflow-y-auto custom-scrollbar bg-white" style="max-height: 350px;">
            @php
                // === PHẦN ĐÃ SỬA LỖI SO SÁNH: Đảm bảo $cartTotal là số nguyên và làm tròn ===
                $cartTotal = (int) round($currentCartTotal ?? 0);
            @endphp

            @forelse ($vouchers as $voucher)
                @php
                    $minCondition = (int) filter_var($voucher->dieu_kien, FILTER_SANITIZE_NUMBER_INT);
                    // Dùng $cartTotal đã được làm tròn để so sánh
                    $isMinConditionMet = $cartTotal >= $minCondition; 
                    
                    $endDate = \Carbon\Carbon::parse($voucher->ngay_ket_thuc);
                    $now = \Carbon\Carbon::now();
                    $isExpired = $now->greaterThan($endDate);
                    $daysRemaining = max(0, floor($now->diffInDays($endDate, false)));
                    $expiryText = $isExpired
                        ? 'Đã hết hạn'
                        : ($daysRemaining === 0
                            ? 'Hết hạn hôm nay'
                            : "Còn $daysRemaining ngày");
                    $isValid = $isMinConditionMet && !$isExpired;
                @endphp

                <div class="custom-voucher-card flex border border-gray-200 rounded-xl shadow-sm overflow-hidden transition hover:shadow-md {{ $isValid ? 'bg-white' : 'bg-gray-50 opacity-70' }}"
                    data-voucher-code="{{ strtoupper($voucher->ma_voucher) }}"
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
                                    Giá phòng tối thiểu từ {{ number_format($minCondition, 0, ',', '.') }}đ
                                </p>
                            </div>

                            {{-- Nút --}}
                            @if ($isValid)
                                <button
                                    class="apply-voucher-btn bg-sky-600 hover:bg-sky-700 text-white text-xs px-4 py-2 rounded-md font-medium transition">
                                    Áp dụng
                                </button>
                            @elseif ($isExpired)
                                <button
                                    class="bg-gray-100 text-gray-400 text-xs px-4 py-2 rounded-md border border-gray-200 cursor-not-allowed">
                                    Hết hạn
                                </button>
                            @else
                                <button
                                    class="bg-gray-100 text-gray-400 text-xs px-4 py-2 rounded-md border border-gray-200 cursor-not-allowed">
                                    Chưa đủ ĐK
                                </button>
                            @endif
                        </div>

                        {{-- Thời gian + điều kiện --}}
                        <div class="mt-2 text-xs text-gray-500 flex items-center">
                            <i class="fa fa-clock mr-1 text-sky-400"></i>
                            <span>{{ $expiryText }}</span>
                            @if (!$isValid && !$isExpired)
                                <span class="text-red-500 ml-1">(Chưa đủ điều kiện)</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 italic py-4">Hiện chưa có ưu đãi nào khả dụng</p>
            @endforelse
        </div>

        {{-- FOOTER --}}
        <div class="p-3 text-center border-t border-gray-100 bg-gray-50">
            <p class="text-sm text-sky-600 font-medium">
                💡 Chọn mã phù hợp để tiết kiệm hơn cho kỳ nghỉ của bạn!
            </p>
        </div>
    </div>
</div>

{{-- CSS thêm --}}
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