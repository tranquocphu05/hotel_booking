@extends('layouts.admin')

@section('title', 'Thêm loại phòng')

@section('admin_content')
<div class="bg-white rounded-2xl shadow p-6 mt-8 mb-8 w-full">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-2xl font-semibold text-green-600 flex items-center gap-2">
            <i class="bi bi-plus-circle"></i>
            Thêm loại phòng
        </h2>
        <a href="{{ route('admin.loai_phong.index') }}"
           class="px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    {{-- Form --}}
    <form action="{{ route('admin.loai_phong.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" novalidate>
        @csrf

        {{-- Hàng 1: Tên loại & Giá cơ bản --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="ten_loai" class="block text-gray-700 font-medium mb-2 text-sm">Tên loại phòng</label>
                <input type="text" name="ten_loai" id="ten_loai" value="{{ old('ten_loai') }}"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm
                              focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all
                              hover:border-gray-300 bg-white text-gray-700 placeholder-gray-400"
                       placeholder="Nhập tên loại phòng" required>
                @error('ten_loai')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="gia_co_ban" class="block text-gray-700 font-medium mb-2 text-sm">Giá cơ bản (₫)</label>
                <input type="number" name="gia_co_ban" id="gia_co_ban" value="{{ old('gia_co_ban') }}"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm
                              focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all
                              hover:border-gray-300 bg-white text-gray-700 placeholder-gray-400"
                       placeholder="Nhập giá cơ bản" required>
                @error('gia_co_ban')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Hàng 1.5: Giá khuyến mãi --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="gia_khuyen_mai" class="block text-gray-700 font-medium mb-2 text-sm">Giá khuyến mãi (₫) <span class="text-gray-500 text-xs">(Tùy chọn)</span></label>
                <input type="number" name="gia_khuyen_mai" id="gia_khuyen_mai" value="{{ old('gia_khuyen_mai') }}"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm
                              focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all
                              hover:border-gray-300 bg-white text-gray-700 placeholder-gray-400"
                       placeholder="Nhập giá khuyến mãi (để trống nếu không có)">
                <p class="text-xs text-gray-500 mt-1">Nếu có giá khuyến mãi, hệ thống sẽ ưu tiên sử dụng giá này thay vì giá cơ bản</p>
                @error('gia_khuyen_mai')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="so_luong_phong" class="block text-gray-700 font-medium mb-2 text-sm">Số lượng phòng</label>
                <input type="number" name="so_luong_phong" id="so_luong_phong" value="{{ old('so_luong_phong', 0) }}"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm
                              focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all
                              hover:border-gray-300 bg-white text-gray-700 placeholder-gray-400"
                       placeholder="Tổng số phòng" min="0" required>
                <p class="text-xs text-gray-500 mt-1">Số lượng phòng trống sẽ tự động bằng số lượng phòng khi tạo mới</p>
                @error('so_luong_phong')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Hàng 2: Mô tả & Trạng thái --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="mo_ta" class="block text-gray-700 font-medium mb-2 text-sm">Mô tả</label>
                <textarea name="mo_ta" id="mo_ta" rows="4"
                          class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm
                                 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all
                                 hover:border-gray-300 bg-white text-gray-700 placeholder-gray-400 resize-none"
                          placeholder="Nhập mô tả...">{{ old('mo_ta') }}</textarea>
                @error('mo_ta')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="trang_thai" class="block text-gray-700 font-medium mb-2 text-sm">Trạng thái</label>
                <select name="trang_thai" id="trang_thai"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm
                               focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all
                               hover:border-gray-300 bg-white text-gray-700">
                    <option value="hoat_dong" {{ old('trang_thai') == 'hoat_dong' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="ngung" {{ old('trang_thai') == 'ngung' ? 'selected' : '' }}>Ngừng</option>
                </select>
                @error('trang_thai')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Hàng 3: Ảnh loại phòng --}}
<div>
            <label for="anh" class="block text-gray-700 font-medium mb-2 text-sm">Ảnh loại phòng *</label>
            <div class="space-y-4">
                {{-- Preview ảnh --}}
                <div id="imagePreview" class="hidden">
                    <label class="block text-gray-700 font-medium mb-2 text-sm">Ảnh xem trước:</label>
                    <div class="relative inline-block">
                        <img id="previewImg" src="" alt="Preview" 
                             class="max-w-full h-64 object-cover rounded-lg shadow border border-gray-200">
                        <button type="button" onclick="removePreview()" 
                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 hover:bg-red-600 transition">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                {{-- Upload area --}}
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 hover:border-green-500 transition-colors">
                    <label for="anh" class="cursor-pointer flex flex-col items-center">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                        <span class="text-gray-700 font-medium mb-1">Click để chọn ảnh hoặc kéo thả ảnh vào đây</span>
                        <span class="text-sm text-gray-500">JPG, PNG, JPEG (Tối đa 2MB)</span>
                    </label>
                    <input type="file" name="anh" id="anh" accept="image/*"
                           class="hidden"
                           onchange="previewImage(this)">
                </div>
                <div id="fileName" class="text-sm text-gray-600 hidden"></div>
    @error('anh')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
            </div>
</div>


        {{-- Nút hành động --}}
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('admin.loai_phong.index') }}"
               class="px-5 py-2.5 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition">
                <i class="bi bi-x-circle"></i> Hủy
            </a>
            <button type="submit"
                    class="px-5 py-2.5 rounded-lg bg-green-600 text-white font-medium hover:bg-green-700 transition">
                <i class="bi bi-save2 me-1"></i> Lưu
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
  // Initialize TinyMCE for description field
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof tinymce !== 'undefined') {
      tinymce.init({
        selector: 'textarea[name="mo_ta"]',
        height: 300,
        menubar: false,
        plugins: 'lists link image table code',
        toolbar: 'undo redo | bold italic underline | bullist numlist | link image | table | code',
        branding: false,
        content_style: 'body { font-family:Inter, sans-serif; font-size:14px }',
        setup: function (editor) {
          editor.on('change', function () {
            editor.save();
          });
        }
      });
    }

    // Drag and drop for image upload
    const dropZone = document.querySelector('.border-dashed');
    const fileInput = document.getElementById('anh');

    dropZone.addEventListener('dragover', function(e) {
      e.preventDefault();
      dropZone.classList.add('border-green-500', 'bg-green-50');
    });

    dropZone.addEventListener('dragleave', function(e) {
      e.preventDefault();
      dropZone.classList.remove('border-green-500', 'bg-green-50');
    });

    dropZone.addEventListener('drop', function(e) {
      e.preventDefault();
      dropZone.classList.remove('border-green-500', 'bg-green-50');
      const files = e.dataTransfer.files;
      if (files.length > 0 && files[0].type.startsWith('image/')) {
        fileInput.files = files;
        previewImage(fileInput);
      }
    });
  });

  function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const fileName = document.getElementById('fileName');

    if (input.files && input.files[0]) {
      const reader = new FileReader();
      
      reader.onload = function(e) {
        previewImg.src = e.target.result;
        preview.classList.remove('hidden');
        fileName.textContent = 'File: ' + input.files[0].name;
        fileName.classList.remove('hidden');
      };

      reader.readAsDataURL(input.files[0]);
    }
  }

  function removePreview() {
    const preview = document.getElementById('imagePreview');
    const fileInput = document.getElementById('anh');
    const fileName = document.getElementById('fileName');
    
    preview.classList.add('hidden');
    fileInput.value = '';
    fileName.classList.add('hidden');
  }
</script>
@endpush
@endsection
