@extends('layouts.admin')

@section('title','Chi tiết Hóa đơn')

@section('admin_content')
    <div class="py-6 bg-gray-100 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Header with Action Buttons --}}
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Hóa đơn #{{ $invoice->id }}</h1>
                <div class="flex gap-3">
                    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg flex items-center gap-2 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        In hóa đơn
                    </button>
                    @if(!in_array($invoice->trang_thai, ['da_thanh_toan', 'hoan_tien']))
                        <a href="{{ route('admin.invoices.edit', $invoice->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-6 rounded-lg flex items-center gap-2 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Chỉnh sửa
                        </a>
                    @endif

                    @if($invoice->trang_thai === 'da_thanh_toan' && !$invoice->isExtra())
                        {{-- Only allow creating an EXTRA invoice from a paid invoice that is NOT itself an EXTRA invoice --}}
                        <a href="{{ route('admin.invoices.create_extra', $invoice->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg flex items-center gap-2 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Thêm hóa đơn dịch vụ phát sinh
                        </a>
                    @endif
                    <a href="{{ route('admin.invoices.export', $invoice->id) }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg flex items-center gap-2 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Xuất Excel
                    </a>
                    <a href="{{ route('admin.invoices.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-lg transition">
                        Quay lại
                    </a>
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
                            <p class="text-2xl font-bold">{{ \Carbon\Carbon::parse($invoice->ngay_tao)->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <p class="text-blue-100 text-sm">Trạng thái</p>
                            <span class="inline-block mt-1 px-4 py-1 rounded-full text-sm font-semibold {{ $invoice->trang_thai == 'da_thanh_toan' ? 'bg-green-400 text-green-900' : 'bg-yellow-400 text-yellow-900' }}">
                                {{ $invoice->trang_thai == 'da_thanh_toan' ? '✓ Đã thanh toán' : 'Chờ thanh toán' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-8">
                    
                    {{-- Customer Info Section --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        {{-- Customer Info --}}
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b-2 border-blue-500">Thông tin khách hàng</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-gray-500 font-semibold">Họ và tên</p>
                                    <p class="text-gray-900">{{ $invoice->datPhong ? ($invoice->datPhong->username ?? ($invoice->datPhong->user->ho_ten ?? 'N/A')) : 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-semibold">Email</p>
                                    <p class="text-gray-900">{{ $invoice->datPhong ? ($invoice->datPhong->email ?? ($invoice->datPhong->user->email ?? 'N/A')) : 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-semibold">Số điện thoại</p>
                                    <p class="text-gray-900">{{ $invoice->datPhong ? ($invoice->datPhong->sdt ?? ($invoice->datPhong->user->sdt ?? 'N/A')) : 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Booking Info --}}
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b-2 border-blue-500">Thông tin đặt phòng</h3>
                            <div class="space-y-3">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500 font-semibold">Check-in</p>
                                        <p class="text-gray-900">{{ $invoice->datPhong ? \Carbon\Carbon::parse($invoice->datPhong->ngay_nhan)->format('d/m/Y') : 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 font-semibold">Check-out</p>
                                        <p class="text-gray-900">{{ $invoice->datPhong ? \Carbon\Carbon::parse($invoice->datPhong->ngay_tra)->format('d/m/Y') : 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500 font-semibold">Số phòng</p>
                                        <p class="text-gray-900 font-bold">{{ $invoice->datPhong ? ($invoice->datPhong->so_luong_da_dat ?? 1) : 1 }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 font-semibold">Số đêm</p>
                                        <p class="text-gray-900 font-bold">
                                            @if($invoice->datPhong && $invoice->datPhong->ngay_nhan && $invoice->datPhong->ngay_tra)
                                                {{ \Carbon\Carbon::parse($invoice->datPhong->ngay_nhan)->diffInDays(\Carbon\Carbon::parse($invoice->datPhong->ngay_tra)) }}
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-semibold">Số phòng cụ thể</p>
                                    <p class="text-gray-900 font-semibold">
                                        @php
                                            $booking = $invoice->datPhong;
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
                                    <p class="text-sm text-gray-500 font-semibold">Loại phòng & Số lượng</p>
                                    <p class="text-gray-900">
                                        @php
                                            $booking = $invoice->datPhong;
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

                        {{-- Services Section --}}
                        <div class="mt-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b-2 border-blue-500">Danh sách dịch vụ</h3>

                            @php
                            $booking = $invoice->datPhong;
                            $services = collect();
                            if ($booking) {
                                // Determine a reliable invoice-created timestamp: prefer 'ngay_tao', fallback to Eloquent 'created_at'
                                $invoiceCreatedAt = null;
                                try {
                                    if (!empty($invoice->ngay_tao)) {
                                        $invoiceCreatedAt = \Carbon\Carbon::parse($invoice->ngay_tao)->toDateTimeString();
                                    } elseif (!empty($invoice->created_at)) {
                                        $invoiceCreatedAt = \Carbon\Carbon::parse($invoice->created_at)->toDateTimeString();
                                    }
                                } catch (\Throwable $ex) {
                                    $invoiceCreatedAt = null;
                                }

                                if ($invoice->isExtra()) {
                                    // Chỉ hiển thị dịch vụ thuộc về hóa đơn phát sinh này
                                    $services = \App\Models\BookingService::with('service')
                                        ->where('dat_phong_id', $booking->id)
                                        ->where('invoice_id', $invoice->id)
                                        ->orderBy('used_at')
                                        ->get();
                                } else {
                                    // Hiển thị tất cả dịch vụ booking-level (invoice_id NULL) cho hóa đơn chính
                                    $services = \App\Models\BookingService::with('service')
                                        ->where('dat_phong_id', $booking->id)
                                        ->whereNull('invoice_id')
                                        ->orderBy('used_at')
                                        ->get();
                                }
                            }
                            $servicesTotal = $services->reduce(function($carry, $item){
                                return $carry + (($item->quantity ?? 0) * ($item->unit_price ?? 0));
                            }, 0);
                            
                            // Calculate room total from booking using promotional prices (same as BookingPriceCalculator)
                            $roomTotal = 0;
                            if ($booking) {
                                if ($invoice->isExtra()) {
                                    // For EXTRA invoice, room price should not be included
                                    $roomTotal = 0;
                                } else {
                                $nights = max(1, \Carbon\Carbon::parse($booking->ngay_nhan)->diffInDays(\Carbon\Carbon::parse($booking->ngay_tra)));
                                $roomTypes = $booking->getRoomTypes();
                                foreach ($roomTypes as $rt) {
                                    $qty = (int) ($rt['so_luong'] ?? 1);
                                    $loaiPhongId = (int) ($rt['loai_phong_id'] ?? 0);
                                    $loaiPhong = \App\Models\LoaiPhong::find($loaiPhongId);
                                    $unit = 0;
                                    if ($loaiPhong) {
                                        $unit = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban ?? 0;
                                    }
                                        $roomTotal += $qty * $unit * $nights;
                                    }
                                }
                            }
                        @endphp

                            @if($services->isEmpty())
                                <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg py-8 text-center">
                                    <p class="text-gray-500 text-sm">Không có dịch vụ kèm theo</p>
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead>
                                            <tr class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                                                <th class="px-6 py-4 text-left font-semibold">DỊCH VỤ</th>
                                                <th class="px-6 py-4 text-center font-semibold">NGÀY DÙNG</th>
                                                <th class="px-6 py-4 text-right font-semibold">SỐ LƯỢNG</th>
                                                <th class="px-6 py-4 text-right font-semibold">ĐƠN GIÁ</th>
                                                <th class="px-6 py-4 text-right font-semibold">THÀNH TIỀN</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @foreach($services as $s)
                                                @php
                                                    $svc = $s->service;
                                                    $name = $svc ? ($svc->name ?? 'N/A') : ($s->service_name ?? 'N/A');
                                                    $usedAt = $s->used_at ? date('d/m/Y', strtotime($s->used_at)) : '-';
                                                    $qty = $s->quantity ?? 0;
                                                    $unitPrice = $s->unit_price ?? 0;
                                                    $subtotal = $qty * $unitPrice;
                                                @endphp
                                                <tr class="hover:bg-blue-50 transition">
                                                    <td class="px-6 py-4 text-gray-900">{{ $name }}</td>
                                                    <td class="px-6 py-4 text-center text-gray-700">{{ $usedAt }}</td>
                                                    <td class="px-6 py-4 text-right text-gray-700">{{ $qty }}</td>
                                                    <td class="px-6 py-4 text-right text-gray-700">{{ number_format($unitPrice, 0, ',', '.') }} đ</td>
                                                    <td class="px-6 py-4 text-right font-semibold text-gray-900">{{ number_format($subtotal, 0, ',', '.') }} đ</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                    {{-- Summary Section --}}
                    <div class="mt-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            {{-- Empty space on left --}}
                            <div></div>
                            
                            {{-- Summary Box on Right --}}
                            <div class="bg-gradient-to-br from-blue-50 to-gray-50 rounded-lg border-2 border-blue-200 p-6">
                                <div class="space-y-4">
                                    {{-- Room fee --}}
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-700 font-medium">Tiền phòng</span>
                                        <span class="text-gray-900 font-semibold">{{ number_format($roomTotal, 0, ',', '.') }} đ</span>
                                    </div>

                                    {{-- Services total --}}
                                    <div class="flex justify-between items-center pb-4 border-b-2 border-blue-300">
                                        <span class="text-gray-700 font-medium">Tổng dịch vụ</span>
                                        <span class="text-green-600 font-semibold">{{ number_format($servicesTotal, 0, ',', '.') }} đ</span>
                                    </div>

                                    {{-- Total --}}
                                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg p-4 text-white">
                                        <div class="flex justify-between items-center">
                                            <span class="font-bold text-lg">TỔNG THANH TOÁN</span>
                                            <span class="text-2xl font-bold">{{ number_format($invoice->tong_tien ?? ($roomTotal + $servicesTotal), 0, ',', '.') }} đ</span>
                                        </div>
                                    </div>

                                    {{-- Payment method --}}
                                    <div>
                                        <p class="text-sm text-gray-600">Phương thức thanh toán</p>
                                        <p class="text-gray-900 font-semibold">{{ $invoice->phuong_thuc ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


