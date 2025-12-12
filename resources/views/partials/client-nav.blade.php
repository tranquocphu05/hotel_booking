<nav id="mainNav" class="fixed top-0 w-full bg-white border-b border-gray-200 z-[9999999] transition-all duration-300">
    <div id="navInner" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 transition-all duration-300">

        <div id="navContainer" class="flex justify-between items-center transition-all duration-300">

            {{-- Logo --}}
            <div class="flex items-center h-[90px] transition-all duration-300">
                <a href="{{ url('/') }}"
                    class="nav-logo text-4xl font-serif font-bold text-gray-800 cursor-pointer transition-all duration-300">OZIA
                    HOTEL</a>
            </div>

            {{-- Menu --}}
            <div class="hidden md:flex items-center space-x-8 text-base font-semibold uppercase tracking-wide">

                <a href="{{ url('/') }}" class="nav-link text-gray-600 hover:text-yellow-600 py-2 font-semibold transition-all duration-200 hover:scale-105">Trang Chủ</a>

                <div class="relative group">
                    <a href="{{ route('client.phong') }}"
                        class="nav-link text-gray-600 hover:text-yellow-600 py-2 text-base inline-flex items-center gap-1 font-semibold transition-all duration-200 hover:scale-105 group/nav">
                        <span>Phòng</span>
                        <svg class="w-4 h-4 transition-transform duration-200 group-hover/nav:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </a>

                    <div
                        class="nav-dropdown-room absolute left-0 top-full mt-2 w-56 bg-white/95 backdrop-blur-sm border border-gray-200/50 shadow-2xl rounded-2xl overflow-hidden z-[9999999] opacity-0 invisible group-hover:opacity-100 group-hover:visible transform translate-y-2 group-hover:translate-y-0 transition-all duration-300 ease-out -translate-x-4">
                        <div class="p-2 space-y-1">
                            @forelse(($menuLoaiPhongs ?? []) as $lp)
                                <a href="{{ route('client.phong', ['loai_phong' => $lp->id]) }}"
                                    class="dropdown-link block px-4 py-3 text-sm font-medium transition-all duration-200"
                                    style="color: #374151 !important;"
                                    onmouseover="this.style.color='#f59e0b'"
                                    onmouseout="this.style.color='#374151'">
                                    {{ $lp->ten_loai }}
                                </a>
                            @empty
                                <div class="px-4 py-3 text-sm text-gray-400 text-center italic">Chưa có loại phòng</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <a href="{{ route('client.gioithieu') }}" class="nav-link text-gray-600 hover:text-yellow-600 py-2 font-semibold transition-all duration-200 hover:scale-105">Giới
                    Thiệu</a>

                <a href="{{ route('client.tintuc') }}" class="nav-link text-gray-600 hover:text-yellow-600 py-2 font-semibold transition-all duration-200 hover:scale-105">Tin
                    Tức</a>

                <a href="{{ route('client.lienhe') }}" class="nav-link text-gray-600 hover:text-yellow-600 py-2 font-semibold transition-all duration-200 hover:scale-105">Liên
                    Hệ</a>

            </div>

            {{-- Client Auth --}}
            <div class="flex items-center h-[90px] transition-all duration-300">
                @auth
                    <div class="relative group">
                        <button
                            class="nav-user-button flex items-center space-x-2 text-sm text-gray-600 hover:text-red-600 focus:outline-none">
                            @if (auth()->user()->img)
                                <img src="{{ asset(auth()->user()->img) }}" alt="{{ auth()->user()->ho_ten }}"
                                    class="w-8 h-8 rounded-full object-cover border-2 border-red-200">
                            @else
                                <div
                                    class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center border-2 border-red-200">
                                    <span
                                        class="text-red-600 font-semibold text-xs">{{ strtoupper(substr(auth()->user()->ho_ten ?? 'U', 0, 1)) }}</span>
                                </div>
                            @endif
                            <span>{{ auth()->user()->username ?? auth()->user()->ho_ten ?? 'User' }}</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div
                            class="nav-dropdown-user absolute right-0 mt-2 w-64 bg-white/95 backdrop-blur-sm border border-gray-200/50 shadow-2xl rounded-xl overflow-hidden z-[999999] opacity-0 invisible group-hover:opacity-100 group-hover:visible transform translate-y-2 group-hover:translate-y-0 transition-all duration-300 ease-out">
                            <div class="p-2.5 border-b border-gray-100">
                                <div class="flex items-center gap-2">
                                    <div class="flex-shrink-0">
                                        @if (auth()->user()->img)
                                            <img src="{{ asset(auth()->user()->img) }}" alt="{{ auth()->user()->ho_ten }}"
                                                class="w-9 h-9 rounded-full object-cover border-2 border-orange-200">
                                        @else
                                            <div class="w-9 h-9 bg-gradient-to-br from-orange-400 to-yellow-500 rounded-full flex items-center justify-center border-2 border-orange-200">
                                                <span class="text-white font-bold text-sm">{{ strtoupper(substr(auth()->user()->ho_ten ?? 'U', 0, 1)) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate">
                                            {{ auth()->user()->username ?? auth()->user()->ho_ten ?? 'User' }}</p>
                                        <p class="text-xs text-orange-600 font-medium">Khách hàng thân thiết</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-1.5">
                                <div class="space-y-0.5">
                                    <p class="px-2 py-0.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Quản lý tài khoản</p>
                                    <div class="space-y-0.5">
                                        <a href="{{ route('profile.edit') }}"
                                            class="user-dropdown-link flex items-center gap-2 px-2 py-1.5 text-sm text-gray-700 rounded-md transition-all duration-200 {{ request()->routeIs('profile.edit') ? 'bg-orange-50 text-orange-600' : '' }}"
                                            onmouseover="this.style.color='#f59e0b'"
                                            onmouseout="this.style.color='#374151'">
                                            <span class="w-5 h-5 flex items-center justify-center rounded bg-gray-100 text-gray-600 transition-colors duration-200">
                                                <i class="fas fa-user text-xs"></i>
                                            </span>
                                            <span class="font-medium text-sm">Thông tin cá nhân</span>
                                        </a>
                                        <a href="{{ route('client.dashboard') }}"
                                            class="user-dropdown-link flex items-center gap-2 px-2 py-1.5 text-sm text-gray-700 rounded-md transition-all duration-200 {{ request()->routeIs('client.dashboard') ? 'bg-orange-50 text-orange-600' : '' }}"
                                            onmouseover="this.style.color='#f59e0b'"
                                            onmouseout="this.style.color='#374151'">
                                            <span class="w-5 h-5 flex items-center justify-center rounded bg-gray-100 text-gray-600 transition-colors duration-200">
                                                <i class="fas fa-tachometer-alt text-xs"></i>
                                            </span>
                                            <span class="font-medium text-sm">Dashboard</span>
                                        </a>
                                        <a href="{{ route('client.phong') }}"
                                            class="user-dropdown-link flex items-center gap-2 px-2 py-1.5 text-sm text-gray-700 rounded-md transition-all duration-200 {{ request()->routeIs('client.phong') ? 'bg-orange-50 text-orange-600' : '' }}"
                                            onmouseover="this.style.color='#f59e0b'"
                                            onmouseout="this.style.color='#374151'">
                                            <span class="w-5 h-5 flex items-center justify-center rounded bg-gray-100 text-gray-600 transition-colors duration-200">
                                                <i class="fas fa-bed text-xs"></i>
                                            </span>
                                            <span class="font-medium text-sm">Đặt phòng</span>
                                        </a>
                                        <a href="{{ route('profile.edit') }}#lich-su"
                                            class="user-dropdown-link flex items-center gap-2 px-2 py-1.5 text-sm text-gray-700 rounded-md transition-all duration-200"
                                            onmouseover="this.style.color='#f59e0b'"
                                            onmouseout="this.style.color='#374151'">
                                            <span class="w-5 h-5 flex items-center justify-center rounded bg-gray-100 text-gray-600 transition-colors duration-200">
                                                <i class="fas fa-calendar-check text-xs"></i>
                                            </span>
                                            <span class="font-medium text-sm">Lịch sử</span>
                                        </a>
                                    </div>
                                </div>

                                @if (auth()->user()->vai_tro === 'admin')
                                    <div class="border-t border-gray-100 pt-3 mt-3">
                                        <p class="px-2 py-0.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Quản trị</p>
                                        <div class="space-y-0.5 mt-1">
                                            <a href="{{ route('admin.dashboard') }}"
                                                class="user-dropdown-link flex items-center gap-2 px-2 py-1.5 text-sm text-gray-700 rounded-md transition-all duration-200 {{ request()->routeIs('admin.*') ? 'bg-red-50 text-red-600' : '' }}"
                                                onmouseover="this.style.color='#f59e0b'"
                                                onmouseout="this.style.color='#374151'">
                                                <span class="w-5 h-5 flex items-center justify-center rounded bg-gray-100 text-gray-600 transition-colors duration-200">
                                                    <i class="fas fa-cog text-xs"></i>
                                                </span>
                                                <span class="font-medium text-sm">Admin Panel</span>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="nav-dropdown-user__footer border-t border-gray-100 p-1.5 bg-gray-50/50">
                                <form method="POST" action="{{ route('logout') }}" class="block">
                                    @csrf
                                    <button type="submit"
                                        class="w-full flex items-center gap-2 px-2 py-1.5 text-sm text-red-600 rounded-md hover:bg-red-50 transition-all duration-200 group/logout border border-red-200 hover:border-red-300">
                                        <span class="w-5 h-5 flex items-center justify-center rounded bg-red-100 text-red-600 group-hover/logout:bg-red-200 transition-colors duration-200">
                                            <i class="fas fa-sign-out-alt text-xs"></i>
                                        </span>
                                        <span class="font-semibold text-sm">Đăng xuất</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="nav-link text-sm mr-4 text-gray-600 hover:text-red-600">Đăng
                        nhập</a>
                    <a href="{{ route('register') }}" class="nav-link text-sm text-gray-600 hover:text-red-600">Đăng ký</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<style>
    /* HIỆU ỨNG GẠCH CHÂN VÀ DROPDOWN (CẦN CSS TÙY CHỈNH) */
    .nav-link,
    .dropdown-link {
        position: relative;
    }

    /* Gạch chân cho menu chính */
    .nav-link::after {
        content: "";
        position: absolute;
        bottom: 5px;
        left: 0;
        width: 0%;
        height: 1px;
        background-color: #eab308; /* yellow-500 */
        transition: width .3s ease;
    }

    .nav-link:hover::after {
        width: 100%;
    }

    /* Gạch chân cho menu con - chạy từ đầu chữ, đồng bộ với padding của item */
    .dropdown-link::after {
        content: "";
        position: absolute;
        bottom: 4px;
        left: 16px;  /* trùng với padding-left của item */
        right: 16px;
        width: 0%;
        height: 1px;
        background-color: #f59e0b; /* amber-500 - đồng bộ với text */
        transition: width .4s ease;
    }

    .dropdown-link:hover::after {
        width: calc(100% - 32px); /* Trừ đi padding left + right */
    }

    /* Override màu & layout cho dropdown links - đảm bảo phẳng, không dạng thẻ card */
    .nav-dropdown-room .dropdown-link {
        display: block;
        color: #374151 !important; /* gray-700 */
        text-decoration: none !important;
        background: transparent !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        margin: 0 !important;
        padding: 8px 16px !important; /* đẩy chữ và gạch chân vào trong một chút */
        line-height: 1.3;
        font-size: 0.95rem; /* cho chữ to hơn một chút */
        white-space: nowrap; /* luôn hiển thị trên 1 dòng */
    }

    /* Giảm khoảng cách giữa các item trong dropdown phòng */
    .nav-dropdown-room .p-2 {
        padding-top: 6px !important;
        padding-bottom: 6px !important;
    }

    .nav-dropdown-room .p-2 > * + * {
        margin-top: 4px !important;
    }

    .nav-dropdown-room .dropdown-link:hover,
    .nav-dropdown-room a.dropdown-link:hover {
        color: #f59e0b !important; /* amber-500 - màu vàng cam */
        background: transparent !important;
    }

    /* Gạch chân cho user dropdown links */
    .user-dropdown-link {
        position: relative;
    }

    .user-dropdown-link::after {
        content: "";
        position: absolute;
        bottom: 4px;
        left: 8px; /* px-2 = 8px */
        right: 8px;
        width: 0%;
        height: 1px;
        background-color: #f59e0b; /* amber-500 */
        transition: width .4s ease;
    }

    .user-dropdown-link:hover::after {
        width: calc(100% - 16px); /* Trừ đi padding left + right */
    }

    .nav-user-button {
        border-radius: 999px;
        padding: 6px 14px;
        border: 1px solid transparent;
        transition: border-color .2s ease, color .2s ease;
    }

    .nav-user-button:hover,
    .nav-user-button:focus-visible {
        border-color: rgba(249, 115, 22, 0.35);
    }

    .nav-dropdown-user {
        width: min(18rem, calc(100vw - 2rem));
        border-radius: 20px;
        padding: 16px;
        box-shadow: 0 25px 60px rgba(15, 23, 42, 0.2);
        border: 1px solid #eef2ff;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .nav-dropdown-user__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        border-bottom: 1px solid #eef2ff;
        padding-bottom: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .nav-dropdown-user__identity {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .nav-dropdown-user__avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #fef3c7;
        color: #b45309;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        text-transform: uppercase;
    }

    .nav-dropdown-user__name {
        font-weight: 600;
        color: #111827;
    }

    .nav-dropdown-user__role {
        font-size: 0.8rem;
        color: #94a3b8;
    }

    .nav-dropdown-user__sections {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
        max-height: 230px;
        overflow-y: auto;
        padding-right: 4px;
    }

    .nav-dropdown-user__section-title {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #94a3b8;
        letter-spacing: 0.05em;
        margin-bottom: 0.4rem;
    }

    .nav-dropdown-user__list {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .nav-dropdown-user__item {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        border-radius: 12px;
        padding: 0.55rem 0.65rem;
        border: 1px solid #f1f5f9;
        background: #fff;
        text-decoration: none;
        color: #1f2937;
        transition: transform .2s ease, border-color .2s ease, background .2s ease, color .2s ease;
    }

    .nav-dropdown-user__item:hover {
        border-color: #fde68a;
        background: #fff7e6;
        color: #b45309;
    }

    .nav-dropdown-user__item.is-active {
        border-color: #f59e0b;
        background: #fff3d4;
        color: #92400e;
        box-shadow: inset 0 0 0 1px rgba(245, 158, 11, 0.25);
    }

    .nav-dropdown-user__item--admin {
        border-style: dashed;
        border-color: #c7d2fe;
        background: #eef2ff;
        color: #4338ca;
    }

    .nav-dropdown-user__icon {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        background: #f1f5f9;
        color: inherit;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
    }

    .nav-dropdown-user__item-label {
        font-weight: 600;
        display: block;
        font-size: 0.92rem;
    }

    .nav-dropdown-user__item-desc {
        font-size: 0.72rem;
        color: #94a3b8;
        display: block;
        margin-top: 2px;
    }

    .nav-dropdown-user__item.is-active .nav-dropdown-user__item-desc {
        color: inherit;
        opacity: 0.9;
    }

    .nav-dropdown-user__chevron {
        margin-left: auto;
        font-size: 0.75rem;
        color: currentColor;
        opacity: 0.4;
    }

    .nav-dropdown-user__footer {
        border-top: 1px solid #eef2ff;
        margin-top: 1rem;
        padding-top: 0.75rem;
    }

    .nav-dropdown-user__footer button {
        border-radius: 16px;
        border: 1px solid transparent;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        transition: border-color .2s ease;
    }

    .nav-dropdown-user__footer button:hover {
        border-color: #fecaca;
    }

    .nav-dropdown-room,
    .nav-dropdown-pages,
    .nav-dropdown-user {
        max-height: 0;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        overflow: hidden;
        transform: translateY(8px);
        transition: max-height .45s ease, opacity .35s ease, transform .35s ease;
        position: absolute;
        top: 100% !important;
        margin-top: 0 !important;
        z-index: 9999999; /* Sửa: Đồng bộ z-index cao cho các dropdown */
        background: #ffffff;
    }

    .group {
        position: relative;
    }

    .group:hover .nav-dropdown-room,
    .group:hover .nav-dropdown-pages,
    .group:hover .nav-dropdown-user {
        max-height: 520px; /* Giữ đủ cao cho nút Đăng xuất nhưng tổng thể gọn hơn */
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        transform: translateY(0);
    }

    .nav-dropdown-user__sections::-webkit-scrollbar {
        width: 6px;
    }

    .nav-dropdown-user__sections::-webkit-scrollbar-track {
        background: transparent;
    }

    .nav-dropdown-user__sections::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.12);
        border-radius: 999px;
    }

    #mainNav {
        /* Bỏ border-b của Tailwind để dùng box-shadow */
        border-bottom: none !important;
        transition: all 0.3s ease;
    }

    #mainNav.scrolled {
        /* Thêm viền dưới màu vàng sáng với hiệu ứng glow khi scroll */
        box-shadow: 
            0 1px 4px rgba(212, 175, 55, 0.4),
            0 2px 0 0 #fbbf24,
            0 4px 8px rgba(251, 191, 36, 0.3),
            0 0 15px rgba(251, 191, 36, 0.2);
    }

    /* Thu nhỏ container khi scroll - giảm ít hơn để giữ text ổn định */
    #navInner.scrolled {
        max-width: 1240px; /* Thu nhỏ từ max-w-7xl (1280px) xuống 1240px - ít hơn */
        padding-left: 1.25rem; /* Giảm padding ít hơn */
        padding-right: 1.25rem;
    }

    #navContainer {
        height: 90px;
        transition: height 0.3s ease; /* Thêm transition cho chiều cao */
    }

    #navContainer.scrolled {
        height: 82px; /* Giảm từ 90px xuống 82px thay vì 75px - ít hơn */
    }

    /* Padding ban đầu cho body, bằng chiều cao ban đầu của nav */
    body {
        padding-top: 90px;
        transition: padding-top 0.3s ease; /* Thêm transition để chuyển đổi mượt */
    }

    /* Thêm padding-top mới khi nav đã cuộn */
    body.scrolled-nav {
        padding-top: 82px !important; /* Điều chỉnh theo chiều cao mới */
    }

    /* Điều chỉnh logo khi scroll để giữ text ổn định */
    #navContainer.scrolled .nav-logo {
        font-size: 2rem; /* Giảm từ text-4xl (2.25rem) xuống 2rem - ít hơn */
        line-height: 1.2;
    }

    /* Điều chỉnh chiều cao của logo container khi scroll */
    #navContainer.scrolled .flex.items-center.h-\[90px\] {
        height: 82px;
    }

    /* Điều chỉnh chiều cao của auth section khi scroll */
    #navContainer.scrolled .flex.items-center.h-\[90px\]:last-child {
        height: 82px;
    }
</style>

<script>
    const nav = document.getElementById("mainNav");
    const container = document.getElementById("navContainer");
    const navInner = document.getElementById("navInner");
    const body = document.body; // Lấy thẻ body

    function handleScroll() {
        const scrollPosition = window.scrollY;

        if (scrollPosition > 120) {
            container.classList.add("scrolled");
            nav.classList.add("scrolled");
            navInner.classList.add("scrolled");
            body.classList.add("scrolled-nav"); // THÊM class vào body
        } else {
            container.classList.remove("scrolled");
            nav.classList.remove("scrolled");
            navInner.classList.remove("scrolled");
            body.classList.remove("scrolled-nav"); // XÓA class khỏi body
        }
    }

    window.addEventListener('scroll', handleScroll);
</script>
