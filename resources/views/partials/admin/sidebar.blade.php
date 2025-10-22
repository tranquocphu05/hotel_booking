<!-- Sidebar -->
<div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0">
    <div class="flex h-full flex-col">
        <!-- Logo -->
        <div class="flex h-20 shrink-0 items-center px-6 border-b border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0 sidebar-icon">
                    <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-hotel text-white text-lg"></i>
                    </div>
                </div>
                <div class="ml-3 logo-text">
                    <h1 class="text-2xl font-bold text-gray-900">OZIA Hotel</h1>
                    <p class="text-sm text-gray-500">Admin Panel</p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
            <!-- Dashboard -->
            <a href="{{ route('admin.dashboard') }}" 
               class="group flex items-center px-4 py-3 text-base font-medium rounded-md {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-tachometer-alt sidebar-icon mr-4 text-gray-400 group-hover:text-gray-500 text-lg"></i>
                <span class="sidebar-text">Dashboard</span>
            </a>

            <!-- Rooms Management -->
            <div class="space-y-1">
                <div class="px-4 py-3 group-header">
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Quản lý phòng</p>
                </div>
                <a href="{{ route('admin.loai_phong.index') }}" 
                   class="group flex items-center px-4 py-3 text-base font-medium rounded-md {{ request()->routeIs('admin.loai_phong.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fas fa-layer-group sidebar-icon mr-4 text-gray-400 group-hover:text-gray-500 text-lg"></i>
                    <span class="sidebar-text">Loại phòng</span>
                </a>
                <a href="{{ route('admin.phong.index') }}" 
                   class="group flex items-center px-4 py-3 text-base font-medium rounded-md {{ request()->routeIs('admin.phong.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fas fa-bed sidebar-icon mr-4 text-gray-400 group-hover:text-gray-500 text-lg"></i>
                    <span class="sidebar-text">Phòng</span>
                </a>
            </div>

            <!-- Bookings & Revenue -->
            <div class="space-y-1">
                <div class="px-4 py-3 group-header">
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Đặt phòng & Doanh thu</p>
                </div>
                <a href="{{ route('admin.dat_phong.index') }}" 
                   class="group flex items-center px-4 py-3 text-base font-medium rounded-md {{ request()->routeIs('admin.dat_phong.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fas fa-calendar-check sidebar-icon mr-4 text-gray-400 group-hover:text-gray-500 text-lg"></i>
                    <span class="sidebar-text">Đặt phòng</span>
                </a>
                <a href="{{ route('admin.invoices.index') }}" 
                   class="group flex items-center px-4 py-3 text-base font-medium rounded-md {{ request()->routeIs('admin.invoices.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fas fa-file-invoice sidebar-icon mr-4 text-gray-400 group-hover:text-gray-500 text-lg"></i>
                    <span class="sidebar-text">Hóa đơn</span>
                </a>
                <a href="{{ route('admin.revenue') }}" 
                   class="group flex items-center px-4 py-3 text-base font-medium rounded-md {{ request()->routeIs('admin.revenue') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fas fa-chart-line sidebar-icon mr-4 text-gray-400 group-hover:text-gray-500 text-lg"></i>
                    <span class="sidebar-text">Chi tiết doanh thu</span>
                </a>
            </div>

            <!-- Customer Management -->
            <div class="space-y-1">
                <div class="px-4 py-3 group-header">
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Khách hàng</p>
                </div>
                <a href="{{ route('admin.users.index') }}" 
                   class="group flex items-center px-4 py-3 text-base font-medium rounded-md {{ request()->routeIs('admin.users.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fas fa-users sidebar-icon mr-4 text-gray-400 group-hover:text-gray-500 text-lg"></i>
                    <span class="sidebar-text">Khách hàng</span>
                </a>
                <a href="{{ route('admin.reviews.index') }}" 
                   class="group flex items-center px-4 py-3 text-base font-medium rounded-md {{ request()->routeIs('admin.reviews.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fas fa-star sidebar-icon mr-4 text-gray-400 group-hover:text-gray-500 text-lg"></i>
                    <span class="sidebar-text">Đánh giá</span>
                </a>
            </div>

            <!-- Promotions -->
            <div class="space-y-1">
                <div class="px-4 py-3 group-header">
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Khuyến mãi</p>
                </div>
                <a href="{{ route('admin.voucher.index') }}" 
                   class="group flex items-center px-4 py-3 text-base font-medium rounded-md {{ request()->routeIs('admin.voucher.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fas fa-ticket-alt sidebar-icon mr-4 text-gray-400 group-hover:text-gray-500 text-lg"></i>
                    <span class="sidebar-text">Voucher</span>
                </a>
            </div>

            <!-- Content Management -->
            <div class="space-y-1">
                <div class="px-4 py-3 group-header">
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Quản lý nội dung</p>
                </div>
                <a href="{{ route('admin.news.index') }}" 
                   class="group flex items-center px-4 py-3 text-base font-medium rounded-md {{ request()->routeIs('admin.news.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fas fa-newspaper sidebar-icon mr-4 text-gray-400 group-hover:text-gray-500 text-lg"></i>
                    <span class="sidebar-text">Tin tức</span>
                </a>
            </div>
        </nav>

        <!-- User section -->
        <div class="border-t border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 sidebar-icon">
                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-shield text-indigo-600 text-lg"></i>
                    </div>
                </div>
                <div class="ml-3 user-text">
                    <p class="text-base font-medium text-gray-900">{{ auth()->user()->ten ?? 'Admin' }}</p>
                    <p class="text-sm text-gray-500">Administrator</p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 
<aside class="bg-white shadow rounded p-4">
    <h3 class="font-semibold mb-3">Admin Menu</h3>
    <ul class="space-y-2 text-sm">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:underline">Dashboard</a></li>
        <li><a href="{{ route('admin.dat_phong.index') }}" class="hover:underline {{ request()->routeIs('admin.dat_phong.*') ? 'bg-indigo-50' : '' }}">Đặt phòng</a></li>
        <li><a href="{{ route('admin.loai_phong.index') }}" class="hover:underline">Loại phòng</a></li>
        <li><a href="{{ route('admin.users.index') }}" class="hover:underline">Users</a></li>
        <li><a href="{{ route('register') }}" class="hover:underline">Register</a></li>
        <li><a href="#" class="hover:underline">Bookings</a></li>
        <li><a href="{{ route('admin.voucher.index') }}" class="hover:underline">Vouchers</a></li>
    </ul>

</aside> -->
