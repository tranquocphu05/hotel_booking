<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Ozia Hotel') }}</title>


    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" media="print" onload="this.media='all'" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLMD/OOS7j5fVlP2WzT7mO3wB/g2wI6y4JpM3t5hE2gE3hB8jA4K5u7O6bN6a5W9fA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ĐIỀU CHỈNH FONT CHỮ (MỚI) */
        body {
            /* Inter vẫn là font chính cho nội dung (độ rõ ràng cao) */
            font-family: 'Inter', sans-serif;
        }

        .display-font {
            /* Sử dụng Playfair Display cho các tiêu đề lớn (Sang trọng) */
            font-family: 'Playfair Display', serif;
        }

        /* BACKGROUND & EFFECTS */
        .hotel-bg {
            background-image: linear-gradient(135deg, rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.6)), url('/img/hero/xinweb.webp');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* ANIMATIONS */
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

        /* CUSTOM GOLD COLORS - Tinh chỉnh màu vàng sang trọng hơn */
        .gold-icon-bg {
            background-color: rgba(212, 175, 55, 0.3);
            /* Vàng đồng nhạt hơn, nổi bật hơn */
        }

        .gold-icon-color {
            color: #D4AF37;
            /* Vàng kim loại (Metallic Gold) */
            text-shadow: 0 0 5px rgba(255, 255, 255, 0.4);
            /* Thêm chút bóng mờ sáng cho icon */
        }

        .logo-dark-color {
            color: #B8860B;
            /* Darker Gold for contrast */
        }

        .fa-logo {
            font-size: 4.5rem;
            /* Tăng nhẹ kích thước Logo */
            line-height: 1;
        }

        .fa-logo-mobile {
            font-size: 3.5rem;
            /* Tăng nhẹ kích thước Logo Mobile */
            line-height: 1;
        }

        /* --- HIỆU ỨNG GẠCH CHÂN MƯỢT TỪ TRÁI SANG PHẢI (MỚI) --- */
        /* Thiết lập cho phần tử cha để định vị gạch chân */
        .service-item {
            position: relative;
            /* Quan trọng để định vị ::after */
        }

        /* Thẻ span chứa nội dung */
        .service-item span {
            position: relative;
            /* Quan trọng để gạch chân nằm dưới text */
            display: inline-block;
        }

        /* Tạo thanh gạch chân ẩn */
        .service-item span::after {
            content: '';
            position: absolute;
            width: 0;
            /* Bắt đầu với chiều rộng 0 */
            height: 2px;
            bottom: -3px;
            /* Đặt dưới chữ một chút */
            left: 0;
            background-color: #FFD700;
            /* Màu vàng gạch chân */
            transition: width 0.35s cubic-bezier(0.25, 0.8, 0.25, 1);
            /* Hiệu ứng chuyển động mượt mà hơn */
        }

        /* Hiệu ứng khi di chuột qua: gạch chân đi từ trái sang phải */
        .service-item:hover span::after {
            width: 100%;
            /* Kéo dài chiều rộng từ 0% lên 100% */
        }

        /* HIỆU ỨNG HOVER BAN ĐẦU CỦA BẠN (Đã sửa để không xung đột với gạch chân) */
        .service-item:hover .gold-icon-bg {
            background-color: #D4AF37;
            /* Đổi nền sang màu vàng đậm khi hover */
            transition: background-color 0.3s ease;
        }

        .service-item:hover .gold-icon-color {
            color: white;
            /* Đổi màu icon sang trắng khi hover */
            text-shadow: none;
            transition: color 0.3s ease;
        }

        .service-item:hover span {
            color: #FFD700;
            /* Nhấn mạnh chữ màu vàng */
            text-shadow: 0 0 1px rgba(255, 215, 0, 0.5);
            transition: color 0.3s ease;
        }
    </style>
</head>

<body class="antialiased min-h-screen font-inter">
    <div class="min-h-screen flex">
        {{-- Left side with hotel image --}}
        <div class="hidden lg:flex lg:w-1/2 hotel-bg relative overflow-hidden">
            {{-- Đổi lớp phủ màu để tăng độ sâu và tương phản --}}
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-900/30 to-slate-900/60"></div>

            {{-- Floating elements --}}
            <div class="absolute top-20 left-10 w-20 h-20 bg-white/10 rounded-full floating-animation">
            </div>
            <div class="absolute top-40 right-16 w-16 h-16 bg-yellow-400/20 rounded-full floating-animation"
                style="animation-delay: -2s;"></div>
            <div class="absolute bottom-32 left-20 w-12 h-12 bg-pink-400/20 rounded-full floating-animation"
                style="animation-delay: -4s;"></div>

            {{-- Content overlay --}}
            <div class="relative z-10 flex flex-col justify-center p-16 text-white">
                <div class="fade-in">
                    <div class="flex items-center gap-5 mb-10">
                        {{-- LOGO FONT AWESOME (Desktop) --}}
                        <i class="fa-solid fa-house-chimney-window fa-logo gold-icon-color drop-shadow-lg"></i>
                        <div>
                            <h1 class="text-5xl font-extrabold tracking-tight display-font">Ozia Hotel</h1>
                            <p class="text-xl text-yellow-200 mt-1">Trải nghiệm nghỉ dưỡng
                                tuyệt vời</p>
                        </div>
                    </div>

                    <div class="space-y-8">
                        <h2 class="text-4xl font-light tracking-wide border-b border-yellow-500/50 pb-2 display-font">
                            Chào mừng đến với Ozia Hotel !</h2>
                        <p class="text-lg text-gray-200 leading-relaxed drop-shadow-md">
                            Khám phá những căn phòng sang trọng và dịch vụ đẳng cấp thế
                            giới.
                            Đặt phòng ngay hôm nay để trải nghiệm sự thoải mái tuyệt đối.
                        </p>

                       <ul class="service-list grid grid-cols-1 gap-5">
    <li class="flex items-center gap-3 service-item transition-transform duration-300 hover:scale-[1.03]">
        <div class="w-8 h-8 gold-icon-bg rounded-full flex items-center justify-center shadow-lg icon-wrapper">
            <svg class="w-4 h-4 gold-icon-color" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    clip-rule="evenodd"></path>
            </svg>
        </div>
        <span class="text-white font-medium text-lg">Phòng nghỉ cao cấp với view đẹp</span>
    </li>

    <li class="flex items-center gap-3 service-item transition-transform duration-300 hover:scale-[1.03]">
        <div class="w-8 h-8 gold-icon-bg rounded-full flex items-center justify-center shadow-lg icon-wrapper">
            <svg class="w-4 h-4 gold-icon-color" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    clip-rule="evenodd"></path>
            </svg>
        </div>
        <span class="text-white font-medium text-lg">Dịch vụ 24/7 chuyên nghiệp</span>
    </li>

    <li class="flex items-center gap-3 service-item transition-transform duration-300 hover:scale-[1.03]">
        <div class="w-8 h-8 gold-icon-bg rounded-full flex items-center justify-center shadow-lg icon-wrapper">
            <svg class="w-4 h-4 gold-icon-color" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    clip-rule="evenodd"></path>
            </svg>
        </div>
        <span class="text-white font-medium text-lg">Ẩm thực sang trọng</span>
    </li>

    <li class="flex items-center gap-3 service-item transition-transform duration-300 hover:scale-[1.03]">
        <div class="w-8 h-8 gold-icon-bg rounded-full flex items-center justify-center shadow-lg icon-wrapper">
            <svg class="w-4 h-4 gold-icon-color" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    clip-rule="evenodd"></path>
            </svg>
        </div>
        <span class="text-white font-medium text-lg">Spa và thư giãn cao cấp</span>
    </li>

    <li class="flex items-center gap-3 service-item transition-transform duration-300 hover:scale-[1.03]">
        <div class="w-8 h-8 gold-icon-bg rounded-full flex items-center justify-center shadow-lg icon-wrapper">
            <svg class="w-4 h-4 gold-icon-color" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    clip-rule="evenodd"></path>
            </svg>
        </div>
        <span class="text-white font-medium text-lg">Đặt phòng dễ dàng và nhanh chóng</span>
    </li>
</ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right side with form --}}
        <div class="flex-1 flex items-center justify-center p-8 bg-gradient-to-br from-slate-50 to-blue-50">
            <div class="w-full max-w-md">
                {{-- Mobile logo --}}
                <div class="lg:hidden flex items-center gap-3 mb-10 justify-center">
                    <i class="fa-solid fa-house-chimney-window fa-logo-mobile logo-dark-color"></i>
                    <div>
                        <div class="text-3xl font-extrabold text-gray-900 display-font">Ozia Hotel</div>
                        <div class="text-md text-gray-600">Đăng nhập tài khoản</div>
                    </div>
                </div>

                {{-- Form container --}}
                <div class="bg-white/90 backdrop-blur-md rounded-2xl shadow-2xl border border-white/40 p-10 fade-in">
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
