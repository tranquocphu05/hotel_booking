@extends('layouts.admin')

@section('title', 'Quản lý loại phòng')

@section('admin_content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-800">Danh sách đặt phòng của bạn</h2>
                {{-- Nút để tạo đặt phòng mới --}}
                <a href="{{ route('admin.dat_phong.create') }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Đặt phòng mới
                </a>
            </div>
            <span class="bg-yellow-500 text-white px-2 py-1 rounded">Thống kê hôm nay</span>

            {{-- Thống kê số phòng chưa xác nhận, đã xác nhận, đã hủy, đã trả phòng trong hôm nay --}}
            <div class="flex space-x-4">
                <div class="bg-gray-100 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-600">Chờ xác nhận</h3>
                    <p class="text-xl font-semibold text-gray-800">
                        {{ $bookingCounts['cho_xac_nhan'] }}</p>
                </div>
                <div class="bg-gray-100 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-600">Đã xác nhận</h3>
                    <p class="text-xl font-semibold text-gray-800">
                        {{ $bookingCounts['da_xac_nhan'] }}
                    </p>
                </div>
                <div class="bg-gray-100 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-600">Đã hủy</h3>
                    <p class="text-xl font-semibold text-gray-800">
                        {{ $bookingCounts['da_huy'] }}</p>
                </div>
                <div class="bg-gray-100 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-600">Đã trả phòng</h3>
                    <p class="text-xl font-semibold text-gray-800">
                        {{ $bookingCounts['da_tra'] }}</p>
                </div>
            </div>

            {{-- Bộ lọc cho danh sách đặt phòng --}}
            <div class="mb-6">
                <div class="flex space-x-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">Tìm theo tên phòng</label>
                        <input type="text" id="search" 
                            class="filter-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="Nhập tên phòng..."
                            value="{{ request('search') }}">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Trạng thái</label>
                        <select id="status"
                            class="filter-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Tất cả</option>
                            <option value="cho_xac_nhan" {{ request('status') == 'cho_xac_nhan' ? 'selected' : '' }}>Chờ xác
                                nhận</option>
                            <option value="da_xac_nhan" {{ request('status') == 'da_xac_nhan' ? 'selected' : '' }}>Đã xác
                                nhận</option>
                            <option value="da_huy" {{ request('status') == 'da_huy' ? 'selected' : '' }}>Đã hủy</option>
                            <option value="da_tra" {{ request('status') == 'da_tra' ? 'selected' : '' }}>Đã trả</option>
                        </select>
                    </div>
                    <div>
                        <label for="from_date" class="block text-sm font-medium text-gray-700">Từ ngày</label>
                        <input type="date" id="from_date"
                            class="filter-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            value="{{ request('from_date') }}">
                    </div>
                    <div>
                        <label for="to_date" class="block text-sm font-medium text-gray-700">Đến ngày</label>
                        <input type="date" id="to_date"
                            class="filter-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            value="{{ request('to_date') }}">
                    </div>
                </div>
            </div>

            <div data-content-container>
                @if ($bookings->isEmpty())
                    <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                        <div class="mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Chưa có đặt phòng nào</h3>
                        <p class="text-gray-500 mb-4">Hãy đặt phòng ngay để trải nghiệm dịch vụ của chúng tôi</p>
                        <a href="{{ route('client.dashboard') }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Đặt phòng ngay
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($bookings as $booking)
                            <div
                                class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-300">
                                <div class="p-4 border-b">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                {{ $booking->phong->ten_phong }}</h3>
                                            <p class="text-sm text-gray-600">{{ $booking->phong->loaiPhong->ten_loai }}</p>
                                        </div>
                                        <span
                                            class="px-3 py-1 rounded-full text-sm font-medium
                                        @if ($booking->trang_thai === 'da_xac_nhan') bg-green-100 text-green-800
                                        @elseif($booking->trang_thai === 'cho_xac_nhan') bg-yellow-100 text-yellow-800
                                        @elseif($booking->trang_thai === 'da_huy') bg-red-100 text-red-800
                                        @elseif($booking->trang_thai === 'da_chong') bg-orange-100 text-orange-800
                                        @else bg-blue-100 text-blue-800 @endif">
                                            @php
                                                $statuses = [
                                                    'cho_xac_nhan' => 'Chờ xác nhận',
                                                    'da_xac_nhan' => 'Đã xác nhận',
                                                    'da_huy' => 'Đã hủy',
                                                    'da_tra' => 'Đã trả phòng',
                                                    'da_chong' => 'Đã chống',
                                                ];
                                            @endphp
                                            {{ $statuses[$booking->trang_thai] ?? $booking->trang_thai }}
                                        </span>
                                    </div>
                                </div>

                                <div class="p-4 space-y-3">
                                    <div class="flex items-center text-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-2"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <div>
                                            <p class="text-gray-600">Nhận phòng: <span
                                                    class="font-medium">{{ date('d/m/Y', strtotime($booking->ngay_nhan)) }}</span>
                                            </p>
                                            <p class="text-gray-600">Trả phòng: <span
                                                    class="font-medium">{{ date('d/m/Y', strtotime($booking->ngay_tra)) }}</span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center text-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-2"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <span class="text-gray-600">Số người: <span
                                                class="font-medium">{{ $booking->so_nguoi }}</span></span>
                                    </div>

                                    <div class="flex items-center text-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-2"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-gray-600">Tổng tiền: <span
                                                class="font-medium">{{ number_format($booking->tong_tien, 0, ',', '.') }}
                                                VNĐ</span></span>
                                    </div>

                                    @if ($booking->voucher_id)
                                        <div class="flex items-center text-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-2"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                            </svg>
                                            <span class="text-gray-600">Mã voucher: <span
                                                    class="font-medium text-indigo-600">{{ $booking->voucher ? $booking->voucher->ma_voucher : 'Không có' }}</span></span>
                                        </div>
                                    @endif
                                    
                                    {{-- Ngày đặt --}}
                                    <div class="flex items-center text-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-2"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-gray-600">Ngày đặt: <span
                                                class="font-medium">{{ date('d/m/Y H:i', strtotime($booking->ngay_dat)) }}</span></span>
                                    </div>

                                    {{-- Sửa thông tin đặt phòng --}}
                                    <div class="flex items-center text-sm">
                                        {{-- Chỉ hiện btn khi là trạng thái chờ xác nhận --}}
                                        @if ($booking->trang_thai === 'cho_xac_nhan')
                                            <a href="{{ route('admin.dat_phong.edit', $booking->id) }}"
                                                class="text-blue-600 hover:text-blue-800 font-medium">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Sửa thông tin đặt phòng
                                        </a>
                                        @endif
                                        <div class="ml-4">
                                            <a href="{{ route('admin.dat_phong.show', $booking->id) }}"
                                                class="text-gray-600 hover:text-gray-800 font-medium">
                                                Xem chi tiết
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                @if ($booking->trang_thai === 'cho_xac_nhan')
                                    <div class="px-4 py-3 bg-gray-50 flex items-center justify-between">
                                        <form action="{{ route('admin.dat_phong.confirm', $booking->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-3 py-2 rounded">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Xác nhận đặt phòng
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.dat_phong.cancel', $booking->id) }}" class="text-red-600 hover:text-red-800 font-medium text-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Hủy đặt phòng
                                        </a>
                                    </div>
                                @elseif ($booking->trang_thai === 'da_xac_nhan')
                                    <div class="px-4 py-3 bg-green-50 flex items-center justify-between">
                                        <div class="text-green-600 font-medium text-sm">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            Phòng đã được đặt và xác nhận
                                        </div>
                                        <form action="{{ route('admin.dat_phong.mark_paid', $booking->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-2 rounded">
                                                <i class="fas fa-money-bill mr-2"></i>
                                                Đánh dấu đã thanh toán
                                            </button>
                                        </form>
                                    </div>
                                @elseif ($booking->trang_thai === 'da_chong')
                                    <div class="px-4 py-3 bg-orange-50 text-right">
                                        <div class="flex items-center justify-center text-orange-600 font-medium text-sm">
                                            <i class="fas fa-ban mr-2"></i>
                                            Phòng đã bị chống
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-6">
                        {{ $bookings->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let filterInputs = document.querySelectorAll('.filter-input');
            let filterTimeout;
            let contentContainer = document.querySelector('[data-content-container]');

            filterInputs.forEach(input => {
                input.addEventListener('change', function() {
                    clearTimeout(filterTimeout);
                    filterTimeout = setTimeout(applyFilters, 300);
                });
            });

            function applyFilters() {
                let search = document.getElementById('search').value;
                let status = document.getElementById('status').value;
                let fromDate = document.getElementById('from_date').value;
                let toDate = document.getElementById('to_date').value;

                let url = new URL(window.location.href);
                url.searchParams.set('search', search);
                url.searchParams.set('status', status);
                url.searchParams.set('from_date', fromDate);
                url.searchParams.set('to_date', toDate);
                url.searchParams.set('page', '1'); // Reset to first page when filtering

                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        // Update both the grid and pagination
                        const newContent = doc.querySelector('[data-content-container]');
                        if (newContent && contentContainer) {
                            contentContainer.innerHTML = newContent.innerHTML;
                        }

                        // Update URL without refreshing the page
                        window.history.pushState({}, '', url);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi lọc dữ liệu. Vui lòng thử lại.');
                    });
            }
        });

        // Function to block room
        function blockRoom(bookingId) {
            if (confirm('Bạn có chắc chắn muốn chống phòng này? Phòng sẽ không thể đặt được cho đến khi bạn hủy chống.')) {
                // Create form to submit block request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/dat_phong/${bookingId}/block`;
                
                // Add CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);
                
                // Add method override for PUT
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                form.appendChild(methodInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    @endpush
