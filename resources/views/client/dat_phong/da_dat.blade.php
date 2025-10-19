@extends('layouts.client')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Phòng Đã Đặt Của Bạn</h1>
        <a href="{{ route('client.datphong') }}" class="inline-flex items-center bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Đặt phòng mới
        </a>
    </div>

    <div class="bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-2xl font-semibold mb-6">Lịch sử đặt phòng</h2>

        {{-- This is a placeholder. In a real application, you would loop through bookings from the database. --}}
        @if(true)
            <div class="space-y-6">
                <!-- Booking 1: Confirmed -->
                <div class="border rounded-lg p-4 flex flex-col md:flex-row items-start md:items-center gap-4 bg-green-50 border-green-200">
                    <img src="{{ asset('img/room/room-2.jpg') }}" alt="Phòng Deluxe" class="w-full md:w-48 h-32 object-cover rounded-md">
                    <div class="flex-grow">
                        <h3 class="text-xl font-bold mb-1">Phòng Deluxe</h3>
                        <p class="text-gray-600"><strong>Ngày nhận phòng:</strong> 25/10/2025</p>
                        <p class="text-gray-600"><strong>Ngày trả phòng:</strong> 28/10/2025</p>
                        <p class="text-gray-600"><strong>Mã đặt phòng:</strong> #HD-12345</p>
                    </div>
                    <div class="flex flex-col items-end space-y-2 self-stretch justify-between">
                        <span class="text-sm font-semibold text-white bg-green-500 px-3 py-1 rounded-full">Đã xác nhận</span>
                        <button class="text-sm text-white bg-indigo-500 hover:bg-indigo-600 font-semibold py-2 px-4 rounded-lg transition">Xem chi tiết</button>
                    </div>
                </div>

                <!-- Booking 2: Pending -->
                <div class="border rounded-lg p-4 flex flex-col md:flex-row items-start md:items-center gap-4 bg-yellow-50 border-yellow-200">
                    <img src="{{ asset('img/room/room-1.jpg') }}" alt="Phòng Standard" class="w-full md:w-48 h-32 object-cover rounded-md">
                    <div class="flex-grow">
                        <h3 class="text-xl font-bold mb-1">Phòng Standard</h3>
                        <p class="text-gray-600"><strong>Ngày nhận phòng:</strong> 15/11/2025</p>
                        <p class="text-gray-600"><strong>Ngày trả phòng:</strong> 16/11/2025</p>
                        <p class="text-gray-600"><strong>Mã đặt phòng:</strong> #HD-67890</p>
                    </div>
                    <div class="flex flex-col items-end space-y-2 self-stretch justify-between">
                        <span class="text-sm font-semibold text-white bg-yellow-500 px-3 py-1 rounded-full">Chờ xử lý</span>
                        <button class="text-sm text-red-500 hover:text-red-700 font-semibold py-2 px-4 rounded-lg transition">Hủy đặt phòng</button>
                    </div>
                </div>

                <!-- Booking 3: Cancelled -->
                <div class="border rounded-lg p-4 flex flex-col md:flex-row items-start md:items-center gap-4 bg-red-50 border-red-200 opacity-70">
                    <img src="{{ asset('img/room/room-3.jpg') }}" alt="Phòng Suite" class="w-full md:w-48 h-32 object-cover rounded-md">
                    <div class="flex-grow">
                        <h3 class="text-xl font-bold mb-1">Phòng Suite</h3>
                        <p class="text-gray-600"><strong>Ngày nhận phòng:</strong> 01/10/2025</p>
                        <p class="text-gray-600"><strong>Ngày trả phòng:</strong> 05/10/2025</p>
                        <p class="text-gray-600"><strong>Mã đặt phòng:</strong> #HD-54321</p>
                    </div>
                    <div class="flex flex-col items-end space-y-2 self-stretch justify-between">
                        <span class="text-sm font-semibold text-white bg-red-500 px-3 py-1 rounded-full">Đã hủy</span>
                        <button class="text-sm text-gray-500 font-semibold py-2 px-4 rounded-lg cursor-not-allowed">Đã hủy</button>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Chưa có phòng nào được đặt</h3>
                <p class="mt-1 text-sm text-gray-500">Bạn chưa có lịch sử đặt phòng nào.</p>
            </div>
        @endif
    </div>
</div>
@endsection
