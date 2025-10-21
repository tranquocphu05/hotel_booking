@extends('layouts.client')

@section('title', $phong->ten_phong ?? 'Đặt phòng')

@section('client_content')
<div class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left card: room summary -->
        <div class="lg:col-span-1 bg-white p-6 rounded shadow">
            @if(isset($phong->img) && $phong->img)
                <img src="{{ asset($phong->img) }}" alt="room" class="w-full h-48 object-cover rounded mb-4">
            @else
                <img src="/img/room/room-1.jpg" alt="room" class="w-full h-48 object-cover rounded mb-4">
            @endif

            <h3 class="text-lg font-semibold">{{ $phong->ten_phong ?? 'Room Title' }}</h3>
            <p class="text-sm text-gray-600">Loại: {{ optional($phong->loaiPhong)->ten_loai ?? '-' }}</p>

            <div class="mt-4 text-sm">
                <div class="flex items-center gap-2">
                    <span class="bg-green-500 text-white rounded px-2 py-1 text-xs">9.2</span>
                    <span class="text-sm text-gray-700">Tuyệt hảo · N/A đánh giá</span>
                </div>
                <div class="mt-4">
                    <h4 class="font-medium">Chi tiết đặt phòng của bạn</h4>
                    <p class="text-sm text-gray-700">Giá: {{ number_format($phong->gia ?? 0) }} VND</p>
                </div>
                <div class="mt-4 font-semibold">Tổng: {{ number_format($phong->gia ?? 0) }} VND</div>
            </div>
        </div>

        <!-- Right card: booking form -->
        <div class="lg:col-span-2 bg-white p-6 rounded shadow">
            <h2 class="text-xl font-semibold mb-4">Nhập thông tin chi tiết của bạn</h2>

            @if(session('status'))
                <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('status') }}</div>
            @endif

            <form action="{{ route('booking.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="phong_id" value="{{ $phong->id }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                            <label class="block text-sm font-medium">Họ Và Tên  (tiếng Anh) *</label>
                            <input type="text" name="first_name" value="{{ old('first_name', auth()->check() ? auth()->user()->ho_ten : '') }}" class="mt-1 block w-full border rounded p-2 @error('first_name') border-red-500 @enderror">
                        @error('first_name') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                    </div>


                    <div>
                        <label class="block text-sm font-medium">Địa chỉ email *</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" class="mt-1 block w-full border rounded p-2 @error('email') border-red-500 @enderror">
                        @error('email') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Số điện thoại</label>
                        <input type="text" name="phone" value="{{ old('phone', auth()->user()->sdt ?? '') }}" class="mt-1 block w-full border rounded p-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Ngày nhận</label>
                        <input type="date" name="ngay_nhan" value="{{ old('ngay_nhan', isset($checkin) ? $checkin : '') }}" class="mt-1 block w-full border rounded p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Ngày trả</label>
                        <input type="date" name="ngay_tra" value="{{ old('ngay_tra', isset($checkout) ? $checkout : '') }}" class="mt-1 block w-full border rounded p-2">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium">Số người</label>
                        <input type="number" name="so_nguoi" value="{{ old('so_nguoi', isset($guests) ? $guests : 1) }}" min="1" class="mt-1 block w-1/6 border rounded p-2">
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Hoàn tất đặt phòng</button>
                    <a href="{{ url()->previous() }}" class="text-sm text-gray-600">Quay lại</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
