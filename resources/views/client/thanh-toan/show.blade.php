@extends('layouts.client')

@section('title', 'Xác nhận Thanh toán')

@section('client_content')
    <div class="min-h-screen bg-gray-50">
        <div class="container mx-auto py-8 px-4">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Xác nhận Thanh toán</h1>
                <p class="text-gray-600">Hoàn tất đặt phòng của bạn</p>
            </div>

            <!-- Alert Messages -->
            @if(session('success') && session('booking_success'))
                <div class="max-w-4xl mx-auto mb-6">
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-300 text-green-800 px-6 py-4 rounded-xl shadow-lg">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center animate-pulse">
                                    <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-lg font-bold text-green-900 mb-1">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Đặt Phòng Thành Công!
                                </h3>
                                <p class="text-sm text-green-700 mb-2">
                                    {{ session('success') }}
                                </p>
                                <div class="bg-white/50 rounded-lg px-3 py-2 inline-block">
                                    <p class="text-xs text-green-800">
                                        <i class="fas fa-receipt mr-1"></i>
                                        Mã đặt phòng: <span class="font-bold text-green-900">#{{ session('booking_id') }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif(session('success'))
                <div class="max-w-4xl mx-auto mb-6">
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            {{ session('success') }}
                        </div>
                    </div>
                </div>
            @endif

        @if(session('error'))
                <div class="max-w-4xl mx-auto mb-6">
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            {{ session('error') }}
                        </div>
                    </div>
            </div>
        @endif

            <div class="max-w-6xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Booking Details -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6 border-b pb-3">Chi tiết đặt phòng</h2>

                        <!-- User Info -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Thông tin người đặt</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Họ tên:</span>
                                    <span class="font-medium">{{ $datPhong->username ?? ($datPhong->user->ho_ten ?? 'Khách ẩn danh') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Email:</span>
                                    <span class="font-medium">{{ $datPhong->email ?? ($datPhong->user->email ?? 'Chưa cập nhật') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">SĐT:</span>
                                    <span class="font-medium">{{ $datPhong->sdt ?? ($datPhong->user->sdt ?? 'Chưa cập nhật') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">CCCD:</span>
                                    <span class="font-medium">{{ $datPhong->user->cccd ?? 'Chưa cập nhật' }}</span>
                </div>
            </div>
                        </div>

                        <!-- Room Info -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Thông tin phòng</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Mã đặt phòng:</span>
                                    <span class="font-medium text-blue-600">#{{ $datPhong->id }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tên phòng:</span>
                                    <span class="font-medium">{{ $datPhong->phong->ten_phong }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Loại phòng:</span>
                                    <span class="font-medium">{{ $datPhong->phong->loaiPhong->ten_loai }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Ngày nhận:</span>
                                    <span class="font-medium">{{ $datPhong->ngay_nhan->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Ngày trả:</span>
                                    <span class="font-medium">{{ $datPhong->ngay_tra->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Số người:</span>
                                    <span class="font-medium">{{ $datPhong->so_nguoi }} người</span>
                                </div>
                            </div>
                        </div>

                        <!-- Total Price -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-center">
                                <p class="text-lg font-medium text-gray-600 mb-1">Tổng thanh toán</p>
                                <p class="text-2xl font-bold text-green-600">
                                    {{ number_format($datPhong->tong_tien, 0, ',', '.') }} VNĐ
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6 border-b pb-3">Phương thức thanh toán</h2>

                        <form action="{{ route('client.thanh-toan.store', ['datPhong' => $datPhong->id]) }}" method="POST">
                            @csrf
                            <div class="space-y-3">
                                <!-- Cash Payment -->
                                <label for="tien_mat" class="block">
                                    <div class="payment-option {{ old('phuong_thuc', $invoice->phuong_thuc) == 'tien_mat' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }} border-2 rounded-lg p-4 hover:border-gray-300 transition-colors cursor-pointer">
                                        <div class="flex items-center">
                                            <input type="radio" id="tien_mat" name="phuong_thuc" value="tien_mat" class="form-radio h-4 w-4 text-blue-600" {{ old('phuong_thuc', $invoice->phuong_thuc) == 'tien_mat' ? 'checked' : '' }}>
                                            <div class="ml-3 flex items-center">
                                                <div class="w-8 h-8 bg-green-100 rounded flex items-center justify-center mr-3">
                                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900">Thanh toán tại quầy</p>
                                                    <p class="text-sm text-gray-500">Tiền mặt</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                <!-- Bank Transfer -->
                                <label for="chuyen_khoan" class="block">
                                    <div class="payment-option {{ old('phuong_thuc', $invoice->phuong_thuc) == 'chuyen_khoan' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }} border-2 rounded-lg p-4 hover:border-gray-300 transition-colors cursor-pointer">
                                        <div class="flex items-center">
                                            <input type="radio" id="chuyen_khoan" name="phuong_thuc" value="chuyen_khoan" class="form-radio h-4 w-4 text-blue-600" {{ old('phuong_thuc', $invoice->phuong_thuc) == 'chuyen_khoan' ? 'checked' : '' }}>
                                            <div class="ml-3 flex items-center">
                                                <div class="w-8 h-8 bg-blue-100 rounded flex items-center justify-center mr-3">
                                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900">Chuyển khoản ngân hàng</p>
                                                    <p class="text-sm text-gray-500">Vietcombank</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                <!-- MoMo -->
                                <label for="momo" class="block">
                                    <div class="payment-option {{ old('phuong_thuc', $invoice->phuong_thuc) == 'momo' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }} border-2 rounded-lg p-4 hover:border-gray-300 transition-colors cursor-pointer">
                                        <div class="flex items-center">
                                            <input type="radio" id="momo" name="phuong_thuc" value="momo" class="form-radio h-4 w-4 text-blue-600" {{ old('phuong_thuc', $invoice->phuong_thuc) == 'momo' ? 'checked' : '' }}>
                                            <div class="ml-3 flex items-center">
                                                <div class="w-8 h-8 bg-pink-100 rounded flex items-center justify-center mr-3">
                                                    <!-- MoMo Logo -->
                                                    <div class="w-6 h-6 bg-gradient-to-r from-pink-500 to-pink-600 rounded flex items-center justify-center">
                                                        <span class="text-white font-bold text-xs">M</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900">MoMo</p>
                                                    <p class="text-sm text-gray-500">Ví điện tử</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                <!-- VNPay -->
                                <label for="vnpay" class="block">
                                    <div class="payment-option {{ old('phuong_thuc', $invoice->phuong_thuc) == 'vnpay' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }} border-2 rounded-lg p-4 hover:border-gray-300 transition-colors cursor-pointer">
                                        <div class="flex items-center">
                                            <input type="radio" id="vnpay" name="phuong_thuc" value="vnpay" class="form-radio h-4 w-4 text-blue-600" {{ old('phuong_thuc', $invoice->phuong_thuc) == 'vnpay' ? 'checked' : '' }}>
                                            <div class="ml-3 flex items-center">
                                                <div class="w-8 h-8 bg-red-100 rounded flex items-center justify-center mr-3">
                                                    <!-- VNPay Logo -->
                                                    <div class="w-6 h-6 bg-gradient-to-r from-red-500 to-red-600 rounded flex items-center justify-center">
                                                        <span class="text-white font-bold text-xs">V</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900">VNPay</p>
                                                    <p class="text-sm text-gray-500">Cổng thanh toán</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                @error('phuong_thuc')
                                    <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded text-sm">
                                        {{ $message }}
                                    </div>
                                @enderror

                                <!-- Bank Transfer Info -->
                                <div id="bank_info" class="hidden mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <h3 class="font-medium text-blue-900 mb-3">Thông tin chuyển khoản</h3>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-blue-700">Ngân hàng:</span>
                                            <span class="font-medium text-blue-900">Vietcombank</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-blue-700">Chủ tài khoản:</span>
                                            <span class="font-medium text-blue-900">CONG TY TNHH KHACH SAN SONA</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-blue-700">Số tài khoản:</span>
                                            <span class="font-medium text-blue-900 font-mono">1234567890</span>
                                        </div>
                                        <div class="mt-3 p-2 bg-blue-100 rounded">
                                            <p class="text-blue-800 font-medium text-xs">Nội dung chuyển khoản:</p>
                                            <p class="text-blue-900 font-mono text-xs">Thanh toan cho ma dat phong #{{ $datPhong->id }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="mt-6">
                                <button type="submit" class="w-full bg-blue-600 text-white font-medium py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                                    Xác nhận phương thức thanh toán
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bankTransferRadio = document.getElementById('chuyen_khoan');
            const bankInfo = document.getElementById('bank_info');
            const paymentOptions = document.querySelectorAll('.payment-option');

            function toggleBankInfo() {
                if (bankTransferRadio.checked) {
                    bankInfo.classList.remove('hidden');
                } else {
                    bankInfo.classList.add('hidden');
                }
            }

            function updatePaymentOptionStyles() {
                paymentOptions.forEach(option => {
                    const radio = option.querySelector('input[type="radio"]');
                    if (radio.checked) {
                        option.classList.add('border-blue-500', 'bg-blue-50');
                        option.classList.remove('border-gray-200');
                    } else {
                        option.classList.remove('border-blue-500', 'bg-blue-50');
                        option.classList.add('border-gray-200');
                    }
                });
            }

            // Add click handlers to payment options
            paymentOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const radio = this.querySelector('input[type="radio"]');
                    radio.checked = true;
                    updatePaymentOptionStyles();
                    toggleBankInfo();
                });
            });

            // Add change handlers to radio buttons
            document.querySelectorAll('input[name="phuong_thuc"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    updatePaymentOptionStyles();
                    toggleBankInfo();
                });
            });

            // Initialize on page load
            toggleBankInfo();
            updatePaymentOptionStyles();

            // Show success modal on booking success
            @if(session('booking_success'))
                // Auto-scroll to success message
                setTimeout(() => {
                    const successAlert = document.querySelector('.bg-gradient-to-r.from-green-50');
                    if (successAlert) {
                        successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        
                        // Add attention animation
                        successAlert.classList.add('animate-bounce-once');
                        setTimeout(() => {
                            successAlert.classList.remove('animate-bounce-once');
                        }, 1000);
                    }
                }, 300);
            @endif
        });
    </script>

    <style>
        @keyframes bounce-once {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }
        
        .animate-bounce-once {
            animation: bounce-once 0.5s ease-in-out 2;
        }
    </style>
@endsection
