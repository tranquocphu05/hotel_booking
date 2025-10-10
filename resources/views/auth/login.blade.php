<x-auth-layout>
    <div class="space-y-6">
        <div class="text-center">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Sign in to your account</h1>
            <p class="text-sm text-gray-500 mt-2">Enter your credentials to continue</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Email or Username</label>
                <div class="mt-1">
                    <x-text-input id="email" class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" type="text" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@example.com" />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                @if(session('login_error'))
                    <p class="text-red-600 mt-2">{{ session('login_error') }}</p>
                @endif
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Password</label>
                <div class="mt-1 relative">
                    <x-text-input id="password" class="block w-full rounded-md border-gray-300 shadow-sm pr-10" type="password" name="password" required autocomplete="current-password" />
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

            <div class="flex items-center justify-between">
                <label class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" name="remember" class="rounded text-indigo-600 focus:ring-indigo-500">
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Remember me</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm text-indigo-600 hover:underline" href="{{ route('password.request') }}">Forgot your password?</a>
                @endif
            </div>

            <div>
                <x-primary-button class="w-full justify-center">{{ __('Log in') }}</x-primary-button>
            </div>

            <div class="text-center">
                <p class="text-sm text-gray-600">Don't have an account? <a href="{{ route('register') }}" class="text-indigo-600 hover:underline">Register</a></p>
            </div>
        </form>
    </div>
</x-auth-layout>
