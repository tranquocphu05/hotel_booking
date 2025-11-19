{{-- CHECK-IN / CHECK-OUT SECTION --}}
<div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Quản Lý Check-in / Check-out
        </h2>
    </div>

    <div class="p-6">
        @if($booking->canCheckin())
            {{-- CAN CHECK-IN --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-blue-800 mb-3">
                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Booking đã thanh toán, sẵn sàng check-in
                </p>
                <form action="{{ route('admin.dat_phong.checkin', $booking->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ghi chú check-in (tùy chọn)</label>
                        <textarea name="ghi_chu_checkin" rows="2" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Ví dụ: Khách yêu cầu phòng tầng cao, view biển..."></textarea>
                    </div>
                    <button type="submit" 
                        class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition shadow-sm">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Check-in Ngay
                    </button>
                </form>
            </div>

        @elseif($booking->canCheckout())
            {{-- CAN CHECK-OUT --}}
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-green-800 mb-1">
                            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Đã check-in
                        </p>
                        <p class="text-sm text-gray-600">
                            Thời gian: {{ $booking->thoi_gian_checkin->format('d/m/Y H:i') }}
                        </p>
                        <p class="text-sm text-gray-600">
                            Nhân viên: {{ $booking->nguoi_checkin }}
                        </p>
                        @if($booking->ghi_chu_checkin)
                            <p class="text-sm text-gray-600 mt-1">
                                Ghi chú: {{ $booking->ghi_chu_checkin }}
                            </p>
                        @endif
                    </div>
                </div>

                <form action="{{ route('admin.dat_phong.checkout', $booking->id) }}" method="POST" class="border-t border-green-200 pt-4">
                    @csrf
                    <h3 class="font-medium text-gray-900 mb-3">Thông tin check-out</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phụ phí phát sinh (nếu có)</label>
                            <input type="number" name="phi_phat_sinh" step="0.01" min="0" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="0">
                            <p class="text-xs text-gray-500 mt-1">Ví dụ: Hư hỏng đồ đạc, minibar...</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lý do phụ phí</label>
                            <input type="text" name="ly_do_phi" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Mô tả lý do...">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ghi chú check-out</label>
                        <textarea name="ghi_chu_checkout" rows="2" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Tình trạng phòng, đồ đạc..."></textarea>
                    </div>

                    @php
                        $expectedCheckout = \Carbon\Carbon::parse($booking->ngay_tra)->setTime(12, 0);
                        $now = now();
                        $isLate = $now->gt($expectedCheckout);
                        $hoursLate = $isLate ? $now->diffInHours($expectedCheckout) : 0;
                    @endphp

                    @if($isLate)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                            <p class="text-sm text-yellow-800 font-medium">
                                ⚠️ Check-out muộn {{ $hoursLate }} giờ
                            </p>
                            <p class="text-xs text-yellow-700 mt-1">
                                @if($hoursLate <= 6)
                                    Phụ phí: 50% giá phòng ({{ number_format($booking->tong_tien * 0.5) }}đ)
                                @else
                                    Phụ phí: 100% giá phòng ({{ number_format($booking->tong_tien) }}đ)
                                @endif
                            </p>
                        </div>
                    @endif

                    <button type="submit" 
                        class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition shadow-sm">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Check-out Ngay
                    </button>
                </form>
            </div>

        @elseif($booking->thoi_gian_checkout)
            {{-- COMPLETED --}}
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h3 class="font-medium text-gray-900 mb-2">Check-in</h3>
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Thời gian:</span> {{ $booking->thoi_gian_checkin->format('d/m/Y H:i') }}
                        </p>
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Nhân viên:</span> {{ $booking->nguoi_checkin }}
                        </p>
                        @if($booking->ghi_chu_checkin)
                            <p class="text-sm text-gray-600 mt-1">
                                <span class="font-medium">Ghi chú:</span> {{ $booking->ghi_chu_checkin }}
                            </p>
                        @endif
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900 mb-2">Check-out</h3>
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Thời gian:</span> {{ $booking->thoi_gian_checkout->format('d/m/Y H:i') }}
                        </p>
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Nhân viên:</span> {{ $booking->nguoi_checkout }}
                        </p>
                        @if($booking->phi_phat_sinh > 0)
                            <p class="text-sm text-red-600 mt-1">
                                <span class="font-medium">Phụ phí:</span> {{ number_format($booking->phi_phat_sinh) }}đ
                            </p>
                        @endif
                        @if($booking->ghi_chu_checkout)
                            <p class="text-sm text-gray-600 mt-1">
                                <span class="font-medium">Ghi chú:</span> {{ $booking->ghi_chu_checkout }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Đã hoàn thành
                    </span>
                </div>
            </div>

        @else
            {{-- CANNOT CHECK-IN YET --}}
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <p class="text-sm text-gray-600">
                    Chưa thể check-in. Booking phải ở trạng thái "Đã xác nhận" (đã thanh toán).
                </p>
            </div>
        @endif
    </div>
</div>
