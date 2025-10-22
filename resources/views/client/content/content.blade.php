<!-- Thông báo thành công -->
@if(session('success'))
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

<section id="about-us">

    <section class="container mx-auto px-4 py-16">
        <div class="flex flex-col lg:flex-row items-center justify-between">

            <div class="lg:w-3/5 text-center mb-8 lg:mb-0">
                <p class="text-sm uppercase tracking-widest text-red-600 mb-2 font-medium">VỀ CHÚNG TÔI</p>
                <h2 class="text-5xl font-serif font-light text-gray-800 mb-6 leading-tight">Khách sạn OZIA HOTEL</h2>
                <p class="text-gray-600 mb-4 max-w-2xl mx-auto">
                    Sona.com là một trang web đặt phòng trực tuyến hàng đầu. Chúng tôi đam mê du lịch. Mỗi ngày, chúng tôi
                    truyền cảm hứng và tiếp cận hàng triệu du khách trên toàn cầu thông qua các trang web của riêng mình.
                </p>

                <p class="text-gray-600 mb-8 max-w-2xl mx-auto">
                    Vì vậy, khi bạn muốn đặt một khách sạn hoàn hảo, thuê nhà nghỉ dưỡng, khu nghỉ dưỡng,
                    căn hộ, nhà khách, hay nhà trên cây, chúng tôi đều có thể đáp ứng.
                </p>

                {{-- ĐÃ DỊCH: READ MORE -> XEM THÊM --}}
                <a href="#"
                    class="inline-block text-gray-900 font-semibold uppercase tracking-wider text-sm border-b border-gray-900 pb-1 transition duration-300 hover:text-red-600 hover:border-red-600">
                    XEM THÊM
                </a>
            </div>
            <div class="lg:w-2/5 flex justify-center lg:justify-end space-x-4">
                {{-- ĐÃ DỊCH: alt text --}}
                <img src="{{ asset('img/about/about-1.jpg') }}" alt="Tháp truyền thống"
                    class="w-40 h-64 md:w-56 md:h-80 object-cover rounded shadow-xl transform transition duration-500">

                {{-- ĐÃ DỊCH: alt text --}}
                <img src="{{ asset('img/about/about-2.jpg') }}" alt="Chi tiết Nhà hàng"
                    class="w-40 h-64 md:w-56 md:h-80 object-cover rounded shadow-xl transform transition duration-500">
            </div>
        </div>
    </section>
</section>


<section id="services">
    <section class="text-center container mx-auto px-4 py-16 bg-white">
        <p class="text-sm uppercase tracking-widest text-red-600 mb-2">DỊCH VỤ CỦA CHÚNG TÔI</p>

        <h2 class="text-3xl font-bold text-gray-800 mb-12">Khám Phá Các Dịch Vụ Của Chúng Tôi</h2>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-8">
            @php
                $services = [
                    ['icon' => 'fas fa-utensils', 'title' => 'Kế Hoạch Du Lịch', 'desc' => 'Chúng tôi sẽ giúp bạn lên lịch trình tốt nhất.'],
                    ['icon' => 'fas fa-mug-hot', 'title' => 'Dịch Vụ Ăn Uống', 'desc' => 'Phục vụ bữa ăn ngon miệng tại phòng hoặc nhà hàng.'],
                    ['icon' => 'fas fa-wifi', 'title' => 'Hướng Dẫn Đặt Phòng', 'desc' => 'Quy trình đặt phòng nhanh chóng và dễ dàng.'],
                    ['icon' => 'fas fa-car', 'title' => 'Bãi Đậu Xe', 'desc' => 'Bãi đậu xe rộng rãi và an toàn 24/7.'],
                    ['icon' => 'fas fa-door-open', 'title' => 'Dịch Vụ Phòng', 'desc' => 'Dịch vụ dọn phòng hàng ngày theo yêu cầu.'],
                    ['icon' => 'fas fa-spa', 'title' => 'Phòng Tập & Spa', 'desc' => 'Thư giãn với dịch vụ spa và phòng tập hiện đại.'],
                    ['icon' => 'fas fa-wine-glass-alt', 'title' => 'Bar & Ăn Uống', 'desc' => 'Thưởng thức đồ uống và món ăn đặc sắc.'],
                    ['icon' => 'fas fa-swimmer', 'title' => 'Hồ Bơi', 'desc' => 'Hồ bơi trong nhà và ngoài trời sang trọng.'],
                ];
            @endphp

            @foreach ($services as $service)
                <div class="p-4 hover:shadow-lg transition duration-300 rounded-lg">
                    <i
                        class="{{ $service['icon'] }} text-4xl text-red-600 mb-4 transition duration-300 hover:text-red-700"></i>
                    <h4 class="text-xl font-semibold mb-2 text-gray-800">{{ $service['title'] }}</h4>
                    <p class="text-gray-500 text-sm">{{ $service['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>
</section>

---

<section id="rooms-gallery" class="py-16">
    <div class="text-center mb-12">
        <p class="text-sm uppercase tracking-widest text-red-600 mb-2 font-medium">PHÒNG CỦA CHÚNG TÔI</p>
        <h2 class="text-3xl font-bold text-gray-800">Trải Nghiệm Các Loại Phòng Sang Trọng Của Chúng Tôi</h2>
    </div>


   <div class="flex flex-wrap -mx-4 sm:mx-0">
        @forelse ($loaiPhongs as $phong)
            <a href="{{ route('client.phong.show', $phong->id) }}" class="block w-full sm:w-1/2 md:w-1/4 h-[600px] relative group overflow-hidden">
                <img src="{{ asset($phong->anh ?: 'img/room/room-1.jpg') }}"
                    alt="{{ $phong->ten_loai }}"
                    class="w-full h-full object-cover transition duration-500 group-hover:scale-105">

                <div class="absolute inset-0 bg-black bg-opacity-40 transition duration-300 group-hover:bg-opacity-80"></div>

                <div class="absolute inset-0 p-6 text-white flex items-end transition duration-300 group-hover:opacity-0 group-hover:invisible">
                    <div>
                        <h4 class="text-3xl font-serif font-bold mb-1">{{ $phong->ten_loai }}</h4>
                        @if($phong->phongs && $phong->phongs->count() > 0)
                            @php $firstRoom = $phong->phongs->first(); @endphp
                            @if($firstRoom->hasPromotion())
                                <div class="flex items-center space-x-2">
                                    <span class="text-lg text-gray-300 line-through">
                                        {{ number_format($firstRoom->gia_goc_hien_thi, 0, ',', '.') }}đ
                                    </span>
                                    <span class="text-xl text-red-400 font-semibold tracking-wider">
                                        {{ number_format($firstRoom->gia_hien_thi, 0, ',', '.') }}đ
                                    </span>
                                </div>
                                <span class="text-white text-sm font-light">/ Đêm</span>
                            @else
                                <span class="text-xl text-red-400 font-semibold tracking-wider">
                                    {{ number_format($firstRoom->gia_hien_thi, 0, ',', '.') }}đ
                                    <span class="text-white text-sm font-light">/ Đêm</span>
                                </span>
                            @endif
                        @else
                            <span class="text-xl text-red-400 font-semibold tracking-wider">
                                {{ number_format($phong->gia_co_ban, 0, ',', '.') }}đ
                                <span class="text-white text-sm font-light">/ Đêm</span>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="absolute inset-0 flex items-center justify-center p-6 transition duration-300 opacity-0 invisible group-hover:opacity-100 group-hover:visible">
                    <div class="text-left text-white w-full max-w-xs">
                        <h4 class="text-3xl font-serif font-bold mb-1">{{ $phong->ten_loai }}</h4>
                        @if($phong->phongs && $phong->phongs->count() > 0)
                            @php $firstRoom = $phong->phongs->first(); @endphp
                            @if($firstRoom->hasPromotion())
                                <div class="flex items-center space-x-2">
                                    <span class="text-lg text-gray-300 line-through">
                                        {{ number_format($firstRoom->gia_goc_hien_thi, 0, ',', '.') }}đ
                                    </span>
                                    <span class="text-xl text-red-400 font-semibold tracking-wider">
                                        {{ number_format($firstRoom->gia_hien_thi, 0, ',', '.') }}đ
                                    </span>
                                </div>
                                <span class="text-white text-sm font-light">/ Đêm</span>
                            @else
                                <span class="text-xl text-red-400 font-semibold tracking-wider">
                                    {{ number_format($firstRoom->gia_hien_thi, 0, ',', '.') }}đ
                                    <span class="text-white text-sm font-light">/ Đêm</span>
                                </span>
                            @endif
                        @else
                            <span class="text-xl text-red-400 font-semibold tracking-wider">
                                {{ number_format($phong->gia_co_ban, 0, ',', '.') }}đ
                                <span class="text-white text-sm font-light">/ Đêm</span>
                            </span>
                        @endif

                        <div class="mt-4 grid grid-cols-2 gap-x-2 gap-y-1 text-sm">
                            <div class="text-left">
                                <span class="font-bold text-red-300">Mô tả:</span>
                            </div>
                            <div class="text-left">
                                <span class="font-light">{{ $phong->mo_ta ?? 'Đang cập nhật' }}</span>
                            </div>
                        </div>

                        {{-- Hiển thị đánh giá sao --}}
                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= floor($phong->diem_danh_gia))
                                            <i class="fas fa-star text-yellow-400 text-sm"></i>
                                        @elseif($i - 0.5 <= $phong->diem_danh_gia)
                                            <i class="fas fa-star-half-alt text-yellow-400 text-sm"></i>
                                        @else
                                            <i class="far fa-star text-gray-300 text-sm"></i>
                                        @endif
                                    @endfor
                                </div>
                                <span class="text-white text-sm font-medium">{{ $phong->stars }}</span>
                            </div>
                            <div class="text-right">
                                <div class="text-white text-sm font-medium">{{ $phong->rating_text }}</div>
                                <div class="text-gray-300 text-xs">{{ $phong->so_luong_danh_gia }} đánh giá</div>
                            </div>
                        </div>

                        
                    </div>
                </div>
            </a>
        @empty
            <div class="w-full text-center py-16">
                <div class="text-gray-500 text-lg">
                    <i class="fas fa-bed text-4xl mb-4"></i>
                    <p>Hiện chưa có loại phòng nào được hiển thị.</p>
                    <p class="text-sm mt-2">Vui lòng quay lại sau hoặc liên hệ với chúng tôi.</p>
                </div>
            </div>
        @endforelse
    </div>
</section>



<section id="testimonials">
    <section class="text-center container mx-auto px-4 py-16 bg-gray-50 my-16 rounded-lg">
        <p class="text-sm uppercase tracking-widest text-red-600 mb-2">Ý KIẾN KHÁCH HÀNG</p>
        <h2 class="text-3xl font-bold text-gray-800 mb-8">Khách Hàng Nói Gì?</h2>

        @if(isset($comments) && $comments->count() > 0)
            <div class="swiper testimonialSwiper w-full max-w-4xl mx-auto relative">
                <div class="swiper-wrapper">
                    @foreach($comments as $comment)
                        <div class="swiper-slide bg-white rounded-2xl shadow-md p-8 flex flex-col items-center text-center transition duration-300 hover:shadow-lg">
                            
                            {{-- Ảnh đại diện --}}
                            @if(!empty($comment->user->avatar))
                                <img src="{{ asset('storage/' . $comment->user->avatar) }}"
                                     alt="Avatar người dùng"
                                     class="w-20 h-20 rounded-full object-cover mb-4 shadow-md border-2 border-red-400 hover:scale-105 transition-transform duration-300">
                            @elseif(!empty($comment->img))
                                <img src="{{ asset('storage/' . $comment->img) }}"
                                     alt="Ảnh đánh giá"
                                     class="w-20 h-20 rounded-full object-cover mb-4 shadow-md border-2 border-red-400 hover:scale-105 transition-transform duration-300">
                            @else
                                <img src="{{ asset('img/avatar-default.svg') }}"
                                     alt="Avatar mặc định"
                                     class="w-20 h-20 rounded-full object-cover mb-4 shadow-md border border-gray-300 bg-gray-100 p-2">
                            @endif

                            {{-- Nội dung đánh giá --}}
                            <p class="italic text-gray-600 text-lg leading-relaxed mb-6 max-w-2xl">
                                “{{ $comment->noi_dung }}”
                            </p>

                            {{-- Số sao --}}
                            <div class="flex justify-center text-yellow-500 mb-3">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $comment->so_sao ? 'text-yellow-500' : 'text-gray-300' }}"></i>
                                @endfor
                            </div>

                            {{-- Người dùng & ngày đánh giá --}}
                            <p class="font-semibold text-gray-800">
                                — {{ $comment->user->username ?? $comment->user->name ?? 'Ẩn danh' }}
                            </p>
                            <p class="text-sm text-gray-500 mt-2">
                                {{ optional($comment->ngay_danh_gia)->format('d/m/Y') }}
                            </p>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="swiper-pagination mt-6"></div>
            </div>
        @else
            <p class="text-gray-500 italic">Chưa có đánh giá 5 sao nào được hiển thị.</p>
        @endif
    </div>
</section>

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
                        <img src="{{ asset($phong->anh ?: 'img/gallery/gallery-1.jpg') }}" alt="{{ $phong->ten_loai }}"
                            class="w-full h-48 object-cover">

                        <button
                            class="absolute top-4 right-4 bg-white p-2 rounded-full shadow-md text-gray-700 hover:text-red-500 transition duration-300 z-10"
                            onclick="event.preventDefault(); event.stopPropagation();">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </button>

                        @if($phong->diem_danh_gia >= 4.8)
                            <span
                                class="absolute bottom-0 left-0 bg-yellow-500 text-white text-xs font-semibold px-2 py-1 rounded-tr-lg">Genius</span>
                        @endif
                    </div>
                    <div class="p-4">
                        <h3 class="text-lg font-bold text-gray-800 mb-1 leading-tight">{{ $phong->ten_loai }}</h3>
                        <p class="text-sm text-gray-500 mb-2">Hà Nội, Việt Nam</p>

                        <div class="flex items-center mb-3">
                            <span
                                class="bg-blue-700 text-white text-sm font-semibold px-2 py-0.5 rounded mr-2">{{ $phong->stars }}</span>
                            <div>
                                <span class="text-sm font-semibold text-gray-800">{{ $phong->rating_text }}</span>
                                <span class="text-xs text-gray-500 block">{{ $phong->so_luong_danh_gia }} đánh giá</span>
                            </div>
                        </div>

                        @if($phong->diem_danh_gia >= 4.8)
                            <p class="text-xs text-green-700 font-medium mb-2">Thỏa thuận thoát hiểm muộn</p>
                        @endif

                        <div class="flex items-baseline justify-between mt-auto pt-2 border-t border-gray-100">
                            <div>
                                @if($phong->phongs && $phong->phongs->count() > 0)
                                    @php $firstRoom = $phong->phongs->first(); @endphp
                                    @if($firstRoom->hasPromotion())
                                        <p class="text-xs text-gray-500 line-through">{{ number_format($firstRoom->gia_goc_hien_thi, 0, ',', '.') }}₫</p>
                                        <p class="text-lg font-bold text-red-600">{{ number_format($firstRoom->gia_hien_thi, 0, ',', '.') }}₫</p>
                                    @else
                                        <p class="text-lg font-bold text-red-600">{{ number_format($firstRoom->gia_hien_thi, 0, ',', '.') }}₫</p>
                                    @endif
                                @else
                                    <p class="text-lg font-bold text-red-600">{{ number_format($phong->gia_co_ban, 0, ',', '.') }}₫</p>
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

<section id="contact-booking" class="py-16">

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 bg-white p-8 sm:p-12 lg:p-16 rounded-lg shadow-lg">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 items-stretch">

            <div class="lg:col-span-1 flex flex-col">
                <div class="h-full overflow-hidden rounded-lg">
                    <img
                        src="{{ asset('img/about/about-1.jpg') }}"
                        alt="Hình ảnh khách sạn"
                        class="w-full h-full object-cover min-h-[300px]">
                </div>
            </div>

            <div class="lg:col-span-1 flex flex-col">
                <h2 class="text-xl font-bold mb-4 sr-only">Vị trí Khách sạn</h2>
                <div class="h-full flex-grow overflow-hidden rounded-lg">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.122108862899!2d106.6781747147715!3d10.79374029231644!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f360824b267%3A0x64f43446b32b85d3!2s244A%20Pasteur%2C%20Ph%C6%B0%E1%BB%9Dng%20Xu%C3%A2n%20Ho%C3%A0%2C%20Th%C3%A0nh%20ph%E1%BB%91%20H%E1%BB%93%20Ch%C3%AD%20Minh!5e0!3m2!1svi!2s!4v1633512345678!5m2!1svi!2s"
                        title="Bản đồ vị trí khách sạn"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        class="w-full h-full border-0 min-h-[300px]"></iframe>
                </div>
            </div>

            <div class="lg:col-span-1">
                <h1 class="text-3xl font-serif font-light tracking-wide mb-8 text-gray-800">THÔNG TIN ĐẶT PHÒNG</h1>
                <p class="text-gray-600 mb-6 leading-relaxed text-base">
                    Bạn có thắc mắc? Đội ngũ của chúng tôi luôn sẵn sàng giúp đỡ. Hãy thoải mái gọi điện với chúng tôi
                    bất cứ lúc nào, chúng tôi rất vui khi được chào đón bạn tại khách sạn của chúng tôi.
                </p>

                <div class="space-y-4 mb-8">
                    <div class="flex items-center text-base">
                        <i class="fas fa-map-marker-alt text-xl text-red-600 mr-3"></i>
                        <span class="text-gray-700">244A Pasteur, Phường Xuân Hòa, Thành phố Hồ Chí Minh</span>
                    </div>

                    <div class="flex items-center text-base">
                        <i class="fas fa-phone text-xl text-red-600 mr-3"></i>
                        <span class="text-gray-700 font-medium">0971 839 55 55</span>
                    </div>

                    <div class="flex items-center text-base">
                        <i class="fas fa-envelope text-xl text-red-600 mr-3"></i>
                        <a href="mailto:book@hotelname.com"
                            class="text-gray-700 hover:text-red-600 transition duration-300">book@hotelname.com</a>
                    </div>

                    <div class="flex items-center text-base">
                        <i class="fas fa-clock text-xl text-red-600 mr-3"></i>
                        <span class="text-gray-700">9:00 - 12:00 và 14:00 - 19:00</span>
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-3 bg-red-600 text-white font-semibold uppercase tracking-wider rounded transition duration-300 hover:bg-red-700 shadow-md">
                    ĐẶT PHÒNG NGAY
                </button>
            </div>
        </div>

        <div class="mt-16 pt-8 border-t border-gray-200">
            <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-12">
                <a href="#" class="flex items-center text-gray-700 hover:text-red-600 transition duration-300">
                    <span class="p-3 mr-2 text-xl text-red-600"><i class="fab fa-facebook-f"></i></span>
                    <span class="text-sm font-medium">Theo dõi trên Facebook</span>
                </a>
                <a href="#" class="flex items-center text-gray-700 hover:text-red-600 transition duration-300">
                    <span class="p-3 mr-2 text-xl text-red-600"><i class="fab fa-instagram"></i></span>
                    <span class="text-sm font-medium">Theo dõi trên Instagram</span>
                </a>
                <a href="#" class="flex items-center text-gray-700 hover:text-red-600 transition duration-300">
                    <span class="p-3 mr-2 text-xl text-red-600"><i class="fab fa-tiktok"></i></span>
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
