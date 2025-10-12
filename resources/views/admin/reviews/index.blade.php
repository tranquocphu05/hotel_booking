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
                            {{ $i }} sao</option>
                    @endfor
                </select>

                <select name="is_active" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Tất cả trạng thái</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Hiển thị</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Ẩn</option>
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
                        <th class="px-4 py-3">Tên khách</th>
                        <th class="px-4 py-3">Đánh giá</th>
                        <th class="px-4 py-3">Nội dung</th>
                        <th class="px-4 py-3">Trạng thái</th>
                        <th class="px-4 py-3">Ngày tạo</th>
                        <th class="px-4 py-3 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($comments as $comment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2">{{ $comment->id }}</td>
                            <td class="px-4 py-2">{{ $comment->user->username ?? 'Ẩn danh' }}</td>
                            <td class="px-4 py-2 text-yellow-500">
                                @for ($i = 1; $i <= 5; $i++)
                                    {!! $i <= $comment->rating ? '★' : '☆' !!}
                                @endfor
                            </td>
                            <td class="px-4 py-2">{{ Str::limit($comment->content, 80) }}</td>
                            <td class="px-4 py-2">
                                @if ($comment->is_active)
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">Hiển thị</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-200 text-gray-700">Ẩn</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">{{ $comment->created_at->format('H:i d/m/Y') }}</td>
                            <td class="px-4 py-2 text-center space-x-2">
                                <button onclick="showDetail({{ $comment->id }})"
                                    class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Xem</button>

                                <button onclick="showReply({{ $comment->id }})"
                                    class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Phản hồi</button>

                                @if ($comment->is_active)
                                    <button onclick="toggleStatus({{ $comment->id }}, false)"
                                        class="bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600">Ẩn</button>
                                @else
                                    <button onclick="toggleStatus({{ $comment->id }}, true)"
                                        class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">Hiển
                                        thị</button>
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

    {{-- Modal hiển thị chi tiết --}}
    <div id="modalOverlay" class="hidden fixed inset-0 bg-black/40 flex justify-center items-center z-50">
        <div class="bg-white rounded-2xl shadow-lg p-6 w-[420px] max-w-[90%]">
            <h5 id="modalTitle" class="text-lg font-semibold mb-3">Chi tiết đánh giá</h5>
            <div id="modalContent" class="text-sm text-gray-700 mb-4"></div>
            <button onclick="closeModal()" class="mt-2 w-full bg-gray-200 text-gray-700 rounded-lg py-2 hover:bg-gray-300">
                Đóng
            </button>
        </div>
    </div>

    <script>
        document.getElementById('searchBox').addEventListener('input', function() {
            this.form.submit(); // mỗi lần gõ sẽ tự submit GET
        });

        // --- TOGGLE STATUS ---
        function toggleStatus(id, status) {
            if (!status) {
                // Nếu là ẩn → hỏi lý do
                const reason = prompt('Nhập lý do ẩn đánh giá:');
                if (!reason) {
                    alert('Bạn phải nhập lý do để ẩn đánh giá!');
                    return;
                }
                sendToggleRequest(id, status, reason);
            } else {
                // Nếu là hiển thị → không cần lý do
                sendToggleRequest(id, status, null);
            }
        }

        function sendToggleRequest(id, status, reason = null) {
            fetch(`/admin/reviews/${id}/toggle`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        status: status,
                        reason: reason
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.message === 'Success') {
                        alert(status ? 'Đánh giá đã được hiển thị lại.' : 'Đánh giá đã được ẩn.');
                        location.reload();
                    } else {
                        alert(data.message || 'Có lỗi xảy ra.');
                    }
                })
                .catch(() => alert('Không thể kết nối đến máy chủ.'));
        }

        function showDetail(id) {
            fetch(`/admin/reviews/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.message === 'Success') {
                        const r = data.data;
                        const html = `
                        <p><b>Người dùng:</b> ${r.name}</p>
                        <p><b>Đánh giá:</b> ${'⭐'.repeat(r.review.rating)}</p>
                        <p class="mt-2">${r.review.content}</p>
                        <p><b>Trạng thái:</b> ${r.moderation.is_active ? 'Hiển thị' : 'Ẩn'}</p>
                        <p><b>Ngày tạo:</b> ${r.review.created_at}</p>
                    `;
                        openModal('Chi tiết đánh giá', html);
                    }
                });
        }

        function showReply(id) {
            fetch(`/admin/reviews/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.message === 'Success') {
                        const r = data.data;
                        const reply = r.moderation.reply ?? '';
                        const hasReply = reply.trim() !== '';

                        let html = `
                    <textarea id="replyText" class="w-full border rounded-lg p-2 mb-3" rows="4" placeholder="Nhập phản hồi...">${reply}</textarea>
                    <div class="flex gap-2">
                        <button onclick="submitReply(${id})" class="flex-1 bg-blue-600 text-white rounded-lg py-2 hover:bg-blue-700">
                            ${hasReply ? 'Cập nhật phản hồi' : 'Gửi phản hồi'}
                        </button>
                        ${hasReply ? `
                                                                                            <button onclick="deleteReply(${id})" class="flex-1 bg-red-500 text-white rounded-lg py-2 hover:bg-red-600">
                                                                                                Xóa phản hồi
                                                                                            </button>` : ''}
                    </div>
                `;

                        openModal('Phản hồi đánh giá', html);
                    }
                });
        }

        function submitReply(id) {
            const text = document.getElementById('replyText').value.trim();
            if (!text) return alert('Vui lòng nhập phản hồi!');

            fetch(`/admin/reviews/${id}/reply`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        reply: text
                    })
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message || 'Đã gửi phản hồi!');
                    closeModal();
                    location.reload();
                })
                .catch(() => alert('Lỗi phản hồi.'));
        }


        function deleteReply(id) {
            if (!confirm('Bạn có chắc muốn xóa phản hồi này?')) return;

            fetch(`/admin/reviews/${id}/reply`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message || 'Đã xóa phản hồi.');
                    closeModal();
                    location.reload();
                })
                .catch(() => alert('Không thể xóa phản hồi.'));
        }

        function openModal(title, content) {
            document.getElementById('modalTitle').innerText = title;
            document.getElementById('modalContent').innerHTML = content;
            document.getElementById('modalOverlay').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('modalOverlay').classList.add('hidden');
        }
    </script>
@endsection
