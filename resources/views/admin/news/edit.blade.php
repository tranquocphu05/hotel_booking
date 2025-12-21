@extends('layouts.admin')

@section('title', 'Chỉnh sửa Tin tức')

@section('admin_content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Chỉnh sửa Tin tức</h1>
            <p class="text-gray-600">Cập nhật thông tin bài viết tin tức</p>
        </div>
        <a href="{{ route('admin.news.index') }}" 
           class="btn-secondary btn-animate inline-flex items-center px-4 py-2 rounded-md">
            <i class="fas fa-arrow-left mr-2"></i>
            Quay lại
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white shadow rounded-lg">
        <form action="{{ route('admin.news.update', $news->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6 p-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <div>
                        <label for="tieu_de" class="block text-sm font-medium text-gray-700 mb-2">
                            Tiêu đề <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="tieu_de" 
                               name="tieu_de" 
                               value="{{ old('tieu_de', $news->tieu_de) }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('tieu_de') border-red-300 @enderror"
                               placeholder="Nhập tiêu đề tin tức"
                               required>
                        @error('tieu_de')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tom_tat" class="block text-sm font-medium text-gray-700 mb-2">
                            Tóm tắt <span class="text-red-500">*</span>
                        </label>
                        <textarea id="tom_tat" 
                                  name="tom_tat" 
                                  rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('tom_tat') border-red-300 @enderror"
                                  placeholder="Nhập tóm tắt ngắn gọn về tin tức"
                                  required>{{ old('tom_tat', $news->tom_tat) }}</textarea>
                        @error('tom_tat')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="noi_dung" class="block text-sm font-medium text-gray-700 mb-2">
                            Nội dung <span class="text-red-500">*</span>
                        </label>
                        <textarea id="noi_dung" 
                                  name="noi_dung" 
                                  rows="15" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('noi_dung') border-red-300 @enderror"
                                  placeholder="Nhập nội dung chi tiết của tin tức"
                                  required>{{ old('noi_dung', $news->noi_dung) }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Bạn có thể sử dụng Enter để xuống dòng. Nội dung sẽ được hiển thị với định dạng đã nhập.</p>
                        @error('noi_dung')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Current Image -->
                    @if($news->hinh_anh)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hình ảnh hiện tại</label>
                            <div class="relative group">
                                <img src="{{ asset($news->hinh_anh) }}" 
                                     id="current-image"
                                     alt="{{ $news->tieu_de }}" 
                                     class="w-full h-48 object-cover rounded-lg border-2 border-gray-200 shadow-sm">
                                <div class="absolute top-2 right-2">
                                    <span class="bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded-full flex items-center">
                                        <i class="fas fa-image mr-1"></i>Hiện tại
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Image Upload -->
                    <div>
                        <label for="hinh_anh" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ $news->hinh_anh ? 'Thay đổi hình ảnh' : 'Hình ảnh' }}
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-indigo-400 transition-colors">
                            <div class="space-y-1 text-center w-full">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="hinh_anh" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                        <span>Chọn hình ảnh mới</span>
                                        <input type="file" 
                                               id="hinh_anh" 
                                               name="hinh_anh" 
                                               accept="image/*"
                                               class="sr-only">
                                    </label>
                                    <p class="pl-1">hoặc kéo thả vào đây</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF tối đa 2MB</p>
                            </div>
                        </div>
                        <div id="image-preview-container" class="mt-4 hidden">
                            <img id="image-preview" src="" alt="Preview" class="w-full h-64 object-cover rounded-lg border-2 border-indigo-300 shadow-sm">
                            <button type="button" id="remove-image" class="mt-2 w-full px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors">
                                <i class="fas fa-times mr-2"></i>Hủy thay đổi
                            </button>
                        </div>
                        @error('hinh_anh')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="trang_thai" class="block text-sm font-medium text-gray-700 mb-2">
                            Trạng thái <span class="text-red-500">*</span>
                        </label>
                        <select id="trang_thai" 
                                name="trang_thai" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('trang_thai') border-red-300 @enderror"
                                required>
                            <option value="">Chọn trạng thái</option>
                            <option value="draft" {{ old('trang_thai', $news->trang_thai) == 'draft' ? 'selected' : '' }}>Bản nháp</option>
                            <option value="published" {{ old('trang_thai', $news->trang_thai) == 'published' ? 'selected' : '' }}>Xuất bản</option>
                            <option value="archived" {{ old('trang_thai', $news->trang_thai) == 'archived' ? 'selected' : '' }}>Lưu trữ</option>
                        </select>
                        @error('trang_thai')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Info Card -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
                        <h3 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                            Thông tin bài viết
                        </h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Slug:</span>
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $news->slug }}</code>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Lượt xem:</span>
                                <span class="font-medium text-blue-600">{{ number_format($news->luot_xem) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Ngày tạo:</span>
                                <span class="text-gray-900">{{ $news->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Cập nhật:</span>
                                <span class="text-gray-900">{{ $news->updated_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.news.index') }}" 
                   class="btn-secondary btn-animate inline-flex items-center px-4 py-2 rounded-md">
                    Hủy
                </a>
                <button type="submit" 
                        id="submit-btn"
                        class="btn-primary btn-animate inline-flex items-center px-4 py-2 rounded-md">
                    <i class="fas fa-save mr-2"></i>
                    <span id="submit-text">Cập nhật tin tức</span>
                    <span id="submit-spinner" class="hidden ml-2">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Loading Overlay - Full Screen -->
<div id="loading-overlay" class="hidden fixed inset-0 bg-black bg-opacity-70 z-[9999]" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100%; height: 100%;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; max-width: 400px; padding: 0 20px;">
        <div class="bg-white rounded-xl p-10 flex flex-col items-center justify-center shadow-2xl" style="width: 100%;">
            <div class="relative mb-6 flex items-center justify-center" style="width: 80px; height: 80px; margin: 0 auto;">
                <div class="animate-spin rounded-full border-4 border-gray-200 border-t-indigo-600" style="width: 80px; height: 80px; position: absolute; top: 0; left: 0;"></div>
                <div class="absolute inset-0 flex items-center justify-center" style="width: 80px; height: 80px;">
                    <i class="fas fa-edit text-indigo-600 text-2xl"></i>
                </div>
            </div>
            <p class="text-gray-800 font-semibold text-lg mb-2 text-center w-full">Đang cập nhật...</p>
            <p class="text-sm text-gray-500 text-center w-full">Vui lòng đợi trong giây lát</p>
            <div class="mt-4 flex space-x-1 justify-center items-center">
                <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 0s;"></div>
                <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
                <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 0.4s;"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('hinh_anh');
    const previewContainer = document.getElementById('image-preview-container');
    const preview = document.getElementById('image-preview');
    const removeBtn = document.getElementById('remove-image');
    const currentImage = document.getElementById('current-image');
    const dropZone = fileInput ? fileInput.closest('.border-dashed') : null;
    const form = document.querySelector('form');
    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitSpinner = document.getElementById('submit-spinner');

    // Loading spinner khi submit form
    if (form && submitBtn) {
        const loadingOverlay = document.getElementById('loading-overlay');
        
        form.addEventListener('submit', function(e) {
            // Hiển thị spinner trên button
            submitBtn.disabled = true;
            submitText.classList.add('hidden');
            submitSpinner.classList.remove('hidden');
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            
            // Hiển thị overlay loading ngay lập tức
            if (loadingOverlay) {
                loadingOverlay.style.display = 'block';
                loadingOverlay.style.position = 'fixed';
                loadingOverlay.style.top = '0';
                loadingOverlay.style.left = '0';
                loadingOverlay.style.right = '0';
                loadingOverlay.style.bottom = '0';
                loadingOverlay.style.width = '100%';
                loadingOverlay.style.height = '100%';
                loadingOverlay.classList.remove('hidden');
            }
        });
    }
    
    // Hiển thị loading khi trang đang load
    window.addEventListener('beforeunload', function() {
        const loadingOverlay = document.getElementById('loading-overlay');
        if (loadingOverlay) {
            loadingOverlay.classList.remove('hidden');
        }
    });
    
    // Hiển thị loading khi click vào các link
    document.querySelectorAll('a[href]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            // Chỉ hiển thị loading cho các link không phải external hoặc anchor
            if (this.href && !this.href.startsWith('#') && !this.href.startsWith('javascript:')) {
                const loadingOverlay = document.getElementById('loading-overlay');
                if (loadingOverlay) {
                    loadingOverlay.classList.remove('hidden');
                }
            }
        });
    });

    if (!fileInput || !previewContainer || !preview) return;

    // Preview khi chọn file
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('Kích thước file không được vượt quá 2MB');
                fileInput.value = '';
                return;
            }

            // Validate file type
            if (!file.type.match('image.*')) {
                alert('Vui lòng chọn file hình ảnh');
                fileInput.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                previewContainer.classList.remove('hidden');
                if (dropZone) dropZone.classList.add('hidden');
                if (currentImage) currentImage.classList.add('opacity-50');
            };
            reader.readAsDataURL(file);
        }
    });

    // Xóa hình ảnh preview
    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            fileInput.value = '';
            preview.src = '';
            previewContainer.classList.add('hidden');
            if (dropZone) dropZone.classList.remove('hidden');
            if (currentImage) currentImage.classList.remove('opacity-50');
        });
    }

    // Drag and drop
    if (dropZone) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.classList.add('border-indigo-500', 'bg-indigo-50');
        }

        function unhighlight(e) {
            dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                fileInput.files = files;
                fileInput.dispatchEvent(new Event('change'));
            }
        }
    }
});
</script>
@endpush
@endsection
