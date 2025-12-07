@extends('layouts.client')

@section('title', 'Thanh toán SePay - QR Code')

@section('client_content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 py-8">
    <div class="container mx-auto px-4">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Thanh toán qua SePay</h1>
            <p class="text-gray-600">Quét mã QR để thanh toán đặt phòng</p>
        </div>

        {{-- Alert Messages --}}
        @if(session('error'))
            <div class="max-w-2xl mx-auto mb-6">
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        {{ session('error') }}
                    </div>
                </div>
            </div>
        @endif

        <div class="max-w-4xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- QR Code Section --}}
                <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
                    <div class="text-center">
                        {{-- SePay Logo --}}
                        <div class="mb-4 flex items-center justify-center">
                            <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg">
                                <span class="text-white font-bold text-2xl">SE</span>
                            </div>
                        </div>

                        <h2 class="text-xl font-semibold text-gray-900 mb-2">Quét mã QR để thanh toán</h2>
                        <p class="text-sm text-gray-500 mb-6">Sử dụng ứng dụng ngân hàng hoặc ví điện tử</p>

                        {{-- QR Code Image --}}
                        <div class="bg-gray-50 rounded-xl p-4 inline-block mb-6" id="qr-container">
                            <img 
                                src="{{ $qrData['qr_url'] }}" 
                                alt="QR Code thanh toán" 
                                class="w-64 h-64 mx-auto"
                                id="qr-image"
                                onerror="this.src='{{ $qrData['sepay_qr_url'] }}'"
                            >
                        </div>

                        {{-- Payment Status Indicator --}}
                        <div id="payment-status" class="mb-6">
                            <div class="inline-flex items-center px-4 py-2 bg-yellow-50 border border-yellow-200 rounded-full">
                                <div class="w-3 h-3 bg-yellow-400 rounded-full animate-pulse mr-2"></div>
                                <span class="text-yellow-700 font-medium text-sm">Đang chờ thanh toán...</span>
                            </div>
                        </div>

                        {{-- Payment Success (Hidden by default) --}}
                        <div id="payment-success" class="hidden mb-6">
                            <div class="inline-flex items-center px-6 py-3 bg-green-50 border-2 border-green-300 rounded-full shadow-lg">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-check text-white"></i>
                                </div>
                                <span class="text-green-700 font-bold text-lg">Thanh toán thành công!</span>
                            </div>
                        </div>

                        {{-- Bank Info --}}
                        <div class="bg-blue-50 rounded-xl p-4 text-left">
                            <h3 class="font-semibold text-blue-900 mb-3 flex items-center">
                                <i class="fas fa-university text-blue-600 mr-2"></i>
                                Thông tin chuyển khoản
                            </h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Ngân hàng:</span>
                                    <span class="font-semibold text-gray-900" id="bank-name">{{ $qrData['bank_name'] }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Số tài khoản:</span>
                                    <div class="flex items-center">
                                        <span class="font-semibold text-gray-900 mr-2" id="bank-account">{{ $qrData['bank_account'] }}</span>
                                        <button onclick="copyToClipboard('{{ $qrData['bank_account'] }}')" class="text-blue-600 hover:text-blue-800 text-xs">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Chủ tài khoản:</span>
                                    <span class="font-semibold text-gray-900">{{ $qrData['account_name'] }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Số tiền:</span>
                                    <span class="font-bold text-green-600 text-lg">{{ number_format($qrData['amount'], 0, ',', '.') }} VNĐ</span>
                                </div>
                                <div class="flex justify-between items-center bg-yellow-50 rounded-lg px-3 py-2 mt-2 border border-yellow-200">
                                    <span class="text-gray-600">Nội dung CK:</span>
                                    <div class="flex items-center">
                                        <span class="font-bold text-red-600 mr-2" id="payment-code">{{ $qrData['payment_code'] }}</span>
                                        <button onclick="copyToClipboard('{{ $qrData['payment_code'] }}')" class="text-blue-600 hover:text-blue-800 text-xs">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Important Notice --}}
                        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-left">
                            <p class="text-red-700 text-xs">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <strong>Quan trọng:</strong> Vui lòng nhập chính xác nội dung chuyển khoản <strong>{{ $qrData['payment_code'] }}</strong> để hệ thống tự động xác nhận thanh toán.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Booking Summary --}}
                <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 h-fit">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6 border-b pb-3 flex items-center">
                        <i class="fas fa-receipt text-blue-600 mr-2"></i>
                        Thông tin đặt phòng
                    </h2>

                    {{-- Booking Details --}}
                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Mã đặt phòng:</span>
                            <span class="font-bold text-blue-600 text-lg">#{{ $datPhong->id }}</span>
                        </div>
                        
                        @php
                            $roomTypes = $datPhong->getRoomTypes();
                        @endphp
                        
                        @if(count($roomTypes) > 0)
                            @foreach($roomTypes as $roomType)
                                @php
                                    $loaiPhong = \App\Models\LoaiPhong::find($roomType['loai_phong_id']);
                                @endphp
                                @if($loaiPhong)
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="text-gray-600">{{ $loaiPhong->ten_loai }}:</span>
                                        <span class="font-medium text-gray-900">{{ $roomType['so_luong'] }} phòng</span>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-600">Loại phòng:</span>
                                <span class="font-medium text-gray-900">{{ $datPhong->loaiPhong->ten_loai ?? 'N/A' }}</span>
                            </div>
                        @endif
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Ngày nhận phòng:</span>
                            <span class="font-medium text-gray-900">{{ $datPhong->ngay_nhan->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Ngày trả phòng:</span>
                            <span class="font-medium text-gray-900">{{ $datPhong->ngay_tra->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Số đêm:</span>
                            <span class="font-medium text-gray-900">{{ $datPhong->ngay_nhan->diffInDays($datPhong->ngay_tra) }} đêm</span>
                        </div>
                    </div>

                    {{-- Total Amount --}}
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 border-2 border-blue-200">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-gray-900">Tổng thanh toán:</span>
                            <span class="text-2xl font-bold text-blue-600">{{ number_format($datPhong->tong_tien, 0, ',', '.') }} VNĐ</span>
                        </div>
                    </div>


                    {{-- Actions --}}
                    <div class="mt-6 space-y-3">
                        <a href="{{ route('client.thanh-toan.show', $datPhong) }}" class="block w-full text-center py-3 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Quay lại chọn phương thức khác
                        </a>
                        
                        <button onclick="refreshQR()" class="block w-full text-center py-3 px-4 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors font-medium">
                            <i class="fas fa-sync-alt mr-2"></i>
                            Làm mới mã QR
                        </button>
                    </div>

                    {{-- Support Info --}}
                    <div class="mt-6 p-4 bg-gray-50 rounded-lg text-center">
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-headset text-gray-400 mr-1"></i>
                            Cần hỗ trợ? Liên hệ hotline: <strong>1900 xxxx</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Toast Notification --}}
<div id="toast" class="fixed bottom-4 right-4 transform translate-y-full opacity-0 transition-all duration-300 z-50">
    <div class="bg-gray-800 text-white px-4 py-3 rounded-lg shadow-lg flex items-center">
        <i class="fas fa-check-circle text-green-400 mr-2"></i>
        <span id="toast-message">Đã sao chép!</span>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentStatusEl = document.getElementById('payment-status');
    const paymentSuccessEl = document.getElementById('payment-success');
    let isPaid = false;

    // Poll for payment status every 5 seconds
    const checkPayment = setInterval(() => {
        if (isPaid) {
            clearInterval(checkPayment);
            return;
        }

        fetch("{{ route('client.sepay.status', $datPhong) }}")
            .then(response => response.json())
            .then(data => {
                if (data.is_paid) {
                    isPaid = true;
                    clearInterval(timer);
                    clearInterval(checkPayment);
                    
                    // Show success state
                    paymentStatusEl.classList.add('hidden');
                    paymentSuccessEl.classList.remove('hidden');
                    
                    // Add success animation
                    document.getElementById('qr-container').classList.add('ring-4', 'ring-green-400', 'ring-opacity-50');
                    
                    // Redirect after 3 seconds
                    setTimeout(() => {
                        window.location.href = "{{ route('client.dashboard') }}?payment=success&booking={{ $datPhong->id }}";
                    }, 3000);
                }
            })
            .catch(error => {
                console.error('Error checking payment status:', error);
            });
    }, 5000);
});

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Đã sao chép: ' + text);
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showToast('Đã sao chép: ' + text);
    });
}

function showToast(message) {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toast-message');
    toastMessage.textContent = message;
    
    toast.classList.remove('translate-y-full', 'opacity-0');
    
    setTimeout(() => {
        toast.classList.add('translate-y-full', 'opacity-0');
    }, 2000);
}

function refreshQR() {
    window.location.reload();
}
</script>
@endpush
@endsection
