@extends('layouts.admin')

@section('title', 'Chỉnh sửa phòng')

@section('admin_content')
<div class="bg-white rounded-2xl shadow p-6 mt-12 mb-10 w-full max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-2xl font-semibold text-amber-600 flex items-center gap-2">
            <i class="fas fa-edit"></i> Chỉnh sửa phòng: {{ $phong->so_phong }}
        </h2>
        <a href="{{ route('admin.phong.index') }}"
           class="px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    @if (session('error'))
        <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-800 text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
            <h4 class="text-red-800 font-semibold mb-2">Có lỗi xảy ra:</h4>
            <ul class="list-disc list-inside text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form --}}
    <form action="{{ route('admin.phong.update', $phong->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Hàng 1: Loại phòng & Số phòng --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <label for="loai_phong_id" class="block text-gray-800 font-medium mb-2">Loại phòng <span class="text-red-500">*</span></label>
                <select name="loai_phong_id" id="loai_phong_id"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    @foreach ($loaiPhongs as $loai)
                        <option value="{{ $loai->id }}" {{ old('loai_phong_id', $phong->loai_phong_id) == $loai->id ? 'selected' : '' }}>
                            {{ $loai->ten_loai }}
                        </option>
                    @endforeach
                </select>
                @error('loai_phong_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="so_phong" class="block text-gray-800 font-medium mb-2">Số phòng <span class="text-red-500">*</span></label>
                <input type="text" name="so_phong" id="so_phong" value="{{ old('so_phong', $phong->so_phong) }}"
                       placeholder="Ví dụ: 101, 201, A01" maxlength="20"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                @error('so_phong')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Hàng 2: Tên phòng & Tầng --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <label for="ten_phong" class="block text-gray-800 font-medium mb-2">Tên phòng</label>
                <input type="text" name="ten_phong" id="ten_phong" value="{{ old('ten_phong', $phong->ten_phong) }}"
                       placeholder="Ví dụ: Phòng Deluxe 101" maxlength="255"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                @error('ten_phong')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="tang" class="block text-gray-800 font-medium mb-2">Tầng</label>
                <input type="number" name="tang" id="tang" value="{{ old('tang', $phong->tang) }}"
                       placeholder="Ví dụ: 1, 2, 3..." min="1" max="50"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                @error('tang')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Hàng 3: Hướng cửa sổ & Trạng thái --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <label for="huong_cua_so" class="block text-gray-800 font-medium mb-2">Hướng cửa sổ</label>
                <select name="huong_cua_so" id="huong_cua_so"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    <option value="">-- Chọn hướng --</option>
                    <option value="bien" {{ old('huong_cua_so', $phong->huong_cua_so) == 'bien' ? 'selected' : '' }}>Biển</option>
                    <option value="nui" {{ old('huong_cua_so', $phong->huong_cua_so) == 'nui' ? 'selected' : '' }}>Núi</option>
                    <option value="thanh_pho" {{ old('huong_cua_so', $phong->huong_cua_so) == 'thanh_pho' ? 'selected' : '' }}>Thành phố</option>
                    <option value="san_vuon" {{ old('huong_cua_so', $phong->huong_cua_so) == 'san_vuon' ? 'selected' : '' }}>Sân vườn</option>
                </select>
                @error('huong_cua_so')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="trang_thai" class="block text-gray-800 font-medium mb-2">Trạng thái <span class="text-red-500">*</span></label>
                <select name="trang_thai" id="trang_thai"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    <option value="trong" {{ old('trang_thai', $phong->trang_thai) == 'trong' ? 'selected' : '' }}>Trống</option>
                    <option value="dang_thue" {{ old('trang_thai', $phong->trang_thai) == 'dang_thue' ? 'selected' : '' }}>Đang thuê</option>
                    <option value="dang_don" {{ old('trang_thai', $phong->trang_thai) == 'dang_don' ? 'selected' : '' }}>Đang dọn</option>
                    <option value="bao_tri" {{ old('trang_thai', $phong->trang_thai) == 'bao_tri' ? 'selected' : '' }}>Bảo trì</option>
                </select>
                @error('trang_thai')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Hàng 4: Tiện ích --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <label class="block text-gray-800 font-medium mb-2">Tiện ích</label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="co_ban_cong" value="1" {{ old('co_ban_cong', $phong->co_ban_cong) ? 'checked' : '' }}
                               class="mr-2 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                        <span class="text-gray-700">Có ban công</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="co_view_dep" value="1" {{ old('co_view_dep', $phong->co_view_dep) ? 'checked' : '' }}
                               class="mr-2 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                        <span class="text-gray-700">Có view đẹp</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Hàng 5: Giá riêng & Giá bổ sung --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <label for="gia_rieng" class="block text-gray-800 font-medium mb-2">Giá riêng (VNĐ)</label>
                <input type="number" name="gia_rieng" id="gia_rieng" value="{{ old('gia_rieng', $phong->gia_rieng) }}"
                       placeholder="Ví dụ: 2000000" min="0" step="1000"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                <p class="mt-1 text-xs text-gray-500">Giá riêng của phòng (nếu khác với loại phòng)</p>
                @error('gia_rieng')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="gia_bo_sung" class="block text-gray-800 font-medium mb-2">Giá bổ sung (VNĐ)</label>
                <input type="number" name="gia_bo_sung" id="gia_bo_sung" value="{{ old('gia_bo_sung', $phong->gia_bo_sung) }}"
                       placeholder="Ví dụ: 200000" min="0" step="1000"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                <p class="mt-1 text-xs text-gray-500">Giá bổ sung (ví dụ: view đẹp +200k)</p>
                @error('gia_bo_sung')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Hàng 6: Ghi chú --}}
        <div>
            <label for="ghi_chu" class="block text-gray-800 font-medium mb-2">Ghi chú</label>
            <textarea name="ghi_chu" id="ghi_chu" rows="4"
                      placeholder="Ghi chú đặc biệt về phòng..."
                      class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">{{ old('ghi_chu', $phong->ghi_chu) }}</textarea>
            @error('ghi_chu')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        {{-- Nút hành động --}}
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('admin.phong.index') }}"
               class="px-5 py-2.5 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition">
                Hủy
            </a>
            <button type="submit"
                    class="px-5 py-2.5 rounded-lg bg-amber-600 text-white font-medium hover:bg-amber-700 transition">
                <i class="fas fa-save mr-1"></i> Cập nhật
            </button>
        </div>
    </form>
</div>
@endsection
