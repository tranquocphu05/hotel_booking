@extends('layouts.client')
@section('title', $reviewRoom->ten_phong ?? $loaiPhong->ten_loai)

@section('client_content')

    <div class="relative w-full bg-cover bg-center bg-no-repeat"
        style="background-image: url('{{ asset('img/hero/chitiet.jpg') }}');">

        {{-- Overlay tối --}}
        <div class="absolute inset-0 bg-black bg-opacity-40"></div>

        <div class="relative py-28 px-4 text-center text-white">
            <nav class="text-sm text-gray-200 mb-4">
                <a href="{{ route('client.dashboard') }}" class="hover:text-[#D4AF37] transition-colors">Trang chủ</a>
                <span class="mx-2">/</span>
                <a href="{{ route('client.phong') }}" class="hover:text-[#D4AF37] transition-colors">Phòng nghỉ</a>
                <span class="mx-2">/</span>
                <span class="text-[#FFD700] font-semibold">
                    {{ $reviewRoom->ten_phong ?? $loaiPhong->ten_loai }}
                </span>
            </nav>

            <h1 class="text-5xl md:text-7xl font-bold mb-6">
                {{ $reviewRoom->ten_phong ?? $loaiPhong->ten_loai }}
            </h1>

            <p class="text-lg md:text-xl text-gray-100 leading-relaxed max-w-4xl mx-auto">
                {{ $reviewRoom->mo_ta ?? $loaiPhong->mo_ta
                    ? Str::limit($reviewRoom->mo_ta ?? $loaiPhong->mo_ta, 200)
                    : 'Phòng nghỉ sang trọng với đầy đủ tiện nghi hiện đại' }}
            </p>
        </div>
    </div>

    <section class="bg-gray-50 py-16 w-full">
        <div class="w-full px-4">
            <div class="grid lg:grid-cols-3 gap-12">
                {{-- Main Content --}}
                <div class="lg:col-span-2">
                    {{-- Room Image Gallery --}}
                    <div class="mb-12">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <img src="{{ $reviewRoom->img ? asset($reviewRoom->img) : ($loaiPhong->anh ? asset($loaiPhong->anh) : asset('img/room/room-1.jpg')) }}"
                                alt="{{ $reviewRoom->ten_phong ?? $loaiPhong->ten_loai }}" class="w-full h-96 object-cover">
                        </div>
                    </div>

                    {{-- Room Details --}}
                    <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                        <h2 class="text-4xl font-bold text-gray-900 mb-6">
                            {{ $reviewRoom->ten_phong ?? $loaiPhong->ten_loai }}</h2>

                        @if ($reviewRoom->mo_ta ?? $loaiPhong->mo_ta)
                            <p class="text-gray-600 text-lg leading-relaxed mb-8">
                                {{ $reviewRoom->mo_ta ?? $loaiPhong->mo_ta }}</p>
                        @endif

                        {{-- Room Information --}}
                        <div class="grid md:grid-cols-2 gap-8 mb-8">
                            {{-- Price Section (Viền màu vàng) --}}
                            <div class="border-l-4 border-[#D4AF37] pl-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">Giá phòng</h4>
                                @if (isset($reviewRoom))
                                    @if ($reviewRoom->hasPromotion())
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-gray-700 text-lg font-medium">Giá phòng</span>
                                            <div class="text-right">
                                                <div class="text-lg text-gray-500 line-through">
                                                    {{ number_format($reviewRoom->gia_goc_hien_thi, 0, ',', '.') }} VNĐ
                                                </div>
                                                <div class="text-3xl font-bold text-red-600">
                                                    {{ number_format($reviewRoom->gia_hien_thi, 0, ',', '.') }} VNĐ
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-600">/ đêm</p>
                                        <div class="mt-2">
                                            <span
                                                class="inline-block bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                                Tiết kiệm
                                                {{ number_format($reviewRoom->gia_goc_hien_thi - $reviewRoom->gia_hien_thi, 0, ',', '.') }}
                                                VNĐ
                                            </span>
                                        </div>
                                    @else
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-gray-700 text-lg font-medium">Giá phòng</span>
                                            {{-- Màu xanh dương cho giá không khuyến mãi, như cũ --}}
                                            <span
                                                class="text-3xl font-bold text-blue-600">{{ number_format($reviewRoom->gia_hien_thi ?? $reviewRoom->gia, 0, ',', '.') }}
                                                VNĐ</span>
                                        </div>
                                        <p class="text-sm text-gray-600">/ đêm</p>
                                    @endif
                                @else
                                    {{-- Màu xanh dương cho giá cơ bản, như cũ --}}
                                    <p class="text-3xl font-bold text-blue-600">
                                        {{ number_format($loaiPhong->gia_co_ban, 0, ',', '.') }} VNĐ</p>
                                    <p class="text-sm text-gray-500">/ đêm</p>
                                @endif
                            </div>

                            {{-- Rating Section (Giữ nguyên) --}}
                            <div class="border-l-4 border-green-600 pl-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">Đánh giá</h4>
                                <div class="flex items-center mb-2">
                                    <div class="flex items-center">
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= floor($loaiPhong->diem_danh_gia))
                                                <i class="fas fa-star text-yellow-400 text-sm"></i>
                                            @elseif($i - 0.5 <= $loaiPhong->diem_danh_gia)
                                                <i class="fas fa-star-half-alt text-yellow-400 text-sm"></i>
                                            @else
                                                <i class="far fa-star text-gray-300 text-sm"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="ml-2 text-lg font-semibold text-gray-900">{{ $loaiPhong->stars }}</span>
                                </div>
                                <p class="text-sm text-gray-600">{{ $loaiPhong->rating_text }}
                                    ({{ $loaiPhong->so_luong_danh_gia }} đánh giá)</p>
                            </div>
                        </div>
                    </div>

                    {{-- Amenities & Services --}}
                    <div class="bg-white rounded-lg shadow-md p-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">Tiện nghi phòng</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Tiện nghi 1 (Màu vàng) --}}
                            <div class="flex items-center py-3">
                                <div class="w-6 h-6 bg-[#D4AF37] rounded-full flex items-center justify-center mr-4">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700 font-medium">WiFi miễn phí</span>
                            </div>
                            {{-- Tiện nghi 2 (Màu vàng) --}}
                            <div class="flex items-center py-3">
                                <div class="w-6 h-6 bg-[#D4AF37] rounded-full flex items-center justify-center mr-4">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700 font-medium">Điều hòa</span>
                            </div>
                            {{-- Tiện nghi 3 (Màu vàng) --}}
                            <div class="flex items-center py-3">
                                <div class="w-6 h-6 bg-[#D4AF37] rounded-full flex items-center justify-center mr-4">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700 font-medium">Tivi</span>
                            </div>
                            {{-- Tiện nghi 4 (Màu vàng) --}}
                            <div class="flex items-center py-3">
                                <div class="w-6 h-6 bg-[#D4AF37] rounded-full flex items-center justify-center mr-4">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700 font-medium">Phòng tắm riêng</span>
                            </div>
                            {{-- Tiện nghi 5 (Màu vàng) --}}
                            <div class="flex items-center py-3">
                                <div class="w-6 h-6 bg-[#D4AF37] rounded-full flex items-center justify-center mr-4">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700 font-medium">Minibar</span>
                            </div>
                            {{-- Tiện nghi 6 (Màu vàng) --}}
                            <div class="flex items-center py-3">
                                <div class="w-6 h-6 bg-[#D4AF37] rounded-full flex items-center justify-center mr-4">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700 font-medium">Dịch vụ 24/7</span>
                            </div>
                        </div>

                        @php
                            $roomForServices = $loaiPhong->phongs->first();
                            $serviceCatalog = [
                                ['icon' => 'fas fa-utensils', 'name' => 'Phục vụ ăn uống tại phòng'],
                                ['icon' => 'fas fa-concierge-bell', 'name' => 'Room Service 24/7'],
                                ['icon' => 'fas fa-tshirt', 'name' => 'Giặt ủi nhanh trong ngày'],
                                ['icon' => 'fas fa-car-side', 'name' => 'Đưa đón sân bay'],
                                ['icon' => 'fas fa-spa', 'name' => 'Spa & massage'],
                                ['icon' => 'fas fa-dumbbell', 'name' => 'Phòng gym miễn phí'],
                            ];
                            $serviceIndex = $roomForServices?->id ? $roomForServices->id % count($serviceCatalog) : 0;
                            $service = $serviceCatalog[$serviceIndex];
                        @endphp

                        <div class="mt-6">
                            <h4 class="text-xl font-semibold text-gray-900 mb-3">Dịch vụ phòng</h4>
                            @if ($roomForServices && $roomForServices->dich_vu)
                                <ul class="list-disc list-inside space-y-2 text-gray-700">
                                    @foreach (explode(',', $roomForServices->dich_vu) as $dichVu)
                                        <li class="flex items-center">
                                            <i class="fas fa-check text-green-600 mr-2"></i>
                                            {{ trim($dichVu) }}
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-gray-500 italic">Chưa có dịch vụ phòng</p>
                            @endif
                        </div>
                    </div>
                </div>

<div class="lg:col-span-1">
    <div class="bg-white rounded-lg shadow-md p-6 sticky top-8">
        <h3 class="text-2xl font-bold text-gray-900 mb-6">Đặt phòng</h3>

        {{-- Price Box (Giữ nguyên) --}}
        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
            @if(isset($reviewRoom))
                @if($reviewRoom->hasPromotion())
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-700 text-base font-medium">Giá phòng</span>
                        <div class="text-right">
                            <div class="text-base text-gray-500 line-through">
                                {{ number_format($reviewRoom->gia_goc_hien_thi, 0, ',', '.') }} VNĐ
                            </div>
                            <div class="text-2xl font-bold text-red-600">
                                {{ number_format($reviewRoom->gia_hien_thi, 0, ',', '.') }} VNĐ
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600">/ đêm</p>
                    <div class="mt-1">
                        <span class="inline-block bg-red-100 text-red-800 text-xs font-medium px-2 py-0.5 rounded-full">
                            Tiết kiệm {{ number_format($reviewRoom->gia_goc_hien_thi - $reviewRoom->gia_hien_thi, 0, ',', '.') }} VNĐ
                        </span>
                    </div>
                @else
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-700 text-base font-medium">Giá phòng</span>
                        <span class="text-2xl font-bold text-blue-600">{{ number_format($reviewRoom->gia_hien_thi ?? $reviewRoom->gia, 0, ',', '.') }} VNĐ</span>
                    </div>
                    <p class="text-xs text-gray-600">/ đêm</p>
                @endif
            @else
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-700 text-base font-medium">Giá phòng</span>
                    <span class="text-2xl font-bold text-blue-600">{{ number_format($loaiPhong->gia_co_ban, 0, ',', '.') }} VNĐ</span>
                </div>
                <p class="text-xs text-gray-600">/ đêm</p>
            @endif
        </div>

        <form action="{{ route('booking.form', ['phong' => $reviewRoom->id ?? ($loaiPhong->phongs->first()->id ?? 1)]) }}" method="GET" onsubmit="return handleBooking(event)">
            {{-- THAY ĐỔI: Tăng khoảng cách dọc giữa các trường nhập liệu từ space-y-5 lên space-y-6 --}}
            <div class="space-y-6">
                <div>
                    <label class="block text-base font-medium text-gray-700 mb-2">Ngày nhận phòng</label>
                    <input type="date" name="checkin" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-base">
                </div>

                <div>
                    <label class="block text-base font-medium text-gray-700 mb-2">Ngày trả phòng</label>
                    <input type="date" name="checkout" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-base">
                </div>

                <div>
                    <label class="block text-base font-medium text-gray-700 mb-2">Số người</label>
                    <select name="guests" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-base">
                        <option value="1">1 người</option>
                        <option value="2" selected>2 người</option>
                        <option value="3">3 người</option>
                        <option value="4">4 người</option>
                    </select>
                </div>
            </div>

            {{-- THAY ĐỔI: Tăng margin-top từ mt-6 lên mt-8 để kéo nút xa form hơn --}}
            <div class="mt-8 flex justify-center">
                <button type="submit" class="bg-[#D4AF37] text-white py-3 rounded-lg hover:bg-[#B8860B] transition-colors font-semibold text-sm shadow-lg hover:shadow-xl max-w-[50%] w-full">
                    <i class="fas fa-calendar-check mr-2"></i> Đặt phòng ngay
                </button>
            </div>
        </form>
    </div>
</div>
            </div>
            {{-- Related Rooms --}}
            @if ($relatedLoaiPhongs && $relatedLoaiPhongs->count() > 0)
                <div class="mt-20">
                    <h3 class="text-3xl font-light text-gray-900 mb-12 text-center">Loại phòng liên quan</h3>
                    <div class="swiper relatedRoomsSwiper relative">
                        <div class="swiper-wrapper">
                            @foreach ($relatedLoaiPhongs as $relatedLoaiPhong)
                                <div class="swiper-slide">
                                    <div class="group cursor-pointer"
                                        onclick="window.location.href='{{ route('client.phong.show', optional($relatedLoaiPhong->phongs->first())->id ?? 0) }}'">
                                        <div
                                            class="relative overflow-hidden bg-white shadow-sm hover:shadow-lg transition-all duration-500">
                                            <div class="relative h-64 overflow-hidden">
                                                <img src="{{ $relatedLoaiPhong->anh ? asset($relatedLoaiPhong->anh) : asset('img/room/room-1.jpg') }}"
                                                    alt="{{ $relatedLoaiPhong->ten_loai }}"
                                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                                                <div
                                                    class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition-all duration-500">
                                                </div>

                                                <div class="absolute top-4 right-4">
                                                    <div class="bg-black/80 backdrop-blur-sm text-white px-4 py-2">
                                                        @if ($relatedLoaiPhong->phongs && $relatedLoaiPhong->phongs->count() > 0)
                                                            @php $firstRoom = $relatedLoaiPhong->phongs->first(); @endphp
                                                            <div class="text-lg font-light">
                                                                {{ number_format($firstRoom->gia_hien_thi, 0, ',', '.') }}
                                                            </div>
                                                        @else
                                                            <div class="text-lg font-light">
                                                                {{ number_format($relatedLoaiPhong->gia_co_ban, 0, ',', '.') }}
                                                            </div>
                                                        @endif
                                                        <div class="text-xs text-gray-300">VNĐ / đêm</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="p-6">
                                                <h4
                                                    class="text-xl font-light text-gray-900 mb-2 group-hover:text-gray-700 transition-colors">
                                                    {{ $relatedLoaiPhong->ten_loai }}
                                                </h4>
                                                {{-- Icon và chữ Xem chi tiết chuyển sang màu vàng --}}
                                                <div class="flex items-center text-[#D4AF37] text-sm">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                    Xem chi tiết
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-pagination mt-6"></div>
                    </div>
            @endif
        </div>
    </section>
    {{-- Phần đánh giá phòng --}}
    <div class="mt-20">
        @include('client.content.comment', [
            'comments' => $comments,
            'room' => $reviewRoom ?? ($loaiPhong->phongs->first() ?? null),
        ])
    </div>
    <script>
        function handleBooking(event) {
            // event may be the Event object or the form element when called differently
            const form = event && event.target ? event.target : event;
            const formData = new FormData(form);
            const checkin = formData.get('checkin');
            const checkout = formData.get('checkout');

            if (!checkin || !checkout) {
                alert('Vui lòng chọn ngày nhận phòng và ngày trả phòng');
                return false;
            }

            if (new Date(checkout) <= new Date(checkin)) {
                alert('Ngày trả phòng phải sau ngày nhận phòng');
                return false;
            }

            // Valid — allow the form to submit normally
            return true;
        }

        // Initialize Related Rooms Swiper (3 rooms per slide on desktop)
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swiper !== 'undefined') {
                new Swiper('.relatedRoomsSwiper', {
                    slidesPerView: 1,
                    slidesPerGroup: 1,
                    spaceBetween: 16,
                    loop: false,
                    navigation: {
                        nextEl: '.relatedRoomsSwiper .swiper-button-next',
                        prevEl: '.relatedRoomsSwiper .swiper-button-prev',
                    },
                    pagination: {
                        el: '.relatedRoomsSwiper .swiper-pagination',
                        clickable: true,
                    },
                    breakpoints: {
                        768: {
                            slidesPerView: 2,
                            slidesPerGroup: 2,
                            spaceBetween: 20,
                        },
                        1024: {
                            slidesPerView: 3,
                            slidesPerGroup: 3,
                            spaceBetween: 24,
                        }
                    }
                });
            }
        });
    </script>

@endsection
