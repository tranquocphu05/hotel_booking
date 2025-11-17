@extends('layouts.admin')
@section('title', 'Chi tiết Hóa đơn #' . $invoice->id)

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
    if($booking && $booking->voucher_id) {
        $voucher = $booking->voucher;
        if($voucher) {
            $discount = $subtotal * ($voucher->gia_tri / 100);
        }
    }
?>
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4">
        <div class="mb-6 flex justify-between">
            <a href="{{ route('admin.invoices.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg">Quay lại</a>
            <a href="{{ route('admin.invoices.print', $invoice->id) }}" target="_blank"
               class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700">In hóa đơn</a>
        </div>

        <div class="bg-white rounded-lg shadow-lg">
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white p-8">
                <div class="flex justify-between">
                    <div>
                        <h1 class="text-3xl font-bold">OZIA Hotel</h1>
                        <p class="text-sm mt-2">123 Đường ABC, Quận 1, TP.HCM</p>
                        <p class="text-sm">(028) 1234 5678</p>
                    </div>
                    <div class="text-right">
                        <h2 class="text-2xl font-bold">HÓA ĐƠN</h2>
                        <p class="text-sm">#{{ $invoice->id }}</p>
                        <p class="text-sm">{{ $invoice->ngay_tao->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <div class="px-8 py-4 bg-gray-50 border-b">
                <?php $st = $invoice->trang_thai_ui; ?>
                <div class="flex justify-between">
                    <span>Trạng thái:</span>
                    <span class="px-4 py-2 rounded-full {{ $st['bg'] }} {{ $st['text'] }} font-semibold">{{ $st['label'] }}</span>
                </div>
            </div>

            <div class="p-8">
                <div class="grid md:grid-cols-2 gap-6 mb-8">
                    <div class="border-2 border-gray-300 rounded-lg p-4 bg-gray-50">
                        <h3 class="font-bold text-base mb-3 text-gray-800 uppercase">Khách hàng</h3>
                        <table class="w-full text-sm">
                            <tr class="border-b border-gray-300">
                                <td class="py-2 text-gray-600 w-24">Họ tên:</td>
                                <td class="py-2 font-semibold text-gray-900">{{ $invoice->datPhong->username ?? 'N/A' }}</td>
                            </tr>
                            <tr class="border-b border-gray-300">
                                <td class="py-2 text-gray-600">Email:</td>
                                <td class="py-2 text-gray-900">{{ $invoice->datPhong->email ?? 'N/A' }}</td>
                            </tr>
                            <tr class="border-b border-gray-300">
                                <td class="py-2 text-gray-600">SĐT:</td>
                                <td class="py-2 text-gray-900">{{ $invoice->datPhong->sdt ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600">CCCD:</td>
                                <td class="py-2 text-gray-900">{{ $invoice->datPhong->cccd ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="border-2 border-gray-300 rounded-lg p-4 bg-gray-50">
                        <h3 class="font-bold text-base mb-3 text-gray-800 uppercase">Đặt phòng</h3>
                        <table class="w-full text-sm">
                            <tr class="border-b border-gray-300">
                                <td class="py-2 text-gray-600 w-28">Số phòng:</td>
                                <td class="py-2 font-semibold text-gray-900">{{ $booking ? ($booking->so_luong_da_dat ?? 1) : 1 }} phòng</td>
                            </tr>
                            <tr class="border-b border-gray-300">
                                <td class="py-2 text-gray-600">Số người:</td>
                                <td class="py-2 text-gray-900">{{ $booking ? ($booking->so_nguoi ?? 'N/A') : 'N/A' }} người</td>
                            </tr>
                            <tr class="border-b border-gray-300">
                                <td class="py-2 text-gray-600">Nhận phòng:</td>
                                <td class="py-2 text-gray-900">{{ $booking ? date('d/m/Y', strtotime($booking->ngay_nhan)) : 'N/A' }}</td>
                            </tr>
                            <tr class="border-b border-gray-300">
                                <td class="py-2 text-gray-600">Trả phòng:</td>
                                <td class="py-2 text-gray-900">{{ $booking ? date('d/m/Y', strtotime($booking->ngay_tra)) : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600">Số đêm:</td>
                                <td class="py-2 font-semibold text-gray-900">{{ $nights }} đêm</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="font-bold mb-4">Chi tiết</h3>
                    <div class="border rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-6 py-3 grid grid-cols-12 gap-4 text-xs font-bold text-gray-600">
                            <div class="col-span-5">MÔ TẢ</div>
                            <div class="col-span-2 text-center">SỐ LƯỢNG</div>
                            <div class="col-span-2 text-center">SỐ ĐÊM</div>
                            <div class="col-span-3 text-right">THÀNH TIỀN</div>
                        </div>
                        <?php if($booking): ?>
                            <?php $roomTypes = $booking->getRoomTypes(); ?>
                            <?php foreach($roomTypes as $rt): ?>
                                <?php $lp = \App\Models\LoaiPhong::find($rt['loai_phong_id']); ?>
                                <div class="px-6 py-4 grid grid-cols-12 gap-4 border-t text-sm">
                                    <div class="col-span-5">{{ $lp->ten_loai ?? 'N/A' }}</div>
                                    <div class="col-span-2 text-center">{{ $rt['so_luong'] }} phòng</div>
                                    <div class="col-span-2 text-center">{{ $nights }} đêm</div>
                                    <div class="col-span-3 text-right font-bold">{{ number_format($rt['gia_rieng'] * $rt['so_luong'] * $nights, 0, ',', '.') }} ₫</div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="border-t-2 pt-6">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-700">Tổng tiền phòng:</span>
                            <span class="font-semibold">{{ number_format($subtotal, 0, ',', '.') }} ₫</span>
                        </div>
                        
                        <?php if($voucher): ?>
                            <div class="flex justify-between text-green-600">
                                <span>Giảm giá ({{ $voucher->ma_voucher }} - {{ $voucher->gia_tri }}%):</span>
                                <span class="font-semibold">-{{ number_format($discount, 0, ',', '.') }} ₫</span>
                            </div>
                            <div class="border-t my-2"></div>
                        <?php endif; ?>
                        
                        <div class="flex justify-between text-xl font-bold">
                            <span>Tổng thanh toán:</span>
                            <span class="text-blue-600">{{ number_format($invoice->tong_tien, 0, ',', '.') }} ₫</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t flex justify-between">
                    <span class="text-sm">Phương thức:</span>
                    <?php $pm = $invoice->phuong_thuc_ui; ?>
                    <span class="px-3 py-1 rounded-full text-sm {{ $pm['bg'] }} {{ $pm['text'] }}">{{ $pm['label'] }}</span>
                </div>
            </div>

            <div class="bg-gray-50 px-8 py-6 border-t text-center text-xs text-gray-500">
                <p>Cảm ơn quý khách đã sử dụng dịch vụ của OZIA Hotel!</p>
                <p class="mt-2">Liên hệ: (028) 1234 5678</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    /* Ẩn tất cả trừ nội dung hóa đơn */
    body > *:not(.admin-layout) {
        display: none !important;
    }
    
    .no-print,
    nav,
    aside,
    header,
    footer,
    .sidebar,
    .admin-sidebar,
    .admin-header,
    .admin-nav {
        display: none !important;
        visibility: hidden !important;
    }
    
    /* Reset layout */
    body,
    html {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
        width: 100% !important;
    }
    
    .admin-layout,
    .admin-content-wrapper,
    .py-6 {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    
    .max-w-4xl {
        max-width: 100% !important;
        margin: 0 auto !important;
        padding: 15mm !important;
    }
    
    /* Bỏ effects */
    .shadow-lg,
    .rounded-lg {
        box-shadow: none !important;
        border-radius: 0 !important;
    }
    
    /* In màu */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    
    /* Trang A4 */
    @page {
        size: A4 portrait;
        margin: 10mm;
    }
    
    /* Tránh ngắt trang */
    .grid,
    table,
    .border-t-2,
    .p-8 {
        page-break-inside: avoid;
    }
}
</style>
@endpush
