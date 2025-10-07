<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-gray-100 text-gray-800">
    @include('partials.nav')

    @php
        // If child defines a 'fullwidth' section, use full width container.
    @endphp

    {{-- full-width header slot (optional) --}}
    @hasSection('fullwidth_header')
        @yield('fullwidth_header')
    @endif

    <div class="@hasSection('fullwidth') w-full px-0 mt-6 @else max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 @endif">
        @yield('content')
    </div>

    {{-- full-width footer slot (optional) --}}
    @hasSection('fullwidth_footer')
        @yield('fullwidth_footer')
    @endif

    @unless(View::hasSection('hideGlobalFooter'))
        @include('partials.footer')
    @endunless

    <!-- Load Chart.js from CDN for dashboards -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @stack('scripts')

    <script>
        // Coordinate mobile sidebar slide-in/out and aria state
        document.addEventListener('DOMContentLoaded', function () {
            var btn = document.getElementById('admin-menu-toggle');
            var sidebar = document.getElementById('admin-sidebar') || document.querySelector('aside.w-64');
            if (btn && sidebar) {
                btn.addEventListener('click', function (e) {
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