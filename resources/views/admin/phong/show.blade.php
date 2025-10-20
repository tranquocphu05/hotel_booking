@extends('layouts.admin')

@section('title', 'Chi tiết phòng')

@section('admin_content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-800">Chi tiết phòng</h2>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.phong.index') }}"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Quay lại
                    </a>
                    <a href="{{ route('admin.phong.edit', $phong->id) }}"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Chỉnh sửa
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Hình ảnh --}}
                    <div class="p-6">
                        @if($phong->img)
                            <div class="aspect-w-16 aspect-h-9 rounded-lg overflow-hidden">
                                <img src="{{ asset($phong->img) }}" 
                                     alt="{{ $phong->ten_phong }}" 
                                     class="w-full h-64 object-cover">
                            </div>
                        @else
                            <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                                <i class="fas fa-bed text-gray-400 text-6xl"></i>
                            </div>
                        @endif
                    </div>

                    {{-- Thông tin chi tiết --}}
                    <div class="p-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ $phong->ten_phong }}</h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <i class="fas fa-tag text-blue-500 w-5 mr-3"></i>
                                <span class="text-gray-600">Loại phòng:</span>
                                <span class="ml-2 font-medium text-gray-900">{{ $phong->loaiPhong->ten_loai }}</span>
                            </div>

                            <div class="flex items-center">
                                <i class="fas fa-dollar-sign text-green-500 w-5 mr-3"></i>
                                <span class="text-gray-600">Giá:</span>
                                <span class="ml-2 font-bold text-green-600 text-xl">
                                    {{ number_format($phong->gia, 0, ',', '.') }} VNĐ/đêm
                                </span>
                            </div>

                            <div class="flex items-center">
                                <i class="fas fa-info-circle text-purple-500 w-5 mr-3"></i>
                                <span class="text-gray-600">Trạng thái:</span>
                                <span class="ml-2 px-3 py-1 rounded-full text-sm font-medium
                                    @if($phong->trang_thai === 'hien') bg-green-100 text-green-800
                                    @elseif($phong->trang_thai === 'an') bg-red-100 text-red-800
                                    @elseif($phong->trang_thai === 'chong') bg-orange-100 text-orange-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    @if($phong->trang_thai === 'hien') Hiện
                                    @elseif($phong->trang_thai === 'an') Ẩn
                                    @elseif($phong->trang_thai === 'chong') Chống
                                    @else {{ $phong->trang_thai }} @endif
                                </span>
                            </div>

                            @if($phong->mo_ta)
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Mô tả</h4>
                                    <div class="text-gray-600 leading-relaxed">
                                        {!! $phong->mo_ta !!}
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Thống kê đặt phòng --}}
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Thống kê đặt phòng</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-check text-blue-500 text-xl mr-3"></i>
                                        <div>
                                            <p class="text-sm text-gray-600">Tổng đặt phòng</p>
                                            <p class="text-2xl font-bold text-blue-600">
                                                {{ \App\Models\DatPhong::where('phong_id', $phong->id)->count() }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                                        <div>
                                            <p class="text-sm text-gray-600">Đã xác nhận</p>
                                            <p class="text-2xl font-bold text-green-600">
                                                {{ \App\Models\DatPhong::where('phong_id', $phong->id)->where('trang_thai', 'da_xac_nhan')->count() }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
