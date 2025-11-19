<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn #{{ $invoice->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
            @page { size: A4; margin: 10mm; }
        }
    </style>
</head>
<body class="bg-white">
<?php
    $booking = $invoice->datPhong;
    $nights = 1;
    if($booking && $booking->ngay_nhan && $booking->ngay_tra) {
        $nights = \Carbon\Carbon::parse($booking->ngay_nhan)->diffInDays(\Carbon\Carbon::parse($booking->ngay_tra));
    }
    
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
    
    // Note: Voucher discount will be calculated after services total is known
?>

<div class="max-w-4xl mx-auto p-8">
    {{-- Buttons --}}
    <div class="no-print mb-6 flex justify-between">
        <a href="{{ route('admin.invoices.show', $invoice->id) }}" 
           class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
            Quay lại
        </a>
        <button onclick="window.print()" 
                class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold">
            In hóa đơn
        </button>
    </div>

    {{-- Invoice --}}
    <div class="border-2 border-gray-800">
        {{-- Header --}}
        <div class="bg-blue-600 text-white p-6">
            <div class="flex justify-between">
                <div>
                    <h1 class="text-3xl font-bold">OZIA HOTEL</h1>
                    <p class="text-sm mt-1">123 Đường ABC, Quận 1, TP.HCM</p>
                    <p class="text-sm">ĐT: (028) 1234 5678</p>
                </div>
                <div class="text-right">
                    <h2 class="text-2xl font-bold">HÓA ĐƠN</h2>
                    <p class="text-sm">Số: #{{ $invoice->id }}</p>
                    <p class="text-sm">Ngày: {{ $invoice->ngay_tao->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-6">
            {{-- Info --}}
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div class="border-2 border-gray-300 rounded-lg p-4 bg-gray-50">
                    <h3 class="font-bold text-base mb-3 text-gray-800 uppercase">Khách hàng</h3>
                    <table class="w-full text-sm">
                        <tr class="border-b border-gray-300">
                            <td class="py-2 text-gray-600 w-24">Họ tên:</td>
                            <td class="py-2 font-semibold text-gray-900">{{ $booking->username ?? 'N/A' }}</td>
                        </tr>
                        <tr class="border-b border-gray-300">
                            <td class="py-2 text-gray-600">Email:</td>
                            <td class="py-2 text-gray-900">{{ $booking->email ?? 'N/A' }}</td>
                        </tr>
                        <tr class="border-b border-gray-300">
                            <td class="py-2 text-gray-600">SĐT:</td>
                            <td class="py-2 text-gray-900">{{ $booking->sdt ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-gray-600">CCCD:</td>
                            <td class="py-2 text-gray-900">{{ $booking->cccd ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="border-2 border-gray-300 rounded-lg p-4 bg-gray-50">
                    <h3 class="font-bold text-base mb-3 text-gray-800 uppercase">Đặt phòng</h3>
                    <table class="w-full text-sm">
                        <tr class="border-b border-gray-300">
                            <td class="py-2 text-gray-600 w-28">Số phòng:</td>
                            <td class="py-2 font-semibold text-gray-900">{{ $booking->so_luong_da_dat ?? 1 }} phòng</td>
                        </tr>
                        <tr class="border-b border-gray-300">
                            <td class="py-2 text-gray-600">Số người:</td>
                            <td class="py-2 text-gray-900">{{ $booking->so_nguoi ?? 'N/A' }} người</td>
                        </tr>
                        <tr class="border-b border-gray-300">
                            <td class="py-2 text-gray-600">Nhận phòng:</td>
                            <td class="py-2 text-gray-900">{{ date('d/m/Y', strtotime($booking->ngay_nhan)) }}</td>
                        </tr>
                        <tr class="border-b border-gray-300">
                            <td class="py-2 text-gray-600">Trả phòng:</td>
                            <td class="py-2 text-gray-900">{{ date('d/m/Y', strtotime($booking->ngay_tra)) }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-gray-600">Số đêm:</td>
                            <td class="py-2 font-semibold text-gray-900">{{ $nights }} đêm</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Chi tiết phòng --}}
            <h3 class="font-bold text-base mb-3 text-gray-800">CHI TIẾT PHÒNG</h3>
            <table class="w-full border-collapse border-2 border-gray-800 mb-6">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border border-gray-800 px-4 py-2 text-left text-sm font-bold">MÔ TẢ</th>
                        <th class="border border-gray-800 px-4 py-2 text-center text-sm font-bold">SỐ LƯỢNG</th>
                        <th class="border border-gray-800 px-4 py-2 text-center text-sm font-bold">SỐ ĐÊM</th>
                        <th class="border border-gray-800 px-4 py-2 text-right text-sm font-bold">THÀNH TIỀN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($booking->getRoomTypes() as $rt): ?>
                        <?php 
                            $lp = \App\Models\LoaiPhong::find($rt['loai_phong_id']); 
                            // Tính thành tiền: Lấy giá từ LoaiPhong × số lượng × số đêm
                            $giaPhong = $lp ? ($lp->gia_khuyen_mai ?? $lp->gia_co_ban) : 0;
                            $thanhTien = $giaPhong * $rt['so_luong'] * $nights;
                        ?>
                        <tr>
                            <td class="border border-gray-800 px-4 py-2 text-sm">{{ $lp->ten_loai ?? 'N/A' }}</td>
                            <td class="border border-gray-800 px-4 py-2 text-center text-sm">{{ $rt['so_luong'] }}</td>
                            <td class="border border-gray-800 px-4 py-2 text-center text-sm">{{ $nights }}</td>
                            <td class="border border-gray-800 px-4 py-2 text-right text-sm font-semibold">
                                {{ number_format($thanhTien, 0, ',', '.') }} ₫
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            {{-- Danh sách dịch vụ --}}
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

            @if($services->isNotEmpty())
                <h3 class="font-bold text-base mb-3 text-gray-800 mt-6">DANH SÁCH DỊCH VỤ</h3>
                <table class="w-full border-collapse border-2 border-gray-800 mb-6">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="border border-gray-800 px-4 py-2 text-left text-sm font-bold">DỊCH VỤ</th>
                            <th class="border border-gray-800 px-4 py-2 text-center text-sm font-bold">NGÀY DÙNG</th>
                            <th class="border border-gray-800 px-4 py-2 text-center text-sm font-bold">SỐ LƯỢNG</th>
                            <th class="border border-gray-800 px-4 py-2 text-right text-sm font-bold">ĐƠN GIÁ</th>
                            <th class="border border-gray-800 px-4 py-2 text-right text-sm font-bold">THÀNH TIỀN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($services as $s)
                            @php
                                $svc = $s->service;
                                $name = $svc ? ($svc->name ?? 'N/A') : ($s->service_name ?? 'N/A');
                                $usedAt = $s->used_at ? date('d/m/Y', strtotime($s->used_at)) : '-';
                                $qty = $s->quantity ?? 0;
                                $unitPrice = $s->unit_price ?? 0;
                                $serviceSubtotal = $qty * $unitPrice;
                            @endphp
                            <tr>
                                <td class="border border-gray-800 px-4 py-2 text-sm">{{ $name }}</td>
                                <td class="border border-gray-800 px-4 py-2 text-center text-sm">{{ $usedAt }}</td>
                                <td class="border border-gray-800 px-4 py-2 text-center text-sm">{{ $qty }}</td>
                                <td class="border border-gray-800 px-4 py-2 text-right text-sm">{{ number_format($unitPrice, 0, ',', '.') }} đ</td>
                                <td class="border border-gray-800 px-4 py-2 text-right text-sm font-semibold">{{ number_format($serviceSubtotal, 0, ',', '.') }} đ</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @php
                // Tính giảm giá từ voucher (CHỈ áp dụng cho tiền phòng)
                $voucher = null;
                $discount = 0;
                if($booking && $booking->voucher_id) {
                    $voucher = $booking->voucher;
                    if($voucher && $voucher->gia_tri) {
                        // Voucher chỉ áp dụng cho tiền phòng (không giảm giá dịch vụ)
                        $discount = $subtotal * ($voucher->gia_tri / 100);
                    }
                }
                
                // Tính tổng cộng: (Tiền phòng - Giảm giá) + Tiền dịch vụ
                $tongCong = max(0, $subtotal - $discount + $servicesTotal);
            @endphp
            
            {{-- Total - Sử dụng dữ liệu từ invoice --}}
            <div class="border-t-2 border-gray-800 pt-4">
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span>Tổng tiền phòng:</span>
                        <span class="font-semibold">{{ number_format($invoice->tien_phong ?? $subtotal, 0, ',', '.') }} ₫</span>
                    </div>
                    
                    @if(($invoice->giam_gia ?? 0) > 0)
                        @php
                            $voucher = $booking->voucher;
                        @endphp
                        <div class="flex justify-between text-sm text-red-600">
                            <span>Giảm giá @if($voucher)({{ $voucher->ma_voucher }} - {{ $voucher->gia_tri }}%)@endif:</span>
                            <span class="font-semibold">-{{ number_format($invoice->giam_gia, 0, ',', '.') }} ₫</span>
                        </div>
                    @endif
                    
                    @if(($invoice->tien_dich_vu ?? $servicesTotal) > 0)
                        <div class="flex justify-between text-sm">
                            <span>Tổng tiền dịch vụ:</span>
                            <span class="font-semibold text-purple-600">{{ number_format($invoice->tien_dich_vu ?? $servicesTotal, 0, ',', '.') }} ₫</span>
                        </div>
                    @endif
                    
                    <div class="border-t border-gray-400 my-1"></div>
                    
                    <div class="flex justify-between text-lg font-bold">
                        <span>TỔNG THANH TOÁN:</span>
                        <span>{{ number_format($invoice->tong_tien, 0, ',', '.') }} ₫</span>
                    </div>
                </div>
            </div>

            {{-- Payment --}}
            <div class="mt-6 pt-4 border-t-2 border-gray-800">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-semibold">Phương thức thanh toán:</span>
                    <?php 
                        $pm = $invoice->phuong_thuc_ui ?? null;
                        if(!$pm) {
                            $methods = ['tien_mat' => 'Tiền mặt', 'vnpay' => 'VNPay', 'momo' => 'MoMo', 'chuyen_khoan' => 'Chuyển khoản'];
                            $pmLabel = $methods[$invoice->phuong_thuc] ?? $invoice->phuong_thuc ?? 'Khác';
                        } else {
                            $pmLabel = $pm['label'];
                        }
                    ?>
                    <span class="text-sm font-bold">{{ $pmLabel }}</span>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="bg-gray-100 p-4 text-center border-t-2 border-gray-800">
            <p class="text-sm font-semibold">Cảm ơn quý khách đã sử dụng dịch vụ của OZIA Hotel!</p>
            <p class="text-xs text-gray-600 mt-1">Liên hệ: (028) 1234 5678</p>
        </div>
    </div>
</div>

</body>
</html>
