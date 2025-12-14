@extends('layouts.client')

@section('title', 'Yêu cầu đổi phòng')

@section('fullwidth_content')
    {{-- Hero giống trang phòng nghỉ --}}
    <div class="relative w-full bg-cover bg-center bg-no-repeat -mt-2"
         style="background-image: url('{{ asset('img/blog/blog-11.jpg') }}');">

        <div class="absolute inset-0 bg-black bg-opacity-50"></div>

        <div class="relative py-24 md:py-28 px-4 text-center text-white">
            <nav class="text-sm text-gray-200 mb-4">
                <a href="{{ url('/') }}" class="hover:text-[#D4AF37] transition-colors">Trang chủ</a> /
                <a href="{{ route('client.phong') }}" class="hover:text-[#D4AF37] transition-colors">Phòng nghỉ</a> /
                <span class="text-[#FFD700] font-semibold">Yêu cầu đổi phòng</span>
            </nav>

            <h1 class="text-4xl md:text-6xl font-bold mb-4">Yêu cầu đổi phòng</h1>
            <p class="text-base md:text-lg text-gray-100 max-w-3xl mx-auto">
                Nếu bạn chưa thật sự hài lòng với phòng hiện tại, hãy gửi yêu cầu đổi phòng
                và chúng tôi sẽ hỗ trợ trong thời gian sớm nhất.
            </p>
        </div>
    </div>
@endsection

@section('client_content')
<div class="bg-gradient-to-b from-gray-50 via-white to-white py-12 min-h-screen">
    <div class="max-w-5xl mx-auto px-4">

        @if (session('error'))
            <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-xl border border-red-100">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid md:grid-cols-2 gap-6 mb-8">
            {{-- Thông tin loại phòng + ảnh --}}
            <div
                class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col md:flex-row md:items-stretch">
                <div class="md:w-1/2 relative h-48 md:h-auto">
                    @php
                        $roomType = $booking->loaiPhong;
                        $roomImg = (!empty($roomType?->anh)) ? asset($roomType->anh) : asset('img/room/room-1.jpg');
                    @endphp
                    <img src="{{ $roomImg }}" alt="{{ $roomType->ten_loai ?? 'Phòng' }}"
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>
                    <div class="absolute bottom-4 left-4 right-4 flex flex-col gap-1">
                        <span
                            class="inline-flex items-center justify-between bg-black/70 text-white px-3 py-1.5 rounded-full text-xs md:text-sm">
                            <span class="font-semibold">
                                {{ $roomType->ten_loai ?? 'Loại phòng' }}
                            </span>
                            <span class="text-[#FFD700] font-bold">
                                {{ number_format($roomType->gia_hien_thi ?? $roomType->gia_co_ban ?? 0, 0, ',', '.') }}
                                <span class="text-[11px] font-normal text-gray-200">VNĐ / đêm</span>
                            </span>
                        </span>
                    </div>
                </div>
                <div class="flex-1 p-5 space-y-3 text-sm text-gray-600">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Mã đặt phòng</p>
                    <p class="text-lg font-semibold text-gray-900 mb-2">#{{ $booking->id }}</p>

                    <p class="flex justify-between">
                        <span>Loại phòng:</span>
                        <strong class="text-gray-900">{{ $roomType->ten_loai ?? 'N/A' }}</strong>
                    </p>
                    <p class="flex justify-between">
                        <span>Ngày nhận:</span>
                        <strong class="text-gray-900">{{ \Carbon\Carbon::parse($booking->ngay_nhan)->format('d/m/Y') }}</strong>
                    </p>
                    <p class="flex justify-between">
                        <span>Ngày trả:</span>
                        <strong class="text-gray-900">{{ \Carbon\Carbon::parse($booking->ngay_tra)->format('d/m/Y') }}</strong>
                    </p>
                    @if($phongHienTai)
                        <p class="flex justify-between">
                            <span>Phòng hiện tại:</span>
                            <strong class="text-gray-900">{{ $phongHienTai->ten_phong ?? ('Phòng #' . $phongHienTai->id) }}</strong>
                        </p>
                    @endif
                </div>
            </div>

            <div class="p-5 bg-[#FFF7E6] rounded-2xl border border-[#FDE3A7]">
                <h3 class="text-base font-semibold text-orange-600 mb-3">Lưu ý</h3>
                <ul class="text-sm text-gray-600 space-y-2">
                    <li>• Có thể đổi sang <strong>bất kỳ loại phòng nào</strong> đang còn trống.</li>
                    <li>• Yêu cầu áp dụng cho đặt phòng đã check-in, chưa check-out.</li>
                    <li>• <strong>Phí đổi phòng:</strong> Miễn phí nếu chênh lệch giá ≤ 100.000 VNĐ, tính theo chênh lệch giá nếu > 100.000 VNĐ.</li>
                    <li>• Có thể thêm người khi đổi phòng (tối đa 4 người lớn, 4 trẻ em, 4 em bé).</li>
                    <li>• Vui lòng mô tả rõ lý do để chúng tôi hỗ trợ nhanh nhất.</li>
                </ul>
            </div>
        </div>

        <form method="POST" action="{{ route('client.yeu_cau_doi_phong.store', $booking->id) }}"
              class="bg-white rounded-2xl shadow-sm p-8 border border-gray-100 space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Phòng hiện tại</label>
                <select name="phong_cu_id" class="w-full border-gray-200 rounded-xl px-4 py-3 focus:ring focus:ring-orange-200 focus:border-orange-400 bg-gray-50">
                    @foreach ($booking->phongs as $p)
                        <option value="{{ $p->id }}"
                            {{ old('phong_cu_id', optional($phongHienTai)->id) == $p->id ? 'selected' : '' }}>
                            {{ $p->ten_phong ?? ('Phòng #' . $p->id) }}
                        </option>
                    @endforeach
                </select>
                @error('phong_cu_id')
                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Phòng muốn đổi sang</label>
                
                @if($availableRooms->isEmpty())
                    <div class="p-6 bg-gray-50 rounded-xl border border-gray-200 text-center">
                        <p class="text-gray-600">Hiện không còn phòng trống trong khoảng thời gian này.</p>
                    </div>
                @else
                    {{-- Hiển thị ảnh các loại phòng --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                        @foreach ($availableRooms->groupBy('loai_phong_id') as $loaiPhongId => $rooms)
                            @php
                                $loaiPhong = $rooms->first()->loaiPhong;
                                $giaPhong = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban ?? 0;
                                $tongGiaPhongMoi = $giaPhong * $nights;
                                $giaPhongCu = $booking->loaiPhong->gia_khuyen_mai ?? $booking->loaiPhong->gia_co_ban ?? 0;
                                $tongGiaPhongCu = $giaPhongCu * $nights;
                                $chenhLech = max(0, $tongGiaPhongMoi - $tongGiaPhongCu);
                                // Phí đổi phòng: nếu chênh lệch giá <= 100K thì miễn phí, còn nếu > 100K thì tính theo chênh lệch giá
                                $phiDoiPhongMacDinh = 100000;
                                if ($chenhLech <= $phiDoiPhongMacDinh) {
                                    $phiDoiPhong = 0; // Miễn phí
                                } else {
                                    $phiDoiPhong = $chenhLech; // Tính theo chênh lệch giá
                                }
                                $roomImg = !empty($loaiPhong->anh) ? asset($loaiPhong->anh) : asset('img/room/room-1.jpg');
                            @endphp
                            
                            <div class="room-type-preview bg-white rounded-xl shadow-md border-2 border-gray-200 overflow-hidden">
                                {{-- Ảnh loại phòng --}}
                                <div class="relative h-40 overflow-hidden">
                                    <img src="{{ $roomImg }}" alt="{{ $loaiPhong->ten_loai }}" 
                                         class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                                    <div class="absolute bottom-3 left-3 right-3">
                                        <h3 class="text-white font-bold text-base mb-1">{{ $loaiPhong->ten_loai }}</h3>
                                        <p class="text-white text-xs">
                                            {{ number_format($giaPhong, 0, ',', '.') }} VNĐ/đêm
                                        </p>
                                    </div>
                                </div>
                                
                                {{-- Dropdown chọn phòng --}}
                                <div class="p-3">
                                    {{-- Hiển thị phí đổi phòng --}}
                                    @if($chenhLech <= 100000)
                                        <div class="mb-2 p-2 bg-green-50 rounded-lg border border-green-200">
                                            <p class="text-xs text-green-700 font-semibold">Miễn phí đổi phòng</p>
                                        </div>
                                    @else
                                        <div class="mb-2 p-2 bg-orange-50 rounded-lg border border-orange-200">
                                            <p class="text-xs text-orange-700 font-semibold">Phí đổi phòng:</p>
                                            <p class="text-sm font-bold text-orange-600">{{ number_format($chenhLech, 0, ',', '.') }} VNĐ</p>
                                        </div>
                                    @endif
                                    
                                    <select name="phong_moi_id_{{ $loaiPhongId }}" 
                                            class="room-select-dropdown w-full border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-orange-200 focus:border-orange-400 bg-white"
                                            data-loai-phong-id="{{ $loaiPhongId }}"
                                            data-gia-phong="{{ $giaPhong }}"
                                            data-gia-phong-cu="{{ $giaPhongCu }}"
                                            data-chenh-lech="{{ $chenhLech }}">
                                        <option value="">-- Chọn phòng --</option>
                                        @foreach ($rooms as $room)
                                            <option value="{{ $room->id }}" 
                                                data-room-id="{{ $room->id }}"
                                                {{ old('phong_moi_id') == $room->id ? 'selected' : '' }}>
                                                {{ $room->ten_phong ?? ('Phòng #' . $room->id) }}
                                                @if($room->tang) - Tầng {{ $room->tang }} @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <input type="hidden" name="phong_moi_id" id="phong_moi_id" value="{{ old('phong_moi_id') }}" required>
                    @error('phong_moi_id')
                        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                    @enderror
                    
                    <p class="text-xs text-gray-500 mt-2">
                        Danh sách hiển thị tất cả các phòng đang còn trống. Phí đổi phòng: miễn phí nếu chênh lệch giá ≤ 100.000 VNĐ, tính theo chênh lệch giá nếu > 100.000 VNĐ.
                    </p>
                    <div id="phi_doi_phong_display" class="mt-3 p-4 bg-blue-50 rounded-lg border border-blue-200 hidden">
                        <p class="text-sm font-semibold text-blue-900 mb-3">Chi tiết phí đổi phòng:</p>
                        <div id="phi_doi_phong_breakdown" class="space-y-2 text-sm">
                            <!-- Chi tiết sẽ được cập nhật bằng JavaScript -->
                        </div>
                        <div class="mt-3 pt-3 border-t border-blue-200">
                            <div class="flex justify-between items-center">
                                <p class="text-sm font-semibold text-blue-900">Tổng phí đổi phòng:</p>
                                <p class="text-lg font-bold text-blue-600" id="phi_doi_phong_amount">0 VNĐ</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Số khách mới (nếu thêm người)</label>
{{-- Phần chọn số khách --}}
<div class="p-4">
    <div class="grid grid-cols-3 gap-4 items-stretch">
        {{-- Người lớn --}}
        <div class="flex flex-col items-center text-center">
            <div class="flex flex-col items-center justify-center h-20 mb-2">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mb-2">
                    <i class="fas fa-user text-blue-600 text-xl"></i>
                </div>
                <span class="text-xs font-semibold text-gray-700">Người lớn</span>
                {{-- Giữ chỗ để cao bằng 2 ô còn lại --}}
                <span class="text-[10px] text-transparent">(0-0 tuổi)</span>
            </div>

            <div class="relative w-full">
                <select name="so_nguoi_lon_moi" id="so_nguoi_lon_moi"
                        class="w-full h-11 text-center appearance-none bg-white border-2 border-blue-200 rounded-lg px-3 pr-9
                               text-sm font-bold text-gray-800 shadow-sm transition-all duration-200
                               hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-500 cursor-pointer">
                    @php $soNguoiLonHienTai = $booking->so_nguoi ?? 2; @endphp
                    <option value="{{ $soNguoiLonHienTai }}" selected>{{ $soNguoiLonHienTai }}</option>
                    @for($i = $soNguoiLonHienTai + 1; $i <= 4; $i++)
                        <option value="{{ $i }}" {{ old('so_nguoi_lon_moi') == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>

        {{-- Trẻ em --}}
        <div class="flex flex-col items-center text-center">
            <div class="flex flex-col items-center justify-center h-20 mb-2">
                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mb-2">
                    <i class="fas fa-child text-green-600 text-xl"></i>
                </div>
                <span class="text-xs font-semibold text-gray-700">Trẻ em</span>
                <span class="text-[10px] text-gray-500">(6-11 tuổi)</span>
            </div>

            <div class="relative w-full">
                <select name="so_tre_em_moi" id="so_tre_em_moi"
                        class="w-full h-11 text-center appearance-none bg-white border-2 border-green-200 rounded-lg px-3 pr-9
                               text-sm font-bold text-gray-800 shadow-sm transition-all duration-200
                               hover:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-300 focus:border-green-500 cursor-pointer">
                    @php $soTreEmHienTai = $booking->so_tre_em ?? 0; @endphp
                    <option value="{{ $soTreEmHienTai }}" selected>{{ $soTreEmHienTai }}</option>
                    @for($i = $soTreEmHienTai + 1; $i <= 4; $i++)
                        <option value="{{ $i }}" {{ old('so_tre_em_moi') == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>

        {{-- Em bé --}}
        <div class="flex flex-col items-center text-center">
            <div class="flex flex-col items-center justify-center h-20 mb-2">
                <div class="w-12 h-12 rounded-full bg-pink-100 flex items-center justify-center mb-2">
                    <i class="fas fa-baby text-pink-600 text-xl"></i>
                </div>
                <span class="text-xs font-semibold text-gray-700">Em bé</span>
                <span class="text-[10px] text-gray-500">(0-5 tuổi)</span>
            </div>

            <div class="relative w-full">
                <select name="so_em_be_moi" id="so_em_be_moi"
                        class="w-full h-11 text-center appearance-none bg-white border-2 border-pink-200 rounded-lg px-3 pr-9
                               text-sm font-bold text-gray-800 shadow-sm transition-all duration-200
                               hover:border-pink-400 focus:outline-none focus:ring-2 focus:ring-pink-300 focus:border-pink-500 cursor-pointer">
                    @php $soEmBeHienTai = $booking->so_em_be ?? 0; @endphp
                    <option value="{{ $soEmBeHienTai }}" selected>{{ $soEmBeHienTai }}</option>
                    @for($i = $soEmBeHienTai + 1; $i <= 4; $i++)
                        <option value="{{ $i }}" {{ old('so_em_be_moi') == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>
    </div>

    <p class="text-xs text-gray-500 mt-3 text-center">
        Chỉ có thể thêm người, không thể giảm. Tối đa 4 người lớn, 4 trẻ em, 4 em bé.
    </p>
</div>

                <input type="hidden" name="so_nguoi_moi" id="so_nguoi_moi" value="{{ $booking->so_nguoi ?? 2 }}">
                @error('so_nguoi_moi')
                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Lý do đổi phòng</label>
                <textarea name="ly_do" rows="4"
                          class="w-full border-gray-200 rounded-xl px-4 py-3 focus:ring focus:ring-orange-200 focus:border-orange-400">{{ old('ly_do') }}</textarea>
                @error('ly_do')
                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-2">
                    Ví dụ: Phòng có vấn đề về tiếng ồn, muốn tầng cao hơn, muốn giường đơn/đôi,...
                </p>
            </div>

            <div class="flex gap-3 justify-end pt-4">
                <a href="{{ route('profile.edit') }}"
                   class="px-5 py-3 bg-gray-100 rounded-xl hover:bg-gray-200 text-gray-700 transition">
                    Quay lại
                </a>
                <button type="submit"
                        class="px-5 py-3 bg-orange-500 text-white rounded-xl shadow hover:bg-orange-600 transition">
                    Gửi yêu cầu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const phongMoiHidden = document.getElementById('phong_moi_id');
    const phiDoiPhongDisplay = document.getElementById('phi_doi_phong_display');
    const phiDoiPhongAmount = document.getElementById('phi_doi_phong_amount');
    const soNguoiLonMoi = document.getElementById('so_nguoi_lon_moi');
    const soTreEmMoi = document.getElementById('so_tre_em_moi');
    const soEmBeMoi = document.getElementById('so_em_be_moi');
    const soNguoiMoiHidden = document.getElementById('so_nguoi_moi');
    
    // Thông tin booking hiện tại
    const bookingInfo = {
        soNguoiHienTai: {{ $booking->so_nguoi ?? 2 }},
        soTreEmHienTai: {{ $booking->so_tre_em ?? 0 }},
        soEmBeHienTai: {{ $booking->so_em_be ?? 0 }},
        giaPhongCu: {{ $booking->loaiPhong->gia_khuyen_mai ?? $booking->loaiPhong->gia_co_ban ?? 0 }},
        ngayNhan: '{{ $booking->ngay_nhan }}',
        ngayTra: '{{ $booking->ngay_tra }}',
        nights: {{ $nights }}
    };
    
    // Hàm tính hệ số giá theo ngày
    function getMultiplierForDate(date) {
        const dayOfWeek = date.getDay();
        const month = date.getMonth();
        const day = date.getDate();
        
        // Ngày lễ: 01/01, 30/04, 01/05, 02/09
        const holidays = [
            { month: 0, day: 1 },   // 01/01
            { month: 3, day: 30 },  // 30/04
            { month: 4, day: 1 },   // 01/05
            { month: 8, day: 2 }    // 02/09
        ];
        
        for (let holiday of holidays) {
            if (month === holiday.month && day === holiday.day) {
                return 1.25; // Ngày lễ: +25%
            }
        }
        
        if (dayOfWeek === 0 || dayOfWeek === 6) {
            return 1.15; // Cuối tuần: +15%
        }
        
        return 1.0; // Ngày thường
    }
    
    // Hàm tính phụ phí thêm người/trẻ em/em bé
    function calculateSurcharge(basePrice, checkIn, checkOut, count, percent) {
        if (count <= 0 || percent <= 0) return 0;
        
        let total = 0;
        const start = new Date(checkIn);
        const end = new Date(checkOut);
        const current = new Date(start);
        
        while (current < end) {
            const multiplier = getMultiplierForDate(current);
            const priceForDay = basePrice * multiplier;
            total += count * priceForDay * percent;
            current.setDate(current.getDate() + 1);
        }
        
        return total;
    }
    
    // Hàm tính phí đổi phòng tổng
    function calculateTotalFee() {
        // Lấy phòng được chọn
        let selectedRoom = null;
        let selectedDropdown = null;
        
        document.querySelectorAll('.room-select-dropdown').forEach(dropdown => {
            if (dropdown.value) {
                selectedRoom = dropdown.options[dropdown.selectedIndex];
                selectedDropdown = dropdown;
            }
        });
        
        if (!selectedRoom || !selectedRoom.value) {
            phiDoiPhongDisplay?.classList.add('hidden');
            return;
        }
        
        // Lấy thông tin phòng mới
        const giaPhongMoi = parseFloat(selectedDropdown.getAttribute('data-gia-phong')) || 0;
        const giaPhongCu = parseFloat(selectedDropdown.getAttribute('data-gia-phong-cu')) || bookingInfo.giaPhongCu;
        const chenhLech = Math.max(0, (giaPhongMoi - giaPhongCu) * bookingInfo.nights);
        
        // 1. Tính phí đổi phòng cơ bản
        const phiDoiPhongMacDinh = 100000;
        let phiDoiPhongCoBan = 0;
        if (chenhLech <= phiDoiPhongMacDinh) {
            phiDoiPhongCoBan = 0; // Miễn phí
        } else {
            phiDoiPhongCoBan = chenhLech; // Tính theo chênh lệch
        }
        
        // 2. Tính phụ phí thêm người lớn
        const soNguoiLon = parseInt(soNguoiLonMoi?.value || bookingInfo.soNguoiHienTai);
        const extraAdults = Math.max(0, soNguoiLon - bookingInfo.soNguoiHienTai);
        const phuPhiNguoiLon = calculateSurcharge(
            giaPhongMoi,
            bookingInfo.ngayNhan,
            bookingInfo.ngayTra,
            extraAdults,
            0.20 // 20%
        );
        
        // 3. Tính phụ phí thêm trẻ em
        const soTreEm = parseInt(soTreEmMoi?.value || bookingInfo.soTreEmHienTai);
        const extraChildren = Math.max(0, soTreEm - bookingInfo.soTreEmHienTai);
        const phuPhiTreEm = calculateSurcharge(
            giaPhongMoi,
            bookingInfo.ngayNhan,
            bookingInfo.ngayTra,
            extraChildren,
            0.10 // 10%
        );
        
        // 4. Tính phụ phí thêm em bé
        const soEmBe = parseInt(soEmBeMoi?.value || bookingInfo.soEmBeHienTai);
        const extraInfants = Math.max(0, soEmBe - bookingInfo.soEmBeHienTai);
        const phuPhiEmBe = calculateSurcharge(
            giaPhongMoi,
            bookingInfo.ngayNhan,
            bookingInfo.ngayTra,
            extraInfants,
            0.05 // 5%
        );
        
        // 5. Tổng phí đổi phòng
        const tongPhiDoiPhong = phiDoiPhongCoBan + phuPhiNguoiLon + phuPhiTreEm + phuPhiEmBe;
        
        // 6. Hiển thị chi tiết phí đổi phòng
        const breakdownDiv = document.getElementById('phi_doi_phong_breakdown');
        if (breakdownDiv) {
            let breakdownHtml = '';
            
            // Phí đổi phòng cơ bản
            if (phiDoiPhongCoBan > 0) {
                breakdownHtml += `
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Phí đổi phòng cơ bản:</span>
                        <span class="font-semibold text-gray-900">${new Intl.NumberFormat('vi-VN').format(Math.round(phiDoiPhongCoBan))} VNĐ</span>
                    </div>
                `;
            } else {
                breakdownHtml += `
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Phí đổi phòng cơ bản:</span>
                        <span class="font-semibold text-green-600">Miễn phí</span>
                    </div>
                `;
            }
            
            // Phụ phí thêm người lớn
            if (phuPhiNguoiLon > 0) {
                breakdownHtml += `
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Phụ phí thêm ${extraAdults} người lớn (20%/người/đêm):</span>
                        <span class="font-semibold text-blue-600">${new Intl.NumberFormat('vi-VN').format(Math.round(phuPhiNguoiLon))} VNĐ</span>
                    </div>
                `;
            }
            
            // Phụ phí thêm trẻ em
            if (phuPhiTreEm > 0) {
                breakdownHtml += `
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Phụ phí thêm ${extraChildren} trẻ em (10%/trẻ/đêm):</span>
                        <span class="font-semibold text-green-600">${new Intl.NumberFormat('vi-VN').format(Math.round(phuPhiTreEm))} VNĐ</span>
                    </div>
                `;
            }
            
            // Phụ phí thêm em bé
            if (phuPhiEmBe > 0) {
                breakdownHtml += `
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Phụ phí thêm ${extraInfants} em bé (5%/em bé/đêm):</span>
                        <span class="font-semibold text-pink-600">${new Intl.NumberFormat('vi-VN').format(Math.round(phuPhiEmBe))} VNĐ</span>
                    </div>
                `;
            }
            
            // Nếu không có phụ phí nào
            if (phuPhiNguoiLon === 0 && phuPhiTreEm === 0 && phuPhiEmBe === 0) {
                breakdownHtml += `
                    <div class="text-xs text-gray-500 italic">
                        Không có phụ phí thêm người
                    </div>
                `;
            }
            
            breakdownDiv.innerHTML = breakdownHtml;
        }
        
        // Cập nhật tổng phí đổi phòng
        if (phiDoiPhongDisplay && phiDoiPhongAmount) {
            if (tongPhiDoiPhong > 0) {
                phiDoiPhongAmount.textContent = new Intl.NumberFormat('vi-VN').format(Math.round(tongPhiDoiPhong)) + ' VNĐ';
                phiDoiPhongDisplay.classList.remove('hidden');
            } else {
                phiDoiPhongAmount.textContent = 'Miễn phí';
                phiDoiPhongDisplay.classList.remove('hidden');
            }
        }
    }
    
    // Xử lý khi chọn phòng từ dropdown
    document.querySelectorAll('.room-select-dropdown').forEach(dropdown => {
        dropdown.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const phongMoiId = selectedOption.value;
            
            // Bỏ chọn tất cả dropdown khác
            document.querySelectorAll('.room-select-dropdown').forEach(other => {
                if (other !== this) {
                    other.value = '';
                }
            });
            
            // Cập nhật hidden input
            if (phongMoiHidden) {
                phongMoiHidden.value = phongMoiId;
            }
            
            // Tính lại phí đổi phòng
            calculateTotalFee();
            
            // Highlight card được chọn
            document.querySelectorAll('.room-type-preview').forEach(card => {
                card.classList.remove('border-orange-500', 'ring-2', 'ring-orange-300');
            });
            if (phongMoiId) {
                this.closest('.room-type-preview')?.classList.add('border-orange-500', 'ring-2', 'ring-orange-300');
            }
        });
    });
    
    // Cập nhật số người mới khi thay đổi
    function updateSoNguoiMoi() {
        const soNguoiLon = parseInt(soNguoiLonMoi?.value || bookingInfo.soNguoiHienTai);
        if (soNguoiMoiHidden) {
            soNguoiMoiHidden.value = soNguoiLon;
        }
        // Tính lại phí đổi phòng
        calculateTotalFee();
    }
    
    if (soNguoiLonMoi) {
        soNguoiLonMoi.addEventListener('change', updateSoNguoiMoi);
    }
    
    if (soTreEmMoi) {
        soTreEmMoi.addEventListener('change', calculateTotalFee);
    }
    
    if (soEmBeMoi) {
        soEmBeMoi.addEventListener('change', calculateTotalFee);
    }
    
    // Không cho phép giảm số khách (chỉ tăng)
    [soNguoiLonMoi, soTreEmMoi, soEmBeMoi].forEach(select => {
        if (select) {
            select.addEventListener('change', function() {
                const currentValue = parseInt(this.value);
                const minValue = parseInt(this.options[0].value);
                if (currentValue < minValue) {
                    this.value = minValue;
                }
            });
        }
    });
    
    // Tính phí ban đầu nếu đã có phòng được chọn
    if (phongMoiHidden && phongMoiHidden.value) {
        calculateTotalFee();
    }
});
</script>

<style>
.room-select-dropdown {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}
</style>
@endsection
