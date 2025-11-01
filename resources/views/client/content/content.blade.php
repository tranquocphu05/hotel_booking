{{-- Toast success đã render global ở layouts/base.blade.php để tránh trùng lặp --}}

<section id="about-us" class="w-full flex justify-center bg-white pt-12 pb-6">
    <style>
        /* CSS tùy chỉnh cho hiệu ứng hover chạy màu của nút "XEM THÊM" */
        /* Đã loại bỏ .animated-hover-button vì sẽ dùng lại .btn-booking */

        @media (min-width: 1024px) {
            .about-block:nth-child(even) .lg\:flex-row {
                flex-direction: row-reverse;
            }
        }

        /* ===================== Contact Info Hover Luxury (Đã có sẵn) ===================== */
        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .info-item i {
            font-size: 18px;
            width: 22px;
            color: #D4AF37;
            transition: 0.3s ease;
        }

        .info-item:hover i {
            transform: translateY(-3px);
            color: #b68b00;
        }

        .info-item:hover span,
        .info-item:hover a {
            color: #b68b00 !important;
        }

        /* ===================== NÚT ĐẶT PHÒNG Luxury (Đã có sẵn) ===================== */
        .btn-booking {
            position: relative;
            overflow: hidden;
            background: transparent;
            border: 2px solid #d4af37;
            color: #d4af37;
            padding: 0.75rem 2.2rem;
            border-radius: 50px;
            font-size: 0.95rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.35s ease;
            z-index: 1;
            /* Thêm z-index để ensure ::before không che mất text */
        }

        .btn-booking::before {
            content: "";
            position: absolute;
            left: -100%;
            top: 0;
            width: 100%;
            /* Giữ nguyên 100% thay vì 200% để đổ đầy nút */
            height: 100%;
            background: linear-gradient(to right, #d4af37, #b68b00);
            transition: all 0.4s ease;
            z-index: 0;
        }

        .btn-booking:hover::before {
            left: 0;
        }

        .btn-booking svg,
        .btn-booking span {
            z-index: 10;
            transition: color 0.3s ease;
        }

        .btn-booking:hover svg,
        .btn-booking:hover span {
            color: #fff;
        }

        .btn-booking:hover svg {
            transform: translateX(6px);
        }
    </style>
    <div class="max-w-7xl w-full px-4">
        <div class="flex flex-col lg:flex-row items-center justify-between">
            <div class="lg:w-3/5 text-center mb-0 lg:mb-0">
                <p class="text-sm uppercase tracking-widest text-[#D4AF37] mb-2 font-medium">VỀ CHÚNG TÔI</p>
                <h2 class="text-5xl font-serif font-extralight text-gray-800 mb-6 leading-tight">
                    Khách sạn OZIA HOTEL
                </h2>
                <p class="text-gray-600 mb-4 max-w-2xl mx-auto">
                    OZIA HOTEL không chỉ là một nơi lưu trú, mà là một tuyệt tác kiến trúc giữa lòng thành phố, định
                    nghĩa lại tiêu chuẩn của sự sang trọng và tinh tế. Lấy cảm hứng từ vẻ đẹp vĩnh cửu của Vàng (Ozia -
                    Gold), Khách sạn mang đến một không gian nghỉ dưỡng biệt lập, nơi mọi chi tiết đều được chăm chút tỉ
                    mỉ.
                </p>
                <p class="text-gray-600 mb-4 max-w-2xl mx-auto">
                    Trải Nghiệm Độc Quyền: Mỗi căn phòng tại OZIA là một chốn riêng tư đậm chất nghệ thuật, với nội thất
                    được chế tác thủ công
                    và tầm nhìn ngoạn mục. Chúng tôi cam kết mang đến dịch vụ Butler (Quản gia) 24/7 cá nhân hóa, đảm
                    bảo mọi nhu cầu của bạn được đáp ứng hoàn hảo.
                </p>
            </div>
            <div class="lg:w-2/5 flex justify-center lg:justify-end space-x-4">
                <img src="{{ asset('img/hero/d.jpeg') }}" alt="Tháp truyền thống"
                    class="w-[24rem] h-[31rem] object-cover rounded shadow-xl transform transition duration-500 ease-in-out hover:scale-[1.02] hover:shadow-2xl hover:rotate-1">
            </div>
        </div>
    </div>
</section>

<section id="about-uss2" class="about-block w-full flex justify-center bg-white pt-6 pb-12">
    <div class="max-w-7xl w-full px-4">
        <div class="flex flex-col lg:flex-row items-center justify-between">
            <div class="lg:w-3/5 text-center mb-8 lg:mb-0">
                <p class="text-sm tracking-widest text-[#D4AF37] mb-2 font-medium">Tiện Nghi</p>
                <h3 class="text-4xl font-serif font-extralight text-gray-800 mb-6 leading-tight">
                    Tiện nghi đẳng cấp & khoảnh khắc vàng son
                </h3>
                <p class="text-gray-600 mb-4 max-w-2xl mx-auto">
                    Khám phá thế giới ẩm thực tại nhà hàng "The Aurum" với những món ăn tinh hoa quốc tế, hay thư giãn
                    tuyệt đối tại OZIA Spa với các liệu pháp trị liệu từ vàng. Hồ bơi vô cực trên tầng thượng là nơi
                    hoàn hảo để chiêm ngưỡng hoàng hôn rực rỡ.
                </p>
                <p class="text-gray-600 mb-8 max-w-2xl mx-auto">
                    OZIA HOTEL: Nơi thời gian dường như ngưng đọng, và mọi khoảnh khắc đều trở thành kỷ niệm vàng son
                    không thể quên.
                </p>
                <a href="{{ route('client.gioithieu') }}" class="btn-booking group">
                    <span>XEM THÊM</span>
                </a>
            </div>
            <div class="lg:w-2/5 flex justify-center lg:justify-end space-x-4">
                <img src="{{ asset('img/hero/b.jpg') }}" alt="Chi tiết Nhà hàng"
                    class="w-[24rem] h-[31rem] object-cover rounded shadow-xl transform transition duration-500 ease-in-out hover:scale-[1.02] hover:shadow-2xl hover:-rotate-1">
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
            /* Giá trị gốc, sẽ bị override trong .service-card */
            --hover-shadow: rgba(212, 175, 55, 0.3);
            --initial-bright-gold: #ffeb3b;
            --icon-size: 22px;
        }

        #services {
            background: url('{{ asset('img/hero/hero-5.jpg') }}') center fixed no-repeat;
            background-size: cover;
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
            background: rgba(255, 255, 255, 0.12);
            /* Tăng độ trong suốt */
            border: 1px solid rgba(255, 255, 255, 0.2);
            /* Viền sáng hơn */
            backdrop-filter: blur(10px);
            /* Tăng độ mờ */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            /* Thêm bóng đổ nhẹ ban đầu */
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px var(--hover-shadow), 0 0 40px rgba(255, 215, 0, 0.4);
            /* Bóng đổ khi hover đẹp hơn */
            background: rgba(255, 255, 255, 0.2);
            /* Sáng hơn khi hover */
        }

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

        .service-icon-wrap i {
            width: var(--icon-size);
            height: var(--icon-size);
            font-size: var(--icon-size);
            color: #fff;
            filter: drop-shadow(0 0 6px rgba(0, 0, 0, 0.5));
            transition: all .4s ease;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .service-card:hover .service-icon-wrap i {
            transform: scale(1.1);
            color: var(--light-text);
        }

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
                $services = [
                    [
                        'icon_class' => 'fas fa-compass',
                        'title' => 'Kế Hoạch Du Lịch',
                        'desc' => 'Chúng tôi sẽ giúp bạn lên lịch trình tốt nhất.',
                    ],
                    [
                        'icon_class' => 'fas fa-mug-hot',
                        'title' => 'Dịch Vụ Ăn Uống',
                        'desc' => 'Phục vụ bữa ăn ngon miệng tại phòng hoặc nhà hàng.',
                    ],
                    [
                        'icon_class' => 'fas fa-lock',
                        'title' => 'Hướng Dẫn Đặt Phòng',
                        'desc' => 'Quy trình đặt phòng nhanh chóng và dễ dàng.',
                    ],
                    [
                        'icon_class' => 'fas fa-car',
                        'title' => 'Bãi Đậu Xe',
                        'desc' => 'Bãi đậu xe rộng rãi và an toàn 24/7.',
                    ],
                    [
                        'icon_class' => 'fas fa-door-open',
                        'title' => 'Dịch Vụ Phòng',
                        'desc' => 'Dịch vụ dọn phòng hàng ngày theo yêu cầu.',
                    ],
                    [
                        'icon_class' => 'fas fa-dumbbell',
                        'title' => 'Phòng Tập & Spa',
                        'desc' => 'Thư giãn với dịch vụ spa và phòng tập hiện đại.',
                    ],
                    [
                        'icon_class' => 'fas fa-wine-glass-alt',
                        'title' => 'Bar & Ăn Uống',
                        'desc' => 'Thưởng thức đồ uống và món ăn đặc sắc.',
                    ],
                    [
                        'icon_class' => 'fas fa-swimmer',
                        'title' => 'Hồ Bơi',
                        'desc' => 'Hồ bơi trong nhà và ngoài trời sang trọng.',
                    ],
                ];
            @endphp

            @foreach ($services as $service)
                <div class="service-card text-center">
                    <div class="service-icon-wrap mx-auto">
                        <i class="{{ $service['icon_class'] }}"></i>
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
    style="background-image: url('{{ asset('img/hero/x.jpg') }}')">
    <div class="absolute inset-0 bg-black/60"></div>
    <div class="relative z-10 max-w-3xl mx-auto">
        <h2 class="text-4xl font-extrabold mb-6">Khám phá khách sạn & dịch vụ của chúng tôi</h2>
        <p class="text-gray-200 mb-10 text-lg leading-relaxed">
            Tận hưởng kỳ nghỉ tuyệt vời cùng không gian sang trọng và dịch vụ đẳng cấp.
        </p>

        <button onclick="openVideoPopup()"
            class="inline-flex items-center justify-center w-20 h-20 rounded-full 
            
            /* Nền vàng trong suốt mặc định */
            bg-[#D4AF37]/30 border-0 
            
            /* Icon luôn màu trắng */
            text-white
            
            /* CSS tùy chỉnh cho hiệu ứng viền/glow */
            play-button-glow
            
            transition duration-300 ease-in-out 
            transform hover:scale-110 
            focus:outline-none">
            
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-8 h-8 ml-1">
                <path d="M3 22v-20l18 10-18 10z" />
            </svg>
        </button>
    </div>
</section>

<style>
.play-button-glow {
    /* MẶC ĐỊNH: Viền thứ 2 màu trắng mờ */
    box-shadow: 
        0 0 0 8px rgba(255, 255, 255, 0.2); /* Viền trắng mờ thứ hai */
    
    /* Icon đã được Tailwind đặt là text-white, nên không cần đặt lại ở đây */
}

.play-button-glow:hover {
    /* HOVER: Hiệu ứng phát sáng vàng mạnh mẽ (Neon Glow) */
    /* Nền sẽ đậm hơn một chút hoặc giữ nguyên tùy theo bg-[#D4AF37]/30 */
    box-shadow: 
        /* Giữ lại viền trắng mờ 0 0 0 8px rgba(255, 255, 255, 0.2), */ /* Tùy chọn: bỏ dòng này nếu muốn viền trắng biến mất khi hover */
        0 0 0 3px #D4AF37, /* Viền vàng rõ nét */
        0 0 0 10px rgba(212, 175, 55, 0.6), /* Viền vàng mờ rộng hơn */
        0 0 30px #D4AF37, /* Sáng vàng chính */
        0 0 60px rgba(212, 175, 55, 0.6); /* Sáng vàng lan rộng */
    
    /* Icon vẫn là màu trắng (đã được Tailwind đặt text-white) */
    /* Nếu muốn chắc chắn, có thể thêm: color: white !important; */
}
</style>
<!-- Popup Video -->
<div id="videoPopup"
    class="fixed inset-0 hidden z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm transition-opacity duration-300">

    <div
        class="relative w-[90vw] max-w-[1000px] overflow-visible shadow-[0_0_60px_rgba(255,255,255,0.1)] border border-white">

        <div class="relative bg-black overflow-hidden">
            <video id="popupVideo" controls preload="auto" playsinline class="w-full h-full cursor-pointer z-10">
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
                @foreach ($phongsUuDai ?? [] as $phong)
                    <div class="swiper-slide">
                        <a href="{{ route('client.phong.show', $phong->id) }}"
                            class="block bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm relative group cursor-pointer
                   hover:shadow-xl hover:scale-[1.02] transition duration-300 ease-in-out">
                            <div class="relative">
                                <img src="{{ asset($phong->img ?: 'img/gallery/gallery-1.jpg') }}"
                                    alt="{{ $phong->ten_phong }}" class="w-full h-48 object-cover">

                                <button
                                    class="absolute top-4 right-4 bg-white p-2 rounded-full shadow-md text-gray-700 hover:text-red-500 transition duration-300 z-10"
                                    onclick="event.preventDefault(); event.stopPropagation();">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </button>

                                @if (data_get($phong, 'loaiPhong.diem_danh_gia', 0) >= 4.8)
                                    <span
                                        class="absolute bottom-0 left-0 bg-yellow-500 text-white text-xs font-semibold px-2 py-1 rounded-tr-lg">Genius</span>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="text-lg font-bold text-gray-800 mb-1 leading-tight">{{ $phong->ten_phong }}
                                </h3>
                                <p class="text-sm text-gray-500 mb-2">
                                    {{ data_get($phong, 'loaiPhong.ten_loai', 'Phòng') }}</p>

                                <div class="flex items-center mb-3">
                                    @php($stars = data_get($phong, 'loaiPhong.stars'))
                                    @if ($stars)
                                        <span
                                            class="bg-blue-700 text-white text-sm font-semibold px-2 py-0.5 rounded mr-2">{{ $stars }}</span>
                                        <div>
                                            <span
                                                class="text-sm font-semibold text-gray-800">{{ data_get($phong, 'loaiPhong.rating_text', '') }}</span>
                                            <span
                                                class="text-xs text-gray-500 block">{{ data_get($phong, 'loaiPhong.so_luong_danh_gia', 0) }}
                                                đánh giá</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-baseline justify-between mt-auto pt-2 border-t border-gray-100">
                                    <div>
                                        @if ($phong->hasPromotion())
                                            <p class="text-xs text-gray-500 line-through">
                                                {{ number_format($phong->gia_goc_hien_thi, 0, ',', '.') }}₫</p>
                                            <p class="text-lg font-bold text-yellow-600">
                                                {{ number_format($phong->gia_hien_thi, 0, ',', '.') }}₫</p>
                                        @else
                                            <p class="text-lg font-bold text-yellow-600">
                                                {{ number_format($phong->gia_hien_thi, 0, ',', '.') }}₫</p>
                                        @endif
                                    </div>
                                    <span class="text-sm text-gray-500">1 đêm</span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</section>

<section id="contact-booking" class="py-16 bg-white">
    <style>
        .equal-box {
            height: 500px;
        }

        .booking-info-box {
            background: linear-gradient(145deg, #ffffff, #f9f7f2);
            padding: 2.5rem;
            border-radius: 12px;
            border-left: 5px solid #d4af37;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05),
                inset 0 0 8px rgba(212, 175, 55, 0.15);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* ===================== Contact Info Hover Luxury ===================== */
        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .info-item i {
            font-size: 18px;
            width: 22px;
            color: #D4AF37;
            transition: 0.3s ease;
        }

        .info-item:hover i {
            transform: translateY(-3px);
            color: #b68b00;
        }

        .info-item:hover span,
        .info-item:hover a {
            color: #b68b00 !important;
        }

        /* ===================== NÚT ĐẶT PHÒNG Luxury ===================== */
        .btn-booking {
            position: relative;
            overflow: hidden;
            background: transparent;
            border: 2px solid #d4af37;
            color: #d4af37;
            padding: 0.75rem 2.2rem;
            border-radius: 50px;
            font-size: 0.95rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.35s ease;
        }

        .btn-booking::before {
            content: "";
            position: absolute;
            left: -100%;
            top: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, #d4af37, #b68b00);
            transition: all 0.4s ease;
            z-index: 0;
        }

        .btn-booking:hover::before {
            left: 0;
        }

        .btn-booking svg,
        .btn-booking span {
            z-index: 10;
            transition: color 0.3s ease;
        }

        .btn-booking:hover svg,
        .btn-booking:hover span {
            color: #fff;
        }

        .btn-booking:hover svg {
            transform: translateX(6px);
        }
    </style>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 items-start">

            <!-- Ảnh -->
            <div class="equal-box overflow-hidden rounded-lg shadow-md">
                <img src="{{ asset('img/hero/7.webp') }}" alt="Hình ảnh khách sạn"
                    class="w-full h-full object-cover">
            </div>

            <!-- Google Maps -->
            <div class="equal-box overflow-hidden rounded-lg shadow-md">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.122108862899!2d106.6781747147715!3d10.79374029231644!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f360824b267%3A0x64f43446b32b85d3!2s244A%20Pasteur%2C%20Ph%C6%B0%E1%BB%9Dng%20Xu%C3%A2n%20Ho%C3%A0%2C%20Th%C3%A0nh%20ph%E1%BB%91%20H%E1%BB%93%20Ch%C3%AD%20Minh!5e0!3m2!1svi!2s!4v1633512345678!5m2!1svi!2s"
                    allowfullscreen="" loading="lazy" class="w-full h-full border-0">
                </iframe>
            </div>

            <!-- Thông tin đặt phòng -->
            <div class="booking-info-box equal-box">
                <div>
                    <p class="text-sm uppercase tracking-widest text-gray-500 mb-2 font-light">
                        Liên Hệ & Đặt Chỗ
                    </p>
                    <h1 class="text-3xl font-serif font-bold tracking-wide mb-6 text-gray-800">
                        THÔNG TIN ĐẶT PHÒNG
                    </h1>
                    <p class="text-gray-600 mb-8 leading-relaxed text-base">
                        Bạn có thắc mắc? Đội ngũ của chúng tôi luôn sẵn sàng giúp đỡ. Hãy thoải mái gọi điện hoặc gửi
                        email cho chúng tôi.
                    </p>

                    <!-- ✅ Contact Info (ĐÃ NÂNG CẤP HOVER) -->
                    <div class="space-y-5">
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>244A Pasteur, TP. Hồ Chí Minh</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-phone"></i>
                            <a href="tel:09718395555" class="text-gray-700">0971 839 55 55</a>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:book@hotelname.com" class="text-gray-700">
                                book@hotelname.com
                            </a>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <span>9:00 - 12:00 & 14:00 - 19:00</span>
                        </div>
                    </div>
                </div>

                <!-- Nút đặt phòng -->
                <div class="flex justify-center mt-8">
                    <a href="{{ route('client.phong') }}" class="btn-booking group">
                        <span>Đặt phòng ngay</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- FOLLOW -->
        <div class="mt-16 pt-8 border-t border-gray-200">
            <div class="flex justify-between items-center max-w-5xl mx-auto w-full px-8">

                <a href="#"
                    class="flex items-center text-gray-700 hover:text-[#D4AF37] transition duration-300">
                    {{-- Đã thay đổi p-3 -> p-4 và text-xl -> text-2xl --}}
                    <span class="p-4 mr-2 text-2xl text-[#D4AF37]"><i class="fab fa-facebook-f"></i></span>
                    {{-- Đã thay đổi text-sm -> text-base và font-medium -> font-semibold --}}
                    <span class="text-base font-semibold">Theo dõi trên Facebook</span>
                </a>

                <a href="#"
                    class="flex items-center text-gray-700 hover:text-[#D4AF37] transition duration-300">
                    {{-- Đã thay đổi p-3 -> p-4 và text-xl -> text-2xl --}}
                    <span class="p-4 mr-2 text-2xl text-[#D4AF37]"><i class="fab fa-instagram"></i></span>
                    {{-- Đã thay đổi text-sm -> text-base và font-medium -> font-semibold --}}
                    <span class="text-base font-semibold">Theo dõi trên Instagram</span>
                </a>

                <a href="#"
                    class="flex items-center text-gray-700 hover:text-[#D4AF37] transition duration-300">
                    {{-- Đã thay đổi p-3 -> p-4 và text-xl -> text-2xl --}}
                    <span class="p-4 mr-2 text-2xl text-[#D4AF37]"><i class="fab fa-tiktok"></i></span>
                    {{-- Đã thay đổi text-sm -> text-base và font-medium -> font-semibold --}}
                    <span class="text-base font-semibold">Theo dõi trên Tiktok</span>
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
