<!doctype html>
{{-- THAY ĐỔI: Đảm bảo lang là 'vi' để hỗ trợ tiếng Việt tốt nhất --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) ?? 'vi' }}">

<head>
    {{-- ĐÃ KIỂM TRA: Khai báo UTF-8 đã chính xác, là bước quan trọng nhất --}}
    <meta charset="utf-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- Preconnect to external domains for faster loading --}}
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">

    <title>@yield('title', config('app.name'))</title>
    
    {{-- Font Awesome - Load async để không block rendering --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" media="print" onload="this.media='all'" />
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    </noscript>
    
    {{-- Swiper CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    
    {{-- THÊM CSS ĐỂ ƯU TIÊN FONT HỖ TRỢ TIẾNG VIỆT (Tùy chọn) --}}
    {{-- Nếu bạn dùng Tailwind, bạy có thể thiết lập điều này trong file CSS gốc --}}
    <style>
        /* Swiper Custom Styles */
        .weekendDealsSwiper {
            padding-bottom: 60px !important;
        }
        
        .weekendDealsSwiper .swiper-button-next,
        .weekendDealsSwiper .swiper-button-prev {
            background-color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .weekendDealsSwiper .swiper-button-next:hover,
        .weekendDealsSwiper .swiper-button-prev:hover {
            background-color: #ef4444;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
        }
        
        .weekendDealsSwiper .swiper-button-next:hover::after,
        .weekendDealsSwiper .swiper-button-prev:hover::after {
            color: white;
        }
        
        .weekendDealsSwiper .swiper-button-next::after,
        .weekendDealsSwiper .swiper-button-prev::after {
            font-size: 16px;
            font-weight: bold;
            color: #374151;
        }
        
        .weekendDealsSwiper .swiper-pagination-bullet {
            width: 10px;
            height: 10px;
            background: #9ca3af;
            opacity: 1;
        }
        
        .weekendDealsSwiper .swiper-pagination-bullet-active {
            background: #ef4444;
            width: 24px;
            border-radius: 5px;
        }
        
        body {
            /* Ưu tiên các font hỗ trợ Unicode/Tiếng Việt tốt */
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji", 
                        /* Thêm một font Tiếng Việt phổ biến, ví dụ: 'Times New Roman' cho font serif */
                        "Times New Roman";
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="min-h-screen bg-gray-100 text-gray-800">
    @include('partials.nav')

    {{-- ============================================================= --}}
    {{-- 1. full-width header slot (Hero Banner) --}}
    @hasSection('fullwidth_header')
        @yield('fullwidth_header')
    @endif
    
    {{-- 2. KHỐI FULL-WIDTH TỪ CÁC FILE CON (Rooms Gallery) --}}
    @stack('fullwidth_content') 
    {{-- ============================================================= --}}

    {{-- 3. KHỐI NỘI DUNG CHÍNH (Chứa @yield('content')) --}}
    <div class="@hasSection('fullwidth')
w-full px-0 mt-6
@else
 mx-auto px-4 sm:px-6 lg:px-8 mt-6
@endif">
        @yield('content')
    </div>

    {{-- 4. full-width footer slot (optional) --}}
    @hasSection('fullwidth_footer')
        @yield('fullwidth_footer')
    @endif

    @unless (View::hasSection('hideGlobalFooter'))
        @include('partials.footer')
    @endunless

    {{-- Swiper JS - Load before other scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    {{-- Lazy load Chart.js only when needed --}}
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @stack('scripts')

    <script>
        // Coordinate mobile sidebar slide-in/out and aria state
        document.addEventListener('DOMContentLoaded', function() {
            var btn = document.getElementById('admin-menu-toggle');
            var sidebar = document.getElementById('admin-sidebar') || document.querySelector('aside.w-64');
            if (btn && sidebar) {
                btn.addEventListener('click', function(e) {
                    var expanded = btn.getAttribute('aria-expanded') === 'true';
                    btn.setAttribute('aria-expanded', (!expanded).toString());
                    // toggle transform class for slide-in
                    sidebar.classList.toggle('-translate-x-full');
                    var backdrop = document.getElementById('admin-backdrop');
                    if (backdrop) {
                        if (sidebar.classList.contains('-translate-x-full')) {
                            backdrop.classList.add('hidden');
                        } else {
                            backdrop.classList.remove('hidden');
                        }
                    }
                });
            }

            // Respect prefers-reduced-motion: reduce animations if set
            var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (reduce) {
                document.documentElement.classList.add('reduce-motion');
            }
        });
    </script>
</body>

</html>