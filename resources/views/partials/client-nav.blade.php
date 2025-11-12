<nav id="mainNav" class="fixed top-0 w-full bg-white border-b border-gray-200 z-[9999999] transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div id="navContainer" class="flex justify-between items-center transition-all duration-300">

            {{-- Logo --}}
            <div class="flex items-center h-[90px] transition-all duration-300">
                <a href="{{ url('/') }}"
                    class="nav-logo text-4xl font-serif font-bold text-gray-800 cursor-pointer">OZIA
                    HOTEL</a>
            </div>

            {{-- Menu --}}
            <div class="hidden md:flex items-center space-x-8 text-base font-semibold uppercase tracking-wide">

                <a href="{{ url('/') }}" class="nav-link text-gray-600 hover:text-yellow-600 py-2">Trang Chủ</a>

                <div class="relative group">
                    <a href="{{ route('client.phong') }}"
                        class="nav-link text-gray-600 hover:text-yellow-600 py-2 text-base inline-flex items-center">Phòng
                        ▾</a>

                    <div
                        class="nav-dropdown-room absolute left-0 top-full min-w-[14rem] bg-white border border-gray-100 shadow-xl rounded-lg overflow-hidden z-[9999999]">
                        <div class="py-2 max-h-80 overflow-auto">
                            @forelse(($menuLoaiPhongs ?? []) as $lp)
                                <a href="{{ route('client.phong', ['loai_phong' => $lp->id]) }}"
                                    class="dropdown-link block px-4 py-2 text-sm text-gray-700 hover:text-yellow-600">{{ $lp->ten_loai }}</a>
                            @empty
                                <div class="px-4 py-3 text-sm text-gray-400">Chưa có loại phòng</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <a href="{{ route('client.gioithieu') }}" class="nav-link text-gray-600 hover:text-yellow-600 py-2">Giới
                    Thiệu</a>

                <a href="{{ route('client.tintuc') }}" class="nav-link text-gray-600 hover:text-yellow-600 py-2">Tin
                    Tức</a>

                <a href="{{ route('client.lienhe') }}" class="nav-link text-gray-600 hover:text-yellow-600 py-2">Liên
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
                            <span>{{ auth()->user()->ten ?? (auth()->user()->ho_ten ?? 'User') }}</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div
                            class="nav-dropdown-user absolute right-0 mt-2 bg-white rounded-2xl shadow-2xl py-1 z-[999999]">
                            <div class="nav-dropdown-user__header">
                                <div class="nav-dropdown-user__identity">
                                    <div class="nav-dropdown-user__avatar">
                                        @if (auth()->user()->img)
                                            <img src="{{ asset(auth()->user()->img) }}" alt="{{ auth()->user()->ho_ten }}"
                                                class="w-10 h-10 rounded-full object-cover">
                                        @else
                                            <span>{{ strtoupper(substr(auth()->user()->ho_ten ?? 'U', 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="nav-dropdown-user__name">
                                            {{ auth()->user()->ten ?? (auth()->user()->ho_ten ?? 'User') }}</p>
                                        <p class="nav-dropdown-user__role">Khách hàng thân thiết</p>
                                    </div>
                                </div>
                            </div>

                            <div class="nav-dropdown-user__sections">
                                <div class="nav-dropdown-user__section">
                                    <p class="nav-dropdown-user__section-title">Quản lý tài khoản</p>
                                    <div class="nav-dropdown-user__list">
                                        <a href="{{ route('profile.edit') }}"
                                            class="nav-dropdown-user__item {{ request()->routeIs('profile.edit') ? 'is-active' : '' }}">
                                            <span class="nav-dropdown-user__icon"><i class="fas fa-user"></i></span>
                                            <div>
                                                <span class="nav-dropdown-user__item-label">Thông tin cá nhân</span>
                                                <span class="nav-dropdown-user__item-desc">Cập nhật hồ sơ và bảo mật</span>
                                            </div>
                                            <i class="fas fa-chevron-right nav-dropdown-user__chevron"></i>
                                        </a>
                                        <a href="{{ route('client.dashboard') }}"
                                            class="nav-dropdown-user__item {{ request()->routeIs('client.dashboard') ? 'is-active' : '' }}">
                                            <span class="nav-dropdown-user__icon"><i class="fas fa-tachometer-alt"></i></span>
                                            <div>
                                                <span class="nav-dropdown-user__item-label">Dashboard</span>
                                                <span class="nav-dropdown-user__item-desc">Theo dõi đặt phòng & ưu đãi</span>
                                            </div>
                                            <i class="fas fa-chevron-right nav-dropdown-user__chevron"></i>
                                        </a>
                                        <a href="{{ route('client.phong') }}"
                                            class="nav-dropdown-user__item {{ request()->routeIs('client.phong') ? 'is-active' : '' }}">
                                            <span class="nav-dropdown-user__icon"><i class="fas fa-bed"></i></span>
                                            <div>
                                                <span class="nav-dropdown-user__item-label">Đặt phòng</span>
                                                <span class="nav-dropdown-user__item-desc">Khám phá phòng còn trống</span>
                                            </div>
                                            <i class="fas fa-chevron-right nav-dropdown-user__chevron"></i>
                                        </a>
                                        <a href="{{ route('profile.edit') }}#lich-su"
                                            class="nav-dropdown-user__item {{ request()->routeIs('profile.edit') ? 'is-active' : '' }}">
                                            <span class="nav-dropdown-user__icon"><i class="fas fa-calendar-check"></i></span>
                                            <div>
                                                <span class="nav-dropdown-user__item-label">Lịch sử đặt phòng</span>
                                                <span class="nav-dropdown-user__item-desc">Xem & quản lý đơn gần đây</span>
                                            </div>
                                            <i class="fas fa-chevron-right nav-dropdown-user__chevron"></i>
                                        </a>
                                        <a href="#"
                                            class="nav-dropdown-user__item">
                                            <span class="nav-dropdown-user__icon"><i class="fas fa-star"></i></span>
                                            <div>
                                                <span class="nav-dropdown-user__item-label">Đánh giá</span>
                                                <span class="nav-dropdown-user__item-desc">Gửi cảm nhận của bạn</span>
                                            </div>
                                            <i class="fas fa-chevron-right nav-dropdown-user__chevron"></i>
                                        </a>
                                    </div>
                                </div>

                                @if (auth()->user()->vai_tro === 'admin')
                                    <div class="nav-dropdown-user__section">
                                        <p class="nav-dropdown-user__section-title">Quản trị</p>
                                        <div class="nav-dropdown-user__list">
                                            <a href="{{ route('admin.dashboard') }}"
                                                class="nav-dropdown-user__item nav-dropdown-user__item--admin {{ request()->routeIs('admin.*') ? 'is-active' : '' }}">
                                                <span class="nav-dropdown-user__icon"><i class="fas fa-cog"></i></span>
                                                <div>
                                                    <span class="nav-dropdown-user__item-label">Admin Panel</span>
                                                    <span class="nav-dropdown-user__item-desc">Điều phối phòng & báo cáo</span>
                                                </div>
                                                <i class="fas fa-chevron-right nav-dropdown-user__chevron"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="nav-dropdown-user__footer">
                                <form method="POST" action="{{ route('logout') }}" class="block">
                                    @csrf
                                    <button type="submit"
                                        class="w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50"><i
                                            class="fas fa-sign-out-alt mr-2"></i>Đăng xuất</button>
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

    .nav-link::after,
    .dropdown-link::after {
        content: "";
        position: absolute;
        bottom: 0px;
        left: 0;
        width: 0%;
        height: 2px;
        background-color: #d4af37;
        transition: width .3s ease;
    }

    .nav-link:hover::after,
    .dropdown-link:hover::after {
        width: 100%;
    }

    .nav-link::after {
        bottom: 5px;
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
        max-height: 500px; /* Sửa: Tăng giá trị này để đảm bảo nút Đăng xuất hiển thị */
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        transform: translateY(0);
    }

    .group:hover .nav-dropdown-user {
        max-height: 360px;
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
    }

    #mainNav.scrolled {
        /* Thêm shadow khi cuộn */
        box-shadow: 0 1px 4px rgba(212, 175, 55, 0.4);
    }

    #navContainer {
        height: 90px;
        transition: height 0.3s ease; /* Thêm transition cho chiều cao */
    }

    #navContainer.scrolled {
        height: 75px;
    }

    /* Padding ban đầu cho body, bằng chiều cao ban đầu của nav */
    body {
        padding-top: 90px;
        transition: padding-top 0.3s ease; /* Thêm transition để chuyển đổi mượt */
    }

    /* Thêm padding-top mới khi nav đã cuộn */
    body.scrolled-nav {
        padding-top: 75px !important;
    }
</style>

<script>
    const nav = document.getElementById("mainNav");
    const container = document.getElementById("navContainer");
    const body = document.body; // Lấy thẻ body

    function handleScroll() {
        const scrollPosition = window.scrollY;

        if (scrollPosition > 120) {
            container.classList.add("scrolled");
            nav.classList.add("scrolled");
            body.classList.add("scrolled-nav"); // THÊM class vào body
        } else {
            container.classList.remove("scrolled");
            nav.classList.remove("scrolled");
            body.classList.remove("scrolled-nav"); // XÓA class khỏi body
        }
    });

</script>
