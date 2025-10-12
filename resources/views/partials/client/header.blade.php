<header class="mb-6 sticky top-0 z-30 bg-white shadow-sm">
    <!-- Preloader -->
    <div id="preloder" class="hidden">
        <div class="loader"></div>
    </div>

    <!-- Main Header -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded p-4 flex items-center justify-between">
            <!-- Left Section: Menu Toggle & Logo -->
            <div class="flex items-center gap-4">
                <!-- Mobile Menu Toggle -->
                <button 
                    id="admin-menu-toggle" 
                    aria-controls="admin-sidebar" 
                    aria-expanded="false" 
                    class="md:hidden p-2 rounded hover:bg-gray-100 transition-colors duration-150"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <h2 class="text-lg font-semibold">TailAdmin</h2>
                    <span class="text-sm text-gray-500">Admin</span>
                </div>
            </div>

            <!-- Center Section: Search Bar (Hidden on mobile) -->
            <div class="hidden lg:flex flex-1 mx-6">
                <div class="max-w-3xl mx-auto w-full">
                    <div class="relative">
                        <input 
                            type="search" 
                            placeholder="Search or type command..." 
                            class="w-full rounded-lg border bg-gray-50 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
                        />
                        <button class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.9 14.32a8 8 0 111.414-1.414l4.387 4.386-1.414 1.415-4.387-4.387zM10 16a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Section: Actions & User -->
            <div class="flex items-center gap-4">
                <!-- Dark Mode Toggle -->
                <button class="p-2 rounded hover:bg-gray-100 transition-colors" title="Toggle dark mode">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zM4.22 5.22a1 1 0 011.415 0l.707.708a1 1 0 11-1.414 1.414L4.22 6.636a1 1 0 010-1.415zM2 10a1 1 0 011-1h1a1 1 0 110 2H3a1 1 0 01-1-1zM5.636 15.364a1 1 0 010-1.415l.707-.707a1 1 0 111.414 1.414l-.707.707a1 1 0 01-1.414 0zM10 16a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1z" />
                    </svg>
                </button>

                <!-- Language Selector (Hidden on small screens) -->
                <div class="hidden sm:flex items-center gap-2 relative group">
                    <img src="img/flag.jpg" alt="Language" class="h-5 w-5 rounded" />
                    <span class="text-sm text-gray-700 cursor-pointer">EN 
                        <svg class="inline h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    <div class="hidden group-hover:block absolute top-full right-0 mt-2 bg-white shadow-lg rounded border py-1 min-w-[80px]">
                        <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100">Zi</a>
                        <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100">Fr</a>
                    </div>
                </div>

                <!-- Booking Button (Hidden on mobile) -->
                <a href="#" class="hidden md:inline-block bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition-colors text-sm font-medium">
                    Booking Now
                </a>

                <!-- User Profile -->
                <div class="flex items-center gap-3">
                    <img src="https://i.pravatar.cc/40" alt="avatar" class="h-8 w-8 rounded-full border" />
                    <div class="hidden sm:block text-sm text-gray-700">
                        {{ auth()->user()?->ho_ten ?? auth()->user()?->username ?? 'Admin' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Search Bar -->
    <div class="lg:hidden px-4 pb-4">
        <div class="relative">
            <input 
                type="search" 
                placeholder="Search..." 
                class="w-full rounded-lg border bg-gray-50 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
            />
            <button class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.9 14.32a8 8 0 111.414-1.414l4.387 4.386-1.414 1.415-4.387-4.387zM10 16a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
</header>

<!-- Offcanvas Menu Overlay -->
<div id="offcanvas-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity"></div>

<!-- Offcanvas Menu -->
<div id="offcanvas-menu" class="fixed top-0 right-0 h-full w-80 bg-white shadow-xl z-50 transform translate-x-full transition-transform duration-300">
    <!-- Close Button -->
    <button id="canvas-close" class="absolute top-4 right-4 p-2 hover:bg-gray-100 rounded">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <!-- Menu Content -->
    <div class="p-6 pt-16">
        <!-- Search Icon -->
        <div class="mb-6">
            <button class="flex items-center gap-2 text-gray-700 hover:text-indigo-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span>Search</span>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="mb-6">
            <ul class="space-y-2">
                <li><a href="./index.html" class="block py-2 px-4 rounded hover:bg-gray-100 text-indigo-600 font-medium">Home</a></li>
                <li><a href="./rooms.html" class="block py-2 px-4 rounded hover:bg-gray-100">Rooms</a></li>
                <li><a href="./about-us.html" class="block py-2 px-4 rounded hover:bg-gray-100">About Us</a></li>
                <li>
                    <details class="group">
                        <summary class="py-2 px-4 rounded hover:bg-gray-100 cursor-pointer list-none flex items-center justify-between">
                            <span>Pages</span>
                            <svg class="h-4 w-4 group-open:rotate-180 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </summary>
                        <ul class="ml-4 mt-2 space-y-1">
                            <li><a href="./room-details.html" class="block py-2 px-4 rounded hover:bg-gray-100 text-sm">Room Details</a></li>
                            <li><a href="#" class="block py-2 px-4 rounded hover:bg-gray-100 text-sm">Deluxe Room</a></li>
                            <li><a href="#" class="block py-2 px-4 rounded hover:bg-gray-100 text-sm">Family Room</a></li>
                            <li><a href="#" class="block py-2 px-4 rounded hover:bg-gray-100 text-sm">Premium Room</a></li>
                        </ul>
                    </details>
                </li>
                <li><a href="./blog.html" class="block py-2 px-4 rounded hover:bg-gray-100">News</a></li>
                <li><a href="./contact.html" class="block py-2 px-4 rounded hover:bg-gray-100">Contact</a></li>
            </ul>
        </nav>

        <!-- Booking Button -->
        <div class="mb-6">
            <a href="#" class="block w-full bg-indigo-600 text-white text-center px-4 py-3 rounded hover:bg-indigo-700 transition-colors font-medium">
                Booking Now
            </a>
        </div>

        <!-- Social Links -->
        <div class="flex gap-4 mb-6 justify-center">
            <a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
            <a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
            </a>
            <a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/></svg>
            </a>
        </div>

        <!-- Contact Info -->
        <ul class="space-y-3 text-sm text-gray-600">
            <li class="flex items-center gap-2">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                </svg>
                <span>(12) 345 67890</span>
            </li>
            <li class="flex items-center gap-2">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                </svg>
                <span>info.colorlib@gmail.com</span>
            </li>
        </ul>
    </div>
</div>

<script>
    // Toggle offcanvas menu
    const menuToggle = document.getElementById('admin-menu-toggle');
    const offcanvasMenu = document.getElementById('offcanvas-menu');
    const offcanvasOverlay = document.getElementById('offcanvas-overlay');
    const canvasClose = document.getElementById('canvas-close');

    function openMenu() {
        offcanvasMenu.classList.remove('translate-x-full');
        offcanvasOverlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        offcanvasMenu.classList.add('translate-x-full');
        offcanvasOverlay.classList.add('hidden');
        document.body.style.overflow = '';
    }

    menuToggle?.addEventListener('click', openMenu);
    canvasClose?.addEventListener('click', closeMenu);
    offcanvasOverlay?.addEventListener('click', closeMenu);
</script>