<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            .hotel-bg {
                background-image: linear-gradient(135deg, rgba(0,0,0,0.4), rgba(0,0,0,0.6)), url('/img/hero/hero-1.jpg');
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
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
            }
            .fade-in {
                animation: fadeIn 1s ease-in;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(30px); }
                to { opacity: 1; transform: translateY(0); }
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
                <div class="absolute top-40 right-16 w-16 h-16 bg-yellow-400/20 rounded-full floating-animation" style="animation-delay: -2s;"></div>
                <div class="absolute bottom-32 left-20 w-12 h-12 bg-pink-400/20 rounded-full floating-animation" style="animation-delay: -4s;"></div>
                
                {{-- Content overlay --}}
                <div class="relative z-10 flex flex-col justify-center p-12 text-white">
                    <div class="fade-in">
                        <div class="flex items-center gap-4 mb-8">
                            <x-application-logo class="w-16 h-16 text-white" />
                            <div>
                                <h1 class="text-4xl font-bold">{{ config('app.name', 'Luxury Hotel') }}</h1>
                                <p class="text-lg text-blue-100">Trải nghiệm nghỉ dưỡng tuyệt vời</p>
                            </div>
                        </div>
                        
                        <div class="space-y-6">
                            <h2 class="text-3xl font-semibold">Chào mừng trở lại!</h2>
                            <p class="text-lg text-blue-100 leading-relaxed">
                                Khám phá những căn phòng sang trọng và dịch vụ đẳng cấp thế giới. 
                                Đặt phòng ngay hôm nay để trải nghiệm sự thoải mái tuyệt đối.
                            </p>
                            
                            <div class="grid grid-cols-1 gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-green-500/20 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <span class="text-white">Phòng nghỉ cao cấp với view đẹp</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-blue-500/20 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <span class="text-white">Dịch vụ 24/7 chuyên nghiệp</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-purple-500/20 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
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
                        <x-application-logo class="w-12 h-12 text-blue-600" />
                        <div>
                            <div class="text-2xl font-bold text-gray-900">{{ config('app.name', 'Luxury Hotel') }}</div>
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
