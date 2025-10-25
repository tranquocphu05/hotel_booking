<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Ozia Hotel') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" media="print" onload="this.media='all'" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLMD/OOS7j5fVlP2WzT7mO3wB/g2wI6y4JpM3t5hE2gE3hB8jA4K5u7O6bN6a5W9fA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .hotel-bg {
            background-image: linear-gradient(135deg, rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.6)), url('/img/hero/hero-2.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .floating-animation {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .fade-in {
            animation: fadeIn 1s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Custom style cho icon vàng ánh kim loại */
        .gold-icon-bg {
            background-color: rgba(255, 215, 0, 0.2);
            /* Vàng kim loại nhạt */
        }

        .gold-icon-color {
            color: #FFD700;
            /* Vàng kim loại */
        }

        /* Màu logo trên nền sáng */
        .logo-dark-color {
            color: #B8860B;
            /* Darker Gold for contrast */
        }

        /* Tăng kích thước Font Awesome Icon */
        .fa-logo {
            font-size: 4rem;
            /* Tương đương w-16 h-16 */
        }

        .fa-logo-mobile {
            font-size: 3rem;
            /* Tương đương w-12 h-12 */
        }
    </style>
</head>

<body class="antialiased min-h-screen font-inter">
    <div class="min-h-screen flex">
        {{-- Left side with hotel image --}}
        <div class="hidden lg:flex lg:w-1/2 hotel-bg relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-900/20 to-purple-900/20"></div>

            {{-- Floating elements --}}
            <div class="absolute top-20 left-10 w-20 h-20 bg-white/10 rounded-full floating-animation"></div>
            <div class="absolute top-40 right-16 w-16 h-16 bg-yellow-400/20 rounded-full floating-animation"
                style="animation-delay: -2s;"></div>
            <div class="absolute bottom-32 left-20 w-12 h-12 bg-pink-400/20 rounded-full floating-animation"
                style="animation-delay: -4s;"></div>

            {{-- Content overlay --}}
            <div class="relative z-10 flex flex-col justify-center p-12 text-white">
                <div class="fade-in">
                    <div class="flex items-center gap-4 mb-8">
                        {{-- LOGO FONT AWESOME (Desktop) --}}
                        <i class="fa-solid fa-house-chimney-window fa-logo gold-icon-color"></i>
                        <div>
                            <h1 class="text-4xl font-bold">Ozia Hotel</h1>
                            <p class="text-lg text-blue-100">Trải nghiệm nghỉ dưỡng tuyệt vời</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <h2 class="text-3xl font-semibold">Chào mừng đến với Ozia Hotel !</h2>
                        <p class="text-lg text-blue-100 leading-relaxed">
                            Khám phá những căn phòng sang trọng và dịch vụ đẳng cấp thế giới.
                            Đặt phòng ngay hôm nay để trải nghiệm sự thoải mái tuyệt đối.
                        </p>

                        <div class="grid grid-cols-1 gap-4">
                            {{-- Dịch vụ 1: Vàng kim loại --}}
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 gold-icon-bg rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 gold-icon-color" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-white">Phòng nghỉ cao cấp với view đẹp</span>
                            </div>
                            {{-- Dịch vụ 2: Vàng kim loại --}}
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 gold-icon-bg rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 gold-icon-color" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-white">Dịch vụ 24/7 chuyên nghiệp</span>
                            </div>
                            {{-- Dịch vụ 3: Vàng kim loại --}}
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 gold-icon-bg rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 gold-icon-color" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-white">Ẩm thực sang trọng</span>
                            </div>
                            {{-- Dịch vụ 4: Vàng kim loại --}}
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 gold-icon-bg rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 gold-icon-color" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-white">Spa và thư giãn cao cấp</span>
                            </div>
                            {{-- Dịch vụ 5: Vàng kim loại --}}
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 gold-icon-bg rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 gold-icon-color" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-white">Đặt phòng dễ dàng và nhanh chóng</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right side with login form --}}
        <div class="flex-1 flex items-center justify-center p-8 bg-gradient-to-br from-slate-50 to-blue-50">
            <div class="w-full max-w-md">
                {{-- Mobile logo --}}
                <div class="lg:hidden flex items-center gap-3 mb-8 justify-center">
                    {{-- LOGO FONT AWESOME (Mobile) --}}
                    <i class="fa-solid fa-house-chimney-window fa-logo-mobile logo-dark-color"></i>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">Ozia Hotel</div>
                        <div class="text-sm text-gray-600">Đăng nhập tài khoản</div>
                    </div>
                </div>

                {{-- Form container --}}
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-8 fade-in">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>

    {{-- Password toggle handler --}}
    <script>
        document.addEventListener('click', function(e) {
            const toggle = e.target.closest('.toggle-password');
            if (!toggle) return;
            const target = document.querySelector(toggle.getAttribute('data-target'));
            if (!target) return;
            if (target.type === 'password') target.type = 'text';
            else target.type = 'password';
            toggle.querySelectorAll('[data-icon]').forEach(el => el.classList.toggle('hidden'));
        });
    </script>
</body>

</html>
