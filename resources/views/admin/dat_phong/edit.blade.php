@extends('layouts.admin')

@section('title', 'Sửa đặt phòng')

@section('admin_content')
    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Sửa thông tin đặt phòng</h2>

                    <form action="{{ route('admin.dat_phong.update', $booking->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Thông tin phòng</h3>
                                <p class="mt-1 text-sm text-gray-500">Phòng: {{ $booking->phong->ten_phong }} ({{ $booking->phong->loaiPhong->ten_loai }})</p>
                            </div>

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

                            @if($booking->voucher_id)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Mã voucher</label>
                                    <p class="mt-1 text-sm text-gray-500">{{ $booking->voucher->ma_voucher }}</p>
                                </div>
                            @endif

                            <div class="pt-5">
                                <div class="flex justify-between">
                                    <a href="{{ route('admin.dat_phong.index') }}" 
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Quay lại
                                    </a>
                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
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