@extends('layouts.admin')

@section('title', 'Thêm phòng mới')

@section('admin_content')
<div class="bg-white rounded-2xl shadow p-6 mt-12 mb-10 w-full max-w-4xl mx-auto">
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
    <form action="{{ route('admin.phong.store') }}" method="POST" enctype="multipart/form-data" class="space-y-10">
        @csrf

        {{-- Hàng 1: Tên phòng & Giá --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <label for="ten_phong" class="block text-gray-800 font-medium mb-2 text-sm">Tên phòng <span class="text-red-500">*</span></label>
                <input type="text" name="ten_phong" id="ten_phong" value="{{ old('ten_phong') }}"
                       placeholder="Ví dụ: Phòng Deluxe 101"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500" required>
                @error('ten_phong')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="gia" class="block text-gray-800 font-medium mb-2 text-sm">Giá (₫) <span class="text-red-500">*</span></label>
                <input type="number" name="gia" id="gia" value="{{ old('gia') }}" maxlength="9"
                       placeholder="Ví dụ: 1200000"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500" required>
                @error('gia')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Hàng 2: Loại phòng & Trạng thái --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <label for="loai_phong_id" class="block text-gray-800 font-medium mb-2 text-sm">Loại phòng <span class="text-red-500">*</span></label>
                <select name="loai_phong_id" id="loai_phong_id"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700" required>
                    <option value="">-- Chọn loại phòng --</option>
                    @foreach ($loaiPhongs as $loai)
                        <option value="{{ $loai->id }}" {{ old('loai_phong_id') == $loai->id ? 'selected' : '' }}>
                            {{ $loai->ten_loai }}
                        </option>
                    @endforeach
                </select>
                @error('loai_phong_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="trang_thai" class="block text-gray-800 font-medium mb-2 text-sm">Trạng thái <span class="text-red-500">*</span></label>
                <select name="trang_thai" id="trang_thai"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700" required>
                    <option value="hien" {{ old('trang_thai') == 'hien' ? 'selected' : '' }}>Hiện</option>
                    <option value="an" {{ old('trang_thai') == 'an' ? 'selected' : '' }}>Ẩn</option>
                    <option value="bao_tri" {{ old('trang_thai') == 'bao_tri' ? 'selected' : '' }}>Bảo trì</option>
                    <option value="chong" {{ old('trang_thai') == 'chong' ? 'selected' : '' }}>Chống (không cho đặt)</option>
                </select>
                @error('trang_thai')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Hàng 3: Mô tả & Ảnh + Xem trước --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div>
                <label for="mo_ta" class="block text-gray-800 font-medium mb-2">Mô tả</label>
                <textarea name="mo_ta" id="mo_ta" rows="8" placeholder="Mô tả ngắn gọn về phòng, tiện nghi..."
                          class="w-full border-gray-300 rounded-lg shadow-sm">{{ old('mo_ta') }}</textarea>
                @error('mo_ta')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="xl:col-span-1">
                <label for="img" class="block text-gray-800 font-medium mb-2">Ảnh phòng</label>
                <input type="file" name="img" id="img"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                <div class="mt-3 flex items-center gap-3">
                    <img id="preview" class="hidden w-[140px] h-[100px] object-cover rounded-lg border border-gray-300 shadow-sm">
                    <span id="fileName" class="text-xs text-gray-500"></span>
                </div>
                @error('img')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Card xem trước --}}
            <div class="xl:col-span-1">
                <div class="border rounded-xl p-4 bg-gray-50">
                    <h4 class="font-semibold text-gray-800 mb-3">Xem trước</h4>
                    <div class="flex items-start gap-3">
                        <img id="previewSmall" class="w-[96px] h-[72px] object-cover rounded-lg border border-gray-200 hidden">
                        <div class="text-sm text-gray-700 space-y-1">
                            <p><span class="text-gray-500">Tên phòng:</span> <span id="pvTenPhong" class="font-medium">—</span></p>
                            <p><span class="text-gray-500">Giá:</span> <span id="pvGia" class="font-medium">—</span></p>
                            <p><span class="text-gray-500">Loại:</span> <span id="pvLoai" class="font-medium">—</span></p>
                            <p><span class="text-gray-500">Trạng thái:</span> <span id="pvTrangThai" class="font-medium">—</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Nút hành động --}}
        <div class="sticky bottom-0 bg-white pt-4 border-t border-gray-100 flex justify-end gap-3">
            <a href="{{ route('admin.phong.index') }}"
               class="px-5 py-2.5 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition">Hủy</a>
            <button type="submit"
                    class="px-5 py-2.5 rounded-lg bg-green-600 text-white font-medium hover:bg-green-700 transition">
                <i class="bi bi-save2 me-1"></i> Thêm
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.2/tinymce.min.js"></script>
<script>
  tinymce.init({
    selector: 'textarea[name="mo_ta"]',
    height: 400,
    menubar: false,
    plugins: 'lists link image table code',
    toolbar: 'undo redo | bold italic underline | bullist numlist | link image | table | code',
    branding: false,
    content_style: 'body { font-family:Inter, sans-serif; font-size:14px }'
  });

  document.getElementById('img').addEventListener('change', function(e) {
      const file = e.target.files[0];
      const preview = document.getElementById('preview');
      const previewSmall = document.getElementById('previewSmall');
      const fileName = document.getElementById('fileName');
      if (file) {
          const reader = new FileReader();
          reader.onload = event => {
              preview.src = event.target.result;
              preview.classList.remove('hidden');
              previewSmall.src = event.target.result;
              previewSmall.classList.remove('hidden');
          };
          reader.readAsDataURL(file);
          fileName.textContent = file.name;
      } else {
          preview.classList.add('hidden');
          previewSmall.classList.add('hidden');
          fileName.textContent = '';
      }
  });

  // Live preview cho các trường
  const tenPhongEl = document.getElementById('ten_phong');
  const giaEl = document.getElementById('gia');
  const loaiEl = document.getElementById('loai_phong_id');
  const trangThaiEl = document.getElementById('trang_thai');
  const pvTenPhong = document.getElementById('pvTenPhong');
  const pvGia = document.getElementById('pvGia');
  const pvLoai = document.getElementById('pvLoai');
  const pvTrangThai = document.getElementById('pvTrangThai');

  function formatVnd(num){
      if(!num) return '—';
      return new Intl.NumberFormat('vi-VN').format(num) + ' ₫';
  }

  tenPhongEl?.addEventListener('input',()=> pvTenPhong.textContent = tenPhongEl.value || '—');
  giaEl?.addEventListener('input',()=> pvGia.textContent = formatVnd(giaEl.value));
  loaiEl?.addEventListener('change',()=> pvLoai.textContent = loaiEl.options[loaiEl.selectedIndex]?.text || '—');
  trangThaiEl?.addEventListener('change',()=> pvTrangThai.textContent = trangThaiEl.options[trangThaiEl.selectedIndex]?.text || '—');

  // Init default preview
  pvLoai.textContent = loaiEl?.options[loaiEl.selectedIndex]?.text || '—';
  pvTrangThai.textContent = trangThaiEl?.options[trangThaiEl.selectedIndex]?.text || '—';
</script>
@endpush
