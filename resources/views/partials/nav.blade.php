{{-- <nav class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="{{ url('/') }}" class="text-lg font-semibold">Hotel Booking</a>
                <div class="ml-6 flex items-baseline space-x-4">
                    <a href="{{ url('/') }}" class="text-sm hover:underline">Home</a>
                </div>
            </div>

            <div class="flex items-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="mr-4 text-sm">Profile</a>
                    @if(auth()->user()->vai_tro === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="mr-4 text-sm">Admin</a>
                        <a href="{{ route('client.dashboard') }}" class="mr-4 text-sm">Client</a>
                    @else
                        <a href="{{ route('client.dashboard') }}" class="mr-4 text-sm">Client</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="px-3 py-1 bg-gray-200 rounded">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm mr-4">Login</a>
                    <a href="{{ route('register') }}" class="text-sm">Register</a>
                @endauth
            </div>
        </div>
    </div>
</nav> --}}

{{-- FILE: resources/views/partials/nav.blade.php --}}

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

{{-- Nav Bar Chính (Đã sửa đổi) --}}
<nav class="bg-white shadow relative z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20"> {{-- Tăng chiều cao lên 20 --}}
            
            {{-- Logo Sona --}}
            <div class="flex items-center">
                <a href="{{ url('/') }}" class="text-3xl font-serif font-bold text-gray-800">OZIA HOTEL</a>
            </div>

            {{-- Menu Sona Style --}}
            <div class="hidden md:flex items-center space-x-8 text-sm font-semibold uppercase tracking-wide">
                <a href="{{ url('/') }}" class="text-gray-900 hover:text-red-600 transition duration-300">Trang Chủ</a>
                <a href="{{ route('client.phong') }}" class="text-gray-600 hover:text-red-600 transition duration-300">Phòng</a>
                <a href="{{ route('client.gioithieu') }}" class="text-gray-600 hover:text-red-600 transition duration-300">Giới Thiệu</a>
                <a href="#" class="text-gray-600 hover:text-red-600 transition duration-300">Pages</a>
                <a href="#" class="text-gray-600 hover:text-red-600 transition duration-300">News</a>
                <a href="{{ route('client.lienhe') }}" class="text-gray-600 hover:text-red-600 transition duration-300">Liên Hệ</a>
            </div>

            {{-- Auth Logic (Đã sửa đổi style) --}}
            <div class="flex items-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="mr-4 text-sm text-gray-600 hover:text-red-600">Profile</a>
                    @if(auth()->user()->vai_tro === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="mr-4 text-sm text-gray-600 hover:text-red-600">Admin</a>
                        <a href="{{ route('client.dashboard') }}" class="mr-4 text-sm text-gray-600 hover:text-red-600">Client</a>
                    @else
                        <a href="{{ route('client.dashboard') }}" class="mr-4 text-sm text-gray-600 hover:text-red-600">Client</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700 transition">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm mr-4 text-gray-600 hover:text-red-600">Login</a>
                    <a href="{{ route('register') }}" class="text-sm text-gray-600 hover:text-red-600">Register</a>
                @endauth
            </div>
            
        </div>
    </div>
</nav>