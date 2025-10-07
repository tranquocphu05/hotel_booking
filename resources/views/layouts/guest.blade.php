<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gradient-to-b from-white via-gray-50 to-gray-100 dark:from-gray-900 dark:via-gray-900 dark:to-black">
        <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-xl bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                <div class="p-8 sm:p-10">
                    <div class="flex items-center gap-3 mb-6">
                        <x-application-logo class="w-10 h-10 text-red-600 dark:text-red-400" />
                        <div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ config('app.name', 'Laravel') }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Hotel booking management</div>
                        </div>
                    </div>

                    <div class="mx-auto">
                        {{ $slot }}

                        <p class="mt-6 text-center text-xs text-gray-500">
                            By continuing you agree to our
                            <a href="#" class="text-indigo-600 hover:underline">Terms</a> and
                            <a href="#" class="text-indigo-600 hover:underline">Privacy Policy</a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Small JS for toggling password visibility used by auth forms --}}
        <script>
            document.addEventListener('click', function(e){
                const toggle = e.target.closest('.toggle-password');
                if(!toggle) return;
                const target = document.querySelector(toggle.getAttribute('data-target'));
                if(!target) return;
                if(target.type === 'password'){
                    target.type = 'text';
                } else {
                    target.type = 'password';
                }
                // swap icons if present
                toggle.querySelectorAll('[data-icon]').forEach(el => el.classList.toggle('hidden'));
            });
        </script>
    </body>
</html>
