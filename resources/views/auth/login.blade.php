<x-auth-layout>
    <div class="space-y-6">
        <div class="text-center">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Đăng nhập tài khoản</h1>
            <p class="text-sm text-gray-500 mt-2">Nhập thông tin đăng nhập để tiếp tục</p>
        </div>

        <!-- Trạng thái phiên -->
        <x-auth-session-status class="mb-4" :status="session('status')" />
        @if (session('error'))
            <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Email hoặc Tên
                    đăng nhập:</label>
                <div class="mt-1">
                    <x-text-input id="email"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        type="text" name="email" :value="old('email')" required autofocus autocomplete="username"
                        placeholder="ban@example.com" />
                </div>

                {{-- @if(session('login_error'))
                @if (session('login_error'))
                    <p class="text-red-600 mt-2">{{ session('login_error') }}</p>
                @endif --}}
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Mật
                    khẩu:</label>
                <div class="mt-1 relative">
                    <x-text-input id="password" class="block w-full rounded-md border-gray-300 shadow-sm pr-10"
                        type="password" name="password" required autocomplete="current-password" />
                    <button type="button" class="absolute inset-y-0 end-0 px-3 toggle-password" data-target="#password"
                        aria-label="Ẩn/Hiện mật khẩu">
                        <svg data-icon="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg data-icon="hide" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 hidden"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.05 10.05 0 012.098-3.306M6.6 6.6L17.4 17.4" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                {{-- <x-input-error :messages="$errors->get('password')" class="mt-2" /> --}}
                <x-input-error :messages="$errors->get('email')" class="mt-2" />

            </div>

            <div class="flex items-center justify-between">
                <label class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" name="remember"
                        class="rounded text-indigo-600 focus:ring-indigo-500">
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Ghi nhớ đăng nhập</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm text-indigo-600 hover:underline" href="{{ route('password.request') }}">Quên mật
                        khẩu?</a>
                @endif
            </div>

            <div class="flex justify-center">
                <x-primary-button
                    class="w-3/4 md:w-1/2 justify-center text-base py-2 
               bg-cyan-600 hover:bg-cyan-700 
               text-white font-bold 
               shadow-lg shadow-cyan-500/50 
               transition duration-300 ease-in-out 
               hover:scale-[1.02] hover:-translate-y-0.5 
               active:scale-[0.98] active:translate-y-0">
                    {{ __('Đăng nhập') }}
                </x-primary-button>
            </div>

            <div class="my-4 flex items-center">
                <div class="flex-grow h-px bg-gray-200"></div>
                <span class="px-3 text-xs uppercase text-gray-500">hoặc</span>
                <div class="flex-grow h-px bg-gray-200"></div>
            </div>

            <a href="{{ route('google.login') }}"
                class="w-full inline-flex items-center justify-center gap-2 border border-gray-300 rounded-md px-4 py-2 text-sm font-medium hover:bg-gray-50">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google"
                    class="w-5 h-5">
                Đăng nhập bằng Google
            </a>

            <div class="text-center">
                <p class="text-sm text-gray-600">Chưa có tài khoản? <a href="{{ route('register') }}"
                        class="text-indigo-600 hover:underline">Đăng ký ngay</a></p>
            </div>
        </form>
    </div>
</x-auth-layout>
