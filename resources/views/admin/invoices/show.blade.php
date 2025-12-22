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
            // For EXTRA invoices: only show services for this invoice
            // For PREPAID invoices: show all booking services (invoice_id NULL or matches main invoice)
            if ($invoice->invoice_type === 'EXTRA') {
                $services = \App\Models\BookingService::with(['service', 'phong'])
                    ->where('dat_phong_id', $booking->id)
                    ->where('invoice_id', $invoice->id)
                    ->orderBy('used_at')
                    ->get();
            } else {
                // PREPAID: show all booking services
                $services = \App\Models\BookingService::with(['service', 'phong'])
                    ->where('dat_phong_id', $booking->id)
                    ->where(function($q) use ($invoice) {
                        $q->whereNull('invoice_id')->orWhere('invoice_id', $invoice->id);
                    })
                    ->orderBy('used_at')
                    ->get();
            }
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
                <div>
                    <h1 class="text-3xl font-bold {{ $invoice->isRefund() ? 'text-red-600' : 'text-gray-900' }}">
                        @if($invoice->isRefund())
                            Hóa đơn hoàn tiền #{{ $invoice->id }}
                        @else
                            Hóa đơn #{{ $invoice->id }}
                        @endif
                    </h1>
                    @if($invoice->isRefund() && $invoice->originalInvoice)
                        <p class="text-sm text-gray-600 mt-1">
                            Hoàn tiền cho hóa đơn gốc: 
                            <a href="{{ route('admin.invoices.show', $invoice->originalInvoice->id) }}" class="text-blue-600 hover:underline">
                                Hóa đơn #{{ $invoice->originalInvoice->id }}
                            </a>
                        </p>
                    @endif
                </div>
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
                    @if ($invoice->trang_thai === 'da_thanh_toan' && !$invoice->isRefund())
                        @php
                            $hasRefundInvoice = \App\Models\Invoice::where('original_invoice_id', $invoice->id)
                                ->where('invoice_type', 'REFUND')
                                ->exists();
                        @endphp
                        {{-- @if (!$hasRefundInvoice)
                            <button id="open_adjust_modal" type="button" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded-lg flex items-center gap-2 transition">
                                Remove Service
                            </button>
                        @endif --}}
                        <button id="open_refund_modal" type="button" class="bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-6 rounded-lg flex items-center gap-2 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Tạo hóa đơn hoàn tiền
                        </button>
                    @endif
                    @if ($invoice->isRefund() && $invoice->originalInvoice)
                        <a href="{{ route('admin.invoices.show', $invoice->originalInvoice->id) }}" 
                           class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-6 rounded-lg flex items-center gap-2 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Xem hóa đơn gốc #{{ $invoice->originalInvoice->id }}
                        </a>
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

                    {{-- Export Excel button tạm ẩn theo yêu cầu --}}

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
                    <form id="adjust_form" method="POST" action="{{ route('admin.invoices.adjust', $invoice->id) }}" onsubmit="console.log('Form onsubmit triggered');">
                        @csrf
                        @method('POST')
                        <div class="grid grid-cols-1 gap-2">
                            <div>
                                <label class="text-sm font-medium">Dịch vụ</label>
                                <select id="adjust_booking_services" class="w-full border rounded px-2 py-1" style="padding: 8px;">
                                    <option value="">-- Chọn dịch vụ để bớt --</option>
                                    @if(isset($bookingServiceOptions) && count($bookingServiceOptions) > 0)
                                        @foreach($bookingServiceOptions as $opt)
                                            <option value="{{ $opt['id'] }}" data-remaining="{{ $opt['remaining'] }}" data-used_at="{{ $opt['used_at'] }}" data-phong-id="{{ $opt['phong_id'] ?? '' }}" data-so-phong="{{ $opt['so_phong'] ?? '' }}" data-service-name="{{ $opt['service_name'] }}" data-unit-price="{{ $opt['unit_price'] }}">{{ $opt['service_name'] }} — Phòng: {{ $opt['so_phong'] ?? '-' }} — {{ $opt['used_at_display'] }}</option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>Không có dịch vụ nào để bớt</option>
                                    @endif
                                </select>
                                <div class="text-xs text-gray-500 mt-1">Chọn dịch vụ từ danh sách để tự động thêm vào danh sách bớt</div>
                                @if(!isset($bookingServiceOptions) || count($bookingServiceOptions) === 0)
                                    <div class="text-sm text-yellow-600 mt-1">⚠ Không có dịch vụ nào để bớt. Tất cả dịch vụ đã được điều chỉnh hoặc không có dịch vụ trong hóa đơn này.</div>
                                @endif
                                <div id="adjust_items_container" class="mt-3 space-y-3"></div>
                            </div> 
                            <div>
                                <label class="text-sm font-medium">Hoàn tiền (tuỳ chọn)</label>
                                <div class="mt-2">
                                    <label class="text-sm">Phương thức hoàn tiền</label>
                                    <select id="adjust_refund_method" name="refund_method" class="w-full border rounded px-2 py-1 mb-2">
                                        <option value="tien_mat">Tiền mặt</option>
                                        <option value="chuyen_khoan">Chuyển khoản</option>
                                
                                    </select>
                                    <div id="adjust_refund_bank_fields" class="space-y-2 hidden">
                                        <input id="adjust_refund_account_number" type="text" name="refund_account_number" placeholder="Số tài khoản" class="w-full border rounded px-2 py-1 mb-2" value="">
                                        <input id="adjust_refund_account_name" type="text" name="refund_account_name" placeholder="Tên chủ tài khoản" class="w-full border rounded px-2 py-1 mb-2" value="">
                                        <input id="adjust_refund_bank_name" type="text" name="refund_bank_name" placeholder="Ngân hàng" class="w-full border rounded px-2 py-1" value="">
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

            <!-- Refund Invoice Modal (hidden) -->
            <div id="refund_modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
                    <h3 class="text-xl font-semibold mb-4 text-orange-600">Tạo hóa đơn hoàn tiền</h3>
                    <p class="text-sm text-gray-600 mb-4">Hóa đơn hoàn tiền sẽ được tạo riêng biệt, hóa đơn gốc #{{ $invoice->id }} sẽ được giữ nguyên.</p>
                    <form id="refund_form" method="POST" action="{{ route('admin.invoices.create_refund', $invoice->id) }}">
                        @csrf
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Chọn dịch vụ cần hoàn tiền</label>
                                <select id="refund_booking_services" class="w-full border rounded-lg px-3 py-2 mt-1">
                                    <option value="">-- Chọn dịch vụ để hoàn tiền --</option>
                                    @if(isset($bookingServiceOptions) && count($bookingServiceOptions) > 0)
                                        @foreach($bookingServiceOptions as $opt)
                                            <option value="{{ $opt['id'] }}" 
                                                    data-remaining="{{ $opt['remaining'] }}" 
                                                    data-used_at="{{ $opt['used_at'] }}" 
                                                    data-phong-id="{{ $opt['phong_id'] ?? '' }}" 
                                                    data-so-phong="{{ $opt['so_phong'] ?? '' }}" 
                                                    data-service-name="{{ $opt['service_name'] }}" 
                                                    data-unit-price="{{ $opt['unit_price'] }}">
                                                {{ $opt['service_name'] }} — Phòng: {{ $opt['so_phong'] ?? '-' }} — {{ $opt['used_at_display'] }} 
                                                @if($invoice->trang_thai === 'da_thanh_toan' && !$invoice->isRefund())
                                                    (Đã hoàn: {{ $opt['remaining'] }})
                                                @else
                                                    (Còn lại: {{ $opt['remaining'] }})
                                                @endif
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>Không có dịch vụ nào để hoàn tiền</option>
                                    @endif
                                </select>
                                <div class="text-xs text-gray-500 mt-1">Chọn dịch vụ từ danh sách để tự động thêm vào danh sách hoàn tiền</div>
                                @if(!isset($bookingServiceOptions) || count($bookingServiceOptions) === 0)
                                    <div class="text-sm text-yellow-600 mt-2 p-2 bg-yellow-50 rounded">
                                        ⚠ Không có dịch vụ nào để hoàn tiền. Tất cả dịch vụ đã được hoàn tiền hoặc không có dịch vụ trong hóa đơn này.
                                    </div>
                                @endif
                                <div id="refund_items_container" class="mt-4 space-y-3"></div>
                            </div>
                            
                            <div class="border-t pt-4">
                                <label class="text-sm font-medium text-gray-700 mb-2 block">Phương thức hoàn tiền</label>
                                <select id="refund_method" name="refund_method" class="w-full border rounded-lg px-3 py-2 mb-3">
                                    <option value="tien_mat">Tiền mặt</option>
                                    <option value="chuyen_khoan">Chuyển khoản</option>
                                    <option value="cong_thanh_toan">Cộng vào thanh toán</option>
                                </select>
                                <div id="refund_bank_fields" class="space-y-2 hidden">
                                    <input type="text" name="refund_account_number" placeholder="Số tài khoản" class="w-full border rounded-lg px-3 py-2">
                                    <input type="text" name="refund_account_name" placeholder="Tên chủ tài khoản" class="w-full border rounded-lg px-3 py-2">
                                    <input type="text" name="refund_bank_name" placeholder="Ngân hàng" class="w-full border rounded-lg px-3 py-2">
                                </div>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-700 mb-2 block">Ghi chú</label>
                                <textarea name="note" rows="3" class="w-full border rounded-lg px-3 py-2" placeholder="Lý do hoàn tiền (ví dụ: Khách không sử dụng dịch vụ)"></textarea>
                            </div>
                            
                            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                                <label class="text-sm font-medium text-gray-700 mb-2 block">Tổng tiền hoàn</label>
                                <div id="refund_total_display" class="text-2xl font-bold text-orange-600">0 đ</div>
                            </div>
                            
                            <div class="flex items-center justify-between pt-4 border-t">
                                <button type="button" id="refund_cancel_btn" class="px-6 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                                    Hủy
                                </button>
                                <button type="submit" class="px-6 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white font-semibold transition">
                                    Tạo hóa đơn hoàn tiền
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Main Invoice Card --}}
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">

                {{-- Invoice Header Section --}}
                <div class="bg-gradient-to-r {{ $invoice->isRefund() ? 'from-red-600 to-red-800' : 'from-blue-600 to-blue-800' }} text-white px-8 py-6">
                    <div class="grid grid-cols-3 gap-6 text-center">
                        <div>
                            <p class="{{ $invoice->isRefund() ? 'text-red-100' : 'text-blue-100' }} text-sm">Mã hóa đơn</p>
                            <p class="text-2xl font-bold">{{ $invoice->id }}</p>
                            @if($invoice->isRefund())
                                <p class="text-xs mt-1 text-red-200">Hóa đơn hoàn tiền</p>
                            @endif
                        </div>
                        <div>
                            <p class="{{ $invoice->isRefund() ? 'text-red-100' : 'text-blue-100' }} text-sm">Ngày tạo</p>
                            <p class="text-2xl font-bold">{{ \Carbon\Carbon::parse($invoice->ngay_tao)->format('d/m/Y') }}
                            </p>
                        </div>
                        <div>
                            <p class="{{ $invoice->isRefund() ? 'text-red-100' : 'text-blue-100' }} text-sm">Trạng thái</p>
                            <span
                                class="inline-block mt-1 px-4 py-1 rounded-full text-sm font-semibold {{ $invoice->trang_thai == 'hoan_tien' ? 'bg-red-300 text-red-900' : ($invoice->trang_thai == 'da_thanh_toan' ? 'bg-green-400 text-green-900' : 'bg-yellow-400 text-yellow-900') }}">
                                @if($invoice->trang_thai == 'hoan_tien')
                                    ↻ Hoàn tiền
                                @elseif($invoice->trang_thai == 'da_thanh_toan')
                                    ✓ Đã thanh toán
                                @else
                                    Chờ thanh toán
                                @endif
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
                                        <tr class="bg-gradient-to-r {{ $invoice->isRefund() ? 'from-red-600 to-red-700' : 'from-blue-600 to-blue-700' }} text-white">
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
                                                $usedAtKey = $s->used_at ? date('Y-m-d', strtotime($s->used_at)) : 'null';
                                                $qty = $s->quantity ?? 0;
                                                $unitPrice = $s->unit_price ?? 0;
                                                $subtotal = $qty * $unitPrice;
                                                
                                                // Kiểm tra xem dịch vụ này đã được hoàn tiền chưa
                                                $isRefunded = false;
                                                if (!$invoice->isRefund() && isset($refundedServiceIds)) {
                                                    $refundKey = $s->service_id . '_' . ($s->phong_id ?? 'null') . '_' . $usedAtKey;
                                                    $isRefunded = $refundedServiceIds->has($refundKey);
                                                }
                                                // Hoặc nếu quantity <= 0 trong hóa đơn gốc (đã được adjust)
                                                if (!$invoice->isRefund() && $qty <= 0) {
                                                    $isRefunded = true;
                                                }
                                            @endphp
                                            <tr class="transition {{ $isRefunded ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-blue-50' }}">
                                                <td class="px-6 py-4 {{ $isRefunded ? 'text-red-700 font-semibold' : 'text-gray-900' }}">
                                                    <div>{{ $name }}</div>
                                                    @if($s->note)
                                                        <div class="text-xs {{ $isRefunded ? 'text-red-600' : 'text-gray-500' }} italic mt-1">{{ $s->note }}</div>
                                                    @endif
                                                    @if($isRefunded && !$invoice->isRefund())
                                                        <div class="text-xs text-red-600 font-semibold mt-1">
                                                            <i class="fas fa-undo-alt mr-1"></i>Đã hoàn tiền
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 text-center {{ $isRefunded ? 'text-red-700' : 'text-gray-700' }}">
                                                    {{ $s->phong ? $s->phong->so_phong ?? $s->phong->id : $s->phong_id ?? '-' }}
                                                </td>
                                                <td class="px-6 py-4 text-center {{ $isRefunded ? 'text-red-700' : 'text-gray-700' }}">{{ $usedAt }}</td>
                                                <td class="px-6 py-4 text-right {{ ($invoice->isRefund() && $qty < 0) || $isRefunded ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                                                    @if($invoice->isRefund() && $qty < 0)
                                                        {{ abs($qty) }}
                                                    @else
                                                        {{ $qty }}
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 text-right {{ $isRefunded ? 'text-red-700' : 'text-gray-700' }}">
                                                    {{ number_format($unitPrice, 0, ',', '.') }} đ</td>
                                                <td class="px-6 py-4 text-right font-semibold {{ ($invoice->isRefund() && $subtotal < 0) || $isRefunded ? 'text-red-600' : 'text-gray-900' }}">
                                                    @if($invoice->isRefund() && $subtotal < 0)
                                                        {{ number_format(abs($subtotal), 0, ',', '.') }} đ
                                                    @else
                                                        {{ number_format($subtotal, 0, ',', '.') }} đ
                                                    @endif
                                                </td>
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
                                <span class="font-semibold {{ $invoice->isRefund() && $servicesTotal < 0 ? 'text-red-600' : 'text-gray-900' }}">
                                    @if($invoice->isRefund() && $servicesTotal < 0)
                                        {{ number_format(abs($servicesTotal), 0, ',', '.') }}₫
                                    @else
                                        {{ number_format($servicesTotal, 0, ',', '.') }}₫
                                    @endif
                                </span>
                            </div>

                            @php
                                $invoicePhiThemNguoi = $invoice->phi_them_nguoi ?? 0;
                                // If invoice does not have phi_them_nguoi, fall back to computed extraGuestTotal
                                $displayPhiThemNguoi = $invoicePhiThemNguoi > 0 ? $invoicePhiThemNguoi : ($extraGuestTotal ?? 0);
                            @endphp

                             @if (($phiPhatSinh ?? 0) > 0)
                                 <div class="flex justify-between text-sm text-gray-600">
                                     <span class="font-medium">Phụ phí phát sinh</span>
                                     <span class="font-bold text-red-600">+{{ number_format($phiPhatSinh, 0, ',', '.') }}₫</span>
                                 </div>
                                 @php
                                     $combinedNotes = ($booking->ghi_chu_checkin ?? '') . "\n" . ($booking->ghi_chu_checkout ?? '');
                                     $feeBreakdown = [];
                                     
                                     // Check for Early Check-in
                                     if (preg_match_all('/Phụ phí check-in sớm: ([\d\.,]+)/i', $combinedNotes, $matches)) {
                                         $sum = 0;
                                         foreach($matches[1] as $val) $sum += (float)str_replace(['.', ','], ['', '.'], $val);
                                         if ($sum > 0) $feeBreakdown[] = ['label' => 'Check-in sớm', 'amount' => $sum];
                                     }

                                     // Check for Late Check-out
                                     if (preg_match_all('/Phụ phí check-out trễ: ([\d\.,]+)/i', $combinedNotes, $matches)) {
                                         $sum = 0;
                                         foreach($matches[1] as $val) $sum += (float)str_replace(['.', ','], ['', '.'], $val);
                                         if ($sum > 0) $feeBreakdown[] = ['label' => 'Check-out trễ', 'amount' => $sum];
                                     }

                                     // Check for Damage Fees (with categories if possible)
                                     if (preg_match_all('/Phụ phí thiệt hại: ([\d\.,]+) VNĐ(?:\nDanh mục: (.*))?/i', $combinedNotes, $matches)) {
                                         foreach($matches[1] as $index => $val) {
                                             $amt = (float)str_replace(['.', ','], ['', '.'], $val);
                                             $cat = !empty($matches[2][$index]) ? $matches[2][$index] : 'Thiệt hại tài sản';
                                             if ($amt > 0) {
                                                 $feeBreakdown[] = ['label' => $cat, 'amount' => $amt];
                                             }
                                         }
                                     }
                                 @endphp

                                 @if(count($feeBreakdown) > 0)
                                     <div class="pl-4 space-y-0.5 mb-2">
                                         @foreach($feeBreakdown as $fee)
                                             <div class="flex justify-between text-[11px] text-gray-500 italic">
                                                 <span>• {{ $fee['label'] }}</span>
                                                 <span>+{{ number_format($fee['amount'], 0, ',', '.') }}₫</span>
                                             </div>
                                         @endforeach
                                     </div>
                                 @endif
                             @endif


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
                                            {{ $invoice->phuong_thuc_ui['label'] }}
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
                                        
                                        <div class="space-y-1 pb-2">
                                            <label class="text-[10px] font-bold text-gray-500 uppercase">Phương thức thanh toán</label>
                                            <select name="phuong_thuc" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition-all" required>
                                                <option value="tien_mat">💵 Tiền mặt</option>
                                                <option value="chuyen_khoan">🏦 Chuyển khoản</option>
                                            </select>
                                        </div>

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
                                            
                                            // Đối với refund invoice, đảm bảo hiển thị số dương
                                            if ($invoice->isRefund() && $tongCuoiCung < 0) {
                                                $tongCuoiCung = abs($tongCuoiCung);
                                            }
                                        @endphp
                                        @if ($invoice->tong_tien && $invoice->tong_tien != $calculatedTotal)
                                            <p class="text-xs text-gray-500 mb-2">
                                                (Đã lưu trong hệ thống: {{ number_format((float) $invoice->tong_tien, 0, ',', '.') }}₫)
                                            </p>
                                        @endif
                                        <div class="flex justify-between items-center py-3 {{ $invoice->isRefund() ? 'bg-red-600' : 'bg-blue-600' }} -mx-6 px-6 -mb-6">
                                            <span class="font-bold text-white text-lg">
                                                @if($invoice->isRefund())
                                                    TỔNG TIỀN HOÀN
                                                @else
                                                    TỔNG THANH TOÁN
                                                @endif
                                            </span>
                                            <span class="text-white text-3xl font-bold">
                                                @if($invoice->isRefund())
                                                    {{ number_format(abs((float)$tongCuoiCung), 0, ',', '.') }} đ
                                                @else
                                                    {{ number_format($tongCuoiCung, 0, ',', '.') }} đ
                                                @endif
                                            </span>
                                        </div>
                            </div>
                            {{-- Payment method --}}
                            
                                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Phương thức thanh toán</span>
                                        <span class="text-sm font-semibold text-gray-900">{{ $invoice->phuong_thuc_ui['label'] }}</span>
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
                                    
                                    // Đối với refund invoice, đảm bảo hiển thị số dương
                                    if ($invoice->isRefund()) {
                                        $tongCuoiCung = abs((float)$tongCuoiCung);
                                    }
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

                                {{-- Chi tiết hóa đơn cho EXTRA invoice --}}
                                @php
                                    // Lấy danh sách tên phòng từ booking hoặc từ services
                                    $roomNames = [];
                                    if ($booking) {
                                        // Lấy từ booking->phongs() (pivot table)
                                        $assignedPhongs = $booking->phongs ?? collect();
                                        if ($assignedPhongs->isNotEmpty()) {
                                            $roomNames = $assignedPhongs->map(function($phong) {
                                                return $phong->so_phong ?? $phong->ten_phong ?? 'Phòng #' . $phong->id;
                                            })->unique()->toArray();
                                        } else {
                                            // Fallback: lấy từ services trong invoice
                                            $servicePhongs = $services->whereNotNull('phong_id')->pluck('phong')->filter()->unique('id');
                                            if ($servicePhongs->isNotEmpty()) {
                                                $roomNames = $servicePhongs->map(function($phong) {
                                                    return $phong->so_phong ?? $phong->ten_phong ?? 'Phòng #' . $phong->id;
                                                })->unique()->toArray();
                                            } elseif ($booking->phong_id) {
                                                // Fallback: lấy từ legacy phong_id
                                                $legacyPhong = $booking->phong;
                                                if ($legacyPhong) {
                                                    $roomNames = [$legacyPhong->so_phong ?? $legacyPhong->ten_phong ?? 'Phòng #' . $legacyPhong->id];
                                                }
                                            }
                                        }
                                    }
                                    $roomNamesDisplay = !empty($roomNames) ? implode(', ', $roomNames) : 'N/A';
                                    
                                    // Format phương thức thanh toán
                                    $phuongThucDisplay = $invoice->phuong_thuc_ui['label'];
                                @endphp

                                <div class="space-y-3 py-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <span class="text-sm text-gray-600">Tên phòng:</span>
                                            <span class="text-sm font-semibold text-gray-900 ml-2">{{ $roomNamesDisplay }}</span>
                                        </div>
                                        <div>
                                            <span class="text-sm text-gray-600">Phương thức thanh toán:</span>
                                            <span class="text-sm font-semibold text-gray-900 ml-2">{{ $phuongThucDisplay }}</span>
                                        </div>
                                    </div>
                                </div>

                                @if ($invoice->trang_thai === 'cho_thanh_toan')
                                    <form action="{{ route('admin.invoices.update', $invoice->id) }}" method="POST"
                                        class="pt-3 border-t space-y-2 no-print">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="trang_thai" value="da_thanh_toan">
                                        
                                        <div class="space-y-1 pb-2">
                                            <label class="text-[10px] font-bold text-gray-500 uppercase">Phương thức thanh toán</label>
                                            <select name="phuong_thuc" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition-all" required>
                                                <option value="tien_mat">💵 Tiền mặt</option>
                                                <option value="chuyen_khoan">🏦 Chuyển khoản</option>
                                            </select>
                                        </div>

                                        <button type="submit"
                                            class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                                            Đánh dấu đã thanh toán
                                        </button>
                                        <div class="text-xs text-gray-500 text-center">
                                            Xác nhận thanh toán cho hóa đơn phát sinh này.
                                        </div>
                                    </form>
                                @endif

                                <div class="border-t-2 border-gray-300"></div>

                                <div class="flex justify-between items-center py-3 {{ $invoice->isRefund() ? 'bg-red-600' : 'bg-blue-600' }} -mx-6 px-6 -mb-6">
                                    <span class="font-bold text-white text-lg">
                                        @if($invoice->isRefund())
                                            TỔNG TIỀN HOÀN
                                        @else
                                            TỔNG THANH TOÁN
                                        @endif
                                    </span>
                                    <span
                                        class="text-white text-3xl font-bold">
                                        @if($invoice->isRefund())
                                            {{ number_format(abs((float)$tongCuoiCung), 0, ',', '.') }} đ
                                        @else
                                            {{ number_format($tongCuoiCung, 0, ',', '.') }} đ
                                        @endif
                                    </span>
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

        function openModal(){ 
            modal.classList.remove('hidden'); 
            console.log('Modal opened');
            
            // Reset form khi mở modal
            if (itemsContainer) {
                itemsContainer.innerHTML = '';
            }
            if (bookingServicesSelect) {
                // Reset to placeholder option
                bookingServicesSelect.value = '';
                
                // Log options count
                console.log('Options in select:', bookingServicesSelect.options.length);
            }
            updateAdjustTotal();
        }
        function closeModal(){ 
            modal.classList.add('hidden'); 
            // Reset form khi đóng modal
            if (itemsContainer) {
                itemsContainer.innerHTML = '';
            }
            if (bookingServicesSelect) {
                // Reset to placeholder
                bookingServicesSelect.value = '';
            }
            updateAdjustTotal();
        }

        if (openBtn) openBtn.addEventListener('click', openModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);


        function findOptionByValue(val) {
            if (!bookingServicesSelect) return null;
            return bookingServicesSelect.querySelector('option[value="' + val + '"]');
        }

        function createAdjustRow(id, opt) {
            if (!opt) {
                console.error('createAdjustRow: option is null for id', id);
                return null;
            }
            
            const rem = parseInt(opt.dataset.remaining || 0, 10);
            const used_at = opt.dataset.used_at || '';
            const phongId = opt.dataset.phongId || '';
            const soPhong = opt.dataset.soPhong || '';
            const svcName = opt.dataset.serviceName || '';
            const unitPrice = parseFloat(opt.dataset.unitPrice || 0);
            
            console.log('createAdjustRow', {id, rem, unitPrice, svcName});
            
            if (!unitPrice || unitPrice <= 0) {
                console.warn('createAdjustRow: unitPrice is invalid', unitPrice);
            }

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

            // Hidden booking_service_id - sẽ được reindex sau
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'adjustments[0][booking_service_id]'; // Temporary, sẽ được reindex
            hidden.value = id;
            hidden.className = 'adjust-booking-service-id';
            wrapper.appendChild(hidden);

            // Grid container
            const grid = document.createElement('div');
            grid.className = 'grid grid-cols-2 gap-2 mb-1';

            // ID column (read-only display, không cần name vì không gửi lên server)
            const idCol = document.createElement('div');
            const idLabel = document.createElement('label');
            idLabel.className = 'text-xs';
            idLabel.textContent = 'ID';
            const idInput = document.createElement('input');
            idInput.type = 'text';
            idInput.className = 'w-full border rounded px-2 py-1 bg-gray-100';
            idInput.readOnly = true;
            // Không đặt name để không gửi lên server
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
            qtyInput.name = 'adjustments[0][quantity]'; // Temporary, sẽ được reindex
            qtyInput.className = 'w-full border rounded px-2 py-1 adjust-qty';
            qtyInput.dataset.max = rem;
            qtyInput.dataset.unitPrice = unitPrice;
            qtyInput.dataset.bookingServiceId = id;
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
            subtotalWrap.className = 'text-right text-sm text-gray-600 line-subtotal';
            const initialQty = rem > 0 ? 1 : 0;
            subtotalWrap.textContent = formatCurrency(unitPrice * initialQty);
            wrapper.appendChild(subtotalWrap);

            // Remove handler
            removeBtn.addEventListener('click', safe(function(){
                try {
                    // Unselect option in native select
                    const opt = findOptionByValue(id);
                    if (opt) opt.selected = false;
                    
                    // Remove row from container
                    wrapper.remove();
                    reindexAdjustItems();
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
            // Reindex ngay sau khi tạo row (không dùng setTimeout để đảm bảo đồng bộ)
            // Note: wrapper chưa được append vào DOM nên cần reindex sau khi append
            // Sẽ được gọi từ updateSelectedItems sau khi append

            return wrapper;
        }

        function updateSelectedItems(values) {
            try {
                console.log('updateSelectedItems called with', values);
                
                if (!itemsContainer) {
                    console.error('itemsContainer not found');
                    return;
                }
                
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

                console.log('Normalized selected IDs', sel);

                // This function is kept for compatibility but not used with new single-select approach
                // The new approach uses addSelectedService() function instead

                // Remove unselected
                const existing = Array.from(itemsContainer.querySelectorAll('.adjust-item'));
                existing.forEach(node => {
                    const id = node.getAttribute('data-id');
                    if (sel.indexOf(id) === -1) {
                        console.log('Removing row for id', id);
                        node.remove();
                    }
                });

                // update total after adding/removing rows
                updateAdjustTotal();
            } catch (err) { 
                console.error('updateSelectedItems error', err); 
            }
        }

        // Reindex tất cả adjust items để đảm bảo name attributes đúng format
        function reindexAdjustItems() {
            try {
                if (!itemsContainer) {
                    console.error('itemsContainer not found in reindexAdjustItems');
                    return;
                }
                
                const items = Array.from(itemsContainer.querySelectorAll('.adjust-item'));
                console.log('Reindexing', items.length, 'items');
                
                items.forEach((item, index) => {
                    // Tìm input bằng nhiều cách
                    const bookingServiceIdInput = item.querySelector('.adjust-booking-service-id') ||
                                                  item.querySelector('input[type="hidden"]') ||
                                                  Array.from(item.querySelectorAll('input')).find(inp => inp.name && inp.name.includes('booking_service_id'));
                    
                    const qtyInput = item.querySelector('.adjust-qty') ||
                                    item.querySelector('input[type="number"]') ||
                                    Array.from(item.querySelectorAll('input')).find(inp => inp.name && inp.name.includes('quantity'));
                    
                    if (bookingServiceIdInput) {
                        const oldName = bookingServiceIdInput.name;
                        const newName = 'adjustments[' + index + '][booking_service_id]';
                        bookingServiceIdInput.name = newName;
                        console.log(`Reindexed booking_service_id: ${oldName} -> ${newName}, value: ${bookingServiceIdInput.value}`);
                        
                        // Đảm bảo input có value hợp lệ
                        if (!bookingServiceIdInput.value || bookingServiceIdInput.value === '0' || bookingServiceIdInput.value === '') {
                            console.error('Invalid booking_service_id value in item', index, bookingServiceIdInput.value);
                        }
                    } else {
                        console.error('Could not find booking_service_id input in item', index, item);
                    }
                    
                    if (qtyInput) {
                        const oldName = qtyInput.name;
                        const newName = 'adjustments[' + index + '][quantity]';
                        qtyInput.name = newName;
                        console.log(`Reindexed quantity: ${oldName} -> ${newName}, value: ${qtyInput.value}`);
                        
                        // Đảm bảo input có value hợp lệ
                        const qtyVal = parseInt(qtyInput.value || 0, 10);
                        if (!qtyVal || qtyVal <= 0) {
                            console.error('Invalid quantity value in item', index, qtyInput.value);
                        }
                    } else {
                        console.error('Could not find quantity input in item', index, item);
                    }
                });
                
                console.log('Reindexed', items.length, 'adjust items successfully');
            } catch (err) {
                console.error('reindexAdjustItems error', err);
            }
        }

        // Recompute the overall total of removed services and update UI
        function updateAdjustTotal() {
            try {
                if (!itemsContainer) {
                    console.error('itemsContainer not found in updateAdjustTotal');
                    return;
                }
                
                const items = Array.from(itemsContainer.querySelectorAll('.adjust-item'));
                let total = 0;
                
                console.log('updateAdjustTotal: processing', items.length, 'items');
                
                items.forEach((it, idx) => {
                    try {
                        const qtyEl = it.querySelector('.adjust-qty');
                        if (!qtyEl) {
                            console.warn('No quantity input found in item', idx);
                            return;
                        }
                        
                        const qty = parseInt(qtyEl.value || 0, 10);
                        if (isNaN(qty) || qty <= 0) {
                            console.log('Item', idx, 'has invalid quantity:', qtyEl.value);
                            return;
                        }
                        
                        let unit = NaN;
                        // Try to get unit price from dataset
                        if (qtyEl.dataset && qtyEl.dataset.unitPrice) {
                            unit = parseFloat(qtyEl.dataset.unitPrice);
                            console.log('Item', idx, 'unit price from dataset:', unit);
                        }

                        // Fallback to option dataset
                        if ((isNaN(unit) || unit === 0) && bookingServicesSelect) {
                            const bsId = it.getAttribute('data-id');
                            if (bsId) {
                                const opt = bookingServicesSelect.querySelector('option[value="' + bsId + '"]');
                                if (opt && opt.dataset.unitPrice) {
                                    unit = parseFloat(opt.dataset.unitPrice);
                                    console.log('Item', idx, 'unit price from option:', unit);
                                }
                            }
                        }

                        if (!isNaN(qty) && !isNaN(unit) && unit > 0) {
                            const itemTotal = qty * unit;
                            total += itemTotal;
                            console.log('Item', idx, 'total:', itemTotal, '(qty:', qty, 'x unit:', unit, ')');
                        } else {
                            console.warn('Item', idx, 'has invalid qty or unit:', {qty, unit});
                        }
                    } catch (inner) {
                        console.error('line total calc error for item', idx, inner);
                    }
                });

                console.log('updateAdjustTotal: calculated total:', total);

                const display = document.getElementById('adjust_total_display');
                if (display) {
                    display.textContent = formatCurrency(total);
                    // Thêm class để highlight khi có giá trị
                    if (total > 0) {
                        display.classList.remove('text-gray-400');
                        display.classList.add('text-red-600', 'font-bold');
                    } else {
                        display.classList.remove('text-red-600', 'font-bold');
                        display.classList.add('text-gray-400');
                    }
                } else {
                    console.error('adjust_total_display element not found');
                }
            } catch (err) { 
                console.error('updateAdjustTotal error', err); 
            }
        }

        // Function to add selected service (must be accessible globally)
        window.addSelectedService = function() {
            if (!bookingServicesSelect || !itemsContainer) {
                console.error('Required elements not found');
                return false;
            }
            
            const selectedValue = bookingServicesSelect.value;
            if (!selectedValue || selectedValue === '') {
                return false;
            }
            
            // Check if already added
            const existing = itemsContainer.querySelector('.adjust-item[data-id="' + selectedValue + '"]');
            if (existing) {
                // Already added, just reset select
                bookingServicesSelect.value = '';
                return false;
            }
            
            const opt = findOptionByValue(selectedValue);
            if (!opt) {
                console.error('Option not found for value', selectedValue);
                return false;
            }
            
            console.log('Adding service:', selectedValue);
            const row = createAdjustRow(selectedValue, opt);
            if (row) {
                itemsContainer.appendChild(row);
                reindexAdjustItems();
                updateAdjustTotal();
                
                // Reset select to placeholder
                bookingServicesSelect.value = '';
                return true;
            }
            return false;
        };
        
        if (bookingServicesSelect) {
            console.log('bookingServicesSelect found, options count:', bookingServicesSelect.options.length);
            
            // Kiểm tra xem có options không
            if (bookingServicesSelect.options.length <= 1) { // <= 1 because first option is placeholder
                console.warn('No service options found in bookingServicesSelect!');
            } else {
                console.log('Service options found:', Array.from(bookingServicesSelect.options).slice(1).map(opt => ({
                    value: opt.value,
                    text: opt.text,
                    remaining: opt.dataset.remaining,
                    unitPrice: opt.dataset.unitPrice
                })));
            }
            
            // Auto-add service when select changes (immediately)
            bookingServicesSelect.addEventListener('change', function() {
                const selectedValue = bookingServicesSelect.value;
                if (selectedValue && selectedValue !== '') {
                    // Check if already added
                    const existing = itemsContainer.querySelector('.adjust-item[data-id="' + selectedValue + '"]');
                    if (!existing) {
                        // Auto-add immediately
                        console.log('Auto-adding service on change:', selectedValue);
                        window.addSelectedService();
                    } else {
                        // Already added, reset select
                        bookingServicesSelect.value = '';
                    }
                }
            });
        } else {
            console.error('bookingServicesSelect element not found! Check if element ID is correct.');
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
            console.log('Form found, attaching submit listener');
            
            form.addEventListener('submit', function(e){
                console.log('Form submit event triggered');
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
                    } else {
                        // Nếu không phải chuyển khoản, xóa các trường refund account để không gửi lên server
                        const acct = document.getElementById('adjust_refund_account_number');
                        const name = document.getElementById('adjust_refund_account_name');
                        const bank = document.getElementById('adjust_refund_bank_name');
                        if (acct) acct.removeAttribute('name');
                        if (name) name.removeAttribute('name');
                        if (bank) bank.removeAttribute('name');
                    }
                } catch (err) { console.error('refund bank client validation error', err); }

                // Debug: log adjustments payload in console so admins can copy it when reporting issues
                try {
                    const debugAdjust = Array.from(itemsContainer.querySelectorAll('.adjust-item')).map(it => {
                        const qtyEl = it.querySelector('.adjust-qty');
                        const hiddenId = it.querySelector('input[name*="[booking_service_id]"]');
                        return {
                            booking_service_id: hiddenId ? hiddenId.value : it.getAttribute('data-id'),
                            quantity: qtyEl ? parseInt(qtyEl.value || 0, 10) : 0,
                            element: it
                        };
                    });
                    console.log('Submitting adjustments', debugAdjust);
                    
                    // Verify form data before submit
                    const formData = new FormData(form);
                    console.log('Form data entries:');
                    for (let [key, value] of formData.entries()) {
                        console.log(key, '=', value);
                    }
                } catch (err) { 
                    console.error('Debug logging error', err); 
                }

                // Đảm bảo reindex trước khi kiểm tra
                reindexAdjustItems();
                
                let items = Array.from(itemsContainer.querySelectorAll('.adjust-item'));
                console.log('Form submit - items found:', items.length);
                
                // Nếu chưa có items nhưng có dịch vụ được chọn trong select, tự động thêm
                if (items.length === 0) {
                    const selectedValue = bookingServicesSelect ? bookingServicesSelect.value : '';
                    if (selectedValue && selectedValue !== '') {
                        console.log('No items found but service selected, auto-adding...');
                        if (window.addSelectedService && window.addSelectedService()) {
                            // Re-check items after adding
                            reindexAdjustItems();
                            items = Array.from(itemsContainer.querySelectorAll('.adjust-item'));
                            console.log('Items after auto-add:', items.length);
                            if (items.length === 0) {
                                e.preventDefault();
                                alert('Vui lòng chọn ít nhất một dịch vụ để bớt.');
                                return;
                            }
                        } else {
                            e.preventDefault();
                            alert('Vui lòng chọn ít nhất một dịch vụ để bớt.');
                            return;
                        }
                    } else {
                        e.preventDefault();
                        alert('Vui lòng chọn ít nhất một dịch vụ để bớt.');
                        return;
                    }
                }
                
                // Đảm bảo tất cả items có dữ liệu hợp lệ
                let hasInvalidItems = false;
                items.forEach((it, idx) => {
                    const qtyEl = it.querySelector('.adjust-qty');
                    const hiddenId = it.querySelector('.adjust-booking-service-id') || it.querySelector('input[name*="booking_service_id"]');
                    
                    if (!qtyEl || !hiddenId) {
                        console.error('Missing inputs in item', idx);
                        hasInvalidItems = true;
                        return;
                    }
                    
                    const qty = parseInt(qtyEl.value || 0, 10);
                    const bsId = hiddenId.value;
                    
                    if (!qty || qty <= 0 || !bsId || bsId === '0') {
                        console.error('Invalid data in item', idx, {qty, bsId});
                        hasInvalidItems = true;
                    }
                });
                
                if (hasInvalidItems) {
                    e.preventDefault();
                    alert('Vui lòng kiểm tra lại số lượng và dịch vụ đã chọn.');
                    return;
                }
                
                // Validate và log để debug
                let hasError = false;
                const validItems = [];
                
                for (const it of items) {
                    // Tìm input bằng nhiều cách để đảm bảo tìm được
                    const qtyEl = it.querySelector('.adjust-qty') || it.querySelector('input[type="number"]');
                    const hiddenId = it.querySelector('.adjust-booking-service-id') || 
                                    it.querySelector('input[name*="booking_service_id"]') ||
                                    it.querySelector('input[type="hidden"]');
                    
                    console.log('Checking item', {
                        item: it,
                        hasQtyEl: !!qtyEl,
                        hasHiddenId: !!hiddenId,
                        itemHTML: it.outerHTML.substring(0, 200)
                    });
                    
                    if (!qtyEl) { 
                        console.error('Quantity input not found in item', it);
                        e.preventDefault(); 
                        alert('Số lượng không hợp lệ.'); 
                        hasError = true;
                        return; 
                    }
                    
                    if (!hiddenId) {
                        console.error('Booking service ID input not found in item', it);
                        console.error('Item HTML:', it.innerHTML);
                        e.preventDefault();
                        alert('Lỗi: Không tìm thấy ID dịch vụ.');
                        hasError = true;
                        return;
                    }
                    
                    const val = parseInt(qtyEl.value || 0, 10);
                    const max = parseInt(qtyEl.dataset.max || qtyEl.getAttribute('max') || 0, 10);
                    const bookingServiceId = hiddenId.value || hiddenId.getAttribute('value');
                    
                    console.log('Validating item', {
                        bookingServiceId, 
                        val, 
                        max,
                        qtyElName: qtyEl.name,
                        hiddenIdName: hiddenId.name
                    });
                    
                    if (!bookingServiceId || bookingServiceId === '0') {
                        console.error('Invalid booking service ID', bookingServiceId);
                        e.preventDefault();
                        alert('Lỗi: ID dịch vụ không hợp lệ.');
                        hasError = true;
                        return;
                    }
                    
                    if (val <= 0 || val > max) { 
                        console.error('Invalid quantity', {val, max, item: it});
                        e.preventDefault(); 
                        alert('Số lượng không hợp lệ hoặc vượt quá khả dụng (' + max + ').'); 
                        hasError = true;
                        return; 
                    }
                    
                    validItems.push({
                        booking_service_id: bookingServiceId,
                        quantity: val
                    });
                }
                
                if (!hasError && validItems.length > 0) {
                    console.log('Form will submit with valid items:', validItems);
                    
                    // Đảm bảo form có đầy đủ dữ liệu trước khi submit
                    console.log('Final form check before submit');
                    const finalFormData = new FormData(form);
                    const adjustmentsInForm = [];
                    for (let [key, value] of finalFormData.entries()) {
                        if (key.includes('adjustments') && key.includes('booking_service_id')) {
                            const indexMatch = key.match(/adjustments\[(\d+)\]/);
                            if (indexMatch) {
                                const idx = indexMatch[1];
                                const qty = finalFormData.get(`adjustments[${idx}][quantity]`);
                                adjustmentsInForm.push({
                                    index: idx,
                                    booking_service_id: value,
                                    quantity: qty
                                });
                            }
                        }
                    }
                    console.log('Adjustments found in form:', adjustmentsInForm);
                    
                    if (adjustmentsInForm.length === 0) {
                        console.error('No adjustments found in form data!');
                        console.error('Form HTML:', form.innerHTML.substring(0, 500));
                        e.preventDefault();
                        alert('Lỗi: Không tìm thấy dữ liệu dịch vụ trong form. Vui lòng thử lại.');
                        return;
                    }
                    
                    // Đảm bảo reindex và fix names TRƯỚC KHI submit
                    reindexAdjustItems();
                    
                    const itemsAfterReindex = Array.from(itemsContainer.querySelectorAll('.adjust-item'));
                    console.log('Items after reindex:', itemsAfterReindex.length);
                    
                    if (itemsAfterReindex.length === 0) {
                        e.preventDefault();
                        alert('Vui lòng chọn ít nhất một dịch vụ để bớt.');
                        return;
                    }
                    
                    // Fix names và verify data - đảm bảo tuần tự từ 0
                    let allValid = true;
                    itemsAfterReindex.forEach((it, idx) => {
                        const qtyEl = it.querySelector('.adjust-qty');
                        const hiddenId = it.querySelector('.adjust-booking-service-id') || it.querySelector('input[name*="booking_service_id"]');
                        
                        if (!qtyEl || !hiddenId) {
                            console.error('Missing inputs in item', idx);
                            allValid = false;
                            return;
                        }
                        
                        // Force correct names với index tuần tự từ 0
                        const qtyName = 'adjustments[' + idx + '][quantity]';
                        const idName = 'adjustments[' + idx + '][booking_service_id]';
                        
                        qtyEl.name = qtyName;
                        hiddenId.name = idName;
                        
                        const qty = parseInt(qtyEl.value || 0, 10);
                        const bsId = hiddenId.value;
                        
                        console.log('Item', idx, ':', {
                            qtyName: qtyEl.name,
                            qtyValue: qty,
                            idName: hiddenId.name,
                            idValue: bsId,
                            qtyElExists: !!qtyEl,
                            hiddenIdExists: !!hiddenId
                        });
                        
                        if (!qty || qty <= 0 || !bsId || bsId === '0' || bsId === '') {
                            console.error('Invalid data in item', idx, {qty, bsId});
                            allValid = false;
                        }
                    });
                    
                    if (!allValid) {
                        e.preventDefault();
                        alert('Lỗi: Dữ liệu dịch vụ không hợp lệ. Vui lòng kiểm tra lại số lượng.');
                        return;
                    }
                    
                    // Kiểm tra FormData sau khi fix - đảm bảo có dữ liệu
                    const finalFormDataCheck = new FormData(form);
                    const adjustmentsFound = [];
                    console.log('Final FormData entries:');
                    for (let [key, value] of finalFormDataCheck.entries()) {
                        console.log('  ', key, '=', value);
                        if (key.startsWith('adjustments[') && key.includes('][booking_service_id]')) {
                            const idxMatch = key.match(/adjustments\[(\d+)\]/);
                            if (idxMatch) {
                                const idx = idxMatch[1];
                                const qty = finalFormDataCheck.get(`adjustments[${idx}][quantity]`);
                                if (qty && value) {
                                    adjustmentsFound.push({idx, booking_service_id: value, quantity: qty});
                                }
                            }
                        }
                    }
                    
                    console.log('Adjustments found in FormData:', adjustmentsFound);
                    
                    if (adjustmentsFound.length === 0) {
                        e.preventDefault();
                        console.error('No adjustments found in FormData!');
                        alert('Lỗi: Không tìm thấy dữ liệu dịch vụ trong form. Vui lòng thử lại.');
                        return;
                    }
                    
                    // Không preventDefault - cho phép form submit
                    console.log('Form submission allowed - submitting now with', adjustmentsFound.length, 'adjustments');
                } else if (!hasError) {
                    e.preventDefault();
                    alert('Không có dịch vụ hợp lệ để xóa.');
                    return;
                } else {
                    console.error('Form submission prevented due to errors');
                }
            });
        }
    })();

    // Refund Invoice Modal Script
    (function() {
        const refundModal = document.getElementById('refund_modal');
        const openRefundBtn = document.getElementById('open_refund_modal');
        const refundCancelBtn = document.getElementById('refund_cancel_btn');
        const refundForm = document.getElementById('refund_form');
        const refundBookingServicesSelect = document.getElementById('refund_booking_services');
        const refundItemsContainer = document.getElementById('refund_items_container');
        const refundTotalDisplay = document.getElementById('refund_total_display');
        const refundMethodSelect = document.getElementById('refund_method');
        const refundBankFields = document.getElementById('refund_bank_fields');

        let refundItemIndex = 0;

        // Open/Close modal
        if (openRefundBtn && refundModal) {
            openRefundBtn.addEventListener('click', function() {
                refundModal.classList.remove('hidden');
                refundItemIndex = 0;
                refundItemsContainer.innerHTML = '';
                updateRefundTotal();
            });
        }

        if (refundCancelBtn && refundModal) {
            refundCancelBtn.addEventListener('click', function() {
                refundModal.classList.add('hidden');
                refundItemsContainer.innerHTML = '';
                refundItemIndex = 0;
                updateRefundTotal();
            });
        }

        // Close modal when clicking outside
        if (refundModal) {
            refundModal.addEventListener('click', function(e) {
                if (e.target === refundModal) {
                    refundModal.classList.add('hidden');
                }
            });
        }

        // Add service to refund list
        if (refundBookingServicesSelect) {
            refundBookingServicesSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (!selectedOption.value) return;

                const bsId = selectedOption.value;
                const serviceName = selectedOption.dataset.serviceName || 'Dịch vụ';
                const soPhong = selectedOption.dataset.soPhong || '-';
                const usedAt = selectedOption.dataset.usedAt || '';
                const unitPrice = parseFloat(selectedOption.dataset.unitPrice || 0);
                const remaining = parseInt(selectedOption.dataset.remaining || 0, 10);

                if (remaining <= 0) {
                    alert('Dịch vụ này đã được hoàn tiền hết.');
                    this.value = '';
                    return;
                }

                // Check if already added
                const existingItem = refundItemsContainer.querySelector(`[data-bs-id="${bsId}"]`);
                if (existingItem) {
                    alert('Dịch vụ này đã được thêm vào danh sách.');
                    this.value = '';
                    return;
                }

                // Create refund item
                const itemDiv = document.createElement('div');
                itemDiv.className = 'refund-item border rounded-lg p-3 bg-gray-50';
                itemDiv.setAttribute('data-bs-id', bsId);
                itemDiv.innerHTML = `
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex-1">
                            <div class="font-medium text-gray-900">${serviceName}</div>
                            <div class="text-xs text-gray-500">Phòng: ${soPhong} | Ngày: ${usedAt ? new Date(usedAt).toLocaleDateString('vi-VN') : '-'}</div>
                        </div>
                        <button type="button" class="remove-refund-item text-red-600 hover:text-red-800 ml-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-xs text-gray-600">Số lượng</label>
                            <input type="number" 
                                   name="adjustments[${refundItemIndex}][quantity]" 
                                   class="refund-qty w-full border rounded px-2 py-1 text-sm" 
                                   min="1" 
                                   max="${remaining}" 
                                   value="1" 
                                   data-unit-price="${unitPrice}"
                                   data-max="${remaining}"
                                   required>
                        </div>
                        <div>
                            <label class="text-xs text-gray-600">Đơn giá</label>
                            <div class="text-sm font-semibold text-gray-700 mt-1">${new Intl.NumberFormat('vi-VN').format(unitPrice)} đ</div>
                        </div>
                    </div>
                    <input type="hidden" name="adjustments[${refundItemIndex}][booking_service_id]" value="${bsId}">
                    <input type="hidden" name="adjustments[${refundItemIndex}][used_at]" value="${usedAt}">
                    <input type="text" name="adjustments[${refundItemIndex}][note]" class="w-full border rounded px-2 py-1 text-xs mt-2" placeholder="Ghi chú (tùy chọn)">
                `;

                refundItemsContainer.appendChild(itemDiv);
                refundItemIndex++;
                this.value = '';

                // Add remove handler
                itemDiv.querySelector('.remove-refund-item').addEventListener('click', function() {
                    itemDiv.remove();
                    reindexRefundItems();
                    updateRefundTotal();
                });

                // Add quantity change handler
                const qtyInput = itemDiv.querySelector('.refund-qty');
                qtyInput.addEventListener('input', function() {
                    updateRefundTotal();
                });

                updateRefundTotal();
            });
        }

        function reindexRefundItems() {
            const items = refundItemsContainer.querySelectorAll('.refund-item');
            items.forEach((item, idx) => {
                const qtyInput = item.querySelector('.refund-qty');
                const bsIdInput = item.querySelector('input[name*="[booking_service_id]"]');
                const usedAtInput = item.querySelector('input[name*="[used_at]"]');
                const noteInput = item.querySelector('input[name*="[note]"]');

                if (qtyInput) qtyInput.name = `adjustments[${idx}][quantity]`;
                if (bsIdInput) bsIdInput.name = `adjustments[${idx}][booking_service_id]`;
                if (usedAtInput) usedAtInput.name = `adjustments[${idx}][used_at]`;
                if (noteInput) noteInput.name = `adjustments[${idx}][note]`;
            });
            refundItemIndex = items.length;
        }

        function updateRefundTotal() {
            let total = 0;
            refundItemsContainer.querySelectorAll('.refund-item').forEach(item => {
                const qtyInput = item.querySelector('.refund-qty');
                const unitPrice = parseFloat(qtyInput?.dataset.unitPrice || 0);
                const qty = parseInt(qtyInput?.value || 0, 10);
                total += unitPrice * qty;
            });
            refundTotalDisplay.textContent = new Intl.NumberFormat('vi-VN').format(total) + ' đ';
        }

        // Show/hide bank fields based on refund method
        if (refundMethodSelect && refundBankFields) {
            refundMethodSelect.addEventListener('change', function() {
                if (this.value === 'chuyen_khoan') {
                    refundBankFields.classList.remove('hidden');
                    refundBankFields.querySelectorAll('input').forEach(input => {
                        input.setAttribute('required', 'required');
                    });
                } else {
                    refundBankFields.classList.add('hidden');
                    refundBankFields.querySelectorAll('input').forEach(input => {
                        input.removeAttribute('required');
                        input.value = '';
                    });
                }
            });
        }

        // Form validation
        if (refundForm) {
            refundForm.addEventListener('submit', function(e) {
                const items = refundItemsContainer.querySelectorAll('.refund-item');
                if (items.length === 0) {
                    e.preventDefault();
                    alert('Vui lòng chọn ít nhất một dịch vụ để hoàn tiền.');
                    return;
                }

                // Validate quantities
                let hasError = false;
                items.forEach(item => {
                    const qtyInput = item.querySelector('.refund-qty');
                    const max = parseInt(qtyInput?.dataset.max || 0, 10);
                    const qty = parseInt(qtyInput?.value || 0, 10);
                    
                    if (qty <= 0 || qty > max) {
                        hasError = true;
                    }
                });

                if (hasError) {
                    e.preventDefault();
                    alert('Vui lòng kiểm tra lại số lượng đã nhập.');
                    return;
                }

                // Validate bank fields if chuyen_khoan
                if (refundMethodSelect?.value === 'chuyen_khoan') {
                    const accountNumber = refundBankFields.querySelector('input[name="refund_account_number"]');
                    const accountName = refundBankFields.querySelector('input[name="refund_account_name"]');
                    const bankName = refundBankFields.querySelector('input[name="refund_bank_name"]');
                    
                    if (!accountNumber?.value || !accountName?.value || !bankName?.value) {
                        e.preventDefault();
                        alert('Vui lòng điền đầy đủ thông tin ngân hàng khi chọn phương thức chuyển khoản.');
                        return;
                    }
                }
            });
        }
    })();
</script>

@endsection
