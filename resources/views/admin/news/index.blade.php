@extends('layouts.admin')

@section('title', 'Quản lý Tin tức')

@section('admin_content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Quản lý Tin tức</h1>
            <p class="text-gray-600">Quản lý các bài viết tin tức của khách sạn</p>
        </div>
        <a href="{{ route('admin.news.create') }}" 
           class="inline-flex items-center px-4 py-2 rounded-md bg-green-600 hover:bg-green-700 text-white shadow-sm transition transform hover:scale-105">
            <i class="fas fa-plus mr-2"></i>
            Thêm tin tức mới
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white shadow rounded-lg p-4">
        <form method="GET" action="{{ route('admin.news.index') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Tìm kiếm theo tiêu đề, tóm tắt..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="md:w-48">
                <select name="status" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Tất cả trạng thái</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Đã xuất bản</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Bản nháp</option>
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Lưu trữ</option>
                </select>
            </div>
            <button type="submit" 
                    class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                <i class="fas fa-search mr-2"></i>Tìm kiếm
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('admin.news.index') }}" 
                   class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition">
                    <i class="fas fa-times mr-2"></i>Xóa bộ lọc
                </a>
            @endif
        </form>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- News Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ảnh</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiêu đề</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tác giả</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lượt xem</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày tạo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($news as $item)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $item->id }}</td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($item->hinh_anh)
                                <img src="{{ asset($item->hinh_anh) }}" 
                                     alt="{{ $item->tieu_de }}" 
                                     class="w-16 h-16 rounded-lg object-cover shadow-sm border border-gray-200">
                            @else
                                <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center border border-gray-200">
                                    <i class="fas fa-image text-gray-400 text-xl"></i>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <div class="text-sm font-semibold text-gray-900 mb-1">{{ Str::limit($item->tieu_de, 50) }}</div>
                            <div class="text-xs text-gray-500 line-clamp-2">{{ Str::limit($item->tom_tat, 80) }}</div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($item->trang_thai == 'published')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>Đã xuất bản
                                </span>
                            @elseif($item->trang_thai == 'draft')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-edit mr-1"></i>Bản nháp
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-archive mr-1"></i>Lưu trữ
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="flex items-center">
                                <i class="fas fa-user-circle mr-2 text-gray-400"></i>
                                {{ $item->admin->ho_ten ?? 'N/A' }}
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="flex items-center">
                                <i class="fas fa-eye mr-2 text-blue-400"></i>
                                {{ number_format($item->luot_xem) }}
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-alt mr-2 text-gray-400"></i>
                                {{ $item->created_at->format('d/m/Y H:i') }}
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.news.show', $item->id) }}" 
                                   class="inline-flex items-center px-3 py-1.5 rounded-md text-white bg-blue-500 hover:bg-blue-600 transition-colors shadow-sm" 
                                   title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.news.edit', $item->id) }}" 
                                   class="inline-flex items-center px-3 py-1.5 rounded-md text-white bg-yellow-500 hover:bg-yellow-600 transition-colors shadow-sm" 
                                   title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.news.destroy', $item->id) }}" 
                                      method="POST" 
                                      class="inline"
                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa tin tức này? Hành động này không thể hoàn tác!')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center px-3 py-1.5 rounded-md text-white bg-red-500 hover:bg-red-600 transition-colors shadow-sm" 
                                            title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-newspaper text-gray-300 text-5xl mb-4"></i>
                                <p class="text-sm font-medium text-gray-500">Không tìm thấy tin tức nào</p>
                                @if(request('search') || request('status'))
                                    <p class="text-xs text-gray-400 mt-1">Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($news->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Hiển thị 
                        <span class="font-medium">{{ $news->firstItem() }}</span>
                        đến 
                        <span class="font-medium">{{ $news->lastItem() }}</span>
                        trong tổng số 
                        <span class="font-medium">{{ $news->total() }}</span>
                        kết quả
                    </div>
                    <div class="flex items-center space-x-2">
                        {{ $news->links('pagination.custom') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection