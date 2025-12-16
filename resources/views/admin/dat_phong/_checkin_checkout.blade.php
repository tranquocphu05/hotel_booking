{{-- CHECK-IN / CHECK-OUT SECTION --}}
<div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Quản Lý Check-in / Check-out từng phòng
        </h2>
    </div>

    <div class="p-6">
        @php
            $assignedPhongs = $booking->getAssignedPhongs();
            // Tính trạng thái tổng dựa trên pivot của từng phòng
            $allCheckedIn = $assignedPhongs->isNotEmpty() && $assignedPhongs->every(function ($phong) {
                return !is_null(optional($phong->pivot)->thoi_gian_checkin);
            });
            $allCheckedOut = $assignedPhongs->isNotEmpty() && $assignedPhongs->every(function ($phong) {
                return !is_null(optional($phong->pivot)->thoi_gian_checkout);
            });
        @endphp

        @if($assignedPhongs->isEmpty())
            {{-- NO ROOMS ASSIGNED --}}
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                <svg class="w-12 h-12 mx-auto text-yellow-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p class="text-sm text-yellow-800 font-medium">
                    Chưa gán phòng cụ thể cho booking này
                </p>
                <p class="text-xs text-yellow-700 mt-1">
                    Vui lòng gán phòng trước khi check-in
                </p>
            </div>
        @elseif($booking->trang_thai !== 'da_xac_nhan')
            {{-- CANNOT CHECK-IN YET (booking chưa ở trạng thái đã xác nhận) --}}
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <p class="text-sm text-gray-600">
                    Chưa thể check-in. Booking phải ở trạng thái "Đã xác nhận" (đã thanh toán).
                </p>
            </div>
        @else
            {{-- DISPLAY ROOMS WITH CHECK-IN/OUT STATUS --}}
            <div class="space-y-6">
                @foreach($assignedPhongs as $phong)
                    @php
                        $roomCheckinTime = optional($phong->pivot)->thoi_gian_checkin;
                        $roomCheckoutTime = optional($phong->pivot)->thoi_gian_checkout;
                        // Cho phép checkin nếu booking đã xác nhận và phòng này chưa có thoi_gian_checkin
                        $canRoomCheckin = $booking->trang_thai === 'da_xac_nhan' && is_null($roomCheckinTime);
                        // Cho phép checkout nếu đã checkin nhưng chưa checkout
                        $canRoomCheckout = $booking->trang_thai === 'da_xac_nhan' && $roomCheckinTime && !$roomCheckoutTime;
                        // Phòng coi như đã checkout khi có thoi_gian_checkout
                        $isRoomCheckedOut = !is_null($roomCheckoutTime);
                    @endphp

                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        {{-- Room Header --}}
                        <div class="px-4 py-3 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                    <h3 class="font-semibold text-gray-900">
                                        Phòng {{ $phong->so_phong }}
                                        @if($phong->ten_phong)
                                            <span class="text-gray-600 font-normal">({{ $phong->ten_phong }})</span>
                                        @endif
                                    </h3>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs px-2 py-1 rounded-full 
                                        @if($isRoomCheckedOut) bg-green-100 text-green-700
                                        @elseif($roomCheckinTime) bg-blue-100 text-blue-700
                                        @else bg-gray-100 text-gray-700
                                        @endif">
                                        @if($isRoomCheckedOut)
                                            ✓ Đã checkout
                                        @elseif($roomCheckinTime)
                                            🔑 Đã checkin
                                        @else
                                            ⏳ Chưa checkin
                                        @endif
                                    </span>
                                    <span class="text-xs text-gray-600">
                                        Tầng {{ $phong->tang ?? 'N/A' }} | {{ $phong->loaiPhong->ten_loai ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Room Body --}}
                        <div class="p-4">
                            @if($canRoomCheckin)
                                {{-- CHECK-IN FORM --}}
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <p class="text-sm text-blue-800 mb-3 flex items-center">
                                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Sẵn sàng check-in phòng này
                                    </p>
                                    
                                    @php
                                        $checkinTime = now();
                                        $standardCheckinTime = \Carbon\Carbon::parse($booking->ngay_nhan)->setTime(14, 0);
                                        $isEarly = $checkinTime->lt($standardCheckinTime);
                                        
                                        if ($isEarly) {
                                            $phiCheckinSom = \App\Services\CheckinCheckoutFeeCalculator::calculateEarlyCheckinFee($booking, $checkinTime);
                                            // Sử dụng diffInMinutes với absolute = true để đảm bảo giá trị dương
                                            $diffMinutes = abs($standardCheckinTime->diffInMinutes($checkinTime));
                                            $earlyHours = (int)floor($diffMinutes / 60);
                                            $earlyMins = (int)($diffMinutes % 60);
                                            
                                            // Format thời gian sớm
                                            if ($earlyHours > 0) {
                                                $earlyTimeText = $earlyHours . ' giờ' . ($earlyMins > 0 ? ' ' . $earlyMins . ' phút' : '');
                                            } else {
                                                $earlyTimeText = $earlyMins . ' phút';
                                            }
                                        } else {
                                            $phiCheckinSom = 0;
                                            $earlyHours = 0;
                                            $earlyMins = 0;
                                            $earlyTimeText = '';
                                        }
                                    @endphp
                                    
                                    @if($isEarly && $phiCheckinSom > 0)
                                        <div class="mb-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                            <p class="text-xs text-yellow-800 font-medium mb-1 flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                Check-in sớm {{ $earlyTimeText }} (trước {{ $standardCheckinTime->format('d/m H:i') }})
                                            </p>
                                            <p class="text-sm font-semibold text-yellow-900">
                                                Phụ phí check-in sớm: {{ number_format($phiCheckinSom, 0, ',', '.') }} VNĐ
                                            </p>
                                        </div>
                                    @endif
                                    
                                    <form action="{{ route('admin.dat_phong.checkin', $booking->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="phong_ids[]" value="{{ $phong->id }}">
                                        
                                        <div class="mb-3">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Ghi chú check-in cho phòng {{ $phong->so_phong }} (tùy chọn)
                                            </label>
                                            <textarea name="ghi_chu_checkin" rows="2" 
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                                placeholder="Ví dụ: Khách yêu cầu phòng view biển, gối thêm..."></textarea>
                                        </div>
                                        
                                        <button type="submit" 
                                            class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition shadow-sm text-sm">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Check-in Phòng {{ $phong->so_phong }}
                                        </button>
                                    </form>
                                </div>

                            @elseif($canRoomCheckout)
                                {{-- CHECK-OUT FORM --}}
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    {{-- Checkin info --}}
                                    <div class="mb-4 pb-4 border-b border-green-200">
                                        <p class="text-sm font-medium text-green-800 mb-1 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Đã check-in
                                        </p>
                                        @php
                                            // $roomCheckinTime từ pivot là string, cần parse sang Carbon trước khi format
                                            $roomCheckinTimeCarbon = $roomCheckinTime ? \Carbon\Carbon::parse($roomCheckinTime) : null;
                                        @endphp
                                        <p class="text-xs text-gray-600">
                                            Thời gian:
                                            {{ $roomCheckinTimeCarbon ? $roomCheckinTimeCarbon->format('d/m/Y H:i') : 'N/A' }}
                                        </p>
                                        <p class="text-xs text-gray-600">
                                            Nhân viên: {{ $booking->nguoi_checkin }}
                                        </p>
                                        @if($booking->ghi_chu_checkin)
                                            <p class="text-xs text-gray-600 mt-1">
                                                Ghi chú: {{ $booking->ghi_chu_checkin }}
                                            </p>
                                        @endif
                                    </div>

                                    {{-- Checkout form --}}
                                    <form action="{{ route('admin.dat_phong.checkout', $booking->id) }}" method="POST" class="checkout-form">
                                        @csrf
                                        <input type="hidden" name="phong_ids[]" value="{{ $phong->id }}">
                                        
                                        <h4 class="font-medium text-gray-900 mb-3 flex items-center text-sm">
                                            <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Thông tin check-out phòng {{ $phong->so_phong }}
                                        </h4>

                                        {{-- Thiệt hại tài sản --}}
                                        <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-3">
                                            <h5 class="text-xs font-semibold text-red-900 mb-2 flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                Thiệt hại & Phụ phí (nếu có)
                                            </h5>

                                            <div class="space-y-2">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Danh mục</label>
                                                    <select name="loai_thiet_hai" 
                                                        class="w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                                        onchange="updateDamageDesc_{{$phong->id}}(this)">
                                                        <option value="">-- Chọn (tùy chọn) --</option>
                                                        <option value="do_dac_hu_hong">Đồ đạc bị hư hỏng</option>
                                                        <option value="thiet_bi_dien">Thiết bị điện tử bị hỏng</option>
                                                        <option value="noi_that">Nội thất bị hư hỏng</option>
                                                        <option value="san_phong">Sàn phòng bị hư hỏng</option>
                                                        <option value="tuong_phong">Tường phòng bị hư hỏng</option>
                                                        <option value="cua_so_kinh">Cửa sổ/kính bị vỡ</option>
                                                        <option value="minibar_thieu">Minibar thiếu đồ</option>
                                                        <option value="do_dung_phong_thieu">Đồ dùng phòng thiếu</option>
                                                        <option value="tham_trang_tri">Thảm/trang trí bị hư</option>
                                                        <option value="phong_tam">Phòng tắm bị hư hỏng</option>
                                                        <option value="khac">Khác</option>
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Mô tả thiệt hại</label>
                                                    <textarea name="ly_do_phi" id="lyDoPhi_{{$phong->id}}" rows="2"
                                                        class="w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                                        placeholder="Mô tả chi tiết thiệt hại (bắt buộc nếu có phí)..."></textarea>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Số tiền phụ phí (VNĐ)</label>
                                                    <input type="number" name="phi_phat_sinh" id="phiPhatSinh_{{$phong->id}}" 
                                                        step="1000" min="0" value="0"
                                                        class="w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                                        placeholder="0"
                                                        oninput="validateFee_{{$phong->id}}(this)">
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Ghi chú checkout --}}
                                        <div class="mb-3">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                                Ghi chú check-out (tình trạng phòng)
                                            </label>
                                            <textarea name="ghi_chu_checkout" rows="2"
                                                class="w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                placeholder="Ví dụ: Phòng sạch sẽ, đồ đạc đầy đủ..."></textarea>
                                        </div>

                                        @php
                                            $checkoutTime = now();
                                            $standardCheckoutTime = \Carbon\Carbon::parse($booking->ngay_tra)->setTime(12, 0);
                                            $isLate = $checkoutTime->gt($standardCheckoutTime);
                                            
                                            if ($isLate) {
                                                $phiCheckoutTre = \App\Services\CheckinCheckoutFeeCalculator::calculateLateCheckoutFee($booking, $checkoutTime);
                                                // Sử dụng diffInMinutes với absolute để đảm bảo giá trị dương
                                                $diffMinutes = abs($checkoutTime->diffInMinutes($standardCheckoutTime));
                                                $lateHours = (int)floor($diffMinutes / 60);
                                                $lateMins = (int)($diffMinutes % 60);
                                                
                                                // Format thời gian trễ
                                                if ($lateHours > 0) {
                                                    $lateTimeText = $lateHours . ' giờ' . ($lateMins > 0 ? ' ' . $lateMins . ' phút' : '');
                                                } else {
                                                    $lateTimeText = $lateMins . ' phút';
                                                }
                                            } else {
                                                $phiCheckoutTre = 0;
                                                $lateHours = 0;
                                                $lateMins = 0;
                                                $lateTimeText = '';
                                            }
                                        @endphp

                                        @if($isLate && $phiCheckoutTre > 0)
                                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-3">
                                                <p class="text-xs text-yellow-800 font-medium mb-1 flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                    Check-out trễ {{ $lateTimeText }} (sau {{ $standardCheckoutTime->format('d/m H:i') }})
                                                </p>
                                                <p class="text-sm font-semibold text-yellow-900">
                                                    Phụ phí check-out trễ: {{ number_format($phiCheckoutTre, 0, ',', '.') }} VNĐ
                                                </p>
                                            </div>
                                        @endif

                                        <div class="flex justify-end">
                                            <button type="submit" 
                                                class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition shadow-sm text-sm"
                                                onclick="return confirmCheckout_{{$phong->id}}()">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                </svg>
                                                Check-out Phòng {{ $phong->so_phong }}
                                            </button>
                                        </div>
                                    </form>

                                    <script>
                                        const damageDescs_{{$phong->id}} = {
                                            'do_dac_hu_hong': 'Đồ đạc trong phòng bị hư hỏng',
                                            'thiet_bi_dien': 'Thiết bị điện tử (TV, điều hòa...) bị hỏng',
                                            'noi_that': 'Nội thất (giường, tủ, bàn ghế...) bị hư',
                                            'san_phong': 'Sàn phòng bị hư hỏng',
                                            'tuong_phong': 'Tường phòng bị hư hỏng',
                                            'cua_so_kinh': 'Cửa sổ/kính bị vỡ',
                                            'minibar_thieu': 'Minibar thiếu đồ',
                                            'do_dung_phong_thieu': 'Đồ dùng phòng thiếu',
                                            'tham_trang_tri': 'Thảm/trang trí bị hư',
                                            'phong_tam': 'Phòng tắm bị hư hỏng',
                                            'khac': ''
                                        };

                                        function updateDamageDesc_{{$phong->id}}(select) {
                                            const textarea = document.getElementById('lyDoPhi_{{$phong->id}}');
                                            const val = select.value;
                                            if (val && damageDescs_{{$phong->id}}[val] && !textarea.value.trim()) {
                                                textarea.value = damageDescs_{{$phong->id}}[val];
                                            }
                                        }

                                        function validateFee_{{$phong->id}}(input) {
                                            const fee = parseFloat(input.value) || 0;
                                            const desc = document.getElementById('lyDoPhi_{{$phong->id}}');
                                            if (fee > 0) {
                                                desc.required = true;
                                            } else {
                                                desc.required = false;
                                            }
                                        }

                                        function confirmCheckout_{{$phong->id}}() {
                                            const fee = parseFloat(document.getElementById('phiPhatSinh_{{$phong->id}}').value) || 0;
                                            const desc = document.getElementById('lyDoPhi_{{$phong->id}}').value.trim();
                                            
                                            if (fee > 0 && !desc) {
                                                alert('Vui lòng nhập mô tả thiệt hại khi có phụ phí!');
                                                document.getElementById('lyDoPhi_{{$phong->id}}').focus();
                                                return false;
                                            }
                                            
                                            if (fee > 0) {
                                                return confirm(`Xác nhận check-out phòng {{ $phong->so_phong }} với phụ phí: ${new Intl.NumberFormat('vi-VN').format(fee)}₫?\n\nLý do: ${desc}`);
                                            }
                                            
                                            return confirm('Xác nhận check-out phòng {{ $phong->so_phong }}?');
                                        }
                                    </script>
                                </div>

                            @elseif($isRoomCheckedOut)
                                {{-- COMPLETED --}}
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                    <div class="grid grid-cols-2 gap-3 text-xs">
                                        @php
                                            $roomCheckinTimeCarbon = $roomCheckinTime ? \Carbon\Carbon::parse($roomCheckinTime) : null;
                                            $roomCheckoutTimeCarbon = $roomCheckoutTime ? \Carbon\Carbon::parse($roomCheckoutTime) : null;
                                        @endphp
                                        <div>
                                            <h4 class="font-medium text-gray-900 mb-1">Check-in</h4>
                                            <p class="text-gray-600">
                                                <span class="font-medium">Thời gian:</span>
                                                {{ $roomCheckinTimeCarbon ? $roomCheckinTimeCarbon->format('d/m/Y H:i') : 'N/A' }}
                                            </p>
                                            <p class="text-gray-600">
                                                <span class="font-medium">NV:</span> {{ $booking->nguoi_checkin }}
                                            </p>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900 mb-1">Check-out</h4>
                                            <p class="text-gray-600">
                                                <span class="font-medium">Thời gian:</span>
                                                {{ $roomCheckoutTimeCarbon ? $roomCheckoutTimeCarbon->format('d/m/Y H:i') : 'Đã trả phòng' }}
                                            </p>
                                            <p class="text-gray-600">
                                                <span class="font-medium">NV:</span> {{ $booking->nguoi_checkout ?? 'N/A' }}
                                            </p>
                                        </div>
                                    </div>

                                    @if($phong->pivot->phu_phi > 0)
                                        <div class="mt-2 p-2 bg-red-50 border border-red-200 rounded">
                                            <p class="text-xs font-semibold text-red-800">
                                                ⚠️ Phụ phí: {{ number_format($phong->pivot->phu_phi, 0, ',', '.') }}₫
                                            </p>
                                        </div>
                                    @endif

                                    <div class="mt-2 pt-2 border-t border-gray-200">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Hoàn thành
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Overall booking checkin button (nếu còn phòng chưa checkin) --}}
            @if(!$allCheckedIn && $booking->trang_thai === 'da_xac_nhan')
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-4">
                        <p class="text-sm font-medium text-blue-900 mb-3">
                            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Check-in tất cả {{ $assignedPhongs->count() }} phòng cùng lúc
                        </p>
                        <form action="{{ route('admin.dat_phong.checkin', $booking->id) }}" method="POST">
                            @csrf
                            @foreach($assignedPhongs as $phong)
                                @if(is_null(optional($phong->pivot)->thoi_gian_checkin))
                                    <input type="hidden" name="phong_ids[]" value="{{ $phong->id }}">
                                @endif
                            @endforeach
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ghi chú chung (tùy chọn)</label>
                                <textarea name="ghi_chu_checkin" rows="2" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                    placeholder="Ghi chú chung cho tất cả phòng..."></textarea>
                            </div>
                            <button type="submit" 
                                class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition shadow-sm">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Check-in Tất Cả Phòng
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
