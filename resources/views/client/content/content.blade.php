<!-- Thông báo thành công -->
@if (session('success'))
    <div class="fixed top-20 right-4 z-50 max-w-md animate-slide-in-right" id="successToast">
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-4 rounded-lg shadow-2xl">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="font-bold text-lg mb-1">Thành công!</h3>
                    <p class="text-sm text-white/90">{{ session('success') }}</p>
                </div>
                <button onclick="closeToast()" class="ml-4 text-white/80 hover:text-white transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        function closeToast() {
            const toast = document.getElementById('successToast');
            if (toast) {
                toast.classList.add('animate-slide-out-right');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }
        }

        // Auto close after 5 seconds
        setTimeout(() => {
            closeToast();
        }, 5000);
    </script>

    <style>
        @keyframes slide-in-right {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slide-out-right {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        .animate-slide-in-right {
            animation: slide-in-right 0.3s ease-out;
        }

        .animate-slide-out-right {
            animation: slide-out-right 0.3s ease-in;
        }
    </style>
@endif

<section id="about-us" class="w-full flex justify-center bg-white py-16">
    <div class="max-w-7xl w-full px-4">
        <div class="flex flex-col lg:flex-row items-center justify-between">
            <!-- Nội dung bên trái -->
            <div class="lg:w-3/5 text-center mb-8 lg:mb-0">
                <p class="text-sm uppercase tracking-widest text-[#D4AF37] mb-2 font-medium">VỀ CHÚNG TÔI</p>
                <h2 class="text-5xl font-serif font-extralight text-gray-800 mb-6 leading-tight">
                    Khách sạn OZIA HOTEL
                </h2>
                <p class="text-gray-600 mb-4 max-w-2xl mx-auto">
                    Ozia Hotel là một trang web đặt phòng trực tuyến hàng đầu. Chúng tôi đam mê du lịch.
                    Mỗi ngày, chúng tôi truyền cảm hứng và tiếp cận hàng triệu du khách trên toàn cầu
                    thông qua các trang web của riêng mình.
                </p>
                <p class="text-gray-600 mb-8 max-w-2xl mx-auto">
                    Vì vậy, khi bạn muốn đặt một khách sạn hoàn hảo, thuê nhà nghỉ dưỡng, khu nghỉ dưỡng,
                    căn hộ, nhà khách, hay nhà trên cây, chúng tôi đều có thể đáp ứng.
                </p>
                <a href="{{ route('client.gioithieu') }}"
                    class="inline-block font-semibold uppercase tracking-wider text-sm px-6 py-2.5 rounded-full
                    border border-[#d4af37] text-[#d4af37]
                    transition-all duration-500 ease-out transform
                    hover:bg-gradient-to-r hover:from-[#ffef9f] hover:to-[#d4af37]
                    hover:text-white hover:shadow-[0_0_20px_rgba(212,175,55,0.6)]
                    hover:-translate-y-1 hover:border-transparent">
                    XEM THÊM
                </a>
            </div>

            <!-- Ảnh bên phải -->
            <div class="lg:w-2/5 flex justify-center lg:justify-end space-x-4">
                <img src="{{ asset('img/about/about-1.jpg') }}" alt="Tháp truyền thống"
                    class="w-40 h-64 md:w-56 md:h-80 object-cover rounded shadow-xl transform transition duration-500 ease-in-out hover:scale-[1.05] hover:shadow-2xl hover:rotate-1">
                <img src="{{ asset('img/about/about-2.jpg') }}" alt="Chi tiết Nhà hàng"
                    class="w-40 h-64 md:w-56 md:h-80 object-cover rounded shadow-xl transform transition duration-500 ease-in-out hover:scale-[1.05] hover:shadow-2xl hover:-rotate-1">
            </div>
        </div>
    </div>
</section>


<section id="services" class="relative">
    <style>
        :root {
            --luxury-gold: #d4af37;
            --gold-gradient: radial-gradient(circle at center, #ffd700, #b8860b);
            --dark-overlay: rgba(0, 0, 0, 0.6);
            --light-text: #f9f9f9;
            --card-bg: rgba(255, 255, 255, 0.08);
            --hover-shadow: rgba(212, 175, 55, 0.3);
            --initial-bright-gold: #ffeb3b;
            --icon-size: 26px;
        }

        #services {
            background: url('{{ asset('img/hero/hero-1.jpg') }}') center/cover no-repeat;
            position: relative;
            z-index: 1;
        }

        #services::before {
            content: "";
            position: absolute;
            inset: 0;
            background: var(--dark-overlay);
            z-index: -1;
        }

        .service-card {
            transition: transform .4s ease, box-shadow .4s ease, background .4s ease;
            will-change: transform, box-shadow;
            border-radius: 16px;
            padding: 1.8rem;
            background: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 0 25px var(--hover-shadow);
            background: rgba(255, 255, 255, 0.15);
        }

        /* ======================================================= */
        /* VÒNG TRÒN VÀNG + HIỆU ỨNG */
        /* ======================================================= */
        .service-icon-wrap {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            margin: 0 auto 1rem;
            background: var(--gold-gradient);
            border: 2px solid var(--luxury-gold);
            box-shadow:
                0 0 15px rgba(255, 215, 0, 0.4),
                inset 0 0 6px rgba(255, 255, 255, 0.4);
            transition: all .4s ease;
            position: relative;
        }

        /* Vòng sáng ngoài */
        .service-icon-wrap::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 50%;
            box-shadow: 0 0 25px rgba(255, 215, 0, 0.8);
            opacity: 0;
            transition: opacity .4s ease;
        }

        .service-card:hover .service-icon-wrap::after {
            opacity: 1;
            animation: rotate-glow 2s linear infinite;
        }

        @keyframes rotate-glow {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .service-card:hover .service-icon-wrap {
            transform: scale(1.1);
            box-shadow:
                0 0 25px rgba(255, 215, 0, 0.7),
                inset 0 0 10px rgba(255, 255, 255, 0.5);
        }

        .service-icon-wrap svg {
            width: var(--icon-size);
            height: var(--icon-size);
            fill: #fff;
            filter: drop-shadow(0 0 6px rgba(0, 0, 0, 0.5));
            transition: all .4s ease;
        }

        .service-card:hover .service-icon-wrap svg {
            transform: scale(1.1);
            fill: var(--light-text);
        }

        /* ======================================================= */
        /* TEXT */
        /* ======================================================= */
        .service-title {
            color: var(--light-text);
            font-weight: 700;
            margin-top: 0.5rem;
            font-size: 1.1rem;
        }

        .service-desc {
            color: rgba(255, 255, 255, 0.8);
            font-size: .95rem;
            margin-top: 0.4rem;
        }

        .section-title {
            color: #fff;
        }

        @media (max-width: 768px) {
            .service-card {
                padding: 1.2rem;
            }

            .service-icon-wrap {
                width: 54px;
                height: 54px;
            }
        }
    </style>
    <div class="text-center container mx-auto px-4 py-20 relative z-10">
        <p class="text-sm uppercase tracking-widest text-[#D4AF37] mb-2 font-light">
            DỊCH VỤ CỦA CHÚNG TÔI
        </p>
        <h2 class="text-3xl font-bold section-title mb-12">
            Khám Phá Các Dịch Vụ Của Chúng Tôi
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            @php
                // MẢNG DỊCH VỤ VÀ SVG ICONS
                $services = [
                    [
                        // Kế Hoạch Du Lịch (Compass - La Bàn)
                        'icon_svg' =>
                            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 0a256 256 0 1 0 0 512A256 256 0 1 0 256 0zM127 64c-35.3 0-64 28.7-64 64V384c0 35.3 28.7 64 64 64H385c35.3 0 64-28.7 64-64V128c0-35.3-28.7-64-64-64H127zM384 192a32 32 0 1 1 0 64 32 32 0 1 1 0-64zM256 320c-17.7 0-32 14.3-32 32v64H160c-17.7 0-32 14.3-32 32s14.3 32 32 32H352c17.7 0 32-14.3 32-32s-14.3-32-32-32H288V352c0-17.7-14.3-32-32-32z"/></svg>',
                        'title' => 'Kế Hoạch Du Lịch',
                        'desc' => 'Chúng tôi sẽ giúp bạn lên lịch trình tốt nhất.',
                    ],
                    [
                        // Dịch Vụ Ăn Uống (Mug Hot - Cốc Nóng)
                        'icon_svg' =>
                            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M336 64a48 48 0 1 0 0 96 48 48 0 1 0 0-96zM32 64a48 48 0 1 0 0 96 48 48 0 1 0 0-96zM192 0c-44.2 0-80 35.8-80 80V256H304V80c0-44.2-35.8-80-80-80zM352 288v48c0 26.5-21.5 48-48 48H128c-26.5 0-48-21.5-48-48V288c0-17.7 14.3-32 32-32h224c17.7 0 32 14.3 32 32zM288 448c0 17.7-14.3 32-32 32H128c-17.7 0-32-14.3-32-32V416h192v32z"/></svg>',
                        'title' => 'Dịch Vụ Ăn Uống',
                        'desc' => 'Phục vụ bữa ăn ngon miệng tại phòng hoặc nhà hàng.',
                    ],
                    [
                        // Hướng Dẫn Đặt Phòng (Lock - Khóa)
                        'icon_svg' =>
                            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M400 224h-24v-72C376 68.2 307.8 0 224 0S72 68.2 72 152v72H48c-26.5 0-48 21.5-48 48v192c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V272c0-26.5-21.5-48-48-48zm-104 0H152v-72c0-39.7 32.3-72 72-72s72 32.3 72 72v72z"/></svg>',
                        'title' => 'Hướng Dẫn Đặt Phòng',
                        'desc' => 'Quy trình đặt phòng nhanh chóng và dễ dàng.',
                    ],
                    [
                        // Bãi Đậu Xe (Car - Xe Hơi)
                        'icon_svg' =>
                            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M499.7 114.7l-48.4-56.7c-3.1-3.6-7.8-5.6-12.7-5.6h-34.6c-11.7 0-21.2 9.5-21.2 21.2V128H128V21.2c0-11.7-9.5-21.2-21.2-21.2H72.3c-4.9 0-9.6 2-12.7 5.6L11.7 114.7c-7.7 9-11.7 20.3-11.7 32V460c0 28 22 52 50 52h412c28 0 50-24 50-52V146.7c0-11.7-4-22.9-11.7-32zM336 288c-17.7 0-32-14.3-32-32s14.3-32 32-32 32 14.3 32 32-14.3 32-32 32zm-160 0c-17.7 0-32-14.3-32-32s14.3-32 32-32 32 14.3 32 32-14.3 32-32 32z"/></svg>',
                        'title' => 'Bãi Đậu Xe',
                        'desc' => 'Bãi đậu xe rộng rãi và an toàn 24/7.',
                    ],
                    [
                        // Dịch Vụ Phòng (Door Open - Cửa Mở)
                        'icon_svg' =>
                            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M384 64H128C57.3 64 0 121.3 0 192v256c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V192c0-70.7-57.3-128-128-128zM48 448V192c0-44.1 35.9-80 80-80H384c44.1 0 80 35.9 80 80v256H48zM240 288V224c0-8.8 7.2-16 16-16s16 7.2 16 16v64c0 8.8-7.2 16-16 16s-16-7.2-16-16z"/></svg>',
                        'title' => 'Dịch Vụ Phòng',
                        'desc' => 'Dịch vụ dọn phòng hàng ngày theo yêu cầu.',
                    ],
                    [
                        // Phòng Tập & Spa (Dumbbell & Heart - Tạ và Trái Tim)
                        'icon_svg' =>
                            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path d="M38.8 5.1C28.4-3.1 13.3-1.2 5.1 9.2S-1.2 38.8 9.2 47.1L192 192 9.2 336.9c-10.4 8.2-12.3 23.3-4.1 33.7s23.3 12.3 33.7 4.1L192 320 384 464c10.4 8.2 25.5 6.4 33.7-4.1s6.4-25.5-4.1-33.7L224 304l160-160c10.4-8.2 12.3-23.3 4.1-33.7S399.5 76.4 389.1 84.6L224 224 38.8 5.1zM512 96a96 96 0 1 0 0 192 96 96 0 1 0 0-192zM512 320a96 96 0 1 0 0 192 96 96 0 1 0 0-192z"/></svg>',
                        'title' => 'Phòng Tập & Spa',
                        'desc' => 'Thư giãn với dịch vụ spa và phòng tập hiện đại.',
                    ],
                    [
                        // Bar & Ăn Uống (Wine Glass - Ly Rượu)
                        'icon_svg' =>
                            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M320 0c-17.7 0-32 14.3-32 32s14.3 32 32 32h72.2c25.4 0 46.1 20.7 46.1 46.1V400H159.7V144c0-13.3-10.7-24-24-24s-24 10.7-24 24V400H64c-17.7 0-32 14.3-32 32s14.3 32 32 32H512c17.7 0 32-14.3 32-32s-14.3-32-32-32H448V128c0-50.5-41-91.5-91.5-91.5H320zM32 256c0 17.7-14.3 32-32 32s-32-14.3-32-32V128C0 57.3 57.3 0 128 0H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H128c-35.3 0-64 28.7-64 64V256z"/></svg>',
                        'title' => 'Bar & Ăn Uống',
                        'desc' => 'Thưởng thức đồ uống và món ăn đặc sắc.',
                    ],
                    [
                        // Hồ Bơi (Swimmer - Người Bơi)
                        'icon_svg' =>
                            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M512 256C512 397.4 397.4 512 256 512S0 397.4 0 256 114.6 0 256 0s256 114.6 256 256zM256 48A208 208 0 1 0 256 464 208 208 0 1 0 256 48zM312 328c0-26.5-21.5-48-48-48s-48 21.5-48 48 21.5 48 48 48 48-21.5 48-48zm-112-96c0 26.5-21.5 48-48 48s-48-21.5-48-48 21.5-48 48-48 48 21.5 48 48zm224 0c0 26.5-21.5 48-48 48s-48-21.5-48-48 21.5-48 48-48 48 21.5 48 48z"/></svg>',
                        'title' => 'Hồ Bơi',
                        'desc' => 'Hồ bơi trong nhà và ngoài trời sang trọng.',
                    ],
                ];
            @endphp

            @foreach ($services as $service)
                <div class="service-card text-center">
                    <div class="service-icon-wrap mx-auto">
                        {!! $service['icon_svg'] !!}
                    </div>
                    <h4 class="service-title text-xl">{{ $service['title'] }}</h4>
                    <p class="service-desc text-sm">{{ $service['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="rooms-gallery" class="py-20 bg-gray-50">
    <style>
        /* CSS tùy chỉnh để làm cho hiệu ứng hover ưng mắt hơn */
        .room-card:hover .room-content-hover {
            opacity: 1;
            /* Giữ nguyên vị trí ban đầu để fade-in mượt mà */
            transform: translateY(0);
        }

        .room-card .room-content-hover {
            /* Tăng tốc độ mượt mà hơn */
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
            /* Đảm bảo nội dung luôn hiển thị nhưng mờ đi, không bị dịch chuyển */
            transform: translateY(0);
        }

        /* Lớp phủ sáng rực khi hover */
        .room-card:hover .overlay-hover {
            /* Tăng độ mờ mạnh mẽ */
            background-color: rgba(0, 0, 0, 0.85);
            /* Tạo hiệu ứng mờ nhẹ nhàng */
            backdrop-filter: blur(2px);
        }

        /* Ẩn các chi tiết danh giá, chỉ giữ lại mô tả/tiện nghi */
        .room-details-unhover {
            transition: opacity 0.5s ease-in;
        }
    </style>

    <div class="text-center mb-12">
        <p class="text-sm uppercase tracking-widest text-[#D4AF37] mb-2 font-light">
            PHÒNG CỦA CHÚNG TÔI
        </p>
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800">
            Trải Nghiệm Các Loại Phòng Sang Trọng Của Chúng Tôi
        </h2>
    </div>

    <ul class="flex flex-wrap gap-4 justify-center px-4 sm:px-6">
        @forelse ($loaiPhongs as $phong)
            <li class="relative group overflow-hidden bg-white shadow-md hover:shadow-xl transition-all duration-500 rounded-xl room-card"
                style="width: 350px; height: 460px;">

                {{-- Ảnh phòng --}}
                <img src="{{ asset($phong->anh ?: 'img/room/room-1.jpg') }}" alt="{{ $phong->ten_loai }}"
                    class="w-full h-full object-cover transform transition-transform duration-700 ease-in-out group-hover:scale-110">

                {{-- Overlay --}}
                <div
                    class="absolute inset-0 bg-black bg-opacity-40 transition-all duration-500 group-hover:bg-opacity-85 overlay-hover">
                </div>

                {{-- Nội dung khi chưa hover (Giá ở dưới) --}}
                <div
                    class="absolute inset-0 flex items-end p-6 text-white transition-all duration-500 group-hover:opacity-0 room-details-unhover">
                    <div>
                        <h4 class="text-2xl font-bold mb-1 font-serif">{{ $phong->ten_loai }}</h4>

                        @if ($phong->phongs && $phong->phongs->count() > 0)
                            @php $firstRoom = $phong->phongs->first(); @endphp
                            @if ($firstRoom->hasPromotion())
                                <div class="flex items-center space-x-2">
                                    <span class="text-lg text-gray-300 line-through">
                                        {{ number_format($firstRoom->gia_goc_hien_thi, 0, ',', '.') }}đ
                                    </span>
                                    <span class="text-xl text-[#D4AF37] font-semibold">
                                        {{ number_format($firstRoom->gia_hien_thi, 0, ',', '.') }}đ
                                    </span>
                                </div>
                            @else
                                <span class="text-xl text-[#D4AF37] font-semibold">
                                    {{ number_format($firstRoom->gia_hien_thi, 0, ',', '.') }}đ
                                </span>
                            @endif
                        @else
                            <span class="text-xl text-[#D4AF37] font-semibold">
                                {{ number_format($phong->gia_co_ban, 0, ',', '.') }}đ
                            </span>
                        @endif
                        <span class="text-sm text-gray-300">/ Đêm</span>
                    </div>
                </div>

                {{-- Nội dung khi hover (Tên, Giá, UL/LI tiện nghi, Đánh giá sao) --}}
                <div class="absolute inset-0 flex flex-col justify-start p-6 text-white opacity-0 room-content-hover">

                    {{-- Tên + Giá --}}
                    <div class="mb-4">
                        <h4 class="text-3xl font-serif font-bold mb-1">{{ $phong->ten_loai }}</h4>

                        @if ($phong->phongs && $phong->phongs->count() > 0)
                            @php $firstRoom = $phong->phongs->first(); @endphp
                            @if ($firstRoom->hasPromotion())
                                <div class="flex items-center space-x-2">
                                    <span class="text-xl text-gray-300 line-through">
                                        {{ number_format($firstRoom->gia_goc_hien_thi, 0, ',', '.') }}đ
                                    </span>
                                    <span class="text-2xl text-[#FFD700] font-bold">
                                        {{ number_format($firstRoom->gia_hien_thi, 0, ',', '.') }}đ
                                    </span>
                                </div>
                            @else
                                <span class="text-2xl text-[#FFD700] font-bold">
                                    {{ number_format($firstRoom->gia_hien_thi, 0, ',', '.') }}đ
                                </span>
                            @endif
                        @else
                            <span class="text-2xl text-[#FFD700] font-bold">
                                {{ number_format($phong->gia_co_ban, 0, ',', '.') }}đ
                            </span>
                        @endif
                        <span class="text-base text-gray-300">/ Đêm</span>
                    </div>

                    <div class="mt-2 mb-4">
                        <p class="uppercase text-sm tracking-wider text-[#D4AF37] mb-3">Tiện Nghi Chính</p>
                        <ul class="space-y-2 text-sm text-gray-100">

                            @php
                                // Lấy nội dung mô tả
                                $moTaRaw = $phong->mo_ta ?? '';
                                // Tách chuỗi bằng dấu phẩy (,)
                                $tienNghiList = array_filter(array_map('trim', explode(',', $moTaRaw)));
                            @endphp

                            {{-- LẶP QUA CÁC MỤC ĐÃ TÁCH (ĐÃ CHỈNH SỬA: Lấy tối đa 8 mục) --}}
                            @forelse (collect($tienNghiList)->take(8) as $tienNghiItem)
                                <li class="flex items-center">
                                    {{-- Dùng icon check mặc định --}}
                                    <i class="fas fa-check text-[#FFD700] mr-2"></i>
                                    {{ $tienNghiItem }}
                                </li>
                            @empty
                                <li class="text-gray-400 italic">Chưa có thông tin tiện nghi được cấu hình.</li>
                            @endforelse
                        </ul>
                    </div>

                    {{-- Khôi phục Đánh giá sao --}}
                    <div class="mt-auto pt-4 border-t border-gray-700/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="flex items-center">
                                    {{-- Vòng lặp hiển thị sao --}}
                                    @for ($i = 1; $i <= 5; $i++)
                                        @if ($i <= floor($phong->diem_danh_gia ?? 4.5))
                                            <i class="fas fa-star text-yellow-400 text-sm"></i>
                                        @elseif($i - 0.5 <= ($phong->diem_danh_gia ?? 4.5))
                                            <i class="fas fa-star-half-alt text-yellow-400 text-sm"></i>
                                        @else
                                            <i class="far fa-star text-gray-300 text-sm"></i>
                                        @endif
                                    @endfor
                                </div>
                                <span
                                    class="text-base font-bold text-white">{{ number_format($phong->diem_danh_gia ?? 4.5, 1) }}</span>
                            </div>
                            <span class="text-sm text-gray-300">{{ $phong->so_luong_danh_gia ?? rand(20, 150) }} đánh
                                giá</span>
                        </div>
                    </div>
                </div>
            </li>
        @empty
            <li class="w-full text-center py-16">
                <div class="text-gray-500 text-lg">
                    <i class="fas fa-bed text-4xl mb-4"></i>
                    <p>Hiện chưa có loại phòng nào được hiển thị.</p>
                    <p class="text-sm mt-2">Vui lòng quay lại sau hoặc liên hệ với chúng tôi.</p>
                </div>
            </li>
        @endforelse
    </ul>
</section>

<section id="testimonials" class="py-16 bg-gray-50 my-16 rounded-lg">
    <div class="text-center container mx-auto px-4">
        <p class="text-sm uppercase tracking-widest text-[#D4AF37] mb-2">Ý KIẾN KHÁCH HÀNG</p>
        <h2 class="text-3xl font-bold text-gray-800 mb-8">Khách Hàng Nói Gì?</h2>

        @if (isset($comments) && $comments->count() > 0)
            <div class="swiper testimonialSwiper w-full max-w-4xl mx-auto relative">
                <div class="swiper-wrapper">
                    @foreach ($comments as $comment)
                        <div
                            class="swiper-slide bg-white rounded-2xl shadow-md p-8 flex flex-col items-center text-center transition duration-300 hover:shadow-lg">

                            {{-- Ảnh đại diện --}}
                            @if (!empty($comment->user->avatar))
                                <img src="{{ asset('storage/' . $comment->user->avatar) }}" alt="Avatar người dùng"
                                    class="w-20 h-20 rounded-full object-cover mb-4 shadow-md border-2 border-yellow-400 hover:scale-105 transition-transform duration-300">
                            @elseif(!empty($comment->img))
                                <img src="{{ asset('storage/' . $comment->img) }}" alt="Ảnh đánh giá"
                                    class="w-20 h-20 rounded-full object-cover mb-4 shadow-md border-2 border-yellow-400 hover:scale-105 transition-transform duration-300">
                            @else
                                <img src="{{ asset('img/default-avatar.png') }}" alt="Avatar mặc định"
                                    class="w-20 h-20 rounded-full object-cover mb-4 shadow-md border-2 border-gray-300">
                            @endif

                            {{-- Nội dung đánh giá --}}
                            <p class="italic text-gray-600 text-lg leading-relaxed mb-6 max-w-2xl">
                                “{{ $comment->noi_dung }}”
                            </p>

                            {{-- Số sao --}}
                            <div class="flex justify-center text-yellow-500 mb-3">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i
                                        class="fas fa-star {{ $i <= $comment->so_sao ? 'text-yellow-500' : 'text-gray-300' }}"></i>
                                @endfor
                            </div>

                            {{-- Người dùng & ngày đánh giá --}}
                            <p class="font-semibold text-gray-800">
                                — {{ $comment->user->username ?? ($comment->user->name ?? 'Ẩn danh') }}
                            </p>
                            <p class="text-sm text-gray-500 mt-2">
                                {{ optional($comment->ngay_danh_gia)->format('d/m/Y') }}
                            </p>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination (Giữ lại phân trang nếu muốn) --}}
                <div class="swiper-pagination mt-6"></div>
            </div>
        @else
            <p class="text-gray-500 italic">Chưa có đánh giá 5 sao nào được hiển thị.</p>
        @endif
    </div>
</section>

<section class="relative bg-center bg-cover text-center text-white py-32 mb-20"
    style="background-image: url('{{ asset('img/video-bg.jpg') }}')">
    <div class="absolute inset-0 bg-black/60"></div>
    <div class="relative z-10 max-w-3xl mx-auto">
        <h2 class="text-4xl font-extrabold mb-6">Khám phá khách sạn & dịch vụ của chúng tôi</h2>
        <p class="text-gray-200 mb-10 text-lg leading-relaxed">
            Tận hưởng kỳ nghỉ tuyệt vời cùng không gian sang trọng và dịch vụ đẳng cấp.
        </p>

        <!-- Nút xem video -->
        <button onclick="openVideoPopup()"
            class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-blue-600 hover:bg-red-600 shadow-lg transition transform hover:scale-110">
            <svg xmlns="http://www.w3.org/2000/svg" fill="white" viewBox="0 0 24 24" class="w-8 h-8 ml-1">
                <path d="M3 22v-20l18 10-18 10z" />
            </svg>
        </button>
    </div>
</section>

<!-- Popup Video -->
<div id="videoPopup"
    class="fixed inset-0 hidden z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm transition-opacity duration-300">

    <div
        class="relative w-[90vw] max-w-[1000px] overflow-visible shadow-[0_0_60px_rgba(255,255,255,0.1)] border border-white">

        <div class="relative bg-black overflow-hidden">
            <video id="popupVideo" controls preload="auto" playsinline
                class="w-full h-full cursor-pointer z-10">
                <source src="{{ asset('videos/video-khach-san.mp4') }}" type="video/mp4">
                Trình duyệt của bạn không hỗ trợ video.
            </video>
        </div>

        <button onclick="closeVideoPopup()"
            class="absolute top-3 right-3 bg-white/90 text-gray-700 rounded-full w-10 h-10 flex items-center justify-center
                   shadow-xl border border-gray-200 backdrop-blur-md hover:bg-red-500 hover:text-white hover:scale-110
                   transition-all duration-200 z-20">
            ✕
        </button>
    </div>
</div>
<script>
    function openVideoPopup() {
        const popup = document.getElementById('videoPopup');
        const video = document.getElementById('popupVideo');
        popup.classList.remove('hidden');
        setTimeout(() => popup.classList.add('opacity-100'), 10);
        video.currentTime = 0;
        video.play();
    }

    function closeVideoPopup() {
        const popup = document.getElementById('videoPopup');
        const video = document.getElementById('popupVideo');
        video.pause();
        popup.classList.remove('opacity-100');
        setTimeout(() => popup.classList.add('hidden'), 200);
    }
</script>
<style>
    #videoPopup {
        opacity: 0;
    }

    #videoPopup.opacity-100 {
        opacity: 1;
    }
</style>

<section id="blog-events">
    <section class="container mx-auto px-4 py-8">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">Ưu đãi Cuối Tuần</h2>
        <p class="text-gray-600 mb-8">Tiết kiệm cho kỳ nghỉ từ ngày 24 tháng 10 đến ngày 26 tháng 10</p>

        {{-- Swiper Container --}}
        <div class="swiper weekendDealsSwiper relative">
            <div class="swiper-wrapper">
                @foreach ($loaiPhongs as $phong)
                    <div class="swiper-slide">
                        <a href="{{ route('client.phong.show', $phong->id) }}"
                            class="block bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm relative group cursor-pointer
                   hover:shadow-xl hover:scale-[1.02] transition duration-300 ease-in-out">
                            <div class="relative">
                                <img src="{{ asset($phong->anh ?: 'img/gallery/gallery-1.jpg') }}"
                                    alt="{{ $phong->ten_loai }}" class="w-full h-48 object-cover">

                                <button
                                    class="absolute top-4 right-4 bg-white p-2 rounded-full shadow-md text-gray-700 hover:text-red-500 transition duration-300 z-10"
                                    onclick="event.preventDefault(); event.stopPropagation();">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </button>

                                @if ($phong->diem_danh_gia >= 4.8)
                                    <span
                                        class="absolute bottom-0 left-0 bg-yellow-500 text-white text-xs font-semibold px-2 py-1 rounded-tr-lg">Genius</span>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="text-lg font-bold text-gray-800 mb-1 leading-tight">{{ $phong->ten_loai }}
                                </h3>
                                <p class="text-sm text-gray-500 mb-2">Hà Nội, Việt Nam</p>

                                <div class="flex items-center mb-3">
                                    <span
                                        class="bg-blue-700 text-white text-sm font-semibold px-2 py-0.5 rounded mr-2">{{ $phong->stars }}</span>
                                    <div>
                                        <span
                                            class="text-sm font-semibold text-gray-800">{{ $phong->rating_text }}</span>
                                        <span class="text-xs text-gray-500 block">{{ $phong->so_luong_danh_gia }} đánh
                                            giá</span>
                                    </div>
                                </div>

                                @if ($phong->diem_danh_gia >= 4.8)
                                    <p class="text-xs text-green-700 font-medium mb-2">Thỏa thuận thoát hiểm muộn</p>
                                @endif

                                <div class="flex items-baseline justify-between mt-auto pt-2 border-t border-gray-100">
                                    <div>
                                        @if ($phong->phongs && $phong->phongs->count() > 0)
                                            @php $firstRoom = $phong->phongs->first(); @endphp
                                            @if ($firstRoom->hasPromotion())
                                                <p class="text-xs text-gray-500 line-through">
                                                    {{ number_format($firstRoom->gia_goc_hien_thi, 0, ',', '.') }}₫</p>
                                                <p class="text-lg font-bold text-yellow-600">
                                                    {{ number_format($firstRoom->gia_hien_thi, 0, ',', '.') }}₫</p>
                                            @else
                                                <p class="text-lg font-bold text-yellow-600">
                                                    {{ number_format($firstRoom->gia_hien_thi, 0, ',', '.') }}₫</p>
                                            @endif
                                        @else
                                            <p class="text-lg font-bold text-yellow-600">
                                                {{ number_format($phong->gia_co_ban, 0, ',', '.') }}₫</p>
                                        @endif
                                    </div>
                                    <span class="text-sm text-gray-500">2 đêm</span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="contact-booking" class="py-16 bg-white">
        <style>
            .booking-info-box {
                background: linear-gradient(145deg, #ffffff, #f9f7f2);
                padding: 2.5rem;
                border-radius: 12px;
                border-left: 5px solid #d4af37;
                box-shadow:
                    0 6px 15px rgba(0, 0, 0, 0.05),
                    inset 0 0 8px rgba(212, 175, 55, 0.15);
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                transition: all 0.4s ease;
                position: relative;
                overflow: hidden;
            }

            .booking-info-box .info-item {
                display: flex;
                align-items: center;
                margin-bottom: 1rem;
            }

            .booking-info-box .info-icon {
                font-size: 1.25rem;
                color: #d4af37;
                margin-right: 0.75rem;
                width: 24px;
                text-align: center;
                transition: transform 0.3s ease, color 0.3s ease;
            }

            .booking-info-box .info-text {
                font-size: 1rem;
                color: #333;
            }

            /* ===================== NÚT ĐẶT PHÒNG ===================== */
            .btn-booking {
                display: block;
                width: fit-content;
                margin: 3rem auto 0;
                /* cách ra và canh giữa */
                background: linear-gradient(to right, #d4af37, #b68b00);
                color: #fff;
                border-radius: 8px;
                font-weight: 600;
                letter-spacing: 0.5px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
                text-align: center;
                padding: 0.9rem 1.8rem;
                text-decoration: none;
            }

            .btn-booking:hover {
                background: linear-gradient(to right, #b68b00, #d4af37);
                transform: translateY(-2px);
                box-shadow: 0 8px 18px rgba(212, 175, 55, 0.4);
                color: #fff;
            }
        </style>


        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 items-start">

                <div class="lg:col-span-1">
                    <div class="h-[530px] overflow-hidden rounded-lg shadow-md">
                        <img src="{{ asset('img/about/about-1.jpg') }}" alt="Hình ảnh khách sạn"
                            class="w-full h-full object-cover">
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <h2 class="text-xl font-bold mb-4 sr-only">Vị trí Khách sạn</h2>
                    <div class="h-[530px] overflow-hidden rounded-lg shadow-md">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.122108862899!2d106.6781747147715!3d10.79374029231644!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f360824b267%3A0x64f43446b32b85d3!2s244A%20Pasteur%2C%20Ph%C6%B0%E1%BB%9Dng%20Xu%C3%A2n%20Ho%C3%A0%2C%20Th%C3%A0nh%20ph%E1%BB%91%20H%E1%BB%93%20Ch%C3%AD%20Minh!5e0!3m2!1svi!2s!4v1633512345678!5m2!1svi!2s"
                            title="Bản đồ vị trí khách sạn" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade" class="w-full h-full border-0"></iframe>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="booking-info-box h-[550px]">
                        <div>
                            <p class="text-sm uppercase tracking-widest text-gray-500 mb-2 font-light">
                                Liên Hệ & Đặt Chỗ
                            </p>
                            <h1 class="text-3xl font-serif font-bold tracking-wide mb-6 text-gray-800">THÔNG TIN ĐẶT
                                PHÒNG
                            </h1>
                            <p class="text-gray-600 mb-8 leading-relaxed text-base">
                                Bạn có thắc mắc? Đội ngũ của chúng tôi luôn sẵn sàng giúp đỡ. Hãy thoải mái gọi điện
                                hoặc gửi email cho chúng tôi. Chúng tôi rất vui khi được chào đón bạn tại khách sạn của
                                chúng tôi.
                            </p>

                            <div class="space-y-5">
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt info-icon"></i>
                                    <span class="text-gray-700">244A Pasteur, Phường Xuân Hòa, Thành phố Hồ Chí
                                        Minh</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-phone info-icon"></i>
                                    <span class="text-gray-700">0971 839 55 55</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-envelope info-icon"></i>
                                    <a href="mailto:book@hotelname.com"
                                        class="text-gray-700 hover:text-[#D4AF37] transition">book@hotelname.com</a>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-clock info-icon"></i>
                                    <span class="text-gray-700">9:00 - 12:00 và 14:00 - 19:00</span>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('client.phong') }}"
                            class="btn-booking relative inline-flex items-center justify-center px-8 py-3 overflow-hidden tracking-wider text-white rounded-md text-lg font-semibold uppercase">
                            <span
                                class="absolute w-0 h-0 transition-all duration-500 ease-out bg-gradient-to-r from-[#d4af37] to-[#b68b00] rounded-full group-hover:w-56 group-hover:h-56"></span>
                            <span class="absolute bottom-0 left-0 h-full -ml-2 opacity-60">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-auto h-full object-stretch"
                                    viewBox="0 0 487 487">
                                    <path fill-opacity=".15" fill-rule="nonzero" fill="#fff"
                                        d="M0 .3c67 2.1 134.1 4.3 186.3 37 52.2 32.7 89.6 95.8 112.8 150.6 23.2 54.8 32.3 101.4 61.2 149.9 28.9 48.4 77.7 98.8 126.4 149.2H0V.3z">
                                    </path>
                                </svg>
                            </span>
                            <span class="absolute top-0 right-0 w-12 h-full -mr-3 opacity-60">
                                <svg xmlns="http://www.w3.org/2000/svg" class="object-cover w-full h-full"
                                    viewBox="0 0 487 487">
                                    <path fill-opacity=".15" fill-rule="nonzero" fill="#fff"
                                        d="M487 486.7c-66.1-3.6-132.3-7.3-186.3-37s-95.9-85.3-126.2-137.2c-30.4-51.8-49.3-99.9-76.5-151.4C70.9 109.6 35.6 54.8.3 0H487v486.7z">
                                    </path>
                                </svg>
                            </span>
                            <span
                                class="absolute inset-0 w-full h-full rounded-lg opacity-20 bg-gradient-to-b from-transparent via-transparent to-yellow-200"></span>
                            <span class="relative text-base font-semibold tracking-wide">ĐẶT PHÒNG NGAY</span>
                        </a>


                    </div>
                </div>
            </div>

            <div class="mt-16 pt-8 border-t border-gray-200">
                <div class="flex justify-between items-center max-w-5xl mx-auto w-full px-8">
                    <a href="#"
                        class="flex items-center text-gray-700 hover:text-[#D4AF37] transition duration-300">
                        <span class="p-3 mr-2 text-xl text-[#D4AF37]"><i class="fab fa-facebook-f"></i></span>
                        <span class="text-sm font-medium">Theo dõi trên Facebook</span>
                    </a>
                    <a href="#"
                        class="flex items-center text-gray-700 hover:text-[#D4AF37] transition duration-300">
                        <span class="p-3 mr-2 text-xl text-[#D4AF37]"><i class="fab fa-instagram"></i></span>
                        <span class="text-sm font-medium">Theo dõi trên Instagram</span>
                    </a>
                    <a href="#"
                        class="flex items-center text-gray-700 hover:text-[#D4AF37] transition duration-300">
                        <span class="p-3 mr-2 text-xl text-[#D4AF37]"><i class="fab fa-tiktok"></i></span>
                        <span class="text-sm font-medium">Theo dõi trên Tiktok</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize Weekend Deals Swiper
                const weekendDealsSwiper = new Swiper('.weekendDealsSwiper', {
                    // Hiển thị 3 phòng cùng lúc
                    slidesPerView: 3,
                    spaceBetween: 20,

                    // Tự động chuyển slide
                    autoplay: {
                        delay: 3000,
                        disableOnInteraction: false,
                    },

                    // Loop vô hạn
                    loop: true,

                    // Responsive breakpoints
                    breakpoints: {
                        320: {
                            slidesPerView: 1,
                            spaceBetween: 10,
                        },
                        768: {
                            slidesPerView: 2,
                            spaceBetween: 15,
                        },
                        1024: {
                            slidesPerView: 3,
                            spaceBetween: 20,
                        },
                    },

                    // Bỏ navigation buttons
                    navigation: {
                        nextEl: null,
                        prevEl: null,
                    },

                    // Bỏ pagination
                    pagination: {
                        el: null,
                    },

                    // Hiệu ứng chuyển slide
                    effect: 'slide',
                    speed: 500,
                });
            });
        </script>
    @endpush
    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                if (typeof Swiper === 'undefined') {
                    console.error('Swiper library not found!');
                    return;
                }

                new Swiper(".testimonialSwiper", {
                    slidesPerView: 1,
                    spaceBetween: 30,
                    centeredSlides: true,
                    grabCursor: true,
                    loop: true,
                    autoplay: {
                        delay: 4000,
                        disableOnInteraction: false,
                    },
                    pagination: {
                        el: ".swiper-pagination",
                        clickable: true,
                    },
                    navigation: {
                        nextEl: ".swiper-button-next",
                        prevEl: ".swiper-button-prev",
                    },
                    speed: 800,
                    effect: 'slide',
                });
            });
        </script>
    @endpush
