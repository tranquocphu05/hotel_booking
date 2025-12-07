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
        </h2>
    </div>

    <div class="p-6">
        {{-- GROUP SERVICES BY SERVICE ID --}}
        @php
            $servicesGrouped = $booking->services->groupBy('service_id');
        @endphp

        @if($booking->services->count() > 0)
            <div class="space-y-6">
                @foreach($servicesGrouped as $serviceId => $serviceEntries)
                    @php
                        $service = $serviceEntries->first()->service;
                        $firstEntry = $serviceEntries->first();
                    @endphp
                    <div class="border border-blue-200 rounded-lg overflow-hidden bg-blue-50">
                        {{-- Service Header --}}
                        <div class="bg-blue-100 px-4 py-3 border-b border-blue-200">
                            <h4 class="font-semibold text-gray-900">{{ $service->name }}</h4>
                            <p class="text-xs text-gray-600 mt-1">Đơn giá: <span class="font-medium">{{ number_format($service->price) }}đ/{{ $service->unit ?? 'cái' }}</span></p>
                        </div>

                        {{-- Service Mode Info --}}
                        @php
                            $globalEntries = $serviceEntries->where('phong_id', null);
                            $specificEntries = $serviceEntries->where('phong_id', '!=', null);
                        @endphp
                        
                        <div class="px-4 py-3">
                            @if($globalEntries->count() > 0)
                                <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded">
                                    <p class="text-sm font-medium text-green-800">✓ Áp dụng tất cả phòng</p>
                                </div>
                            @endif

                            @if($specificEntries->count() > 0)
                                <div class="mb-4 p-3 bg-purple-50 border border-purple-200 rounded">
                                    <p class="text-sm font-medium text-purple-800">✓ Chọn phòng riêng</p>
                                </div>
                            @endif
                        </div>

                        {{-- Entries (Per-day) --}}
                        <div class="px-4 py-3 space-y-2">
                            @foreach($serviceEntries as $entry)
                                <div class="p-3 bg-white border border-gray-200 rounded">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900">
                                                📅 {{ \Carbon\Carbon::parse($entry->used_at)->format('d/m/Y') }}
                                                <span class="text-xs text-gray-600">- Số lượng: {{ $entry->quantity }}</span>
                                            </p>
                                            <p class="text-sm text-gray-700 mt-1">
                                                Tiền: <span class="font-semibold text-blue-600">{{ number_format($entry->quantity * $entry->unit_price) }}đ</span>
                                            </p>

                                            {{-- Show room info if specific --}}
                                            @if($entry->phong_id)
                                                <p class="text-xs text-purple-700 mt-1">
                                                    🏠 Phòng {{ $entry->phong->so_phong ?? "ID: {$entry->phong_id}" }}
                                                </p>
                                            @endif

                                            @if($entry->ghi_chu)
                                                <p class="text-xs text-gray-600 mt-1">Ghi chú: {{ $entry->ghi_chu }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Total Service Price --}}
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-semibold text-gray-900">Tổng tiền dịch vụ:</span>
                    <span class="text-2xl font-bold text-purple-600">
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
