@extends('layouts.client')
@section('title', $room->ten_phong)

@section('client_content')

<div class="bg-white py-20 w-full">
    <div class="w-full px-4 text-center">
        <nav class="text-sm text-gray-500 mb-8">
            <a href="{{ route('client.dashboard') }}" class="hover:text-gray-700 transition-colors">Trang chủ</a>
            <span class="mx-2">/</span>
            <a href="{{ route('client.phong') }}" class="hover:text-gray-700 transition-colors">Phòng nghỉ</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900">{{ $room->ten_phong }}</span>
        </nav>

        <h1 class="text-6xl md:text-8xl font-bold text-black mb-12">{{ $room->ten_phong }}</h1>

        <p class="text-xl text-gray-600 leading-relaxed max-w-4xl mx-auto">
            {{ $room->loaiPhong->ten_loai ?? 'N/A' }} - {{ $room->mo_ta ? Str::limit($room->mo_ta, 200) : 'Phòng nghỉ sang trọng với đầy đủ tiện nghi hiện đại' }}
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
                        <img src="{{ $room->img ? asset($room->img) : asset('img/room/room-1.jpg') }}"
                             alt="{{ $room->ten_phong }}"
                             class="w-full h-96 object-cover">
                    </div>
                </div>

                {{-- Room Details --}}
                <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                    <h2 class="text-4xl font-bold text-gray-900 mb-6">{{ $room->ten_phong }}</h2>

                    @if($room->mo_ta)
                    <p class="text-gray-600 text-lg leading-relaxed mb-8">{{ $room->mo_ta }}</p>
                    @endif

                    {{-- Room Information --}}
                    <div class="grid md:grid-cols-2 gap-8 mb-8">
                        <div class="border-l-4 border-blue-600 pl-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">Giá phòng</h4>
                            <p class="text-3xl font-bold text-blue-600">{{ number_format($room->gia, 0, ',', '.') }} VNĐ</p>
                            <p class="text-sm text-gray-500">/ đêm</p>
                        </div>

                        <div class="border-l-4 border-green-600 pl-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">Loại phòng</h4>
                            <p class="text-xl font-semibold text-green-600">{{ $room->loaiPhong->ten_loai ?? 'N/A' }}</p>
                            @if($room->loaiPhong && $room->loaiPhong->mo_ta)
                            <p class="text-sm text-gray-500 mt-1">{{ $room->loaiPhong->mo_ta }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Amenities --}}
                <div class="bg-white rounded-lg shadow-md p-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6">Tiện nghi phòng</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-center py-3">
                            <div class="w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center mr-4">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-gray-700 font-medium">WiFi miễn phí</span>
                        </div>
                        <div class="flex items-center py-3">
                            <div class="w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center mr-4">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-gray-700 font-medium">Điều hòa</span>
                        </div>
                        <div class="flex items-center py-3">
                            <div class="w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center mr-4">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-gray-700 font-medium">Tivi</span>
                        </div>
                        <div class="flex items-center py-3">
                            <div class="w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center mr-4">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-gray-700 font-medium">Phòng tắm riêng</span>
                        </div>
                        <div class="flex items-center py-3">
                            <div class="w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center mr-4">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-gray-700 font-medium">Minibar</span>
                        </div>
                        <div class="flex items-center py-3">
                            <div class="w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center mr-4">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-gray-700 font-medium">Dịch vụ 24/7</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Booking Sidebar --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-8 sticky top-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-8">Đặt phòng</h3>

                    <div class="mb-8 p-6 bg-blue-50 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-700 text-lg font-medium">Giá phòng</span>
                            <span class="text-3xl font-bold text-blue-600">{{ number_format($room->gia, 0, ',', '.') }} VNĐ</span>
                        </div>
                        <p class="text-sm text-gray-600">/ đêm</p>
                    </div>

                    <form action="{{ route('booking.form', ['phong' => $room->id]) }}" method="GET" onsubmit="return handleBooking(event)">
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">Ngày nhận phòng</label>
                                <input type="date" name="checkin" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">Ngày trả phòng</label>
                                <input type="date" name="checkout" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">Số người</label>
                                <select name="guests" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                    <option value="1">1 người</option>
                                    <option value="2" selected>2 người</option>
                                    <option value="3">3 người</option>
                                    <option value="4">4 người</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="w-full mt-8 bg-blue-600 text-white py-4 rounded-lg hover:bg-blue-700 transition-colors font-semibold text-lg">
                            Đặt phòng ngay
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Related Rooms --}}
        @if($relatedRooms && $relatedRooms->count() > 0)
        <div class="mt-20">
            <h3 class="text-3xl font-light text-gray-900 mb-12 text-center">Phòng liên quan</h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                @foreach($relatedRooms as $relatedRoom)
                <div class="group cursor-pointer" onclick="window.location.href='{{ route('client.phong.show', $relatedRoom->id) }}'">
                    <div class="relative overflow-hidden bg-white shadow-sm hover:shadow-lg transition-all duration-500">
                        <div class="relative h-64 overflow-hidden">
                            <img src="{{ $relatedRoom->img ? asset('uploads/phong/' . $relatedRoom->img) : asset('img/room/room-1.jpg') }}"
                                 alt="{{ $relatedRoom->ten_phong }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition-all duration-500"></div>

                            <div class="absolute top-4 right-4">
                                <div class="bg-black/80 backdrop-blur-sm text-white px-4 py-2">
                                    <div class="text-lg font-light">{{ number_format($relatedRoom->gia, 0, ',', '.') }}</div>
                                    <div class="text-xs text-gray-300">VNĐ / đêm</div>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <h4 class="text-xl font-light text-gray-900 mb-2 group-hover:text-gray-700 transition-colors">
                                {{ $relatedRoom->ten_phong }}
                            </h4>
                            <div class="flex items-center text-gray-500 text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                Xem chi tiết
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>

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
</script>

@endsection
