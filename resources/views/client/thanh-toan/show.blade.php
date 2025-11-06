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
                    <!-- Booking Details - Enhanced Card -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6 border-b pb-3 flex items-center">
                            <i class="fas fa-receipt text-blue-600 mr-2"></i>
                            Chi tiết đặt phòng
                        </h2>

                        <!-- User Info -->
                        <div class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-user text-blue-600 mr-2"></i>
                                Thông tin người đặt
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-user-circle text-gray-400 mr-2 text-sm"></i>
                                        Họ tên:
                                    </span>
                                    <span class="font-medium text-gray-900">{{ trim($datPhong->username ?? '') !== '' ? $datPhong->username : ($datPhong->user->ho_ten ?? 'Khách ẩn danh') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-envelope text-gray-400 mr-2 text-sm"></i>
                                        Email:
                                    </span>
                                    <span class="font-medium text-gray-900">{{ $datPhong->email ?? ($datPhong->user->email ?? 'Chưa cập nhật') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-phone text-gray-400 mr-2 text-sm"></i>
                                        SĐT:
                                    </span>
                                    <span class="font-medium text-gray-900">{{ $datPhong->sdt ?? ($datPhong->user->sdt ?? 'Chưa cập nhật') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-id-card text-gray-400 mr-2 text-sm"></i>
                                        CCCD:
                                    </span>
                                    <span class="font-medium text-gray-900">{{ $datPhong->cccd ?? ($datPhong->user->cccd ?? 'Chưa cập nhật') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Room Info -->
                        <div class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-bed text-blue-600 mr-2"></i>
                                Thông tin phòng
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-hashtag text-gray-400 mr-2 text-sm"></i>
                                        Mã đặt phòng:
                                    </span>
                                    <span class="font-semibold text-blue-600 text-lg">#{{ $datPhong->id }}</span>
                                </div>
                                @php
                                    $roomTypes = $datPhong->getRoomTypes();
                                @endphp

                                @if(count($roomTypes) > 1)
                                    {{-- Hiển thị nhiều loại phòng --}}
                                    <div class="flex flex-col gap-2">
                                        <span class="text-gray-600 flex items-center">
                                            <i class="fas fa-door-open text-gray-400 mr-2 text-sm"></i>
                                            Các loại phòng:
                                        </span>
                                        <div class="ml-6 space-y-2">
                                            @foreach($roomTypes as $roomType)
                                                @php
                                                    $loaiPhong = \App\Models\LoaiPhong::find($roomType['loai_phong_id']);
                                                @endphp
                                                @if($loaiPhong)
                                                    <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
                                                        <div class="flex justify-between items-center">
                                                            <span class="font-semibold text-gray-900">{{ $loaiPhong->ten_loai }}</span>
                                                            <span class="text-sm text-gray-600">{{ $roomType['so_luong'] }} phòng</span>
                                                        </div>
                                                        <div class="text-sm text-gray-600 mt-1">
                                                            Giá: {{ number_format($roomType['gia_rieng'] ?? 0, 0, ',', '.') }} VNĐ
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    {{-- Hiển thị 1 loại phòng (legacy hoặc single room) --}}
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 flex items-center">
                                            <i class="fas fa-door-open text-gray-400 mr-2 text-sm"></i>
                                            Loại phòng:
                                        </span>
                                        <span class="font-medium text-gray-900">{{ $datPhong->loaiPhong->ten_loai ?? 'N/A' }}</span>
                                    </div>
                                @endif
                                @php
                                    $assignedPhongs = $datPhong->getAssignedPhongs();
                                    $assignedCount = $assignedPhongs->count();
                                    $totalRooms = array_sum(array_column($roomTypes, 'so_luong')) ?: ($datPhong->so_luong_da_dat ?? 1);
                                @endphp

                                @if($assignedCount > 0)
                                    <div class="flex flex-col gap-2">
                                        <span class="text-gray-600 flex items-center">
                                            <i class="fas fa-key text-gray-400 mr-2 text-sm"></i>
                                            Phòng ({{ $assignedCount }}/{{ $totalRooms }}):
                                        </span>
                                        <div class="ml-6 space-y-1 flex flex-wrap gap-2">
                                            @foreach($assignedPhongs as $phong)
                                                <span class="inline-block font-semibold text-blue-600 bg-blue-50 px-3 py-1 rounded-md text-sm border border-blue-200">
                                                    {{ $phong->so_phong }}
                                                    @if($phong->tang)
                                                        <span class="text-gray-500">(Tầng {{ $phong->tang }})</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @elseif($datPhong->phong)
                                    {{-- Legacy support: Hiển thị phòng từ phong_id --}}
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 flex items-center">
                                            <i class="fas fa-key text-gray-400 mr-2 text-sm"></i>
                                            Số phòng:
                                        </span>
                                        <span class="font-semibold text-blue-600">
                                            {{ $datPhong->phong->so_phong }}
                                            @if($datPhong->phong->tang)
                                                <span class="text-gray-500 text-sm">(Tầng {{ $datPhong->phong->tang }})</span>
                                            @endif
                                        </span>
                                    </div>
                                @endif

                                @php
                                    $remainingCount = $totalRooms - $assignedCount;
                                @endphp

                                @if($remainingCount > 0 && isset($availableRooms) && $availableRooms->count() > 0 && $datPhong->trang_thai === 'cho_xac_nhan')
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-3">
                                            <p class="text-sm text-yellow-800 mb-2">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                <strong>Còn thiếu {{ $remainingCount }} phòng.</strong> Vui lòng liên hệ admin để được gán phòng cụ thể.
                                            </p>
                                        </div>
                                        <details class="text-sm">
                                            <summary class="cursor-pointer text-blue-600 hover:text-blue-800 font-medium">
                                                <i class="fas fa-eye mr-1"></i> Xem danh sách phòng trống
                                            </summary>
                                            <div class="mt-2 space-y-1 max-h-48 overflow-y-auto">
                                                @foreach($availableRooms as $room)
                                                    <div class="text-xs text-gray-600 bg-gray-50 px-2 py-1 rounded">
                                                        {{ $room->so_phong }}
                                                        @if($room->tang) (Tầng {{ $room->tang }}) @endif
                                                        - {{ \App\Models\LoaiPhong::find($room->loai_phong_id)->ten_loai ?? 'N/A' }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        </details>
                                    </div>
                                @elseif($remainingCount > 0 && $datPhong->trang_thai === 'cho_xac_nhan')
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                            <p class="text-sm text-yellow-800">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                <strong>Còn thiếu {{ $remainingCount }} phòng.</strong> Vui lòng liên hệ admin để được gán phòng cụ thể.
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                @if($datPhong->so_luong_da_dat && $datPhong->so_luong_da_dat > 1)
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 flex items-center">
                                            <i class="fas fa-bed text-gray-400 mr-2 text-sm"></i>
                                            Số lượng phòng:
                                        </span>
                                        <span class="font-medium text-gray-900">{{ $datPhong->so_luong_da_dat }} phòng</span>
                                    </div>
                                @endif
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-calendar-check text-gray-400 mr-2 text-sm"></i>
                                        Ngày nhận:
                                    </span>
                                    <span class="font-medium text-gray-900">{{ $datPhong->ngay_nhan->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-calendar-times text-gray-400 mr-2 text-sm"></i>
                                        Ngày trả:
                                    </span>
                                    <span class="font-medium text-gray-900">{{ $datPhong->ngay_tra->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-users text-gray-400 mr-2 text-sm"></i>
                                        Số người:
                                    </span>
                                    <span class="font-medium text-gray-900">{{ $datPhong->so_nguoi ?? 1 }} người</span>
                                </div>
                                @if(isset($datPhong->so_luong_da_dat) && $datPhong->so_luong_da_dat > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-layer-group text-gray-400 mr-2 text-sm"></i>
                                        Số lượng phòng:
                                    </span>
                                    <span class="font-medium text-gray-900">{{ $datPhong->so_luong_da_dat }} phòng</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Total Price -->
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl shadow-md border-2 border-blue-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-5 flex items-center">
                                <i class="fas fa-calculator text-blue-600 mr-2"></i>
                                Tóm tắt hóa đơn
                            </h3>
                            <div class="space-y-4">
                                @php
                                    $roomTypes = $datPhong->getRoomTypes();
                                @endphp

                                @if(count($roomTypes) > 1)
                                    {{-- Hiển thị chi tiết từng loại phòng --}}
                                    <div class="space-y-2">
                                        @foreach($roomTypes as $roomType)
                                            @php
                                                $loaiPhong = \App\Models\LoaiPhong::find($roomType['loai_phong_id']);
                                                $soLuong = $roomType['so_luong'] ?? 1;
                                                $giaRieng = $roomType['gia_rieng'] ?? 0;
                                            @endphp
                                            @if($loaiPhong)
                                                <div class="flex justify-between items-center bg-white/60 rounded-lg px-4 py-3">
                                                    <span class="text-gray-700 font-medium">
                                                        <i class="fas fa-bed text-blue-500 mr-2"></i>
                                                        {{ $loaiPhong->ten_loai }}
                                                        @if($soLuong > 1)
                                                            ({{ $nights }} đêm × {{ $soLuong }} phòng)
                                                        @else
                                                            ({{ $nights }} đêm)
                                                        @endif
                                                    </span>
                                                    <span class="font-semibold text-gray-900 text-base">{{ number_format($giaRieng, 0, ',', '.') }} VNĐ</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    {{-- Hiển thị 1 loại phòng (legacy) --}}
                                    @php
                                        $soLuongPhong = $datPhong->so_luong_da_dat ?? 1;
                                        $displayPrice = $originalPrice ?? 0;
                                    @endphp
                                    <div class="flex justify-between items-center bg-white/60 rounded-lg px-4 py-3">
                                        <span class="text-gray-700 font-medium">
                                            <i class="fas fa-bed text-blue-500 mr-2"></i>
                                            Giá phòng
                                            @if($soLuongPhong > 1)
                                                ({{ $nights }} đêm × {{ $soLuongPhong }} phòng)
                                            @else
                                                ({{ $nights }} đêm)
                                            @endif
                                        </span>
                                        <span class="font-semibold text-gray-900 text-base">{{ number_format($displayPrice, 0, ',', '.') }} VNĐ</span>
                                    </div>
                                @endif

                                @if ($datPhong->voucher && $discountAmount > 0)
                                    <div class="flex justify-between items-center bg-green-50 rounded-lg px-4 py-3 border border-green-200">
                                        <span class="text-gray-700 font-medium">
                                            <i class="fas fa-tag text-green-600 mr-2"></i>
                                            Voucher <span class="font-mono bg-green-100 text-green-700 px-2 py-1 rounded-md text-xs">{{ $datPhong->voucher->ma_voucher }}</span>
                                        </span>
                                        <span class="font-semibold text-green-600 text-base">-{{ number_format($discountAmount, 0, ',', '.') }} VNĐ</span>
                                    </div>
                                @endif

                                <div class="border-t-2 border-blue-300 my-4"></div>

                                <div class="flex justify-between items-center bg-white/80 rounded-lg px-4 py-4 shadow-sm">
                                    <span class="text-lg font-bold text-gray-900">Tổng cộng:</span>
                                    <span class="text-2xl font-bold text-blue-600">{{ number_format($datPhong->tong_tien, 0, ',', '.') }} VNĐ</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6 border-b pb-3">Phương thức thanh toán</h2>

                        <form action="{{ route('client.thanh-toan.store', ['datPhong' => $datPhong->id]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="phuong_thuc" value="vnpay">

                            <!-- VNPay Payment Info -->
                            <div class="border-2 border-blue-200 bg-blue-50 rounded-lg p-5 mb-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                                            <!-- VNPay Logo -->
                                            <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-red-600 rounded-lg flex items-center justify-center shadow-md">
                                                <span class="text-white font-bold text-lg">V</span>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900 text-lg">VNPay</p>
                                            <p class="text-sm text-gray-600">Cổng thanh toán trực tuyến</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-500 mb-1">Phương thức</p>
                                        <p class="text-sm font-medium text-blue-600">Đã chọn</p>
                                    </div>
                                </div>
                            </div>

                            @error('phuong_thuc')
                                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-6">
                                    <div class="flex items-center">
                                        <i class="fas fa-exclamation-circle mr-2"></i>
                                        {{ $message }}
                                    </div>
                                </div>
                            @enderror

                            <!-- Payment Notice -->
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                                <div class="flex items-start">
                                    <i class="fas fa-info-circle text-yellow-600 mr-2 mt-0.5"></i>
                                    <div class="text-sm text-yellow-800">
                                        <p class="font-medium mb-1">Lưu ý:</p>
                                        <p>Bạn sẽ được chuyển đến trang thanh toán VNPay để hoàn tất giao dịch. Vui lòng không đóng trình duyệt trong quá trình thanh toán.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="mt-6">
                                <button type="submit" class="w-full bg-gradient-to-r from-red-600 to-red-700 text-white font-semibold py-4 px-6 rounded-lg hover:from-red-700 hover:to-red-800 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center">
                                    <i class="fas fa-credit-card mr-2"></i>
                                    Thanh toán bằng VNPay
                                </button>
                                <p class="text-center text-xs text-gray-500 mt-3">
                                    <i class="fas fa-lock mr-1"></i>
                                    Giao dịch được bảo mật bởi VNPay
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

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
