@extends('layouts.admin')

@section('title', 'Chỉnh sửa Voucher')

@section('admin_content')
    <div class="p-6">
        {{-- Phần Tiêu đề Form và Nút Quay lại (Giống form Thêm) --}}
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-green-700">Chỉnh sửa Voucher</h2>
            <a href="{{ route('admin.voucher.index') }}"
                class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Quay lại
            </a>
        </div>

        {{-- Khung form chính (Card) --}}
        <div class="bg-white shadow rounded p-8">
            <form action="{{ route('admin.voucher.update', $voucher->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Dòng 1: Mã voucher và Giảm (%) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    {{-- Mã voucher --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Mã voucher:</label>
                        <input type="text" name="ma_voucher"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700 placeholder-gray-400"
                            value="{{ old('ma_voucher', $voucher->ma_voucher) }}" placeholder="Nhập mã voucher">
                        @error('ma_voucher')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Giảm (%) --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Giảm (%):</label>
                        <input type="number" name="gia_tri"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700 placeholder-gray-400"
                            value="{{ old('gia_tri', $voucher->gia_tri) }}" min="1" max="100"
                            placeholder="Giá trị giảm (1-100)">
                        @error('gia_tri')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Dòng 2: Ngày bắt đầu và Ngày kết thúc --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    {{-- Ngày bắt đầu --}}
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Ngày bắt đầu:</label>
                        <input type="date" name="ngay_bat_dau"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-green-500 focus:border-green-500"
                            value="{{ old('ngay_bat_dau', $voucher->ngay_bat_dau) }}">
                        @error('ngay_bat_dau')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Ngày kết thúc --}}
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Ngày kết thúc:</label>
                        <input type="date" name="ngay_ket_thuc"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-green-500 focus:border-green-500"
                            value="{{ old('ngay_ket_thuc', $voucher->ngay_ket_thuc) }}">
                        @error('ngay_ket_thuc')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Dòng 3: Số lượng và Loại phòng áp dụng --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    {{-- Số lượng --}}
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Số lượng:</label>
                        <input type="number" name="so_luong"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-green-500 focus:border-green-500"
                            value="{{ old('so_luong', $voucher->so_luong) }}" min="1"
                            placeholder="Số lượng voucher phát hành">
                        @error('so_luong')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Loại phòng áp dụng --}}
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Loại phòng áp dụng:</label>
                        <select name="loai_phong_id"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Tất cả loại phòng</option>
                            @foreach ($loaiPhongs as $lp)
                                <option value="{{ $lp->id }}"
                                    {{ old('loai_phong_id', $voucher->loai_phong_id) == $lp->id ? 'selected' : '' }}>
                                    {{ $lp->ten_loai }}
                                </option>
                            @endforeach
                        </select>
                        @error('loai_phong_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Dòng 4: Điều kiện áp dụng (Full width) --}}
                <div class="mb-4">
                    <label class="block mb-1 font-medium text-gray-700">Điều kiện áp dụng:</label>
                    <textarea name="dieu_kien" rows="3"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Ví dụ: Giảm 20% cho đơn hàng từ 500.000 VNĐ">{{ old('dieu_kien', $voucher->dieu_kien) }}</textarea>
                    @error('dieu_kien')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Trạng thái (Ở cuối trước nút) --}}
                <div class="mb-6">
                    <label class="block mb-1 font-medium text-gray-700">Trạng thái:</label>
                    <select name="trang_thai"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-green-500 focus:border-green-500">
                        <option value="con_han"
                            {{ old('trang_thai', $voucher->trang_thai) == 'con_han' ? 'selected' : '' }}>
                            Còn hạn</option>
                        <option value="het_han"
                            {{ old('trang_thai', $voucher->trang_thai) == 'het_han' ? 'selected' : '' }}>
                            Hết hạn</option>
                        <option value="huy" {{ old('trang_thai', $voucher->trang_thai) == 'huy' ? 'selected' : '' }}>Hủy
                        </option>
                    </select>
                    @error('trang_thai')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                    <button type="button" onclick="window.location='{{ route('admin.voucher.index') }}'"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md shadow-sm bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Hủy
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
