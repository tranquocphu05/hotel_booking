@extends('layouts.client')

@section('client_content')
    <div class="relative w-full bg-cover bg-center bg-no-repeat -mt-2"
        style="background-image: url('{{ asset('img/blog/blog-11.jpg') }}');">

        {{-- Lớp phủ tối giúp chữ nổi bật --}}
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
        <div class="w-full px-4">
            {{-- Filter Section --}}
            <div class="bg-white p-8 mb-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Loại phòng</label>
                        <select name="loai_phong" id="loai_phong"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
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
                        <label class="block text-sm font-medium text-gray-700 mb-3">Giá từ</label>
                        <input type="number" name="gia_min" id="gia_min" placeholder="0" value="{{ request('gia_min') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Giá đến</label>
                        <input type="number" name="gia_max" id="gia_max" placeholder="Không giới hạn"
                            value="{{ request('gia_max') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <div class="flex items-end">
                        <button onclick="filterRooms()"
                            class="w-full 
    bg-[#D4AF37] 
    text-white 
    px-6 py-3 rounded-lg 
    hover:bg-[#b68b00] 
    transition-colors font-medium">
                            Tìm phòng
                        </button>
                    </div>
                </div>
            </div>

<div class="space-y-6">
    @forelse($phongs as $phong)
        <div class="group cursor-pointer"
            onclick="window.location.href='{{ route('client.phong.show', $phong->id) }}'">
            <div
                class="bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden">
                <div class="flex">
                    {{-- Image --}}
                    <div class="w-1/3 h-64 relative overflow-hidden">
                        @php
                            $roomImg = null;
                            if (!empty($phong->img)) {
                                $roomImg = asset($phong->img); // ảnh lưu trong public/uploads/...
                            } elseif (!empty($phong->loaiPhong->anh)) {
                                $roomImg = asset($phong->loaiPhong->anh);
                            } else {
                                $roomImg = asset('img/room/room-1.jpg');
                            }
                        @endphp
                        <img src="{{ $roomImg }}" alt="{{ $phong->ten_phong }}"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <div class="absolute top-4 left-4">
                            <span class="bg-white/90 text-gray-900 px-3 py-1 text-sm font-medium rounded">
                                {{ $phong->loaiPhong->ten_loai }}
                            </span>
                        </div>
                        <div class="absolute top-4 right-4">
                            <div class="bg-black/80 text-white px-3 py-1 rounded">
                                @if ($phong->hasPromotion())
                                    <div class="text-sm text-gray-300 line-through">
                                        {{ number_format($phong->gia_goc_hien_thi, 0, ',', '.') }}
                                    </div>
                                    <div class="text-lg font-semibold text-[#D4AF37]">
                                        {{ number_format($phong->gia_hien_thi, 0, ',', '.') }}
                                    </div>
                                    <div class="text-xs">VNĐ / đêm</div>
                                @else
                                    <div class="text-lg font-semibold">
                                        {{ number_format($phong->gia, 0, ',', '.') }}</div>
                                    <div class="text-xs">VNĐ / đêm</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 p-8">
                        <h3
                            class="text-3xl font-bold text-gray-900 mb-4 group-hover:text-[#D4AF37] transition-colors">
                            {{ $phong->ten_phong ?: $phong->loaiPhong->ten_loai }}
                        </h3>
                        @php
                            $amenities = [
                                ['icon' => 'fas fa-wifi', 'name' => 'WiFi miễn phí'],
                                ['icon' => 'fas fa-snowflake', 'name' => 'Điều hòa'],
                                ['icon' => 'fas fa-tv', 'name' => 'Tivi'],
                                ['icon' => 'fas fa-bath', 'name' => 'Phòng tắm riêng'],
                                ['icon' => 'fas fa-wine-glass', 'name' => 'Minibar'],
                                ['icon' => 'fas fa-concierge-bell', 'name' => 'Dịch vụ 24/7'],
                            ];
                            $services = [
                                ['icon' => 'fas fa-utensils', 'name' => 'Bữa sáng'],
                                ['icon' => 'fas fa-car', 'name' => 'Đưa đón sân bay'],
                                ['icon' => 'fas fa-spa', 'name' => 'Spa'],
                                ['icon' => 'fas fa-swimming-pool', 'name' => 'Hồ bơi'],
                                ['icon' => 'fas fa-dumbbell', 'name' => 'Gym'],
                                ['icon' => 'fas fa-broom', 'name' => 'Dọn phòng'],
                            ];
                            $amenity = $amenities[$phong->id % count($amenities)];
                            $service = $services[$phong->id % count($services)];
                        @endphp
                        <div class="flex items-center gap-3 mb-4">
                            <span
                                class="inline-flex items-center gap-2 text-sm bg-blue-50 text-blue-700 px-3 py-1 rounded-full">
                                <i class="{{ $amenity['icon'] }}"></i>
                                {{ $amenity['name'] }}
                            </span>
                            <span
                                class="inline-flex items-center gap-2 text-sm bg-green-50 text-green-700 px-3 py-1 rounded-full">
                                <i class="{{ $service['icon'] }}"></i>
                                {{ $service['name'] }}
                            </span>
                        </div>

                        @if ($phong->mo_ta)
                            <p class="text-gray-600 leading-relaxed mb-6">
                                {{ Str::limit($phong->mo_ta, 150) }}
                            </p>
                        @elseif($phong->loaiPhong->mo_ta)
                            <p class="text-gray-600 leading-relaxed mb-6">
                                {{ Str::limit($phong->loaiPhong->mo_ta, 150) }}
                            </p>
                        @endif

                        <div class="flex items-center justify-between">
                            <div class="flex items-center text-[#D4AF37] font-medium">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                                Xem chi tiết
                            </div>
                            <div class="text-gray-400 group-hover:text-[#D4AF37] transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full text-center py-24">
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
            const loaiPhong = document.getElementById('loai_phong').value;
            const giaMin = document.getElementById('gia_min').value;
            const giaMax = document.getElementById('gia_max').value;

            const params = new URLSearchParams();
            if (loaiPhong) params.append('loai_phong', loaiPhong);
            if (giaMin) params.append('gia_min', giaMin);
            if (giaMax) params.append('gia_max', giaMax);

            window.location.href = '{{ route('client.phong') }}?' + params.toString();
        }

        function bookRoom(roomId) {
            // For now, show alert. In the future, redirect to booking page
            alert(`Chức năng đặt phòng ${roomId} sẽ được phát triển sớm!`);

            // Future implementation:
            // window.location.href = '{{ url('/booking') }}/' + roomId;
        }
    </script>
@endsection
