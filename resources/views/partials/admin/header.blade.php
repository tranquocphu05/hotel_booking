<!-- Top navigation -->
<header class="fixed top-0 left-0 right-0 lg:left-64 z-40 bg-white/95 backdrop-blur shadow-sm border-b border-gray-200">
    <style>
        /* Bảo đảm ẩn dropdown thông báo theo mặc định */
        #notifDropdown{display:none !important;}
    </style>
    <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
        <!-- Mobile menu button -->
        <button id="sidebar-toggle" type="button" class="-m-2.5 p-2.5 text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all duration-300 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <span class="sr-only">Toggle sidebar</span>
            <!-- Hamburger icon -->
            <svg id="hamburger-icon" class="h-6 w-6 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
            <!-- Close icon -->
            <svg id="close-icon" class="h-6 w-6 transition-transform duration-300 hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Search -->
        <div class="flex flex-1 items-center justify-center px-2 lg:ml-6 lg:justify-end">
            <div class="w-full max-w-lg lg:max-w-xs">
                <label for="search" class="sr-only">Search</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input id="search" name="search" class="block w-full rounded-md border-0 bg-white py-1.5 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Tìm kiếm..." type="search">
                </div>
            </div>
        </div>

        <!-- Right side -->
        <div class="flex items-center gap-6">
            <!-- Dark Mode Toggle -->
            <button id="dark-mode-toggle" type="button" class="relative p-2 text-gray-400 hover:text-gray-500 transition-all duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-full dark-mode-toggle">
                <span class="sr-only">Toggle dark mode</span>
                <!-- Sun icon (light mode) -->
                <svg id="sun-icon" class="h-6 w-6 transition-all duration-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                </svg>
                <!-- Moon icon (dark mode) -->
                <svg id="moon-icon" class="h-6 w-6 transition-all duration-300 hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                </svg>
            </button>

            <!-- Notifications -->
            <div class="relative">
            <button type="button" id="notifToggle" class="relative p-2 text-gray-400 hover:text-gray-500 transition-all duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-full notification-bell">
                <span class="sr-only">View notifications</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                @if(($pendingBookingCount ?? 0) > 0)
                    <span class="absolute -top-2 -right-2 min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] leading-[18px] text-center font-semibold shadow">
                        {{ $pendingBookingCount > 99 ? '99+' : $pendingBookingCount }}
                    </span>
                @endif
            </button>

            {{-- Dropdown chi tiết thông báo --}}
            <div id="notifDropdown" class="absolute right-0 mt-2 w-80 bg-white border border-gray-100 rounded-xl shadow-xl z-50 transition-all duration-200" style="display:none;">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <div class="text-sm font-semibold text-gray-800">Thông báo đặt phòng</div>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700">{{ $pendingBookingCount ?? 0 }} chờ</span>
                </div>
                <div class="max-h-80 overflow-auto">
                    @forelse(($pendingRecentBookings ?? []) as $bk)
                        <a href="{{ route('admin.dat_phong.show', data_get($bk,'id')) }}" class="block px-4 py-3 hover:bg-gray-50">
                            <div class="text-sm text-gray-800 font-medium">{{ data_get($bk,'username','Khách') }} - {{ data_get($bk,'loaiPhong.ten_loai','Loại phòng') }}</div>
                            <div class="text-xs text-gray-500">Đặt lúc {{ \Carbon\Carbon::parse(data_get($bk,'ngay_dat'))->format('d/m/Y H:i') }}</div>
                        </a>
                    @empty
                        <div class="px-4 py-6 text-center text-sm text-gray-500">Không có đơn chờ xác nhận</div>
                    @endforelse
                </div>
                <div class="px-4 py-2 border-t border-gray-100 text-right">
                    <a href="{{ route('admin.dat_phong.index', ['status' => 'cho_xac_nhan']) }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">Xem tất cả</a>
                </div>
            </div>
            </div>

            <!-- Profile dropdown -->
            <div class="relative">
                <button type="button" class="admin-profile-button flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                    <span class="sr-only">Open user menu</span>
                    <div class="admin-avatar h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-300 hover:rotate-12">
                        <i class="fas fa-user-shield text-white text-sm"></i>
                    </div>
                    <span class="hidden sm:block">{{ auth()->user()->ten ?? 'Admin' }}</span>
                    <svg class="dropdown-arrow h-4 w-4 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>

                <!-- Dropdown menu -->
                <div class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none hidden" id="user-menu" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button">
                    <div class="px-4 py-2 border-b border-gray-100">
                        <p class="text-sm font-medium text-gray-900">{{ auth()->user()->ten ?? 'Admin' }}</p>
                        <p class="text-xs text-gray-500">Administrator</p>
                    </div>
                    
                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                    </a>
                    <a href="{{ route('client.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                        <i class="fas fa-home mr-2"></i>Về Website
                    </a>
                    <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                        <i class="fas fa-user mr-2"></i>Profile
                    </a>
                    
                    <div class="border-t border-gray-100 my-1"></div>
                    
                    <form method="POST" action="{{ route('logout') }}" class="block">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50" role="menuitem">
                            <i class="fas fa-sign-out-alt mr-2"></i>Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userMenuButton = document.getElementById('user-menu-button');
    const userMenu = document.getElementById('user-menu');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const hamburgerIcon = document.getElementById('hamburger-icon');
    const closeIcon = document.getElementById('close-icon');
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    
    // User menu dropdown
    if (userMenuButton && userMenu) {
        // Ensure dropdown is hidden by default
        userMenu.classList.remove('show');
        
        userMenuButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (userMenu.classList.contains('show')) {
                userMenu.classList.remove('show');
            } else {
                userMenu.classList.add('show');
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!userMenuButton.contains(event.target) && !userMenu.contains(event.target)) {
                userMenu.classList.remove('show');
            }
        });
        
        // Close dropdown when pressing Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                userMenu.classList.remove('show');
            }
        });
    }
    
    // Dark mode toggle functionality
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    const sunIcon = document.getElementById('sun-icon');
    const moonIcon = document.getElementById('moon-icon');
    const body = document.body;
    
    // Check for saved theme preference or default to light mode
    const currentTheme = localStorage.getItem('theme') || 'light';
    
    // Apply theme on page load
    if (currentTheme === 'dark') {
        body.classList.add('dark');
        sunIcon.classList.add('hidden');
        moonIcon.classList.remove('hidden');
    } else {
        body.classList.remove('dark');
        sunIcon.classList.remove('hidden');
        moonIcon.classList.add('hidden');
    }
    
    // Dark mode toggle event listener
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (body.classList.contains('dark')) {
                // Switch to light mode
                body.classList.remove('dark');
                sunIcon.classList.remove('hidden');
                moonIcon.classList.add('hidden');
                localStorage.setItem('theme', 'light');
            } else {
                // Switch to dark mode
                body.classList.add('dark');
                sunIcon.classList.add('hidden');
                moonIcon.classList.remove('hidden');
                localStorage.setItem('theme', 'dark');
            }
        });
    }
    
    // Sidebar toggle functionality
    if (sidebarToggle && sidebar) {
        let sidebarOpen = true; // Sidebar mở mặc định
        
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            sidebarOpen = !sidebarOpen;
            
            if (sidebarOpen) {
                // Show sidebar (full width)
                sidebar.classList.remove('show');
                
                // Hide backdrop
                if (backdrop) {
                    backdrop.classList.add('hidden');
                    backdrop.classList.remove('block');
                }
                
                // Change icon to hamburger (sidebar visible)
                hamburgerIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
                
                // Remove animation
                sidebarToggle.classList.remove('rotate-90');
                
            } else {
                // Collapse sidebar (icon only)
                sidebar.classList.add('show');
                
                // Show backdrop
                if (backdrop) {
                    backdrop.classList.remove('hidden');
                    backdrop.classList.add('block');
                }
                
                // Change icon to X (sidebar collapsed)
                hamburgerIcon.classList.add('hidden');
                closeIcon.classList.remove('hidden');
                
                // Add animation
                sidebarToggle.classList.add('rotate-90');
            }
        });
        
        // Close sidebar when clicking backdrop
        if (backdrop) {
            backdrop.addEventListener('click', function() {
                if (!sidebarOpen) {
                    sidebarToggle.click();
                }
            });
        }
        
        // Close sidebar on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && !sidebarOpen) {
                sidebarToggle.click();
            }
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (!sidebarOpen && !sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                sidebarToggle.click();
            }
        });
    }
});
</script>
@push('scripts')
<script>
  (function(){
    const toggleBtn = document.getElementById('notifToggle');
    const dropdown = document.getElementById('notifDropdown');
    if(!toggleBtn || !dropdown) return;

    function openDropdown(){ dropdown.style.display = 'block'; }
    function closeDropdown(){ dropdown.style.display = 'none'; }
    function toggle(){
      if (dropdown.style.display === 'none' || dropdown.style.display === '') openDropdown(); else closeDropdown();
    }

    toggleBtn.addEventListener('click', function(e){
      e.stopPropagation();
      toggle();
    });
    document.addEventListener('click', function(e){
      if (!dropdown.contains(e.target) && e.target !== toggleBtn) closeDropdown();
    });
  })();
</script>
@endpush