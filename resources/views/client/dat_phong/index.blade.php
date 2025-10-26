@extends('layouts.client')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Đặt Phòng Khách Sạn</h1>
        <a href="{{ route('client.da_dat_phong') }}" class="inline-flex items-center bg-teal-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-teal-600 transition duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h4a1 1 0 100-2H7zm0 4a1 1 0 100 2h4a1 1 0 100-2H7z" clip-rule="evenodd" />
            </svg>
            Xem phòng đã đặt
        </a>
    </div>

    <!-- Booking Details Form -->
    <div class="bg-white shadow-lg rounded-lg p-8 mb-8">
        <h2 class="text-2xl font-semibold mb-6">1. Chọn ngày và số lượng khách</h2>
        <form>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <label for="checkin_date" class="block text-sm font-medium text-gray-700">Ngày nhận phòng</label>
                    <input type="date" name="checkin_date" id="checkin_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="checkout_date" class="block text-sm font-medium text-gray-700">Ngày trả phòng</label>
                    <input type="date" name="checkout_date" id="checkout_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="num_adults" class="block text-sm font-medium text-gray-700">Số người lớn</label>
                    <input type="number" name="num_adults" id="num_adults" min="1" value="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="num_children" class="block text-sm font-medium text-gray-700">Số trẻ em</label>
                    <input type="number" name="num_children" id="num_children" min="0" value="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>
            </div>
             <div class="mt-6 text-right">
                <button type="button" class="bg-blue-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-blue-700 transition duration-300">
                    Tìm phòng
                </button>
            </div>
        </form>
    </div>

    <!-- Room Selection -->
    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-6">2. Chọn phòng phù hợp</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Room 1 -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden transform hover:scale-105 transition duration-300">
                <img src="{{ asset('img/room/room-1.jpg') }}" alt="Phòng Standard" class="w-full h-48 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Phòng Standard</h3>
                    <p class="text-gray-600 mb-4">Phòng tiêu chuẩn với đầy đủ tiện nghi cơ bản, phù hợp cho 2 người.</p>
                    <div class="flex justify-between items-center">
                        <p class="text-lg font-bold text-blue-600">1.200.000 VNĐ<span class="text-sm font-normal text-gray-500">/đêm</span></p>
                        <button class="bg-green-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-600 transition">Chọn</button>
                    </div>
                </div>
            </div>
            <!-- Room 2 -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden transform hover:scale-105 transition duration-300">
                <img src="{{ asset('img/room/room-2.jpg') }}" alt="Phòng Deluxe" class="w-full h-48 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Phòng Deluxe</h3>
                    <p class="text-gray-600 mb-4">Phòng rộng rãi hơn với view đẹp, nội thất sang trọng.</p>
                    <div class="flex justify-between items-center">
                        <p class="text-lg font-bold text-blue-600">2.000.000 VNĐ<span class="text-sm font-normal text-gray-500">/đêm</span></p>
                        <button class="bg-green-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-600 transition">Chọn</button>
                    </div>
                </div>
            </div>
            <!-- Room 3 -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden transform hover:scale-105 transition duration-300">
                <img src="{{ asset('img/room/room-3.jpg') }}" alt="Phòng Suite" class="w-full h-48 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Phòng Suite</h3>
                    <p class="text-gray-600 mb-4">Phòng cao cấp nhất với không gian riêng biệt và dịch vụ đặc biệt.</p>
                    <div class="flex justify-between items-center">
                        <p class="text-lg font-bold text-blue-600">3.500.000 VNĐ<span class="text-sm font-normal text-gray-500">/đêm</span></p>
                        <button class="bg-green-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-600 transition">Chọn</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Summary and Payment -->
    <div>
        <h2 class="text-2xl font-semibold mb-6">3. Xác nhận và thanh toán</h2>
        <div class="bg-white shadow-lg rounded-lg p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Left side: Voucher and User Info -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Thông tin khách hàng</h3>
                    <div class="space-y-4">
                         <div>
                            <label for="full_name" class="block text-sm font-medium text-gray-700">Họ và tên</label>
                            <input type="text" name="full_name" id="full_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Nguyễn Văn A">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="example@email.com">
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                            <input type="tel" name="phone" id="phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="09xxxxxxxx">
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold mt-8 mb-4">Chọn mã giảm giá có sẵn</h3>
                    <div class="space-y-3">
                        <!-- Voucher 1 -->
                        <label for="voucher1" class="flex items-center justify-between p-4 border rounded-lg cursor-pointer hover:bg-gray-100 has-[:checked]:bg-blue-50 has-[:checked]:border-blue-400">
                            <div>
                                <p class="font-semibold text-gray-800">GIAM10</p>
                                <p class="text-sm text-gray-600">Giảm 10% cho tất cả đơn hàng.</p>
                            </div>
                            <input type="radio" name="voucher_option" id="voucher1" class="form-radio h-5 w-5 text-blue-600">
                        </label>
                        <!-- Voucher 2 -->
                        <label for="voucher2" class="flex items-center justify-between p-4 border rounded-lg cursor-pointer hover:bg-gray-100 has-[:checked]:bg-blue-50 has-[:checked]:border-blue-400">
                            <div>
                                <p class="font-semibold text-gray-800">UPGRADEFREE</p>
                                <p class="text-sm text-gray-600">Miễn phí nâng cấp hạng phòng.</p>
                            </div>
                            <input type="radio" name="voucher_option" id="voucher2" class="form-radio h-5 w-5 text-blue-600">
                        </label>
                        <!-- Voucher 3 -->
                        <label for="voucher3" class="flex items-center justify-between p-4 border rounded-lg cursor-pointer hover:bg-gray-100 has-[:checked]:bg-blue-50 has-[:checked]:border-blue-400">
                            <div>
                                <p class="font-semibold text-gray-800">GIAM500K</p>
                                <p class="text-sm text-gray-600">Giảm 500.000 VNĐ cho đơn từ 5.000.000 VNĐ.</p>
                            </div>
                            <input type="radio" name="voucher_option" id="voucher3" class="form-radio h-5 w-5 text-blue-600">
                        </label>
                    </div>
                </div>

                <!-- Right side: Order Summary -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 border-b pb-4">Tóm tắt đơn hàng</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Phòng đã chọn:</span>
                            <span class="font-semibold">Phòng Deluxe</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Số đêm:</span>
                            <span class="font-semibold">2</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Giá mỗi đêm:</span>
                            <span class="font-semibold">2.000.000 VNĐ</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tạm tính:</span>
                            <span class="font-semibold">4.000.000 VNĐ</span>
                        </div>
                        <div class="flex justify-between text-green-600">
                            <span class="text-gray-600">Giảm giá (VOUCHER10):</span>
                            <span class="font-semibold">- 400.000 VNĐ</span>
                        </div>
                        <div class="border-t pt-4 mt-4 flex justify-between items-center">
                            <span class="text-xl font-bold">Tổng cộng:</span>
                            <span class="text-xl font-bold text-blue-600">3.600.000 VNĐ</span>
                        </div>
                    </div>
                     <button type="submit" class="w-full mt-6 bg-red-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-red-700 transition duration-300">
                        Xác nhận đặt phòng
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection