@extends('layouts.client')

@section('title', 'Tin Tức & Blog')

@section('client_content')

    <div class="bg-gray-50 py-16 bg-cover bg-center">
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-800">
            <h2 class="text-4xl font-serif font-bold mb-3">Tin Tức Khách Sạn</h2>
            <div class="text-lg text-gray-600">
                <a href="{{ url('/') }}" class="hover:text-red-600 transition">Trang Chủ</a>
                <span class="mx-2">/</span>
                <span class="font-semibold text-gray-800">Tin Tức</span>
            </div>
        </div>
    </div>
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4">

            @if ($posts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                    @foreach ($posts as $post)
                        <div class="relative h-96 rounded-lg overflow-hidden shadow-xl hover:shadow-2xl transition duration-300 group"
                            style="background-image: url('{{ $post->hinh_anh ? asset($post->hinh_anh) : 'https://placehold.co/600x400/D9D9D9/333333?text=Hotel+Blog' }}'); background-size: cover; background-position: center;">

                            <div
                                class="absolute inset-0 bg-black bg-opacity-30 group-hover:bg-opacity-10 transition duration-300">
                            </div>

                            <div class="absolute bottom-0 left-0 p-6 text-white z-10">
                                <span
                                    class="inline-block bg-red-600 text-white text-xs uppercase px-3 py-1 mb-3 font-semibold rounded-full tracking-wider">
                                    Tin tức
                                </span>

                                <h4 class="text-2xl font-serif font-bold leading-snug hover:text-red-300 transition">
                                    <a href="{{ route('client.tintuc.show', $post->slug) }}">{{ $post->tieu_de }}</a>
                                </h4>
                                <div class="text-sm mt-2 flex items-center opacity-90">
                                    <i class="fa fa-clock mr-2 text-red-400"></i> {{ $post->created_at->format('d/m/Y') }}
                                </div>
                                <div class="text-sm mt-1 flex items-center opacity-90">
                                    <i class="fa fa-eye mr-2 text-red-400"></i> {{ number_format($post->luot_xem) }} lượt
                                    xem
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>

                @if ($posts->hasPages())
                    <div class="text-center mt-12">
                        {{ $posts->links() }}
                    </div>
                @endif
            @else
                <p class="text-center text-gray-500 text-lg py-10">Hiện chưa có bài viết nào được đăng tải.</p>
            @endif
        </div>
    </section>
<div id="openVoucherBtn"
    class="flex items-center justify-between bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg p-3 cursor-pointer transition shadow-sm">
    <div class="flex items-center space-x-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 7l5 5-5 5M6 7h7v10H6z" />
        </svg>
        <span class="text-blue-700 font-semibold text-sm">
            Chọn hoặc nhập mã giảm giá
        </span>
    </div>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none"
        viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 5l7 7-7 7" />
    </svg>
</div>

<!-- Voucher đã chọn -->
<div id="voucherDisplay" class="text-sm text-green-600 font-medium mt-2 hidden"></div>

<!-- Input ẩn -->
<input type="hidden" id="voucherCode" name="voucher_code">

<!-- Hiệu ứng -->
<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const openVoucherBtn = document.getElementById('openVoucherBtn'); // nút bạn có
    const voucherDisplayDiv = document.getElementById('voucherDisplay');
    const voucherCodeInput = document.getElementById('voucherCode');

    let popupElement = null;
    let originalVoucherContent = '';

    // Áp dụng voucher: nhận code và popup reference (để đóng)
    function applyVoucher(code, popup) {
        if (!code) return;
        console.log('Applied voucher code:', code);

        if (voucherCodeInput) voucherCodeInput.value = code;
        if (voucherDisplayDiv) {
            voucherDisplayDiv.textContent = `Mã đã áp dụng: ${code}`;
            voucherDisplayDiv.classList.remove('hidden');
        }

        // Đóng popup thân thiện
        if (popup) {
            const closeBtn = popup.querySelector('#closeVoucherPopup');
            if (closeBtn) closeBtn.click();
        }
    }

    // Thiết lập logic tìm & apply cho popup (gọi sau khi popupElement có trong DOM)
    function setupVoucherLogic(popup) {
        if (!popup) return;

        const popupContent = popup.querySelector('.custom-voucher-inner') || popup.children[0];
        const popupVoucherCodeInput = popup.querySelector('#popupVoucherCodeInput');
        const searchVoucherBtn = popup.querySelector('#searchVoucherBtn');
        const voucherListContainer = popup.querySelector('.custom-scrollbar');
        const closeBtn = popup.querySelector('#closeVoucherPopup');
        const alertContainer = popup.querySelector('#voucherAlertMessage');
        const alertText = alertContainer ? alertContainer.querySelector('p') : null;

        if (!voucherListContainer) {
            console.warn('Không tìm thấy container voucher (.custom-scrollbar)');
            return;
        }

        // Lưu HTML gốc 1 lần
        if (!originalVoucherContent) originalVoucherContent = voucherListContainer.innerHTML;

        function displayAlert(message, isError = false) {
            if (!alertContainer || !alertText) return;
            if (!message) {
                alertContainer.classList.add('hidden');
                alertText.textContent = '';
                alertText.className = 'text-sm font-medium p-2 rounded-lg border';
                return;
            }
            alertText.textContent = message;
            alertContainer.classList.remove('hidden');
            if (isError) {
                alertText.className = 'text-sm font-medium text-red-600 bg-red-100 p-2 rounded-lg border border-red-300 animate-fadeIn';
            } else {
                alertText.className = 'text-sm font-medium text-green-600 bg-green-100 p-2 rounded-lg border border-green-300 animate-fadeIn';
            }
        }

        // Đóng popup + reset trạng thái tìm kiếm
        function closePopup() {
            if (popupContent) {
                popupContent.classList.remove('animate-fadeIn');
                popupContent.classList.add('fade-out');
            }

            if (popupVoucherCodeInput) popupVoucherCodeInput.value = '';
            displayAlert('');

            // restore danh sách gốc (nếu đã bị thay đổi)
            if (voucherListContainer.innerHTML !== originalVoucherContent) {
                voucherListContainer.innerHTML = originalVoucherContent;
            }

            setTimeout(() => {
                popup.classList.add('hidden');
                if (popupContent) popupContent.classList.remove('fade-out');
            }, 300);
        }

        // Đóng bằng nút & click ngoài
        if (closeBtn) closeBtn.addEventListener('click', closePopup);
        popup.addEventListener('click', function (e) {
            if (e.target === popup) closePopup();
        });

        // Tìm kiếm: lọc dựa trên mã (dùng HTML gốc lưu sẵn)
        function handleSearch() {
            const searchCode = (popupVoucherCodeInput?.value || '').trim().toUpperCase();
            displayAlert('');
            // nếu trống -> show lỗi và restore
            if (!searchCode) {
                displayAlert('Vui lòng nhập mã voucher để tìm kiếm hoặc áp dụng.', true);
                voucherListContainer.innerHTML = originalVoucherContent;
                return;
            }

            // tạo DOM từ originalVoucherContent và tìm node khớp
            const tmp = document.createElement('div');
            tmp.innerHTML = originalVoucherContent;
            const allCards = Array.from(tmp.querySelectorAll('.custom-voucher-card'));
            const matched = allCards.filter(c => {
                const code = (c.dataset.voucherCode || '').toUpperCase();
                return code === searchCode;
            });

            voucherListContainer.innerHTML = ''; // clear hiện tại

            if (matched.length) {
                // append clone để giữ data-* etc
                matched.forEach(node => voucherListContainer.appendChild(node.cloneNode(true)));
                displayAlert(`Đã tìm thấy mã voucher "${searchCode}". Nhấn "Dùng ngay" để áp dụng.`, false);
            } else {
                const p = document.createElement('p');
                p.className = 'text-center text-gray-500 italic py-4 no-voucher-result';
                p.textContent = `Không tìm thấy mã voucher "${searchCode}". Vui lòng kiểm tra lại.`;
                voucherListContainer.appendChild(p);
                displayAlert(`Không tìm thấy mã voucher "${searchCode}".`, true);
            }
        }

        if (searchVoucherBtn) searchVoucherBtn.addEventListener('click', handleSearch);
        if (popupVoucherCodeInput) {
            popupVoucherCodeInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleSearch();
                }
            });
        }

        // Event delegation: xử lý click vào button "Dùng ngay" hoặc click vào thẻ
        voucherListContainer.addEventListener('click', function (e) {
            const applyBtn = e.target.closest('.apply-voucher-btn');
            if (applyBtn) {
                // tìm thẻ bao quanh
                const card = applyBtn.closest('.custom-voucher-card');
                if (card && card.dataset.isValid === 'true') {
                    applyVoucher(card.dataset.voucherCode, popup);
                } else {
                    // optional: feedback
                    displayAlert('Voucher này chưa đủ điều kiện để áp dụng.', true);
                }
                return;
            }

            // nếu click vào thẻ voucher (ngoài button)
            const card = e.target.closest('.custom-voucher-card');
            if (card) {
                if (card.dataset.isValid === 'true') {
                    applyVoucher(card.dataset.voucherCode, popup);
                } else {
                    displayAlert('Voucher này chưa đủ điều kiện để áp dụng.', true);
                }
            }
        });
    }

    // Mở popup: fetch lần đầu, cache lần sau
    if (openVoucherBtn) {
        openVoucherBtn.addEventListener('click', function () {
            if (popupElement) {
                popupElement.classList.remove('hidden');
                const inner = popupElement.querySelector('.custom-voucher-inner');
                if (inner) inner.classList.add('animate-fadeIn');
                return;
            }

            // fetch nội dung popup (route trả về HTML chứa #voucherPopupContent)
            fetch('/client/voucher')
                .then(res => res.text())
                .then(html => {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    const fetchedPopup = tempDiv.querySelector('#voucherPopupContent');
                    if (!fetchedPopup) {
                        alert('Không tìm thấy popup voucher trong phản hồi từ server.');
                        return;
                    }

                    // đảm bảo class tên giống (custom-voucher-inner) — nếu không, sửa Blade tương ứng
                    // 1) thêm hidden trước khi append
                    fetchedPopup.classList.add('hidden');
                    document.body.appendChild(fetchedPopup);
                    popupElement = fetchedPopup;

                    // Thiết lập logic (binding events, delegation...)
                    setupVoucherLogic(popupElement);

                    // Hiển thị popup mượt
                    popupElement.classList.remove('hidden');
                    const inner = popupElement.querySelector('.custom-voucher-inner');
                    if (inner) {
                        // bật animation
                        inner.classList.add('animate-fadeIn');
                    }
                })
                .catch(err => {
                    console.error('Lỗi khi fetch voucher:', err);
                    alert('Không thể tải danh sách voucher. Vui lòng thử lại.');
                });
        });
    } else {
        console.warn('#openVoucherBtn không tìm thấy trên trang — popup sẽ không mở được');
    }
});
</script>
@endsection
