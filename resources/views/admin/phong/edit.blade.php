@extends('layouts.admin')

@section('title', 'Chỉnh sửa phòng')

@section('admin_content')
<div class="bg-white rounded-2xl shadow p-6 mt-12 mb-10 w-full max-w-4xl mx-auto">
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
    <form action="{{ route('admin.phong.update', $phong->id) }}" method="POST" enctype="multipart/form-data" class="space-y-10">
        @csrf
        @method('PUT')
        @if (session('error'))
            <div class="p-4 rounded-lg bg-red-100 text-red-700 text-sm">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="p-4 rounded-lg bg-red-50 text-red-700 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Hàng 1: Tên phòng & Giá gốc --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-gray-700 font-medium mb-2 text-sm">Tên phòng</label>
                <input type="text" name="ten_phong" value="{{ old('ten_phong', $phong->ten_phong) }}" maxlength="255"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700 placeholder-gray-400" required>
                @error('ten_phong')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2 text-sm">Giá gốc (₫)</label>
                <input type="number" name="gia_goc" value="{{ old('gia_goc', $phong->gia_goc) }}" maxlength="9"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700 placeholder-gray-400" required>
                @error('gia_goc')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Hàng 2: Giá khuyến mãi & Có khuyến mãi --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-gray-700 font-medium mb-2 text-sm">Giá khuyến mãi (₫)</label>
                <input type="number" name="gia_khuyen_mai" value="{{ old('gia_khuyen_mai', $phong->gia_khuyen_mai) }}" maxlength="9"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700 placeholder-gray-400">
                @error('gia_khuyen_mai')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2 text-sm">Có khuyến mãi</label>
                <div class="flex items-center space-x-4">
                    <label class="flex items-center">
                        <input type="radio" name="co_khuyen_mai" value="1" {{ old('co_khuyen_mai', $phong->co_khuyen_mai) == '1' ? 'checked' : '' }}
                               class="mr-2 text-amber-600 focus:ring-amber-500">
                        <span class="text-sm text-gray-700">Có</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="co_khuyen_mai" value="0" {{ old('co_khuyen_mai', $phong->co_khuyen_mai) == '0' ? 'checked' : '' }}
                               class="mr-2 text-amber-600 focus:ring-amber-500">
                        <span class="text-sm text-gray-700">Không</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Hàng 2: Loại phòng & Trạng thái --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-gray-700 font-medium mb-2 text-sm">Loại phòng</label>
                <select name="loai_phong_id"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700" required>
                    @foreach ($loaiPhongs as $loai)
                        <option value="{{ $loai->id }}" {{ old('loai_phong_id', $phong->loai_phong_id) == $loai->id ? 'selected' : '' }}>
                            {{ $loai->ten_loai }}
                        </option>
                    @endforeach
                </select>
                @error('loai_phong_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2 text-sm">Trạng thái</label>
                <select name="trang_thai"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700" required>
                    <option value="hien" {{ old('trang_thai', $phong->trang_thai) == 'hien' ? 'selected' : '' }}>Hiện</option>
                    <option value="an" {{ old('trang_thai', $phong->trang_thai) == 'an' ? 'selected' : '' }}>Ẩn</option>
                    <option value="bao_tri" {{ old('trang_thai', $phong->trang_thai) == 'bao_tri' ? 'selected' : '' }}>Bảo trì</option>
                </select>
                @error('trang_thai')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Hàng 3: Mô tả & Ảnh --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="mo_ta" class="block text-gray-800 font-medium mb-2">Mô tả</label>
                <textarea name="mo_ta" id="mo_ta" rows="8" class="w-full border-gray-300 rounded-lg shadow-sm">{{ old('mo_ta', $phong->mo_ta) }}</textarea>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2 text-sm">Ảnh phòng</label>
                <div class="relative">
                    <input type="file" name="img" id="img" accept="image/*"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                </div>
                @error('img')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                @if ($phong->img)
                    <div class="mt-3">
                        <p class="text-gray-600 text-sm mb-1">Ảnh hiện tại:</p>
                        <img src="{{ asset($phong->img) }}" class="w-32 h-32 object-cover rounded-lg shadow mb-2">
                    </div>
                @endif
                <img id="preview" class="hidden w-[120px] h-[90px] object-cover rounded-lg border border-gray-300 shadow-sm mt-3">
            </div>
        </div>

        {{-- Dịch vụ phòng --}}
        <div>
            <label for="dich_vu" class="block text-gray-800 font-medium mb-2">Dịch vụ phòng (phân tách bằng dấu phẩy)</label>
            <input type="text" name="dich_vu" id="dich_vu" value="{{ old('dich_vu', $phong->dich_vu) }}"
                   class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500">
            @error('dich_vu')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
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

@push('scripts')
<script>
  // (Removed CKEditor init to avoid conflicts; keep TinyMCE below)

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

  // Initialize TinyMCE
  tinymce.init({
    selector: '#mo_ta',
    height: 300,
    menubar: false,
    plugins: [
      'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
      'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
      'insertdatetime', 'media', 'table', 'help', 'wordcount'
    ],
    toolbar: 'undo redo | blocks | ' +
      'bold italic forecolor | alignleft aligncenter ' +
      'alignright alignjustify | bullist numlist outdent indent | ' +
      'removeformat | help',
    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }',
    language: 'vi',
    branding: false,
    promotion: false
  });
</script>
@endpush
