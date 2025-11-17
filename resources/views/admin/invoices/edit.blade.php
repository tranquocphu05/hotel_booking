@extends('layouts.admin')

@section('title', 'Cập nhật Hóa đơn')

@section('admin_content')
<div class="container mx-auto px-4 sm:px-8">
    <div class="py-8">
        <!-- Header with Invoice Info -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-blue-600 to-blue-400 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-3xl font-bold">Hóa đơn #{{ $invoice->id }}</h2>
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold
                        @if($invoice->trang_thai === 'da_thanh_toan') bg-green-100 text-green-800
                        @elseif($invoice->trang_thai === 'cho_thanh_toan') bg-yellow-100 text-yellow-800
                        @elseif($invoice->trang_thai === 'hoan_tien') bg-red-100 text-red-800
                        @endif">
                        {{ $invoice->trang_thai === 'da_thanh_toan' ? '✓ Đã thanh toán' : 
                           ($invoice->trang_thai === 'cho_thanh_toan' ? '⏳ Chờ thanh toán' : '↻ Hoàn tiền') }}
                    </span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-blue-100 text-sm">Khách hàng</p>
                        <p class="text-xl font-semibold">{{ $invoice->datPhong ? ($invoice->datPhong->username ?? ($invoice->datPhong->user->ho_ten ?? 'N/A')) : 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-blue-100 text-sm">Ngày tạo</p>
                        <p class="text-xl font-semibold">{{ $invoice->ngay_tao->format('d/m/Y') }}</p>
                        <p class="text-sm text-blue-100">{{ $invoice->ngay_tao->format('H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-blue-100 text-sm">Tổng thanh toán</p>
                        <p class="text-2xl font-bold">{{ number_format($invoice->tong_tien, 0, ',', '.') }} VNĐ</p>
                    </div>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Booking Info Section -->
        <div class="mb-6 p-4 bg-blue-50 border-2 border-blue-200 rounded-lg">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Thông tin đặt phòng</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Check-in</p>
                    <p class="text-gray-900 font-medium">{{ $booking ? \Carbon\Carbon::parse($booking->ngay_nhan)->format('d/m/Y') : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Check-out</p>
                    <p class="text-gray-900 font-medium">{{ $booking ? \Carbon\Carbon::parse($booking->ngay_tra)->format('d/m/Y') : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Số người</p>
                    <p class="text-gray-900 font-medium">{{ $booking ? ($booking->so_nguoi ?? 'N/A') : 'N/A' }} người</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Số phòng cụ thể</p>
                    <p class="text-gray-900 font-medium">
                        @php
                            if($booking) {
                                $assignedPhongs = $booking->getAssignedPhongs();
                                if($assignedPhongs->count() > 0) {
                                    $phongNumbers = $assignedPhongs->pluck('so_phong')->toArray();
                                    echo implode(', ', $phongNumbers);
                                } else {
                                    echo 'N/A';
                                }
                            } else {
                                echo 'N/A';
                            }
                        @endphp
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Loại phòng & SL</p>
                    <p class="text-gray-900 font-medium">
                        @php
                            if($booking) {
                                $roomTypes = $booking->getRoomTypes();
                                if(count($roomTypes) > 0) {
                                    $typesList = [];
                                    foreach($roomTypes as $rt) {
                                        $loaiPhong = \App\Models\LoaiPhong::find($rt['loai_phong_id'] ?? null);
                                        $tenLoai = $loaiPhong ? $loaiPhong->ten_loai : 'N/A';
                                        $soLuong = $rt['so_luong'] ?? 1;
                                        $typesList[] = "{$tenLoai} ({$soLuong})";
                                    }
                                    echo implode(', ', $typesList);
                                } else {
                                    echo 'N/A';
                                }
                            } else {
                                echo 'N/A';
                            }
                        @endphp
                    </p>
                </div>
            </div>
        </div>
            <div class="inline-block min-w-full shadow rounded-lg overflow-hidden">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('admin.invoices.update', $invoice->id) }}">
                        @csrf
                        @method('PATCH')

                        <!-- Trạng thái thanh toán -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <label for="trang_thai" class="block text-gray-900 text-sm font-bold mb-3">
                                <i class="fas fa-sync-alt mr-2 text-blue-600"></i>Trạng thái thanh toán
                            </label>
                            <select name="trang_thai" id="trang_thai" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 font-medium">
                                <option value="cho_thanh_toan" {{ $invoice->trang_thai == 'cho_thanh_toan' ? 'selected' : '' }}>⏳ Chờ thanh toán</option>
                                <option value="da_thanh_toan" {{ $invoice->trang_thai == 'da_thanh_toan' ? 'selected' : '' }}>✓ Đã thanh toán</option>
                                <option value="hoan_tien" {{ $invoice->trang_thai == 'hoan_tien' ? 'selected' : '' }}>↻ Hoàn tiền</option>
                            </select>
                        </div>

                        <!-- Inline services picker (same UI as booking create/edit) -->
                        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css" rel="stylesheet">
                        <style>
                            .service-card-custom{border-radius:12px;background:linear-gradient(135deg, #f0fdfc 0%, #ccfbf1 100%);border:2px solid #99f6e4;padding:1.25rem;box-shadow:0 10px 25px rgba(16, 185, 129, 0.08);} 
                            .service-card-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1.25rem}
                            .service-card-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;padding-bottom:.5rem;border-bottom:2px solid #d1fae5}
                            .service-card-header .service-title{color:#0d9488;font-weight:700;font-size:1.1rem}
                            .service-card-header .service-price{color:#0f766e;font-weight:600;font-size:0.95rem}
                            .service-date-row{display:flex;gap:.75rem;align-items:center;margin-top:.75rem;padding:.5rem;background:#ffffff;border-radius:8px;border:1px solid #d1fae5}
                            .service-date-row input[type=date]{border:1px solid #a7f3d0;padding:.45rem .6rem;border-radius:6px;background:#f0fdfc;font-size:0.9rem;flex:1}
                            .service-date-row input[type=number]{border:1px solid #a7f3d0;padding:.45rem .6rem;border-radius:6px;background:#f0fdfc;width:80px;text-align:center}
                            .service-add-day{background:linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);color:#0d7377;padding:.5rem .75rem;border-radius:8px;border:1.5px solid #6ee7b7;cursor:pointer;font-weight:600;font-size:0.9rem}
                            .service-add-day:hover{background:linear-gradient(135deg, #a7f3d0 0%, #6ee7b7 100%);box-shadow:0 4px 12px rgba(13, 148, 136, 0.2)}
                            .service-remove-btn{background:#fecaca;color:#991b1b;padding:.4rem .6rem;border-radius:6px;border:1px solid #fca5a5;cursor:pointer;font-weight:600;font-size:0.85rem}
                            .service-remove-btn:hover{background:#f87171;box-shadow:0 4px 12px rgba(185, 28, 28, 0.15)}
                            #services_select + .ts-control{margin-top:.5rem;border-color:#99f6e4}
                            #selected_services_list .service-card-custom{transition:all .2s ease}
                            #selected_services_list .service-card-custom:hover{transform:translateY(-6px);box-shadow:0 15px 35px rgba(16, 185, 129, 0.15)}
                        </style>
                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                            <label for="services_select" class="block text-sm font-medium text-gray-700 mb-2">Chọn dịch vụ kèm theo</label>
                            <select id="services_select" placeholder="Chọn 1 hoặc nhiều dịch vụ..." multiple>
                                @foreach ($services as $service)
                                    <option value="{{ $service->id }}" data-price="{{ $service->price }}" data-unit="{{ $service->unit ?? 'cái' }}">{{ $service->name }} - {{ number_format($service->price,0,',','.') }} VNĐ</option>
                                @endforeach
                            </select>
                            <div id="selected_services_list" class="service-card-grid grid grid-cols-1 md:grid-cols-3 gap-6 mt-4"></div>
                        </div>

                        {{-- Show dynamic total so admin sees live changes --}}
                        @php
                            // Calculate room total properly from booking data
                            $nights = 0;
                            $roomTotalCalculated = 0;
                            if($booking && $booking->ngay_nhan && $booking->ngay_tra) {
                                $checkin = \Carbon\Carbon::parse($booking->ngay_nhan);
                                $checkout = \Carbon\Carbon::parse($booking->ngay_tra);
                                $nights = $checkin->diffInDays($checkout);
                                
                                // Get room types and calculate room total
                                $roomTypes = $booking->getRoomTypes();
                                foreach ($roomTypes as $rt) {
                                    // If 'gia_rieng' is stored as subtotal (common in this codebase), use it directly.
                                    if (isset($rt['gia_rieng']) && $rt['gia_rieng'] !== null) {
                                        $roomTotalCalculated += (float) $rt['gia_rieng'];
                                    } else {
                                        // Fallback: compute from LoaiPhong unit price
                                        $loaiPhong = \App\Models\LoaiPhong::find($rt['loai_phong_id'] ?? null);
                                        $unit = $loaiPhong ? ($loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban ?? 0) : 0;
                                        $roomTotalCalculated += $unit * $nights * ($rt['so_luong'] ?? 1);
                                    }
                                }
                            }
                            
                            // Get current service total from database
                            $currentServiceTotal = 0;
                            foreach ($bookingServices as $bs) {
                                $currentServiceTotal += ($bs->quantity ?? 0) * ($bs->unit_price ?? 0);
                            }
                        @endphp
                        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p><strong>Ngày nhận:</strong> {{ optional($booking)->ngay_nhan ? date('d/m/Y', strtotime($booking->ngay_nhan)) : 'N/A' }} | <strong>Ngày trả:</strong> {{ optional($booking)->ngay_tra ? date('d/m/Y', strtotime($booking->ngay_tra)) : 'N/A' }} | <strong>{{ $nights }} đêm</strong></p>
                            <p class="mt-2"><strong>Giá phòng:</strong> <span id="base_room_total_text" class="text-lg font-semibold text-blue-600">{{ number_format($roomTotalCalculated,0,',','.') }} VNĐ</span></p>
                            <p class="mt-2"><strong>Tổng tiền dịch vụ:</strong> <span id="service_total_text" class="text-lg font-semibold text-green-600">{{ number_format($currentServiceTotal,0,',','.') }} VNĐ</span></p>
                            <p class="mt-3 pt-3 border-t border-blue-200"><strong>Tổng thanh toán:</strong> <span id="total_price" class="text-2xl font-bold text-blue-700">{{ number_format($roomTotalCalculated + $currentServiceTotal,0,',','.') }} VNĐ</span></p>
                            <input type="hidden" id="base_room_total" value="{{ $roomTotalCalculated }}">
                            <input type="hidden" id="tong_tien_input" name="tong_tien" value="{{ $roomTotalCalculated + $currentServiceTotal }}">
                        </div>

                        <div class="flex items-center justify-between">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Cập nhật
                            </button>
                            <a href="{{ route('admin.invoices.index') }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                                Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        // booking services grouped from server: { service_id: [ {ngay, so_luong}, ... ] }
        const bookingServicesServer = {!! json_encode($bookingServices->map(function($b) use($booking) { return ['service_id' => $b->service_id, 'quantity' => $b->quantity, 'used_at' => $b->used_at ? date('Y-m-d', strtotime($b->used_at)) : date('Y-m-d', strtotime($booking->ngay_nhan))]; })->groupBy('service_id')->map(function($group){ return $group->map(function($item){ return ['ngay'=>$item['used_at'],'so_luong'=>$item['quantity']]; })->values(); })->toArray()) !!};

        function loadTomSelectAndInit(cb) {
            if (window.TomSelect) return cb();
            var s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js';
            s.onload = cb;
            document.head.appendChild(s);
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount).replace('₫', 'VNĐ');
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadTomSelectAndInit(function() {
                try {
                    const selectEl = document.getElementById('services_select');
                    if (!selectEl) return;
                    const ts = new TomSelect(selectEl, {plugins:['remove_button'], persist:false, create:false,});

                    // pre-select existing services
                    try {
                        const initialIds = Object.keys(bookingServicesServer || {});
                        if (initialIds && initialIds.length) ts.setValue(initialIds);
                    } catch(e){ console.warn('preselect services error', e); }

                    function getBookingRangeDates() {
                        // invoice has booking object passed
                        const start = '{{ optional($booking)->ngay_nhan ? date('Y-m-d', strtotime($booking->ngay_nhan)) : '' }}';
                        const end = '{{ optional($booking)->ngay_tra ? date('Y-m-d', strtotime($booking->ngay_tra)) : '' }}';
                        if (!start || !end) return [];
                        const a = [];
                        const s = new Date(start);
                        const e = new Date(end);
                        for (let d = new Date(s); d <= e; d.setDate(d.getDate()+1)) a.push(new Date(d).toISOString().split('T')[0]);
                        return a;
                    }

                    function updateTotalsFromHidden() {
                        // Sum all service entries (sum across all entry hidden inputs for each service)
                        const baseRoom = parseFloat(document.getElementById('base_room_total')?.value || 0);
                        let servicesTotal = 0;
                        // iterate over selected service cards
                        const container = document.getElementById('selected_services_list');
                        if (!container) return;
                        const cards = container.querySelectorAll('[data-service-id]');
                        cards.forEach(card => {
                            const sid = card.getAttribute('data-service-id');
                            const price = parseFloat((document.querySelector('#services_select option[value="'+sid+'"]')?.dataset.price) || 0);
                            // sum all quantities from per-entry hidden inputs (ngay index) for this service
                            const hiddenQtys = Array.from(document.querySelectorAll('input[name^="services_data['+sid+'][entries]["][name$="[so_luong]"]'));
                            let svcTotal = 0;
                            hiddenQtys.forEach(h => { svcTotal += (parseFloat(h.value||0)); });
                            servicesTotal += (svcTotal * price);
                        });
                        const total = Math.max(0, baseRoom + servicesTotal);
                        // update UI and hidden input - display room, services, and total separately
                        document.getElementById('base_room_total_text').textContent = formatCurrency(baseRoom);
                        document.getElementById('service_total_text').textContent = formatCurrency(servicesTotal);
                        document.getElementById('total_price').textContent = formatCurrency(total);
                        const tInput = document.getElementById('tong_tien_input');
                        if (tInput) tInput.value = Math.round(total);
                    }

                    function renderSelectedServices(values) {
                        const container = document.getElementById('selected_services_list');
                        // Clear ALL child elements including stale services from cache
                        while (container.firstChild) {
                            container.removeChild(container.firstChild);
                        }
                        const range = getBookingRangeDates();

                        (values||[]).forEach(val => {
                            const option = selectEl.querySelector('option[value="'+val+'"]'); if(!option) return;
                            const id = val;
                            const serviceName = option.textContent?.split(' - ')[0] || option.innerText;
                            const price = parseFloat(option.dataset.price||0)||0;
                            const unit = option.dataset.unit || 'cái';

                            const card = document.createElement('div'); card.className='service-card-custom'; card.setAttribute('data-service-id', id);
                            const header = document.createElement('div'); header.className='service-card-header';
                            const titleDiv = document.createElement('div'); titleDiv.className='service-title'; titleDiv.textContent = serviceName;
                            const priceDiv = document.createElement('div'); priceDiv.className='service-price'; priceDiv.textContent = `${new Intl.NumberFormat('vi-VN').format(price)}/${unit}`;
                            header.appendChild(titleDiv); header.appendChild(priceDiv); card.appendChild(header);

                            const rows = document.createElement('div'); rows.id = 'service_dates_'+id;
                            function buildRow(dv, qty){
                                const r=document.createElement('div'); r.className='service-date-row';
                                const di=document.createElement('input'); di.type='date'; di.value=dv||'';
                                const rg=range; if(rg.length){ di.min=rg[0]; di.max=rg[rg.length-1]; }
                                di.addEventListener('focus', function(){ this.dataset.prev = this.value || ''; });
                                di.addEventListener('change', function(){ const val = this.value || ''; if(!val) { syncHidden(id); return; } const others = Array.from(document.querySelectorAll('#service_dates_'+id+' input[type=date]')).filter(i=>i!==this).map(i=>i.value); if (others.includes(val)){ this.value = this.dataset.prev || ''; alert('Ngày này đã được chọn cho dịch vụ này. Vui lòng chọn ngày khác.'); return; } syncHidden(id); });
                                const qi=document.createElement('input'); qi.type='number'; qi.min=1; qi.value=(qty && qty>0)?qty:1; qi.className='w-24'; qi.onchange = ()=>syncHidden(id);
                                const rem=document.createElement('button'); rem.type='button'; rem.className='service-remove-btn ml-2'; rem.textContent='Xóa'; rem.onclick=()=>{ r.remove(); syncHidden(id); };
                                r.appendChild(di); r.appendChild(qi); r.appendChild(rem); return r;
                            }

                            const existing = bookingServicesServer && bookingServicesServer[id] ? bookingServicesServer[id] : null;
                            if (existing && existing.length) {
                                existing.forEach(e => { rows.appendChild(buildRow(e.ngay || (range.length? range[0] : ''), e.so_luong || 1)); });
                            } else {
                                rows.appendChild(buildRow((range.length? range[0] : ''), 1));
                            }

                            const addBtn = document.createElement('button'); addBtn.type='button'; addBtn.className='service-add-day mt-2'; addBtn.textContent='Thêm ngày'; addBtn.onclick=function(){ const used=Array.from(rows.querySelectorAll('input[type=date]')).map(i=>i.value); const avail=getBookingRangeDates().find(d=>!used.includes(d)); if(avail) { rows.appendChild(buildRow(avail)); syncHidden(id); } };

                            card.appendChild(rows); card.appendChild(addBtn);

                            // checkbox + hidden sum + hidden service id
                            const cb = document.createElement('input'); cb.type='checkbox'; cb.name='services[]'; cb.value=id; cb.className='service-checkbox'; cb.style.display='none'; cb.checked=true;
                            const sum = document.createElement('input'); sum.type='hidden'; sum.name='services_data['+id+'][so_luong]'; sum.id='service_quantity_hidden_'+id; sum.value='1';
                            const dv = document.createElement('input'); dv.type='hidden'; dv.name='services_data['+id+'][dich_vu_id]'; dv.value=id;

                            container.appendChild(card); container.appendChild(cb); container.appendChild(sum); container.appendChild(dv);

                            function syncHidden(id){ // remove old entry-hidden
                                Array.from(document.querySelectorAll('input.entry-hidden[data-service="'+id+'"]')).forEach(n=>n.remove());
                                const rowsNow = Array.from(document.querySelectorAll('#service_dates_'+id+' .service-date-row'));
                                if(rowsNow.length===0){ try{ ts.removeItem(id); }catch(e){ const el=document.querySelector('[data-service-id="'+id+'"]'); if(el) el.remove(); } updateTotalsFromHidden(); return; }
                                let total=0; rowsNow.forEach((r,idx)=>{
                                    const dateVal = r.querySelector('input[type=date]')?.value||'';
                                    const qty = parseInt(r.querySelector('input[type=number]')?.value||1);
                                    total += qty;
                                    const h1=document.createElement('input'); h1.type='hidden'; h1.name='services_data['+id+'][entries]['+idx+'][ngay]'; h1.value=dateVal; h1.className='entry-hidden'; h1.setAttribute('data-service', id);
                                    const h2=document.createElement('input'); h2.type='hidden'; h2.name='services_data['+id+'][entries]['+idx+'][so_luong]'; h2.value=qty; h2.className='entry-hidden'; h2.setAttribute('data-service', id);
                                    container.appendChild(h1); container.appendChild(h2);
                                });
                                const sumEl = document.getElementById('service_quantity_hidden_'+id); if(sumEl) sumEl.value = total;
                                updateTotalsFromHidden();
                            }

                            // ensure hidden inputs created
                            syncHidden(id);
                        });
                        // after rendering, update totals
                        updateTotalsFromHidden();
                    }

                    // Initialize service total from existing services on page load
                    function initializeServiceTotal() {
                        const baseRoom = parseFloat(document.getElementById('base_room_total')?.value || 0);
                        let currentServiceTotal = 0;
                        const container = document.getElementById('selected_services_list');
                        if (container) {
                            const cards = container.querySelectorAll('[data-service-id]');
                            cards.forEach(card => {
                                const sid = card.getAttribute('data-service-id');
                                const price = parseFloat((document.querySelector('#services_select option[value="'+sid+'"]')?.dataset.price) || 0);
                                const hiddenQtys = Array.from(document.querySelectorAll('input[name^="services_data['+sid+'][entries]["][name$="[so_luong]"]'));
                                let svcTotal = 0;
                                hiddenQtys.forEach(h => { svcTotal += (parseFloat(h.value||0)); });
                                currentServiceTotal += (svcTotal * price);
                            });
                        }
                        const total = Math.max(0, baseRoom + currentServiceTotal);
                        document.getElementById('service_total_text').textContent = formatCurrency(currentServiceTotal);
                        document.getElementById('total_price').textContent = formatCurrency(total);
                        const tInput = document.getElementById('tong_tien_input');
                        if (tInput) tInput.value = Math.round(total);
                    }

                    ts.on('change', function(values){ renderSelectedServices(values || []); });
                    // initial render
                    renderSelectedServices(ts.getValue() || []);
                    // Initialize totals after initial render
                    setTimeout(() => { initializeServiceTotal(); }, 100);

                } catch(e){ console.error('Services init error', e); }
            });
        });
    </script>
@endpush
