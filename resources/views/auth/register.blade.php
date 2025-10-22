<x-auth-layout>
    <div class="space-y-6">
        <div class="text-center">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Create a new account</h1>
            <p class="text-sm text-gray-500 mt-2">Fill the form to get started — it only takes a minute.</p>
        </div>

        @if(session('error'))
            <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Full name</label>
                <div class="mt-1">
                    <x-text-input id="name" class="block w-full rounded-md border-gray-300 shadow-sm" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                </div>
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Email</label>
                <div class="mt-1">
                    <x-text-input id="email" class="block w-full rounded-md border-gray-300 shadow-sm" type="email" name="email" :value="old('email')" required autocomplete="username" />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="cccd" class="block text-sm font-medium text-gray-700 dark:text-gray-200">CCCD</label>
                    <div class="mt-1">
                        <x-text-input id="cccd" class="block w-full rounded-md border-gray-300 shadow-sm" type="text" name="cccd" :value="old('cccd')" />
                    </div>
                    <x-input-error :messages="$errors->get('cccd')" class="mt-2" />
                </div>

                <div>
                    <label for="sdt" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Phone</label>
                    <div class="mt-1">
                        <x-text-input id="sdt" class="block w-full rounded-md border-gray-300 shadow-sm" type="text" name="sdt" :value="old('sdt')" />
                    </div>
                    <x-input-error :messages="$errors->get('sdt')" class="mt-2" />
                </div>
            </div>

            <div>
                <label for="dia_chi" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Address</label>
                <div class="mt-1">
                    <x-text-input id="dia_chi" class="block w-full rounded-md border-gray-300 shadow-sm" type="text" name="dia_chi" :value="old('dia_chi')" />
                </div>
                <x-input-error :messages="$errors->get('dia_chi')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Password</label>
                        <div class="mt-1 relative">
                            <x-text-input id="password" class="block w-full rounded-md border-gray-300 shadow-sm pr-10" type="password" name="password" required autocomplete="new-password" />
                            <button type="button" class="absolute inset-y-0 end-0 px-3 toggle-password" data-target="#password" aria-label="Toggle password visibility">
                                <svg data-icon="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg data-icon="hide" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.05 10.05 0 012.098-3.306M6.6 6.6L17.4 17.4" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Confirm password</label>
                    <div class="mt-1 relative">
                        <x-text-input id="password_confirmation" class="block w-full rounded-md border-gray-300 shadow-sm pr-10" type="password" name="password_confirmation" required autocomplete="new-password" />
                        <button type="button" class="absolute inset-y-0 end-0 px-3 toggle-password" data-target="#password_confirmation" aria-label="Toggle password visibility">
                            <svg data-icon="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg data-icon="hide" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.05 10.05 0 012.098-3.306M6.6 6.6L17.4 17.4" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>
            </div>

            <div class="flex items-center justify-between">
                <a class="text-sm text-indigo-600 hover:underline" href="{{ route('login') }}">Already registered?</a>
                <x-primary-button>{{ __('Register') }}</x-primary-button>
            </div>

            <div class="my-4 flex items-center">
                <div class="flex-grow h-px bg-gray-200"></div>
                <span class="px-3 text-xs uppercase text-gray-500">hoặc</span>
                <div class="flex-grow h-px bg-gray-200"></div>
            </div>

            <a href="{{ route('google.register') }}" class="w-full inline-flex items-center justify-center gap-2 border border-gray-300 rounded-md px-4 py-2 text-sm font-medium hover:bg-gray-50">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" class="w-5 h-5">
                Đăng ký/Đăng nhập bằng Google
            </a>
        </form>
    </div>
</x-auth-layout>
