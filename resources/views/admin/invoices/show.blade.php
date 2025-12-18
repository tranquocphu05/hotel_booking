@extends('layouts.admin')
@section('admin_content')
    @php
        $booking = $invoice->datPhong;
        $nights = 1;
        if ($booking && $booking->ngay_nhan && $booking->ngay_tra) {
            $nights = \Carbon\Carbon::parse($booking->ngay_nhan)->diffInDays(\Carbon\Carbon::parse($booking->ngay_tra));
        }
        $nights = max(1, $nights);

        // Calculate room total from booking using promotional prices
        $roomTotal = 0;
        if ($booking) {
            if ($invoice->isExtra()) {
                $roomTotal = 0;
            } else {
                $roomTypes = $booking->getRoomTypes();
                foreach ($roomTypes as $rt) {
                    $qty = (int) ($rt['so_luong'] ?? 1);
                    $loaiPhongId = (int) ($rt['loai_phong_id'] ?? 0);
                    $loaiPhong = \App\Models\LoaiPhong::find($loaiPhongId);
                    $unit = 0;
                    if ($loaiPhong) {
                        $unit = $loaiPhong->gia_khuyen_mai ?? ($loaiPhong->gia_co_ban ?? 0);
                    }
                    $roomTotal += $qty * $unit * $nights;
                }
            }
        }
        // Get services
        $services = collect();
        if ($booking) {
            // Load service and room so we can display room number in the table
            $services = \App\Models\BookingService::with(['service', 'phong'])
                ->where('dat_phong_id', $booking->id)
                ->where('invoice_id', $invoice->id)
                ->orderBy('used_at')
                ->get();
        }

        $servicesTotal = $services->reduce(function ($carry, $item) {
            return $carry + ($item->quantity ?? 0) * ($item->unit_price ?? 0);
        }, 0);
        $servicesQty = $services->reduce(function ($carry, $item) {
            return $carry + ($item->quantity ?? 0);
        }, 0);

        // Determine voucher and discount (use explicit invoice giam_gia if present otherwise compute from voucher)
        $voucher = null;
        if ($booking && isset($booking->voucher) && $booking->voucher) {
            $voucher = $booking->voucher;
        }

        $computedVoucherDiscount = 0;
        if (!$invoice->isExtra() && $booking && $voucher) {
            $voucherPercent = floatval($voucher->gia_tri ?? 0);
            $voucherLoaiPhongId = $voucher->loai_phong_id ?? null;
            if ($voucherPercent > 0) {
                $applicableTotal = 0;
                if ($voucherLoaiPhongId) {
                    $roomTypes = $booking->getRoomTypes();
                    foreach ($roomTypes as $rt) {
                        $lpId = $rt['loai_phong_id'] ?? null;
                        if ($lpId && $lpId == $voucherLoaiPhongId) {
                            $loaiPhong = \App\Models\LoaiPhong::find($lpId);
                            if ($loaiPhong) {
                                $unit = $loaiPhong->gia_khuyen_mai ?? ($loaiPhong->gia_co_ban ?? 0);
                                $soLuong = $rt['so_luong'] ?? 1;
                                $applicableTotal += $unit * $soLuong * $nights;
                            }
                        }
                    }
                } else {
                    $applicableTotal = $roomTotal;
                }

                if ($applicableTotal > 0) {
                    if ($voucherPercent <= 100) {
                        $computedVoucherDiscount = intval(round($applicableTotal * ($voucherPercent / 100)));
                    } else {
                        $computedVoucherDiscount = intval(min(round($voucherPercent), $applicableTotal));
                    }
                }
            }
        }

        $discount = $invoice->giam_gia ?? $computedVoucherDiscount;

        $phiPhatSinh = $invoice->phi_phat_sinh ?? 0;

        // Invoice items (detailed lines). If present, use their sum as the authoritative total for display.
        $invoiceItems = collect();
        $itemsTotal = 0;
        if (Schema::hasTable('invoice_items')) {
            $invoiceItems = $invoice->items()->orderBy('created_at')->get();
            $itemsTotal = $invoiceItems->sum('amount');
        }

        // Sum up extra guest fees across all invoices for this booking (if any)
        $extraGuestTotal = 0;
        if (Schema::hasTable('invoice_items') && $booking) {
            $extraGuestTotal = \App\Models\InvoiceItem::where('type', 'extra_guest')
                ->whereHas('invoice', function ($q) use ($booking) {
                    $q->where('dat_phong_id', $booking->id);
                })->sum('amount');
        }

        $calculatedTotal = max(0, $roomTotal - ($discount ?? 0) + ($servicesTotal ?? 0) + ($phiPhatSinh ?? 0));
        // By default show all items, but hide 'extra_guest' on the main invoice
        $visibleInvoiceItems = $invoiceItems;
        if (! $invoice->isExtra()) {
            // For main invoices we do not show 'extra_guest' lines (they belong on EXTRA invoices)
            $visibleInvoiceItems = $invoiceItems->filter(function ($it) {
                return ($it->type ?? '') !== 'extra_guest';
            });
        }
        $visibleItemsTotal = $visibleInvoiceItems->sum('amount');
        $displayTotal = $visibleItemsTotal > 0 ? $visibleItemsTotal : ($invoice->tong_tien ?? $calculatedTotal);
    @endphp

    <div class="py-6 bg-gray-100 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Header with Action Buttons --}}
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Hóa đơn #{{ $invoice->id }}</h1>
                <div class="flex gap-3">
                    <a href="{{ route('admin.invoices.print', $invoice->id) }}" target="_blank"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg flex items-center gap-2 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                            </path>
                        </svg>
                        Print
                    </a>
                    @if ($invoice->trang_thai === 'da_thanh_toan')
                        <button id="open_adjust_modal" type="button" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded-lg flex items-center gap-2 transition">
                            Remove Service
                        </button>
                    @endif
                    @if (!in_array($invoice->trang_thai, ['da_thanh_toan', 'hoan_tien']))
                        <a href="{{ route('admin.invoices.edit', $invoice->id) }}"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-6 rounded-lg flex items-center gap-2 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            Chỉnh sửa
                        </a>
                    @endif
                    @if ($invoice->trang_thai === 'da_thanh_toan' && !$invoice->isExtra())
                        <a href="{{ route('admin.invoices.create_extra', $invoice->id) }}"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg flex items-center gap-2 transition">
                            Add Extra Service
                        </a>
                    @endif

                    <a href="{{ route('admin.invoices.export', $invoice->id) }}"
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg flex items-center gap-2 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Export Excel
                    </a>

                    <a href="{{ route('admin.invoices.index') }}"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-lg transition">
                        Back
                    </a>
                </div>
            </div>

            <!-- Adjustment modal (hidden) -->
            <div id="adjust_modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <h3 class="text-lg font-semibold mb-4">Bớt dịch vụ</h3>
                    <form id="adjust_form" method="POST" action="{{ route('admin.invoices.adjust', $invoice->id) }}">
                        @csrf
                        <div class="grid grid-cols-1 gap-2">
                            <div>
                                <label class="text-sm font-medium">Dịch vụ</label>
                                <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css" rel="stylesheet">
                                <select id="adjust_booking_services" name="booking_services[]" multiple class="w-full border rounded px-2 py-1">
                                    @foreach($bookingServiceOptions ?? [] as $opt)
                                        <option value="{{ $opt['id'] }}" data-remaining="{{ $opt['remaining'] }}" data-used_at="{{ $opt['used_at'] }}" data-phong-id="{{ $opt['phong_id'] ?? '' }}" data-so-phong="{{ $opt['so_phong'] ?? '' }}" data-service-name="{{ $opt['service_name'] }}" data-unit-price="{{ $opt['unit_price'] }}">{{ $opt['service_name'] }} — Phòng: {{ $opt['so_phong'] ?? '-' }} — {{ $opt['used_at_display'] }}</option>
                                    @endforeach
                                </select>
                                <div id="adjust_items_container" class="mt-2 space-y-3"></div>
                            </div> 
                            <div>
                                <label class="text-sm font-medium">Hoàn tiền (tuỳ chọn)</label>
                                <div class="mt-2">
                                    <label class="text-sm">Phương thức hoàn tiền</label>
                                    <select id="adjust_refund_method" name="refund_method" class="w-full border rounded px-2 py-1 mb-2">
                                        <option value="tien_mat">Tiền mặt</option>
                                        <option value="chuyen_khoan">Chuyển khoản</option>
                                
                                    </select>
                                    <div id="adjust_refund_bank_fields" class="space-y-2">
                                        <input id="adjust_refund_account_number" type="text" name="refund_account_number" placeholder="Số tài khoản" class="w-full border rounded px-2 py-1 mb-2">
                                        <input id="adjust_refund_account_name" type="text" name="refund_account_name" placeholder="Tên chủ tài khoản" class="w-full border rounded px-2 py-1 mb-2">
                                        <input id="adjust_refund_bank_name" type="text" name="refund_bank_name" placeholder="Ngân hàng" class="w-full border rounded px-2 py-1">
                                    </div>
                                </div>
                            </div>                           
                            <div>
                                <label class="text-sm font-medium">Ghi chú</label>
                                <input type="text" name="note" class="w-full border rounded px-2 py-1" placeholder="Lý do ví dụ: Khách không dùng">
                            </div>
                            <div class="mt-2">
                                <label class="text-sm font-medium">Tổng bớt</label>
                                <div id="adjust_total_display" class="text-lg font-semibold text-red-600">0 đ</div>
                            </div>
                            <div class="flex items-center justify-between mt-4">
                                <div class="text-sm text-gray-500">&nbsp;</div>
                                <div class="flex items-center gap-2">
                                    <button type="button" id="adjust_cancel_btn" class="px-4 py-2 rounded border">Hủy</button>
                                    <button type="submit" class="px-4 py-2 rounded bg-red-600 text-white">Xác nhận</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Main Invoice Card --}}
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">

                {{-- Invoice Header Section --}}
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white px-8 py-6">
                    <div class="grid grid-cols-3 gap-6 text-center">
                        <div>
                            <p class="text-blue-100 text-sm">Mã hóa đơn</p>
                            <p class="text-2xl font-bold">{{ $invoice->id }}</p>
                        </div>
                        <div>
                            <p class="text-blue-100 text-sm">Ngày tạo</p>
                            <p class="text-2xl font-bold">{{ \Carbon\Carbon::parse($invoice->ngay_tao)->format('d/m/Y') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-blue-100 text-sm">Trạng thái</p>
                            <span
                                class="inline-block mt-1 px-4 py-1 rounded-full text-sm font-semibold {{ $invoice->trang_thai == 'da_thanh_toan' ? 'bg-green-400 text-green-900' : 'bg-yellow-400 text-yellow-900' }}">
                                {{ $invoice->trang_thai == 'da_thanh_toan' ? '✓ Đã thanh toán' : 'Chờ thanh toán' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="p-8">
                    {{-- Room Details --}}
                    @if (!$invoice->isExtra() && $booking)
                        <div class="mb-8">
                            <h3 class="font-bold mb-4">Chi tiết phòng</h3>
                            <div class="border rounded-lg overflow-hidden">
                                <div class="bg-gray-100 px-4 py-2 grid grid-cols-12 text-xs font-semibold text-gray-600">
                                    <div class="col-span-3">Loại phòng</div>
                                    <div class="col-span-2 text-center">Số lượng</div>
                                    <div class="col-span-2 text-center">Giá/đêm</div>
                                    <div class="col-span-2 text-center">Số đêm</div>
                                    <div class="col-span-3 text-right">Thành tiền</div>
                                </div>
                                @foreach ($booking->getRoomTypes() as $rt)
                                    @php
                                        $lp = \App\Models\LoaiPhong::find($rt['loai_phong_id']);
                                        $giaCoBan = $lp ? $lp->gia_co_ban : 0;
                                        $giaKhuyenMai = $lp ? $lp->gia_khuyen_mai : null;
                                        $giaPhong = $giaKhuyenMai ?? $giaCoBan;
                                        $thanhTien = $giaPhong * $rt['so_luong'] * $nights;
                                    @endphp
                                    <div class="px-6 py-4 grid grid-cols-12 gap-4 border-t text-sm">
                                        <div class="col-span-4">{{ $lp->ten_loai ?? 'N/A' }}</div>
                                        <div class="col-span-2 text-center">{{ $rt['so_luong'] }} phòng</div>
                                        <div class="col-span-2 text-center">
                                            @if ($giaKhuyenMai && $giaKhuyenMai < $giaCoBan)
                                                <div class="flex flex-col items-center">
                                                    <span
                                                        class="text-gray-400 line-through text-xs">{{ number_format($giaCoBan, 0, ',', '.') }}
                                                        ₫</span>
                                                    <span
                                                        class="text-red-600 font-semibold">{{ number_format($giaKhuyenMai, 0, ',', '.') }}
                                                        ₫</span>
                                                </div>
                                            @else
                                                <span>{{ number_format($giaPhong, 0, ',', '.') }} ₫</span>
                                            @endif
                                        </div>
                                        <div class="col-span-2 text-center">{{ $nights }} đêm</div>
                                        <div class="col-span-2 text-right font-bold">
                                            {{ number_format($thanhTien, 0, ',', '.') }} ₫</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    {{-- Services Section --}}
                    <div class="mb-8">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b-2 border-blue-500">Danh sách dịch vụ
                        </h3>

                        @if ($services->isEmpty())
                            <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg py-8 text-center">
                                <p class="text-gray-500 text-sm">Không có dịch vụ kèm theo</p>
                            </div>

                                {{-- Invoice Items Section --}}
                                @if(isset($invoiceItems) && $invoiceItems->isNotEmpty())
                                    <div class="mb-8">
                                        <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b-2 border-blue-500">Chi tiết phát sinh</h3>
                                        <div class="overflow-x-auto">
                                            <table class="w-full border rounded-lg overflow-hidden">
                                                <thead>
                                                    <tr class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                                                        <th class="px-6 py-4 text-left font-semibold">Mô tả</th>
                                                        <th class="px-6 py-4 text-center font-semibold">Loại</th>
                                                        <th class="px-6 py-4 text-center font-semibold">Đơn giá</th>
                                                        <th class="px-6 py-4 text-right font-semibold">Thành tiền</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200">
                                                    @foreach($invoiceItems as $it)
                                                        <tr class="hover:bg-blue-50 transition">
                                                            <td class="px-6 py-4 text-gray-900">{{ $it->description }}</td>
                                                            <td class="px-6 py-4 text-center text-gray-700">{{ $it->type }}</td>
                                                            <td class="px-6 py-4 text-right text-gray-700">{{ number_format($it->unit_price,0,',','.') }} đ</td>
                                                            <td class="px-6 py-4 text-right font-semibold text-gray-900">{{ number_format($it->amount,0,',','.') }} đ</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            <div class="text-right mt-3 font-bold">Tổng phát sinh: {{ number_format($itemsTotal,0,',','.') }} đ</div>
                                        </div>
                                    </div>
                                @endif
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full border rounded-lg overflow-hidden">
                                    <thead>
                                        <tr class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                                            <th class="px-6 py-4 text-left font-semibold">DỊCH VỤ</th>
                                            <th class="px-6 py-4 text-center font-semibold">PHÒNG</th>
                                            <th class="px-6 py-4 text-center font-semibold">NGÀY DÙNG</th>
                                            <th class="px-6 py-4 text-right font-semibold">SỐ LƯỢNG</th>
                                            <th class="px-6 py-4 text-right font-semibold">ĐƠN GIÁ</th>
                                            <th class="px-6 py-4 text-right font-semibold">THÀNH TIỀN</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach ($services as $s)
                                            @php
                                                $svc = $s->service;
                                                $name = $svc ? $svc->name ?? 'N/A' : $s->service_name ?? 'N/A';
                                                $usedAt = $s->used_at ? date('d/m/Y', strtotime($s->used_at)) : '-';
                                                $qty = $s->quantity ?? 0;
                                                $unitPrice = $s->unit_price ?? 0;
                                                $subtotal = $qty * $unitPrice;
                                            @endphp
                                            <tr class="hover:bg-blue-50 transition">
                                                <td class="px-6 py-4 text-gray-900">
                                                    <div>{{ $name }}</div>
                                                    @if($s->note)
                                                        <div class="text-xs text-gray-500 italic mt-1">{{ $s->note }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 text-center text-gray-700">
                                                    {{ $s->phong ? $s->phong->so_phong ?? $s->phong->id : $s->phong_id ?? '-' }}
                                                </td>
                                                <td class="px-6 py-4 text-center text-gray-700">{{ $usedAt }}</td>
                                                <td class="px-6 py-4 text-right text-gray-700">{{ $qty }}</td>
                                                <td class="px-6 py-4 text-right text-gray-700">
                                                    {{ number_format($unitPrice, 0, ',', '.') }} đ</td>
                                                <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                                    {{ number_format($subtotal, 0, ',', '.') }} đ</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- Extra guest details for main invoice (only show when not EXTRA and when extra_guest items exist) --}}
                    @if(! $invoice->isExtra())
                        @php
                            $extraGuestItems = isset($invoiceItems) ? $invoiceItems->where('type', 'extra_guest') : collect();
                            $extraGuestItemsTotal = $extraGuestItems->sum('amount');
                        @endphp

                        @if($extraGuestItems->isNotEmpty())
                            <div class="mb-8">
                                <h3 class="font-bold mb-4">Chi tiết phí thêm người</h3>
                                <div class="border rounded-lg overflow-hidden">
                                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 font-semibold">
                                        <div class="grid grid-cols-4 gap-4 text-sm">
                                            <div>Mô tả</div>
                                            <div class="text-center">Loại</div>
                                            <div class="text-center">Đơn giá</div>
                                            <div class="text-right">Thành tiền</div>
                                        </div>
                                    </div>
                                    <div class="p-6 bg-white">
                                        <div class="space-y-4">
                                            @foreach($extraGuestItems as $it)
                                                <div class="grid grid-cols-4 gap-4 items-center text-sm">
                                                    <div>{{ $it->description ?? $it->name ?? 'Phát sinh' }}</div>
                                                    <div class="text-center text-gray-600">{{ $it->type ?? '' }}</div>
                                                    <div class="text-center">{{ number_format((float)($it->unit_price ?? $it->amount), 0, ',', '.') }} đ</div>
                                                    <div class="text-right font-semibold">{{ number_format((float)$it->amount, 0, ',', '.') }} đ</div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="mt-6 text-right font-bold text-lg">Tổng phát sinh: {{ number_format($extraGuestItemsTotal, 0, ',', '.') }} đ</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    <div class="grid md:grid-cols-1 gap-6">
                        <div class="bg-white border rounded-xl p-5 shadow-sm space-y-2">
                            <h3 class="text-base font-bold text-gray-900 mb-4">Tóm tắt thanh toán</h3>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Tiền phòng</span>
                                <span
                                    class="font-semibold text-gray-900">{{ number_format($roomTotal, 0, ',', '.') }}₫</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Dịch vụ</span>
                                <span
                                    class="font-semibold text-gray-900">{{ number_format($servicesTotal, 0, ',', '.') }}₫</span>
                            </div>

                            @php
                                $invoicePhiThemNguoi = $invoice->phi_them_nguoi ?? 0;
                                // If invoice does not have phi_them_nguoi, fall back to computed extraGuestTotal
                                $displayPhiThemNguoi = $invoicePhiThemNguoi > 0 ? $invoicePhiThemNguoi : ($extraGuestTotal ?? 0);
                            @endphp

                            @if ($displayPhiThemNguoi > 0)
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Phí thêm người</span>
                                    <span class="font-semibold text-gray-900">{{ number_format($displayPhiThemNguoi, 0, ',', '.') }}₫</span>
                                </div>
                            @endif

                            @if ($discount > 0)
                                <div class="flex justify-between text-sm text-red-600">
                                    <span>Giảm giá @if ($voucher)
                                            ({{ $voucher->ma_voucher }} - {{ $voucher->gia_tri }}%)
                                        @endif
                                    </span>
                                    <span class="font-semibold">-{{ number_format($discount, 0, ',', '.') }}₫</span>
                                </div>
                            @endif

                            <div class="pt-4 border-t border-gray-200 space-y-4">                                    @if($invoice->ghi_chu)
                                        <div class="text-sm text-gray-700 italic">Ghi chú: {{ $invoice->ghi_chu }}</div>
                                    @endif                                <div class="grid grid-cols-3 items-center gap-4">
                                    <h3 class="text-base font-bold text-gray-900">
                                        Chi tiết hóa đơn
                                    </h3>

                                    <p class="text-sm text-gray-600 text-center">
                                        Phương thức:
                                        <span class="font-semibold text-gray-900">
                                            {{ $invoice->phuong_thuc ? strtoupper(str_replace('_', ' ', $invoice->phuong_thuc)) : 'N/A' }}
                                        </span>
                                    </p>

                                    <p class="text-sm text-gray-600 text-right">
                                        Ngày tạo:
                                        <span class="font-semibold text-gray-900">
                                            {{ $invoice->ngay_tao ? \Carbon\Carbon::parse($invoice->ngay_tao)->format('d/m/Y H:i') : 'N/A' }}
                                        </span>
                                    </p>
                                </div>

                                <div class="flex items-center justify-between">
                                    <p class="text-sm text-gray-500 uppercase font-semibold">
                                        Trạng thái thanh toán
                                    </p>

                                    <span
                                        class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold
        @if ($invoice->trang_thai === 'da_thanh_toan') bg-green-100 text-green-800
        @elseif($invoice->trang_thai === 'hoan_tien') bg-red-100 text-red-800
        @else bg-yellow-100 text-yellow-800 @endif">
                                        {{ $invoice->trang_thai === 'da_thanh_toan'
                                            ? 'Đã thanh toán'
                                            : ($invoice->trang_thai === 'hoan_tien'
                                                ? 'Hoàn tiền'
                                                : 'Chờ thanh toán') }}
                                    </span>
                                </div>
                                @if ($invoice->trang_thai === 'cho_thanh_toan')
                                    <form action="{{ route('admin.invoices.update', $invoice->id) }}" method="POST"
                                        class="pt-3 border-t space-y-2 no-print">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="trang_thai" value="da_thanh_toan">
                                        <button type="submit"
                                            class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                                            Đánh dấu đã thanh toán
                                        </button>
                                        <div class="text-xs text-gray-500 text-center">
                                            Áp dụng cho cả hóa đơn phát sinh (không ảnh hưởng hóa đơn chính).
                                        </div>

                                        
                                        {{-- Phụ phí thiệt hại tài sản --}}
                                        @php
                                            $phiPhatSinh = $invoice->phi_phat_sinh ?? 0;
                                            $lyDoThietHai = '';
                                            $loaiThietHai = '';

                                            // Extract thông tin thiệt hại từ ghi_chu_checkout
                                            if ($booking && $booking->ghi_chu_checkout) {
                                                // Extract lý do từ format [LY_DO_PHI: ...]
                                                if (
                                                    preg_match(
                                                        '/\[LY_DO_PHI:\s*(.+?)\]/',
                                                        $booking->ghi_chu_checkout,
                                                        $matches,
                                                    )
                                                ) {
                                                    $lyDoThietHai = trim($matches[1]);
                                                }

                                                // Extract danh mục thiệt hại từ format === THIỆT HẠI TÀI SẢN ===
                                                if (
                                                    preg_match(
                                                        '/Danh mục:\s*(.+?)(?:\n|$)/',
                                                        $booking->ghi_chu_checkout,
                                                        $matches,
                                                    )
                                                ) {
                                                    $loaiThietHai = trim($matches[1]);
                                                }
                                            }
                                        @endphp

                                        @php
                                            // Only treat phi_phat_sinh as a "damage" display when there is
                                            // explicit damage information in the booking notes OR when an
                                            // invoice item of type 'damage' exists. Otherwise phi_phat_sinh
                                            // may represent other additional charges (eg. extra_guest).
                                            $hasDamageNotes = ($loaiThietHai || $lyDoThietHai);
                                            $hasDamageItem = isset($invoiceItems) && $invoiceItems->contains(function ($it) {
                                                return in_array($it->type, ['damage', 'thiet_hai']);
                                            });
                                        @endphp

                                        @if ($phiPhatSinh > 0 && ($hasDamageNotes || $hasDamageItem))
                                            <div class="border-t border-gray-200 my-2"></div>
                                            <div class="bg-red-50 border-l-4 border-red-500 rounded p-4 mb-2">
                                                <div class="flex justify-between items-start mb-2">
                                                    <div class="flex-1">
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <svg class="w-5 h-5 text-red-600" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                            </svg>
                                                            <span class="text-red-800 font-semibold">Phụ phí thiệt hại tài
                                                                sản</span>
                                                        </div>
                                                        @if ($loaiThietHai)
                                                            <p class="text-sm text-red-700 mb-1">
                                                                <span class="font-medium">Danh mục:</span>
                                                                {{ $loaiThietHai }}
                                                            </p>
                                                        @endif
                                                        @if ($lyDoThietHai)
                                                            <p class="text-sm text-red-700">
                                                                <span class="font-medium">Mô tả:</span>
                                                                {{ $lyDoThietHai }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                    <span
                                                        class="text-red-600 font-bold text-lg ml-4">{{ number_format($phiPhatSinh, 0, ',', '.') }}
                                                        đ</span>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="border-t-2 border-gray-300"></div>

                                        {{-- Total --}}
                                        @php
                                            // Tính tổng cuối cùng: (Tiền phòng - Giảm giá) + Tiền dịch vụ + Phụ phí
                                            $tienPhong = $invoice->tien_phong ?? $roomTotal;
                                            $giamGia = $invoice->giam_gia ?? 0;
                                            $tienDichVu = $invoice->tien_dich_vu ?? $servicesTotal;
                                            $phiPhatSinh = $invoice->phi_phat_sinh ?? 0;
                                            $tongCuoiCung =
                                                $invoice->tong_tien ??
                                                max(0, $tienPhong - $giamGia + $tienDichVu + $phiPhatSinh);
                                        @endphp
                                        @if ($invoice->tong_tien && $invoice->tong_tien != $calculatedTotal)
                                            <p class="text-xs text-gray-500 mb-2">
                                                (Đã lưu trong hệ thống: {{ number_format((float) $invoice->tong_tien, 0, ',', '.') }}₫)
                                            </p>
                                        @endif
                                        <div class="flex justify-between items-center py-3 bg-blue-600 -mx-6 px-6 -mb-6">
                                            <span class="font-bold text-white text-lg">TỔNG THANH TOÁN</span>
                                            <span
                                                class="text-white text-3xl font-bold">{{ number_format($tongCuoiCung, 0, ',', '.') }}
                                                đ</span>
                                        </div>
                            </div>
                            {{-- Payment method --}}
                            
                                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Phương thức thanh toán</span>
                                        <span class="text-sm font-semibold text-gray-900">{{ $invoice->phuong_thuc ?? 'N/A' }}</span>
                                    </div>
                                </div>
                        
                    </div>
                @else
                    {{-- Extra invoice summary: show invoice items (extra_guest, services, adjustments) and total payment --}}
                    <div class="mt-8">
                        <div class="bg-white rounded-lg border-2 border-gray-200 shadow-sm overflow-hidden">
                            <div class="p-6 space-y-4">
                                @php
                                    $itemsQty = $visibleInvoiceItems->sum('quantity');
                                @endphp
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-700 font-medium">Tổng số lượng mục</span>
                                    <span class="text-gray-900 font-semibold text-lg">{{ $itemsQty }}</span>
                                </div>

                                @php
                                    // For EXTRA invoices, prefer invoice_items as authoritative line items
                                    $tienDichVu = $invoice->tien_dich_vu ?? $servicesTotal;

                                    // Gather extra_guest items and invoice-level phi_them_nguoi
                                    $extraGuestItems = isset($invoiceItems) ? $invoiceItems->where('type', 'extra_guest') : collect();
                                    $extraGuestItemsTotal = $extraGuestItems->sum('amount');
                                    $invoicePhiThemNguoi = $invoice->phi_them_nguoi ?? 0;
                                    $displayPhiThemNguoi = $invoicePhiThemNguoi > 0 ? $invoicePhiThemNguoi : ($extraGuestItemsTotal ?? 0);

                                    // If there are no invoice_items but we have an invoice-level phi_them_nguoi,
                                    // treat that as the effective items total so the amount is visible on the invoice.
                                    $itemsTotalEffective = $itemsTotal;
                                    if (empty($itemsTotalEffective) && $displayPhiThemNguoi > 0) {
                                        $itemsTotalEffective = $displayPhiThemNguoi;
                                    }

                                    $tongCuoiCung = $invoice->tong_tien ?? max(0, $itemsTotalEffective ?: $tienDichVu);
                                @endphp

                                @if($visibleInvoiceItems->isNotEmpty())
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm text-gray-700 table-auto">
                                            <thead class="bg-gray-100 text-xs text-gray-800 uppercase font-semibold">
                                                <tr>
                                                    <th class="px-3 py-2 text-left">Mục</th>
                                                    <th class="px-3 py-2 text-right">SL</th>
                                                    <th class="px-3 py-2 text-right">Đơn giá</th>
                                                    <th class="px-3 py-2 text-right">Thành tiền</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($visibleInvoiceItems as $it)
                                                    <tr>
                                                        <td class="px-3 py-2">{{ $it->description ?? ($it->type ?? 'item') }}</td>
                                                        <td class="px-3 py-2 text-right">{{ $it->quantity }}</td>
                                                        <td class="px-3 py-2 text-right">{{ number_format($it->unit_price ?? 0, 0, ',', '.') }} đ</td>
                                                        <td class="px-3 py-2 text-right font-medium">{{ number_format($it->amount ?? 0, 0, ',', '.') }} đ</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                {{-- Show extra guest details for EXTRA invoices (either itemized or invoice-level) --}}
                                @if($extraGuestItems->isNotEmpty())
                                    <div class="mt-4">
                                        <h4 class="font-bold mb-2">Chi tiết phí thêm người</h4>
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-sm text-gray-700 table-auto">
                                                <thead class="bg-gray-100 text-xs text-gray-800 uppercase font-semibold">
                                                    <tr>
                                                        <th class="px-3 py-2 text-left">Mục</th>
                                                        <th class="px-3 py-2 text-right">SL</th>
                                                        <th class="px-3 py-2 text-right">Đơn giá</th>
                                                        <th class="px-3 py-2 text-right">Thành tiền</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100">
                                                    @foreach($extraGuestItems as $it)
                                                        <tr>
                                                            <td class="px-3 py-2">{{ $it->description ?? ($it->type ?? 'phí thêm người') }}</td>
                                                            <td class="px-3 py-2 text-right">{{ $it->quantity ?? 1 }}</td>
                                                            <td class="px-3 py-2 text-right">{{ number_format($it->unit_price ?? $it->amount, 0, ',', '.') }} đ</td>
                                                            <td class="px-3 py-2 text-right font-medium">{{ number_format($it->amount ?? 0, 0, ',', '.') }} đ</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            <div class="text-right mt-3 font-bold">Tổng phí thêm người: {{ number_format($extraGuestItemsTotal,0,',','.') }} đ</div>
                                        </div>
                                    </div>
                                @elseif($displayPhiThemNguoi > 0)
                                    <div class="mt-4">
                                        <h4 class="font-bold mb-2">Chi tiết phí thêm người</h4>
                                        <div class="p-3 border rounded bg-white">
                                            <div class="flex justify-between items-center">
                                                <div class="text-sm text-gray-700">Phí thêm người</div>
                                                <div class="font-semibold text-gray-900">{{ number_format($displayPhiThemNguoi,0,',','.') }} đ</div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="border-t-2 border-gray-300"></div>

                                <div class="flex justify-between items-center py-3 bg-blue-600 -mx-6 px-6 -mb-6">
                                    <span class="font-bold text-white text-lg">TỔNG THANH TOÁN</span>
                                    <span
                                        class="text-white text-3xl font-bold">{{ number_format($tongCuoiCung, 0, ',', '.') }}
                                        đ</span>
                                </div>
                            </div>

                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
<script>
    (function(){
        // Protect modal JS from uncaught exceptions so the rest of the page keeps working
        function safe(fn, ctx){
            return function(){
                try { return fn.apply(ctx || this, arguments); }
                catch (e) { console.error('Invoice adjust modal error', e); }
            };
        }

        // Small currency formatter used by this modal (keeps format like "0 đ")
        function formatCurrency(amount) {
            try {
                const v = Number(amount) || 0;
                return v.toLocaleString('vi-VN') + ' đ';
            } catch (e) {
                return (amount || 0) + ' đ';
            }
        }

        const openBtn = document.getElementById('open_adjust_modal');
        const modal = document.getElementById('adjust_modal');
        const cancelBtn = document.getElementById('adjust_cancel_btn');
        const bookingServicesSelect = document.getElementById('adjust_booking_services');
        const itemsContainer = document.getElementById('adjust_items_container');
        const refundCb = document.getElementById('adjust_create_refund_cb');
        const refundFields = document.getElementById('refund_fields');

        function openModal(){ modal.classList.remove('hidden'); }
        function closeModal(){ modal.classList.add('hidden'); }

        if (openBtn) openBtn.addEventListener('click', openModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        // TomSelect loader and init
        function loadTomSelectAndInit(cb) {
            if (window.TomSelect) return cb();
            if (!document.querySelector('link[href*="tom-select"]')) {
                const link = document.createElement('link');
                link.href = 'https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css';
                link.rel = 'stylesheet';
                document.head.appendChild(link);
            }
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js';
            s.onload = cb;
            document.body.appendChild(s);
        }

        function findOptionByValue(val) {
            if (!bookingServicesSelect) return null;
            return bookingServicesSelect.querySelector('option[value="' + val + '"]');
        }

        function createAdjustRow(id, opt) {
            const rem = parseInt(opt.dataset.remaining || 0, 10);
            const used_at = opt.dataset.used_at || '';
            const phongId = opt.dataset.phongId || '';
            const soPhong = opt.dataset.soPhong || '';
            const svcName = opt.dataset.serviceName || '';
            const unitPrice = parseFloat(opt.dataset.unitPrice || 0);

            const wrapper = document.createElement('div');
            wrapper.className = 'adjust-item border rounded p-3 bg-gray-50';
            wrapper.setAttribute('data-id', id);

            // Header
            const header = document.createElement('div');
            header.className = 'flex justify-between items-center mb-1';
            const title = document.createElement('div');
            title.className = 'text-sm font-semibold';
            title.innerHTML = svcName + ' <span class="text-xs text-gray-500"> — Phòng: ' + soPhong + '</span> <span class="text-xs text-gray-500"> — Ngày: ' + used_at + '</span>';
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'remove-adjust-item px-2 py-1 text-sm text-red-600';
            removeBtn.textContent = 'Xoá';
            header.appendChild(title);
            header.appendChild(removeBtn);
            wrapper.appendChild(header);

            // Hidden booking_service_id
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'adjustments[' + id + '][booking_service_id]';
            hidden.value = id;
            wrapper.appendChild(hidden);

            // Grid container
            const grid = document.createElement('div');
            grid.className = 'grid grid-cols-2 gap-2 mb-1';

            // ID column
            const idCol = document.createElement('div');
            const idLabel = document.createElement('label');
            idLabel.className = 'text-xs';
            idLabel.textContent = 'ID';
            const idInput = document.createElement('input');
            idInput.type = 'text';
            idInput.className = 'w-full border rounded px-2 py-1 bg-gray-100';
            idInput.readOnly = true;
            idInput.name = 'adjustments[' + id + '][phong_id]';
            idInput.value = phongId;
            idCol.appendChild(idLabel);
            idCol.appendChild(idInput);
            grid.appendChild(idCol);

            // Quantity column
            const qtyCol = document.createElement('div');
            const qtyLabel = document.createElement('label');
            qtyLabel.className = 'text-xs';
            qtyLabel.textContent = 'Số lượng';
            const qtyInput = document.createElement('input');
            qtyInput.type = 'number';
            qtyInput.min = 1;
            qtyInput.max = rem;
            qtyInput.value = rem > 0 ? 1 : 0;
            qtyInput.name = 'adjustments[' + id + '][quantity]';
            qtyInput.className = 'w-full border rounded px-2 py-1 adjust-qty';
            qtyInput.dataset.max = rem;
            qtyInput.dataset.unitPrice = unitPrice;
            const hint = document.createElement('div');
            hint.className = 'text-xs text-gray-500 mt-1';
            hint.textContent = 'Có thể bớt tối đa: ' + rem;
            qtyCol.appendChild(qtyLabel);
            qtyCol.appendChild(qtyInput);
            qtyCol.appendChild(hint);
            grid.appendChild(qtyCol);

            wrapper.appendChild(grid);

            // Subtotal display
            const subtotalWrap = document.createElement('div');
            subtotalWrap.className = 'text-right text-sm text-gray-600';
            
            wrapper.appendChild(subtotalWrap);

            // Remove handler
            removeBtn.addEventListener('click', safe(function(){
                try {
                    if (window.TomSelect && bookingServicesSelect && bookingServicesSelect._tom_select) {
                        bookingServicesSelect._tom_select.removeItem(id);
                    } else {
                        const opt = findOptionByValue(id);
                        if (opt) opt.selected = false;
                    }
                    wrapper.remove();
                    updateAdjustTotal();
                } catch (err) { console.error('remove handler error', err); }
            }));

            // qty change handler
            qtyInput.addEventListener('input', safe(function(){
                try {
                    let val = parseInt(qtyInput.value || 0, 10);
                    const max = parseInt(qtyInput.dataset.max || 0, 10);
                    if (isNaN(val) || val < 1) val = 1;
                    if (val > max) val = max;
                    qtyInput.value = val;
                    const unit = parseFloat(qtyInput.dataset.unitPrice || unitPrice || 0);
                    const subtotalEl = wrapper.querySelector('.line-subtotal');
                    if (subtotalEl) subtotalEl.textContent = formatCurrency(unit * val);
                    updateAdjustTotal();
                } catch (err) { console.error('qty change handler error', err); }
            }));

            // ensure total updates when row is created
            updateAdjustTotal();

            return wrapper;
        }

        function updateSelectedItems(values) {
            try {
                // Normalize values to an array of string ids
                let sel = [];
                if (Array.isArray(values)) {
                    sel = values.map(v => {
                        if (v && typeof v === 'object' && 'value' in v) return String(v.value);
                        return String(v || '');
                    }).map(s => s.trim()).filter(Boolean);
                } else if (values) {
                    sel = String(values).split(',').map(s => s.trim()).filter(Boolean);
                } else {
                    sel = [];
                }

                // Add new
                sel.forEach(id => {
                    if (!itemsContainer.querySelector('.adjust-item[data-id="' + id + '"]')) {
                        const opt = findOptionByValue(id);
                        if (!opt) return;
                        const row = createAdjustRow(id, opt);
                        itemsContainer.appendChild(row);
                    }
                });

                // Remove unselected
                const existing = Array.from(itemsContainer.querySelectorAll('.adjust-item'));
                existing.forEach(node => {
                    const id = node.getAttribute('data-id');
                    if (sel.indexOf(id) === -1) node.remove();
                });

                // update total after adding/removing rows
                updateAdjustTotal();
            } catch (err) { console.error('updateSelectedItems error', err); }
        }

        // Recompute the overall total of removed services and update UI
        function updateAdjustTotal() {
            try {
                const items = Array.from(itemsContainer.querySelectorAll('.adjust-item'));
                let total = 0;
                items.forEach(it => {
                    try {
                        const qtyEl = it.querySelector('.adjust-qty');
                        const qty = parseInt(qtyEl ? qtyEl.value || 0 : 0, 10);
                        let unit = NaN;
                        if (qtyEl && qtyEl.dataset && qtyEl.dataset.unitPrice) unit = parseFloat(qtyEl.dataset.unitPrice || NaN);

                        // Fallback to option dataset
                        if ((isNaN(unit) || unit === 0) && bookingServicesSelect) {
                            const bsId = it.getAttribute('data-id');
                            if (bsId) {
                                const opt = bookingServicesSelect.querySelector('option[value="' + bsId + '"]');
                                if (opt) unit = parseFloat(opt.dataset.unitPrice || 0);
                            }
                        }

                        if (!isNaN(qty) && !isNaN(unit)) total += qty * unit;
                    } catch (inner) { console.error('line total calc error', inner); }
                });
                const display = document.getElementById('adjust_total_display');
                if (display) display.textContent = formatCurrency(total);
            } catch (err) { console.error('updateAdjustTotal error', err); }
        }

        if (bookingServicesSelect) {
            loadTomSelectAndInit(function(){
                try {
                    const ts = new TomSelect(bookingServicesSelect, { plugins:['remove_button'], persist:false, create:false, onChange: function(vals){ updateSelectedItems(vals); }});
                    // keep reference for remove convenience
                    bookingServicesSelect._tom_select = ts;
                    // initialize rows for any options that are already selected
                    const initial = Array.from(bookingServicesSelect.selectedOptions).map(o => o.value);
                    if (initial.length) updateSelectedItems(initial);
                } catch (e) {
                    // fallback: bind native change
                    bookingServicesSelect.addEventListener('change', function(){
                        const values = Array.from(bookingServicesSelect.selectedOptions).map(o => o.value);
                        updateSelectedItems(values);
                    });
                }
            });
        }

        if (refundCb) {
            refundCb.addEventListener('change', function(){
                if (refundCb.checked) refundFields.classList.remove('hidden'); else refundFields.classList.add('hidden');
            });
        }

        // Show/hide bank/account inputs based on refund method (hide for tiền mặt)
        const refundMethodEl = document.getElementById('adjust_refund_method');
        const refundBankFields = document.getElementById('adjust_refund_bank_fields');
        function updateRefundBankFields() {
            if (!refundMethodEl || !refundBankFields) return;
            const inputs = refundBankFields.querySelectorAll('input');
            if (refundMethodEl.value === 'tien_mat') {
                refundBankFields.classList.add('hidden');
                // clear bank inputs when hidden and remove required flag
                inputs.forEach(i => { i.value = ''; i.removeAttribute('required'); });
            } else {
                refundBankFields.classList.remove('hidden');
                // If method is bank transfer, mark bank fields required
                if (refundMethodEl.value === 'chuyen_khoan') {
                    inputs.forEach(i => i.setAttribute('required', 'required'));
                } else {
                    inputs.forEach(i => i.removeAttribute('required'));
                }
            }
        }
        if (refundMethodEl) {
            refundMethodEl.addEventListener('change', updateRefundBankFields);
            // init on load
            updateRefundBankFields();
        }

        // form validation for batch items
        const form = document.getElementById('adjust_form');
        if (form) {
            form.addEventListener('submit', function(e){
                // If a refund method is selected, signal the server to create RefundService rows
                try {
                    if (refundMethodEl && refundMethodEl.value) {
                        let createRefundInput = form.querySelector('input[name="create_refund"]');
                        if (!createRefundInput) {
                            createRefundInput = document.createElement('input');
                            createRefundInput.type = 'hidden';
                            createRefundInput.name = 'create_refund';
                            createRefundInput.value = '1';
                            form.appendChild(createRefundInput);
                        } else {
                            createRefundInput.value = '1';
                        }
                    }
                } catch (err) { console.error('set create_refund failed', err); }

                // If refund method is bank transfer, ensure bank details are provided client-side
                try {
                    if (refundMethodEl && refundMethodEl.value === 'chuyen_khoan') {
                        const acct = document.getElementById('adjust_refund_account_number');
                        const name = document.getElementById('adjust_refund_account_name');
                        const bank = document.getElementById('adjust_refund_bank_name');
                        const missing = [];
                        if (!acct || !acct.value.trim()) missing.push('Số tài khoản');
                        if (!name || !name.value.trim()) missing.push('Tên chủ tài khoản');
                        if (!bank || !bank.value.trim()) missing.push('Ngân hàng');
                        if (missing.length) {
                            e.preventDefault();
                            alert('Vui lòng cung cấp: ' + missing.join(', ') + ' khi chọn phương thức chuyển khoản.');
                            return;
                        }
                    }
                } catch (err) { console.error('refund bank client validation error', err); }

                // Debug: log adjustments payload in console so admins can copy it when reporting issues
                try {
                    const debugAdjust = Array.from(itemsContainer.querySelectorAll('.adjust-item')).map(it => ({
                        booking_service_id: it.getAttribute('data-id'),
                        quantity: (it.querySelector('.adjust-qty') || {}).value || 0
                    }));
                    console.debug('Submitting adjustments', debugAdjust);
                } catch (err) { /* ignore */ }

                const items = Array.from(itemsContainer.querySelectorAll('.adjust-item'));
                if (items.length === 0) { e.preventDefault(); alert('Vui lòng chọn ít nhất một dịch vụ để bớt.'); return; }
                for (const it of items) {
                    const qtyEl = it.querySelector('.adjust-qty');
                    if (!qtyEl) { e.preventDefault(); alert('Số lượng không hợp lệ.'); return; }
                    const val = parseInt(qtyEl.value || 0, 10);
                    const max = parseInt(qtyEl.dataset.max || 0, 10);
                    if (val <= 0 || val > max) { e.preventDefault(); alert('Số lượng không hợp lệ hoặc vượt quá khả dụng (' + max + ').'); return; }
                }
            });
        }
    })();
</script>

@endsection
