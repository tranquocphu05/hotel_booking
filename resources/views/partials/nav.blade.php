<nav class="bg-white shadow">
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
</nav>