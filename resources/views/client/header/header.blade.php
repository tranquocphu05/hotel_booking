<div x-data="{
         slides: @js(collect(['img/hero/3.webp', 'img/hero/abc.jpg', 'img/hero/dx.jpg'])->map(fn($p) => asset($p))->values()),
         currentIndex: 0,
         nextIndex: 1,
         isFading: false,
         isLocked: false,
         duration: 1200,
         interval: 6000,
         timer: null,
         loaded: {},
         setNextIndex() {
             if (!this.slides.length) return;
             this.nextIndex = (this.currentIndex + 1) % this.slides.length;
         },
         ensureLoaded(index) {
             const src = this.slides[index];
             if (!src) return Promise.resolve();
             if (this.loaded[src]) return Promise.resolve();

             return new Promise((resolve) => {
                 let done = false;
                 const finish = () => {
                     if (done) return;
                     done = true;
                     this.loaded[src] = true;
                     resolve();
                 };

                 const img = new Image();
                 img.decoding = 'async';
                 img.onload = finish;
                 img.onerror = finish;
                 img.src = src;

                 if (img.decode) {
                     img.decode().then(finish).catch(() => {});
                 }
             });
         },
         preload() {
             return Promise.all(this.slides.map((_, idx) => this.ensureLoaded(idx)));
         },
         start() {
             if (this.slides.length <= 1) return;
             this.setNextIndex();
             this.preload().finally(() => {
                 this.timer = setInterval(() => this.goTo(), this.interval);
             });
         },
         goTo(index = null) {
             if (this.isLocked) return;
             if (!this.slides.length) return;

             const targetIndex = (index === null)
                 ? ((this.currentIndex + 1) % this.slides.length)
                 : index;

             if (targetIndex === this.currentIndex) return;

             this.isLocked = true;
             this.nextIndex = targetIndex;
             this.isFading = false;

             this.ensureLoaded(this.nextIndex).finally(() => {
                 requestAnimationFrame(() => {
                     this.isFading = true;
                     setTimeout(() => {
                         this.currentIndex = this.nextIndex;
                         this.isFading = false;
                         this.setNextIndex();
                         this.isLocked = false;
                     }, this.duration);
                 });
             });
         },
     }"
     x-init="start()"
     class="relative min-h-[780px] lg:min-h-[600px]">

     <div class="absolute inset-0">
         <div class="hero-slide absolute inset-0 bg-cover bg-center h-full w-full"
             :style="slides.length ? `background-image: url('${slides[currentIndex]}')` : ''"></div>

         <div x-cloak class="hero-slide absolute inset-0 bg-cover bg-center h-full w-full transition-opacity ease-in-out opacity-0"
             :class="isFading ? 'opacity-100' : 'opacity-0'"
             :style="slides.length
                 ? `background-image: url('${slides[nextIndex]}'); transition-duration: ${isFading ? duration : 0}ms;`
                 : `transition-duration: ${isFading ? duration : 0}ms;`"></div>
     </div>
    <div class="absolute inset-0 bg-black/60 z-10"></div>

    <div class="w-full max-w-screen-xl mx-auto flex flex-col items-center justify-center relative z-10 pt-36 md:pt-44 pb-4">

        {{-- Khối Nội dung Chính --}}
        <div class="text-white z-10 text-center max-w-4xl drop-shadow-[0_4px_20px_rgba(0,0,0,0.55)]">
            <p class="text-sm uppercase tracking-[0.35em] mb-3 font-medium text-[#D4AF37]">
                CHÀO MỪNG ĐẾN VỚI OZIA</p>

            <h1
                class="text-5xl md:text-7xl font-serif font-extrabold mb-6 leading-tight
                   whitespace-nowrap [text-shadow:0_8px_25px_rgba(0,0,0,0.8)]">
                OZIA Khách Sạn Sang Trọng
            </h1>

            <p class="text-lg md:text-xl mb-6 mx-auto font-light text-gray-100 max-w-3xl">
                Trải nghiệm sự xa hoa tột đỉnh. Chúng tôi cam kết mang đến dịch vụ 5 sao cá nhân hóa,
                nơi mọi chi tiết đều được chạm khắc bằng vàng.
            </p>
        </div>

    </div>




    <div
        class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-[55%] z-30 w-full flex justify-center px-4 h-[140px]">

        <form action="{{ route('client.phong') }}" method="GET"
            class="w-full max-w-7xl bg-white rounded-2xl shadow-2xl p-8 border border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6">
                {{-- Ngày nhận --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">Ngày nhận phòng</label>
                    <input type="date" name="checkin" id="checkin_filter"
                        value="{{ request('checkin', $checkin ?? '') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white text-sm focus:border-yellow-500 focus:ring-yellow-500">
                </div>

                {{-- Ngày trả --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">Ngày trả phòng</label>
                    <input type="date" name="checkout" id="checkout_filter"
                        value="{{ request('checkout', $checkout ?? '') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white text-sm focus:border-yellow-500 focus:ring-yellow-500">
                </div>

                {{-- Loại phòng --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">Loại phòng</label>
                    <select name="loai_phong" id="loai_phong"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white text-sm focus:border-yellow-500 focus:ring-yellow-500">
                        <option value="">Tất cả loại phòng</option>
                        @foreach ($menuLoaiPhongs ?? [] as $loai)
                            <option value="{{ $loai->id }}"
                                {{ request('loai_phong') == $loai->id ? 'selected' : '' }}>
                                {{ $loai->ten_loai }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Giá từ --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">Giá từ (VNĐ)</label>
                    <input type="number" name="gia_min" id="gia_min" placeholder="0" value="{{ request('gia_min') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white text-sm focus:border-yellow-500 focus:ring-yellow-500 placeholder-black font-semibold">
                </div>

                {{-- Giá đến --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">Giá đến (VNĐ)</label>
                    <input type="number" name="gia_max" id="gia_max" placeholder="Không giới hạn"
                        value="{{ request('gia_max') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white text-sm focus:border-yellow-500 focus:ring-yellow-500 placeholder-black">
                </div>

                {{-- Button --}}
                <div class="flex items-end">
                    <button type="submit"
                        class="w-full bg-[#D4AF37] text-white px-6 py-3 rounded-lg hover:bg-[#b68b00] transition-colors font-semibold text-sm shadow-md">
                        <i class="fas fa-search mr-2"></i> Tìm phòng
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="absolute bottom-5 left-1/2 -translate-x-1/2 flex space-x-2 z-20">
        <template x-for="i in slides.length" :key="i">
            <button @click="goTo(i - 1)"
                :class="{ 'bg-white w-10': currentIndex === i - 1, 'bg-gray-400 w-3': currentIndex !== i - 1 }"
                class="h-3 rounded-full transition-all duration-300 cursor-pointer">
            </button>
        </template>
    </div>
</div>

<style>
    .hero-slide {
        will-change: opacity, transform;
        backface-visibility: hidden;
        transform: translateZ(0);
        background-color: #000;
    }

    .placeholder-dark::placeholder {
        color: #374151 !important;
        /* Ví dụ: một màu xám đậm hơn */
        opacity: 1;
        /* Đảm bảo độ mờ (opacity) là 1 để màu hiển thị đầy đủ */
    }

    /* Cho trình duyệt IE và Edge */
    .placeholder-dark::-ms-input-placeholder {
        color: #374151 !important;
    }

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
