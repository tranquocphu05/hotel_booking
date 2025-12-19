@extends('layouts.admin')

@section('title', 'Yêu cầu đổi phòng - OZIA Hotel')

@section('admin_content')
    @php
        $statusTabs = [
            'all' => ['label' => 'Tất cả', 'color' => 'text-gray-700', 'bg' => 'bg-gray-100'],
            'cho_duyet' => ['label' => 'Chờ duyệt', 'color' => 'text-yellow-700', 'bg' => 'bg-yellow-50'],
            'da_duyet' => ['label' => 'Đã duyệt', 'color' => 'text-green-700', 'bg' => 'bg-green-50'],
            'bi_tu_choi' => ['label' => 'Từ chối', 'color' => 'text-red-700', 'bg' => 'bg-red-50'],
        ];
    @endphp

    <div class="space-y-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Yêu cầu đổi phòng</h1>
                <p class="text-gray-500 mt-1 text-sm">
                    Kiểm tra các yêu cầu đổi phòng mà khách đã gửi trong quá trình lưu trú.
                </p>
            </div>

            <form method="GET" class="w-full lg:w-80">
                <div class="relative">
                    <input type="text" name="q" value="{{ $searchTerm }}"
                        placeholder="Tìm theo mã, khách hàng, phòng..."
                        class="w-full pl-11 pr-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-300 bg-white text-sm">
                    <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    @if($activeStatus && $activeStatus !== 'all')
                        <input type="hidden" name="status" value="{{ $activeStatus }}">
                    @endif
                </div>
            </form>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-4 lg:p-6 shadow-sm">
            <div class="flex flex-wrap gap-3 mb-5">
                @foreach ($statusTabs as $key => $tab)
                    @php
                        $isActive = $activeStatus === $key;
                        $count = $counts[$key] ?? 0;
                    @endphp
                    <a href="{{ route('admin.yeu_cau_doi_phong.index', array_filter(['status' => $key !== 'all' ? $key : null, 'q' => $searchTerm])) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-full border text-sm font-medium transition
                            {{ $isActive ? 'border-indigo-500 text-indigo-600 bg-indigo-50' : 'border-gray-200 text-gray-600 hover:border-indigo-200 hover:text-indigo-600' }}">
                        <span>{{ $tab['label'] }}</span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $tab['bg'] }} {{ $tab['color'] }}">
                            {{ $count }}
                        </span>
                    </a>
                @endforeach
            </div>

            <div class="space-y-4">
                @forelse ($yeuCau as $item)
                    @php
                        $statusMeta = [
                            'cho_duyet' => ['label' => 'Chờ duyệt', 'bg' => 'bg-amber-100 text-amber-700'],
                            'da_duyet' => ['label' => 'Đã duyệt', 'bg' => 'bg-green-100 text-green-700'],
                            'bi_tu_choi' => ['label' => 'Bị từ chối', 'bg' => 'bg-red-100 text-red-700'],
                        ][$item->trang_thai] ?? ['label' => $item->trang_thai, 'bg' => 'bg-gray-100 text-gray-600'];
                    @endphp

                    <div class="rounded-xl border border-gray-200 bg-white p-4 lg:p-5 shadow-sm hover:border-indigo-200 transition">
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div class="space-y-2 flex-1">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs uppercase tracking-wider text-gray-400">YÊU CẦU</span>
                                    <span class="text-sm font-semibold text-gray-900">#{{ $item->id }}</span>
                                    <span class="text-xs text-gray-400">
                                        {{ optional($item->created_at)->diffForHumans() }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-6 text-sm text-gray-600">
                                    @php
                                        $khachHang = optional($item->datPhong)->user;
                                    @endphp
                                    <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-3">Thông tin khách hàng</p>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-2 text-sm">
                                            <div class="text-gray-500">Họ tên</div>
                                            <div class="font-semibold text-gray-900">{{ $khachHang->ho_ten ?? 'Không tìm thấy' }}</div>

                                            <div class="text-gray-500">Email</div>
                                            <div class="text-gray-900">{{ $khachHang->email ?? 'N/A' }}</div>

                                            <div class="text-gray-500">Số điện thoại</div>
                                            <div class="text-gray-900">{{ $khachHang->sdt ?? 'Chưa cập nhật' }}</div>

                                            <div class="text-gray-500">CCCD/CMND</div>
                                            <div class="text-gray-900">{{ $khachHang->cccd ?? 'Chưa cập nhật' }}</div>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 text-xs uppercase">Đặt phòng</p>
                                        <p class="font-semibold text-gray-900">#{{ $item->dat_phong_id }}</p>
                                        <p class="text-xs text-gray-500">
                                            Nhận:
                                            {{ optional(optional($item->datPhong)->ngay_nhan)->format('d/m/Y') ?? '--' }}
                                            · Trả:
                                            {{ optional(optional($item->datPhong)->ngay_tra)->format('d/m/Y') ?? '--' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 text-xs uppercase">Phòng</p>
                                        <p class="font-semibold text-gray-900">
                                            {{ $item->phongCu->ten_phong ?? ('Phòng #' . $item->phong_cu_id) }}
                                            <span class="text-gray-400 mx-1">→</span>
                                            {{ $item->phongMoi->ten_phong ?? ('Phòng #' . $item->phong_moi_id) }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Loại: {{ optional(optional($item->datPhong)->loaiPhong)->ten_loai ?? 'N/A' }}
                                        </p>
                                        @php
                                            // Tính phí đổi phòng tổng (bao gồm phụ phí thêm người)
                                            $phiDoiPhongCoBan = $item->phi_doi_phong ?? 0;
                                            $tongPhiDoiPhong = $phiDoiPhongCoBan;
                                            
                                            // Biến để lưu thông tin chi tiết
                                            $extraAdults = 0;
                                            $extraChildren = 0;
                                            $extraInfants = 0;
                                            $phuPhiNguoiLon = 0;
                                            $phuPhiTreEm = 0;
                                            $phuPhiEmBe = 0;
                                            
                                            // Tính phụ phí thêm người nếu có
                                            // SỬ DỤNG SỐ NGƯỜI BAN ĐẦU (trước khi approve) để tính phụ phí
                                            if ($item->datPhong && $item->phongMoi && $item->phongMoi->loaiPhong) {
                                                $booking = $item->datPhong;
                                                $loaiPhongMoi = $item->phongMoi->loaiPhong;
                                                $soNguoiMoi = $item->so_nguoi_moi ?? null;
                                                $soTreEmMoi = $item->so_tre_em_moi ?? null;
                                                $soEmBeMoi = $item->so_em_be_moi ?? null;
                                                
                                                // Lấy số người ban đầu (trước khi approve) từ yêu cầu
                                                // Nếu chưa có (trường hợp cũ), lấy từ booking hiện tại
                                                $soNguoiBanDau = $item->so_nguoi_ban_dau ?? ($booking->so_nguoi ?? 2);
                                                $soTreEmBanDau = $item->so_tre_em_ban_dau ?? ($booking->so_tre_em ?? 0);
                                                $soEmBeBanDau = $item->so_em_be_ban_dau ?? ($booking->so_em_be ?? 0);
                                                
                                                if ($booking->ngay_nhan && $booking->ngay_tra) {
                                                    $checkIn = \Carbon\Carbon::parse($booking->ngay_nhan);
                                                    $checkOut = \Carbon\Carbon::parse($booking->ngay_tra);
                                                    
                                                    // Phụ phí thêm người lớn (so sánh với số người ban đầu)
                                                    if ($soNguoiMoi !== null && $soNguoiMoi > $soNguoiBanDau) {
                                                        $extraAdults = $soNguoiMoi - $soNguoiBanDau;
                                                        // Tính phụ phí người lớn theo giá cố định 300k/người/đêm (BookingPriceCalculator đã dùng fixed-fee, tham số % giữ nguyên nhưng không còn dùng)
                                                        $phuPhiNguoiLon = \App\Services\BookingPriceCalculator::calculateExtraGuestSurcharge(
                                                            $loaiPhongMoi,
                                                            $checkIn,
                                                            $checkOut,
                                                            $extraAdults,
                                                            0 // percent không còn sử dụng
                                                        );
                                                        $tongPhiDoiPhong += $phuPhiNguoiLon;
                                                    }
                                                    
                                                    // Phụ phí thêm trẻ em (so sánh với số trẻ em ban đầu)
                                                    if ($soTreEmMoi !== null && $soTreEmMoi > $soTreEmBanDau) {
                                                        $extraChildren = $soTreEmMoi - $soTreEmBanDau;
                                                        // Tính phụ phí trẻ em theo giá cố định 150k/người/đêm
                                                        $phuPhiTreEm = \App\Services\BookingPriceCalculator::calculateChildSurcharge(
                                                            $loaiPhongMoi,
                                                            $checkIn,
                                                            $checkOut,
                                                            $extraChildren,
                                                            0
                                                        );
                                                        $tongPhiDoiPhong += $phuPhiTreEm;
                                                    }
                                                    
                                                    // Phụ phí thêm em bé (so sánh với số em bé ban đầu)
                                                    if ($soEmBeMoi !== null && $soEmBeMoi > $soEmBeBanDau) {
                                                        $extraInfants = $soEmBeMoi - $soEmBeBanDau;
                                                        // Em bé miễn phí theo policy mới, BookingPriceCalculator sẽ luôn trả 0
                                                        $phuPhiEmBe = \App\Services\BookingPriceCalculator::calculateInfantSurcharge(
                                                            $loaiPhongMoi,
                                                            $checkIn,
                                                            $checkOut,
                                                            $extraInfants,
                                                            0
                                                        );
                                                        $tongPhiDoiPhong += $phuPhiEmBe;
                                                    }
                                                }
                                            }
                                        @endphp
                                        
                                        {{-- Hiển thị chi tiết số người thêm --}}
                                        @if($extraAdults > 0 || $extraChildren > 0 || $extraInfants > 0)
                                            <div class="text-xs text-gray-600 mt-2 space-y-1">
                                                <p class="font-semibold text-gray-700">Thêm khách:</p>
                                                @if($extraAdults > 0)
                                                    <p class="text-gray-600">
                                                        +{{ $extraAdults }} người lớn
                                                        @if($phuPhiNguoiLon > 0)
                                                            <span class="text-orange-600 font-semibold">
                                                                ({{ number_format($phuPhiNguoiLon, 0, ',', '.') }} VNĐ)
                                                            </span>
                                                        @endif
                                                    </p>
                                                @endif
                                                @if($extraChildren > 0)
                                                    <p class="text-gray-600">
                                                        +{{ $extraChildren }} trẻ em
                                                        @if($phuPhiTreEm > 0)
                                                            <span class="text-orange-600 font-semibold">
                                                                ({{ number_format($phuPhiTreEm, 0, ',', '.') }} VNĐ)
                                                            </span>
                                                        @endif
                                                    </p>
                                                @endif
                                                @if($extraInfants > 0)
                                                    <p class="text-gray-600">
                                                        +{{ $extraInfants }} em bé
                                                        @if($phuPhiEmBe > 0)
                                                            <span class="text-orange-600 font-semibold">
                                                                ({{ number_format($phuPhiEmBe, 0, ',', '.') }} VNĐ)
                                                            </span>
                                                        @endif
                                                    </p>
                                                @endif
                                            </div>
                                        @endif
                                        
                                        {{-- Hiển thị phí đổi phòng --}}
                                        @if($tongPhiDoiPhong > 0)
                                            <p class="text-xs font-semibold text-orange-600 mt-2">
                                                Phí đổi phòng: {{ number_format($tongPhiDoiPhong, 0, ',', '.') }} VNĐ
                                            </p>
                                            @if($tongPhiDoiPhong > $phiDoiPhongCoBan)
                                                <p class="text-xs text-gray-500 mt-0.5">
                                                    (Phí cơ bản: {{ number_format($phiDoiPhongCoBan, 0, ',', '.') }} VNĐ + Phụ phí thêm người)
                                                </p>
                                            @endif
                                        @else
                                            {{-- Chỉ hiển thị "Miễn phí" nếu không có phụ phí thêm người --}}
                                            @if($extraAdults == 0 && $extraChildren == 0 && $extraInfants == 0)
                                                <p class="text-xs font-semibold text-green-600 mt-2">
                                                    Phí đổi phòng: Miễn phí
                                                </p>
                                            @else
                                                {{-- Nếu có thêm người nhưng tổng phí = 0 (không nên xảy ra), vẫn hiển thị 0 VNĐ --}}
                                                <p class="text-xs font-semibold text-orange-600 mt-2">
                                                    Phí đổi phòng: 0 VNĐ
                                                </p>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div class="text-sm text-gray-600 bg-gray-50 rounded-lg px-4 py-3 border border-gray-100">
                                    <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Lý do</p>
                                    <p>{{ \Illuminate\Support\Str::limit($item->ly_do, 220) }}</p>
                                </div>
                            </div>

                            <div class="flex flex-col items-stretch gap-3 w-full md:w-56">
                                <span class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold rounded-full {{ $statusMeta['bg'] }}">
                                    {{ $statusMeta['label'] }}
                                </span>

                                @if($item->trang_thai === 'cho_duyet')
                                    <form method="POST" action="{{ route('admin.yeu_cau_doi_phong.approve', $item->id) }}" class="w-full">
                                        @csrf
                                        <input type="hidden" name="change_at" value="{{ now()->format('Y-m-d H:i:s') }}">
                                        <input type="hidden" name="vat_percent" value="0">
                                        <input type="hidden" name="service_charge_percent" value="0">
                                        <input type="hidden" name="so_nguoi_moi" value="{{ $item->so_nguoi_moi ?? '' }}">
                                        <input type="hidden" name="so_tre_em_moi" value="{{ $item->so_tre_em_moi ?? '' }}">
                                        <input type="hidden" name="so_em_be_moi" value="{{ $item->so_em_be_moi ?? '' }}">
                                        <button type="submit"
                                                class="w-full px-4 py-2 text-sm font-semibold rounded-lg bg-green-500 hover:bg-green-600 text-white border border-green-500 shadow-sm transition">
                                            <i class="fas fa-check mr-2 text-xs"></i>Duyệt yêu cầu
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.yeu_cau_doi_phong.reject', $item->id) }}" class="w-full reject-form">
                                        @csrf
                                        <button type="button"
                                                onclick="handleReject(this.form)"
                                                class="w-full px-4 py-2 text-sm font-semibold rounded-lg bg-red-500 hover:bg-red-600 text-white border border-red-500 shadow-sm transition">
                                            <i class="fas fa-ban mr-2 text-xs"></i>Từ chối
                                        </button>
                                    </form>
                                @else
                                    <button class="w-full px-4 py-2 text-sm font-semibold rounded-lg bg-gray-50 text-gray-500 border border-gray-200 cursor-default">
                                        <i class="fas fa-check mr-2 text-xs"></i>Duyệt
                                    </button>
                                    <button class="w-full px-4 py-2 text-sm font-semibold rounded-lg bg-gray-50 text-gray-400 border border-gray-200 cursor-default">
                                        <i class="fas fa-ban mr-2 text-xs"></i>Từ chối
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 border border-dashed border-gray-200 rounded-2xl bg-gray-50">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-white shadow flex items-center justify-center text-gray-400">
                            <i class="fas fa-exchange-alt text-2xl"></i>
                        </div>
                        <p class="text-gray-600 font-medium">Chưa có yêu cầu đổi phòng nào.</p>
                        <p class="text-gray-400 text-sm mt-1">Khi khách gửi yêu cầu, thông tin sẽ hiển thị tại đây.</p>
                    </div>
                @endforelse
            </div>

            <div class="pt-4">
                {{ $yeuCau->links() }}
            </div>
        </div>
    </div>


@endsection

@push('scripts')
<script>
function handleReject(form) {
    if (confirm('Bạn có chắc chắn muốn từ chối yêu cầu đổi phòng này?')) {
        form.submit();
    }
}
</script>
@endpush
