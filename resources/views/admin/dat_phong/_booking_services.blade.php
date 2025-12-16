{{-- BOOKING SERVICES SECTION --}}
<div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-pink-50">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center justify-between">
            <span class="flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Dịch Vụ Phát Sinh
            </span>
            @if($booking->canRequestService())
                <button onclick="toggleAddServiceForm()" 
                    class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Thêm Dịch Vụ
                </button>
            @endif
        </h2>
    </div>

    <div class="p-6">
        @if($booking->canRequestService())
            {{-- ADD SERVICE FORM (Hidden by default) --}}
            <div id="addServiceForm" class="hidden bg-purple-50 border border-purple-200 rounded-lg p-4 mb-4">
                <h3 class="font-medium text-gray-900 mb-3">Thêm dịch vụ mới</h3>
                <form id="serviceForm" class="space-y-3">
                    @csrf
                    <input type="hidden" name="dat_phong_id" value="{{ $booking->id }}">
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dịch vụ *</label>
                            <select name="service_id" id="serviceSelect" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">-- Chọn dịch vụ --</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" data-price="{{ $service->price }}" data-unit="{{ $service->unit }}">
                                        {{ $service->name }} - {{ number_format($service->price) }}đ/{{ $service->unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Số lượng *</label>
                            <input type="number" name="quantity" id="quantityInput" min="1" value="1" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Đơn giá *</label>
                            <input type="number" name="unit_price" id="unitPriceInput" step="0.01" min="0" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <input type="text" name="ghi_chu" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Ví dụ: Phòng 101, giao lúc 14:00...">
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2">
                        <button type="submit" 
                            class="flex-1 sm:flex-none inline-flex items-center justify-center px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Thêm dịch vụ
                        </button>
                        <button type="button" onclick="toggleAddServiceForm()" 
                            class="sm:flex-none inline-flex items-center justify-center px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition">
                            Hủy
                        </button>
                    </div>
                </form>
            </div>
        @elseif(!$booking->thoi_gian_checkin)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-yellow-800">
                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Chỉ có thể thêm dịch vụ sau khi khách check-in
                </p>
            </div>
        @endif

        {{-- SERVICES LIST --}}
        <div id="servicesList">
            @php
                // Eager-load phong relation to display room number
                $bookingServices = $booking->services()->with('phong')->get();
            @endphp
            @if($bookingServices->count() > 0)
                <div class="space-y-3">
                    @foreach($bookingServices as $bookingService)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200 hover:border-purple-300 transition">
                            <div class="flex-1">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-900">{{ $bookingService->service->name }}</h4>
                                        <p class="text-sm text-gray-600 mt-1">
                                            <span class="inline-block mr-3">Phòng: <span class="font-medium">{{ $bookingService->phong ? ($bookingService->phong->so_phong ?? $bookingService->phong->id) : ($bookingService->phong_id ?? '-') }}</span></span>
                                            Số lượng: <span class="font-medium">{{ $bookingService->quantity }}</span> {{ $bookingService->service->unit }}
                                            × {{ number_format($bookingService->unit_price) }}đ
                                            = <span class="font-semibold text-purple-600">{{ number_format($bookingService->quantity * $bookingService->unit_price) }}đ</span>
                                        </p>
                                        @if($bookingService->used_at)
                                            <p class="text-xs text-gray-500 mt-1">
                                                <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ $bookingService->used_at->format('d/m/Y H:i') }}
                                            </p>
                                        @endif
                                        @if($bookingService->ghi_chu)
                                            <p class="text-xs text-gray-600 mt-1">
                                                <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                                </svg>
                                                {{ $bookingService->ghi_chu }}
                                            </p>
                                        @endif
                                    </div>
                                    @if($booking->canRequestService())
                                        <button onclick="deleteService({{ $bookingService->id }})" 
                                            class="ml-4 text-red-600 hover:text-red-800 transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Tổng tiền dịch vụ:</span>
                        <span class="text-lg font-bold text-purple-600">
                            {{ number_format($booking->services->sum(function($s) { return $s->quantity * $s->unit_price; })) }}đ
                        </span>
                    </div>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-gray-500">Chưa có dịch vụ nào</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function toggleAddServiceForm() {
    const form = document.getElementById('addServiceForm');
    form.classList.toggle('hidden');
    if (!form.classList.contains('hidden')) {
        document.getElementById('serviceSelect').focus();
    }
}

// Auto-fill unit price when service is selected
document.getElementById('serviceSelect')?.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const price = selectedOption.getAttribute('data-price');
    if (price) {
        document.getElementById('unitPriceInput').value = price;
    }
});

// Handle form submission
document.getElementById('serviceForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch('{{ route("admin.booking_services.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            // Success - reload page to show new service
            window.location.reload();
        } else {
            // Error
            alert(result.message || 'Có lỗi xảy ra khi thêm dịch vụ');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Có lỗi xảy ra. Vui lòng thử lại.');
    }
});

async function deleteService(id) {
    if (!confirm('Bạn có chắc muốn xóa dịch vụ này?')) {
        return;
    }
    
    try {
        const response = await fetch(`{{ url('admin/booking-services') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (response.ok) {
            // Success - reload page
            window.location.reload();
        } else {
            alert(result.message || 'Có lỗi xảy ra khi xóa dịch vụ');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Có lỗi xảy ra. Vui lòng thử lại.');
    }
}
</script>
