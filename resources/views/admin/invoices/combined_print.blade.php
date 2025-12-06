@extends('layouts.admin')

@section('title', 'Hóa đơn tổng #' . $invoice->id)

@section('admin_content')
<div class="py-6 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto bg-white border rounded-lg shadow p-6">
        {{-- Header with hotel info and invoice number --}}
        <div class="flex items-start justify-between mb-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-blue-600 flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 21v-8a2 2 0 012-2h14a2 2 0 012 2v8M7 10V7a2 2 0 012-2h6a2 2 0 012 2v3" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">OZIA HOTEL</h2>
                    <p class="text-sm text-gray-500">123 Đường ABC, Quận 1, TP.HCM</p>
                    <p class="text-sm text-gray-500">ĐT: (028) 1234 5678</p>
                </div>
            </div>

            <div class="text-right">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-gray-100 rounded-md">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6M7 21h10" />
                    </svg>
                    <div class="text-sm text-gray-700">
                        <div class="font-semibold">HÓA ĐƠN TỔNG</div>
                        <div class="text-xs">Số: #{{ $invoice->id }}</div>
                        <div class="text-xs">Ngày: {{ $invoice->ngay_tao ? \Carbon\Carbon::parse($invoice->ngay_tao)->format('d/m/Y') : now()->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action buttons (no-print) --}}
        <div class="mb-6 flex flex-wrap gap-2 no-print">
            <button onclick="window.print();"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                In (ESC/POS)
            </button>
            <a href="{{ route('admin.invoices.export', $invoice->id) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Xuất Excel
            </a>
            <a href="{{ route('admin.invoices.show', $invoice->id) }}"
                class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Quay lại
            </a>
        </div>

        {{-- Main invoice block --}}
        <div class="mb-6 border rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6M7 6h10v12H7z" />
                        </svg>
                        <h4 class="font-semibold">Hóa đơn chính</h4>
                    </div>
                    <div class="text-sm">Tổng: <span class="font-bold text-white">{{ number_format($invoice->tong_tien,0,',','.') }} ₫</span></div>
                </div>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-xs text-gray-500">Khách hàng</p>
                        <p class="font-medium text-gray-800">{{ $invoice->datPhong?->username ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500">{{ $invoice->datPhong?->email ?? '' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500">Tổng tiền hóa đơn chính</p>
                        <div class="inline-flex items-baseline gap-2">
                            <span class="text-sm text-gray-500">VND</span>
                            <div class="bg-blue-50 text-blue-800 px-3 py-1 rounded font-semibold">{{ number_format($invoice->tong_tien,0,',','.') }} ₫</div>
                        </div>
                    </div>
                </div>

                {{-- Room summary --}}
                @if($invoice->datPhong)
                    @php
                        $booking = $invoice->datPhong;
                        $nights = 1;
                        if($booking->ngay_nhan && $booking->ngay_tra) {
                            $nights = max(1, \Carbon\Carbon::parse($booking->ngay_nhan)->diffInDays(\Carbon\Carbon::parse($booking->ngay_tra)));
                        }
                        $roomTypes = $booking->getRoomTypes();
                        $assignedPhongs = $booking->getAssignedPhongs();
                        $totalRoomCount = 0;
                        $roomTypeCount = count($roomTypes);
                        foreach($roomTypes as $rt) {
                            $totalRoomCount += (int)($rt['so_luong'] ?? 1);
                        }
                    @endphp

                    <div class="bg-blue-50 rounded p-3 mb-4">
                        <div class="grid grid-cols-3 gap-3 text-sm">
                            <div>
                                <p class="text-xs text-gray-600">Số loại phòng</p>
                                <p class="text-lg font-bold text-blue-700">{{ $roomTypeCount }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600">Tổng số phòng</p>
                                <p class="text-lg font-bold text-blue-700">{{ $totalRoomCount }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600">Số phòng riêng</p>
                                <p class="text-lg font-bold text-blue-700">{{ $assignedPhongs->count() }}/{{ $totalRoomCount }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="text-sm text-gray-700 mb-3">
                        <p class="font-semibold mb-2">Chi tiết phòng:</p>
                        <ul class="list-disc ml-5 text-sm space-y-1">
                            @foreach($roomTypes as $rt)
                                @php $lp = \App\Models\LoaiPhong::find($rt['loai_phong_id']); @endphp
                                <li>{{ $lp?->ten_loai ?? 'N/A' }} — {{ $rt['so_luong'] }} phòng × {{ $nights }} đêm</li>
                            @endforeach
                        </ul>
                    </div>

                    @if($assignedPhongs->count() > 0)
                        <div class="text-sm text-gray-700">
                            <p class="font-semibold mb-2">Phòng đã gán:</p>
                            <div class="grid grid-cols-4 gap-2">
                                @foreach($assignedPhongs as $phong)
                                    <div class="bg-gray-100 px-2 py-1 rounded text-center">
                                        <p class="font-medium">{{ $phong->so_phong ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-500">{{ $phong->loaiPhong?->ten_loai ?? 'N/A' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- Extras list --}}
        <div class="mb-6 border rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white px-4 py-3">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3" />
                    </svg>
                    <h4 class="font-semibold">Hóa đơn phát sinh (Đã thanh toán)</h4>
                </div>
            </div>
            <div class="p-4">
                @if($extras->isEmpty())
                    <p class="text-sm text-gray-500">Không có hóa đơn phát sinh đã thanh toán.</p>
                @else
                    <div class="space-y-4">
                        @foreach($extras as $ex)
                            <div class="border rounded p-3 flex justify-between items-start">
                                <div class="pr-4 flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <p class="font-medium">Hóa đơn #{{ $ex->id }}</p>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800">
                                            Đã thanh toán
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mb-2">Ngày: {{ $ex->ngay_tao ? \Carbon\Carbon::parse($ex->ngay_tao)->format('d/m/Y') : 'N/A' }}</p>

                                    @php
                                        $svcRows = \App\Models\BookingService::where('invoice_id', $ex->id)->get();
                                    @endphp
                                    @if($svcRows->isNotEmpty())
                                        <ul class="list-disc ml-5 text-sm space-y-1">
                                            @foreach($svcRows as $s)
                                                <li>{{ $s->service?->name ?? $s->service_name }} — {{ $s->quantity ?? 0 }} × {{ number_format($s->unit_price ?? 0,0,',','.') }} ₫</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <div class="bg-purple-50 text-purple-800 px-3 py-1 rounded font-semibold">{{ number_format($ex->tong_tien,0,',','.') }} ₫</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Combined total --}}
        <div class="text-right mb-6">
            <p class="text-sm text-gray-500">Tổng (chính + phát sinh)</p>
            <div class="mt-2 inline-flex items-center gap-3">
                <div class="text-2xl font-bold text-gray-800">{{ number_format($combinedTotal,0,',','.') }} ₫</div>
                <div class="bg-emerald-50 text-emerald-800 px-4 py-2 rounded font-bold">TỔNG</div>
            </div>
        </div>

        <div class="text-sm text-gray-500 text-center pb-4">
            <p>In / Xuất file này làm bằng chứng thanh toán. Xin cảm ơn quý khách.</p>
        </div>
    </div>
</div>

@push('styles')
    <style>
        .no-print {
            display: block;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                background: #fff !important;
            }
            
            .max-w-4xl {
                max-width: 100% !important;
            }
        }
    </style>
@endpush
@endsection

