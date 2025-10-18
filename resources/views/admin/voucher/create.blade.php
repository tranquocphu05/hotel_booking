@extends('layouts.admin')

@section('title', 'Thêm Voucher mới')

@section('admin_content')
    <div class="p-6">
        {{-- Phần Tiêu đề và Nút Quay lại --}}
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-green-700">Thêm Voucher mới</h2>
            <a href="{{ route('admin.voucher.index') }}"
                class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Quay lại
            </a>
        </div>

        {{-- Khung form chính (Card) --}}
        <div class="bg-white shadow rounded p-8">
            <form action="{{ route('admin.voucher.store') }}" method="POST">
                @csrf

                {{-- Dòng 1: Mã voucher và Giảm (%) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    {{-- Mã voucher --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Mã voucher:</label>
                        <input type="text" name="ma_voucher" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700 placeholder-gray-400"
                            value="{{ old('ma_voucher') }}" placeholder="Nhập mã voucher">
                        @error('ma_voucher')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Giảm (%) --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Giảm (%):</label>
                        <input type="number" name="gia_tri" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700 placeholder-gray-400"
                            value="{{ old('gia_tri') }}" min="1" max="100" placeholder="Giá trị giảm (1-100)">
                        @error('gia_tri')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Dòng 2: Ngày bắt đầu và Ngày kết thúc --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    {{-- Ngày bắt đầu --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Ngày bắt đầu:</label>
                        <input type="date" name="ngay_bat_dau" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700"
                            value="{{ old('ngay_bat_dau') }}">
                        @error('ngay_bat_dau')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Ngày kết thúc --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Ngày kết thúc:</label>
                        <input type="date" name="ngay_ket_thuc" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700"
                            value="{{ old('ngay_ket_thuc') }}">
                        @error('ngay_ket_thuc')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Dòng 3: Số lượng và Loại phòng áp dụng --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    {{-- Số lượng --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Số lượng:</label>
                        <input type="number" name="so_luong" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700 placeholder-gray-400"
                            value="{{ old('so_luong') }}" min="1" placeholder="Số lượng voucher phát hành">
                        @error('so_luong')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Loại phòng áp dụng (Đã sửa) --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Loại phòng áp dụng:</label>
                        <select name="loai_phong_id" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700">
                            <option value="">-- Chọn loại phòng --</option>
                            {{-- Kiểm tra nếu biến $loaiPhongs tồn tại và là mảng/Collection --}}
                            @if (isset($loaiPhongs))
                                @foreach ($loaiPhongs as $lp)
                                    <option value="{{ $lp->id }}"
                                        {{ old('loai_phong_id') == $lp->id ? 'selected' : '' }}>
                                        {{ $lp->ten_loai }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('loai_phong_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Dòng 4: Điều kiện áp dụng (Full width) --}}
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-700">Điều kiện áp dụng:</label>
                    <textarea name="dieu_kien" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700 placeholder-gray-400 resize-none"
                        placeholder="Ví dụ: Giảm 20% cho đơn hàng từ 500.000 VNĐ">{{ old('dieu_kien') }}</textarea>
                    @error('dieu_kien')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Trạng thái (Ở cuối trước nút) --}}
                <div class="mb-6">
                    <label class="block mb-2 text-sm font-medium text-gray-700">Trạng thái:</label>
                    <select name="trang_thai" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700">
                        <option value="con_han" {{ old('trang_thai', 'con_han') == 'con_han' ? 'selected' : '' }}>
                            Còn hạn</option>
                        <option value="het_han" {{ old('trang_thai') == 'het_han' ? 'selected' : '' }}>
                            Hết hạn</option>
                        <option value="huy" {{ old('trang_thai') == 'huy' ? 'selected' : '' }}>Hủy
                        </option>
                    </select>
                    @error('trang_thai')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Action Buttons (Hủy và Thêm) --}}
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <button type="button" onclick="window.location='{{ route('admin.voucher.index') }}'"
                        class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-xl shadow-sm bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-300 hover:scale-105 font-medium">
                        Hủy
                    </button>
                    <button type="submit"
                        class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl shadow-lg hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-300 hover:scale-105 font-medium">
                        <i class="bi bi-plus-circle mr-2"></i>Thêm
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection