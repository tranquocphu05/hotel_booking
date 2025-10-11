@extends('layouts.admin')

@section('title', 'Sửa đặt phòng')

@section('admin_content')
    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Sửa thông tin đặt phòng <b>{{ $booking->phong->ten_phong }}</b></h2>

                    <form action="{{ route('admin.dat_phong.update', $booking->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <!-- Thông tin phòng -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Thông tin phòng</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label for="phong_id" class="block text-sm font-medium text-gray-700">Chọn phòng mới</label>
                                        <select name="phong_id" id="phong_id" 
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            @foreach($rooms as $room)
                                                <option value="{{ $room->id }}" {{ $booking->phong_id == $room->id ? 'selected' : '' }}>
                                                    {{ $room->ten_phong }} ({{ $room->loaiPhong->ten_loai }}) - {{ number_format($room->gia, 0, ',', '.') }} VNĐ
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('phong_id')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="trang_thai" class="block text-sm font-medium text-gray-700">Trạng thái</label>
                                        <select name="trang_thai" id="trang_thai" 
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="cho_xac_nhan" {{ $booking->trang_thai == 'cho_xac_nhan' ? 'selected' : '' }}>Chờ xác nhận</option>
                                            <option value="da_xac_nhan" {{ $booking->trang_thai == 'da_xac_nhan' ? 'selected' : '' }}>Đã xác nhận</option>
                                            <option value="da_huy" {{ $booking->trang_thai == 'da_huy' ? 'selected' : '' }}>Đã hủy</option>
                                            <option value="da_tra" {{ $booking->trang_thai == 'da_tra' ? 'selected' : '' }}>Đã trả phòng</option>
                                        </select>
                                        @error('trang_thai')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Thông tin đặt phòng -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Thông tin đặt phòng</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label for="so_nguoi" class="block text-sm font-medium text-gray-700">Số người</label>
                                        <input type="number" name="so_nguoi" id="so_nguoi" 
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            value="{{ old('so_nguoi', $booking->so_nguoi) }}" required>
                                        @error('so_nguoi')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="ngay_nhan" class="block text-sm font-medium text-gray-700">Ngày nhận phòng</label>
                                            <input type="date" name="ngay_nhan" id="ngay_nhan" 
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                value="{{ old('ngay_nhan', date('Y-m-d', strtotime($booking->ngay_nhan))) }}" required>
                                            @error('ngay_nhan')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="ngay_tra" class="block text-sm font-medium text-gray-700">Ngày trả phòng</label>
                                            <input type="date" name="ngay_tra" id="ngay_tra" 
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                value="{{ old('ngay_tra', date('Y-m-d', strtotime($booking->ngay_tra))) }}" required>
                                            @error('ngay_tra')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Thông tin khách hàng -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Thông tin khách hàng</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label for="username" class="block text-sm font-medium text-gray-700">Tên khách hàng</label>
                                        <input type="text" name="username" id="username" 
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            value="{{ old('username', $booking->username) }}" required>
                                        @error('username')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" name="email" id="email" 
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            value="{{ old('email', $booking->email) }}" required>
                                        @error('email')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="sdt" class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                                            <input type="text" name="sdt" id="sdt" 
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                value="{{ old('sdt', $booking->sdt) }}" required>
                                            @error('sdt')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="cccd" class="block text-sm font-medium text-gray-700">CCCD/CMND</label>
                                            <input type="text" name="cccd" id="cccd" 
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                value="{{ old('cccd', $booking->cccd) }}" required>
                                            @error('cccd')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($booking->voucher_id)
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Thông tin voucher</h3>
                                    <p class="text-sm text-gray-600">Mã voucher: <span class="font-medium">{{ $booking->voucher->ma_voucher }}</span></p>
                                    <p class="text-sm text-gray-600">Giảm giá: <span class="font-medium">{{ $booking->voucher->giam_gia }}%</span></p>
                                </div>
                            @endif

                            <div class="pt-5">
                                <div class="flex justify-between">
                                    <a href="{{ route('admin.dat_phong.index') }}" 
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                        </svg>
                                        Quay lại
                                    </a>
                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Cập nhật
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ngayNhanInput = document.getElementById('ngay_nhan');
            const ngayTraInput = document.getElementById('ngay_tra');

            // Đặt ngày tối thiểu cho ngày nhận phòng là ngày hiện tại
            const today = new Date().toISOString().split('T')[0];
            ngayNhanInput.setAttribute('min', today);

            // Cập nhật ngày trả phòng tối thiểu khi ngày nhận thay đổi
            ngayNhanInput.addEventListener('change', function() {
                ngayTraInput.setAttribute('min', this.value);
                if (ngayTraInput.value && ngayTraInput.value < this.value) {
                    ngayTraInput.value = this.value;
                }
            });
        });
    </script>
    @endpush
@endsection