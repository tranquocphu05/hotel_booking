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
                <form id="create_extra_form" method="POST" action="{{ route('admin.invoices.store_extra', $invoice->id) }}" onsubmit="return prepareServicesBeforeSubmit(event)">
                    @csrf

                    <!-- Inline services picker -->
                    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css" rel="stylesheet">
                    
                    <style>
                        .service-card-custom {
                            border-radius: 10px;
                            background: linear-gradient(135deg, #e0f2fe 0%, #bfdbfe 100%);
                            border: 1.5px solid #2563eb;
                            padding: 0.875rem;
                            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.06);
                        }
                        .service-card-grid {
                            display: grid;
                            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                            gap: 0.75rem;
                        }
                        .service-card-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            margin-bottom: 0.5rem;
                            padding-bottom: 0.4rem;
                            border-bottom: 1.5px solid #bfdbfe;
                        }
                        .service-card-header .service-title {
                            color: #1e40af;
                            font-weight: 600;
                            font-size: 0.95rem;
                        }
                        .service-card-header .service-price {
                            color: #1e3a8a;
                            font-weight: 600;
                            font-size: 0.85rem;
                        }
                        .service-date-row {
                            display: flex;
                            gap: 0.5rem;
                            align-items: center;
                            margin-top: 0.5rem;
                            padding: 0.4rem;
                            background: #ffffff;
                            border-radius: 6px;
                            border: 1px solid #bfdbfe;
                        }
                        .service-date-row input[type=date] {
                            border: 1px solid #93c5fd;
                            padding: 0.35rem 0.5rem;
                            border-radius: 5px;
                            background: #eff6ff;
                            font-size: 0.85rem;
                            flex: 1;
                        }
                        .service-date-row input[type=number] {
                            border: 1px solid #93c5fd;
                            padding: 0.35rem 0.5rem;
                            border-radius: 5px;
                            background: #eff6ff;
                            width: 64px;
                            text-align: center;
                            font-size: 0.85rem;
                        }
                        .service-add-day {
                            background: linear-gradient(135deg, #93c5fd 0%, #2563eb 100%);
                            color: #08203a;
                            padding: 0.4rem 0.6rem;
                            border-radius: 6px;
                            border: 1.5px solid #60a5fa;
                            cursor: pointer;
                            font-weight: 600;
                            font-size: 0.85rem;
                        }
                        .service-add-day:hover {
                            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
                            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.12);
                        }
                        .service-remove-btn {
                            background: #fee2e2;
                            color: #991b1b;
                            padding: 0.3rem 0.5rem;
                            border-radius: 5px;
                            border: 1px solid #fecaca;
                            cursor: pointer;
                            font-weight: 600;
                            font-size: 0.8rem;
                        }
                        .service-remove-btn:hover {
                            background: #fca5a5;
                            box-shadow: 0 3px 10px rgba(185, 28, 28, 0.12);
                        }
                        .entry-room-container {
                            display: flex;
                            gap: 0.5rem;
                            flex-wrap: wrap;
                            align-items: center;
                        }
                        #services_select + .ts-control {
                            margin-top: 0.5rem;
                            border-color: #2563eb;
                        }
                        #selected_services_list .service-card-custom {
                            transition: all 0.18s ease;
                        }
                        #selected_services_list .service-card-custom:hover {
                            transform: translateY(-4px);
                            box-shadow: 0 10px 26px rgba(37, 99, 235, 0.12);
                        }
                    </style>

                    <div class="bg-gray-50 p-4 rounded-lg mb-4">
                        <label for="services_select" class="block text-sm font-medium text-gray-700 mb-2">Chọn dịch vụ phát sinh (chỉ tính tiền dịch vụ)</label>
                        <select id="services_select" placeholder="Chọn 1 hoặc nhiều dịch vụ..." multiple>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" data-price="{{ $service->price }}" data-unit="{{ $service->unit ?? '' }}">{{ $service->name }} - {{ number_format($service->price,0,',','.') }} VNĐ</option>
                            @endforeach
                        </select>

                        <!-- selected services list (rendered by JS) -->
                        <div id="selected_services_list" class="service-card-grid grid grid-cols-1 md:grid-cols-3 gap-6 mt-4"></div>
                    </div>

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
        const PREFILL_BOOKING_SERVICES = false;
        const bookingServicesServer = PREFILL_BOOKING_SERVICES ? {!! json_encode($bookingServices ?? []) !!} : {};

        function loadTomSelectAndInit(cb) {
            if (window.TomSelect) return cb();
            
            // Ensure CSS is loaded
            if (!document.querySelector('link[href*="tom-select"]')) {
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css';
                document.head.appendChild(link);
            }
            
            var s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js';
            s.onerror = function() {
                console.error('Failed to load Tom Select');
            };
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
                    if (!selectEl) {
                        console.error('services_select element not found');
                        return;
                    }
                    if (!window.TomSelect) {
                        console.error('TomSelect not loaded');
                        return;
                    }
                    const ts = new TomSelect(selectEl, {
                        plugins: ['remove_button'],
                        persist: false,
                        create: false,
                        placeholder: 'Chọn 1 hoặc nhiều dịch vụ...'
                    });

                    function getRangeDates() {
                        const start = '{{ optional($booking)->ngay_nhan ? date('Y-m-d', strtotime($booking->ngay_nhan)) : '' }}';
                        const end = '{{ optional($booking)->ngay_tra ? date('Y-m-d', strtotime($booking->ngay_tra)) : '' }}';
                        if (!start || !end) return [];
                        const a = [];
                        const s = new Date(start);
                        const e = new Date(end);
                        for (let d = new Date(s); d <= e; d.setDate(d.getDate()+1)) a.push(new Date(d).toISOString().split('T')[0]);
                        return a;
                    }

                    function updateTotalPrice() {
                        const ngayNhan = '{{ optional($booking)->ngay_nhan ? date('Y-m-d', strtotime($booking->ngay_nhan)) : '' }}';
                        const ngayTra = '{{ optional($booking)->ngay_tra ? date('Y-m-d', strtotime($booking->ngay_tra)) : '' }}';
                        
                        const assignedRooms = {!! json_encode($assignedRooms ?? []) !!};
                        const roomCount = (assignedRooms && assignedRooms.length) ? assignedRooms.length : 1;
                        
                        let servicesTotal = 0;
                        const container = document.getElementById('selected_services_list');
                        if (!container) return;
                        
                        const cards = container.querySelectorAll('[data-service-id]');
                        cards.forEach(card => {
                            const sid = card.getAttribute('data-service-id');
                            const price = parseFloat(document.querySelector('#services_select option[value="'+sid+'"]')?.dataset.price || 0);
                            const mode = card.querySelector('input[name="service_room_mode_' + sid + '"]:checked')?.value || 'global';
                            const rows = Array.from(card.querySelectorAll('#service_dates_'+sid+' .service-date-row'));
                            
                            rows.forEach(r => {
                                const qty = parseInt(r.querySelector('input[type=number]')?.value || 1) || 0;
                                if (qty <= 0) return;
                                
                                if (mode === 'global') {
                                    // Global: áp dụng tất cả phòng = price × roomCount × qty
                                    servicesTotal += (price * roomCount * qty);
                                } else {
                                    // Specific: chỉ phòng được chọn = price × selectedRoomCount × qty
                                    const entryRoomChecks = r.querySelectorAll('.entry-room-checkbox:checked').length || 0;
                                    servicesTotal += (price * entryRoomChecks * qty);
                                }
                            });
                        });
                        
                        document.getElementById('service_total_text').textContent = formatCurrency(servicesTotal);
                        document.getElementById('final_total').textContent = formatCurrency(servicesTotal);
                        const tInput = document.getElementById('tong_tien_input');
                        if (tInput) tInput.value = Math.round(servicesTotal);
                    }

                    function renderSelectedServices(values) {
                        const container = document.getElementById('selected_services_list');
                        const prevCounts = {};
                        
                        Array.from(container.querySelectorAll('[data-service-id]')).forEach(card => {
                            const id = card.getAttribute('data-service-id');
                            prevCounts[id] = card.querySelectorAll('.service-date-row')?.length || 0;
                        });

                        container.innerHTML = '';
                        const range = getRangeDates();

                        (values || []).forEach(val => {
                            const option = selectEl.querySelector('option[value="' + val + '"]');
                            if (!option) return;
                            const serviceId = val;
                            const serviceName = option.textContent?.split(' - ')[0] || option.innerText;
                            const servicePrice = parseFloat(option.dataset.price || 0);
                            const unit = option.dataset.unit || 'cái';

                            const card = document.createElement('div');
                            card.className = 'service-card-custom';
                            card.setAttribute('data-service-id', serviceId);

                            const header = document.createElement('div');
                            header.className = 'service-card-header';
                            const title = document.createElement('div');
                            title.className = 'service-title';
                            title.textContent = serviceName;
                            const price = document.createElement('div');
                            price.className = 'service-price';
                            price.textContent = `${new Intl.NumberFormat('vi-VN').format(servicePrice)}/${unit}`;
                            header.appendChild(title);
                            header.appendChild(price);
                            card.appendChild(header);

                            // Room selection section
                            const roomSection = document.createElement('div');
                            roomSection.className = 'bg-blue-50 p-3 rounded-lg mt-3 border border-blue-200';
                            roomSection.id = 'room_selection_' + serviceId;
                            
                            const roomToggle = document.createElement('div');
                            roomToggle.className = 'flex gap-2 mb-2';
                            
                            const globalRadio = document.createElement('input');
                            globalRadio.type = 'radio';
                            globalRadio.name = 'service_room_mode_' + serviceId;
                            globalRadio.value = 'global';
                            globalRadio.checked = true;
                            globalRadio.id = 'global_' + serviceId;
                            
                            const globalLabel = document.createElement('label');
                            globalLabel.htmlFor = 'global_' + serviceId;
                            globalLabel.className = 'text-sm flex items-center gap-2 cursor-pointer';
                            globalLabel.innerHTML = '<span>Áp dụng tất cả phòng</span>';
                            
                            const specificRadio = document.createElement('input');
                            specificRadio.type = 'radio';
                            specificRadio.name = 'service_room_mode_' + serviceId;
                            specificRadio.value = 'specific';
                            specificRadio.id = 'specific_' + serviceId;
                            
                            const specificLabel = document.createElement('label');
                            specificLabel.htmlFor = 'specific_' + serviceId;
                            specificLabel.className = 'text-sm flex items-center gap-2 cursor-pointer';
                            specificLabel.innerHTML = '<span>Chọn phòng riêng</span>';
                            
                            roomToggle.appendChild(globalRadio);
                            roomToggle.appendChild(globalLabel);
                            roomToggle.appendChild(specificRadio);
                            roomToggle.appendChild(specificLabel);
                            roomSection.appendChild(roomToggle);
                            card.appendChild(roomSection);

                            const rows = document.createElement('div');
                            rows.id = 'service_dates_' + serviceId;

                            function buildDateRow(dateVal) {
                                const r = document.createElement('div');
                                r.className = 'service-date-row';
                                const dateInput = document.createElement('input');
                                dateInput.type = 'date';
                                dateInput.value = dateVal || '';
                                if (range.length) {
                                    dateInput.min = range[0];
                                    dateInput.max = range[range.length - 1];
                                }
                                dateInput.addEventListener('change', () => syncHiddenEntries(serviceId));
                                
                                const qtyInput = document.createElement('input');
                                qtyInput.type = 'number';
                                qtyInput.min = 1;
                                qtyInput.value = 1;
                                qtyInput.className = 'w-24';
                                qtyInput.addEventListener('change', () => syncHiddenEntries(serviceId));
                                
                                const removeBtn = document.createElement('button');
                                removeBtn.type = 'button';
                                removeBtn.className = 'service-remove-btn ml-2';
                                removeBtn.textContent = 'Xóa';
                                removeBtn.onclick = () => {
                                    r.remove();
                                    syncHiddenEntries(serviceId);
                                };
                                
                                r.appendChild(dateInput);
                                r.appendChild(qtyInput);
                                r.appendChild(removeBtn);
                                
                                const entryRoomContainer = document.createElement('div');
                                entryRoomContainer.className = 'entry-room-container mt-2 pl-2 border-l hidden';
                                r.appendChild(entryRoomContainer);
                                
                                return r;
                            }

                            if (range.length) {
                                rows.appendChild(buildDateRow(range[0]));
                            } else {
                                rows.appendChild(buildDateRow(''));
                            }

                            const addBtn = document.createElement('button');
                            addBtn.type = 'button';
                            addBtn.className = 'service-add-day mt-2';
                            addBtn.textContent = 'Thêm ngày';
                            addBtn.onclick = function() {
                                const used = Array.from(rows.querySelectorAll('input[type=date]')).map(i => i.value);
                                const avail = getRangeDates().find(d => !used.includes(d));
                                if (avail) {
                                    rows.appendChild(buildDateRow(avail));
                                    syncHiddenEntries(serviceId);
                                }
                            };
                            
                            card.appendChild(rows);
                            card.appendChild(addBtn);
                            
                            globalRadio.onchange = () => {
                                Array.from(card.querySelectorAll('.entry-room-container')).forEach(c => c.classList.add('hidden'));
                                updateServiceRoomLists();
                                syncHiddenEntries(serviceId);
                            };
                            
                            specificRadio.onchange = () => {
                                updateServiceRoomLists();
                                syncHiddenEntries(serviceId);
                            };
                            
                            container.appendChild(card);
                            window.syncHiddenEntries = syncHiddenEntries;
                            syncHiddenEntries(serviceId);
                        });

                        // Update room lists to show checkboxes for each entry
                        try { updateServiceRoomLists(); } catch(e) { console.error('Error updating room lists:', e); }
                        
                        updateTotalPrice();
                    }

                    // Build per-entry room checkboxes based on assigned rooms
                    function updateServiceRoomLists() {
                        const assignedRooms = {!! json_encode($assignedRooms ?? []) !!};
                        
                        if (!assignedRooms || assignedRooms.length === 0) {
                            console.warn('No assigned rooms available');
                            return;
                        }
                        
                        document.querySelectorAll('[data-service-id]').forEach(card => {
                            const serviceId = card.getAttribute('data-service-id');
                            const rows = card.querySelectorAll('.service-date-row');
                            const modeRadios = card.querySelectorAll('input[name="service_room_mode_' + serviceId + '"]');
                            const mode = Array.from(modeRadios).find(r => r.checked)?.value || 'global';
                            
                            rows.forEach((r, idx) => {
                                let entryRoomContainer = r.querySelector('.entry-room-container');
                                if (!entryRoomContainer) {
                                    entryRoomContainer = document.createElement('div');
                                    entryRoomContainer.className = 'entry-room-container mt-2 pl-2 border-l hidden';
                                    r.appendChild(entryRoomContainer);
                                }
                                entryRoomContainer.innerHTML = '';
                                
                                if (mode === 'specific') {
                                    entryRoomContainer.classList.remove('hidden');
                                    // Create checkbox for each assigned room
                                    assignedRooms.forEach(room => {
                                        const wrap = document.createElement('label');
                                        wrap.className = 'flex items-center gap-1 text-xs cursor-pointer mb-1';
                                        
                                        const cb = document.createElement('input');
                                        cb.type = 'checkbox';
                                        cb.className = 'entry-room-checkbox';
                                        cb.setAttribute('data-room-id', room.id);
                                        cb.value = room.id;
                                        cb.onchange = () => syncHiddenEntries(serviceId);
                                        
                                        wrap.appendChild(cb);
                                        wrap.appendChild(document.createTextNode(room.so_phong + ' (' + room.ten_loai + ')'));
                                        entryRoomContainer.appendChild(wrap);
                                    });
                                } else {
                                    entryRoomContainer.classList.add('hidden');
                                }
                            });
                        });
                    }

                    function syncHiddenEntries(id) {
                        const card = document.querySelector('[data-service-id="'+id+'"]');
                        if (!card) return;
                        
                        const container = document.getElementById('selected_services_list');
                        if (!container) return;
                        
                        const rowsNow = Array.from(document.querySelectorAll('#service_dates_'+id+' .service-date-row'));
                        if (rowsNow.length === 0) {
                            try { ts.removeItem(id); } catch(e){
                                if(card) card.remove();
                            }
                            updateTotalPrice();
                            return;
                        }

                        Array.from(document.querySelectorAll('input.entry-hidden[data-service="'+id+'"]')).forEach(n=>n.remove());

                        const mode = card.querySelector('input[name="service_room_mode_' + id + '"]:checked')?.value || 'global';
                        const assignedRooms = {!! json_encode($assignedRooms ?? []) !!};

                        let total=0;
                        rowsNow.forEach((r, idx)=>{
                            const dateVal = r.querySelector('input[type=date]')?.value || '';
                            const qty = parseInt(r.querySelector('input[type=number]')?.value || 1);
                            
                            const entryRoomChecks = Array.from(r.querySelectorAll('.entry-room-checkbox:checked'));
                            
                            if (mode === 'specific' && entryRoomChecks.length === 0) {
                                return;
                            }
                            
                            total += qty;
                            
                            const hNgay = document.createElement('input');
                            hNgay.type='hidden';
                            hNgay.name='services_data['+id+'][entries]['+idx+'][ngay]';
                            hNgay.value=dateVal;
                            hNgay.className='entry-hidden';
                            hNgay.setAttribute('data-service', id);
                            const hSo = document.createElement('input');
                            hSo.type='hidden';
                            hSo.name='services_data['+id+'][entries]['+idx+'][so_luong]';
                            hSo.value=qty;
                            hSo.className='entry-hidden';
                            hSo.setAttribute('data-service', id);
                            container.appendChild(hNgay);
                            container.appendChild(hSo);

                            if (mode === 'specific') {
                                entryRoomChecks.forEach((erc) => {
                                    const hRoom = document.createElement('input');
                                    hRoom.type = 'hidden';
                                    hRoom.name = 'services_data['+id+'][entries]['+idx+'][phong_ids][]';
                                    hRoom.value = erc.getAttribute('data-room-id') || erc.value;
                                    hRoom.className = 'entry-hidden';
                                    hRoom.setAttribute('data-service', id);
                                    container.appendChild(hRoom);
                                });
                            } else if (mode === 'global') {
                                // When global mode, still create phong_ids for all assigned rooms
                                // so the controller creates per-room BookingService entries
                                if (assignedRooms && assignedRooms.length > 0) {
                                    assignedRooms.forEach(room => {
                                        const hRoom = document.createElement('input');
                                        hRoom.type = 'hidden';
                                        hRoom.name = 'services_data['+id+'][entries]['+idx+'][phong_ids][]';
                                        hRoom.value = room.id;
                                        hRoom.className = 'entry-hidden';
                                        hRoom.setAttribute('data-service', id);
                                        container.appendChild(hRoom);
                                    });
                                }
                            }
                        });
                        const sumEl = document.getElementById('service_quantity_hidden_'+id);
                        if(sumEl) sumEl.value = total;
                        updateTotalPrice();
                    }

                    renderSelectedServices(ts.getValue() || []);
                    try { updateServiceRoomLists(); } catch(e){}

                    ts.on('change', function(values){ 
                        renderSelectedServices(values || []); 
                        try { updateServiceRoomLists(); } catch(e){}
                    });

                    // Ensure hidden inputs are synced just before submit
                    window.prepareServicesBeforeSubmit = function(e) {
                        try {
                            // For each selected service, force sync of its hidden inputs
                            const vals = ts.getValue() || [];
                            vals.forEach(id => {
                                try { syncHiddenEntries(id); } catch(_) {}
                            });
                        } catch (_) {}
                        // allow submit to continue
                        return true;
                    };

                    console.log('✓ Extra invoice form initialized successfully');
                } catch(e) { 
                    console.error('Services init error', e); 
                }
            });
        });
    </script>
@endpush
