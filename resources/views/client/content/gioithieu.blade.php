@extends('layouts.client')

@section('client_content')
    <div class="relative w-full bg-cover bg-center bg-no-repeat -mt-2"
        style="background-image: url('{{ asset('img/blog/bg-6.jpg') }}');">

        {{-- Lớp phủ tối giúp chữ nổi bật --}}
        <div class="absolute inset-0 bg-black bg-opacity-40"></div>

        <div class="relative py-28 px-4 text-center text-white">
            <nav class="text-sm text-gray-200 mb-4">
                <a href="{{ url('/') }}" class="hover:text-[#D4AF37] transition-colors">Trang chủ</a> /
                <span class="text-[#FFD700] font-semibold">Giới thiệu</span>
            </nav>

            <h1 class="text-5xl md:text-7xl font-bold mb-8">Giới thiệu về chúng tôi</h1>

            <p class="text-lg md:text-xl text-gray-100 leading-relaxed max-w-4xl mx-auto">
                Chào mừng quý khách đến với Khách sạn Ozia Hotel – nơi kiến tạo một không gian nghỉ dưỡng độc đáo, nơi sự
                tinh tế của kiến trúc truyền thống hòa quyện hoàn hảo với đẳng cấp của tiện nghi hiện đại.
            </p>
        </div>
    </div>

    <!-- ABOUT AREA -->
    <section class="py-20 max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        <div>
            <h3 class="text-4xl font-bold mb-4">Một Nơi Đáng Nhớ</h3>
            <div class="w-20 h-1 bg-yellow-500 mb-6"></div>
            <p class="text-gray-700 mb-6 leading-relaxed">
                Khách sạn Ozia không chỉ là một điểm dừng chân, mà là một trải nghiệm nghỉ dưỡng đẳng cấp được kiến tạo
                từ sự tỉ mỉ và tận tâm. Chúng tôi tin rằng kỳ nghỉ của bạn xứng đáng được trọn vẹn và hoàn hảo.
                <br><br>
                Bước vào Ozia, quý khách sẽ cảm nhận ngay sự hài hòa tuyệt vời giữa phong cách nội thất truyền thống trang
                nhã và nét hiện đại, tiện nghi. Đội ngũ nhân viên của Ozia Hotel luôn sẵn sàng phục vụ với sự chuyên
                nghiệp và nụ cười thân thiện, đảm bảo mang đến sự thoải mái và hài lòng tối đa.
                <br><br>
                Tọa lạc tại vị trí đắc địa, Ozia Hotel là cánh cửa mở ra vẻ đẹp của thành phố. Hãy để Ozia trở thành
                ngôi nhà thứ hai của quý khách.
            </p>
            <a href="#"
                class="inline-block bg-yellow-500 text-white px-6 py-3 rounded-full transition duration-300
                   hover:bg-yellow-600 hover:shadow-lg hover:-translate-y-1 transform">
                Xem thêm
            </a>
        </div>
        <div class="relative group lg:aspect-[4/3] w-full">
            <img src="{{ asset('img/blog/17.jpg') }}" alt="Phòng nghỉ sang trọng Khách sạn Ozia"
                class="rounded-2xl shadow-2xl w-full h-full object-cover transform group-hover:scale-105 transition duration-700">
            <div
                class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition duration-700">
            </div>
        </div>
    </section>

    <!-- MILESTONES -->
<section id="counter-section" class="relative py-20 bg-cover bg-center bg-fixed"
    style="background-image: url('{{ asset('img/room/4.png') }}');">

    <div class="absolute inset-0 bg-black opacity-60"></div>

    <div class="relative z-10 max-w-6xl mx-auto px-6 text-center text-white">

        <h2 class="text-5xl font-extrabold mb-4">Các Cột Mốc Quan Trọng Của Chúng Tôi</h2>
        <p class="max-w-3xl mx-auto mb-16 text-lg opacity-80 leading-relaxed">
            Khách sạn Ozia đã đạt được nhiều thành tựu đáng tự hào trong suốt thời gian hoạt động. Từ số lượng đồ uống
            phục vụ đến quy mô tiện ích, chúng tôi luôn nỗ lực mang đến trải nghiệm tốt nhất cho quý khách. Hãy cùng
            khám phá những con số ấn tượng của chúng tôi.
        </p>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            {{-- Mục 1: Cocktails/ngày --}}
            <div
                class="flex flex-col items-center justify-center p-8 border border-white/50 rounded-lg bg-black/30 backdrop-blur-sm counter-item">

                <svg class="h-12 w-12 text-amber-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 21.5c-3.8 0-7-3.1-7-7s3.1-7 7-7 7 3.1 7 7-3.1 7-7 7zM12 10v4M12 14h2"></path>
                </svg>
                {{-- THAY ĐỔI: Thêm data-target và đặt giá trị ban đầu là 0 --}}
                <h4 class="text-4xl font-semibold mb-2 text-amber-300" data-target="231">0</h4>
                <p class="uppercase text-sm tracking-wider text-white/90">Cocktails/ngày</p>
            </div>

            {{-- Mục 2: Hồ bơi --}}
            <div
                class="flex flex-col items-center justify-center p-8 border border-white/50 rounded-lg bg-black/30 backdrop-blur-sm counter-item">

                <svg class="h-12 w-12 text-amber-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 12.79A9 9 0 1111.21 3A7 7 0 0021 12.79z"></path>
                </svg>
                {{-- THAY ĐỔI: Thêm data-target và đặt giá trị ban đầu là 0 --}}
                <h4 class="text-4xl font-semibold mb-2 text-amber-300" data-target="3">0</h4>
                <p class="uppercase text-sm tracking-wider text-white/90">Hồ bơi</p>
            </div>

            {{-- Mục 3: Phòng --}}
            <div
                class="flex flex-col items-center justify-center p-8 border border-white/50 rounded-lg bg-black/30 backdrop-blur-sm counter-item">

                <svg class="h-12 w-12 text-amber-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m-1 4h1m-1 4h1m-1 4h1m-1 4h1m-1 4h1m-1 4h1">
                        </path>
                </svg>
                {{-- THAY ĐỔI: Thêm data-target và đặt giá trị ban đầu là 0 --}}
                <h4 class="text-4xl font-semibold mb-2 text-amber-300" data-target="79">0</h4>
                <p class="uppercase text-sm tracking-wider text-white/90">Phòng</p>
            </div>

            {{-- Mục 4: Căn hộ --}}
            <div
                class="flex flex-col items-center justify-center p-8 border border-white/50 rounded-lg bg-black/30 backdrop-blur-sm counter-item">

                <svg class="h-12 w-12 text-amber-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                </svg>
                {{-- THAY ĐỔI: Thêm data-target và đặt giá trị ban đầu là 0 --}}
                <h4 class="text-4xl font-semibold mb-2 text-amber-300" data-target="25">0</h4>
                <p class="uppercase text-sm tracking-wider text-white/90">Căn hộ</p>
            </div>
        </div>
    </div>
</section>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const counterSection = document.getElementById('counter-section');
        // Lưu trữ trạng thái để tránh đếm nhiều lần
        let hasCounted = false;

        // Hàm đếm số
        const runCounter = (counter) => {
            const target = +counter.getAttribute('data-target');
            const speed = 600; // Tốc độ càng lớn, thời gian đếm càng dài

            const updateCount = () => {
                const count = +counter.innerText;
                const increment = target / speed;

                if (count < target) {
                    // Cập nhật số
                    counter.innerText = Math.ceil(count + increment);
                    // Lặp lại
                    setTimeout(updateCount, 1);
                } else {
                    // Đảm bảo số cuối cùng chính xác
                    counter.innerText = target;
                }
            };

            updateCount();
        };

        // Khởi tạo Intersection Observer
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach(entry => {
                    // Kiểm tra nếu phần tử nằm trong khung nhìn và chưa đếm
                    if (entry.isIntersecting && !hasCounted) {
                        const counters = entry.target.querySelectorAll('[data-target]');
                        counters.forEach(runCounter);
                        hasCounted = true; // Đánh dấu đã đếm
                        observer.unobserve(entry.target); // Ngừng theo dõi
                    }
                });
            }, 
            {
                // Root: null (khung nhìn của trình duyệt)
                threshold: 0.5 // Kích hoạt khi 50% section hiển thị
            }
        );

        // Bắt đầu theo dõi section
        if (counterSection) {
            observer.observe(counterSection);
        }
    });
</script>
    <!-- OUR HOTEL -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-6 text-center mb-10">
        <h3 class="text-3xl md:text-4xl font-bold mb-3 text-gray-800">
            Khách Sạn Của Chúng Tôi
        </h3>
        <div class="w-16 h-1 bg-[#D4AF37] mx-auto mb-6"></div> 
    </div>

    {{-- KHỐI TIỆN ÍCH CÓ DẤU TÍCH (Dãn khoảng cách) --}}
    <div class="max-w-7xl mx-auto px-6 mb-12"> 
        
        {{-- SỬA ĐỔI QUAN TRỌNG: Tăng gap-x-12 lên gap-x-20 --}}
        <div class="flex flex-wrap justify-center gap-y-6 gap-x-20 max-w-6xl mx-auto">
            
            @php
                $amenities = [
                    'Bể bơi vô cực',
                    'Phòng tập gym cao cấp',
                    'Dịch vụ spa thư giãn',
                    'Nhà hàng Á - Âu',
                    'Bar Rooftop',
                    'Lễ tân 24/7',
                    'Wifi tốc độ cao',
                    'Dịch vụ giặt ủi',
                    'Đưa đón sân bay',
                ];
                $cols = array_chunk($amenities, 3);
            @endphp

            @foreach ($cols as $col)
                {{-- SỬA ĐỔI: Thêm px-4 để tăng thêm khoảng đệm bên trong (nếu cần) --}}
                <ul class="space-y-2 text-left flex-shrink-0 px-4"> 
                    @foreach ($col as $item)
                        <li class="flex items-start text-gray-700 text-base">
                            {{-- Giữ nguyên màu vàng kim loại và căn chỉnh thẳng hàng --}}
                            <i class="fas fa-check-circle text-xl flex-shrink-0" style="color: #D4AF37; margin-top: 0.2rem; margin-right: 0.5rem;"></i>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </div>
    </div>
    
    {{-- KHỐI ẢNH (Giữ nguyên) --}}
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="group relative overflow-hidden rounded-xl shadow-lg">
            <img src="{{ asset('img/room/room-4.jpg') }}"
                class="w-full h-[280px] object-cover group-hover:scale-110 transition duration-700" alt="Nhà Hàng">
            <div
                class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                <h4 class="text-white text-xl font-semibold">Nhà Hàng</h4>
            </div>
        </div>
        <div class="group relative overflow-hidden rounded-xl shadow-lg">
            <img src="{{ asset('img/room/room-6.jpg') }}"
                class="w-full h-[280px] object-cover group-hover:scale-110 transition duration-700" alt="Hồ Bơi">
            <div
                class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                <h4 class="text-white text-xl font-semibold">Hồ Bơi</h4>
            </div>
        </div>
        <div class="group relative overflow-hidden rounded-xl shadow-lg">
            <img src="{{ asset('img/room/room-5.jpg') }}"
                class="w-full h-[280px] object-cover group-hover:scale-110 transition duration-700" alt="Phòng Nghỉ">
            <div
                class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                <h4 class="text-white text-xl font-semibold">Phòng Nghỉ</h4>
            </div>
        </div>
    </div>
</section>
    <!-- SERVICES -->
    <section id="services" class="py-12 bg-white">

        <div class="text-center container mx-auto px-4 relative z-10">
            <div class="max-w-7xl mx-auto px-6 text-center mb-16">
                <h3 class="text-4xl font-bold mb-4">Dịch Vụ Của Chúng Tôi</h3>
                <div class="w-20 h-1 bg-yellow-500 mx-auto mb-6"></div>

            </div>
            {{-- Giữ nguyên grid 4 cột --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @php
                    $services = [
                        [
                            'icon' => 'fas fa-utensils',
                            'title' => 'Kế Hoạch Du Lịch',
                            'desc' => 'Chúng tôi sẽ giúp bạn lên lịch trình tốt nhất.',
                        ],
                        [
                            'icon' => 'fas fa-mug-hot',
                            'title' => 'Dịch Vụ Ăn Uống',
                            'desc' => 'Phục vụ bữa ăn ngon miệng tại phòng hoặc nhà hàng.',
                        ],
                        [
                            'icon' => 'fas fa-wifi',
                            'title' => 'Hướng Dẫn Đặt Phòng',
                            'desc' => 'Quy trình đặt phòng nhanh chóng và dễ dàng.',
                        ],
                        [
                            'icon' => 'fas fa-car',
                            'title' => 'Bãi Đậu Xe',
                            'desc' => 'Bãi đậu xe rộng rãi và an toàn 24/7.',
                        ],
                        [
                            'icon' => 'fas fa-door-open',
                            'title' => 'Dịch Vụ Phòng',
                            'desc' => 'Dịch vụ dọn phòng hàng ngày theo yêu cầu.',
                        ],
                        [
                            'icon' => 'fas fa-spa',
                            'title' => 'Phòng Tập & Spa',
                            'desc' => 'Thư giãn với dịch vụ spa và phòng tập hiện đại.',
                        ],
                        [
                            'icon' => 'fas fa-wine-glass-alt',
                            'title' => 'Bar & Ăn Uống',
                            'desc' => 'Thưởng thức đồ uống và món ăn đặc sắc.',
                        ],
                        [
                            'icon' => 'fas fa-swimmer',
                            'title' => 'Hồ Bơi',
                            'desc' => 'Hồ bơi trong nhà và ngoài trời sang trọng.',
                        ],
                    ];
                @endphp

                @foreach ($services as $service)
                    {{-- THẺ DỊCH VỤ --}}
                    <div
                        class="p-6 text-center border border-gray-200 rounded-lg shadow-md hover:shadow-xl transition-all duration-400 transform hover:-translate-y-1 bg-white">

                        {{-- Icon Wrapper --}}
                        <div
                            class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center border-2 border-yellow-300 bg-yellow-50">
                            {{-- Icon --}}
                            <i class="{{ $service['icon'] }} text-3xl text-yellow-600"></i>
                        </div>

                        {{-- Tiêu đề --}}
                        <h4 class="text-lg font-bold text-gray-800 mb-1">{{ $service['title'] }}</h4>

                        {{-- Mô tả --}}
                        <p class="text-sm text-gray-500">{{ $service['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
