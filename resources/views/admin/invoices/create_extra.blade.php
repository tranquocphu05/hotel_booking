@extends('layouts.admin')

@section('title', 'Tạo hóa đơn phát sinh')

@section('admin_content')
<div class="container mx-auto px-4 sm:px-8">
    <div class="py-8">
        <!-- Header with Invoice Info -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-blue-600 to-blue-400 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-3xl font-bold">Tạo hóa đơn phát sinh cho HĐ #{{ $invoice->id }}</h2>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-purple-100 text-sm">Khách hàng</p>
                        <p class="text-xl font-semibold">{{ $booking ? ($booking->username ?? ($booking->user->ho_ten ?? 'N/A')) : 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-blue-100 text-sm">Loại hóa đơn</p>
                        <p class="text-xl font-semibold">💙 Phát sinh (Dịch vụ)</p>
                    </div>
                    <div>
                        <p class="text-purple-100 text-sm">Tổng thanh toán</p>
                        <p class="text-2xl font-bold" id="final_total">0 VNĐ</p>
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
        <div class="mb-6 p-4 bg-purple-50 border-2 border-purple-200 rounded-lg">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Thông tin đặt phòng (phạm vi chọn ngày dịch vụ)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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
                    <p class="text-sm text-gray-600 font-semibold">Chú thích</p>
                    <p class="text-gray-900 font-medium text-sm italic">Chỉ tính tiền dịch vụ</p>
                </div>
            </div>
        </div>

        <div class="inline-block min-w-full shadow rounded-lg overflow-hidden">
            <div class="p-6 bg-white border-b border-gray-200">
                <form method="POST" action="{{ route('admin.invoices.store_extra', $invoice->id) }}">
                    @csrf

                    <!-- Inline services picker -->
                    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css" rel="stylesheet">
                    <style>
                        .service-card-custom{border-radius:12px;background:linear-gradient(135deg, #e0f2fe 0%, #bfdbfe 100%);border:2px solid #2563eb;padding:1.25rem;box-shadow:0 10px 25px rgba(37, 99, 235, 0.08);} 
                        .service-card-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1.25rem}
                        .service-card-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;padding-bottom:.5rem;border-bottom:2px solid #bfdbfe}
                        .service-card-header .service-title{color:#1e40af;font-weight:700;font-size:1.1rem}
                        .service-card-header .service-price{color:#1e3a8a;font-weight:600;font-size:0.95rem}
                        .service-date-row{display:flex;gap:.75rem;align-items:center;margin-top:.75rem;padding:.5rem;background:#ffffff;border-radius:8px;border:1px solid #bfdbfe}
                        .service-date-row input[type=date]{border:1px solid #93c5fd;padding:.45rem .6rem;border-radius:6px;background:#eff6ff;font-size:0.9rem;flex:1}
                        .service-date-row input[type=number]{border:1px solid #93c5fd;padding:.45rem .6rem;border-radius:6px;background:#eff6ff;width:80px;text-align:center}
                        .service-add-day{background:linear-gradient(135deg, #93c5fd 0%, #2563eb 100%);color:#07316a;padding:.5rem .75rem;border-radius:8px;border:1.5px solid #60a5fa;cursor:pointer;font-weight:600;font-size:0.9rem}
                        .service-add-day:hover{background:linear-gradient(135deg, #2563eb 0%, #1e40af 100%);box-shadow:0 4px 12px rgba(37, 99, 235, 0.15)}
                        .service-remove-btn{background:#fee2e2;color:#991b1b;padding:.4rem .6rem;border-radius:6px;border:1px solid #fecaca;cursor:pointer;font-weight:600;font-size:0.85rem}
                        .service-remove-btn:hover{background:#fca5a5;box-shadow:0 4px 12px rgba(185, 28, 28, 0.15)}
                        #services_select + .ts-control{margin-top:.5rem;border-color:#2563eb}
                        #selected_services_list .service-card-custom{transition:all .2s ease}
                        #selected_services_list .service-card-custom:hover{transform:translateY(-6px);box-shadow:0 15px 35px rgba(37, 99, 235, 0.15)}
                    </style>
                    <div class="bg-purple-50 p-4 rounded-lg mb-4">
                        <label for="services_select" class="block text-sm font-medium text-gray-700 mb-2">Chọn dịch vụ phát sinh (chỉ tính tiền dịch vụ)</label>
                        <select id="services_select" placeholder="Chọn 1 hoặc nhiều dịch vụ..." multiple>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" data-price="{{ $service->price }}" data-unit="{{ $service->unit ?? 'cái' }}">{{ $service->name }} - {{ number_format($service->price,0,',','.') }} VNĐ</option>
                            @endforeach
                        </select>
                        <div id="selected_services_list" class="service-card-grid grid grid-cols-1 md:grid-cols-3 gap-6 mt-4"></div>
                    </div>

                    {{-- Show dynamic service total ONLY (no room price) --}}
                    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p><strong>Ngày nhận:</strong> {{ optional($booking)->ngay_nhan ? date('d/m/Y', strtotime($booking->ngay_nhan)) : 'N/A' }} | <strong>Ngày trả:</strong> {{ optional($booking)->ngay_tra ? date('d/m/Y', strtotime($booking->ngay_tra)) : 'N/A' }}</p>
                        <p class="mt-2"><strong>⚠️ Lưu ý:</strong> Hóa đơn phát sinh chỉ tính tiền dịch vụ, không bao gồm tiền phòng</p>
                        <p class="mt-3 pt-3 border-t border-blue-200"><strong>Tổng tiền dịch vụ:</strong> <span id="service_total_text" class="text-2xl font-bold text-blue-700">0 VNĐ</span></p>
                        <input type="hidden" id="tong_tien_input" name="tong_tien" value="0">
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Xác nhận tạo hóa đơn phát sinh
                        </button>
                        <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="inline-block align-baseline font-bold text-sm text-blue-600 hover:text-blue-800">
                            Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        // Disable server-side prefill by default so "add new service" starts clean.
        const PREFILL_BOOKING_SERVICES = false;
        const bookingServicesServer = PREFILL_BOOKING_SERVICES ? {!! json_encode($bookingServices ?? []) !!} : {};

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
                    const ts = new TomSelect(selectEl, {plugins:['remove_button'], persist:false, create:false});

                    function getBookingRangeDates() {
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
                        let servicesTotal = 0;
                        const container = document.getElementById('selected_services_list');
                        if (!container) return;
                        const cards = container.querySelectorAll('[data-service-id]');
                        cards.forEach(card => {
                            const sid = card.getAttribute('data-service-id');
                            const price = parseFloat((document.querySelector('#services_select option[value="'+sid+'"]')?.dataset.price) || 0);
                            const hiddenQtys = Array.from(document.querySelectorAll('input[name^="services_data['+sid+'][entries]["][name$="[so_luong]"]'));
                            let svcTotal = 0;
                            hiddenQtys.forEach(h => { svcTotal += (parseFloat(h.value||0)); });
                            servicesTotal += (svcTotal * price);
                        });
                        document.getElementById('service_total_text').textContent = formatCurrency(servicesTotal);
                        document.getElementById('final_total').textContent = formatCurrency(servicesTotal);
                        const tInput = document.getElementById('tong_tien_input');
                        if (tInput) tInput.value = Math.round(servicesTotal);
                    }

                    const renderedServiceIds = new Set();

                    function renderSelectedServices(values) {
                        const container = document.getElementById('selected_services_list');
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

                            const existing = (PREFILL_BOOKING_SERVICES && bookingServicesServer && bookingServicesServer[id]) ? bookingServicesServer[id] : null;
                            if (existing && existing.length && !renderedServiceIds.has(id)) {
                                existing.forEach(e => { rows.appendChild(buildRow(e.ngay || (range.length? range[0] : ''), e.so_luong || 1)); });
                            } else {
                                // Default to a single entry when adding a new service
                                rows.appendChild(buildRow((range.length? range[0] : ''), 1));
                            }

                            const addBtn = document.createElement('button'); addBtn.type='button'; addBtn.className='service-add-day mt-2'; addBtn.textContent='Thêm ngày'; addBtn.onclick=function(){ const used=Array.from(rows.querySelectorAll('input[type=date]')).map(i=>i.value); const avail=getBookingRangeDates().find(d=>!used.includes(d)); if(avail) { rows.appendChild(buildRow(avail)); syncHidden(id); } };

                            card.appendChild(rows); card.appendChild(addBtn);

                            const cb = document.createElement('input'); cb.type='checkbox'; cb.name='services[]'; cb.value=id; cb.className='service-checkbox'; cb.style.display='none'; cb.checked=true;
                            const sum = document.createElement('input'); sum.type='hidden'; sum.name='services_data['+id+'][so_luong]'; sum.id='service_quantity_hidden_'+id; sum.value='1';
                            const dv = document.createElement('input'); dv.type='hidden'; dv.name='services_data['+id+'][dich_vu_id]'; dv.value=id;

                            container.appendChild(card); container.appendChild(cb); container.appendChild(sum); container.appendChild(dv);

                            function syncHidden(id){
                                Array.from(document.querySelectorAll('input.entry-hidden[data-service="'+id+'"]')).forEach(n=>n.remove());
                                const rowsNow = Array.from(document.querySelectorAll('#service_dates_'+id+' .service-date-row'));
                                if(rowsNow.length===0){
                                    // If no rows left, remove from rendered set so re-adding is fresh
                                    try{ ts.removeItem(id); }catch(e){ const el=document.querySelector('[data-service-id="'+id+'"]'); if(el) el.remove(); }
                                    renderedServiceIds.delete(id);
                                    updateTotalsFromHidden();
                                    return;
                                }
                                let total=0; rowsNow.forEach((r,idx)=>{
                                    const dateVal = r.querySelector('input[type=date]')?.value||'';
                                    const qty = parseInt(r.querySelector('input[type=number]')?.value||1);
                                    total += qty;
                                    const h1=document.createElement('input'); h1.type='hidden'; h1.name='services_data['+id+'][entries]['+idx+'][ngay]'; h1.value=dateVal; h1.className='entry-hidden'; h1.setAttribute('data-service', id);
                                    const h2=document.createElement('input'); h2.type='hidden'; h2.name='services_data['+id+'][entries]['+idx+'][so_luong]'; h2.value=qty; h2.className='entry-hidden'; h2.setAttribute('data-service', id);
                                    container.appendChild(h1); container.appendChild(h2);
                                });
                                const sumEl = document.getElementById('service_quantity_hidden_'+id); if(sumEl) sumEl.value = total;
                                // mark this service as rendered while it has rows
                                if (total > 0) renderedServiceIds.add(id);
                                updateTotalsFromHidden();
                            }
                            syncHidden(id);
                        });
                        updateTotalsFromHidden();
                    }

                    ts.on('change', function(values){ renderSelectedServices(values || []); });
                    renderSelectedServices([]);
                } catch(e){ console.error('Services init error', e); }
            });
        });
    </script>
@endpush
