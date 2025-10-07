<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-gradient-to-br from-gray-50 via-white to-indigo-50 dark:from-gray-900 dark:via-gray-900 dark:to-black min-h-screen flex items-center justify-center">
        <div class="w-full max-w-4xl mx-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                {{-- Illustration / branding --}}
                <div class="hidden md:flex flex-col justify-center p-8 bg-cover bg-center rounded-xl" style="background-image:linear-gradient(180deg, rgba(245,245,245,0.9), rgba(255,255,255,0.6));">
                    <div class="flex items-center gap-3 mb-6">
                        <x-application-logo class="w-12 h-12 text-red-600" />
                        <div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ config('app.name', 'Hotel') }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Book rooms, manage reservations</div>
                        </div>
                    </div>

                    <div class="text-gray-700 dark:text-gray-300">
                        <h3 class="text-lg font-semibold mb-2">Fast. Reliable. Delightful.</h3>
                        <p class="text-sm mb-4">Manage bookings, invoices and customers all in one place. Our admin tools help you stay on top of occupancy and guest needs.</p>
                        <ul class="text-sm space-y-2">
                            <li class="flex items-start gap-2"><span class="text-red-600">●</span> Clean admin UI</li>
                            <li class="flex items-start gap-2"><span class="text-red-600">●</span> Secure authentication</li>
                            <li class="flex items-start gap-2"><span class="text-red-600">●</span> Mobile friendly</li>
                        </ul>
                    </div>
                </div>

                {{-- Form card --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 sm:p-8">
                    <div class="max-w-md mx-auto">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Password toggle handler (anonymous component can include small script) --}}
        <script>
            document.addEventListener('click', function(e){
                const toggle = e.target.closest('.toggle-password');
                if(!toggle) return;
                const target = document.querySelector(toggle.getAttribute('data-target'));
                if(!target) return;
                if(target.type === 'password') target.type = 'text'; else target.type = 'password';
                toggle.querySelectorAll('[data-icon]').forEach(el => el.classList.toggle('hidden'));
            });
        </script>
    </body>
</html>
