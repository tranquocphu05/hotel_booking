<!DOCTYPE html>
<html lang="vi" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- TinyMCE will be loaded per page -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    @stack('styles')
</head>
<body class="h-full transition-colors duration-300">
    <!-- Global Loading Overlay -->
    <div id="global-loading-overlay" class="hidden fixed inset-0 bg-black bg-opacity-70 z-[9999]" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100%; height: 100%;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; max-width: 400px; padding: 0 20px;">
            <div class="bg-white rounded-xl p-10 flex flex-col items-center justify-center shadow-2xl" style="width: 100%;">
                <div class="relative mb-6 flex items-center justify-center" style="width: 80px; height: 80px; margin: 0 auto;">
                    <div class="animate-spin rounded-full border-4 border-gray-200 border-t-indigo-600" style="width: 80px; height: 80px; position: absolute; top: 0; left: 0;"></div>
                    <div class="absolute inset-0 flex items-center justify-center" style="width: 80px; height: 80px;">
                        <i class="fas fa-spinner text-indigo-600 text-2xl"></i>
                    </div>
                </div>
                <p class="text-gray-800 font-semibold text-lg mb-2 text-center w-full">Đang tải...</p>
                <p class="text-sm text-gray-500 text-center w-full">Vui lòng đợi trong giây lát</p>
                <div class="mt-4 flex space-x-1 justify-center items-center">
                    <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 0s;"></div>
                    <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
                    <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 0.4s;"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="min-h-full flex">
        <!-- Mobile sidebar backdrop -->
        <div id="sidebar-backdrop" class="fixed inset-0 bg-gray-600 bg-opacity-75 z-20 hidden lg:hidden"></div>
        
        <!-- Sidebar -->
        @include('partials.admin.sidebar')
        
        <!-- Main content area -->
        <div class="flex-1 flex flex-col lg:ml-64 main-content pt-16">
            <!-- Top navigation -->
            @include('partials.admin.header')
            
            <!-- Page content -->
            <main class="flex-1 bg-gray-50">
                <div class="py-6">
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                            <div class="mb-4 rounded-md bg-green-50 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-check-circle text-green-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                                    </div>
                                </div>
                            </div>
            @endif
                        
            @if (session('error'))
                            <div class="mb-4 rounded-md bg-red-50 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-circle text-red-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                                    </div>
                                </div>
                            </div>
            @endif
                        
                        <div class="admin-content-wrapper">
            @yield('admin_content')
        </div>
    </div>
            </div>
            </main>
        </div>
</div>

    <!-- Mobile sidebar toggle script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            
            if (sidebarToggle && sidebar && backdrop) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('-translate-x-full');
                    backdrop.classList.toggle('hidden');
                });
                
                backdrop.addEventListener('click', function() {
                    sidebar.classList.add('-translate-x-full');
                    backdrop.classList.add('hidden');
                });
            }
        });
    </script>
    
    <!-- Ensure content is visible -->
    <style>
        /* Fix layout alignment */
        body {
            margin: 0;
            padding: 0;
        }
        
        .min-h-full {
            min-height: 100vh;
        }
        
        /* Sidebar positioning */
        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 30;
        }
        
        /* Main content area */
        .lg\:ml-64 {
            margin-left: 0;
            transition: margin-left 0.3s ease;
        }

        @media (min-width: 1024px) {
            .lg\:ml-64 {
                margin-left: 16rem; /* 256px */
            }
        }
        
        /* When sidebar is hidden, expand main content */
        #sidebar.show ~ .flex-1 {
            margin-left: 0 !important;
        }
        
        /* Content visibility */
        .admin-content-wrapper {
            opacity: 1 !important;
            visibility: visible !important;
            display: block !important;
        }
        
        .admin-content-wrapper > * {
            opacity: 1 !important;
            visibility: visible !important;
            display: block !important;
        }
        
        /* Ensure tables are visible */
        table {
            display: table !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        .bg-white {
            background-color: white !important;
            opacity: 1 !important;
            visibility: visible !important;
            display: block !important;
        }
        
        /* Remove any duplicate elements */
        .duplicate-header {
            display: none !important;
        }
        
        /* Ensure dropdown is hidden by default */
        #user-menu {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
        }
        
        #user-menu.show {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        /* Custom animations for admin elements */
        .admin-profile-button {
            position: relative;
            overflow: hidden;
        }
        
        .admin-profile-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .admin-profile-button:hover::before {
            left: 100%;
        }
        
        .admin-avatar {
            position: relative;
            overflow: hidden;
        }
        
        .admin-avatar::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.3s, height 0.3s;
        }
        
        .admin-avatar:hover::after {
            width: 100%;
            height: 100%;
        }
        
        .notification-bell {
            animation: ring 2s infinite;
        }
        
        @keyframes ring {
            0%, 100% { transform: rotate(0deg); }
            10%, 30% { transform: rotate(-10deg); }
            20%, 40% { transform: rotate(10deg); }
        }
        
        .dropdown-arrow {
            transition: transform 0.2s ease;
        }
        
        #user-menu-button:hover .dropdown-arrow {
            transform: rotate(180deg);
        }
        
        /* Đảm bảo modal ẩn hoàn toàn */
        #modalOverlay {
            display: none !important;
        }
        
        #modalOverlay.show {
            display: flex !important;
        }
        
        /* Button Animations */
        .btn-animate {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-animate:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .btn-animate:active {
            transform: translateY(0);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        /* Ripple Effect */
        .btn-animate::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-animate:active::before {
            width: 300px;
            height: 300px;
        }
        
        /* Button Types */
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #3d8bfe 0%, #00d4fe 100%);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, #ff5252 0%, #e53935 100%);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #feca57 0%, #ff9ff3 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .btn-warning:hover {
            background: linear-gradient(135deg, #fdcb6e 0%, #fd79a8 100%);
        }
        
        .btn-info {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .btn-info:hover {
            background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);
        }
        
        /* Table Button Effects */
        .table-btn {
            transition: all 0.2s ease;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
        }
        
        .table-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .table-btn:active {
            transform: scale(0.95);
        }
        
        /* Link Effects */
        .link-hover {
            transition: all 0.3s ease;
            position: relative;
        }
        
        .link-hover::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background: currentColor;
            transition: width 0.3s ease;
        }
        
        .link-hover:hover::after {
            width: 100%;
        }
        
        /* Pulse Animation */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .btn-pulse {
            animation: pulse 2s infinite;
        }
        
        /* Shake Animation */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .btn-shake {
            animation: shake 0.5s ease-in-out;
        }
        
        /* Bounce Animation */
        @keyframes bounce {
            0%, 20%, 53%, 80%, 100% { transform: translateY(0); }
            40%, 43% { transform: translateY(-10px); }
            70% { transform: translateY(-5px); }
            90% { transform: translateY(-2px); }
        }
        
        .btn-bounce {
            animation: bounce 1s ease-in-out;
        }
        
        /* Sidebar Animation */
        #sidebar {
            transition: transform 0.3s ease-in-out;
        }
        
        #sidebar-backdrop {
            transition: opacity 0.3s ease-in-out;
        }
        
        /* Hamburger Menu Animation */
        #sidebar-toggle {
            transition: all 0.3s ease;
        }
        
        #sidebar-toggle:hover {
            transform: scale(1.1);
        }
        
        #sidebar-toggle.rotate-90 {
            transform: rotate(90deg);
        }
        
        /* Sidebar collapse effect - chỉ ẩn chữ, giữ icon */
        #sidebar {
            width: 16rem; /* 256px - full width */
            transition: width 0.3s ease;
            overflow: hidden; /* Ẩn nội dung bị tràn */
        }
        
        #sidebar.show {
            width: 4rem; /* 64px - chỉ hiển thị icon */
            min-width: 4rem !important;
            max-width: 4rem !important;
        }
        
        /* Đảm bảo sidebar collapsed chỉ hiển thị icon */
        #sidebar.show .flex {
            justify-content: center !important; /* Căn giữa icon */
            align-items: center !important;
        }
        
        #sidebar.show .ml-3 {
            margin-left: 0 !important; /* Xóa margin khi collapsed */
            display: none !important;
        }
        
        /* Ẩn tất cả padding và margin không cần thiết */
        #sidebar.show .px-3,
        #sidebar.show .px-4,
        #sidebar.show .px-6 {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }
        
        /* Ẩn tất cả text content */
        #sidebar.show *:not(.sidebar-icon):not(.fas):not(.fa):not(i) {
            display: none !important;
        }
        
        /* Ẩn tất cả text khi sidebar collapsed */
        #sidebar.show .sidebar-text,
        #sidebar.show .logo-text,
        #sidebar.show .group-header,
        #sidebar.show .user-text,
        #sidebar.show nav span,
        #sidebar.show p,
        #sidebar.show h1,
        #sidebar.show h2,
        #sidebar.show h3,
        #sidebar.show h4,
        #sidebar.show h5,
        #sidebar.show h6 {
            display: none !important;
        }
        
        /* Ẩn tất cả div chứa text */
        #sidebar.show .ml-3 {
            display: none !important;
        }
        
        /* Chỉ hiển thị icon và căn giữa */
        #sidebar.show .sidebar-icon {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            width: 100% !important;
            margin: 0 !important;
        }
        
        /* Căn giữa các navigation items */
        #sidebar.show nav a {
            justify-content: center !important;
            padding: 0.75rem 0.5rem !important;
        }
        
        /* Ẩn group headers hoàn toàn */
        #sidebar.show .group-header {
            display: none !important;
        }
        
        /* User section collapsed */
        #sidebar.show .user-text {
            display: none !important;
        }
        
        #sidebar.show .user-text + * {
            display: none !important;
        }
        
        /* Icon vẫn hiển thị */
        #sidebar.show .sidebar-icon {
            opacity: 1 !important;
            visibility: visible !important;
            display: flex !important;
        }
        
        /* Đảm bảo icon không bị ẩn */
        #sidebar.show i,
        #sidebar.show .fas,
        #sidebar.show .fa {
            display: inline-block !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        /* Logo icon khi collapsed */
        #sidebar.show .logo-text + * {
            display: none !important;
        }
        
        /* Navigation links chỉ hiển thị icon */
        #sidebar.show nav a {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        /* User avatar khi collapsed */
        #sidebar.show .user-text {
            display: none !important;
        }
        
        /* Main content adjustment */
        .main-content {
            transition: margin-left 0.3s ease;
        }
        
        #sidebar.show ~ .main-content {
            margin-left: 4rem !important; /* 64px khi sidebar collapsed */
        }
        
        /* Smooth transitions for all interactive elements */
        * {
            transition: all 0.2s ease;
        }
        
        /* Dark Mode Styles */
        .dark {
            color-scheme: dark;
        }
        
        .dark body {
            background-color: #1f2937;
            color: #f9fafb;
        }
        
        .dark .bg-white {
            background-color: #374151 !important;
            color: #f9fafb !important;
        }
        
        .dark .bg-gray-50 {
            background-color: #1f2937 !important;
        }
        
        /* Input fields dark mode */
        .dark input[type="text"],
        .dark input[type="email"],
        .dark input[type="password"],
        .dark input[type="number"],
        .dark input[type="search"],
        .dark textarea,
        .dark select {
            background-color: #4b5563 !important;
            border-color: #6b7280 !important;
            color: #f9fafb !important;
        }
        
        .dark input[type="text"]:focus,
        .dark input[type="email"]:focus,
        .dark input[type="password"]:focus,
        .dark input[type="number"]:focus,
        .dark input[type="search"]:focus,
        .dark textarea:focus,
        .dark select:focus {
            background-color: #4b5563 !important;
            border-color: #6366f1 !important;
            color: #f9fafb !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
        }
        
        .dark input::placeholder,
        .dark textarea::placeholder {
            color: #9ca3af !important;
        }
        
        .dark .text-gray-900 {
            color: #f9fafb !important;
        }
        
        .dark .text-gray-700 {
            color: #d1d5db !important;
        }
        
        .dark .text-gray-600 {
            color: #9ca3af !important;
        }
        
        .dark .text-gray-500 {
            color: #6b7280 !important;
        }
        
        .dark .border-gray-200 {
            border-color: #4b5563 !important;
        }
        
        .dark .border-gray-300 {
            border-color: #6b7280 !important;
        }
        
        .dark .ring-gray-300 {
            --tw-ring-color: #6b7280 !important;
        }
        
        .dark .hover\:bg-gray-50:hover {
            background-color: #4b5563 !important;
        }
        
        .dark .hover\:bg-gray-100:hover {
            background-color: #4b5563 !important;
        }
        
        .dark .hover\:text-gray-900:hover {
            color: #f9fafb !important;
        }
        
        .dark .hover\:text-gray-500:hover {
            color: #d1d5db !important;
        }
        
        .dark .focus\:ring-indigo-500:focus {
            --tw-ring-color: #6366f1 !important;
        }
        
        .dark .focus\:border-indigo-500:focus {
            border-color: #6366f1 !important;
        }
        
        .dark .bg-indigo-50 {
            background-color: #312e81 !important;
        }
        
        .dark .text-indigo-700 {
            color: #a5b4fc !important;
        }
        
        .dark .bg-green-100 {
            background-color: #064e3b !important;
        }
        
        .dark .text-green-700 {
            color: #6ee7b7 !important;
        }
        
        .dark .bg-red-100 {
            background-color: #7f1d1d !important;
        }
        
        .dark .text-red-700 {
            color: #fca5a5 !important;
        }
        
        .dark .bg-yellow-100 {
            background-color: #78350f !important;
        }
        
        .dark .text-yellow-700 {
            color: #fde68a !important;
        }
        
        .dark .bg-blue-100 {
            background-color: #1e3a8a !important;
        }
        
        .dark .text-blue-700 {
            color: #93c5fd !important;
        }
        
        /* Button dark mode */
        .dark .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
            color: #ffffff !important;
            border: none !important;
        }
        
        .dark .btn-primary:hover {
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%) !important;
        }
        
        .dark .btn-success {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
            color: #ffffff !important;
            border: none !important;
        }
        
        .dark .btn-success:hover {
            background: linear-gradient(135deg, #047857 0%, #059669 100%) !important;
        }
        
        .dark .btn-danger {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%) !important;
            color: #ffffff !important;
            border: none !important;
        }
        
        .dark .btn-danger:hover {
            background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%) !important;
        }
        
        .dark .btn-warning {
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%) !important;
            color: #ffffff !important;
            border: none !important;
        }
        
        .dark .btn-warning:hover {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%) !important;
        }
        
        .dark .btn-info {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%) !important;
            color: #ffffff !important;
            border: none !important;
        }
        
        .dark .btn-info:hover {
            background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%) !important;
        }
        
        /* Table button dark mode */
        .dark .table-btn {
            background-color: #4b5563 !important;
            color: #f9fafb !important;
            border: 1px solid #6b7280 !important;
        }
        
        .dark .table-btn:hover {
            background-color: #6b7280 !important;
            color: #ffffff !important;
        }
        
        /* Link dark mode */
        .dark a {
            color: #93c5fd !important;
        }
        
        .dark a:hover {
            color: #60a5fa !important;
        }
        
        /* Form labels dark mode */
        .dark label {
            color: #d1d5db !important;
        }
        
        /* Error messages dark mode */
        .dark .text-red-600 {
            color: #fca5a5 !important;
        }
        
        /* Success messages dark mode */
        .dark .text-green-600 {
            color: #6ee7b7 !important;
        }
        
        .dark .shadow {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.3), 0 1px 2px 0 rgba(0, 0, 0, 0.2) !important;
        }
        
        .dark .shadow-lg {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2) !important;
        }
        
        .dark .shadow-xl {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2) !important;
        }
        
        /* Dark mode toggle animation */
        .dark-mode-toggle {
            position: relative;
            overflow: hidden;
        }
        
        .dark-mode-toggle::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .dark-mode-toggle:active::before {
            width: 300px;
            height: 300px;
        }
        
        /* Icon rotation animation */
        #sun-icon, #moon-icon {
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        
        .dark #sun-icon {
            transform: rotate(180deg);
            opacity: 0;
        }
        
        .dark #moon-icon {
            transform: rotate(0deg);
            opacity: 1;
        }
        
        /* Smooth dark mode transition */
        * {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        /* Additional dark mode improvements */
        .dark .rounded-2xl {
            background-color: #374151 !important;
            border: 1px solid #4b5563 !important;
        }
        
        .dark .border-gray-100 {
            border-color: #4b5563 !important;
        }
        
        .dark .border-gray-200 {
            border-color: #4b5563 !important;
        }
        
        .dark .border-gray-300 {
            border-color: #6b7280 !important;
        }
        
        /* Sidebar dark mode */
        .dark #sidebar {
            background-color: #1f2937 !important;
            border-right: 1px solid #374151 !important;
        }
        
        .dark #sidebar .bg-white {
            background-color: #1f2937 !important;
        }
        
        /* Header dark mode */
        .dark header {
            background-color: #374151 !important;
            border-bottom: 1px solid #4b5563 !important;
        }
        
        /* Search input dark mode */
        .dark #search {
            background-color: #4b5563 !important;
            border-color: #6b7280 !important;
            color: #f9fafb !important;
        }
        
        .dark #search:focus {
            background-color: #4b5563 !important;
            border-color: #6366f1 !important;
            color: #f9fafb !important;
        }
        
        /* Dropdown dark mode */
        .dark #user-menu {
            background-color: #374151 !important;
            border: 1px solid #4b5563 !important;
        }
        
        .dark #user-menu a {
            color: #d1d5db !important;
        }
        
        .dark #user-menu a:hover {
            background-color: #4b5563 !important;
            color: #f9fafb !important;
        }
        
        /* File input dark mode */
        .dark input[type="file"] {
            background-color: #4b5563 !important;
            border-color: #6b7280 !important;
            color: #f9fafb !important;
        }
        
        .dark input[type="file"]:hover {
            background-color: #6b7280 !important;
        }
        
        /* Pagination dark mode */
        .dark .pagination {
            background-color: #374151 !important;
        }
        
        .dark .pagination a {
            background-color: #4b5563 !important;
            color: #d1d5db !important;
            border-color: #6b7280 !important;
        }
        
        .dark .pagination a:hover {
            background-color: #6b7280 !important;
            color: #f9fafb !important;
        }
        
        .dark .pagination .active {
            background-color: #6366f1 !important;
            color: #ffffff !important;
        }
        
        /* Enhanced Input Styling */
        input[type="text"], 
        input[type="number"], 
        input[type="email"], 
        input[type="password"], 
        input[type="search"],
        textarea, 
        select {
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        input[type="text"]:focus, 
        input[type="number"]:focus, 
        input[type="email"]:focus, 
        input[type="password"]:focus, 
        input[type="search"]:focus,
        textarea:focus, 
        select:focus {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        input[type="file"] {
            transition: all 0.3s ease;
        }
        
        input[type="file"]:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        /* File input styling */
        input[type="file"]::file-selector-button {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            margin-right: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        input[type="file"]::file-selector-button:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        /* Label styling */
        label {
            font-weight: 600;
            letter-spacing: 0.025em;
        }
        
        /* Form group spacing */
        .space-y-6 > div {
            margin-bottom: 1.5rem;
        }
        
        /* Enhanced focus states */
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        
        /* Placeholder styling */
        input::placeholder, textarea::placeholder {
            color: #9ca3af;
            font-style: italic;
        }
        
        /* Error state styling */
        .border-red-300:focus {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }
        
        /* CKEditor Styling */
        .ck-editor__editable {
            min-height: 300px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
        }
        
        .ck-editor__editable:focus {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important;
        }
        
        .ck-toolbar {
            border: 1px solid #d1d5db !important;
            border-bottom: none !important;
            border-radius: 0.5rem 0.5rem 0 0 !important;
        }
        
        .ck-editor {
            border-radius: 0.5rem !important;
            overflow: hidden !important;
        }
        
        /* Ensure CKEditor is visible */
        .ck-editor__main {
            display: block !important;
        }
        
        .ck-editor__editable_inline {
            padding: 1rem !important;
        }
        
        /* Description column styling */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* Table description cell - Fixed width */
        .table-description {
            width: 192px; /* w-48 = 12rem = 192px */
            max-width: 192px;
            min-width: 192px;
            word-wrap: break-word;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Description column in table */
        table th:nth-child(5),
        table td:nth-child(5) {
            width: 200px;
            max-width: 200px;
            min-width: 200px;
        }
        
        /* Description tooltip */
        .description-tooltip {
            position: relative;
            cursor: help;
        }
        
        .description-tooltip:hover::after {
            content: attr(title);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #1f2937;
            color: white;
            padding: 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            white-space: nowrap;
            z-index: 1000;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        /* Voucher table styling - Clean layout */
        .voucher-table {
            table-layout: auto;
            width: 100%;
            border-collapse: collapse;
        }
        
        .voucher-table th,
        .voucher-table td {
            padding: 12px 8px;
            vertical-align: middle;
            border: 1px solid #e5e7eb;
            text-align: left;
        }
        
        .voucher-table th {
            background-color: #f9fafb;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .voucher-table td {
            font-size: 0.875rem;
        }
        
        .voucher-table tr:hover {
            background-color: #f9fafb;
        }
    </style>
    
    @yield('modals')
    
    @stack('scripts')
    
    <script>
        // Button Animation Effects
        document.addEventListener('DOMContentLoaded', function() {
            // Add click effects to all animated buttons
            const animatedButtons = document.querySelectorAll('.btn-animate');
            animatedButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    // Add ripple effect
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.classList.add('ripple');
                    
                    this.appendChild(ripple);
                    
                    // Remove ripple after animation
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
            
            // Add hover effects to table buttons
            const tableButtons = document.querySelectorAll('.table-btn');
            tableButtons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.05)';
                });
                
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });
            
            // Add success animation after form submission
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    const submitButton = this.querySelector('button[type="submit"]');
                    if (submitButton) {
                        submitButton.classList.add('btn-bounce');
                        setTimeout(() => {
                            submitButton.classList.remove('btn-bounce');
                        }, 1000);
                    }
                });
            });
            
            // Add shake effect for delete buttons
            const deleteButtons = document.querySelectorAll('button[type="submit"]');
            deleteButtons.forEach(button => {
                if (button.textContent.includes('Delete') || button.textContent.includes('Xóa')) {
                    button.addEventListener('click', function(e) {
                        if (!confirm('Bạn có chắc muốn thực hiện hành động này?')) {
                            e.preventDefault();
                            this.classList.add('btn-shake');
                            setTimeout(() => {
                                this.classList.remove('btn-shake');
                            }, 500);
                        }
                    });
                }
            });
        });
        
        // Add CSS for ripple effect
        const style = document.createElement('style');
        style.textContent = `
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.6);
                transform: scale(0);
                animation: ripple-animation 0.6s linear;
                pointer-events: none;
            }
            
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
    
    <!-- Global Loading Script -->
    <script>
        // Đảm bảo loading luôn căn giữa khi hiển thị
        function showGlobalLoading() {
            const globalLoading = document.getElementById('global-loading-overlay');
            if (globalLoading) {
                globalLoading.style.display = 'block';
                globalLoading.style.position = 'fixed';
                globalLoading.style.top = '0';
                globalLoading.style.left = '0';
                globalLoading.style.right = '0';
                globalLoading.style.bottom = '0';
                globalLoading.style.width = '100%';
                globalLoading.style.height = '100%';
                globalLoading.classList.remove('hidden');
            }
        }
        
        function hideGlobalLoading() {
            const globalLoading = document.getElementById('global-loading-overlay');
            if (globalLoading) {
                globalLoading.style.display = 'none';
                globalLoading.classList.add('hidden');
            }
        }
        
        // Ẩn loading ngay khi DOM ready
        document.addEventListener('DOMContentLoaded', function() {
            hideGlobalLoading();
        });
        
        // Ẩn loading khi trang load xong
        window.addEventListener('load', function() {
            hideGlobalLoading();
        });
        
        // Hiển thị loading khi trang bắt đầu unload (chuyển trang)
        window.addEventListener('beforeunload', function() {
            showGlobalLoading();
        });
        
        // Hiển thị loading khi click vào các link navigation
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('a[href]:not([href^="#"]):not([href^="javascript:"])').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    // Chỉ hiển thị loading cho các link trong cùng domain
                    if (this.href && (this.href.includes(window.location.host) || this.href.startsWith('/'))) {
                        showGlobalLoading();
                    }
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
