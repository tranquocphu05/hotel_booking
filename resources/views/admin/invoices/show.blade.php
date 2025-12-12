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
                        In hóa đơn
                    </a>
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
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            Thêm hóa đơn dịch vụ phát sinh
                        </a>
                    @endif

                    <a href="{{ route('admin.invoices.export', $invoice->id) }}"
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg flex items-center gap-2 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Xuất Excel
                    </a>

                    <a href="{{ route('admin.invoices.index') }}"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-lg transition">
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
                                <div class="bg-gray-50 px-6 py-3 grid grid-cols-12 gap-4 text-xs font-bold text-gray-600">
                                    <div class="col-span-4">MÔ TẢ</div>
                                    <div class="col-span-2 text-center">SỐ LƯỢNG</div>
                                    <div class="col-span-2 text-center">GIÁ/ĐÊM</div>
                                    <div class="col-span-2 text-center">SỐ ĐÊM</div>
                                    <div class="col-span-2 text-right">THÀNH TIỀN</div>
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
                                                <td class="px-6 py-4 text-gray-900">{{ $name }}</td>
                                                <td class="px-6 py-4 text-center text-gray-700">
                                                    {{ $s->phong ? ($s->phong->so_phong ?? $s->phong->id) : ($s->phong_id ?? '-') }}
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

                    {{-- Summary Section --}}
                    <div class="mt-8">
                        <div class="bg-white rounded-lg border-2 border-gray-200 shadow-sm overflow-hidden">
                            <div class="p-6 space-y-4">
                                @if (!$invoice->isExtra())
                                    {{-- Room fee --}}
                                    <div class="flex justify-between items-center py-2">
                                        <span class="text-gray-700 font-medium">Tiền phòng</span>
                                        <span
                                            class="text-gray-900 font-semibold text-lg">{{ number_format($invoice->tien_phong ?? $roomTotal, 0, ',', '.') }}
                                            đ</span>
                                    </div>
                                    {{-- Voucher discount --}}
                                    @if (($invoice->giam_gia ?? 0) > 0)
                                        @php
                                            $voucher = $booking->voucher;
                                        @endphp
                                        <div class="flex justify-between items-center py-2">
                                            <span class="text-gray-700 font-medium">
                                                Giảm giá @if ($voucher)
                                                    ({{ $voucher->ma_voucher ?? '' }} - {{ $voucher->gia_tri ?? 0 }}%)
                                                @endif
                                            </span>
                                            <span
                                                class="text-red-600 font-semibold text-lg">-{{ number_format($invoice->giam_gia ?? 0, 0, ',', '.') }}
                                                đ</span>
                                        </div>
                                    @endif

                                    <div class="border-t border-gray-200"></div>
                                    {{-- Services total --}}
                                    @if ($servicesTotal > 0 || ($invoice->tien_dich_vu ?? 0) > 0)
                                        <div class="flex justify-between items-center py-2">
                                            <span class="text-gray-700 font-medium">Tổng dịch vụ</span>
                                            <span
                                                class="text-gray-900 font-semibold text-lg">{{ number_format($invoice->tien_dich_vu ?? $servicesTotal, 0, ',', '.') }}
                                                đ</span>
                                        </div>
                                    @endif
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

                                    @if ($phiPhatSinh > 0)
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
                                                            <span class="font-medium">Danh mục:</span> {{ $loaiThietHai }}
                                                        </p>
                                                    @endif
                                                    @if ($lyDoThietHai)
                                                        <p class="text-sm text-red-700">
                                                            <span class="font-medium">Mô tả:</span> {{ $lyDoThietHai }}
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
                                    <span
                                        class="text-sm font-semibold text-gray-900">{{ $invoice->phuong_thuc ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Extra invoice (service-only) summary: show only total quantity and total payment --}}
                    <div class="mt-8">
                        <div class="bg-white rounded-lg border-2 border-gray-200 shadow-sm overflow-hidden">
                            <div class="p-6 space-y-4">
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-700 font-medium">Tổng số lượng</span>
                                    <span class="text-gray-900 font-semibold text-lg">{{ $servicesQty }}</span>
                                </div>

                                @php
                                    $tienDichVu = $invoice->tien_dich_vu ?? $servicesTotal;
                                    $tongCuoiCung = $invoice->tong_tien ?? max(0, $tienDichVu);
                                @endphp

                                <div class="border-t-2 border-gray-300"></div>

                                <div class="flex justify-between items-center py-3 bg-blue-600 -mx-6 px-6 -mb-6">
                                    <span class="font-bold text-white text-lg">TỔNG THANH TOÁN</span>
                                    <span class="text-white text-3xl font-bold">{{ number_format($tongCuoiCung, 0, ',', '.') }} đ</span>
                                </div>
                            </div>
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Phương thức thanh toán</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $invoice->phuong_thuc ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                </div>
            </div>
        </div>
    </div>
@endsection
