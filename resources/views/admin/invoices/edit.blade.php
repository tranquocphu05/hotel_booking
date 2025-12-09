@extends('layouts.admin')

@section('title', 'Cập nhật Hóa đơn')

@section('admin_content')
@php
    $nights = 1;
    if ($booking && $booking->ngay_nhan && $booking->ngay_tra) {
        $nights = max(1, \Carbon\Carbon::parse($booking->ngay_tra)->diffInDays(\Carbon\Carbon::parse($booking->ngay_nhan)));
    }
@endphp
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

                        <!-- Inline services picker (same UI as create_extra) -->
                        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css" rel="stylesheet">
                        <style>
                            .service-card-custom{
                                border-radius:10px;
                                background: linear-gradient(135deg, #e0f2fe 0%, #bfdbfe 100%);
                                border:1.5px solid #2563eb;
                                padding:0.875rem;
                                box-shadow: 0 6px 18px rgba(37, 99, 235, 0.06);
                            }
                            .service-card-grid{display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:0.75rem}
                            .service-card-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;padding-bottom:0.4rem;border-bottom:1.5px solid #bfdbfe}
                            .service-card-header .service-title{color:#1e40af;font-weight:600;font-size:0.95rem}
                            .service-card-header .service-price{color:#1e3a8a;font-weight:600;font-size:0.85rem}
                            .service-date-row{display:flex;gap:0.5rem;align-items:center;margin-top:0.5rem;padding:0.4rem;background:#ffffff;border-radius:6px;border:1px solid #bfdbfe}
                            .service-date-row input[type=date]{border:1px solid #93c5fd;padding:0.35rem 0.5rem;border-radius:5px;background:#eff6ff;font-size:0.85rem;flex:1}
                            .service-date-row input[type=number]{border:1px solid #93c5fd;padding:0.35rem 0.5rem;border-radius:5px;background:#eff6ff;width:64px;text-align:center;font-size:0.85rem}
                            .service-add-day{background:linear-gradient(135deg, #93c5fd 0%, #2563eb 100%);color:#08203a;padding:0.4rem 0.6rem;border-radius:6px;border:1.5px solid #60a5fa;cursor:pointer;font-weight:600;font-size:0.85rem}
                            .service-add-day:hover{background:linear-gradient(135deg, #2563eb 0%, #1e40af 100%);box-shadow:0 4px 12px rgba(37, 99, 235, 0.12)}
                            .service-remove-btn{background:#fee2e2;color:#991b1b;padding:0.3rem 0.5rem;border-radius:5px;border:1px solid #fecaca;cursor:pointer;font-weight:600;font-size:0.8rem}
                            .service-remove-btn:hover{background:#fca5a5;box-shadow:0 3px 10px rgba(185,28,28,0.12)}
                            .entry-room-container{display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center}
                            #services_select + .ts-control{margin-top:.5rem;border-color:#2563eb}
                            #selected_services_list .service-card-custom{transition:all .18s ease}
                            #selected_services_list .service-card-custom:hover{transform:translateY(-4px);box-shadow:0 10px 26px rgba(37, 99, 235, 0.12)}
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
                        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p><strong>Ngày nhận:</strong> {{ optional($booking)->ngay_nhan ? date('d/m/Y', strtotime($booking->ngay_nhan)) : 'N/A' }} | <strong>Ngày trả:</strong> {{ optional($booking)->ngay_tra ? date('d/m/Y', strtotime($booking->ngay_tra)) : 'N/A' }} | <strong>{{ $nights }} đêm</strong></p>
                            @php
                                $voucherPercent = 0;
                                $voucherLoaiPhongId = null;
                                if ($booking && isset($booking->voucher) && $booking->voucher) {
                                    $voucher = $booking->voucher;
                                    $voucherPercent = $voucher->gia_tri ?? 0;
                                    $voucherLoaiPhongId = $voucher->loai_phong_id ?? null;
                                }
                            @endphp
                            @if($invoice->isExtra())
                                <p class="mt-2"><strong>Hóa đơn dịch vụ:</strong> <span class="text-sm text-gray-600">(Giá phòng không được tính trong hóa đơn này)</span></p>
                                <p class="mt-2"><strong>Giá phòng:</strong> <span id="base_room_total_text" class="text-lg font-semibold text-blue-600">0 VNĐ</span></p>
                                <p class="mt-2"><strong>Tổng tiền dịch vụ:</strong> <span id="service_total_text" class="text-lg font-semibold text-green-600">{{ number_format($currentServiceTotal,0,',','.') }} VNĐ</span></p>
                                <p class="mt-3 pt-3 border-t border-blue-200"><strong>Tổng thanh toán:</strong> <span id="total_price" class="text-2xl font-bold text-blue-700">{{ number_format($currentServiceTotal,0,',','.') }} VNĐ</span></p>
                                <input type="hidden" id="base_room_total" value="0">
                                <input type="hidden" id="server_nights" value="{{ $nights }}">
                                <input type="hidden" id="voucher_percent" value="0">
                                <input type="hidden" id="voucher_amount" value="0">
                                <input type="hidden" id="tong_tien_input" name="tong_tien" value="{{ $currentServiceTotal }}">
                            @else
                                <p class="mt-2"><strong>Giá phòng (tien_phong):</strong> <span id="base_room_total_text" class="text-lg font-semibold text-blue-600">{{ number_format($roomTotalCalculated,0,',','.') }} VNĐ</span></p>
                                <p class="mt-2"><strong>Tổng tiền dịch vụ (tien_dich_vu):</strong> <span id="service_total_text" class="text-lg font-semibold text-green-600">{{ number_format($currentServiceTotal,0,',','.') }} VNĐ</span></p>
                                <p class="mt-2"><strong>Giảm giá (giam_gia):</strong> <span id="voucher_discount_text" class="text-sm text-red-600">{{ $voucherPercent > 0 ? ('-' . number_format($voucherDiscount,0,',','.') . ' VNĐ') : '0 VNĐ' }}</span></p>
                                <p class="mt-3 pt-3 border-t border-blue-200"><strong>Tổng thanh toán (tong_tien):</strong> <span id="total_price" class="text-2xl font-bold text-blue-700">{{ number_format($roomTotalCalculated + $currentServiceTotal - $voucherDiscount,0,',','.') }} VNĐ</span></p>
                                <input type="hidden" id="base_room_total" value="{{ $roomTotalCalculated }}">
                                <input type="hidden" id="server_nights" value="{{ $nights }}">
                                <input type="hidden" id="voucher_percent" value="{{ $voucherPercent }}">
                                <input type="hidden" id="voucher_amount" value="{{ $voucherDiscount }}">
                                <input type="hidden" id="tong_tien_input" name="tong_tien" value="{{ $roomTotalCalculated + $currentServiceTotal - $voucherDiscount }}">
                            @endif
                        </div>

                        <div class="flex items-center justify-between">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Cập nhật
                            </button>
                            <a href="{{ route('admin.invoices.index') }}" class="inline-block align-baseline font-bold text-sm text-blue-600 hover:text-blue-800">
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
        const bookingServicesServer = {!! json_encode($bookingServicesServer ?? []) !!};
        const assignedRooms = {!! json_encode($assignedRooms ?? []) !!};
        // Array of assigned room ids for quick access in global mode
        const assignedPhongIds = Array.isArray(assignedRooms) ? assignedRooms.map(r => r.id) : [];
        const allServices = {!! json_encode($services->toArray()) !!};

        function getBookingRangeDates() {
            const start = '{{ optional($booking)->ngay_nhan ? date('Y-m-d', strtotime($booking->ngay_nhan)) : '' }}';
            const end = '{{ optional($booking)->ngay_tra ? date('Y-m-d', strtotime($booking->ngay_tra)) : '' }}';
            if (!start || !end) return [];
            const dates = [];
            const s = new Date(start);
            const e = new Date(end);
            for (let d = new Date(s); d <= e; d.setDate(d.getDate() + 1)) {
                dates.push(new Date(d).toISOString().split('T')[0]);
            }
            return dates;
        }

        function buildDateRow(serviceId, dateVal = '') {
            const r = document.createElement('div');
            r.className = 'service-date-row';

            const d = document.createElement('input');
            d.type = 'date';
            d.className = 'border rounded p-1';
            d.value = dateVal || '';
            const rg = getBookingRangeDates();
            if (rg.length) {
                d.min = rg[0];
                d.max = rg[rg.length - 1];
            }
            d.addEventListener('change', () => syncHiddenEntries(serviceId));

            const q = document.createElement('input');
            q.type = 'number';
            q.min = 1;
            q.value = 1;
            q.className = 'w-24 border rounded p-1 text-center';
            q.onchange = () => syncHiddenEntries(serviceId);

            const rem = document.createElement('button');
            rem.type = 'button';
            rem.className = 'service-remove-btn ml-2';
            rem.textContent = 'Xóa';
            rem.onclick = () => {
                r.remove();
                syncHiddenEntries(serviceId);
                updateTotalsFromHidden && updateTotalsFromHidden();
            };

            r.appendChild(d);
            r.appendChild(q);
            r.appendChild(rem);

            // Entry room container for per-room selection - INLINE
            const entryRoomContainer = document.createElement('div');
            entryRoomContainer.className = 'entry-room-container';
            entryRoomContainer.style.display = 'none';
            r.appendChild(entryRoomContainer);

            return r;
        }

        function updateServiceRoomLists(filterServiceId) {
            document.querySelectorAll('[data-service-id]').forEach(card => {
                const serviceId = card.getAttribute('data-service-id');

                if (filterServiceId && parseInt(serviceId) !== parseInt(filterServiceId)) {
                    return;
                }

                const specificRadio = card.querySelector('input[name="service_room_mode_' + serviceId + '"][value="specific"]');
                const isSpecific = specificRadio ? specificRadio.checked : false;
                console.log('updateServiceRoomLists - serviceId:', serviceId, 'isSpecific:', isSpecific);

                const rows = card.querySelectorAll('.service-date-row');
                console.log('updateServiceRoomLists - found rows:', rows.length);
                rows.forEach((r, rowIdx) => {
                    const dateValRow = r.querySelector('input[type=date]')?.value || '';
                    console.log('  Row', rowIdx, '- dateValRow:', dateValRow);
                    let entryRoomContainer = r.querySelector('.entry-room-container');
                    if (!entryRoomContainer) {
                        entryRoomContainer = document.createElement('div');
                        entryRoomContainer.className = 'entry-room-container';
                        r.appendChild(entryRoomContainer);
                    }
                    entryRoomContainer.innerHTML = '';

                    if (!isSpecific) {
                        entryRoomContainer.style.display = 'none';
                        return;
                    } else {
                        entryRoomContainer.style.display = '';
                    }

                    // Build entriesByDate for this service so we can pre-check boxes per row date
                    const entriesByDate = {};
                    if (bookingServicesServer[serviceId]) {
                        console.log('  Building entriesByDate for service', serviceId, 'from bookingServicesServer entries:', bookingServicesServer[serviceId]['entries'].length);
                        bookingServicesServer[serviceId]['entries'].forEach(entry => {
                            const day = entry['ngay'] || '';
                            console.log('    Entry day:', day, 'phong_ids:', entry['phong_ids']);
                            if (!entriesByDate[day]) entriesByDate[day] = {
                                phong_ids: []
                            };
                            if (entry['phong_ids'] && Array.isArray(entry['phong_ids'])) {
                                entriesByDate[day].phong_ids = Array.from(new Set([
                                    ...entriesByDate[day].phong_ids,
                                    ...entry['phong_ids']
                                ]));
                            }
                        });
                    }
                    console.log('  entriesByDate:', entriesByDate, 'phong_ids for row date:', entriesByDate[dateValRow]?.phong_ids);

                    assignedRooms.forEach(room => {
                        const ewrap = document.createElement('div');
                        ewrap.className = 'inline-flex items-center gap-1 mr-2';

                        const ecb = document.createElement('input');
                        ecb.type = 'checkbox';
                        ecb.className = 'entry-room-checkbox';
                        ecb.setAttribute('data-room-id', room.id);
                        ecb.value = room.id;

                        ecb.onchange = () => {
                            syncHiddenEntries(serviceId);
                            updateTotalsFromHidden && updateTotalsFromHidden();
                        };

                        // Pre-check if bookingServicesServer has this room for this row date
                        try {
                            const phongIdsForDate = (entriesByDate[dateValRow]?.phong_ids || [])
                                .map(p => parseInt(p));
                            if (phongIdsForDate.includes(parseInt(room.id))) {
                                console.log('    Pre-checking room', room.id, 'for date', dateValRow);
                                ecb.checked = true;
                            }
                        } catch (e) {
                            console.log('    Error pre-checking room', room.id, ':', e.message);
                        }

                        const elbl = document.createElement('label');
                        elbl.className = 'text-xs cursor-pointer';
                        elbl.textContent = room.so_phong + ' (' + room.ten_loai + ')';

                        ewrap.appendChild(ecb);
                        ewrap.appendChild(elbl);
                        entryRoomContainer.appendChild(ewrap);
                    });
                });
                
                // After rendering all checkboxes, sync the hidden inputs
                syncHiddenEntries(serviceId);
            });
        }

        function renderServiceCard(service) {
            const sid = service.id;
            const existing = bookingServicesServer[sid] ? bookingServicesServer[sid]['entries'] || [] : [];
            const hasSpecific = existing.some(e => e['phong_ids'] && e['phong_ids'].length > 0);

            console.log('renderServiceCard for service:', sid, 'existing entries:', existing, 'hasSpecific:', hasSpecific);

            // Card wrapper
            const card = document.createElement('div');
            card.className = 'service-card-custom';
            card.setAttribute('data-service-id', sid);
            console.log('Created card element:', card);

            // Header
            const header = document.createElement('div');
            header.className = 'service-card-header';
            const title = document.createElement('div');
            title.innerHTML = `<div class="service-title">${service.name}</div>`;
            const price = document.createElement('div');
            price.className = 'service-price';
            price.innerHTML = `${new Intl.NumberFormat('vi-VN').format(service.price)}/${service.unit || 'cái'}`;
            header.appendChild(title);
            header.appendChild(price);
            card.appendChild(header);

            // Room selection radios
            const roomSection = document.createElement('div');
            roomSection.className = 'service-room-mode';

            const globalRadio = document.createElement('input');
            globalRadio.type = 'radio';
            globalRadio.name = 'service_room_mode_' + sid;
            globalRadio.value = 'global';
            globalRadio.checked = !hasSpecific;
            globalRadio.id = 'global_' + sid;

            const globalLabel = document.createElement('label');
            globalLabel.htmlFor = 'global_' + sid;
            globalLabel.className = 'text-sm flex items-center gap-2 cursor-pointer';
            globalLabel.innerHTML = '<span>Áp dụng tất cả phòng</span>';

            const specificRadio = document.createElement('input');
            specificRadio.type = 'radio';
            specificRadio.name = 'service_room_mode_' + sid;
            specificRadio.value = 'specific';
            specificRadio.checked = hasSpecific;
            specificRadio.id = 'specific_' + sid;

            const specificLabel = document.createElement('label');
            specificLabel.htmlFor = 'specific_' + sid;
            specificLabel.className = 'text-sm flex items-center gap-2 cursor-pointer';
            specificLabel.innerHTML = '<span>Chọn phòng riêng</span>';

            roomSection.appendChild(globalRadio);
            roomSection.appendChild(globalLabel);
            roomSection.appendChild(specificRadio);
            roomSection.appendChild(specificLabel);

            globalRadio.onchange = () => {
                card.querySelectorAll('.entry-room-container').forEach(c => {
                    c.style.display = 'none';
                    Array.from(c.querySelectorAll('input[type=checkbox]')).forEach(cb => cb.checked = false);
                });
                updateServiceRoomLists();
                syncHiddenEntries(sid);
                updateTotalsFromHidden && updateTotalsFromHidden();
            };

            specificRadio.onchange = () => {
                updateServiceRoomLists();
                syncHiddenEntries(sid);
                updateTotalsFromHidden && updateTotalsFromHidden();
            };

            card.appendChild(roomSection);

            // Date rows container
            const rows = document.createElement('div');
            rows.id = 'service_dates_' + sid;

            // Render existing entries - GROUP BY DATE to avoid duplicates
            if (existing.length > 0) {
                const entriesByDate = {};
                existing.forEach((entry, idx) => {
                    const day = entry['ngay'] || '';
                    if (!entriesByDate[day]) {
                        entriesByDate[day] = {
                            so_luong: entry['so_luong'] || 1,
                            phong_ids: entry['phong_ids'] || [],
                            first_idx: idx
                        };
                    } else {
                        // Merge phong_ids if multiple entries for same date
                        entriesByDate[day].phong_ids = Array.from(new Set([
                            ...entriesByDate[day].phong_ids,
                            ...(entry['phong_ids'] || [])
                        ]));
                    }
                });

                Object.entries(entriesByDate).forEach(([day, data]) => {
                    const row = buildDateRow(sid, day);
                    // Set quantity
                    row.querySelector('input[type=number]').value = data.so_luong || 1;
                    rows.appendChild(row);
                });
            }

            card.appendChild(rows);

            // Don't call updateServiceRoomLists here - card not in DOM yet!
            // It will be called after card is appended in updateServiceCards

            // Add day button
            const addBtn = document.createElement('button');
            addBtn.type = 'button';
            addBtn.className = 'service-add-day mt-3';
            addBtn.textContent = '+ Thêm ngày';
            addBtn.onclick = () => {
                const used = Array.from(rows.querySelectorAll('input[type="date"]')).map(i => i.value);
                const avail = getBookingRangeDates().find(d => !used.includes(d));
                if (avail) {
                    const newRow = buildDateRow(sid, avail);
                    rows.appendChild(newRow);
                    updateServiceRoomLists();
                    syncHiddenEntries(sid);
                    updateTotalsFromHidden && updateTotalsFromHidden();
                } else {
                    alert('Đã chọn đủ ngày. Không thể thêm ngày nữa.');
                }
            };

            card.appendChild(addBtn);

            // Hidden service marker
            const hcb = document.createElement('input');
            hcb.type = 'checkbox';
            hcb.className = 'service-checkbox';
            hcb.name = 'services[]';
            hcb.value = sid;
            hcb.setAttribute('data-price', service.price);
            hcb.style.display = 'none';
            hcb.checked = true;
            card.appendChild(hcb);

            // Hidden total quantity
            const hsum = document.createElement('input');
            hsum.type = 'hidden';
            hsum.name = 'services_data[' + sid + '][so_luong]';
            hsum.id = 'service_quantity_hidden_' + sid;
            hsum.value = '1';
            card.appendChild(hsum);

            // Hidden service ID
            const hdv = document.createElement('input');
            hdv.type = 'hidden';
            hdv.name = 'services_data[' + sid + '][dich_vu_id]';
            hdv.value = sid;
            card.appendChild(hdv);

            return card;
        }

        function syncHiddenEntries(serviceId) {
            console.log('syncHiddenEntries called for service:', serviceId);
            const container = document.getElementById('selected_services_list');

            // Get all entry rows
            const rowsNow = Array.from(document.querySelectorAll('#service_dates_' + serviceId + ' .service-date-row'));
            console.log('syncHiddenEntries - found rows:', rowsNow.length);

            // Remove existing hidden entry inputs for this service
            const oldHiddens = Array.from(document.querySelectorAll('input.entry-hidden[data-service="' + serviceId + '"]'));
            console.log('syncHiddenEntries - removing old hidden inputs:', oldHiddens.length);
            oldHiddens.forEach(n => {
                n.remove();
            });

            // Determine current mode
            const card = document.querySelector('[data-service-id="' + serviceId + '"]');
            const mode = card?.querySelector('input[name="service_room_mode_' + serviceId + '"]:checked')?.value || 'global';
            console.log('syncHiddenEntries - mode:', mode);

            let total = 0;
            rowsNow.forEach((r, idx) => {
                const dateVal = r.querySelector('input[type=date]')?.value || '';
                const qty = parseInt(r.querySelector('input[type=number]')?.value || 1);
                console.log('syncHiddenEntries - row', idx, 'date:', dateVal, 'qty:', qty);

                // Collect per-entry selected rooms
                const entryRoomChecks = Array.from(r.querySelectorAll('.entry-room-checkbox:checked'));
                console.log('  Found', entryRoomChecks.length, 'checked rooms:', entryRoomChecks.map(c => c.value));

                // If specific mode but no rooms checked, skip this entry
                if (mode === 'specific' && entryRoomChecks.length === 0) {
                    console.log('  Skipping - specific mode, no rooms checked');
                    return;
                }

                total += qty;

                // Create hidden inputs for this entry
                const hNgay = document.createElement('input');
                hNgay.type = 'hidden';
                hNgay.name = 'services_data[' + serviceId + '][entries][' + idx + '][ngay]';
                hNgay.value = dateVal;
                hNgay.className = 'entry-hidden';
                hNgay.setAttribute('data-service', serviceId);
                console.log('  Creating hidden input:', hNgay.name, '=', dateVal);
                container.appendChild(hNgay);

                const hSo = document.createElement('input');
                hSo.type = 'hidden';
                hSo.name = 'services_data[' + serviceId + '][entries][' + idx + '][so_luong]';
                hSo.value = qty;
                hSo.className = 'entry-hidden';
                hSo.setAttribute('data-service', serviceId);
                console.log('  Creating hidden input:', hSo.name, '=', qty);
                container.appendChild(hSo);

                // Add room IDs based on mode
                if (mode === 'global') {
                    // Global mode: add ALL assigned room IDs
                    console.log('  Global mode - adding all assigned rooms:', assignedPhongIds);
                    assignedPhongIds.forEach(phongId => {
                        const hRoom = document.createElement('input');
                        hRoom.type = 'hidden';
                        hRoom.name = 'services_data[' + serviceId + '][entries][' + idx + '][phong_ids][]';
                        hRoom.value = phongId;
                        hRoom.className = 'entry-hidden';
                        hRoom.setAttribute('data-service', serviceId);
                        container.appendChild(hRoom);
                    });
                } else {
                    // Specific mode: add only checked room IDs
                    console.log('  Specific mode - adding checked rooms:', entryRoomChecks.map(c => c.value));
                    entryRoomChecks.forEach((erc) => {
                        const hRoom = document.createElement('input');
                        hRoom.type = 'hidden';
                        hRoom.name = 'services_data[' + serviceId + '][entries][' + idx + '][phong_ids][]';
                        hRoom.value = erc.getAttribute('data-room-id') || erc.value;
                        hRoom.className = 'entry-hidden';
                        hRoom.setAttribute('data-service', serviceId);
                        container.appendChild(hRoom);
                    });
                }
            });

            const sumEl = document.getElementById('service_quantity_hidden_' + serviceId);
            if (sumEl) sumEl.value = total;
            console.log('syncHiddenEntries - total qty for service', serviceId, ':', total);
        }

        function updateTotalsFromHidden() {
            const isExtraInvoice = {!! json_encode($invoice->isExtra()) !!};
            // Read server-provided base room total (may be per-night or already total)
            const baseRoomRaw = parseFloat(document.getElementById('base_room_total')?.value || 0);
            // Determine server-side nights (fallback to 1)
            const serverNights = parseInt(document.getElementById('server_nights')?.value || 0) || 1;

            // Compute client-side nights from booking range (using getBookingRangeDates())
            let clientNights = 1;
            try {
                const rg = getBookingRangeDates();
                if (rg.length >= 2) {
                    // getBookingRangeDates returns inclusive dates array; nights = length - 1
                    clientNights = Math.max(1, rg.length - 1);
                }
            } catch (e) {
                clientNights = 1;
            }

            // Adjust baseRoom if server reported a per-night amount (serverNights may differ)
            let baseRoom = isExtraInvoice ? 0 : baseRoomRaw;
            if (!isExtraInvoice && serverNights > 0 && clientNights > 0 && serverNights !== clientNights && baseRoomRaw > 0) {
                const unit = baseRoomRaw / serverNights;
                baseRoom = unit * clientNights;
                console.log('updateTotalsFromHidden - adjusted baseRoom from', baseRoomRaw, 'serverNights', serverNights, 'clientNights', clientNights, '=>', baseRoom);
            }
            let servicesTotal = 0;
            const container = document.getElementById('selected_services_list');
            console.log('updateTotalsFromHidden - isExtraInvoice:', isExtraInvoice, 'baseRoom:', baseRoom);
            if (!container) {
                console.log('updateTotalsFromHidden - container not found!');
                return;
            }
            const cards = container.querySelectorAll('[data-service-id]');
            console.log('updateTotalsFromHidden - found cards:', cards.length);
            cards.forEach(card => {
                const sid = card.getAttribute('data-service-id');
                const serviceObj = allServices.find(s => s.id == sid);
                const price = parseFloat(serviceObj ? serviceObj.price : 0);
                console.log('updateTotalsFromHidden - service', sid, 'price:', price, 'serviceObj:', serviceObj);
                
                // Find ALL hidden inputs for this service's entries
                // Select hidden inputs which represent per-entry quantities for this service
                const selector = 'input[name^="services_data['+sid+'][entries]"][name$="[so_luong]"]';
                const hiddenQtys = Array.from(document.querySelectorAll(selector));
                console.log('updateTotalsFromHidden - selector:', selector, 'found inputs:', hiddenQtys.length);
                
                let svcTotal = 0;
                hiddenQtys.forEach((h, idx) => { 
                    const val = parseFloat(h.value||0);
                    
                    // For this entry, count how many phong_ids are associated
                    // Find all phong_ids hidden inputs for this specific entry
                    const entryIdx = h.name.match(/\[entries\]\[(\d+)\]/)[1];
                    const phongIdSelector = 'input[name="services_data['+sid+'][entries]['+entryIdx+'][phong_ids][]"]';
                    const phongIds = Array.from(document.querySelectorAll(phongIdSelector));
                    
                    const numRooms = phongIds.length > 0 ? phongIds.length : 1; // If no phong_ids, count as 1 (global)
                    const entryTotal = val * numRooms;
                    
                    console.log('  Entry', idx, '- input name:', h.name, 'qty:', val, 'rooms:', numRooms, 'entry total:', entryTotal);
                    svcTotal += entryTotal; 
                });
                console.log('updateTotalsFromHidden - service', sid, 'total qty*rooms:', svcTotal, 'price:', price, 'subtotal:', svcTotal * price);
                servicesTotal += (svcTotal * price);
            });
            // Use server-provided voucher amount (already respects voucher type)
            let voucherAmount = parseFloat(document.getElementById('voucher_amount')?.value || 0);
            if (isExtraInvoice) voucherAmount = 0; // voucher not applied to EXTRA invoices

            const voucherDiscountText = document.getElementById('voucher_discount_text');
            if (voucherDiscountText) voucherDiscountText.textContent = voucherAmount > 0 ? ('-' + formatCurrency(voucherAmount)) : '0 VNĐ';

            const total = Math.max(0, (baseRoom - voucherAmount) + servicesTotal);
            console.log('updateTotalsFromHidden - final: baseRoom:', baseRoom, 'servicesTotal:', servicesTotal, 'total:', total);
            document.getElementById('base_room_total_text').textContent = formatCurrency(baseRoom);
            document.getElementById('service_total_text').textContent = formatCurrency(servicesTotal);
            document.getElementById('total_price').textContent = formatCurrency(total);
            const tInput = document.getElementById('tong_tien_input');
            if (tInput) tInput.value = Math.round(total);
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount).replace('₫', 'VNĐ');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('selected_services_list');
            const selectEl = document.getElementById('services_select');
            
            console.log('DOMContentLoaded: container found:', !!container, container);
            console.log('DOMContentLoaded: selectEl found:', !!selectEl, selectEl);
            
            if (!selectEl || !container) return;

            // Initialize TomSelect
            function loadTomSelectAndInit(cb) {
                if (window.TomSelect) return cb();
                var s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js';
                s.onload = cb;
                document.head.appendChild(s);
            }

            loadTomSelectAndInit(function() {
                try {
                    const ts = new TomSelect(selectEl, {plugins:['remove_button'], persist:false, create:false});

                    function updateServiceCards() {
                        // Clear and rebuild cards
                        container.innerHTML = '';
                        const selectedIds = ts.getValue();
                        console.log('updateServiceCards selectedIds:', selectedIds, 'allServices:', allServices);
                        selectedIds.forEach(sid => {
                            const service = allServices.find(s => s.id == sid);
                            console.log('Looking for service', sid, 'found:', service);
                            if (service) {
                                const card = renderServiceCard(service);
                                console.log('Appending card to container:', card, 'container:', container);
                                container.appendChild(card);
                                console.log('Card appended, container now has', container.children.length, 'children');
                                
                                // NOW update room lists for this specific service - card is in DOM
                                updateServiceRoomLists(sid);
                                
                                syncHiddenEntries(sid);
                            }
                        });

                        // Update totals (room lists already updated per-service above)
                        updateTotalsFromHidden();
                    }

                    ts.on('change', function(values){ 
                        console.log('TomSelect change event, values:', values);
                        updateServiceCards(); 
                    });

                    // Preselect services from bookingServicesServer
                    const initialIds = Object.keys(bookingServicesServer || {});
                    console.log('Initial service IDs from server:', initialIds, 'bookingServicesServer:', bookingServicesServer);
                    if (initialIds && initialIds.length) {
                        ts.setValue(initialIds);
                    }
                    // Always call updateServiceCards to render initial services
                    updateServiceCards();

                } catch(e){ console.error('Services init error', e); }
            });
        });

        @if (config('app.debug'))
            console.log('DEBUG bookingServicesServer:', {!! json_encode($bookingServicesServer ?? []) !!});
            console.log('DEBUG assignedRooms:', {!! json_encode($assignedRooms ?? []) !!});
            console.log('DEBUG allServices:', {!! json_encode($services->toArray()) !!});
        @endif
    </script>
@endpush
