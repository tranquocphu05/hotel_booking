@extends('layouts.admin')

@section('title', 'Quản lý Tin tức')

@section('admin_content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Quản lý Tin tức</h1>
            <p class="text-gray-600">Quản lý các bài viết tin tức của khách sạn</p>
        </div>
        <a href="{{ route('admin.news.create') }}" 
           class="btn-primary btn-animate inline-flex items-center px-4 py-2 rounded-md">
            <i class="fas fa-plus mr-2"></i>
            Thêm tin tức mới
        </a>
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
    <div class="bg-white shadow rounded-lg">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ảnh</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tiêu đề</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tác giả</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lượt xem</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ngày tạo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($news as $item)
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ $item->id }}</td>
                    <td class="px-4 py-4">
                        @if($item->hinh_anh)
                            <img src="{{ asset($item->hinh_anh) }}" 
                                 alt="{{ $item->tieu_de }}" 
                                 class="w-16 h-16 rounded object-cover">
                        @else
                            <div class="w-16 h-16 rounded bg-gray-100 flex items-center justify-center">
                                <i class="fas fa-image text-gray-400"></i>
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ Str::limit($item->tieu_de, 50) }}</div>
                        <div class="text-sm text-gray-500">{{ Str::limit($item->tom_tat, 80) }}</div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        @if($item->trang_thai == 'published')
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Đã xuất bản</span>
                        @elseif($item->trang_thai == 'draft')
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Bản nháp</span>
                        @else
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Lưu trữ</span>
                        @endif
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-900">{{ $item->admin->ho_ten ?? 'N/A' }}</td>
                    <td class="px-4 py-4 text-sm text-gray-900">{{ number_format($item->luot_xem) }}</td>
                    <td class="px-4 py-4 text-sm text-gray-500">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-4">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.news.show', $item->id) }}" 
                               class="inline-flex items-center px-3 py-1 rounded text-white bg-blue-500 hover:bg-blue-600" title="Xem">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.news.edit', $item->id) }}" 
                               class="inline-flex items-center px-3 py-1 rounded text-white bg-yellow-500 hover:bg-yellow-600" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.news.destroy', $item->id) }}" 
                                  method="POST" 
                                  class="inline"
                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa tin tức này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-1 rounded text-white bg-red-500 hover:bg-red-600" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-4 text-center text-sm text-gray-500">Không có tin tức nào</td>
                </tr>
                @endforelse
            </tbody>
        </table>

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