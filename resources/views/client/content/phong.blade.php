@extends('layouts.client')

@section('client_content')

<div class="bg-white py-20 w-full">
    <div class="w-full px-4 text-center">
        <nav class="text-sm text-gray-500 mb-8">
            <a href="{{ url('/') }}" class="hover:text-gray-700 transition-colors">Trang chủ</a> / 
            <span class="text-gray-900">Phòng nghỉ</span>
        </nav>
        
        <h1 class="text-6xl md:text-8xl font-bold text-black mb-12">Phòng Nghỉ</h1>
        
        <p class="text-xl text-gray-600 leading-relaxed max-w-4xl mx-auto">
            Khách sạn Luxury Hotel sở hữu những căn phòng nghỉ kết hợp hoàn hảo phong cách thiết kế nội thất truyền thống trang nhã cùng với các tiện nghi đẳng cấp. Nơi đây là điểm đến lý tưởng để nghỉ dưỡng và thưởng lãm vẻ đẹp của thành phố.
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
                    <select name="loai_phong" id="loai_phong" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="">Tất cả loại phòng</option>
                        @foreach($loaiPhongs as $loai)
                            <option value="{{ $loai->id }}" {{ request('loai_phong') == $loai->id ? 'selected' : '' }}>
                                {{ $loai->ten_loai }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Giá từ</label>
                    <input type="number" name="gia_min" id="gia_min" placeholder="0" 
                           value="{{ request('gia_min') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Giá đến</label>
                    <input type="number" name="gia_max" id="gia_max" placeholder="Không giới hạn" 
                           value="{{ request('gia_max') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>
                <div class="flex items-end">
                    <button onclick="filterRooms()" class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                        Tìm phòng
                    </button>
                </div>
            </div>
        </div>

        {{-- Rooms List --}}
        <div class="space-y-6">
            @forelse($rooms as $phong)
            <div class="group cursor-pointer" onclick="window.location.href='{{ route('client.phong.show', $phong->id) }}'">
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden">
                    <div class="flex">
                        {{-- Image --}}
                        <div class="w-1/3 h-64 relative overflow-hidden">
                            <img src="{{ $phong->img ? asset('uploads/phong/' . $phong->img) : asset('img/room/room-1.jpg') }}" 
                                 alt="{{ $phong->ten_phong }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute top-4 left-4">
                                <span class="bg-white/90 text-gray-900 px-3 py-1 text-sm font-medium rounded">
                                    {{ $phong->loaiPhong->ten_loai ?? 'N/A' }}
                                </span>
                            </div>
                            <div class="absolute top-4 right-4">
                                <div class="bg-black/80 text-white px-3 py-1 rounded">
                                    <div class="text-lg font-semibold">{{ number_format($phong->gia, 0, ',', '.') }}</div>
                                    <div class="text-xs">VNĐ / đêm</div>
                                </div>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 p-8">
                            <h3 class="text-3xl font-bold text-gray-900 mb-4 group-hover:text-blue-600 transition-colors">
                                {{ $phong->ten_phong }}
                            </h3>
                            
                            @if($phong->mo_ta)
                            <p class="text-gray-600 leading-relaxed mb-6">
                                {{ Str::limit($phong->mo_ta, 150) }}
                            </p>
                            @endif

                            <div class="flex items-center justify-between">
                                <div class="flex items-center text-blue-600 font-medium">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    Xem chi tiết
                                </div>
                                <div class="text-gray-400 group-hover:text-blue-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <h3 class="text-3xl font-light text-gray-600 mb-6">Không tìm thấy phòng</h3>
                <p class="text-gray-500 text-lg">Vui lòng thử lại với bộ lọc khác</p>
            </div>
            @endforelse
        </div>

    {{-- Pagination --}}
    @if(is_object($rooms) && method_exists($rooms, 'hasPages') && $rooms->hasPages())
    <div class="mt-12">
        {{ $rooms->links() }}
    </div>
    @endif
</section>

<script>
function filterRooms() {
    const loaiPhong = document.getElementById('loai_phong').value;
    const giaMin = document.getElementById('gia_min').value;
    const giaMax = document.getElementById('gia_max').value;
    
    const params = new URLSearchParams();
    if (loaiPhong) params.append('loai_phong', loaiPhong);
    if (giaMin) params.append('gia_min', giaMin);
    if (giaMax) params.append('gia_max', giaMax);
    
    window.location.href = '{{ route("client.phong") }}?' + params.toString();
}

function bookRoom(roomId) {
    // For now, show alert. In the future, redirect to booking page
    alert(`Chức năng đặt phòng ${roomId} sẽ được phát triển sớm!`);
    
    // Future implementation:
    // window.location.href = '{{ url("/booking") }}/' + roomId;
}
</script>

@endsection
