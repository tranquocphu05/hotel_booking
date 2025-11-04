<div x-data="{ currentSlide: 1, totalSlides: 3 }" x-init="setInterval(() => { currentSlide = (currentSlide % totalSlides) + 1 }, 3000)" class="relative min-h-[780px] lg:min-h-[780px]">

    <div class="absolute inset-0">
        @php
            $slides = ['img/hero/3.webp', 'img/hero/abc.jpg', 'img/hero/dx.jpg'];
        @endphp

        @foreach ($slides as $index => $image)
            <div x-show="currentSlide === {{ $index + 1 }}" x-transition:enter="transition ease-out duration-1000"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-1000" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="absolute inset-0 bg-cover bg-center h-full w-full"
                style="background-image: url('{{ asset($image) }}');">
            </div>
        @endforeach
    </div>

    {{-- Nội dung Banner (Lớp phủ ĐEN ĐẬM: bg-opacity-70) --}}
    <div class="absolute inset-0 bg-black bg-opacity-70 flex items-center justify-center p-4 sm:px-8 lg:px-16">
        <div
            class="w-full max-w-screen-xl mx-auto flex flex-col lg:flex-row items-center lg:items-start lg:justify-between relative z-10">

            {{-- Khối Nội dung Chính (Căn trái) --}}
            <div class="text-white z-10 text-center lg:text-left lg:w-1/2 max-w-xl mt-12 lg:mt-0">
                <p class="text-sm uppercase tracking-widest mb-2 font-sans font-medium text-gold">
                    CHÀO MỪNG ĐẾN VỚI OZIA</p>
                <h1 class="text-6xl md:text-7xl font-serif font-extrabold mb-4 leading-tight text-shadow-lg text-white">
                    OZIA Khách Sạn Sang Trọng</h1>
                <p class="text-lg mb-10 mx-auto lg:mx-0 font-light text-gray-200">
                    Trải nghiệm sự xa hoa tột đỉnh. Chúng tôi cam kết mang đến dịch vụ 5 sao cá nhân hóa, nơi mọi chi
                    tiết đều được chạm khắc bằng vàng.
                </p>
                {{-- Nút KHÁM PHẢ NGAY: Hiệu ứng hover swipe/fill --}}
                <a href="#"
                    class="inline-block px-8 py-3 text-white font-semibold uppercase tracking-wider transition duration-300 shadow-lg border-2 border-gold relative overflow-hidden hover-swipe-btn rounded-full">
                    KHÁM PHẢ NGAY
                </a>
            </div>

            {{-- Khối Form Đặt Phòng (Căn phải, FORM 4 CỘT) --}}
            <div class="w-full lg:w-1/2 max-w-xl lg:ml-auto lg:mr-0 mt-12 lg:mt-0">
                <form action="#" method="POST"
                    class="bg-black/70 p-6 sm:p-8 rounded-lg shadow-2xl mx-auto lg:mr-0 lg:ml-auto border border-gold/50">
                    @csrf
                    <p class="text-lg text-gold font-semibold mb-6 uppercase tracking-wider text-center">BOOK YOUR STAY
                    </p>

                    <div class="flex flex-wrap -mx-2 mb-6">

                        {{-- Field 1: Check In --}}
                        <div class="w-1/2 px-2 mb-4">
                            <label class="text-xs uppercase block mb-2 text-gold font-medium">CHECK IN</label>
                            <div class="relative booking-field">
                                <input type="text" placeholder="Select Date"
                                    class="w-full text-xl font-bold text-white bg-transparent booking-box pr-10"
                                    onfocus="(this.type='date')" onblur="(this.type='text')">
                                {{-- Icon lịch: Màu trắng --}}
                                <i
                                    class="fas fa-calendar-alt absolute right-3 top-1/2 -translate-y-1/2 text-white pointer-events-none text-sm"></i>
                            </div>
                        </div>

                        {{-- Field 2: Check Out --}}
                        <div class="w-1/2 px-2 mb-4">
                            <label class="text-xs uppercase block mb-2 text-gold font-medium">CHECK OUT</label>
                            <div class="relative booking-field">
                                <input type="text" placeholder="Select Date"
                                    class="w-full text-xl font-bold text-white bg-transparent booking-box pr-10"
                                    onfocus="(this.type='date')" onblur="(this.type='text')">
                                {{-- Icon lịch: Màu trắng --}}
                                <i
                                    class="fas fa-calendar-alt absolute right-3 top-1/2 -translate-y-1/2 text-white pointer-events-none text-sm"></i>
                            </div>
                        </div>

                        {{-- Field 3: Adults --}}
                        <div class="w-1/2 px-2 mb-4">
                            <label class="text-xs uppercase block mb-2 text-gold font-medium">ADULTS</label>
                            <div class="relative booking-field">
                                {{-- Thêm class 'select-custom' để ẩn icon mặc định của trình duyệt --}}
                                <select
                                    class="w-full text-xl font-bold text-white bg-transparent booking-box pr-10 appearance-none select-custom">
                                    <option value="02" selected>02</option>
                                    <option value="01">01</option>
                                    <option value="03">03</option>
                                </select>
                            </div>
                        </div>

                        {{-- Field 4: Childrens --}}
                        <div class="w-1/2 px-2 mb-4">
                            <label class="text-xs uppercase block mb-2 text-gold font-medium">CHILDREN</label>
                            <div class="relative booking-field">

                                <select
                                    class=" w-full text-xl font-bold text-white bg-transparent booking-box pr-10 appearance-none select-custom">
                                    <option value="01" selected>01</option>
                                    <option value="02">02</option>
                                    <option value="03">03</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-64 mx-auto block bg-gold text-white font-semibold uppercase tracking-wider transition duration-300 border-2 border-white rounded-full mt-2 hover:bg-yellow-600 hover:border-white hover:text-white px-8 py-3">
                        BOOK NOW
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- SLIDER INDICATOR: Đặt giữa, dưới cùng, to hơn, gần nhau hơn (space-x-2) --}}
    <div class="absolute bottom-5 left-1/2 -translate-x-1/2 flex space-x-2 z-20">
        <template x-for="i in totalSlides" :key="i">
            <button @click="currentSlide = i"
                :class="{ 'bg-white w-10': currentSlide === i, 'bg-gray-400 w-3': currentSlide !== i }"
                class="h-3 rounded-full transition-all duration-300 cursor-pointer">
            </button>
        </template>
    </div>
</div>

<style>
    /* CSS cho bóng chữ trên banner */
    .text-shadow-lg {
        text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.7);
    }

    /* Định nghĩa màu vàng kim loại */
    .text-gold {
        color: gold;
    }

    /* CSS color name 'gold' */
    .bg-gold {
        background-color: gold;
    }

    .border-gold {
        border-color: gold;
    }

    /* --- HIỆU ỨNG VÀ MÀU SẮC MỚI CHO BOOK NOW --- */
    /* Định nghĩa màu vàng đậm hơn cho hiệu ứng hover (đặt tên là dark-gold) */
    .bg-dark-gold {
        background-color: #c99300;
        /* Màu vàng sẫm hơn so với 'gold' */
    }

    /* Định nghĩa viền trắng cho nút BOOK NOW */
    .border-white {
        border-color: white;
    }

    /* ------------------------------------------- */


    /* --- Hiệu ứng Hover Swipe cho KHÁM PHẢ NGAY --- */
    .hover-swipe-btn {
        z-index: 10;
        position: relative;
        color: white;
        transition: color 0.35s ease-out;
    }

    .hover-swipe-btn::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 0;
        height: 100%;
        background: gold;
        transition: width 0.35s ease-out;
        z-index: -1;
        border-radius: 9999px;
    }

    .hover-swipe-btn:hover::after {
        width: 100%;
    }

    .hover-swipe-btn:hover {
        color: black;
        /* Chữ đen khi nền vàng */
    }

    /* --- Hết Hiệu ứng Hover Swipe --- */


    /* Tinh chỉnh cho Input/Select: Kiểu BOX viền vàng, nền đen mờ */
    .booking-box {
        border: 2px solid rgba(255, 255, 255, 0.2);
        /* Viền xám mờ ban đầu */
        background-color: rgba(0, 0, 0, 0.5);
        /* Nền đen mờ */
        padding: 8px 12px;
        font-family: inherit;
        outline: none;
        transition: border-color 0.3s ease, background-color 0.3s ease;
        border-radius: 6px;
        /* Bo góc nhẹ */
        color: white !important;
        /* Đảm bảo chữ/số là màu trắng */
        box-shadow: none;
    }

    /* Ghi đè để loại bỏ icon mũi tên mặc định của trình duyệt */
    .select-custom {
        /* Dùng appearance-none trong Tailwind để ẩn icon mũi tên mặc định */
        -moz-appearance: none;
        -ms-appearance: none;
    }

    /* Hiệu ứng khi focus: CHỈ GIỮ MÀU VÀNG */
    .booking-box:focus {
        border-color: gold !important;
        /* Chỉ dùng màu vàng khi focus */
        box-shadow: 0 0 0 1px gold;
        /* Thêm shadow nhẹ màu vàng để nổi bật hơn */
        background-color: rgba(0, 0, 0, 0.7);
    }

    /* Tinh chỉnh màu placeholder */
    input::placeholder {
        color: #ddd;
        font-weight: 300;
    }

    /* Đảm bảo các option trong Select có nền trắng, chữ đen */
    .select-custom option {
        color: black !important;
        /* Màu chữ đen */
        background-color: white !important;
        /* Nền trắng */
    }
</style>
