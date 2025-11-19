@extends('layouts.admin')

@section('title', 'Quản lý loại phòng')

@section('admin_content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-800">Danh sách đặt phòng của bạn</h2>
                {{-- Nút để tạo đặt phòng mới --}}
                <a href="{{ route('admin.dat_phong.create') }}"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Đặt phòng mới
                </a>
            </div>
            
            {{-- Thống kê số phòng --}}
            <span class="bg-yellow-500 text-white text-sm px-2 py-1 rounded-full inline-block mb-4 font-medium">Thống kê hôm nay</span>
            <div class="flex space-x-4 mb-8">
                <div class="bg-white border border-gray-200 p-4 rounded-lg shadow-sm w-1/4">
                    <h3 class="text-sm font-medium text-gray-600">Chờ xác nhận</h3>
                    <p class="text-xl font-semibold text-gray-800">{{ $bookingCounts['cho_xac_nhan'] }}</p>
                </div>
                <div class="bg-white border border-gray-200 p-4 rounded-lg shadow-sm w-1/4">
                    <h3 class="text-sm font-medium text-gray-600">Đã xác nhận</h3>
                    <p class="text-xl font-semibold text-gray-800">{{ $bookingCounts['da_xac_nhan'] }}</p>
                </div>
                <div class="bg-white border border-gray-200 p-4 rounded-lg shadow-sm w-1/4">
                    <h3 class="text-sm font-medium text-gray-600">Đã hủy</h3>
                    <p class="text-xl font-semibold text-gray-800">{{ $bookingCounts['da_huy'] }}</p>
                </div>
                <div class="bg-white border border-gray-200 p-4 rounded-lg shadow-sm w-1/4">
                    <h3 class="text-sm font-medium text-gray-600">Đã trả phòng</h3>
                    <p class="text-xl font-semibold text-gray-800">{{ $bookingCounts['da_tra'] }}</p>
                </div>
            </div>

            {{-- Bộ lọc cho danh sách đặt phòng --}}
            <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                <h3 class="text-lg font-medium text-gray-800 mb-3">Bộ lọc</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">Tìm theo tên/mã</label>
                        <input type="text" id="search" 
                            class="filter-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="Nhập tên phòng, mã đặt..."
                            value="{{ request('search') }}">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Trạng thái</label>
                        <select id="status"
                            class="filter-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Tất cả</option>
                            <option value="cho_xac_nhan" {{ request('status') == 'cho_xac_nhan' ? 'selected' : '' }}>Chờ xác nhận</option>
                            <option value="da_xac_nhan" {{ request('status') == 'da_xac_nhan' ? 'selected' : '' }}>Đã xác nhận</option>
                            <option value="da_huy" {{ request('status') == 'da_huy' ? 'selected' : '' }}>Đã hủy</option>
                            <option value="da_tra" {{ request('status') == 'da_tra' ? 'selected' : '' }}>Đã trả phòng</option>
                            <option value="da_chong" {{ request('status') == 'da_chong' ? 'selected' : '' }}>Đã chống</option>
                        </select>
                    </div>
                    <div>
                        <label for="from_date" class="block text-sm font-medium text-gray-700">Từ ngày nhận</label>
                        <input type="date" id="from_date"
                            class="filter-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            value="{{ request('from_date') }}">
                    </div>
                    <div>
                        <label for="to_date" class="block text-sm font-medium text-gray-700">Đến ngày trả</label>
                        <input type="date" id="to_date"
                            class="filter-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            value="{{ request('to_date') }}">
                    </div>
                </div>
            </div>

            <div data-content-container class="bg-white rounded-lg shadow-md overflow-hidden">
                @if ($bookings->isEmpty() && !request()->hasAny(['search', 'status', 'from_date', 'to_date']))
                    <div class="p-6 text-center">
                        <div class="mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Chưa có đặt phòng nào</h3>
                        <p class="text-gray-500 mb-4">Hãy tạo một đặt phòng mới để bắt đầu quản lý.</p>
                        <a href="{{ route('admin.dat_phong.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Đặt phòng mới
                        </a>
                    </div>
                @elseif ($bookings->isEmpty() && request()->hasAny(['search', 'status', 'from_date', 'to_date']))
                    <div class="p-6 text-center text-gray-500">
                        Không tìm thấy đặt phòng nào phù hợp với điều kiện lọc.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-gray-700 uppercase">
                                <tr>
                                    <th class="px-6 py-3 text-center whitespace-nowrap">Mã Đặt</th>
                                    <th class="px-6 py-3 text-left whitespace-nowrap">Loại Phòng & SL</th>
                                    <th class="px-6 py-3 text-center whitespace-nowrap">Check-in</th>
                                    <th class="px-6 py-3 text-center whitespace-nowrap">Check-out</th>
                                    <th class="px-6 py-3 text-center whitespace-nowrap">Số người</th>
                                    <th class="px-6 py-3 text-center whitespace-nowrap">Tổng tiền</th>
                                    <th class="px-6 py-3 text-center whitespace-nowrap">Ngày đặt</th>
                                    <th class="px-6 py-3 text-center whitespace-nowrap">Trạng thái</th>
                                    <th class="px-6 py-3 text-center whitespace-nowrap">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($bookings as $booking)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 text-center font-semibold text-blue-600 whitespace-nowrap">#{{ $booking->id }}</td>
                                        <td class="px-6 py-4 text-left">
                                            @php
                                                $roomTypes = $booking->getRoomTypes();
                                            @endphp
                                            @if(count($roomTypes) > 0)
                                                @foreach($roomTypes as $roomType)
                                                    @php
                                                        $loaiPhong = \App\Models\LoaiPhong::find($roomType['loai_phong_id']);
                                                    @endphp
                                                    @if($loaiPhong)
                                                        <p class="text-gray-700 whitespace-nowrap">
                                                            <span class="font-medium">{{ $loaiPhong->ten_loai }}</span>
                                                            <span class="text-gray-500 text-xs">({{ $roomType['so_luong'] }} P)</span>
                                                        </p>
                                                    @endif
                                                @endforeach
                                                <p class="text-xs text-gray-500 mt-1">Tổng: {{ $booking->so_luong_da_dat ?? 1 }} phòng</p>
                                            @else
                                                 <p class="text-gray-500">-</p>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            {{ date('d/m/Y', strtotime($booking->ngay_nhan)) }}
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            {{ date('d/m/Y', strtotime($booking->ngay_tra)) }}
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">{{ $booking->so_nguoi }}</td>
                                        <td class="px-6 py-4 text-right font-medium text-blue-700 whitespace-nowrap">
                                            {{ number_format($booking->tong_tien, 0, ',', '.') }} VNĐ
                                            @if ($booking->voucher_id)
                                                <p class="text-xs text-green-500 mt-1">
                                                    ({{ $booking->voucher ? $booking->voucher->ma_voucher : 'Voucher' }})
                                                </p>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center text-gray-500 whitespace-nowrap">
                                            {{ date('d/m/Y H:i', strtotime($booking->ngay_dat)) }}
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            @php
                                                $status = $booking->trang_thai;
                                                $statusMap = [
                                                    'cho_xac_nhan' => ['label' => 'Chờ xác nhận', 'class' => 'bg-yellow-100 text-yellow-800'],
                                                    'da_xac_nhan' => ['label' => 'Đã xác nhận', 'class' => 'bg-green-100 text-green-800'],
                                                    'da_huy' => ['label' => 'Đã hủy', 'class' => 'bg-red-100 text-red-800'],
                                                    'da_tra' => ['label' => 'Đã trả phòng', 'class' => 'bg-blue-100 text-blue-800'],
                                                    'da_chong' => ['label' => 'Đã chống', 'class' => 'bg-orange-100 text-orange-800'],
                                                ];
                                                $statusInfo = $statusMap[$status] ?? ['label' => $status, 'class' => 'bg-gray-100 text-gray-800'];
                                            @endphp
                                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $statusInfo['class'] }}">
                                                {{ $statusInfo['label'] }}
                                            </span>
                                            @if($booking->invoice && $booking->invoice->trang_thai === 'da_thanh_toan')
                                                <p class="text-xs text-green-600 font-medium mt-1">Đã TT</p>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center space-x-2">
                                                <a href="{{ route('admin.dat_phong.show', $booking->id) }}" title="Xem chi tiết"
                                                    class="text-blue-600 hover:text-blue-700 transition">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                @if ($booking->trang_thai === 'cho_xac_nhan')
                                                    <a href="{{ route('admin.dat_phong.edit', $booking->id) }}" title="Sửa"
                                                        class="text-gray-500 hover:text-amber-600 transition">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    {{-- Nút Xác nhận --}}
                                                    <form action="{{ route('admin.dat_phong.confirm', $booking->id) }}" method="POST" class="inline"
                                                        onsubmit="return confirm('Xác nhận đặt phòng #{{ $booking->id }}?')">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" title="Xác nhận" class="text-green-600 hover:text-green-700 transition">
                                                            <i class="fas fa-check-circle"></i>
                                                        </button>
                                                    </form>

                                                    {{-- Nút Hủy --}}
                                                    <a href="{{ route('admin.dat_phong.cancel', $booking->id) }}" title="Hủy"
                                                        class="text-red-600 hover:text-red-700 transition"
                                                        onclick="event.preventDefault(); if(confirm('Bạn có chắc chắn muốn hủy đặt phòng #{{ $booking->id }}?')) { document.getElementById('cancel-form-{{ $booking->id }}').submit(); }">
                                                        <i class="fas fa-times-circle"></i>
                                                    </a>
                                                    <form id="cancel-form-{{ $booking->id }}" action="{{ route('admin.dat_phong.cancel', $booking->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('PUT')
                                                    </form>
                                                
                                                @elseif ($booking->trang_thai === 'da_xac_nhan' && (!$booking->invoice || $booking->invoice->trang_thai !== 'da_thanh_toan'))
                                                    {{-- Nút Đánh dấu đã thanh toán (chỉ khi đã xác nhận và chưa thanh toán) --}}
                                                    <form action="{{ route('admin.dat_phong.mark_paid', $booking->id) }}" method="POST" class="inline"
                                                        onsubmit="return confirm('Đánh dấu đặt phòng #{{ $booking->id }} đã thanh toán?')">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" title="Đánh dấu đã TT" class="text-blue-600 hover:text-blue-700 transition">
                                                            <i class="fas fa-money-bill-wave"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6 p-4">
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

            // Use 'input' for text and 'change' for select/date inputs
            filterInputs.forEach(input => {
                input.addEventListener(input.type === 'text' ? 'input' : 'change', function() {
                    clearTimeout(filterTimeout);
                    filterTimeout = setTimeout(applyFilters, 400); // 400ms debounce
                });
            });

            // Re-fetch content via AJAX (Keep existing logic for now)
            function applyFilters() {
                let search = document.getElementById('search').value;
                let status = document.getElementById('status').value;
                let fromDate = document.getElementById('from_date').value;
                let toDate = document.getElementById('to_date').value;

                let url = new URL(window.location.href);
                // Clear existing filters
                url.searchParams.delete('search');
                url.searchParams.delete('status');
                url.searchParams.delete('from_date');
                url.searchParams.delete('to_date');

                // Set new filters
                if (search) url.searchParams.set('search', search);
                if (status) url.searchParams.set('status', status);
                if (fromDate) url.searchParams.set('from_date', fromDate);
                if (toDate) url.searchParams.set('to_date', toDate);
                
                url.searchParams.set('page', '1'); // Reset to first page when filtering

                // Show loading state (optional)
                contentContainer.style.opacity = '0.5';

                fetch(url.href)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text();
                    })
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        // Find the new content container from the fetched HTML
                        const newContent = doc.querySelector('[data-content-container]');
                        
                        if (newContent) {
                            contentContainer.innerHTML = newContent.innerHTML;
                        } else {
                            // Handle case where newContent is not found (e.g., error page returned)
                            contentContainer.innerHTML = '<div class="p-6 text-center text-red-500">Lỗi: Không tải được dữ liệu mới.</div>';
                        }
                        
                        // Update URL without refreshing the page
                        window.history.pushState({}, '', url.href);
                        contentContainer.style.opacity = '1';

                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                        alert('Có lỗi xảy ra khi lọc dữ liệu. Vui lòng thử lại.');
                        contentContainer.style.opacity = '1';
                    });
            }
        });

        // Function to block room (Keep existing logic)
        function blockRoom(bookingId) {
            // ... (Your existing blockRoom logic)
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