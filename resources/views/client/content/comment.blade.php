@php
use App\Models\Comment;
use App\Models\DatPhong; // thêm để kiểm tra đặt phòng

$existing = null;
$hasBooking = false;

if (auth()->check()) {
    $user = auth()->user();

    // ✅ Kiểm tra xem user đã có đơn đặt phòng của loại phòng này chưa
    $hasBooking = DatPhong::where('nguoi_dung_id', $user->id)
        ->where('loai_phong_id', $room->id)
        ->whereIn('trang_thai', ['da_xac_nhan', 'da_tra']) // trạng thái đã hoàn tất
        ->exists();

    // ✅ Kiểm tra user đã đánh giá chưa
    $existing = Comment::where('loai_phong_id', $room->id)
        ->where('nguoi_dung_id', $user->id)
        ->first();
}

$averageRating = Comment::where('loai_phong_id', $room->id)
    ->where('trang_thai', 'hien_thi')
    ->avg('so_sao');

$totalReviews = Comment::where('loai_phong_id', $room->id)
    ->where('trang_thai', 'hien_thi')
    ->count();

$countByStars = Comment::selectRaw('so_sao, COUNT(*) as total')
    ->where('loai_phong_id', $room->id)
    ->where('trang_thai', 'hien_thi')
    ->groupBy('so_sao')
    ->pluck('total', 'so_sao');

$filterStar = request()->query('star');
@endphp

{{-- Thông báo sẽ được hiển thị trong form --}}


{{-- 🔴 THÔNG BÁO KHI CHƯA ĐẶT PHÒNG --}}
@if(auth()->check() && !$hasBooking)
<div class="bg-yellow-50 border border-yellow-200 p-6 rounded-xl shadow-md mb-8">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium text-yellow-800">
                Bạn cần đặt phòng trước khi đánh giá
            </h3>
            <div class="mt-2 text-sm text-yellow-700">
                <p>Để đảm bảo tính chính xác của đánh giá, chỉ những khách hàng đã đặt phòng thành công mới có thể gửi đánh giá.</p>
            </div>
        </div>
    </div>
</div>
@endif

{{-- 🟢 FORM GỬI ĐÁNH GIÁ (chỉ hiển thị khi đã đặt phòng và chưa đánh giá) --}}
<form id="newReviewForm" action="{{ route('client.comment.store') }}" method="POST" enctype="multipart/form-data"
      class="bg-white p-6 rounded-xl shadow-md mb-8" style="display: {{ (auth()->check() && $hasBooking && (!$existing || session('success') || session('error'))) ? 'block' : 'none' }}">
    @csrf
    <input type="hidden" name="loai_phong_id" value="{{ $room->id }}" id="reviewFormRoomId">

    {{-- THÔNG BÁO TRONG FORM --}}
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

    {{-- Chỉ hiển thị form input khi chưa có thông báo thành công --}}
    @if (!session('success'))
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
    @endif
</form>


{{-- ⭐ PHÒNG + ĐIỂM TRUNG BÌNH + LỌC SAO --}}
<div id="existingReviewsSection" class="bg-white rounded-xl shadow-md p-6 mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4" style="display: {{ $totalReviews > 0 ? 'flex' : 'none' }}">
    {{-- Trái: Tên + sao trung bình --}}
    <div class="flex items-center gap-4">
        <div>
            <h3 class="text-2xl font-bold text-gray-800" id="reviewFormRoomName">{{ $room->ten_loai ?? 'Loại phòng' }}</h3>
            <p class="text-gray-600" id="reviewSummaryText">
                ⭐ {{ number_format($averageRating, 1) }} / 5 ({{ $totalReviews }} đánh giá)
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

    {{-- Phải: Bộ lọc sao --}}
    <div class="flex flex-wrap gap-2 justify-start md:justify-end">
        @php
            $filters = [
                'Tất cả' => null,
                '5 Sao' => 5,
                '4 Sao' => 4,
                '3 Sao' => 3,
                '2 Sao' => 2,
                '1 Sao' => 1,
            ];
        @endphp
        @foreach ($filters as $label => $star)
            @php
                $isActive = $filterStar == $star || ($filterStar === null && $star === null);
                $count = $star ? ($countByStars[$star] ?? 0) : $totalReviews;
            @endphp
            <a href="{{ request()->fullUrlWithQuery(['star' => $star]) }}"
               class="px-4 py-2 text-sm font-medium rounded-lg border transition
                      {{ $isActive ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100' }}">
                {{ $label }} ({{ number_format($count) }})
            </a>
        @endforeach
    </div>
</div>


{{-- 🔹 DANH SÁCH ĐÁNH GIÁ --}}
<h3 class="text-2xl font-bold text-gray-800 mb-4">Đánh giá gần đây</h3>

@php
$comments = Comment::where('loai_phong_id', $room->id)
    ->where('trang_thai', 'hien_thi')
    ->when($filterStar && in_array($filterStar, [1,2,3,4,5]), function($q) use ($filterStar) {
        $q->where('so_sao', $filterStar);
    })
    ->latest('ngay_danh_gia')
    ->get();
@endphp

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

                {{-- Cập nhật sao --}}
                <div x-data="{ rating: {{ $comment->so_sao }}, hover: 0 }">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cập nhật số sao:</label>
                    <div class="flex space-x-1 text-2xl text-gray-300">
                        @for ($i = 1; $i <= 5; $i++)
                            <button type="button"
                                @mouseover="hover = {{ $i }}"
                                @mouseleave="hover = 0"
                                @click="rating = {{ $i }}"
                                class="focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                    viewBox="0 0 24 24"
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

        @if($comment->img)
            <img src="{{ asset('storage/' . $comment->img) }}"
                 alt="Ảnh đánh giá"
                 class="w-32 h-32 object-cover rounded-lg mt-2 border border-gray-200 shadow-sm">
        @endif
        <p class="text-gray-400 text-xs mt-1">
            {{ \Carbon\Carbon::parse($comment->ngay_danh_gia)->format('H:i d/m/Y') }}
        </p>

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

@if($comment->reply)
<div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-400 mb-3 ml-8">
    <div class="flex items-start">
        <div class="flex-shrink-0 mr-3">
            <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h.01a1 1 0 100-2H10V9z" clip-rule="evenodd" />
            </svg>
        </div>
        <div>
            <div class="font-medium text-blue-800 text-sm">Phản hồi từ quản trị viên</div>
            <p class="text-gray-700 text-sm mt-1">{{ $comment->reply }}</p>
            @if($comment->reply_at)
                <div class="text-xs text-gray-500 mt-1">
                    {{ is_string($comment->reply_at) ? \Carbon\Carbon::parse($comment->reply_at)->format('d/m/Y H:i') : $comment->reply_at->format('d/m/Y H:i') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endif

@empty
<p class="text-gray-500 italic">Chưa có đánh giá nào.</p>
@endforelse
</div>

{{-- ALPINE.JS --}}
<script src="//unpkg.com/alpinejs" defer></script>
