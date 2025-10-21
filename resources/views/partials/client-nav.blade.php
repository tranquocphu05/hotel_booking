{{-- Top Bar - Thông tin liên hệ --}}
<div class="bg-gray-100 hidden md:block border-b border-gray-200 text-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex justify-between items-center">
        <div class="flex space-x-4 text-gray-600">
            <span class="flex items-center"><i class="fas fa-phone-alt mr-2 text-red-500"></i> (12) 345 67890</span>
            <span class="flex items-center"><i class="fas fa-envelope mr-2 text-red-500"></i> info.colorlib@gmail.com</span>
        </div>
        <div class="flex items-center space-x-3">
            <i class="fab fa-facebook-f text-gray-500 hover:text-red-500 transition"></i>
            <i class="fab fa-twitter text-gray-500 hover:text-red-500 transition"></i>
            <i class="fab fa-instagram text-gray-500 hover:text-red-500 transition"></i>
            <a href="#" class="bg-yellow-600 px-3 py-1 text-white uppercase text-xs font-bold tracking-wider">Booking Now</a>
        </div>
    </div>
</div>

{{-- Main Navigation Bar --}}
<nav class="bg-white shadow relative z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            
            {{-- Logo --}}
            <div class="flex items-center">
                <a href="javascript:void(0)" onclick="window.location.reload()" class="text-3xl font-serif font-bold text-gray-800 cursor-pointer">OZIA HOTEL</a>
            </div>

            {{-- Menu --}}
            <div class="hidden md:flex items-center space-x-8 text-sm font-semibold uppercase tracking-wide">
                <a href="{{ url('/') }}" class="text-gray-900 hover:text-red-600 transition duration-300">Trang Chủ</a>
                <a href="{{ route('client.phong') }}" class="text-gray-600 hover:text-red-600 transition duration-300">Phòng</a>
                <a href="{{ route('client.gioithieu') }}" class="text-gray-600 hover:text-red-600 transition duration-300">Giới Thiệu</a>
                <a href="#" class="text-gray-600 hover:text-red-600 transition duration-300">Pages</a>
                <a href="{{ route('client.tintuc') }}" class="text-gray-600 hover:text-red-600 transition duration-300">News</a>
                <a href="{{ route('client.lienhe') }}" class="text-gray-600 hover:text-red-600 transition duration-300">Liên Hệ</a>
            </div>

            {{-- Client Auth Logic với Dropdown --}}
            <div class="flex items-center">
                @auth
                    {{-- Client User Dropdown --}}
                    <div class="relative group">
                        <button class="flex items-center space-x-2 text-sm text-gray-600 hover:text-red-600 focus:outline-none">
                            @if(auth()->user()->img)
                                <img src="{{ asset(auth()->user()->img) }}" alt="{{ auth()->user()->ho_ten }}" class="w-8 h-8 rounded-full object-cover border-2 border-red-200">
                            @else
                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center border-2 border-red-200">
                                    <span class="text-red-600 font-semibold text-xs">{{ strtoupper(substr(auth()->user()->ho_ten ?? 'U', 0, 1)) }}</span>
                                </div>
                            @endif
                            <span>{{ auth()->user()->ten ?? auth()->user()->ho_ten ?? 'User' }}</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        
                        {{-- Client Dropdown Menu --}}
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-900">{{ auth()->user()->ten ?? auth()->user()->ho_ten ?? 'User' }}</p>
                                <p class="text-xs text-gray-500">Khách hàng</p>
                            </div>
                            
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i>Thông tin cá nhân
                            </a>
                            <a href="{{ route('client.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                            </a>
                            <a href="{{ route('client.phong') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-bed mr-2"></i>Đặt phòng
                            </a>
                            <a href="{{ route('profile.edit') }}#lich-su" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-calendar-check mr-2"></i>Lịch sử đặt phòng
                            </a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-star mr-2"></i>Đánh giá
                            </a>
                            
                            @if(auth()->user()->vai_tro === 'admin')
                                <hr class="my-1">
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50">
                                    <i class="fas fa-cog mr-2"></i>Admin Panel
                                </a>
                            @endif
                            
                            <hr class="my-1">
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-sm mr-4 text-gray-600 hover:text-red-600">Đăng nhập</a>
                    <a href="{{ route('register') }}" class="text-sm text-gray-600 hover:text-red-600">Đăng ký</a>
                @endauth
            </div>
            
        </div>
    </div>
</nav>



