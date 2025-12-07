@extends('layouts.client')

@section('title', 'SePay Test - Simulate Payment')

@section('client_content')
<div class="min-h-screen bg-gradient-to-br from-yellow-50 via-white to-orange-50 py-8">
    <div class="container mx-auto px-4">
        {{-- Warning Banner --}}
        <div class="max-w-4xl mx-auto mb-6">
            <div class="bg-yellow-100 border-2 border-yellow-400 text-yellow-800 px-6 py-4 rounded-xl">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-2xl text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold">⚠️ CHỈ DÀNH CHO TEST LOCALHOST</h3>
                        <p class="text-sm mt-1">
                            Trang này dùng để simulate thanh toán SePay khi không có webhook thực. 
                            <strong class="text-red-600">XÓA CONTROLLER NÀY TRƯỚC KHI DEPLOY LÊN PRODUCTION!</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                <i class="fas fa-vial text-green-600 mr-2"></i>
                SePay Test Panel
            </h1>
            <p class="text-gray-600">Simulate thanh toán SePay cho các đơn đặt phòng</p>
        </div>

        {{-- Alert Messages --}}
        @if(session('success'))
            <div class="max-w-4xl mx-auto mb-6">
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ session('success') }}
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="max-w-4xl mx-auto mb-6">
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        {{ session('error') }}
                    </div>
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="max-w-4xl mx-auto mb-6">
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        {{ session('warning') }}
                    </div>
                </div>
            </div>
        @endif

        {{-- Pending Bookings Table --}}
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4">
                    <h2 class="text-xl font-semibold text-white flex items-center">
                        <i class="fas fa-clock mr-2"></i>
                        Đơn đặt phòng chờ thanh toán
                    </h2>
                </div>

                @if($pendingBookings->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã ĐP</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Khách hàng</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số tiền</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nội dung CK</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($pendingBookings as $booking)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-bold text-blue-600">#{{ $booking->id }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $booking->username ?? $booking->user->ho_ten ?? 'N/A' }}</div>
                                            <div class="text-xs text-gray-500">{{ $booking->email ?? $booking->user->email ?? '' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-semibold text-green-600">{{ number_format($booking->tong_tien, 0, ',', '.') }} VNĐ</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <code class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded font-mono text-sm">SE{{ $booking->id }}</code>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($booking->invoice)
                                                @php $ui = $booking->invoice->trang_thai_ui; @endphp
                                                <span class="px-2 py-1 text-xs font-medium {{ $ui['bg'] }} {{ $ui['text'] }} rounded-full">
                                                    {{ $ui['label'] }}
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">
                                                    Chưa có hóa đơn
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <form action="{{ route('sepay.test.simulate', $booking) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white text-sm font-medium rounded-lg hover:from-green-600 hover:to-emerald-700 transition-all shadow-md hover:shadow-lg">
                                                    <i class="fas fa-check-circle mr-2"></i>
                                                    Simulate Thanh toán
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">Không có đơn nào chờ thanh toán</h3>
                        <p class="text-gray-500 text-sm">Tất cả đơn đặt phòng đã được thanh toán hoặc chưa có đơn mới.</p>
                    </div>
                @endif
            </div>

            {{-- Instructions --}}
            <div class="mt-8 bg-white rounded-2xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    Hướng dẫn test
                </h3>
                <ol class="list-decimal list-inside space-y-2 text-gray-700">
                    <li>Tạo một đơn đặt phòng mới từ trang client</li>
                    <li>Chọn phương thức thanh toán <strong>SePay</strong></li>
                    <li>Trang QR Code sẽ hiển thị và bắt đầu polling (kiểm tra mỗi 5 giây)</li>
                    <li>Mở tab mới, vào trang này <strong>/sepay/test</strong></li>
                    <li>Click <strong>"Simulate Thanh toán"</strong> cho đơn tương ứng</li>
                    <li>Quay lại tab QR Code - trang sẽ tự động chuyển sang thành công!</li>
                </ol>
            </div>

            {{-- Back Link --}}
            <div class="mt-6 text-center">
                <a href="{{ route('client.dashboard') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Quay lại Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
