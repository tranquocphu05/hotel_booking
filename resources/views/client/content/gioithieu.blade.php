@extends('layouts.client')
@section('title', 'Giới thiệu')
@section('content')

    <div class="bg-gray-50 py-12 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <h2 class="text-4xl font-bold text-gray-800 mb-3">Giới thiệu</h2>
            <p class="text-gray-500 text-sm">
                <a href="{{ route('client.dashboard') }}" class="hover:text-blue-600 transition">Trang chủ</a>
                <span class="mx-1 text-gray-400">/</span>
                <span class="text-gray-800 font-medium">Giới thiệu</span>
            </p>
        </div>
    </div>

<!-- Hero: White luxury -->
<section class="py-24 bg-white mb-20">
  <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-2 gap-12 items-center">
    <!-- Left: Text -->
    <div data-aos="fade-right" class="space-y-6">
      <h1 class="font-serif text-4xl sm:text-5xl md:text-6xl font-extrabold leading-tight text-gray-900">
        Chào mừng đến với
        <span class="gold">Ozia Hotel</span>
      </h1>

      <p class="text-gray-700 max-w-xl leading-relaxed text-lg">
        Kết hợp tinh tế giữa kiến trúc cổ điển và tiện nghi hiện đại — OZIA mang đến trải nghiệm nghỉ dưỡng
        đẳng cấp, ấm áp và đầy phong cách. Dịch vụ tận tâm, không gian nghệ thuật và từng chi tiết đều được
        chăm chút tỉ mỉ.
      </p>

      <ul class="space-y-3 text-gray-700">
        @foreach (['Giảm 20% giá phòng', 'Bữa sáng thượng hạng', 'Concierge 24/7', 'Wifi tốc độ cao', 'Ưu đãi nhà hàng & spa'] as $item)
          <li class="flex items-center">
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-yellow-50 text-yellow-500 mr-4 lux-shadow">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
              </svg>
            </span>
            <span class="text-gray-800 font-medium">{{ $item }}</span>
          </li>
        @endforeach
      </ul>

      <div class="mt-6 flex items-center gap-4">
        <a href="{{ route('client.phong') }}" class="inline-flex items-center gap-3 px-6 py-3 rounded-full gold-bg text-gray-900 font-semibold shadow-lg transform transition hover:scale-[1.02]">
          Khám phá ngay
        </a>
        <a href="#gallery" class="text-gray-600 hover:underline underline-offset-4">Xem bộ sưu tập</a>
      </div>
    </div>

    <!-- Right: Collage -->
    <div data-aos="fade-left" class="flex items-center justify-center">
      <div class="w-full grid grid-cols-2 gap-4">
        <div class="col-span-2 rounded-3xl overflow-hidden lux-shadow relative">
          <img src="{{ asset('img/about/about-p1.jpg') }}" alt="Sona main" class="w-full h-80 md:h-96 object-cover">
          <div class="absolute inset-0 gallery-mask"></div>
          <div class="absolute left-6 bottom-6">
            <div class="bg-white/80 rounded-full px-4 py-2 font-semibold text-gray-900">Ozia Suite</div>
          </div>
        </div>

        <div class="rounded-2xl overflow-hidden shadow-lg transform transition hover:scale-[1.02]">
          <img src="{{ asset('img/about/about-p2.jpg') }}" alt="detail 1" class="w-full h-44 object-cover">
        </div>
        <div class="rounded-2xl overflow-hidden shadow-lg transform transition hover:scale-[1.02]">
          <img src="{{ asset('img/about/about-p3.jpg') }}" alt="detail 2" class="w-full h-44 object-cover">
        </div>
      </div>
    </div>
  </div>
</section>

    <!-- Video Section -->
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



    <!-- Gallery -->
<section id="gallery" class="py-24 bg-gray-50">
  <div class="max-w-6xl mx-auto px-4 text-center">
    <h3 class="text-3xl font-extrabold text-gray-900 mb-3">Bộ sưu tập</h3>
    <p class="text-gray-500 mb-12">Không gian, tiện nghi và những góc chụp tạo cảm hứng.</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
      @foreach ([['gallery-1.jpg', 'Phòng sang trọng'], ['gallery-2.jpg', 'Không gian thư giãn'], ['gallery-3.jpg', 'Phòng gia đình'], ['gallery-4.jpg', 'Phòng luxury'], ['gallery-5.jpg', 'Bãi biển đẹp'], ['gallery-6.jpg', 'Phong cảnh tao nhã']] as [$img, $title])
        <div class="relative group rounded-2xl overflow-hidden shadow-lg">
          <img src="{{ asset('img/gallery/' . $img) }}" class="w-full h-72 object-cover transition-transform duration-700 group-hover:scale-105">
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition duration-500 flex items-end p-6">
            <div class="text-left">
              <h4 class="text-white text-lg font-semibold">{{ $title }}</h4>
              <p class="text-sm text-gray-200 mt-1">Trải nghiệm không gian đẳng cấp</p>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>

    <div id="videoPopup" class="fixed inset-0 bg-black/60 hidden z-50 flex items-center justify-center px-4">
        <div class="relative w-full max-w-2xl mx-auto max-h-[80vh]">
            <!-- Video box: giữ tỉ lệ 16:9, không vượt quá viewport -->
            <div
                class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-200 w-full aspect-[16/9] animate-fadeIn">
                <video id="popupVideo" controls class="w-full h-full object-contain bg-black">
                    <source src="{{ asset('videos/video-khach-san.mp4') }}" type="video/mp4">
                    Trình duyệt của bạn không hỗ trợ video.
                </video>
            </div>

            <!-- Nút đóng (nằm sát khung, nhỏ gọn) -->
            <button onclick="closeVideoPopup()"
                class="absolute -top-3 -right-3 bg-white text-gray-700 rounded-full w-9 h-9 shadow-md
                   flex items-center justify-center border border-gray-200 hover:bg-red-500 hover:text-white
                   transition duration-150">
                ✕
            </button>
        </div>
    </div>

    <script>
        function openVideoPopup() {
            const popup = document.getElementById('videoPopup');
            const video = document.getElementById('popupVideo');
            popup.classList.remove('hidden');
            video.currentTime = 0;
            video.play();
        }

        function closeVideoPopup() {
            const popup = document.getElementById('videoPopup');
            const video = document.getElementById('popupVideo');
            video.pause();
            popup.classList.add('hidden');
        }
    </script>
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(10px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.35s ease-out;
        }

        .gold {
            color: #C89A3A;
        }

        /* text gold */
        .gold-bg {
            background: linear-gradient(90deg, #caa73a, #ffd36b);
        }

        .glass {
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(6px) saturate(120%);
        }

        .lux-shadow {
            box-shadow: 0 10px 30px rgba(6, 6, 6, 0.25), inset 0 -6px 20px rgba(0, 0, 0, 0.06);
        }

        .underline-lux {
            text-decoration-thickness: 3px;
            text-underline-offset: 6px;
            text-decoration-color: #c89a3a;
        }

        .play-ring {
            box-shadow: 0 6px 18px rgba(200, 154, 58, 0.18);
            border: 2px solid rgba(255, 255, 255, 0.06);
        }

        .gallery-mask {
            background: linear-gradient(180deg, rgba(0, 0, 0, 0) 40%, rgba(0, 0, 0, 0.45) 100%);
        }

        @media (max-width: 640px) {
            .aspect-4-3 {
                aspect-ratio: 4/3;
            }
        }
    </style>
@endsection
