@extends('layouts.admin')

@section('title', 'Chỉnh sửa phòng')

@section('admin_content')
<div class="bg-white rounded-2xl shadow p-6 mt-8 mb-8 w-full">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-2xl font-semibold text-amber-600 flex items-center gap-2">
            <i class="bi bi-pencil-square"></i> Chỉnh sửa phòng
        </h2>
        <a href="{{ route('admin.phong.index') }}"
           class="px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    {{-- Form --}}
    <form action="{{ route('admin.phong.update', $phong->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Hàng 1: Tên phòng & Giá --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-gray-700 font-medium mb-1">Tên phòng</label>
                <input type="text" name="ten_phong" value="{{ old('ten_phong', $phong->ten_phong) }}"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500" required>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Giá (₫)</label>
                <input type="number" name="gia" value="{{ old('gia', $phong->gia) }}" maxlength="9"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500" required>
            </div>
        </div>

        {{-- Hàng 2: Loại phòng & Trạng thái --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-gray-700 font-medium mb-1">Loại phòng</label>
                <select name="loai_phong_id"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500" required>
                    @foreach ($loaiPhongs as $loai)
                        <option value="{{ $loai->id }}" {{ old('loai_phong_id', $phong->loai_phong_id) == $loai->id ? 'selected' : '' }}>
                            {{ $loai->ten_loai }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Trạng thái</label>
                <select name="trang_thai"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500" required>
                    <option value="hien" {{ old('trang_thai', $phong->trang_thai) == 'hien' ? 'selected' : '' }}>Hiện</option>
                    <option value="an" {{ old('trang_thai', $phong->trang_thai) == 'an' ? 'selected' : '' }}>Ẩn</option>
                    <option value="bao_tri" {{ old('trang_thai', $phong->trang_thai) == 'bao_tri' ? 'selected' : '' }}>Bảo trì</option>
                </select>
            </div>
        </div>

        {{-- Hàng 3: Mô tả & Ảnh --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-gray-700 font-medium mb-1">Mô tả</label>
                <textarea name="mo_ta" rows="4"
                          class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500">{{ old('mo_ta', $phong->mo_ta) }}</textarea>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Ảnh phòng</label>
                <input type="file" name="img" id="img"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500">
                @if ($phong->img)
                    <div class="mt-3">
                        <p class="text-gray-600 text-sm mb-1">Ảnh hiện tại:</p>
                        <img src="{{ asset($phong->img) }}" class="w-32 h-32 object-cover rounded-lg shadow mb-2">
                    </div>
                @endif
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
                    class="px-5 py-2.5 rounded-lg bg-amber-600 text-white font-medium hover:bg-amber-700 transition">
                <i class="bi bi-save2 me-1"></i> Cập nhật
            </button>
        </div>
    </form>
</div>

{{-- JS Preview ảnh --}}
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const imgInput = document.getElementById('img');
    const preview = document.getElementById('preview');
    
    if (imgInput && preview) {
        imgInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    preview.src = event.target.result;
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                preview.classList.add('hidden');
            }
        });
    }
});
</script>
@endpush
