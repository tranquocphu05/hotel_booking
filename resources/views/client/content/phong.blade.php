@extends('layouts.client')

@section('client_content')
    {{-- Banner - Giữ w-full --}}
    <div class="relative w-full bg-cover bg-center bg-no-repeat -mt-2"
        style="background-image: url('{{ asset('img/blog/blog-11.jpg') }}');">

        <div class="absolute inset-0 bg-black bg-opacity-40"></div>

        <div class="relative py-28 px-4 text-center text-white">
            <nav class="text-sm text-gray-200 mb-4">
                <a href="{{ url('/') }}" class="hover:text-[#D4AF37] transition-colors">Trang chủ</a> /
                <span class="text-[#FFD700] font-semibold">Phòng nghỉ</span>
            </nav>

            <h1 class="text-5xl md:text-7xl font-bold mb-8">Phòng Nghỉ</h1>

            <p class="text-lg md:text-xl text-gray-100 leading-relaxed max-w-4xl mx-auto">
                Khách sạn Ozia Hotel sở hữu những căn phòng nghỉ kết hợp hoàn hảo phong cách thiết kế nội thất
                truyền thống trang nhã cùng với các tiện nghi đẳng cấp.
                Nơi đây là điểm đến lý tưởng để nghỉ dưỡng và thưởng lãm vẻ đẹp của thành phố.
            </p>
        </div>
    </div>

    <section class="bg-gray-50 py-16 w-full">
        <div class="px-4 sm:px-6 lg:px-8">
            {{-- Giữ max-w-7xl để bộ lọc không quá dài và khó thao tác --}}
            <div class="max-w-7xl mx-auto">
                 <div class="bg-white p-8 mb-12 rounded-xl shadow-2xl transition-all duration-300 border border-gray-100/50">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Tìm kiếm phòng</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ngày nhận phòng</label>
                            <input type="date" name="checkin" id="checkin_filter" 
                                    value="{{ request('checkin', $checkin ?? '') }}"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-[#D4AF37] focus:border-[#D4AF37] transition-all text-sm appearance-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ngày trả phòng</label>
                            <input type="date" name="checkout" id="checkout_filter"
                                    value="{{ request('checkout', $checkout ?? '') }}"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-[#D4AF37] focus:border-[#D4AF37] transition-all text-sm appearance-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Loại phòng</label>
                            <select name="loai_phong" id="loai_phong"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-[#D4AF37] focus:border-[#D4AF37] transition-all text-sm">
                                <option value="">Tất cả loại phòng</option>
                                @foreach ($allLoaiPhongs as $loai)
                                    <option value="{{ $loai->id }}"
                                        {{ request('loai_phong') == $loai->id ? 'selected' : '' }}>
                                        {{ $loai->ten_loai }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Giá từ (VNĐ)</label>
                            <input type="number" name="gia_min" id="gia_min" placeholder="0" value="{{ request('gia_min') }}"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-[#D4AF37] focus:border-[#D4AF37] transition-all text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Giá đến (VNĐ)</label>
                            <input type="number" name="gia_max" id="gia_max" placeholder="Không giới hạn"
                                value="{{ request('gia_max') }}"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-[#D4AF37] focus:border-[#D4AF37] transition-all text-sm">
                        </div>
                        <div class="flex items-end">
                            <button onclick="filterRooms()"
                                class="w-full bg-[#D4AF37] text-white px-6 py-2.5 rounded-xl hover:bg-[#b68b00] transition-colors font-semibold text-sm shadow-md">
                                <i class="fas fa-search mr-2"></i> Tìm phòng
                            </button>
                        </div>
                    </div>

                    @if($checkin && $checkout)
                    <div class="mt-6 p-3 bg-blue-50 border border-blue-200 rounded-xl">
                        <p class="text-sm text-blue-800 flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            Đang tìm kiếm phòng từ <span class="font-bold ml-1">{{ date('d/m/Y', strtotime($checkin)) }}</span> đến <span class="font-bold ml-1">{{ date('d/m/Y', strtotime($checkout)) }}</span>
                        </p>
                    </div>
                    @else
                    <div class="mt-6 p-3 bg-gray-50 border border-gray-200 rounded-xl">
                        <p class="text-sm text-gray-600 flex items-center">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            Chọn ngày để tìm kiếm phòng theo khoảng thời gian cụ thể
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 space-y-8">
        @forelse($phongs as $phong)
            <div class="group cursor-pointer"
                onclick="window.location.href='{{ route('client.phong.show', $phong->id) }}'">
                <div
                    class="bg-white rounded-xl shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-100">
                    <div class="flex flex-col md:flex-row md:items-stretch">
                        {{-- Ảnh phòng (1/3) --}}
                        <div class="w-full md:w-1/3 h-72 md:h-auto relative overflow-hidden flex-shrink-0 md:self-stretch">
                            @php
                                $roomImg = !empty($phong->anh) ? asset($phong->anh) : asset('img/room/room-1.jpg');
                            @endphp
                            <img src="{{ $roomImg }}" alt="{{ $phong->ten_loai }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                loading="lazy" decoding="async">
                            
                            <div class="absolute top-4 left-4">
                                <span class="bg-white text-gray-900 px-3 py-1.5 text-sm font-semibold rounded-full shadow-lg">
                                    {{ $phong->ten_loai }}
                                </span>
                            </div>

                            <div class="absolute bottom-4 right-4 flex flex-col items-end gap-1">
                                @if($phong->gia_khuyen_mai)
                                    @php
                                        $discountPercent = round((($phong->gia_co_ban - $phong->gia_khuyen_mai) / $phong->gia_co_ban) * 100);
                                    @endphp
                                    
                                    <div class="inline-flex items-center gap-1 bg-gradient-to-r from-red-500 to-red-600 text-white text-xs font-bold px-3 py-1 rounded-lg shadow-md">
                                        <i class="fas fa-tag text-white text-xs"></i>
                                        <span>GIẢM {{ $discountPercent }}%</span>
                                    </div>
                                @endif
                                
                                <div class="bg-black/80 text-white px-4 py-2.5 rounded-lg shadow-xl text-right">
                                    <div class="text-2xl font-extrabold text-[#FFD700] whitespace-nowrap">
                                        {{ number_format($phong->gia_khuyen_mai ?? $phong->gia_co_ban, 0, ',', '.') }}
                                        <span class="text-base text-gray-300"> VNĐ / đêm</span>
                                    </div>
                                    @if($phong->gia_khuyen_mai)
                                        <div class="text-sm text-gray-400 line-through mt-0.5 whitespace-nowrap">
                                            {{ number_format($phong->gia_co_ban, 0, ',', '.') }} VNĐ
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Nội dung phòng (2/3) --}}
                        <div class="md:flex-1 p-8 flex flex-col">
                            <h3
                                class="text-3xl font-bold text-gray-900 mb-4 group-hover:text-[#D4AF37] transition-colors">
                                {{ $phong->ten_loai }}
                            </h3>
                            
                            @php
                                $amenities = [
                                    ['icon' => 'fas fa-wifi', 'name' => 'WiFi miễn phí'],
                                    ['icon' => 'fas fa-snowflake', 'name' => 'Điều hòa'],
                                    ['icon' => 'fas fa-tv', 'name' => 'Tivi 65 inch'],
                                    ['icon' => 'fas fa-bath', 'name' => 'Bồn tắm nằm'],
                                    ['icon' => 'fas fa-wine-glass', 'name' => 'Minibar'],
                                    ['icon' => 'fas fa-concierge-bell', 'name' => 'Dịch vụ Butler'],
                                ];
                                $amenity1 = $amenities[$phong->id % count($amenities)];
                                $amenity2 = $amenities[($phong->id + 1) % count($amenities)];
                            @endphp
                            <div class="flex items-center gap-3 mb-4 flex-wrap">
                                <span
                                    class="inline-flex items-center gap-2 text-sm bg-blue-50 text-blue-700 px-3 py-1 rounded-full font-medium">
                                    <i class="{{ $amenity1['icon'] }}"></i>
                                    {{ $amenity1['name'] }}
                                </span>
                                <span
                                    class="inline-flex items-center gap-2 text-sm bg-green-50 text-green-700 px-3 py-1 rounded-full font-medium">
                                    <i class="{{ $amenity2['icon'] }}"></i>
                                    {{ $amenity2['name'] }}
                                </span>
                            </div>

                            @if ($phong->mo_ta)
                                <p class="text-gray-600 leading-relaxed mb-6">
                                    {{ Str::limit($phong->mo_ta, 150) }}
                                </p>
                            @endif

                            @if($checkin && $checkout && isset($availabilityMap[$phong->id]) && $availabilityMap[$phong->id] !== null)
                                @php
                                    // x: số phòng còn trống theo khoảng ngày đã chọn
                                    $availableCount = $availabilityMap[$phong->id];
                                    // y: tổng số phòng được thêm trong DB (đếm số bản ghi phòng thuộc loại này)
                                    // totalRoomsMap được controller tính bằng COUNT(*) từ bảng phong theo loai_phong_id
                                    $totalRooms = isset($totalRoomsMap[$phong->id]) ? $totalRoomsMap[$phong->id] : 0;
                                @endphp
                                <div class="mb-6">
                                    @if($availableCount > 0)
                                        <div class="inline-flex items-center gap-2 text-sm bg-green-50 text-green-700 px-4 py-2 rounded-full font-semibold border border-green-200">
                                            <i class="fas fa-check-circle"></i>
                                            <span>Còn <span class="text-lg">{{ $availableCount }}</span>/{{ $totalRooms }} phòng trống</span>
                                            <span class="text-xs text-blue-600">({{ date('d/m/Y', strtotime($checkin)) }} - {{ date('d/m/Y', strtotime($checkout)) }})</span>
                                        </div>
                                    @else
                                        <div class="inline-flex flex-col gap-2">
                                            <div class="inline-flex items-center gap-2 text-sm bg-red-50 text-red-700 px-4 py-2 rounded-full font-semibold border border-red-200">
                                                <i class="fas fa-times-circle"></i>
                                                <span>Đã hết phòng trong khoảng thời gian này</span>
                                            </div>
                                            <p class="text-xs text-gray-600 italic">
                                                Không còn phòng từ {{ date('d/m/Y', strtotime($checkin)) }} đến {{ date('d/m/Y', strtotime($checkout)) }}. 
                                                <span class="text-blue-600 font-medium cursor-pointer" onclick="filterRooms()">Vui lòng chọn ngày khác để kiểm tra.</span>
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div class="mt-auto flex justify-end gap-3 pt-4">
                                @if($checkin && $checkout && isset($availabilityMap[$phong->id]) && $availabilityMap[$phong->id] > 0)
                                    {{-- Có ngày và còn phòng → Hiện nút Đặt ngay --}}
                                    <button onclick="event.stopPropagation(); bookRoomQuick({{ $phong->id }}, '{{ $checkin }}', '{{ $checkout }}')"
                                        class="inline-flex items-center justify-center gap-2 bg-[#D4AF37] hover:bg-[#C9A961] text-white px-4 py-2 rounded-lg transition-all duration-200 font-semibold text-sm shadow-sm hover:shadow-md">
                                        <i class="fas fa-calendar-check"></i>
                                        <span>Đặt ngay</span>
                                    </button>
                                    <button onclick="event.stopPropagation(); window.location.href='{{ route('client.phong.show', $phong->id) }}?checkin={{ $checkin }}&checkout={{ $checkout }}'"
                                        class="inline-flex items-center justify-center gap-1.5 bg-white hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg transition-all duration-200 font-medium text-sm border border-gray-200 hover:border-gray-300">
                                        <i class="fas fa-info-circle text-[#D4AF37] text-xs"></i>
                                        <span>Xem chi tiết phòng</span>
                                    </button>
                                @elseif($checkin && $checkout)
                                    {{-- Có ngày nhưng hết phòng → Chỉ hiện nút Chi tiết --}}
                                    <button onclick="event.stopPropagation(); window.location.href='{{ route('client.phong.show', $phong->id) }}?checkin={{ $checkin }}&checkout={{ $checkout }}'"
                                        class="inline-flex items-center justify-center gap-1.5 bg-white hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg transition-all duration-200 font-medium text-sm border border-gray-200 hover:border-gray-300">
                                        <i class="fas fa-info-circle text-[#D4AF37] text-xs"></i>
                                        <span>Xem chi tiết phòng</span>
                                    </button>
                                @else
                                    {{-- Chưa có ngày → Khuyến khích chọn ngày --}}
                                    <button onclick="event.stopPropagation(); document.getElementById('checkin_filter').focus(); document.getElementById('checkin_filter').scrollIntoView({ behavior: 'smooth', block: 'center' });"
                                        class="inline-flex items-center justify-center gap-2 bg-[#D4AF37] hover:bg-[#C9A961] text-white px-4 py-2 rounded-lg transition-all duration-200 font-semibold text-sm shadow-sm hover:shadow-md">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Chọn ngày để đặt</span>
                                    </button>
                                    <button onclick="event.stopPropagation(); window.location.href='{{ route('client.phong.show', $phong->id) }}'"
                                        class="inline-flex items-center justify-center gap-1.5 bg-white hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg transition-all duration-200 font-medium text-sm border border-gray-200 hover:border-gray-300">
                                        <i class="fas fa-info-circle text-[#D4AF37] text-xs"></i>
                                        <span>Xem chi tiết phòng</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-24 bg-white rounded-xl shadow-lg mt-8">
                <div class="text-gray-300 mb-8">
                    <svg class="w-24 h-24 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                </div>
                <h3 class="text-3xl font-light text-gray-600 mb-6">Không tìm thấy phòng</h3>
                <p class="text-gray-500 text-lg">Vui lòng thử lại với bộ lọc khác</p>
            </div>
        @endforelse
    </div>

    <script>
        function filterRooms() {
            const checkin = document.getElementById('checkin_filter')?.value;
            const checkout = document.getElementById('checkout_filter')?.value;
            const loaiPhong = document.getElementById('loai_phong').value;
            const giaMin = document.getElementById('gia_min').value;
            const giaMax = document.getElementById('gia_max').value;

            const params = new URLSearchParams();
            if (checkin) params.append('checkin', checkin);
            if (checkout) params.append('checkout', checkout);
            if (loaiPhong) params.append('loai_phong', loaiPhong);
            if (giaMin) params.append('gia_min', giaMin);
            if (giaMax) params.append('gia_max', giaMax);

            window.location.href = '{{ route('client.phong') }}?' + params.toString();
        }

        function bookRoomQuick(roomId, checkin, checkout) {
            // Redirect đến trang booking với params đã điền sẵn
            const bookingUrl = '{{ route('booking.form', ':roomId') }}'.replace(':roomId', roomId);
            const params = new URLSearchParams({
                checkin: checkin,
                checkout: checkout
            });
            window.location.href = bookingUrl + '?' + params.toString();
        }

        // Set min date cho date inputs
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const checkinInput = document.getElementById('checkin_filter');
            const checkoutInput = document.getElementById('checkout_filter');
            
            if (checkinInput) {
                checkinInput.setAttribute('min', today);
                checkinInput.addEventListener('change', function() {
                    if (checkoutInput) {
                        const nextDay = new Date(this.value);
                        nextDay.setDate(nextDay.getDate() + 1);
                        checkoutInput.setAttribute('min', nextDay.toISOString().split('T')[0]);
                        
                        // Auto-set checkout nếu chưa có hoặc nhỏ hơn checkin
                        if (!checkoutInput.value || checkoutInput.value <= this.value) {
                            checkoutInput.value = nextDay.toISOString().split('T')[0];
                        }
                    }
                });
            }
        });
    </script>
@endsection