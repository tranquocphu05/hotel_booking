@extends('layouts.admin')

@section('title', 'Thêm phòng mới')

@section('admin_content')
<div class="bg-white rounded-2xl shadow p-6 mt-8 mb-8 w-full">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-2xl font-semibold text-green-600 flex items-center gap-2">
            <i class="bi bi-plus-circle"></i> Thêm phòng mới
        </h2>
        <a href="{{ route('admin.phong.index') }}"
           class="px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    {{-- Form --}}
    <form action="{{ route('admin.phong.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Hàng 1: Tên phòng & Giá --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="ten_phong" class="block text-gray-700 font-medium mb-1">Tên phòng</label>
                <input type="text" name="ten_phong" id="ten_phong" value="{{ old('ten_phong') }}"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500" required>
                @error('ten_phong')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="gia" class="block text-gray-700 font-medium mb-1">Giá (₫)</label>
                <input type="number" name="gia" id="gia" value="{{ old('gia') }}" maxlength="9"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500" required>
                @error('gia')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Hàng 2: Loại phòng & Trạng thái --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="loai_phong_id" class="block text-gray-700 font-medium mb-1">Loại phòng</label>
                <select name="loai_phong_id" id="loai_phong_id"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500" required>
                    <option value="">-- Chọn loại phòng --</option>
                    @foreach ($loaiPhongs as $loai) 
                        <option value="{{ $loai->id }}" {{ old('loai_phong_id') == $loai->id ? 'selected' : '' }}>
                            {{ $loai->ten_loai }}
                        </option>
                    @endforeach
                </select>
                @error('loai_phong_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="trang_thai" class="block text-gray-700 font-medium mb-1">Trạng thái</label>
                <select name="trang_thai" id="trang_thai"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500" required>
                    <option value="hien" {{ old('trang_thai') == 'hien' ? 'selected' : '' }}>Hiện</option>
                    <option value="an" {{ old('trang_thai') == 'an' ? 'selected' : '' }}>Ẩn</option>
                    <option value="bao_tri" {{ old('trang_thai') == 'bao_tri' ? 'selected' : '' }}>Bảo trì</option>
                </select>
            </div>
        </div>

        {{-- Hàng 3: Mô tả & Ảnh --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="mo_ta" class="block text-gray-700 font-medium mb-1">Mô tả</label>
                <textarea name="mo_ta" id="mo_ta" rows="4"
                          class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">{{ old('mo_ta') }}</textarea>
            </div>

            <div>
                <label for="img" class="block text-gray-700 font-medium mb-1">Ảnh phòng</label>
                <input type="file" name="img" id="img"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
<img id="preview" class="hidden w-[120px] h-[90px] object-cover rounded-lg border border-gray-300 shadow-sm mt-3">
            </div>
        </div>

        {{-- Nút hành động --}}
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('admin.phong.index') }}"
               class="px-5 py-2.5 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition">
                Hủy
            </a>
            <button type="submit"
                    class="px-5 py-2.5 rounded-lg bg-green-600 text-white font-medium hover:bg-green-700 transition">
                <i class="bi bi-save2 me-1"></i> Thêm
            </button>
        </div>
    </form>
</div>

{{-- JS Preview ảnh --}}
<script>
document.getElementById('img').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('preview');
    if (file) {
        const reader = new FileReader();
        reader.onload = event => {
            preview.src = event.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
    }
});
</script>
@endsection
