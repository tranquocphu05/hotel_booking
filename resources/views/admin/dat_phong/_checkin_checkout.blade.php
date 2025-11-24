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

                <form action="{{ route('admin.dat_phong.checkout', $booking->id) }}" method="POST" class="border-t border-green-200 pt-4" id="checkoutForm">
                    @csrf
                    <h3 class="font-medium text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Thông tin check-out
                    </h3>
                    
                    {{-- Section: Thiệt hại tài sản và phụ phí --}}
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                        <h4 class="text-sm font-semibold text-red-900 mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Thiệt hại tài sản & Phụ phí phát sinh
                        </h4>
                        
                        <div class="space-y-3">
                            {{-- Danh mục thiệt hại --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Danh mục thiệt hại / Phụ phí
                                </label>
                                <select name="loai_thiet_hai" id="loaiThietHai" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    onchange="updateDamageDescription()">
                                    <option value="">-- Chọn danh mục (tùy chọn) --</option>
                                    <option value="do_dac_hu_hong">Đồ đạc bị hư hỏng</option>
                                    <option value="thiet_bi_dien">Thiết bị điện tử bị hỏng</option>
                                    <option value="noi_that">Nội thất bị hư hỏng</option>
                                    <option value="san_phong">Sàn phòng bị hư hỏng</option>
                                    <option value="tuong_phong">Tường phòng bị hư hỏng</option>
                                    <option value="cua_so_kinh">Cửa sổ/kính bị vỡ</option>
                                    <option value="minibar_thieu">Minibar thiếu đồ</option>
                                    <option value="do_dung_phong_thieu">Đồ dùng phòng thiếu</option>
                                    <option value="tham_trang_tri">Thảm/trang trí bị hư hỏng</option>
                                    <option value="phong_tam">Phòng tắm bị hư hỏng</option>
                                    <option value="khac">Khác</option>
                                </select>
                            </div>

                            {{-- Mô tả chi tiết thiệt hại --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Mô tả chi tiết thiệt hại / Lý do phụ phí <span class="text-red-500">*</span>
                                </label>
                                <textarea name="ly_do_phi" id="lyDoPhi" rows="3" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="Ví dụ: TV bị vỡ màn hình, ghế sofa bị rách, minibar thiếu 2 chai nước..."></textarea>
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-info-circle"></i> Mô tả rõ ràng thiệt hại để làm cơ sở tính phí
                                </p>
                            </div>

                            {{-- Số tiền phụ phí --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Số tiền phụ phí (VNĐ) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="phi_phat_sinh" id="phiPhatSinh" step="1000" min="0" required
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 pl-8 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                        placeholder="0"
                                        oninput="formatCurrency(this)">
                                    <span class="absolute left-3 top-2.5 text-gray-500">₫</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-calculator"></i> Nhập số tiền cần thu bù cho thiệt hại
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Section: Ghi chú check-out --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Ghi chú check-out (tình trạng phòng)
                        </label>
                        <textarea name="ghi_chu_checkout" rows="3" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Ví dụ: Phòng sạch sẽ, đồ đạc đầy đủ, không có vấn đề gì..."></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle"></i> Ghi chú về tình trạng tổng thể của phòng sau khi khách trả phòng
                        </p>
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

                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <div class="text-sm text-gray-600">
                            <p id="totalFeeDisplay" class="hidden">
                                <span class="font-medium">Tổng phụ phí:</span> 
                                <span id="totalFeeAmount" class="text-red-600 font-bold">0₫</span>
                            </p>
                        </div>
                        <button type="submit" 
                            class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition shadow-sm"
                            onclick="return confirmCheckout()">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Check-out Ngay
                        </button>
                    </div>
                </form>

                <script>
                    // Mapping danh mục thiệt hại với mô tả mặc định
                    const damageDescriptions = {
                        'do_dac_hu_hong': 'Đồ đạc trong phòng bị hư hỏng',
                        'thiet_bi_dien': 'Thiết bị điện tử (TV, điều hòa, tủ lạnh...) bị hỏng',
                        'noi_that': 'Nội thất (giường, tủ, bàn ghế...) bị hư hỏng',
                        'san_phong': 'Sàn phòng bị hư hỏng (trầy xước, ố vàng...)',
                        'tuong_phong': 'Tường phòng bị hư hỏng (vết bẩn, trầy xước...)',
                        'cua_so_kinh': 'Cửa sổ/kính bị vỡ hoặc hư hỏng',
                        'minibar_thieu': 'Minibar thiếu đồ (nước, đồ ăn...)',
                        'do_dung_phong_thieu': 'Đồ dùng phòng thiếu (khăn tắm, chăn gối...)',
                        'tham_trang_tri': 'Thảm/đồ trang trí bị hư hỏng',
                        'phong_tam': 'Phòng tắm bị hư hỏng (vòi nước, bồn tắm...)',
                        'khac': ''
                    };

                    function updateDamageDescription() {
                        const select = document.getElementById('loaiThietHai');
                        const textarea = document.getElementById('lyDoPhi');
                        const selectedValue = select.value;
                        
                        if (selectedValue && damageDescriptions[selectedValue]) {
                            const currentValue = textarea.value.trim();
                            // Chỉ thêm mô tả mặc định nếu textarea đang trống
                            if (!currentValue || currentValue === '') {
                                textarea.value = damageDescriptions[selectedValue];
                            } else if (!currentValue.includes(damageDescriptions[selectedValue])) {
                                // Nếu đã có nội dung, thêm vào cuối
                                textarea.value = currentValue + '. ' + damageDescriptions[selectedValue];
                            }
                        }
                    }

                    function formatCurrency(input) {
                        const value = parseFloat(input.value) || 0;
                        const formatted = new Intl.NumberFormat('vi-VN').format(value);
                        const display = document.getElementById('totalFeeDisplay');
                        const amount = document.getElementById('totalFeeAmount');
                        
                        if (value > 0) {
                            display.classList.remove('hidden');
                            amount.textContent = formatted + '₫';
                        } else {
                            display.classList.add('hidden');
                        }
                    }

                    function confirmCheckout() {
                        const phiPhatSinh = parseFloat(document.getElementById('phiPhatSinh').value) || 0;
                        const lyDoPhi = document.getElementById('lyDoPhi').value.trim();
                        
                        if (phiPhatSinh > 0 && !lyDoPhi) {
                            alert('Vui lòng nhập mô tả chi tiết thiệt hại khi có phụ phí!');
                            document.getElementById('lyDoPhi').focus();
                            return false;
                        }
                        
                        if (phiPhatSinh > 0) {
                            const confirmMsg = `Xác nhận check-out với phụ phí thiệt hại: ${new Intl.NumberFormat('vi-VN').format(phiPhatSinh)}₫?\n\nLý do: ${lyDoPhi}`;
                            return confirm(confirmMsg);
                        }
                        
                        return confirm('Xác nhận check-out?');
                    }

                    // Tính tổng phụ phí khi có check-out muộn
                    @if($isLate)
                        document.addEventListener('DOMContentLoaded', function() {
                            const phiCheckoutMuon = {{ $hoursLate <= 6 ? $booking->tong_tien * 0.5 : $booking->tong_tien }};
                            const phiPhatSinhInput = document.getElementById('phiPhatSinh');
                            
                            // Cộng phí checkout muộn vào tổng
                            phiPhatSinhInput.addEventListener('input', function() {
                                const phiThietHai = parseFloat(this.value) || 0;
                                const total = phiThietHai + phiCheckoutMuon;
                                formatCurrency({value: total});
                            });
                            
                            // Trigger initial calculation
                            phiPhatSinhInput.dispatchEvent(new Event('input'));
                        });
                    @endif
                </script>
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
                        @php
                            // Parse ghi_chu_checkout để hiển thị đẹp hơn
                            $ghiChuCheckout = $booking->ghi_chu_checkout ?? '';
                            $ghiChuThongThuong = '';
                            $thietHaiDetails = [];
                            
                            if ($ghiChuCheckout) {
                                // Loại bỏ phần [LY_DO_PHI: ...] ở đầu (nếu có, từ format cũ)
                                $ghiChuCheckout = preg_replace('/^\[LY_DO_PHI:\s*.+?\]\s*/', '', $ghiChuCheckout);
                                
                                // Tách phần thiệt hại tài sản
                                if (strpos($ghiChuCheckout, '=== THIỆT HẠI TÀI SẢN ===') !== false) {
                                    $parts = explode('=== THIỆT HẠI TÀI SẢN ===', $ghiChuCheckout, 2);
                                    $ghiChuThongThuong = trim($parts[0]);
                                    
                                    if (isset($parts[1])) {
                                        $thietHaiSection = $parts[1];
                                        // Extract danh mục
                                        if (preg_match('/Danh mục:\s*(.+?)(?:\n|$)/', $thietHaiSection, $matches)) {
                                            $thietHaiDetails['category'] = trim($matches[1]);
                                        }
                                        // Extract mô tả
                                        if (preg_match('/Mô tả:\s*(.+?)(?:\n(?:Số tiền|Phí)|$)/s', $thietHaiSection, $matches)) {
                                            $thietHaiDetails['description'] = trim($matches[1]);
                                        }
                                        // Extract số tiền
                                        if (preg_match('/Số tiền:\s*(.+?)(?:\n|$)/', $thietHaiSection, $matches)) {
                                            $thietHaiDetails['amount'] = trim($matches[1]);
                                        }
                                    }
                                } else {
                                    // Không có phần thiệt hại, chỉ có ghi chú thông thường
                                    $ghiChuThongThuong = trim($ghiChuCheckout);
                                }
                                
                                // Loại bỏ phần phí check-out muộn nếu có
                                $ghiChuThongThuong = preg_replace('/\n?Phí check-out muộn:.*$/m', '', $ghiChuThongThuong);
                                $ghiChuThongThuong = trim($ghiChuThongThuong);
                            }
                        @endphp
                        
                        @if($booking->phi_phat_sinh > 0)
                            <div class="mt-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <div class="flex items-start mb-2">
                                    <svg class="w-5 h-5 text-red-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-red-800 mb-2">
                                            Phụ phí thiệt hại: {{ number_format($booking->phi_phat_sinh, 0, ',', '.') }}₫
                                        </p>
                                        @if(!empty($thietHaiDetails))
                                            <div class="text-xs text-red-700 space-y-1">
                                                @if(isset($thietHaiDetails['category']))
                                                    <p><span class="font-medium">Danh mục:</span> {{ $thietHaiDetails['category'] }}</p>
                                                @endif
                                                @if(isset($thietHaiDetails['description']))
                                                    <p><span class="font-medium">Mô tả:</span> {{ $thietHaiDetails['description'] }}</p>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @if($ghiChuThongThuong)
                            <div class="mt-2">
                                <p class="text-xs text-gray-500 mb-1 font-medium">Ghi chú:</p>
                                <p class="text-sm text-gray-600 bg-gray-50 border border-gray-200 rounded p-2 whitespace-pre-wrap">{{ $ghiChuThongThuong }}</p>
                            </div>
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
