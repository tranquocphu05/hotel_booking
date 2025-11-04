<div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 transition-all duration-300">
    <style>
        /* CSS tuỳ chỉnh cho Sidebar khi thu gọn (chế độ Icon-Only) */
        @media (min-width: 1024px) { /* Áp dụng cho desktop */
            #sidebar.sidebar-collapsed {
                width: 5rem; /* Chiều rộng thu gọn (80px) */
            }

            /* Ẩn chữ và tiêu đề nhóm mượt mà */
            #sidebar.sidebar-collapsed .sidebar-text,
            #sidebar.sidebar-collapsed .logo-text,
            #sidebar.sidebar-collapsed .group-header,
            #sidebar.sidebar-collapsed .user-text {
                display: none;
                opacity: 0;
            }

            /* Căn giữa icon logo khi thu gọn */
            #sidebar.sidebar-collapsed .flex.h-20.shrink-0.items-center {
                justify-content: center;
                padding-left: 0;
                padding-right: 0;
            }

            /* Đảm bảo menu item chỉ hiển thị icon và căn giữa */
            #sidebar.sidebar-collapsed a.group.flex.items-center {
                /* Thiết lập lại căn chỉnh ngang cho menu item */
                justify-content: center;
                padding-left: 0;
                padding-right: 0;
                /* Căn icon: Đặt margin icon về 0 và icon chiếm toàn bộ chiều rộng */
            }
            #sidebar.sidebar-collapsed a.group.flex.items-center .sidebar-icon {
                margin-right: 0;
            }

            /* Căn giữa phần User/Admin dưới cùng */
            #sidebar.sidebar-collapsed .border-t.border-gray-200.p-4 {
                padding: 1rem 0; /* Giảm padding dọc */
                display: flex;
                justify-content: center; /* Căn giữa ngang */
            }
            #sidebar.sidebar-collapsed .border-t.border-gray-200.p-4 .flex.items-center {
                justify-content: center;
            }
        }
    </style>
    <div class="flex h-full flex-col">
        <div class="flex h-20 shrink-0 items-center px-6 border-b border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0 sidebar-icon">
                    <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-hotel text-white text-lg"></i>
                    </div>
                </div>
                <div class="ml-3 logo-text transition-all duration-300">
                    <h1 class="text-2xl font-bold text-gray-900">OZIA Hotel</h1>
                    <p class="text-sm text-gray-500">Admin Panel</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" 
                class="group flex items-center px-4 py-3 text-base font-medium rounded-md {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-tachometer-alt sidebar-icon mr-4 text-gray-400 group-hover:text-gray-500 text-lg"></i>
                <span class="sidebar-text transition-all duration-300">Dashboard</span>
            </a>

            <div class="space-y-1">
                <div class="px-4 py-3 group-header transition-all duration-300">
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Quản lý phòng</p>
                </div>
                <a href="{{ route('admin.loai_phong.index') }}" 
                    class="group flex items-center px-4 py-3 text-base font-medium rounded-md {{ request()->routeIs('admin.loai_phong.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fas fa-layer-group sidebar-icon mr-4 text-gray-400 group-hover:text-gray-500 text-lg"></i>
                    <span class="sidebar-text transition-all duration-300">Loại phòng</span>
                </a>
            </div>

            <div class="space-y-1">
                <div class="px-4 py-3 group-header transition-all duration-300">
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Đặt phòng & Doanh thu</p>
                </div>
                <a href="{{ route('admin.dat_phong.index') }}" 
                    class="group flex items-center px-4 py-3 text-base font-medium rounded-md {{ request()->routeIs('admin.dat_phong.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fas fa-calendar-check sidebar-icon mr-4 text-gray-400 group-hover:text-gray-500 text-lg"></i>
                    <span class="sidebar-text transition-all duration-300">Đặt phòng</span>
                </a>
                <a href="{{ route('admin.invoices.index') }}" 
                    class="group flex items-center px-4 py-3 text-base font-medium rounded-md {{ request()->routeIs('admin.invoices.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fas fa-file-invoice sidebar-icon mr-4 text-gray-400 group-hover:text-gray-500 text-lg"></i>
                    <span class="sidebar-text transition-all duration-300">Hóa đơn</span>
                </a>
                <a href="{{ route('admin.revenue') }}" 
                    class="group flex items-center px-4 py-3 text-base font-medium rounded-md {{ request()->routeIs('admin.revenue') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fas fa-chart-line sidebar-icon mr-4 text-gray-400 group-hover:text-gray-500 text-lg"></i>
                    <span class="sidebar-text transition-all duration-300">Chi tiết doanh thu</span>
                </a>
            </div>

            <div class="space-y-1">
                <div class="px-4 py-3 group-header transition-all duration-300">
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Khách hàng</p>
                </div>
                <a href="{{ route('admin.users.index') }}" 
                    class="group flex items-center px-4 py-3 text-base font-medium rounded-md {{ request()->routeIs('admin.users.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fas fa-users sidebar-icon mr-4 text-gray-400 group-hover:text-gray-500 text-lg"></i>
                    <span class="sidebar-text transition-all duration-300">Khách hàng</span>
                </a>
                <a href="{{ route('admin.reviews.index') }}" 
                    class="group flex items-center px-4 py-3 text-base font-medium rounded-md {{ request()->routeIs('admin.reviews.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fas fa-star sidebar-icon mr-4 text-gray-400 group-hover:text-gray-500 text-lg"></i>
                    <span class="sidebar-text transition-all duration-300">Đánh giá</span>
                </a>
            </div>

            <div class="space-y-1">
                <div class="px-4 py-3 group-header transition-all duration-300">
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Khuyến mãi</p>
                </div>
                <a href="{{ route('admin.voucher.index') }}" 
                    class="group flex items-center px-4 py-3 text-base font-medium rounded-md {{ request()->routeIs('admin.voucher.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fas fa-ticket-alt sidebar-icon mr-4 text-gray-400 group-hover:text-gray-500 text-lg"></i>
                    <span class="sidebar-text transition-all duration-300">Voucher</span>
                </a>
            </div>

            <div class="space-y-1">
                <div class="px-4 py-3 group-header transition-all duration-300">
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Quản lý nội dung</p>
                </div>
                <a href="{{ route('admin.news.index') }}" 
                    class="group flex items-center px-4 py-3 text-base font-medium rounded-md {{ request()->routeIs('admin.news.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fas fa-newspaper sidebar-icon mr-4 text-gray-400 group-hover:text-gray-500 text-lg"></i>
                    <span class="sidebar-text transition-all duration-300">Tin tức</span>
                </a>
            </div>
        </nav>

        <div class="border-t border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 sidebar-icon">
                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-shield text-indigo-600 text-lg"></i>
                    </div>
                </div>
                <div class="ml-3 user-text transition-all duration-300">
                    <p class="text-base font-medium text-gray-900">{{ auth()->user()->ten ?? 'Admin' }}</p>
                    <p class="text-sm text-gray-500">Administrator</p>
                </div>
            </div>
        </div>
    </div>
</div>