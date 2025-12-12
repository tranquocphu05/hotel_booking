@extends('layouts.admin')

@section('title', 'Chi tiết đánh giá')

@section('admin_content')
<div class="bg-white shadow rounded-xl p-6 mt-6">
    <h2 class="text-2xl font-semibold mb-4 text-gray-800">Chi tiết đánh giá</h2>

    {{-- THÔNG TIN CHI TIẾT ĐÁNH GIÁ --}}
    <div class="mb-6 space-y-2 text-gray-700">
        <p>
            <strong>Người dùng:</strong><br>
            @if ($comment->user)
                <span class="font-medium text-gray-900">
                    {{ $comment->user->name ?? $comment->user->username ?? 'Không rõ tên' }}
                </span><br>
                <span class="text-sm text-gray-500">
                    {{ $comment->user->email ?? 'Không có email' }}
                </span>
            @else
                <span class="italic text-gray-400">Người dùng ẩn danh hoặc đã bị xóa</span>
            @endif
        </p>

        <p>
            <strong>Số sao:</strong>
            <span class="text-yellow-500 text-lg">
                @for ($i = 1; $i <= 5; $i++)
                    {!! $i <= $comment->so_sao ? '★' : '☆' !!}
                @endfor
            </span>
        </p>

        <p><strong>Nội dung:</strong> {{ $comment->noi_dung }}</p>

        <p>
            <strong>Ngày đánh giá:</strong>
            {{ $comment->ngay_danh_gia 
                ? \Carbon\Carbon::parse($comment->ngay_danh_gia)->format('H:i d/m/Y') 
                : '—' }}
        </p>

        <p>
            <strong>Trạng thái:</strong>
            @if ($comment->trang_thai === 'hien_thi')
                <span class="text-green-600 font-semibold">Hiển thị</span>
            @else
                <span class="text-gray-500 font-semibold">Ẩn</span>
            @endif
        </p>
    </div>

    {{-- FORM PHẢN HỒI CỦA ADMIN --}}
    {{-- Chỉ admin và nhân viên mới được phản hồi --}}
    @if (in_array(auth()->user()->vai_tro ?? '', ['admin', 'nhan_vien']))
        <div class="mt-8 border-t pt-4">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Phản hồi của Admin</h3>

            <form action="{{ route('admin.reviews.reply', $comment->id) }}" method="POST" class="mb-4">
                @csrf
                @method('PUT')

                <textarea name="reply" rows="4" placeholder="Nhập phản hồi..." 
                    class="w-full border border-gray-300 rounded-lg p-3 focus:ring focus:ring-blue-300">{{ old('reply', $comment->reply) }}</textarea>

                <button type="submit" 
                    class="mt-3 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                    Gửi phản hồi
                </button>
            </form>

            {{-- FORM XÓA PHẢN HỒI --}}
            @if ($comment->reply)
                <form action="{{ route('admin.reviews.reply.delete', $comment->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa phản hồi này không?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">
                        Xóa phản hồi
                    </button>
                </form>
            @endif
        </div>
    @else
        {{-- Lễ tân chỉ xem phản hồi nếu có --}}
        @if ($comment->reply)
            <div class="mt-8 border-t pt-4">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Phản hồi của Admin</h3>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <p class="text-gray-700">{{ $comment->reply }}</p>
                    @if ($comment->reply_at)
                        <p class="text-sm text-gray-500 mt-2">
                            Phản hồi lúc: {{ \Carbon\Carbon::parse($comment->reply_at)->format('H:i d/m/Y') }}
                        </p>
                    @endif
                </div>
            </div>
        @endif
    @endif

    {{-- NÚT QUAY LẠI --}}
    <div class="mt-6">
        <a href="{{ route('admin.reviews.index') }}" 
           class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
            ← Quay lại danh sách
        </a>
    </div>
</div>
@endsection
