@extends('layouts.admin')

@section('title', 'Chi tiết Tin tức')

@section('admin_content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Chi tiết Tin tức</h1>
            <p class="text-gray-600">Xem thông tin chi tiết bài viết</p>
        </div>
        <a href="{{ route('admin.news.index') }}" 
           class="btn-secondary btn-animate inline-flex items-center px-4 py-2 rounded-md">
            <i class="fas fa-arrow-left mr-2"></i>
            Quay lại
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Article Header -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $news->tieu_de }}</h2>
                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                            <span class="inline-flex items-center">
                                <i class="fas fa-user mr-2 text-blue-500"></i>
                                {{ $news->admin->ho_ten ?? 'N/A' }}
                            </span>
                            <span class="inline-flex items-center">
                                <i class="fas fa-calendar mr-2 text-green-500"></i>
                                {{ $news->created_at->format('d/m/Y H:i') }}
                            </span>
                            <span class="inline-flex items-center">
                                <i class="fas fa-eye mr-2 text-purple-500"></i>
                                {{ number_format($news->luot_xem) }} lượt xem
                            </span>
                        </div>
                    </div>
                    <div class="ml-4">
                        @if($news->trang_thai == 'published')
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>
                                Đã xuất bản
                            </span>
                        @elseif($news->trang_thai == 'draft')
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-edit mr-1"></i>
                                Bản nháp
                            </span>
                        @else
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">
                                <i class="fas fa-archive mr-1"></i>
                                Lưu trữ
                            </span>
                        @endif
                    </div>
                </div>

                @if($news->hinh_anh)
                    <div class="mb-6">
                        <img src="{{ asset($news->hinh_anh) }}" 
                             alt="{{ $news->tieu_de }}" 
                             class="w-full h-64 object-cover rounded-lg shadow-md">
                    </div>
                @endif

                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Tóm tắt</h3>
                        <p class="text-gray-700 leading-relaxed">{{ $news->tom_tat }}</p>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Nội dung</h3>
                        <div class="prose max-w-none text-gray-700 leading-relaxed">
                            {!! nl2br(e($news->noi_dung)) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Article Info -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                    Thông tin bài viết
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">ID:</span>
                        <span class="text-sm font-medium text-gray-900">#{{ $news->id }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Slug:</span>
                        <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $news->slug }}</code>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Trạng thái:</span>
                        @if($news->trang_thai == 'published')
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Đã xuất bản</span>
                        @elseif($news->trang_thai == 'draft')
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Bản nháp</span>
                        @else
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Lưu trữ</span>
                        @endif
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Tác giả:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $news->admin->ho_ten ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Lượt xem:</span>
                        <span class="text-sm font-medium text-blue-600">{{ number_format($news->luot_xem) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Ngày tạo:</span>
                        <span class="text-sm text-gray-900">{{ $news->created_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-gray-600">Cập nhật:</span>
                        <span class="text-sm text-gray-900">{{ $news->updated_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-cogs mr-2 text-gray-500"></i>
                    Thao tác
                </h3>
                <div class="space-y-3">
                    <a href="{{ route('admin.news.edit', $news->id) }}" 
                       class="btn-warning btn-animate w-full inline-flex items-center justify-center px-4 py-2 rounded-md">
                        <i class="fas fa-edit mr-2"></i>
                        Chỉnh sửa
                    </a>
                    
                    @if($news->trang_thai == 'published')
                        <a href="{{ route('client.tintuc.show', $news->slug) }}" 
                           class="btn-info btn-animate w-full inline-flex items-center justify-center px-4 py-2 rounded-md"
                           target="_blank">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            Xem công khai
                        </a>
                    @endif
                    
                    <form action="{{ route('admin.news.destroy', $news->id) }}" 
                          method="POST" 
                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa tin tức này?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="btn-danger btn-animate w-full inline-flex items-center justify-center px-4 py-2 rounded-md">
                            <i class="fas fa-trash mr-2"></i>
                            Xóa tin tức
                        </button>
                    </form>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 border border-blue-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-chart-bar mr-2 text-blue-500"></i>
                    Thống kê nhanh
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ number_format($news->luot_xem) }}</div>
                        <div class="text-sm text-gray-600">Lượt xem</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $news->created_at->diffInDays(now()) }}</div>
                        <div class="text-sm text-gray-600">Ngày tuổi</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection