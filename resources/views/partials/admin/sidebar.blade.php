<aside class="bg-white shadow rounded p-4">
    <h3 class="font-semibold mb-3">Admin Menu</h3>
    <ul class="space-y-2 text-sm">
    <li><a href="{{ route('client.dashboard') }}" class="hover:underline">Dashboard</a></li>
        <li><a href="{{ route('admin.loai_phong.index') }}" class="hover:underline">Loại phòng</a></li>
        <li><a href="{{ route('admin.users.index') }}" class="hover:underline">Users</a></li>
    <li><a href="{{ route('register') }}" class="hover:underline">Register</a></li>
        <li><a href="#" class="hover:underline">Bookings</a></li>
        <li><a href="#" class="hover:underline">Vouchers</a></li>
    </ul>
</aside>