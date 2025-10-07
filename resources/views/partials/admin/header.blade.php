<header class="mb-6 p-0 sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded p-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button id="admin-menu-toggle" aria-controls="admin-sidebar" aria-expanded="false" class="md:hidden p-2 rounded hover:bg-gray-100 transition-colors duration-150">
            <!-- hamburger -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

            <div class="flex items-center gap-3">
                <h2 class="text-lg font-semibold">TailAdmin</h2>
                <span class="text-sm text-gray-500">Admin</span>
            </div>
    </div>

    <div class="flex-1 mx-6">
        <div class="max-w-3xl mx-auto">
            <div class="relative">
                <input type="search" placeholder="Search or type command..." class="w-full rounded-lg border bg-gray-50 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                <button class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.9 14.32a8 8 0 111.414-1.414l4.387 4.386-1.414 1.415-4.387-4.387zM10 16a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-4">
        <button class="p-2 rounded hover:bg-gray-100" title="Toggle dark">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zM4.22 5.22a1 1 0 011.415 0l.707.708a1 1 0 11-1.414 1.414L4.22 6.636a1 1 0 010-1.415zM2 10a1 1 0 011-1h1a1 1 0 110 2H3a1 1 0 01-1-1zM5.636 15.364a1 1 0 010-1.415l.707-.707a1 1 0 111.414 1.414l-.707.707a1 1 0 01-1.414 0zM10 16a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1z" />
            </svg>
        </button>

        <div class="flex items-center gap-3">
            <img src="https://i.pravatar.cc/40" alt="avatar" class="h-8 w-8 rounded-full border" />
            <div class="text-sm text-gray-700">{{ auth()->user()?->ho_ten ?? auth()->user()?->username ?? 'Admin' }}</div>
        </div>
            </div>
        </div>
    </div>
</header>