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
                            class="nav-dropdown-user absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-[9999999]">
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

    /* HIỆU ỨNG SCROLL (CẦN CSS TÙY CHỈNH) */
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
    }

    // Gán sự kiện cuộn
    window.addEventListener("scroll", handleScroll);

    // Chạy hàm kiểm tra ngay khi tải trang để xử lý trường hợp refresh ở vị trí đã cuộn
    document.addEventListener('DOMContentLoaded', handleScroll);
</script>