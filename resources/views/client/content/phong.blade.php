@extends('layouts.client')

@section('client_content')

<div class="bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-semibold text-center text-gray-800 mb-2">Our Rooms</h2>
        <p class="text-center text-gray-500 text-sm">
            <a href="{{ url('/') }}" class="hover:text-red-600">Home</a> / 
            <span class="text-gray-800">Rooms</span>
        </p>
    </div>
</div>

<section class="max-w-7xl mx-auto px-4 py-12">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($rooms as $phong)
        <div class="border border-gray-200 shadow-sm hover:shadow-lg transition rounded overflow-hidden">
            <img src="{{ asset($phong['img']) }}" alt="{{ $phong['name'] }}" class="w-full h-56 object-cover">
            <div class="p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ $phong['name'] }}</h3>
                <p class="text-[#b88746] font-semibold text-lg mb-3">{{ number_format($phong['price'],0,',','.') }} VNĐ <span class="text-sm text-gray-500">/Per night</span></p>

                <ul class="text-gray-600 text-sm space-y-1 mb-4">
                    <li><strong>Size:</strong> {{ $phong['size'] }}</li>
                    <li><strong>Capacity:</strong> {{ $phong['capacity'] }}</li>
                    <li><strong>Bed:</strong> {{ $phong['bed'] }}</li>
                    <li><strong>Services:</strong> {{ $phong['services'] }}</li>
                </ul>

                <a href="{{ route('client.phong.show', $phong['id']) }}" class="text-[#b88746] text-sm font-semibold border-b border-[#b88746] hover:text-[#9a6a34]">
                    More Details
                </a>
            </div>
        </div>
        @endforeach
    </div>
</section>

@endsection
