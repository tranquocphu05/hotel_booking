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
                                  rows="10" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('noi_dung') border-red-300 @enderror"
                                  placeholder="Nhập nội dung chi tiết của tin tức"
                                  required>{{ old('noi_dung', $news->noi_dung) }}</textarea>
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
                            <div class="relative">
                                <img src="{{ asset($news->hinh_anh) }}" 
                                     alt="{{ $news->tieu_de }}" 
                                     class="w-full h-48 object-cover rounded-lg border border-gray-200">
                                <div class="absolute top-2 right-2">
                                    <span class="bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">Hiện tại</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Image Upload -->
                    <div>
                        <label for="hinh_anh" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ $news->hinh_anh ? 'Thay đổi hình ảnh' : 'Hình ảnh' }}
                        </label>
                        <input type="file" 
                               id="hinh_anh" 
                               name="hinh_anh" 
                               accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('hinh_anh') border-red-300 @enderror">
                        @error('hinh_anh')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Định dạng: JPG, PNG, GIF. Kích thước tối đa: 2MB</p>
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
                        class="btn-primary btn-animate inline-flex items-center px-4 py-2 rounded-md">
                    <i class="fas fa-save mr-2"></i>
                    Cập nhật tin tức
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('hinh_anh');
    if (!input) return;
    input.addEventListener('change', function (e) {
        var file = e.target.files && e.target.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (ev) {
            var preview = document.getElementById('image-preview');
            if (!preview) {
                preview = document.createElement('img');
                preview.id = 'image-preview';
                preview.className = 'mt-2 rounded-lg object-cover border border-gray-200';
                preview.style.maxWidth = '200px';
                preview.style.maxHeight = '200px';
                input.parentNode.appendChild(preview);
            }
            preview.src = ev.target.result;
        };
        reader.readAsDataURL(file);
    });
});
</script>

@endpush
@endsection
