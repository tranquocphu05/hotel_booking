@extends('layouts.admin')

@section('title', 'Quản lý Đánh giá')

@section('admin_content')
<div class="bg-white rounded-2xl shadow p-6 mt-8 mb-8 w-full">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Danh sách đánh giá</h2>
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="flex gap-2 mb-4">
            <input type="text" name="keyword" placeholder="Tìm nội dung hoặc người dùng..."
                   value="{{ request('keyword') }}"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-64 focus:ring focus:ring-blue-200">

            <select name="rating" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Tất cả sao</option>
                @for ($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>
                        {{ $i }} sao
                    </option>
                @endfor
            </select>

            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Tất cả trạng thái</option>
                <option value="hien_thi" {{ request('status') === 'hien_thi' ? 'selected' : '' }}>Hiển thị</option>
                <option value="an" {{ request('status') === 'an' ? 'selected' : '' }}>Ẩn</option>
            </select>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Lọc
            </button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-600 border border-gray-200 rounded-lg">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">Người dùng</th>
                    <th class="px-4 py-3">Loại phòng</th>
                    <th class="px-4 py-3">Số sao</th>
                    <th class="px-4 py-3">Nội dung</th>
                    <th class="px-4 py-3">Trạng thái</th>
                    <th class="px-4 py-3">Ngày đánh giá</th>
                    <th class="px-4 py-3 text-center">Thao tác</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse($comments as $comment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $comment->id }}</td>

                        {{-- ✅ Hiển thị đầy đủ tên và email --}}
                        <td class="px-4 py-2">
                            @if ($comment->user)
                                <div>
                                    <span class="font-medium text-gray-800">
                                        {{ $comment->user->name ?? $comment->user->username ?? 'Không rõ tên' }}
                                    </span><br>
                                    <span class="text-gray-500 text-xs">
                                        {{ $comment->user->email ?? 'Không có email' }}
                                    </span>
                                </div>
                            @else
                                <span class="italic text-gray-400">Người dùng ẩn danh hoặc đã bị xóa</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            @if($comment->loaiPhong)
                                {{ $comment->loaiPhong->ten_loai }}
                            @else
                                <span class="text-red-500">Loại phòng không tồn tại (ID: {{ $comment->loai_phong_id }})</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-yellow-500">
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $comment->so_sao ? 'text-yellow-400' : 'text-gray-300' }}"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                        </td>

                        {{-- Nội dung --}}
                        <td class="px-4 py-2">{{ Str::limit($comment->noi_dung, 80) }}</td>

                        {{-- Trạng thái --}}
                        <td class="px-4 py-2">
                            @if ($comment->trang_thai === 'hien_thi')
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">Hiển thị</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-200 text-gray-700">Ẩn</span>
                            @endif
                        </td>

                        {{-- Ngày đánh giá --}}
                        <td class="px-4 py-2">
                            {{ $comment->ngay_danh_gia ? \Carbon\Carbon::parse($comment->ngay_danh_gia)->format('H:i d/m/Y') : '—' }}
                        </td>

                        {{-- Thao tác --}}
                        <td class="px-4 py-2 text-center space-x-2">
                            <a href="{{ route('admin.reviews.show', $comment->id) }}" 
                               class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                Xem
                            </a>

                            {{-- Chỉ admin và nhân viên mới được cập nhật trạng thái --}}
                            @if (in_array(auth()->user()->vai_tro ?? '', ['admin', 'nhan_vien']))
                                @if ($comment->trang_thai === 'hien_thi')
                                    <form action="{{ route('admin.reviews.toggle', $comment->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="an">
                                        <button type="submit" class="bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600">
                                            Ẩn
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.reviews.toggle', $comment->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="hien_thi">
                                        <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
                                            Hiển thị
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-gray-400">Không có đánh giá nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $comments->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endsection
