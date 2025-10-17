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
<!-- About Section -->
<section class="py-24 bg-white mb-20">
    <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-2 gap-16 items-center">
        <div data-aos="fade-right">
            <h2 class="text-4xl font-extrabold text-gray-800 mb-8">
                Chào mừng đến với <span class="text-blue-600">Sona Hotel</span>
            </h2>
            <p class="text-gray-600 leading-relaxed mb-8 text-justify">
                Được xây dựng từ năm 1910 trong thời kỳ Belle Epoque, khách sạn tọa lạc tại trung tâm thành phố, 
                mang đến trải nghiệm sang trọng, đẳng cấp và gần gũi thiên nhiên.
            </p>
            <ul class="space-y-4 text-gray-700">
                @foreach ([
                    'Giảm 20% giá phòng',
                    'Bữa sáng miễn phí mỗi ngày',
                    'Dịch vụ giặt ủi 3 món/ngày',
                    'Wifi tốc độ cao miễn phí',
                    'Giảm 20% tại nhà hàng & bar'
                ] as $item)
                <li class="flex items-center">
                    <span class="text-green-500 text-lg mr-3">✔</span> {{ $item }}
                </li>
                @endforeach
            </ul>
        </div>
        <div class="grid grid-cols-2 gap-6" data-aos="fade-left">
            <img src="{{ asset('img/about/about-p1.jpg') }}" alt="Restaurant" class="rounded-xl shadow-lg hover:scale-105 transition-transform duration-300">
            <img src="{{ asset('img/about/about-p2.jpg') }}" alt="Travel" class="rounded-xl shadow-lg hover:scale-105 transition-transform duration-300">
            <img src="{{ asset('img/about/about-p3.jpg') }}" alt="Event" class="rounded-xl shadow-lg hover:scale-105 transition-transform duration-300 col-span-2">
        </div>
    </div>
</section>

<!-- Video Section -->
<section class="relative bg-center bg-cover text-center text-white py-32 mb-20" style="background-image: url('{{ asset('img/video-bg.jpg') }}')">
    <div class="absolute inset-0 bg-black/60"></div>
    <div class="relative z-10 max-w-3xl mx-auto">
        <h2 class="text-4xl font-extrabold mb-6">Khám phá khách sạn & dịch vụ của chúng tôi</h2>
        <p class="text-gray-200 mb-10 text-lg leading-relaxed">Tận hưởng kỳ nghỉ tuyệt vời cùng không gian sang trọng và dịch vụ đẳng cấp.</p>
        <a href="https://www.youtube.com/watch?v=EzKkl64rRbM" target="_blank"
           class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-blue-600 hover:bg-blue-700 shadow-lg transition">
            <svg xmlns="http://www.w3.org/2000/svg" fill="white" viewBox="0 0 24 24" class="w-8 h-8 ml-1">
                <path d="M3 22v-20l18 10-18 10z"/>
            </svg>
        </a>
    </div>
</section>

<!-- Gallery -->
<section class="py-24 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold text-gray-800 mb-3">Bộ sưu tập</h2>
        <p class="text-gray-500 mb-14">Khám phá không gian nghỉ dưỡng của chúng tôi</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach ([
                ['gallery-1.jpg', 'Phòng sang trọng'],
                ['gallery-2.jpg', 'Không gian thư giãn'],
                ['gallery-3.jpg', 'Phòng gia đình'],
                ['gallery-4.jpg', 'Nhà hàng']
            ] as [$img, $title])
            <div class="relative group overflow-hidden rounded-xl shadow-lg">
                <img src="{{ asset('img/gallery/' . $img) }}" class="w-full h-72 object-cover transform group-hover:scale-110 transition duration-500">
                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white font-semibold text-lg transition">
                    {{ $title }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
