@extends('layouts.admin')

@section('admin_content')
<?php
    $booking = $invoice->datPhong;
    $nights = 1;
    if($booking && $booking->ngay_nhan && $booking->ngay_tra) {
        $nights = \Carbon\Carbon::parse($booking->ngay_nhan)->diffInDays(\Carbon\Carbon::parse($booking->ngay_tra));
    }
    
    // Tính tổng tiền gốc từ LoaiPhong (giá chưa giảm)
    $subtotal = 0;
    if($booking) {
        foreach($booking->getRoomTypes() as $rt) {
            $loaiPhong = \App\Models\LoaiPhong::find($rt['loai_phong_id']);
            if($loaiPhong) {
                $giaGoc = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban;
                $subtotal += $giaGoc * $rt['so_luong'] * $nights;
            }
        }
    }
    
    // Lấy thông tin voucher nếu có
    $voucher = null;
    $discount = 0;
    // Note: Discount will be calculated after services total is known
?>
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4">
        <div class="mb-6 flex justify-between">
            <a href="{{ route('admin.invoices.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg">Quay lại</a>
            <a href="{{ route('admin.invoices.print', $invoice->id) }}" target="_blank"
               class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700">In hóa đơn</a>
        </div>

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

                <div class="mb-8">
                    <h3 class="font-bold mb-4">Chi tiết phòng</h3>
                    <div class="border rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-6 py-3 grid grid-cols-12 gap-4 text-xs font-bold text-gray-600">
                            <div class="col-span-4">MÔ TẢ</div>
                            <div class="col-span-2 text-center">SỐ LƯỢNG</div>
                            <div class="col-span-2 text-center">GIÁ/ĐÊM</div>
                            <div class="col-span-2 text-center">SỐ ĐÊM</div>
                            <div class="col-span-2 text-right">THÀNH TIỀN</div>
                        </div>
                        <?php if($booking): ?>
                            <?php $roomTypes = $booking->getRoomTypes(); ?>
                            <?php foreach($roomTypes as $rt): ?>
                                <?php 
                                    $lp = \App\Models\LoaiPhong::find($rt['loai_phong_id']); 
                                    // Lấy giá/đêm từ LoaiPhong
                                    $giaCoBan = $lp ? $lp->gia_co_ban : 0;
                                    $giaKhuyenMai = $lp ? $lp->gia_khuyen_mai : null;
                                    $giaPhong = $giaKhuyenMai ?? $giaCoBan; // Giá thực tế áp dụng
                                    // Tính thành tiền: giá/đêm × số lượng × số đêm
                                    $thanhTien = $giaPhong * $rt['so_luong'] * $nights;
                                ?>
                                <div class="px-6 py-4 grid grid-cols-12 gap-4 border-t text-sm">
                                    <div class="col-span-4">{{ $lp->ten_loai ?? 'N/A' }}</div>
                                    <div class="col-span-2 text-center">{{ $rt['so_luong'] }} phòng</div>
                                    <div class="col-span-2 text-center">
                                        @if($giaKhuyenMai && $giaKhuyenMai < $giaCoBan)
                                            <div class="flex flex-col items-center">
                                                <span class="text-gray-400 line-through text-xs">{{ number_format($giaCoBan, 0, ',', '.') }} ₫</span>
                                                <span class="text-red-600 font-semibold">{{ number_format($giaKhuyenMai, 0, ',', '.') }} ₫</span>
                                            </div>
                                        @else
                                            <span>{{ number_format($giaPhong, 0, ',', '.') }} ₫</span>
                                        @endif
                                    </div>
                                    <div class="col-span-2 text-center">{{ $nights }} đêm</div>
                                    <div class="col-span-2 text-right font-bold">{{ number_format($thanhTien, 0, ',', '.') }} ₫</div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                {{-- Services Section --}}
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b-2 border-blue-500">Danh sách dịch vụ</h3>

                    @php
                        $services = collect();
                        if ($booking) {
                            $services = \App\Models\BookingService::with('service')
                                ->where('dat_phong_id', $booking->id)
                                ->orderBy('used_at')
                                ->get();
                        }
                        $servicesTotal = $services->reduce(function($carry, $item){
                            return $carry + (($item->quantity ?? 0) * ($item->unit_price ?? 0));
                        }, 0);
                    @endphp

                    @if($services->isEmpty())
                        <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg py-8 text-center">
                            <p class="text-gray-500 text-sm">Không có dịch vụ kèm theo</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full border rounded-lg overflow-hidden">
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
                                            $serviceSubtotal = $qty * $unitPrice;
                                        @endphp
                                        <tr class="hover:bg-blue-50 transition">
                                            <td class="px-6 py-4 text-gray-900">{{ $name }}</td>
                                            <td class="px-6 py-4 text-center text-gray-700">{{ $usedAt }}</td>
                                            <td class="px-6 py-4 text-right text-gray-700">{{ $qty }}</td>
                                            <td class="px-6 py-4 text-right text-gray-700">{{ number_format($unitPrice, 0, ',', '.') }} đ</td>
                                            <td class="px-6 py-4 text-right font-semibold text-gray-900">{{ number_format($serviceSubtotal, 0, ',', '.') }} đ</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Hiển thị breakdown từ invoice (đã được tính sẵn bởi BookingPriceCalculator) --}}
                <div class="border-t-2 pt-6">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-700">Tổng tiền phòng:</span>
                            <span class="font-semibold">{{ number_format($invoice->tien_phong ?? 0, 0, ',', '.') }} ₫</span>
                        </div>
                        
                        @if(($invoice->giam_gia ?? 0) > 0)
                            @php
                                $voucher = $booking->voucher;
                            @endphp
                            <div class="flex justify-between text-red-600">
                                <span>Giảm giá @if($voucher)({{ $voucher->ma_voucher }} - {{ $voucher->gia_tri }}%)@endif:</span>
                                <span class="font-semibold">-{{ number_format($invoice->giam_gia, 0, ',', '.') }} ₫</span>
                            </div>
                        @endif
                        
                        @if(($invoice->tien_dich_vu ?? 0) > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-700">Tổng tiền dịch vụ:</span>
                                <span class="font-semibold text-purple-600">{{ number_format($invoice->tien_dich_vu, 0, ',', '.') }} ₫</span>
                            </div>
                        @endif
                        
                        <div class="border-t my-2"></div>
                        
                        <div class="flex justify-between text-xl font-bold">
                            <span>Tổng thanh toán:</span>
                            <span class="text-blue-600">{{ number_format($invoice->tong_tien, 0, ',', '.') }} ₫</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


