@extends('layouts.client')
@section('title', $loaiPhong->ten_loai)

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
                    {{ $loaiPhong->ten_loai }}
                </span>
            </nav>

            <h1 class="text-5xl md:text-7xl font-bold mb-6">
                {{ $loaiPhong->ten_loai }}
            </h1>

            <p class="text-lg md:text-xl text-gray-100 leading-relaxed max-w-4xl mx-auto">
                {{ $loaiPhong->mo_ta
                    ? Str::limit($loaiPhong->mo_ta, 200)
                    : 'Phòng nghỉ sang trọng với đầy đủ tiện nghi hiện đại' }}
            </p>
        </div>
    </div>

    <section class="bg-gray-50 py-16 w-full">
        <div class="w-full mx-auto px-8 lg:px-12">
            <div class="grid lg:grid-cols-3 gap-10">
                {{-- Main Content --}}
                <div class="lg:col-span-2">
                    {{-- Room Image Gallery --}}
                    <div class="mb-10">
                        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                            <img src="{{ $loaiPhong->anh ? asset($loaiPhong->anh) : asset('img/room/room-1.jpg') }}"
                                alt="{{ $loaiPhong->ten_loai }}" class="w-full h-72 md:h-96 lg:h-[430px] object-cover"
                                loading="lazy" decoding="async">
                        </div>
                    </div>

                    {{-- Room Details --}}
                    <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                        <h2 class="text-4xl font-bold text-gray-900 mb-6">
                            {{ $loaiPhong->ten_loai }}</h2>

                        @if ($loaiPhong->mo_ta)
                            <p class="text-gray-600 text-lg leading-relaxed mb-8">
                                {{ $loaiPhong->mo_ta }}</p>
                        @endif

                        {{-- Hiển thị số phòng còn trống --}}
                        <div class="mb-6" id="availability_display">
                            @if(isset($availableCount) && $availableCount !== null)
                                @if($availableCount > 0)
                                    <div class="inline-flex items-center gap-2 text-sm bg-green-50 text-green-700 px-5 py-3 rounded-full font-medium shadow-sm">
                                        <i class="fas fa-bed text-lg"></i>
                                        <span class="text-base">Còn <strong class="text-xl text-green-800">{{ $availableCount }}</strong>/{{ $loaiPhong->so_luong_phong }} phòng trống</span>
                                        @if($checkin && $checkout)
                                            <span class="text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded">({{ date('d/m/Y', strtotime($checkin)) }} - {{ date('d/m/Y', strtotime($checkout)) }})</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="bg-orange-50 border-l-4 border-orange-400 p-4 rounded-lg">
                                        <div class="flex items-start">
                                            <i class="fas fa-exclamation-triangle text-orange-600 text-xl mr-3 mt-1"></i>
                                            <div class="flex-1">
                                                <h4 class="text-orange-800 font-bold text-base mb-2">Đã hết phòng trong khoảng thời gian này</h4>
                                                @if($checkin && $checkout)
                                                    <p class="text-sm text-orange-700">
                                                        Không còn phòng trống từ <strong>{{ date('d/m/Y', strtotime($checkin)) }}</strong> đến <strong>{{ date('d/m/Y', strtotime($checkout)) }}</strong>
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="inline-flex items-center gap-2 text-sm bg-gray-50 text-gray-700 px-4 py-2 rounded-full font-medium">
                                    <i class="fas fa-bed"></i>
                                    <span>Còn {{ max(0, $loaiPhong->so_luong_trong) }}/{{ $loaiPhong->so_luong_phong }} phòng</span>
                                    <span class="text-xs text-gray-500">(chọn ngày để xem số phòng trống chính xác)</span>
                                </div>
                            @endif
                        </div>

                        {{-- Room Information --}}
                        <div class="grid md:grid-cols-2 gap-8 mb-8">
                            {{-- Price Section (Viền màu vàng) --}}
                            <div class="border-l-4 border-[#D4AF37] pl-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">Giá phòng</h4>
                                @if($loaiPhong->gia_khuyen_mai)
                                    @php
                                        $discountPercent = round((($loaiPhong->gia_co_ban - $loaiPhong->gia_khuyen_mai) / $loaiPhong->gia_co_ban) * 100);
                                    @endphp
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="inline-flex items-center gap-1 bg-gradient-to-r from-red-500 to-red-600 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg">
                                            <i class="fas fa-tag"></i>
                                            <span>GIẢM {{ $discountPercent }}%</span>
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-700 text-lg font-medium">Giá khuyến mãi</span>
                                        <span class="text-3xl font-bold text-red-600">
                                            {{ number_format($loaiPhong->gia_khuyen_mai, 0, ',', '.') }} VNĐ
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-gray-500 text-sm font-medium">Giá gốc</span>
                                        <span class="text-xl font-medium text-gray-400 line-through">
                                            {{ number_format($loaiPhong->gia_co_ban, 0, ',', '.') }} VNĐ
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600">/ đêm</p>
                                @else
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-700 text-lg font-medium">Giá phòng</span>
                                        <span class="text-3xl font-bold text-blue-600">
                                            {{ number_format($loaiPhong->gia_co_ban, 0, ',', '.') }} VNĐ
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600">/ đêm</p>
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
                            $serviceCatalog = [
                                ['icon' => 'fas fa-utensils', 'name' => 'Phục vụ ăn uống tại phòng'],
                                ['icon' => 'fas fa-concierge-bell', 'name' => 'Room Service 24/7'],
                                ['icon' => 'fas fa-tshirt', 'name' => 'Giặt ủi nhanh trong ngày'],
                                ['icon' => 'fas fa-car-side', 'name' => 'Đưa đón sân bay'],
                                ['icon' => 'fas fa-spa', 'name' => 'Spa & massage'],
                                ['icon' => 'fas fa-dumbbell', 'name' => 'Phòng gym miễn phí'],
                            ];
                            $serviceIndex = $loaiPhong->id % count($serviceCatalog);
                            $service = $serviceCatalog[$serviceIndex];
                        @endphp

                        <div class="mt-6">
                            <h4 class="text-xl font-semibold text-gray-900 mb-3">Dịch vụ phòng</h4>
                            <ul class="list-disc list-inside space-y-2 text-gray-700">
                                <li class="flex items-center">
                                    <i class="{{ $service['icon'] }} text-green-600 mr-2"></i>
                                    {{ $service['name'] }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

<div class="lg:col-span-1">
    <div class="bg-white rounded-lg shadow-md p-6 sticky top-8">
        <h3 class="text-2xl font-bold text-gray-900 mb-6">Đặt phòng</h3>

        {{-- Price Box --}}
        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
            @if($loaiPhong->gia_khuyen_mai)
                @php
                    $discountPercent = round((($loaiPhong->gia_co_ban - $loaiPhong->gia_khuyen_mai) / $loaiPhong->gia_co_ban) * 100);
                @endphp
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center gap-1 bg-gradient-to-r from-red-500 to-red-600 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg">
                        <i class="fas fa-tag"></i>
                        <span>GIẢM {{ $discountPercent }}%</span>
                    </span>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-700 text-base font-medium">Giá khuyến mãi</span>
                    <span class="text-2xl font-bold text-red-600">{{ number_format($loaiPhong->gia_khuyen_mai, 0, ',', '.') }} VNĐ</span>
                </div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-gray-500 text-sm">Giá gốc</span>
                    <span class="text-lg font-medium text-gray-400 line-through">{{ number_format($loaiPhong->gia_co_ban, 0, ',', '.') }} VNĐ</span>
                </div>
            @else
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-700 text-base font-medium">Giá phòng</span>
                    <span class="text-2xl font-bold text-blue-600">{{ number_format($loaiPhong->gia_co_ban, 0, ',', '.') }} VNĐ</span>
                </div>
            @endif
            <p class="text-xs text-gray-600">/ đêm</p>
        </div>

        <form action="{{ route('booking.form', ['loaiPhongId' => $loaiPhong->id]) }}" method="GET" onsubmit="return handleBooking(event)">
            {{-- THAY ĐỔI: Tăng khoảng cách dọc giữa các trường nhập liệu từ space-y-5 lên space-y-6 --}}
            <div class="space-y-6">
                <div>
                    <label class="block text-base font-medium text-gray-700 mb-2">Ngày nhận phòng</label>
                    <input type="date" name="checkin" id="checkin_input" 
                            value="{{ old('checkin', $checkin ?? now()->format('Y-m-d')) }}"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-base">
                </div>

                <div>
                    <label class="block text-base font-medium text-gray-700 mb-2">Ngày trả phòng</label>
                    <input type="date" name="checkout" id="checkout_input"
                            value="{{ old('checkout', $checkout ?? now()->addDay()->format('Y-m-d')) }}"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-base">
                </div>

                {{-- Chính sách check-in/check-out --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Chính sách check-in/check-out</h4>
                    <ul class="text-xs text-gray-700 space-y-1">
                        <li>• Giờ nhận phòng: từ 14:00</li>
                        <li>• Giờ trả phòng: trước 12:00</li>
                        <li class="text-orange-600 font-medium">• Đến trước 14:00 hoặc trả phòng sau 12:00 có thể phát sinh phụ thu theo chính sách khách sạn.</li>
                    </ul>
                </div>

                {{-- Số khách --}}
                <div>
                    <label class="block text-base font-medium text-gray-700 mb-3">Số khách</label>
                    
                    {{-- Người lớn --}}
                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-user text-gray-500"></i>
                            <div>
                                <span class="text-sm font-medium text-gray-700">Người lớn</span>
                                <p class="text-xs text-gray-400">Từ 13 tuổi trở lên</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="decrementGuest('adults')" class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center text-gray-500 hover:border-[#D4AF37] hover:text-[#D4AF37] transition-colors">
                                <i class="fas fa-minus text-xs"></i>
                            </button>
                            <span id="adults_display" class="w-8 text-center text-base font-semibold text-gray-700">2</span>
                            <input type="hidden" name="adults" id="adults_input" value="2">
                            <button type="button" onclick="incrementGuest('adults')" class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center text-gray-500 hover:border-[#D4AF37] hover:text-[#D4AF37] transition-colors">
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Trẻ em --}}
                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-child text-green-500"></i>
                            <div>
                                <span class="text-sm font-medium text-gray-700">Trẻ em</span>
                                <p class="text-xs text-gray-400">Từ 6 - 12 tuổi</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="decrementGuest('children')" class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center text-gray-500 hover:border-[#D4AF37] hover:text-[#D4AF37] transition-colors">
                                <i class="fas fa-minus text-xs"></i>
                            </button>
                            <span id="children_display" class="w-8 text-center text-base font-semibold text-gray-700">0</span>
                            <input type="hidden" name="children" id="children_input" value="0">
                            <button type="button" onclick="incrementGuest('children')" class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center text-gray-500 hover:border-[#D4AF37] hover:text-[#D4AF37] transition-colors">
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Em bé --}}
                    <div class="flex items-center justify-between py-3">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-baby text-pink-500"></i>
                            <div>
                                <span class="text-sm font-medium text-gray-700">Em bé</span>
                                <p class="text-xs text-gray-400">Dưới 6 tuổi</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="decrementGuest('infants')" class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center text-gray-500 hover:border-[#D4AF37] hover:text-[#D4AF37] transition-colors">
                                <i class="fas fa-minus text-xs"></i>
                            </button>
                            <span id="infants_display" class="w-8 text-center text-base font-semibold text-gray-700">0</span>
                            <input type="hidden" name="infants" id="infants_input" value="0">
                            <button type="button" onclick="incrementGuest('infants')" class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center text-gray-500 hover:border-[#D4AF37] hover:text-[#D4AF37] transition-colors">
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Thông tin sức chứa --}}
                    <div class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                        <p class="text-xs text-blue-700">
                            <i class="fas fa-users mr-1"></i>
                            <strong>Sức chứa tối đa:</strong> {{ $loaiPhong->suc_chua ?? 2 }} người lớn, {{ $loaiPhong->suc_chua_tre_em ?? 2 }} trẻ em, {{ $loaiPhong->suc_chua_em_be ?? 2 }} em bé
                        </p>
                    </div>
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
                                        onclick="window.location.href='{{ route('client.phong.show', $relatedLoaiPhong->id) }}'">
                                        <div
                                            class="relative overflow-hidden bg-white shadow-sm hover:shadow-lg transition-all duration-500">
                                            <div class="relative h-64 overflow-hidden">
                                                <img src="{{ $relatedLoaiPhong->anh ? asset($relatedLoaiPhong->anh) : asset('img/room/room-1.jpg') }}"
                                                    alt="{{ $relatedLoaiPhong->ten_loai }}"
                                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                                                <div
                                                    class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition-all duration-500">
                                                </div>

                                                <div class="absolute top-4 right-4 flex flex-col items-end gap-1">
                                                    @if($relatedLoaiPhong->gia_khuyen_mai)
                                                        @php
                                                            $discountPercent = round((($relatedLoaiPhong->gia_co_ban - $relatedLoaiPhong->gia_khuyen_mai) / $relatedLoaiPhong->gia_co_ban) * 100);
                                                        @endphp
                                                        {{-- Badge khuyến mãi (giữ lại), bỏ ô giá --}}
                                                        <div class="inline-flex items-center gap-1.5 bg-gradient-to-r from-red-500 to-red-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-lg">
                                                            <i class="fas fa-tag text-white text-xs"></i>
                                                            <span>GIẢM {{ $discountPercent }}%</span>
                                                        </div>
                                                    @endif
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
            'room' => $reviewRoom ?? $loaiPhong,
        ])
    </div>
    <script>
        // Guest limits from database
        const guestLimits = {
            adults: { min: 1, max: {{ $loaiPhong->suc_chua ?? 2 }}, current: 2 },
            children: { min: 0, max: {{ $loaiPhong->suc_chua_tre_em ?? 2 }}, current: 0 },
            infants: { min: 0, max: {{ $loaiPhong->suc_chua_em_be ?? 2 }}, current: 0 }
        };

        // Guest counter functions
        function incrementGuest(type) {
            const limit = guestLimits[type];
            if (limit.current < limit.max) {
                limit.current++;
                updateGuestDisplay(type);
            }
        }

        function decrementGuest(type) {
            const limit = guestLimits[type];
            if (limit.current > limit.min) {
                limit.current--;
                updateGuestDisplay(type);
            }
        }

        function updateGuestDisplay(type) {
            const display = document.getElementById(type + '_display');
            const input = document.getElementById(type + '_input');
            if (display) display.textContent = guestLimits[type].current;
            if (input) input.value = guestLimits[type].current;
        }

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

        // Cập nhật availability khi user thay đổi ngày
        function updateAvailabilityOnDateChange() {
            const checkinInput = document.getElementById('checkin_input');
            const checkoutInput = document.getElementById('checkout_input');
            
            if (!checkinInput || !checkoutInput) return;

            function fetchAvailability() {
                const checkin = checkinInput.value;
                const checkout = checkoutInput.value;
                
                if (!checkin || !checkout) return;
                
                if (new Date(checkout) <= new Date(checkin)) return;

                fetch('{{ route("booking.available_count") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        loai_phong_id: {{ $loaiPhong->id }},
                        checkin: checkin,
                        checkout: checkout
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const availableCount = Math.max(0, data.availableCount);
                        const availabilityEl = document.getElementById('availability_display');
                        
                        if (availabilityEl) {
                            const formatDate = (dateStr) => {
                                const date = new Date(dateStr);
                                const day = String(date.getDate()).padStart(2, '0');
                                const month = String(date.getMonth() + 1).padStart(2, '0');
                                const year = date.getFullYear();
                                return `${day}/${month}/${year}`;
                            };
                            
                            if (availableCount === 0) {
                                // Hiển thị thông báo chi tiết khi hết phòng
                                availabilityEl.innerHTML = `
                                    <div class="bg-orange-50 border-l-4 border-orange-400 p-4 rounded-lg">
                                        <div class="flex items-start">
                                            <i class="fas fa-exclamation-triangle text-orange-600 text-xl mr-3 mt-1"></i>
                                            <div class="flex-1">
                                                <h4 class="text-orange-800 font-bold text-base mb-2">Đã hết phòng trong khoảng thời gian này</h4>
                                                <p class="text-sm text-orange-700">
                                                    Không còn phòng trống từ <strong>${formatDate(checkin)}</strong> đến <strong>${formatDate(checkout)}</strong>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            } else {
                                // Hiển thị số phòng trống
                                availabilityEl.innerHTML = `
                                    <div class="inline-flex items-center gap-2 text-sm bg-green-50 text-green-700 px-5 py-3 rounded-full font-medium shadow-sm">
                                        <i class="fas fa-bed text-lg"></i>
                                        <span class="text-base">Còn <strong class="text-xl text-green-800">${availableCount}</strong>/{{ $loaiPhong->so_luong_phong }} phòng trống</span>
                                        <span class="text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded">(${formatDate(checkin)} - ${formatDate(checkout)})</span>
                                    </div>
                                `;
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating availability:', error);
                });
            }

            checkinInput.addEventListener('change', fetchAvailability);
            checkoutInput.addEventListener('change', fetchAvailability);
            
            // Gọi lần đầu nếu đã có giá trị (từ query params hoặc form)
            if (checkinInput.value && checkoutInput.value) {
                // Delay để đảm bảo DOM đã sẵn sàng
                setTimeout(function() {
                    fetchAvailability();
                }, 300);
            }
        }

        // Gọi khi DOM ready
        document.addEventListener('DOMContentLoaded', function() {
            updateAvailabilityOnDateChange();
        });

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
