@extends('layouts.admin')

@section('title', 'Phòng trống theo thời gian')

@section('admin_content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-800">Phòng trống theo thời gian</h2>
                <a href="{{ route('admin.phong.index') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Quay lại danh sách phòng
                </a>
            </div>

            {{-- Bộ lọc thời gian --}}
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Tìm phòng trống</h3>
                <form method="GET" action="{{ route('admin.phong.available') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="ngay_nhan" class="block text-sm font-medium text-gray-700 mb-2">
                            Ngày nhận phòng
                        </label>
                        <input type="date" 
                               id="ngay_nhan" 
                               name="ngay_nhan" 
                               value="{{ $ngayNhan }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white shadow-sm hover:shadow-md">
                    </div>
                    
                    <div>
                        <label for="ngay_tra" class="block text-sm font-medium text-gray-700 mb-2">
                            Ngày trả phòng
                        </label>
                        <input type="date" 
                               id="ngay_tra" 
                               name="ngay_tra" 
                               value="{{ $ngayTra }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white shadow-sm hover:shadow-md">
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" 
                                class="w-full bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105">
                            <i class="fas fa-search mr-2"></i>
                            Tìm phòng trống
                        </button>
                    </div>
                </form>
            </div>

            {{-- Thống kê --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-bed text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Tổng phòng</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $availableRooms->count() + count($bookedRoomIds ?? []) }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Phòng trống</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $availableRooms->count() }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 rounded-lg">
                            <i class="fas fa-times-circle text-red-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Phòng đã đặt</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $bookedCount }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Danh sách phòng trống --}}
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">
                        Phòng trống từ {{ date('d/m/Y', strtotime($ngayNhan)) }} đến {{ date('d/m/Y', strtotime($ngayTra)) }}
                    </h3>
                </div>
                
                @if($availableRooms->count() > 0)
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($availableRooms as $room)
                                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300">
                                    @if($room->img)
                                        <div class="h-48 bg-gray-200 overflow-hidden">
                                            <img src="{{ asset($room->img) }}" 
                                                 alt="{{ $room->ten_phong }}" 
                                                 class="w-full h-full object-cover">
                                        </div>
                                    @else
                                        <div class="h-48 bg-gray-200 flex items-center justify-center">
                                            <i class="fas fa-bed text-gray-400 text-4xl"></i>
                                        </div>
                                    @endif
                                    
                                    <div class="p-4">
                                        <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ $room->ten_phong }}</h4>
                                        <p class="text-sm text-gray-600 mb-2">{{ $room->loaiPhong->ten_loai }}</p>
                                        
                                        @if($room->mo_ta)
                                            <div class="text-sm text-gray-500 mb-3 line-clamp-2">
                                                {!! Str::limit(strip_tags($room->mo_ta), 100) !!}
                                            </div>
                                        @endif
                                        
                                        <div class="flex items-center justify-between">
                                            <div class="text-lg font-bold text-blue-600">
                                                {{ number_format($room->gia, 0, ',', '.') }} VNĐ/đêm
                                            </div>
                                            <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                                                <i class="fas fa-check mr-1"></i>Trống
                                            </span>
                                        </div>
                                        
                                        <div class="mt-4 flex space-x-2">
                                            <a href="{{ route('admin.phong.show', $room->id) }}" 
                                               class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-center py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                                                <i class="fas fa-eye mr-1"></i>Xem chi tiết
                                            </a>
                                            <a href="{{ route('admin.dat_phong.create', ['phong_id' => $room->id, 'ngay_nhan' => $ngayNhan, 'ngay_tra' => $ngayTra]) }}" 
                                               class="flex-1 bg-green-500 hover:bg-green-600 text-white text-center py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                                                <i class="fas fa-calendar-plus mr-1"></i>Đặt phòng
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="p-12 text-center">
                        <div class="text-gray-400 mb-4">
                            <i class="fas fa-bed text-6xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Không có phòng trống</h3>
                        <p class="text-gray-500 mb-4">
                            Trong khoảng thời gian từ {{ date('d/m/Y', strtotime($ngayNhan)) }} đến {{ date('d/m/Y', strtotime($ngayTra)) }}, 
                            tất cả phòng đều đã được đặt.
                        </p>
                        <a href="{{ route('admin.phong.available') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors">
                            <i class="fas fa-search mr-2"></i>
                            Tìm khoảng thời gian khác
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('ngay_nhan').setAttribute('min', today);
        document.getElementById('ngay_tra').setAttribute('min', today);
        
        // Update ngay_tra when ngay_nhan changes
        document.getElementById('ngay_nhan').addEventListener('change', function() {
            const ngayNhan = new Date(this.value);
            const ngayTra = new Date(ngayNhan);
            ngayTra.setDate(ngayTra.getDate() + 1);
            
            document.getElementById('ngay_tra').value = ngayTra.toISOString().split('T')[0];
            document.getElementById('ngay_tra').setAttribute('min', this.value);
        });
    });
</script>
@endpush
