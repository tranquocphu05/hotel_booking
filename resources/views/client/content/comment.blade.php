@php
use App\Models\Comment;

$existing = null;
if (auth()->check()) {
    $existing = Comment::where('phong_id', $room->id)
        ->where('nguoi_dung_id', auth()->id())
        ->first();
}

$averageRating = Comment::where('phong_id', $room->id)
    ->where('trang_thai', 'hien_thi')
    ->avg('so_sao');
$totalReviews = Comment::where('phong_id', $room->id)
    ->where('trang_thai', 'hien_thi')
    ->count();
@endphp

{{-- THÔNG BÁO --}}
@if (session('success'))
    <div class="bg-green-100 text-green-800 p-3 mb-4 rounded-lg text-center shadow">
        {{ session('success') }}
    </div>
@endif
@if (session('error'))
    <div class="bg-red-100 text-red-800 p-3 mb-4 rounded-lg text-center shadow">
        {{ session('error') }}
    </div>
@endif

{{-- ĐÁNH GIÁ TRUNG BÌNH --}}
@if ($totalReviews > 0)
<div class="bg-white rounded-xl shadow-md p-6 mb-8 flex items-center justify-between">
    <div>
        <h3 class="text-2xl font-bold text-gray-800">{{ $room->ten_phong }}</h3>
        <p class="text-gray-600">
            ⭐ {{ number_format($averageRating, 1) }} / 5 · {{ $totalReviews }} đánh giá
        </p>
    </div>
    <div class="flex items-center space-x-1">
        @for ($i = 1; $i <= 5; $i++)
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                 viewBox="0 0 24 24"
                 class="w-6 h-6 {{ $i <= round($averageRating) ? 'text-yellow-400' : 'text-gray-300' }}">
                <path d="M12 .587l3.668 7.431 8.2 1.193-5.934 5.782
                    1.4 8.173L12 18.896l-7.334 3.87
                    1.4-8.173L.132 9.211l8.2-1.193z"/>
            </svg>
        @endfor
    </div>
</div>
@endif

{{-- FORM GỬI ĐÁNH GIÁ: chỉ hiển thị nếu user CHƯA đánh giá --}}
@if (auth()->check() && !$existing)
<form action="{{ route('client.comment.store') }}" method="POST" enctype="multipart/form-data"
      class="bg-white p-6 rounded-xl shadow-md mb-12">
    @csrf
    <input type="hidden" name="phong_id" value="{{ $room->id }}">

    {{-- Đánh giá sao --}}
    <div class="mb-6" x-data="{ rating: 0, hover: 0 }">
        <label class="block text-gray-700 font-semibold mb-2">Đánh giá (1–5 sao)</label>
        <div class="flex space-x-1 text-3xl text-gray-300">
            @for ($i = 1; $i <= 5; $i++)
                <button type="button"
                        @mouseover="hover = {{ $i }}"
                        @mouseleave="hover = 0"
                        @click="rating = {{ $i }}"
                        class="focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         fill="currentColor"
                         :class="{
                             'text-yellow-400': {{ $i }} <= (hover || rating),
                             'text-gray-300': {{ $i }} > (hover || rating)
                         }"
                         class="w-8 h-8 transition-colors duration-150">
                        <path d="M12 .587l3.668 7.431 8.2 1.193-5.934 5.782
                                 1.4 8.173L12 18.896l-7.334 3.87
                                 1.4-8.173L.132 9.211l8.2-1.193z"/>
                    </svg>
                </button>
            @endfor
        </div>
        <input type="hidden" name="so_sao" x-model="rating" required>
    </div>

    {{-- Nội dung --}}
    <div class="mb-6">
        <label class="block text-gray-700 font-semibold mb-2">Nội dung đánh giá</label>
        <textarea name="noi_dung" rows="4"
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-200 focus:outline-none"
            placeholder="Nhập nhận xét của bạn về phòng..." required></textarea>
    </div>

    {{-- Upload ảnh --}}
    <div class="mb-6">
        <label class="block text-gray-700 font-semibold mb-2">Ảnh minh họa (tùy chọn)</label>
        <input type="file" name="img" accept="image/png, image/jpeg, image/jpg, image/webp"
               class="block w-full border border-gray-300 rounded-lg p-2">
        <p class="text-xs text-gray-500 mt-1">Chỉ chấp nhận: JPG, JPEG, PNG, WEBP (tối đa 4MB)</p>
    </div>

    <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-lg shadow transition">
        Gửi đánh giá
    </button>
</form>
@endif

{{-- DANH SÁCH ĐÁNH GIÁ GẦN ĐÂY --}}
<h3 class="text-2xl font-bold text-gray-800 mb-4">Đánh giá gần đây</h3>

@forelse ($comments as $comment)
<div x-data="{ editing: false }"
     class="bg-gray-50 p-4 rounded-lg shadow mb-3 flex justify-between items-start">

    <div class="flex-1">
        <p class="font-semibold text-gray-800 text-lg">
            {{ $comment->user->name ?? $comment->user->username ?? 'Khách ẩn danh' }}
        </p>

        {{-- Nếu không chỉnh sửa --}}
        <template x-if="!editing">
            <p class="text-gray-600 text-sm mt-1">{{ $comment->noi_dung }}</p>
        </template>

        {{-- Khi đang chỉnh sửa --}}
        <template x-if="editing">
            <form action="{{ route('client.comment.update', $comment->id) }}"
                  method="POST" enctype="multipart/form-data" class="mt-2 space-y-3">
                @csrf
                <textarea name="noi_dung" rows="3"
                          class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-200"
                          required>{{ $comment->noi_dung }}</textarea>

            {{-- Cập nhật sao bằng biểu tượng --}}
            <div x-data="{ rating: {{ $comment->so_sao }}, hover: 0 }">
                <label class="block text-sm font-medium text-gray-700 mb-1">Cập nhật số sao:</label>
                <div class="flex space-x-1 text-2xl text-gray-300">
                    @for ($i = 1; $i <= 5; $i++)
                        <button type="button"
                                @mouseover="hover = {{ $i }}"
                                @mouseleave="hover = 0"
                                @click="rating = {{ $i }}"
                                class="focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                fill="currentColor"
                                :class="{
                                    'text-yellow-400': {{ $i }} <= (hover || rating),
                                    'text-gray-300': {{ $i }} > (hover || rating)
                                }"
                                class="w-7 h-7 transition-colors duration-150">
                                <path d="M12 .587l3.668 7.431 8.2 1.193-5.934 5.782
                                        1.4 8.173L12 18.896l-7.334 3.87
                                        1.4-8.173L.132 9.211l8.2-1.193z"/>
                            </svg>
                        </button>
                    @endfor
                </div>
                <input type="hidden" name="so_sao" x-model="rating" required>
            </div>


                <input type="file" name="img" accept="image/*"
                       class="w-full text-sm border border-gray-200 rounded-lg p-2">
                @if($comment->img)
                    <img src="{{ asset('storage/' . $comment->img) }}" 
                         alt="Ảnh cũ" class="w-20 h-20 mt-2 rounded border">
                @endif

                <div class="flex gap-2 mt-3">
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-1 rounded">
                        💾 Lưu
                    </button>
                    <button type="button"
                            @click="editing = false"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-medium px-3 py-1 rounded">
                        Hủy
                    </button>
                </div>
            </form>
        </template>

        {{-- Ảnh & ngày --}}
        <template x-if="!editing">
            <div>
                @if($comment->img)
                    <img src="{{ asset('storage/' . $comment->img) }}" 
                         alt="Ảnh đánh giá" 
                         class="w-32 h-32 object-cover rounded-lg mt-2 border border-gray-200 shadow-sm">
                @endif
                <p class="text-gray-400 text-xs mt-1">
                    {{ \Carbon\Carbon::parse($comment->ngay_danh_gia)->format('H:i d/m/Y') }}
                </p>
            </div>
        </template>

        {{-- Nút sửa/xóa --}}
        @if(auth()->check() && auth()->id() === $comment->nguoi_dung_id)
        <div class="flex gap-3 mt-3">
            <button type="button"
                    @click="editing = !editing"
                    class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                ✏️ <span x-text="editing ? 'Đang sửa...' : 'Chỉnh sửa'"></span>
            </button>

            <form action="{{ route('client.comment.destroy', $comment->id) }}" 
                  method="POST" 
                  onsubmit="return confirm('Bạn có chắc muốn xóa đánh giá này không?')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="text-red-600 hover:text-red-800 font-medium text-sm">
                    🗑️ Xóa
                </button>
            </form>
        </div>
        @endif
    </div>

    {{-- Sao --}}
    <div class="flex items-center space-x-1" x-show="!editing">
        @for ($i = 1; $i <= 5; $i++)
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                 viewBox="0 0 24 24"
                 class="w-5 h-5 {{ $i <= $comment->so_sao ? 'text-yellow-400' : 'text-gray-300' }}">
                <path d="M12 .587l3.668 7.431 8.2 1.193-5.934 5.782
                         1.4 8.173L12 18.896l-7.334 3.87
                         1.4-8.173L.132 9.211l8.2-1.193z"/>
            </svg>
        @endfor
    </div>
</div>
@empty
<p class="text-gray-500 italic">Chưa có đánh giá nào.</p>
@endforelse

{{-- ALPINE.JS --}}
<script src="//unpkg.com/alpinejs" defer></script>
