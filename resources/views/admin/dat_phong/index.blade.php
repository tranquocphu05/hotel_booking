@extends('layouts.admin')

@section('title', 'Quản lý loại phòng')

@section('admin_content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Danh sách đặt phòng</h1>
                    <p class="mt-1 text-sm text-gray-600">Quản lý tất cả các đặt phòng của bạn</p>
                </div>
                {{-- Tạo đặt phòng: Tất cả đều có quyền --}}
                <a href="{{ route('admin.dat_phong.create') }}"
                    class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Đặt phòng mới
                </a>
            </div>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Chờ xác nhận</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $bookingCounts['cho_xac_nhan'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Đã xác nhận</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $bookingCounts['da_xac_nhan'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Đã hủy</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $bookingCounts['da_huy'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Đã trả phòng</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $bookingCounts['da_tra'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="mb-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Bộ lọc</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Tìm theo tên/mã</label>
                        <input type="text" id="search" 
                            class="filter-input w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                            placeholder="Nhập tên phòng, mã đặt..."
                            value="{{ request('search') }}">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Trạng thái</label>
                        <select id="status"
                            class="filter-input w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                            <option value="">Tất cả trạng thái</option>
                            <option value="cho_xac_nhan" {{ request('status') == 'cho_xac_nhan' ? 'selected' : '' }}>Chờ xác nhận</option>
                            <option value="da_xac_nhan" {{ request('status') == 'da_xac_nhan' ? 'selected' : '' }}>Đã xác nhận</option>
                            <option value="da_huy" {{ request('status') == 'da_huy' ? 'selected' : '' }}>Đã hủy</option>
                            <option value="da_tra" {{ request('status') == 'da_tra' ? 'selected' : '' }}>Đã trả phòng</option>
                            <option value="da_chong" {{ request('status') == 'da_chong' ? 'selected' : '' }}>Đã chống</option>
                        </select>
                    </div>
                    <div>
                        <label for="from_date" class="block text-sm font-medium text-gray-700 mb-2">Từ ngày nhận</label>
                        <input type="date" id="from_date"
                            class="filter-input w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                            value="{{ request('from_date') }}">
                    </div>
                    <div>
                        <label for="to_date" class="block text-sm font-medium text-gray-700 mb-2">Đến ngày trả</label>
                        <input type="date" id="to_date"
                            class="filter-input w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                            value="{{ request('to_date') }}">
                    </div>
                </div>
            </div>

            <div data-content-container class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                @if ($bookings->isEmpty() && !request()->hasAny(['search', 'status', 'from_date', 'to_date']))
                    <div class="p-12 text-center">
                        <div class="mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-16 w-16 text-gray-300" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Chưa có đặt phòng nào</h3>
                        <p class="text-gray-600 mb-6">Hãy tạo một đặt phòng mới để bắt đầu quản lý</p>
                        {{-- Tạo đặt phòng: Tất cả đều có quyền --}}
                        <a href="{{ route('admin.dat_phong.create') }}"
                            class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Đặt phòng mới
                        </a>
                    </div>
                @elseif ($bookings->isEmpty() && request()->hasAny(['search', 'status', 'from_date', 'to_date']))
                    <div class="p-12 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <p class="text-gray-600 font-medium">Không tìm thấy đặt phòng nào phù hợp với điều kiện lọc</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-sm">Mã Đặt</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-sm">Loại Phòng & SL</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700 text-sm">Check-in</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700 text-sm">Check-out</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700 text-sm">Số người</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-sm">Tổng tiền</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700 text-sm">Ngày đặt</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700 text-sm">Trạng thái</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700 text-sm">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($bookings as $booking)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-3 text-left whitespace-nowrap">
                                            <span class="font-semibold text-blue-600">#{{ $booking->id }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-left whitespace-nowrap">
                                            @php
                                                $roomTypes = $booking->getRoomTypes();
                                            @endphp
                                            @if(count($roomTypes) > 0)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($roomTypes as $roomType)
                                                        @php
                                                            $loaiPhong = \App\Models\LoaiPhong::find($roomType['loai_phong_id']);
                                                        @endphp
                                                        @if($loaiPhong)
                                                            <span class="inline-block text-xs font-medium text-gray-900 bg-gray-100 px-2 py-1 rounded">
                                                                {{ substr($loaiPhong->ten_loai, 0, 10) }} ({{ $roomType['so_luong'] }}P)
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-500">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-700 whitespace-nowrap text-sm">
                                            {{ date('d/m/Y', strtotime($booking->ngay_nhan)) }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-700 whitespace-nowrap text-sm">
                                            {{ date('d/m/Y', strtotime($booking->ngay_tra)) }}
                                        </td>
                                        {{-- <td class="px-4 py-3 text-center text-gray-700 whitespace-nowrap">{{ $booking->so_nguoi }}</td> --}}
                                        <td class="px-4 py-3 text-center text-gray-700 whitespace-nowrap text-sm">
                                            <div class="font-medium">{{ $booking->so_nguoi ?? 0 }} NL</div>
                                            @if(($booking->so_tre_em ?? 0) > 0)
                                            <div class="text-xs text-green-600">{{ $booking->so_tre_em }} TE</div>
                                            @endif
                                            @if(($booking->so_em_be ?? 0) > 0)
                                            <div class="text-xs text-pink-600">{{ $booking->so_em_be }} EB</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap">
                                            <div class="font-semibold text-blue-700 text-sm">
                                                {{ number_format($booking->tong_tien, 0, ',', '.') }} VNĐ
                                            </div>

                                            @if ($booking->voucher_id)
                                                <div class="text-xs text-green-600 font-medium">
                                                    {{ $booking->voucher ? $booking->voucher->ma_voucher : 'Voucher' }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center text-sm text-gray-600 whitespace-nowrap">
                                            {{ date('d/m/Y', strtotime($booking->ngay_dat)) }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
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
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold whitespace-nowrap {{ $statusInfo['class'] }}">
                                                {{ $statusInfo['label'] }}
                                            </span>
                                            @if($booking->invoice && $booking->invoice->trang_thai === 'da_thanh_toan')
                                                <div class="text-xs text-green-700 font-medium">✓ Đã TT</div>
                                            @endif

                                            @php
                                                $hasCancelRequest = $booking->ghi_chu_hoan_tien && 
                                                    strpos($booking->ghi_chu_hoan_tien, 'YÊU CẦU HỦY ĐẶT PHÒNG TỪ KHÁCH HÀNG') !== false;
                                            @endphp
                                            @if(in_array($booking->trang_thai, ['cho_xac_nhan', 'da_xac_nhan']) && $hasCancelRequest)
                                                <div class="mt-1 inline-flex items-center px-2 py-1 rounded-full text-[11px] font-semibold bg-orange-100 text-orange-700">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    Có yêu cầu hủy từ khách
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-center gap-1">
                                                <a href="{{ route('admin.dat_phong.show', $booking->id) }}" title="Xem chi tiết"
                                                    class="inline-flex items-center justify-center w-7 h-7 rounded hover:bg-blue-50 text-blue-600 hover:text-blue-700 transition text-xs">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                @if ($booking->trang_thai === 'cho_xac_nhan')
                                                    {{-- Sửa: Admin và Nhân viên --}}
                                                    @if (in_array(auth()->user()->vai_tro ?? '', ['admin', 'nhan_vien']))
                                                    <a href="{{ route('admin.dat_phong.edit', $booking->id) }}" title="Sửa"
                                                        class="inline-flex items-center justify-center w-7 h-7 rounded hover:bg-amber-50 text-amber-600 hover:text-amber-700 transition text-xs">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @endif
                                                    
                                                    {{-- Xác nhận: Admin và Nhân viên (không phải Lễ tân) --}}
                                                    @if (in_array(auth()->user()->vai_tro ?? '', ['admin', 'nhan_vien']))
                                                    <form action="{{ route('admin.dat_phong.confirm', $booking->id) }}" method="POST" class="inline"
                                                        onsubmit="return confirm('Xác nhận đặt phòng #{{ $booking->id }}?')">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" title="Xác nhận"
                                                            class="inline-flex items-center justify-center w-7 h-7 rounded hover:bg-green-50 text-green-600 hover:text-green-700 transition text-xs">
                                                            <i class="fas fa-check-circle"></i>
                                                        </button>
                                                    </form>
                                                    @endif

                                                    {{-- Hủy: Chỉ Admin --}}
                                                    @hasRole('admin')
                                                    <a href="{{ route('admin.dat_phong.cancel', $booking->id) }}" title="Hủy"
                                                        class="inline-flex items-center justify-center w-7 h-7 rounded hover:bg-red-50 text-red-600 hover:text-red-700 transition text-xs"
                                                        onclick="if(!confirm('Bạn có chắc chắn muốn hủy đặt phòng #{{ $booking->id }}?')) return false;">
                                                        <i class="fas fa-times-circle"></i>
                                                    </a>
                                                    @endhasRole
                                                
                                                @elseif ($booking->trang_thai === 'da_xac_nhan' && (!$booking->invoice || $booking->invoice->trang_thai !== 'da_thanh_toan'))
                                                    <form action="{{ route('admin.dat_phong.mark_paid', $booking->id) }}" method="POST" class="inline"
                                                        onsubmit="return confirm('Đánh dấu đặt phòng #{{ $booking->id }} đã thanh toán?')">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" title="Đánh dấu đã thanh toán"
                                                            class="inline-flex items-center justify-center w-7 h-7 rounded hover:bg-blue-50 text-blue-600 hover:text-blue-700 transition text-xs">
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
                    <div class="border-t border-gray-200 px-6 py-4">
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