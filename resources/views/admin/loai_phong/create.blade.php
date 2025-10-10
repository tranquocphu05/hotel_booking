@extends('layouts.admin')

@section('title', 'Thêm loại phòng')

@section('admin_content')
<div class="bg-white rounded-2xl shadow p-6 mt-8 mb-8 w-full">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-2xl font-semibold text-green-600 flex items-center gap-2">
            <i class="bi bi-plus-circle text-dark-600"></i>
            Thêm loại phòng
        </h2>
        <a href="{{ route('admin.loai_phong.index') }}"
           class="px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
            <i class="bi bi-arrow-left"></i> Go back
        </a>
    </div>

    {{-- Form --}}
    <form action="{{ route('admin.loai_phong.store') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Hàng 1: Tên loại & Giá cơ bản --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="ten_loai" class="block text-gray-700 font-medium mb-1">Tên loại phòng</label>
                <input type="text" name="ten_loai" id="ten_loai" value="{{ old('ten_loai') }}"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                @error('ten_loai')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="gia_co_ban" class="block text-gray-700 font-medium mb-1">Giá cơ bản (₫)</label>
                <input type="number" name="gia_co_ban" id="gia_co_ban" value="{{ old('gia_co_ban') }}"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                @error('gia_co_ban')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Hàng 2: Mô tả & Trạng thái --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="mo_ta" class="block text-gray-700 font-medium mb-1">Mô tả</label>
                <textarea name="mo_ta" id="mo_ta" rows="4"
                          class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('mo_ta') }}</textarea>
            </div>

            <div>
                <label for="trang_thai" class="block text-gray-700 font-medium mb-1">Trạng thái</label>
                <select name="trang_thai" id="trang_thai"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="hoat_dong" {{ old('trang_thai') == 'hoat_dong' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="ngung" {{ old('trang_thai') == 'ngung' ? 'selected' : '' }}>Ngừng</option>
                </select>
            </div>
        </div>

        {{-- Nút hành động --}}
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('admin.loai_phong.index') }}"
               class="px-5 py-2.5 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition">
                Cancel
            </a>
            <button type="submit"
                    class="px-5 py-2.5 rounded-lg bg-green-600 text-white font-medium hover:bg-green-700 transition">
                <i class="bi bi-save2 me-1"></i> Add
            </button>
        </div>
    </form>
</div>
@endsection
