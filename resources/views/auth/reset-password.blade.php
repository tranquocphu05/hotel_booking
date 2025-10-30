<x-auth-layout>
    <div class="space-y-6">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Đặt Lại Mật Khẩu</h1>
            <p class="text-sm text-gray-500 mt-2">Tạo mật khẩu mới và an toàn cho tài khoản của bạn.</p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Địa chỉ Email:</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <x-text-input id="email"
                        class="block w-full rounded-md border-gray-300 shadow-sm pl-10 bg-gray-50 cursor-not-allowed"
                        type="email" name="email" :value="old('email', $request->email)" required readonly />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Mật khẩu mới:</label>
                <div class="mt-1 relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <x-text-input id="password"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 pl-10 pr-10"
                        type="password" name="password" required autocomplete="new-password"
                        placeholder="Nhập mật khẩu mới (tối thiểu 8 ký tự)" />
                    <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <i class="fas fa-eye" id="password-icon"></i>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Xác nhận mật khẩu:</label>
                <div class="mt-1 relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <x-text-input id="password_confirmation"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 pl-10 pr-10"
                        type="password" name="password_confirmation" required autocomplete="new-password"
                        placeholder="Nhập lại mật khẩu mới" />
                    <button type="button" onclick="togglePassword('password_confirmation')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <i class="fas fa-eye" id="password_confirmation-icon"></i>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex justify-center pt-2">
                <x-primary-button
                    class="w-full justify-center text-base py-3 
                    bg-indigo-600 hover:bg-indigo-700 
                    text-white font-bold 
                    shadow-lg shadow-indigo-500/50 
                    transition duration-300 ease-in-out 
                    hover:scale-[1.02] hover:-translate-y-0.5 
                    active:scale-[0.98] active:translate-y-0">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ __('ĐẶT LẠI MẬT KHẨU') }}
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
        </div>

        <div class="mt-6 border-t pt-4 border-gray-100">
            <h3 class="font-semibold mb-2 flex items-center text-gray-700">
                <i class="fas fa-lightbulb mr-2 text-yellow-500"></i>
                Mẹo bảo mật:
            </h3>
            <ul class="space-y-1 pl-5 text-sm text-gray-600 list-disc">
                <li>Sử dụng ít nhất 8 ký tự</li>
                <li>Kết hợp chữ hoa, chữ thường, số và ký tự đặc biệt</li>
                <li>Không sử dụng thông tin cá nhân dễ đoán như tên hoặc ngày sinh</li>
            </ul>
        </div>
        <div class="mt-4 text-center">
            <p class="text-sm text-gray-500">
                <i class="fas fa-question-circle mr-1"></i>
                Cần hỗ trợ?
                <a href="mailto:support@hotel.com" class="font-semibold hover:underline text-indigo-600">
                    Liên hệ chúng tôi
                </a>
            </p>
        </div>

    </div>
</x-auth-layout>

<script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '-icon');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>