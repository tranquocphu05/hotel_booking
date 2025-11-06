{{-- Main Navigation Bar --}}
{{-- Đã loại bỏ class 'shadow-lg' vì không cần thiết với nền đen mờ --}}
<nav id="mainNav" class="transition-all duration-500 z-[999990]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Chiều cao nav container: 90px mặc định --}}
        <div id="navContainer" class="flex justify-between items-center transition-all duration-300">

            {{-- Logo --}}
            <div class="flex items-center h-20 md:h-[90px] transition-all duration-300">
                <a href="{{ url('/') }}"
                    class="nav-logo text-4xl font-serif font-bold text-gray-800 cursor-pointer">OZIA
                    HOTEL</a>
            </div>

            {{-- Menu --}}
            {{-- Đã giảm khoảng cách xuống space-x-8 (hẹp hơn) --}}
            <div class="hidden md:flex items-center space-x-8 text-base font-semibold uppercase tracking-wide">

                <a href="{{ url('/') }}" class="nav-link text-gray-600 hover:text-yellow-600 py-2">Trang Chủ</a>

                <div class="relative group">
                    <a href="{{ route('client.phong') }}"
                        class="nav-link text-gray-600 hover:text-yellow-600 py-2 text-base inline-flex items-center">Phòng
                        ▾</a>

                    <div
                        class="nav-dropdown-room absolute left-0 top-full min-w-[14rem] bg-white border border-gray-100 shadow-xl rounded-lg overflow-hidden z-[999999]">
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

                {{-- Đã XÓA menu "Trang" ở đây --}}

                <a href="{{ route('client.tintuc') }}" class="nav-link text-gray-600 hover:text-yellow-600 py-2">Tin
                    Tức</a>

                <a href="{{ route('client.lienhe') }}" class="nav-link text-gray-600 hover:text-yellow-600 py-2">Liên
                    Hệ</a>

            </div>

            {{-- Client Auth --}}
            <div class="flex items-center h-20 md:h-[90px] transition-all duration-300">
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
                            class="nav-dropdown-user absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-[999999]">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ auth()->user()->ten ?? (auth()->user()->ho_ten ?? 'User') }}</p>
                                <p class="text-xs text-gray-500">Khách hàng</p>
                            </div>
                            <a href="{{ route('profile.edit') }}"
                                class="dropdown-link block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i
                                    class="fas fa-user mr-2"></i>Thông tin cá nhân</a>
                            <a href="{{ route('client.dashboard') }}"
                                class="dropdown-link block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i
                                    class="fas fa-tachometer-alt mr-2"></i>Dashboard</a>
                            <a href="{{ route('client.phong') }}"
                                class="dropdown-link block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i
                                    class="fas fa-bed mr-2"></i>Đặt phòng</a>
                            <a href="{{ route('profile.edit') }}#lich-su"
                                class="dropdown-link block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i
                                    class="fas fa-calendar-check mr-2"></i>Lịch sử đặt phòng</a>
                            <a href="#"
                                class="dropdown-link block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i
                                    class="fas fa-star mr-2"></i>Đánh giá</a>
                            @if (auth()->user()->vai_tro === 'admin')
                                <hr class="my-1">
                                <a href="{{ route('admin.dashboard') }}"
                                    class="dropdown-link block px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50"><i
                                        class="fas fa-cog mr-2"></i>Admin Panel</a>
                            @endif
                            <hr class="my-1">
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"><i
                                        class="fas fa-sign-out-alt mr-2"></i>Đăng xuất</button>
                            </form>
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
    /* 1. HIỆU ỨNG GẠCH CHÂN */
    .nav-link,
    /* Áp dụng cho menu chính */
    .dropdown-link {
        /* Áp dụng cho menu con */
        position: relative;
    }

    .nav-link::after,
    .dropdown-link::after {
        content: "";
        position: absolute;
        /* Điều chỉnh vị trí cho menu con: bottom 0 thay vì 5px */
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

    /* Đảm bảo gạch chân của menu chính vẫn nằm ở vị trí 5px */
    .nav-link::after {
        bottom: 5px;
    }


    /* 2. DROPDOWN (Luôn nổi lên trên) */
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
        z-index: 999999;
        background: #ffffff;
    }

    .group {
        position: relative;
    }

    .group:hover .nav-dropdown-room,
    .group:hover .nav-dropdown-pages,
    .group:hover .nav-dropdown-user {
        max-height: 350px;
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        transform: translateY(0);
    }

    /* 3. MAIN NAV (Trong Suốt Mặc Định XÁM & Đổi Màu Khi Cuộn) */
    #mainNav {
        position: fixed;
        top: 0;
        width: 100%;
        /* Mặc định: Màu xám mờ (Gray 700, Opacity 50%) */
        background-color: rgba(55, 65, 81, 0.5); 
        box-shadow: none;
        z-index: 999990;
        transition: background-color .35s ease;
    }

    /* Khi cuộn, áp dụng màu nền xám đậm hơn (Gray 800, Opacity 85%) */
    #mainNav.scrolled {
        background-color: rgba(31, 41, 55, 0.85); 
    }

    /* Điều chỉnh màu chữ mặc định trong Nav thành trắng */
    #mainNav .nav-link,
    #mainNav .nav-logo {
        color: #ffffff !important;
        font-weight: 500;
        transition: color .2s ease;
    }

    /* Điều chỉnh màu hover (vàng) */
    #mainNav .nav-link:hover,
    #mainNav .nav-link:hover .fa-chevron-down,
    #mainNav .nav-user-button:hover span,
    #mainNav .nav-user-button:hover .fa-chevron-down {
        color: #d4af37 !important;
    }

    /* Chỉnh màu chữ trong nút user auth */
    #mainNav .nav-user-button span,
    #mainNav .nav-user-button .fa-chevron-down {
        color: #ffffff;
    }


    /* Chiều cao nav container */
    #navContainer {
        height: 90px;
        transition: height .28s ease;
    }

    /* Khi scroll thu gọn nav container */
    #navContainer.scrolled {
        height: 75px;
    }

    /* Xóa padding-top khỏi body để banner/ảnh tràn lên */
    body {
        padding-top: 0 !important;
    }
</style>

<script>
    const nav = document.getElementById("mainNav");
    const container = document.getElementById("navContainer");

    window.addEventListener("scroll", () => {
        // Nav bắt đầu chuyển đổi sau khi cuộn qua 120px
        if (window.scrollY > 120) {
            // Đổi màu nền của #mainNav
            nav.classList.add("scrolled");
            // Thu gọn chiều cao của #navContainer
            container.classList.add("scrolled");
        } else {
            // Trả về màu nền trong suốt mặc định của #mainNav
            nav.classList.remove("scrolled");
            // Trả về chiều cao mặc định của #navContainer
            container.classList.remove("scrolled");
        }
    });
</script>
