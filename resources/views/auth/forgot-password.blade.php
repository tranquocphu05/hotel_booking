<x-auth-layout>
    <div class="space-y-6">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Khôi phục Tài khoản</h1>
            <p class="text-sm text-gray-500 mt-2">Vui lòng nhập email đã đăng ký để chúng tôi gửi link đặt lại mật khẩu.</p>
        </div>

        <x-auth-session-status class="mb-4 text-green-600" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <div class="bg-indigo-50 border-l-4 border-indigo-500 p-3 mb-4 rounded-lg">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 mr-3" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <p class="text-sm text-gray-700 font-medium">
                        Chúng tôi sẽ gửi một email chứa liên kết đặt lại mật khẩu.
                    </p>
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Email đã đăng ký:</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <x-text-input id="email"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 pl-10"
                        type="email" name="email" :value="old('email')" required autofocus
                        placeholder="Nhập email của bạn" />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="flex justify-center pt-2">
                <x-primary-button
                    class="w-full justify-center text-base py-3 
                    /* Màu nền TÍM/INDIGO (Khác màu Đăng nhập) */
                    bg-indigo-600 hover:bg-indigo-700 
                    /* Hiệu ứng nổi bật */
                    text-white font-bold 
                    shadow-lg shadow-indigo-500/50 
                    /* Hiệu ứng chuyển động */
                    transition duration-300 ease-in-out 
                    hover:scale-[1.02] hover:-translate-y-0.5 
                    active:scale-[0.98] active:translate-y-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l.493-.185A.993.993 0 006 17c3.957 0 7.823-1.47 10.707-4.137l.493-.185a1 1 0 001.169-1.409l-7-14z" />
                    </svg>
                    {{ __('Gửi Link Đặt Lại Mật Khẩu') }}
                </x-primary-button>
            </div>
        </form>

        <div class="text-center space-y-3 pt-2">
            @if (Route::has('login'))
                <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-indigo-600 transition-colors font-medium flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Quay lại Đăng nhập
                </a>
            @endif
            @if (Route::has('register'))
                <p class="text-sm text-gray-600">Chưa có tài khoản? 
                    <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-700 font-semibold">Đăng ký ngay</a>
                </p>
            @endif
        </div>
    </div>
</x-auth-layout>