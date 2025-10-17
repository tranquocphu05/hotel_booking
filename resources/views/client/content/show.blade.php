@extends('layouts.client')
@section('title', $room['name'])
@section('content')

<div class="bg-gray-50 py-12 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold text-gray-800 mb-3">{{ $room['name'] }}</h2>
        <p class="text-gray-500 text-sm">
            <a href="{{ route('client.dashboard') }}" class="hover:text-blue-600">Trang chủ</a>
            <span class="mx-1 text-gray-400">/</span>
            <a href="{{ route('client.phong') }}" class="hover:text-blue-600">Phòng</a>
            <span class="mx-1 text-gray-400">/</span>
            <span class="text-gray-800 font-medium">{{ $room['name'] }}</span>
        </p>
    </div>
</div>

<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-3 gap-10">
        <div class="md:col-span-2">
            <img src="{{ asset($room['img']) }}" alt="{{ $room['name'] }}" class="rounded-xl shadow-lg mb-6">
            <h3 class="text-3xl font-bold text-gray-800 mb-4">{{ $room['name'] }}</h3>
            <p class="text-gray-600 mb-6">{{ $room['desc'] }}</p>

            <table class="w-full text-left text-gray-700 mb-8">
                <tbody>
                    <tr><td class="font-semibold py-2">Giá:</td><td>{{ number_format($room['price'],0,',','.') }} VNĐ / đêm</td></tr>
                    <tr><td class="font-semibold py-2">Diện tích:</td><td>{{ $room['size'] }}</td></tr>
                    <tr><td class="font-semibold py-2">Sức chứa:</td><td>{{ $room['capacity'] }}</td></tr>
                    <tr><td class="font-semibold py-2">Giường:</td><td>{{ $room['bed'] }}</td></tr>
                    <tr><td class="font-semibold py-2">Dịch vụ:</td><td>{{ $room['services'] }}</td></tr>
                </tbody>
            </table>

            <a href="#" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">Đặt phòng ngay</a>
        </div>

        <div class="bg-gray-50 p-6 rounded-xl shadow-md">
            <h4 class="text-xl font-bold mb-4 text-gray-800">Đặt phòng nhanh</h4>
            <form action="#">
                <label class="block mb-2 text-gray-700">Ngày nhận phòng</label>
                <input type="date" class="w-full border rounded-lg p-2 mb-4">
                <label class="block mb-2 text-gray-700">Ngày trả phòng</label>
                <input type="date" class="w-full border rounded-lg p-2 mb-4">
                <label class="block mb-2 text-gray-700">Số người</label>
                <select class="w-full border rounded-lg p-2 mb-6">
                    <option>1 người</option>
                    <option>2 người</option>
                    <option>3 người</option>
                    <option>4 người</option>
                </select>
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition">Kiểm tra phòng</button>
            </form>
        </div>
    </div>
</section>

@endsection
