@php
use App\Models\Comment;
use App\Models\DatPhong; // thêm để kiểm tra đặt phòng

$existing = null;
$hasBooking = false;

if (auth()->check()) {
    $user = auth()->user();

    // ✅ Kiểm tra xem user đã có đơn đặt phòng của loại phòng này chưa
    $hasBookingQuery = DatPhong::where('nguoi_dung_id', $user->id)
        ->whereIn('trang_thai', ['da_tra']); // trạng thái đã hoàn tất

    // Prefer checking via pivot `roomTypes` (booking_room_types). Fall back to legacy column only if it exists.
    $hasBookingQuery->where(function($q) use ($room) {
        $q->whereHas('roomTypes', function($qq) use ($room) {
            // Qualify to pivot column to avoid ambiguous `id` when joined
            $qq->where('booking_room_types.loai_phong_id', $room->id);
        });

        if (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'loai_phong_id')) {
            $q->orWhere('loai_phong_id', $room->id);
        }
    });

    $hasBooking = $hasBookingQuery->exists();

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
{{-- @if(auth()->check() && !$hasBooking)
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
@endif --}}

{{-- 🟢 FORM GỬI ĐÁNH GIÁ (chỉ hiển thị khi đã đặt phòng và chưa đánh giá) --}}
<div id="reviews" class="mt-8">
    <!-- Reviews content goes here -->
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
</div>


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
<div class="mb-8">
    <h3 class="text-3xl font-bold text-gray-900 mb-6 flex items-center gap-3">
        <span class="w-1 h-8 bg-gradient-to-b from-yellow-400 to-orange-500 rounded-full"></span>
        Đánh giá gần đây
    </h3>

    @php
    $comments = Comment::where('loai_phong_id', $room->id)
        ->where('trang_thai', 'hien_thi')
        ->when($filterStar && in_array($filterStar, [1,2,3,4,5]), function($q) use ($filterStar) {
            $q->where('so_sao', $filterStar);
        })
        ->latest('ngay_danh_gia')
        ->get();
    @endphp

    <div class="space-y-4">
        @forelse ($comments as $comment)
        <div x-data="{ editing: false }"
             class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden group">
            
            <div class="p-6">
                {{-- Header: Avatar + Name + Rating + Time --}}
                <div class="flex items-start gap-4 mb-4">
                    {{-- Avatar --}}
                    <div class="flex-shrink-0">
                        @if($comment->user && $comment->user->img)
                            <img src="{{ asset($comment->user->img) }}" 
                                 alt="{{ $comment->user->username ?? 'User' }}"
                                 class="w-14 h-14 rounded-full object-cover border-2 border-yellow-200 shadow-md">
                        @else
                            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-yellow-400 via-orange-400 to-pink-400 flex items-center justify-center shadow-md border-2 border-yellow-200">
                                <span class="text-white font-bold text-lg">
                                    {{ strtoupper(substr($comment->user->username ?? $comment->user->ho_ten ?? 'U', 0, 1)) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-4 mb-2">
                            <div>
                                <h4 class="font-bold text-gray-900 text-lg mb-1">
                                    {{ $comment->user->username ?? $comment->user->ho_ten ?? 'Khách ẩn danh' }}
                                </h4>
                                <div class="flex items-center gap-2 text-sm text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>{{ \Carbon\Carbon::parse($comment->ngay_danh_gia)->format('H:i d/m/Y') }}</span>
                                </div>
                            </div>

                            {{-- Rating Stars --}}
                            <div class="flex items-center gap-1 flex-shrink-0" x-show="!editing">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                         viewBox="0 0 24 24"
                                         class="w-6 h-6 {{ $i <= $comment->so_sao ? 'text-yellow-400' : 'text-gray-200' }} transition-all duration-200">
                                        <path d="M12 .587l3.668 7.431 8.2 1.193-5.934 5.782
                                                 1.4 8.173L12 18.896l-7.334 3.87
                                                 1.4-8.173L.132 9.211l8.2-1.193z"/>
                                    </svg>
                                @endfor
                            </div>
                        </div>

                        {{-- Comment Content --}}
                        <template x-if="!editing">
                            <div>
                                <p class="text-gray-700 leading-relaxed mb-3">{{ $comment->noi_dung }}</p>
                                
                                {{-- Image --}}
                                @if($comment->img)
                                    <div class="mb-3">
                                        <img src="{{ asset('storage/' . $comment->img) }}"
                                             alt="Ảnh đánh giá"
                                             class="max-w-xs rounded-xl border border-gray-200 shadow-md hover:shadow-lg transition-shadow duration-300 cursor-pointer"
                                             onclick="window.open(this.src, '_blank')">
                                    </div>
                                @endif

                                {{-- Action Buttons --}}
                                @if(auth()->check() && auth()->id() === $comment->nguoi_dung_id)
                                <div class="flex items-center gap-4 pt-3 border-t border-gray-100">
                                    <button type="button"
                                            @click="editing = !editing"
                                            class="flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium text-sm transition-colors duration-200 hover:bg-blue-50 px-3 py-1.5 rounded-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        <span x-text="editing ? 'Đang sửa...' : 'Chỉnh sửa'"></span>
                                    </button>
                                    <form action="{{ route('client.comment.destroy', $comment->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Bạn có chắc muốn xóa đánh giá này không?')"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="flex items-center gap-2 text-red-600 hover:text-red-700 font-medium text-sm transition-colors duration-200 hover:bg-red-50 px-3 py-1.5 rounded-lg">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Xóa
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </div>
                        </template>

                        {{-- Edit Form --}}
                        <template x-if="editing">
                            <form action="{{ route('client.comment.update', $comment->id) }}"
                                  method="POST" enctype="multipart/form-data" 
                                  class="space-y-4 bg-gray-50 p-4 rounded-xl border border-gray-200">
                                @csrf
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nội dung đánh giá</label>
                                    <textarea name="noi_dung" rows="4"
                                              class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                              required>{{ $comment->noi_dung }}</textarea>
                                </div>

                                {{-- Cập nhật sao --}}
                                <div x-data="{ rating: {{ $comment->so_sao }}, hover: 0 }">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Đánh giá sao</label>
                                    <div class="flex space-x-1 text-3xl">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <button type="button"
                                                @mouseover="hover = {{ $i }}"
                                                @mouseleave="hover = 0"
                                                @click="rating = {{ $i }}"
                                                class="focus:outline-none transform transition-transform duration-150 hover:scale-110">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                                    viewBox="0 0 24 24"
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

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Cập nhật ảnh (tùy chọn)</label>
                                    <input type="file" name="img" accept="image/*"
                                           class="w-full text-sm border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    @if($comment->img)
                                        <div class="mt-2">
                                            <p class="text-xs text-gray-600 mb-1">Ảnh hiện tại:</p>
                                            <img src="{{ asset('storage/' . $comment->img) }}"
                                                 alt="Ảnh cũ" 
                                                 class="w-24 h-24 rounded-lg border-2 border-gray-200 object-cover">
                                        </div>
                                    @endif
                                </div>

                                <div class="flex gap-3 pt-2">
                                    <button type="submit"
                                            class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Lưu thay đổi
                                    </button>
                                    <button type="button"
                                            @click="editing = false"
                                            class="flex items-center gap-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-5 py-2.5 rounded-lg transition-all duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Hủy
                                    </button>
                                </div>
                            </form>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Admin Reply --}}
            @if($comment->reply)
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-t border-blue-200 p-5">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center shadow-md">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="font-bold text-blue-800 text-sm">Phản hồi từ quản trị viên</span>
                            @if($comment->reply_at)
                                <span class="text-xs text-blue-600">
                                    {{ is_string($comment->reply_at) ? \Carbon\Carbon::parse($comment->reply_at)->format('d/m/Y H:i') : $comment->reply_at->format('d/m/Y H:i') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-gray-800 leading-relaxed">{{ $comment->reply }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        @empty
        <div class="bg-white rounded-2xl shadow-lg p-12 text-center border border-gray-100">
            <div class="max-w-md mx-auto">
                <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                </svg>
                <h4 class="text-xl font-semibold text-gray-700 mb-2">Chưa có đánh giá nào</h4>
                <p class="text-gray-500">Hãy là người đầu tiên đánh giá về loại phòng này!</p>
            </div>
        </div>
        @endforelse
    </div>
</div>
</div>

{{-- ALPINE.JS --}}
<script src="//unpkg.com/alpinejs" defer></script>
