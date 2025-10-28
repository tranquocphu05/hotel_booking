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

    <style>

        html {
            font-size: 90%;
        }

        /* Swiper Custom Styles */
        .weekendDealsSwiper {
            padding-bottom: 20px !important;
        }

        /* Ẩn navigation buttons */
        .weekendDealsSwiper .swiper-button-next,
        .weekendDealsSwiper .swiper-button-prev {
            display: none !important;
        }

        /* Ẩn pagination */
        .weekendDealsSwiper .swiper-pagination {
            display: none !important;
        }

        body {
            /* Ưu tiên các font hỗ trợ Unicode/Tiếng Việt tốt */
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji",
                "Times New Roman";
            height: auto !important;
            overflow-y: visible !important;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

{{-- LOẠI BỎ 'text-sm' ở đây vì đã điều chỉnh font-size trên 'html' --}}

<body class="bg-gray-100 text-gray-800">
    @include('partials.nav')

    {{-- Global Success Toast --}}
    {{-- GIỮ NGUYÊN hoặc điều chỉnh nhẹ nhàng khoảng cách nếu cần --}}
    @if (session('success'))
        <div class="fixed top-16 right-4 z-50 max-w-md animate-slide-in-right" id="successToast">
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-4 rounded-lg shadow-2xl">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-check-circle text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="font-bold text-lg mb-1">Thành công!</h3>
                        <p class="text-sm text-white/90">{{ session('success') }}</p>
                    </div>
                    <button onclick="closeToast()" class="ml-4 text-white/80 hover:text-white transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        <script>
            function closeToast() {
                const toast = document.getElementById('successToast');
                if (toast) {
                    toast.classList.add('animate-slide-out-right');
                    setTimeout(() => {
                        toast.remove();
                    }, 300);
                }
            }

            // Auto close after 5 seconds
            setTimeout(() => {
                closeToast();
            }, 5000);
        </script>
    @endif

    {{-- ============================================================= --}}
    {{-- 1. full-width header slot (Hero Banner) --}}
    @hasSection('fullwidth_header')
        @yield('fullwidth_header')
    @endif

    {{-- 2. KHỐI FULL-WIDTH TỪ CÁC FILE CON (Rooms Gallery) --}}
    @stack('fullwidth_content')
    {{-- ============================================================= --}}

    {{-- 3. KHỐI NỘI DUNG CHÍNH (Chứa @yield('content')) --}}
    <div
        class="@hasSection('boxed')
{{-- Giữ nguyên hoặc điều chỉnh thêm nếu bạn muốn thu hẹp tối đa chiều rộng nội dung --}}
        mx-auto px-4 sm:px-6 lg:px-8 mt-0
@else
w-full px-0 mt-0
@endif">
        @yield('content')
    </div>


    {{-- 4. full-width footer slot (optional) --}}
    @hasSection('fullwidth_footer')
        @yield('fullwidth_footer')
    @endif

    @unless (View::hasSection('hideGlobalFooter'))
        @include('client.footer.footer')
    @endunless

    {{-- Swiper JS - Load before other scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    {{-- Lazy load Chart.js only when needed --}}
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- TinyMCE CDN --}}
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

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
