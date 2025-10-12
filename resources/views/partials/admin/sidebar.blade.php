<aside id="admin-sidebar"
    class="w-64 bg-white shadow p-4 min-h-screen transform -translate-x-full md:translate-x-0 md:block transition-transform duration-300 ease-in-out">
    <div class="mb-6">
        <h3 class="font-bold text-lg">TailAdmin</h3>
        <p class="text-sm text-gray-500">Control panel</p>
    </div>

    <nav class="space-y-2 text-sm">
        <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-50 {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50' : '' }}">Dashboard</a>
        <a href="{{ route('admin.dat_phong.index') }}" class="block px-3 py-2 rounded hover:bg-gray-50 {{ request()->routeIs('admin.dat_phong.*') ? 'bg-indigo-50' : '' }}">Đặt phòng</a>
        <a href="{{ route('admin.loai_phong.index') }}" class="block px-3 py-2 rounded hover:bg-gray-50 {{ request()->routeIs('admin.loai_phong.*') ? 'bg-indigo-50' : '' }}">Loại phòng</a>
        <a href="{{ route('admin.phong.index') }}" class="block px-3 py-2 rounded hover:bg-gray-50 {{ request()->routeIs('admin.phong.*') ? 'bg-indigo-50' : '' }}">Phòng</a>
        <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 rounded hover:bg-gray-50 {{ request()->routeIs('admin.users.*') ? 'bg-indigo-50' : '' }}">Users</a>
        <a href="{{ route('admin.invoices.index') }}" class="block px-3 py-2 rounded hover:bg-gray-50 {{ request()->routeIs('admin.invoices.*') ? 'bg-indigo-50' : '' }}">Hóa Đơn</a>
        <a href="{{ route('register') }}" class="block px-3 py-2 rounded hover:bg-gray-50">Register</a>
        <a href="#" class="block px-3 py-2 rounded hover:bg-gray-50">Bookings</a>
        <a href="{{ route('admin.voucher.index') }}"
            class="block px-3 py-2 rounded hover:bg-gray-50 {{ request()->routeIs('admin.voucher.*') ? 'bg-indigo-50' : '' }}">Vouchers</a>
        <a href="{{ route('admin.reviews.index') }}"
            class="block px-3 py-2 rounded hover:bg-gray-50 {{ request()->routeIs('admin.reviews.index') ? 'bg-indigo-50' : '' }}">Reviews</a>
    </nav>
</aside>

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

</aside>
