@extends('layouts.admin')

@section('title', 'Hóa đơn #' . $invoice->id)

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body.invoice-printing {
            font-family: 'Roboto', 'Segoe UI', system-ui, sans-serif;
            background: #f7fafc;
        }

        .invoice-print {
            color: #1a202c;
        }

        .invoice-print h1,
        .invoice-print h2,
        .invoice-print h3 {
            font-family: 'Roboto', 'Segoe UI', system-ui, sans-serif;
        }

        .no-print {
            display: block;
        }

        @media print {
            body {
                font-family: 'Roboto', 'Segoe UI', system-ui, sans-serif !important;
                background: #fff !important;
            }

            .no-print {
                display: none !important;
            }

            .invoice-page {
                background: #fff !important;
                padding: 0 !important;
                box-shadow: none !important;
            }

            .invoice-print {
                box-shadow: none !important;
                border: none !important;
            }

            .invoice-print table {
                border-color: #cbd5f5 !important;
            }

            .invoice-print dl,
            .invoice-print div,
            .invoice-print table {
                break-inside: avoid;
            }

            @page {
                size: A4;
                margin: 12mm;
            }
        }
    </style>
@endpush

@section('admin_content')
    @php
        $booking = $invoice->datPhong;

        $nights = 1;
        if ($booking && $booking->ngay_nhan && $booking->ngay_tra) {
            $nights = max(
                1,
                \Carbon\Carbon::parse($booking->ngay_nhan)->diffInDays(\Carbon\Carbon::parse($booking->ngay_tra)),
            );
        }

        $roomLines = [];
        $roomTotal = 0;
        if ($booking) {
            foreach ($booking->getRoomTypes() as $rt) {
                $loaiPhong = \App\Models\LoaiPhong::find($rt['loai_phong_id']);
                if (!$loaiPhong) {
                    continue;
                }

                $giaCoBan = $loaiPhong->gia_co_ban ?? 0;
                $giaKhuyenMai = $loaiPhong->gia_khuyen_mai ?? null;
                $giaPhong = $giaKhuyenMai && $giaKhuyenMai < $giaCoBan ? $giaKhuyenMai : $giaCoBan;
                $soLuong = (int) ($rt['so_luong'] ?? 1);
                $thanhTien = $giaPhong * $soLuong * $nights;
                $roomTotal += $thanhTien;

                $roomLines[] = [
                    'name' => $loaiPhong->ten_loai ?? 'N/A',
                    'quantity' => $soLuong,
                    'base' => $giaCoBan,
                    'promo' => $giaKhuyenMai,
                    'unit' => $giaPhong,
                    'subtotal' => $thanhTien,
                ];
            }
        }

        $services = collect();
        if ($booking) {
            $serviceQuery = \App\Models\BookingService::with('service')
                ->where('dat_phong_id', $booking->id)
                ->orderBy('used_at');

            if ($invoice->isExtra()) {
                // Extra invoice: services linked directly to this invoice
                $serviceQuery->where('invoice_id', $invoice->id);
            } else {
                // Main invoice: include booking-scoped services (invoice_id IS NULL)
                // as well as any services already linked to this invoice (invoice_id == $invoice->id)
                $serviceQuery->where(function($q) use ($invoice) {
                    $q->whereNull('invoice_id')
                      ->orWhere('invoice_id', $invoice->id);
                });
            }

            $services = $serviceQuery->get();
        }

        $servicesTotal = $services->reduce(function ($carry, $item) {
            return $carry + (($item->quantity ?? 0) * ($item->unit_price ?? 0));
        }, 0);

        $voucher = $booking && !$invoice->isExtra() ? $booking->voucher : null;
        $discount = 0;
        if ($voucher && $voucher->gia_tri) {
            // If voucher applies to a specific room type, compute subtotal for that type only
            if (!empty($voucher->loai_phong_id)) {
                $applicableTotal = 0;
                foreach ($booking->getRoomTypes() as $rt) {
                    $lpId = $rt['loai_phong_id'] ?? null;
                    if ($lpId && $lpId == $voucher->loai_phong_id) {
                        $loaiPhong = \App\Models\LoaiPhong::find($lpId);
                        if (!$loaiPhong) continue;
                        $giaCoBan = $loaiPhong->gia_co_ban ?? 0;
                        $giaKhuyenMai = $loaiPhong->gia_khuyen_mai ?? null;
                        $giaPhong = $giaKhuyenMai && $giaKhuyenMai < $giaCoBan ? $giaKhuyenMai : $giaCoBan;
                        $soLuong = (int) ($rt['so_luong'] ?? 1);
                        $applicableTotal += $giaPhong * $soLuong * $nights;
                    }
                }
                $discount = round($applicableTotal * ($voucher->gia_tri / 100));
            } else {
                // Applies to all rooms
                $discount = round($roomTotal * ($voucher->gia_tri / 100));
            }
        }

        $calculatedTotal = max(0, $roomTotal + $servicesTotal - $discount);
        $displayTotal = $invoice->tong_tien ?? $calculatedTotal;
    @endphp

    <div class="py-6 bg-gray-100 min-h-screen invoice-page">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 invoice-print">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500">HÓA ĐƠN</p>
                    <h1 class="text-3xl font-bold text-gray-900">#{{ $invoice->id }}</h1>
                </div>
                <div class="flex flex-wrap gap-3 no-print">
                    <button onclick="document.body.classList.add('invoice-printing'); window.print(); setTimeout(() => document.body.classList.remove('invoice-printing'), 1000);"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        In hóa đơn
                    </button>

                    @if (!in_array($invoice->trang_thai, ['da_thanh_toan', 'hoan_tien']))
                        <a href="{{ route('admin.invoices.edit', $invoice->id) }}"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold rounded-lg shadow">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Chỉnh sửa
                        </a>
                    @endif

                    @if ($invoice->trang_thai === 'da_thanh_toan' && !$invoice->isExtra())
                        <a href="{{ route('admin.invoices.create_extra', $invoice->id) }}"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Hóa đơn phát sinh
                        </a>
                    @endif

                    <a href="{{ route('admin.invoices.export', $invoice->id) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg shadow">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Xuất Excel
                    </a>

                    @if(!$invoice->isExtra() && $invoice->trang_thai === 'da_thanh_toan')
                        <a href="{{ route('admin.invoices.export_combined', $invoice->id) }}"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7v4a1 1 0 001 1h3m10 0h3a1 1 0 001-1V7m-7 10v-4m0 0H8m7 4h2a2 2 0 002-2v-3a2 2 0 00-2-2h-5m-6 0H5a2 2 0 00-2 2v3a2 2 0 002 2h2m6-3v3" />
                            </svg>
                            Xuất hóa đơn tổng
                        </a>
                    @endif

                    <a href="{{ route('admin.invoices.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50">
                        Quay lại
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white px-6 py-5 grid gap-4 md:grid-cols-3 text-center">
                    <div>
                        <p class="text-blue-100 text-sm uppercase tracking-wide">Mã hóa đơn</p>
                        <p class="text-2xl font-bold">{{ $invoice->id }}</p>
                    </div>
                    <div>
                        <p class="text-blue-100 text-sm uppercase tracking-wide">Ngày tạo</p>
                        <p class="text-2xl font-bold">
                            {{ $invoice->ngay_tao ? \Carbon\Carbon::parse($invoice->ngay_tao)->format('d/m/Y') : 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-blue-100 text-sm uppercase tracking-wide">Trạng thái</p>
                        <span
                            class="inline-flex items-center justify-center px-4 py-1 rounded-full text-sm font-semibold
                                @if ($invoice->trang_thai === 'da_thanh_toan') bg-green-200 text-green-900
                                @elseif($invoice->trang_thai === 'hoan_tien') bg-red-200 text-red-900
                                @else bg-yellow-200 text-yellow-900 @endif">
                            {{ strtoupper(str_replace('_', ' ', $invoice->trang_thai)) }}
                        </span>
                    </div>
                </div>

                <div class="p-6 space-y-8">
                    <div class="grid gap-6 md:grid-cols-3">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-sm text-gray-500 uppercase font-semibold mb-1">Khách hàng</p>
                            @if ($booking)
                                <p class="text-lg font-semibold text-gray-900">{{ $booking->username }}</p>
                                <p class="text-sm text-gray-600">{{ $booking->email }}</p>
                                <p class="text-sm text-gray-600">{{ $booking->sdt }}</p>
                                @if($booking->cccd)
                                    <p class="text-sm text-gray-600 mt-2">CCCD: <span class="font-medium">{{ $booking->cccd }}</span></p>
                                @endif
                            @else
                                <p class="text-sm text-gray-500 italic">Không có thông tin</p>
                            @endif
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-sm text-gray-500 uppercase font-semibold mb-1">Đặt phòng</p>
                            @if ($booking)
                                <p class="text-sm text-gray-700">Check-in:
                                    <span class="font-semibold">{{ date('d/m/Y', strtotime($booking->ngay_nhan)) }}</span>
                                </p>
                                <p class="text-sm text-gray-700">Check-out:
                                    <span class="font-semibold">{{ date('d/m/Y', strtotime($booking->ngay_tra)) }}</span>
                                </p>
                                <p class="text-sm text-gray-700">Số đêm: <span class="font-semibold">{{ $nights }}</span>
                                </p>
                                <p class="text-sm text-gray-700">Số người: <span class="font-semibold">{{ $booking->so_nguoi }}</span>
                                </p>
                            @else
                                <p class="text-sm text-gray-500 italic">Không có thông tin</p>
                            @endif
                        </div>
                        @if($voucher)
                            <div class="bg-green-50 rounded-xl p-4 border border-green-200">
                                <p class="text-sm text-green-700 uppercase font-semibold mb-2">Mã giảm giá</p>
                                <p class="text-xl font-bold text-green-600">{{ $voucher->ma_voucher }}</p>
                                <p class="text-sm text-green-700 mt-2">Giảm giá: <span class="font-semibold">{{ $voucher->gia_tri }}%</span></p>
                                <p class="text-xs text-gray-600 mt-2">Tiết kiệm: <span class="font-medium text-red-600">-{{ number_format($discount, 0, ',', '.') }}₫</span></p>
                            </div>
                        @endif
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-3">Chi tiết phòng</h3>
                        @if (empty($roomLines))
                            <div class="border-2 border-dashed border-gray-200 rounded-lg py-6 text-center text-gray-500">
                                Không có thông tin phòng
                            </div>
                        @else
                            <div class="border rounded-lg overflow-hidden">
                                <div class="bg-gray-100 px-4 py-2 grid grid-cols-12 text-xs font-semibold text-gray-600">
                                    <div class="col-span-3">Loại phòng</div>
                                    <div class="col-span-2 text-center">Số lượng</div>
                                    <div class="col-span-2 text-center">Giá/đêm</div>
                                    <div class="col-span-2 text-center">Số đêm</div>
                                    <div class="col-span-3 text-right">Thành tiền</div>
                                </div>
                                @foreach ($roomLines as $line)
                                    <div class="px-4 py-3 grid grid-cols-12 text-sm border-t">
                                        <div class="col-span-3 font-medium text-gray-900">{{ $line['name'] }}</div>
                                        <div class="col-span-2 text-center">{{ $line['quantity'] }} phòng</div>
                                        <div class="col-span-2 text-center">
                                            @if ($line['promo'] && $line['promo'] < $line['base'])
                                                <div class="flex flex-col text-xs items-center">
                                                    <span class="text-gray-400 line-through">{{ number_format($line['base'], 0, ',', '.') }}₫</span>
                                                    <span class="text-red-600 font-semibold">{{ number_format($line['promo'], 0, ',', '.') }}₫</span>
                                                </div>
                                            @else
                                                <span>{{ number_format($line['unit'], 0, ',', '.') }}₫</span>
                                            @endif
                                        </div>
                                        <div class="col-span-2 text-center">{{ $nights }}</div>
                                        <div class="col-span-3 text-right font-semibold text-gray-900">
                                            {{ number_format($line['subtotal'], 0, ',', '.') }}₫
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            {{-- Phòng đã gán --}}
                            @if($booking)
                                @php
                                    $assignedPhongs = $booking->getAssignedPhongs();
                                @endphp
                                @if($assignedPhongs->count() > 0)
                                    <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                        <p class="text-sm font-semibold text-blue-900 mb-3">Phòng đã gán ({{ $assignedPhongs->count() }}/{{ $booking->so_luong_da_dat }}):</p>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                            @foreach($assignedPhongs as $phong)
                                                <div class="p-3 bg-white border border-blue-300 rounded text-center">
                                                    <p class="text-sm font-semibold text-gray-900">{{ $phong->so_phong ?? 'N/A' }}</p>
                                                    <p class="text-xs text-gray-600">{{ $phong->loaiPhong->ten_loai ?? 'N/A' }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endif
                        @endif
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-3">Dịch vụ phát sinh</h3>
                        @if ($services->isEmpty())
                            <div class="border-2 border-dashed border-gray-200 rounded-lg py-6 text-center text-gray-500">
                                Không có dịch vụ kèm theo
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm border rounded-lg overflow-hidden">
                                    <thead>
                                        <tr class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                                            <th class="px-4 py-3 text-left font-semibold">Dịch vụ</th>
                                            <th class="px-4 py-3 text-center font-semibold">Ngày dùng</th>
                                            <th class="px-4 py-3 text-center font-semibold">Áp dụng</th>
                                            <th class="px-4 py-3 text-right font-semibold">Số lượng</th>
                                            <th class="px-4 py-3 text-right font-semibold">Đơn giá</th>
                                            <th class="px-4 py-3 text-right font-semibold">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach ($services as $serviceLine)
                                            @php
                                                $svc = $serviceLine->service;
                                                $qty = $serviceLine->quantity ?? 0;
                                                $unit = $serviceLine->unit_price ?? 0;
                                                $lineTotal = $qty * $unit;
                                                
                                                // Determine if service is for all rooms or specific rooms
                                                $isGlobal = !$serviceLine->phong_id;
                                                $phongLabel = 'Tất cả phòng';
                                                if (!$isGlobal && $serviceLine->phong) {
                                                    $phongLabel = 'P. ' . ($serviceLine->phong->so_phong ?? 'N/A');
                                                }
                                            @endphp
                                            <tr class="hover:bg-blue-50">
                                                <td class="px-4 py-3 text-gray-900 font-medium">{{ $svc->name ?? $serviceLine->service_name ?? 'N/A' }}</td>
                                                <td class="px-4 py-3 text-center text-gray-600">
                                                    {{ $serviceLine->used_at ? date('d/m/Y', strtotime($serviceLine->used_at)) : '-' }}
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $isGlobal ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                        {{ $phongLabel }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-right text-gray-700">{{ $qty }}</td>
                                                <td class="px-4 py-3 text-right text-gray-700">{{ number_format($unit, 0, ',', '.') }}₫</td>
                                                <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                                    {{ number_format($lineTotal, 0, ',', '.') }}₫
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Tổng dịch vụ --}}
                            <div class="mt-4 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                                <div class="flex justify-between items-center">
                                    <p class="text-sm font-semibold text-gray-900">Tổng tiền dịch vụ:</p>
                                    <p class="text-lg font-bold text-purple-700">{{ number_format($servicesTotal, 0, ',', '.') }}₫</p>
                                </div>
                                <p class="text-xs text-gray-600 mt-2">Số dịch vụ: <span class="font-medium">{{ $services->count() }} mục</span></p>
                            </div>
                        @endif
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="bg-white border rounded-xl p-5 shadow-sm space-y-2">
                            <h3 class="text-base font-bold text-gray-900 mb-4">Tóm tắt thanh toán</h3>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Tiền phòng</span>
                                <span class="font-semibold text-gray-900">{{ number_format($roomTotal, 0, ',', '.') }}₫</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Dịch vụ</span>
                                <span class="font-semibold text-gray-900">{{ number_format($servicesTotal, 0, ',', '.') }}₫</span>
                            </div>
                            @if ($discount > 0)
                                <div class="flex justify-between text-sm text-red-600">
                                    <span>Giảm giá @if ($voucher) ({{ $voucher->ma_voucher }} - {{ $voucher->gia_tri }}%) @endif</span>
                                    <span class="font-semibold">-{{ number_format($discount, 0, ',', '.') }}₫</span>
                                </div>
                            @endif
                            <div class="border-t pt-3 mt-3 flex justify-between items-center">
                                <span class="text-base font-semibold text-gray-700">Tổng cần thanh toán</span>
                                <span class="text-2xl font-bold text-blue-600">{{ number_format($displayTotal, 0, ',', '.') }}₫</span>
                            </div>
                            @if ($invoice->tong_tien && $invoice->tong_tien != $calculatedTotal)
                                <p class="text-xs text-gray-500">
                                    (Đã lưu trong hệ thống: {{ number_format((float)$invoice->tong_tien, 0, ',', '.') }}₫)
                                </p>
                            @endif
                        </div>

                        <div class="bg-white border rounded-xl p-5 shadow-sm space-y-4">
                            <h3 class="text-base font-bold text-gray-900 mb-4">Chi tiết hóa đơn</h3>
                            
                            <div>
                                <p class="text-sm text-gray-500 uppercase font-semibold mb-2">Trạng thái thanh toán</p>
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                        @if ($invoice->trang_thai === 'da_thanh_toan') bg-green-100 text-green-800
                                        @elseif($invoice->trang_thai === 'hoan_tien') bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ $invoice->trang_thai === 'da_thanh_toan' ? 'Đã thanh toán' : ($invoice->trang_thai === 'hoan_tien' ? 'Hoàn tiền' : 'Chờ thanh toán') }}
                                </span>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-600">
                                    Phương thức:
                                    <span class="font-semibold text-gray-900">{{ $invoice->phuong_thuc ? strtoupper(str_replace('_', ' ', $invoice->phuong_thuc)) : 'N/A' }}</span>
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-600">
                                    Ngày tạo:
                                    <span class="font-semibold text-gray-900">{{ $invoice->ngay_tao ? \Carbon\Carbon::parse($invoice->ngay_tao)->format('d/m/Y H:i') : 'N/A' }}</span>
                                </p>
                            </div>

                            @if ($invoice->trang_thai === 'cho_thanh_toan')
                                <form action="{{ route('admin.invoices.update', $invoice->id) }}" method="POST" class="pt-3 border-t space-y-2 no-print">
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
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection