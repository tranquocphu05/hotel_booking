@extends('layouts.admin')

@section('title', 'Chi tiết đặt phòng')

@section('admin_content')

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8 flex justify-between items-start">
                <div>
                    @php
                        $roomTypes = $booking->getRoomTypes();
                    @endphp
                    <h1 class="text-3xl font-bold text-gray-900">
                        Chi tiết đặt phòng #{{ $booking->id }}
                        @if (count($roomTypes) > 1)
                            <span class="text-lg text-gray-600">({{ count($roomTypes) }} loại phòng)</span>
                        @endif
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">
                        @if($booking->loaiPhong && count($roomTypes) == 1)
                            {{ $booking->loaiPhong->ten_loai }}
                        @else
                            Đặt phòng ngày {{ date('d/m/Y', strtotime($booking->ngay_nhan)) }}
                        @endif
                    </p>
                </div>
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold
                    @if ($booking->trang_thai === 'da_xac_nhan') bg-green-100 text-green-800
                    @elseif($booking->trang_thai === 'cho_xac_nhan') bg-yellow-100 text-yellow-800
                    @elseif($booking->trang_thai === 'da_huy') bg-red-100 text-red-800
                    @else bg-blue-100 text-blue-800 @endif">
                    @php
                        $statuses = [
                            'cho_xac_nhan' => 'Chờ xác nhận',
                            'da_xac_nhan' => 'Đã xác nhận',
                            'da_huy' => 'Đã hủy',
                            'da_tra' => 'Đã trả phòng',
                        ];
                    @endphp
                    {{ $statuses[$booking->trang_thai] ?? $booking->trang_thai }}
                </span>
            </div>

            @if ($booking->trang_thai === 'da_xac_nhan' && isset($cancellationPolicy))
                <div class="bg-blue-50 border border-blue-200 rounded-lg shadow-sm p-6 mb-8">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Chính sách hủy phòng</h3>
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <!-- Time Info -->
                                <div class="bg-white p-4 rounded-lg border border-blue-200">
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                                            <span class="text-sm font-medium text-gray-600">Ngày nhận phòng</span>
                                            <span class="text-lg font-semibold text-gray-900">{{ date('d/m/Y', strtotime($booking->ngay_nhan)) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-medium text-gray-600">Thời gian còn lại</span>
                                            <span class="text-2xl font-bold text-blue-600">{{ number_format($cancellationPolicy['days_until_checkin'], 0) }} ngày</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Refund Policy Grid -->
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="text-center p-3 bg-green-50 rounded border border-green-200">
                                        <p class="text-xs text-gray-600 mb-1">≥ 7 ngày</p>
                                        <p class="text-lg font-bold text-green-600">100%</p>
                                    </div>
                                    <div class="text-center p-3 bg-yellow-50 rounded border border-yellow-200">
                                        <p class="text-xs text-gray-600 mb-1">3-6 ngày</p>
                                        <p class="text-lg font-bold text-yellow-600">50%</p>
                                    </div>
                                    <div class="text-center p-3 bg-orange-50 rounded border border-orange-200">
                                        <p class="text-xs text-gray-600 mb-1">1-2 ngày</p>
                                        <p class="text-lg font-bold text-orange-600">25%</p>
                                    </div>
                                    <div class="text-center p-3 bg-red-50 rounded border border-red-200">
                                        <p class="text-xs text-gray-600 mb-1">Trong ngày</p>
                                        <p class="text-lg font-bold text-red-600">0%</p>
                                    </div>
                                </div>
                            </div>
                            <p class="text-sm font-semibold {{ $step3Complete ? 'text-purple-600' : 'text-gray-400' }} mb-1">Check-in</p>
                            @if($step3Complete && $step3Date)
                                <p class="text-xs text-gray-500">{{ $step3Date->format('d/m/Y H:i') }}</p>
                                @if($booking->nguoi_checkin)
                                    <p class="text-xs text-gray-400">{{ $booking->nguoi_checkin }}</p>
                                @endif
                            @endif
                        </div>
                            <!-- Cancel Button -->
                            <div class="mt-4">
                                @if ($cancellationPolicy['can_cancel'])
                                    <a href="{{ route('admin.dat_phong.cancel', $booking->id) }}"
                                        class="inline-flex items-center px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Hủy đặt phòng
                                    </a>
                                @else
                                    <div class="inline-block p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700">
                                        <strong>{{ $cancellationPolicy['message'] }}</strong>

                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif


            {{-- CHECK-IN / CHECK-OUT SECTION --}}
            @include('admin.dat_phong._checkin_checkout')

            {{-- BOOKING SERVICES SECTION --}}
            @include('admin.dat_phong._booking_services')

            {{-- MAIN CONTENT: 1 COLUMN LAYOUT WITH SIDEBAR --}}
            <div class="lg:grid lg:grid-cols-6 lg:gap-6">

                {{-- MAIN CONTENT (LEFT) --}}
                <div class="lg:col-span-6 space-y-6">

                    {{-- THÔNG TIN PHÒNG --}}
                    <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
                        <div class="p-4 border-b bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900">Thông tin phòng</h3>
                        </div>

                        <div class="p-4 space-y-4">

                            @php $roomTypes = $booking->getRoomTypes(); @endphp

                            {{-- NHIỀU LOẠI PHÒNG --}}
                            @if (count($roomTypes) > 1)
                                <p class="text-sm font-semibold text-gray-700">Các loại phòng:</p>

                                <div class="space-y-4">
                                    @foreach ($roomTypes as $roomType)
                                        @php
                                            $loaiPhong = \App\Models\LoaiPhong::find($roomType['loai_phong_id']);
                                        @endphp

                                        @if ($loaiPhong)
                                            <div class="p-3 rounded-lg border bg-gray-50 flex gap-4 items-start">
                                                {{-- Ảnh phòng --}}
                                                <img src="{{ asset($loaiPhong->anh ?? 'img/room/room-1.jpg') }}"
                                                    class="w-40 h-28 rounded-lg object-cover shadow-sm">

                                                {{-- Nội dung --}}
                                                <div class="flex-1">
                                                    <h4 class="text-sm font-semibold text-gray-900 mb-1">
                                                        {{ $loaiPhong->ten_loai }}</h4>

                                                    <p class="text-xs text-gray-600 mb-1">Số lượng:
                                                        <span
                                                            class="font-medium text-gray-900">{{ $roomType['so_luong'] }}</span>
                                                        phòng
                                                    </p>

                                                    {{-- Giá --}}
                                                    @php
                                                        $giaCoBan = $loaiPhong->gia_co_ban ?? 0;
                                                        $giaKhuyenMai = $loaiPhong->gia_khuyen_mai ?? null;
                                                        $lpUnit = $giaKhuyenMai ?? $giaCoBan;
                                                        $soLuong = $roomType['so_luong'] ?? 1;

                                                        $nights =
                                                            $booking->ngay_nhan && $booking->ngay_tra
                                                                ? \Carbon\Carbon::parse(
                                                                    $booking->ngay_nhan,
                                                                )->diffInDays($booking->ngay_tra)
                                                                : 1;
                                                        $nights = max(1, $nights);
                                                        $subtotal = $lpUnit * $nights * $soLuong;
                                                    @endphp


                                                    {{-- Giá/đêm --}}
                                                    <div class="text-xs text-gray-600 mb-1">
                                                        Giá/đêm:
                                                        @if ($giaKhuyenMai && $giaKhuyenMai < $giaCoBan)
                                                            <span
                                                                class="line-through text-gray-400">{{ number_format($giaCoBan, 0, ',', '.') }}</span>
                                                            <span
                                                                class="text-red-600 font-semibold ml-1">{{ number_format($giaKhuyenMai, 0, ',', '.') }}
                                                                VNĐ</span>
                                                        @else
                                                            <span
                                                                class="font-semibold">{{ number_format($giaCoBan, 0, ',', '.') }}
                                                                VNĐ</span>
                                                        @endif
                                                    </div>

                                                    <p class="text-xs text-gray-600">
                                                        Tổng: <span
                                                            class="font-semibold text-gray-900">{{ number_format($subtotal, 0, ',', '.') }}
                                                            VNĐ</span>
                                                    </p>

                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>


                                <p class="text-sm text-gray-600">
                                    Tổng số phòng:
                                    <span class="font-semibold">{{ $booking->so_luong_da_dat }} phòng</span>
                                </p>
                            @else
                                {{-- CHỈ 1 LOẠI PHÒNG --}}
                                <div class="flex gap-4 items-start">
                                    <img src="{{ asset($booking->loaiPhong->anh ?? 'img/room/room-1.jpg') }}"
                                        class="w-32 h-32 object-cover rounded-lg shadow-sm">

                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ $booking->loaiPhong->ten_loai }}</h3>

                                        <div class="grid grid-cols-2 gap-4 mt-3">
                                            <div>
                                                <p class="text-sm text-gray-500">Số lượng phòng</p>
                                                <p class="text-lg font-semibold text-gray-900">
                                                    {{ $booking->so_luong_da_dat }}</p>
                                            </div>

                                            <div>
                                                <p class="text-sm text-gray-500">Giá/đêm</p>
                                                @php
                                                    $lp = $booking->loaiPhong;
                                                    $giaCoBan = $lp->gia_co_ban ?? 0;
                                                    $giaKhuyenMai = $lp->gia_khuyen_mai ?? null;
                                                @endphp

                                                @if ($giaKhuyenMai && $giaKhuyenMai < $giaCoBan)
                                                    <div class="flex items-center gap-2">
                                                        <span
                                                            class="text-xs text-gray-400 line-through">{{ number_format($giaCoBan, 0, ',', '.') }}</span>
                                                        <span
                                                            class="text-lg font-semibold text-red-600">{{ number_format($giaKhuyenMai, 0, ',', '.') }}
                                                            VNĐ</span>
                                                    </div>
                                                @else
                                                    <p class="text-lg font-semibold text-gray-900">
                                                        {{ number_format($giaCoBan, 0, ',', '.') }} VNĐ</p>

                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif


                            {{-- DANH SÁCH PHÒNG ĐÃ GÁN --}}
                            @php
                                $assignedPhongs = $booking->getAssignedPhongs();
                                $assignedCount = $assignedPhongs->count();
                                $remainingCount = max(0, ($booking->so_luong_da_dat ?? 0) - $assignedCount);
                            @endphp


                            @if ($assignedCount > 0)
                                <div class="pt-3 border-t">
                                    <p class="text-sm font-semibold text-gray-700 mb-2">
                                        Phòng đã gán ({{ $assignedCount }}/{{ $booking->so_luong_da_dat }}):
                                    </p>

                                    <div class="space-y-2">
                                        @foreach ($assignedPhongs as $phong)
                                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                                <p class="text-sm font-medium text-blue-900">
                                                    Phòng: {{ $phong->so_phong }}
                                                    @if ($phong->ten_phong)
                                                        ({{ $phong->ten_phong }})
                                                    @endif
                                                </p>

                                                <p class="text-xs text-blue-700 mt-1">
                                                    Tầng: {{ $phong->tang ?? 'N/A' }} |
                                                    Trạng thái:
                                                    <span
                                                        class="@if ($phong->trang_thai == 'trong') text-green-600
                                              @elseif($phong->trang_thai == 'dang_thue') text-blue-600
                                              @elseif($phong->trang_thai == 'dang_don') text-yellow-600
                                              @else text-red-600 @endif">
                                                        {{ $phong->trang_thai === 'trong'
                                                            ? 'Trống'
                                                            : ($phong->trang_thai === 'dang_thue'
                                                                ? 'Đang thuê'
                                                                : ($phong->trang_thai === 'dang_don'
                                                                    ? 'Đang dọn'
                                                                    : 'Bảo trì')) }}

                                                    </span>
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                            @elseif ($booking->phong)
                                {{-- Legacy --}}
                                <div class="pt-3 border-t">
                                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                        <p class="text-sm font-medium text-blue-900">
                                            Phòng: {{ $booking->phong->so_phong }}
                                            @if ($booking->phong->ten_phong)
                                                ({{ $booking->phong->ten_phong }})
                                            @endif
                                        </p>

                                        <p class="text-xs text-blue-700 mt-1">
                                            Tầng: {{ $booking->phong->tang ?? 'N/A' }} |
                                            Trạng thái: {{ $booking->phong->trang_thai }}
                                        </p>
                                    </div>
                                </div>
                            @else
                                <p class="text-sm text-yellow-600">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Chưa gán phòng cụ thể
                                </p>
                            @endif

                            {{-- CHỌN PHÒNG ĐỂ GÁN --}}
                            @php
                                $hasRooms = false;
                                if (isset($availableRooms) && $availableRooms->count() > 0) {
                                    $hasRooms = true;
                                } elseif (isset($availableRoomsByLoaiPhong) && is_array($availableRoomsByLoaiPhong)) {
                                    // check if any of the per-type room lists contains items
                                    foreach ($availableRoomsByLoaiPhong as $arr) {
                                        if (is_object($arr) && $arr->count() > 0) { $hasRooms = true; break; }
                                        if (is_array($arr) && count($arr) > 0) { $hasRooms = true; break; }
                                    }
                                }
                            @endphp

                            @if ($remainingCount > 0 && $hasRooms)
                                <div class="pt-3 border-t">
                                    <p class="text-xs text-gray-600 mb-2">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Còn thiếu {{ $remainingCount }} phòng. Chọn để gán:
                                    </p>

                                    <form action="{{ route('admin.dat_phong.assign_room', $booking->id) }}" method="POST">
                                        @csrf @method('PUT')

                                        @php
                                            $roomTypes = $booking->getRoomTypes();
                                            $assignedIds = $booking->getPhongIds();
                                        @endphp

                                        @foreach ($roomTypes as $rt)
                                            @php
                                                $lid = $rt['loai_phong_id'] ?? null;
                                                $needed = max(0, ($rt['so_luong'] ?? 0));
                                                // count already assigned for this type
                                                $assignedForType = 0;
                                                if (!empty($assignedIds)) {
                                                    $assignedForType = \App\Models\Phong::whereIn('id', $assignedIds)
                                                        ->where('loai_phong_id', $lid)
                                                        ->count();
                                                }
                                                $remainingForType = max(0, $needed - $assignedForType);
                                                // start with available rooms for this type (may be collection or array)
                                                $roomsForType = collect($availableRoomsByLoaiPhong[$lid] ?? collect())
                                                    // remove rooms that are already assigned to this booking so they don't show up
                                                    ->reject(function ($room) use ($assignedIds) {
                                                        return in_array($room->id, (array) $assignedIds);
                                                    })
                                                    ->values();
                                            @endphp

                                            @if ($remainingForType > 0)
                                                <div class="mb-3">
                                                    <label class="text-xs text-gray-600">Chọn phòng cho loại <strong>{{ \App\Models\LoaiPhong::find($lid)->ten_loai ?? $lid }}</strong> (Cần {{ $remainingForType }}):</label>
                                                    <select name="phong_ids[{{ $lid }}][]" multiple size="4" class="w-full text-sm border-gray-300 rounded-md mt-1">
                                                        @foreach ($roomsForType as $room)
                                                            @php
                                                                $label = $room->so_phong . ($room->tang ? ' (Tầng ' . $room->tang . ')': '');
                                                                if ($room->co_view_dep) $label .= ' - View đẹp';
                                                            @endphp
                                                            <option value="{{ $room->id }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    <p class="text-xs text-gray-500 mt-1">Giữ Ctrl/Cmd để chọn nhiều phòng. Không được chọn quá {{ $remainingForType }} phòng.</p>
                                                </div>
                                            @endif
                                        @endforeach

                                        <button type="submit" class="mt-2 px-3 py-1.5 bg-blue-600 text-white text-xs rounded shadow hover:bg-blue-700">
                                            Gán phòng
                                        </button>
                                    </form>
                                </div>
                            @elseif ($remainingCount > 0)
                                <p class="text-xs text-gray-500">
                                    Không có phòng trống trong khoảng thời gian này.
                                </p>
                            @endif

                            {{-- TỔNG GIÁ --}}
                            @if (count($roomTypes) > 1)
                                <div class="pt-3 border-t">
                                        <p class="text-sm text-gray-700">
                                        Tổng giá:
                                        <span class="font-semibold">{{ number_format((float) $booking->tong_tien, 0, ',', '.') }}
                                            VNĐ</span>
                                    </p>
                                </div>
                            @else
                                <p class="text-sm text-gray-700">
                                    Giá phòng:
                                    <span class="font-semibold">
                                        {{ number_format($booking->loaiPhong->gia_khuyen_mai ?? $booking->loaiPhong->gia_co_ban, 0, ',', '.') }}
                                        VNĐ/đêm
                                    </span>
                                </p>
                            @endif

                        </div>
                    </div>


                    <!-- Cards thông tin: Đặt phòng, Khách hàng, Hủy (nằm ngang) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Card Thông tin đặt phòng -->
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Thông tin đặt phòng</h3>
                            </div>
                            <div class="p-4">
                                <div class="space-y-3">
                                    <p class="text-sm text-gray-600">Ngày đặt: <span
                                            class="font-medium">{{ date('d/m/Y H:i', strtotime($booking->ngay_dat)) }}</span>
                                    </p>
                                    <p class="text-sm text-gray-600">Số người: <span
                                            class="font-medium">{{ $booking->so_nguoi }}
                                            người</span></p>
                                    <p class="text-sm text-gray-600">Ngày nhận phòng: <span
                                            class="font-medium">{{ date('d/m/Y', strtotime($booking->ngay_nhan)) }}</span>
                                    </p>
                                    <p class="text-sm text-gray-600">Ngày trả phòng: <span
                                            class="font-medium">{{ date('d/m/Y', strtotime($booking->ngay_tra)) }}</span>
                                    </p>
                                    @if ($booking->ghi_chu)
                                        <p class="text-sm text-gray-600">Ghi chú: <span
                                                class="font-medium">{{ $booking->ghi_chu }}</span></p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Card Thông tin khách hàng -->
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Thông tin khách hàng</h3>
                            </div>
                            <div class="p-4">
                                <div class="space-y-3">
                                    <p class="text-sm text-gray-600">Tên khách: <span
                                            class="font-medium">{{ $booking->username }}</span></p>
                                    <p class="text-sm text-gray-600">Email: <span
                                            class="font-medium">{{ $booking->email }}</span></p>
                                    <p class="text-sm text-gray-600">Số điện thoại: <span
                                            class="font-medium">{{ $booking->sdt }}</span></p>
                                    <p class="text-sm text-gray-600">CCCD/CMND:
                                        @if ($booking->cccd)
                                            <span class="font-medium">{{ $booking->cccd }}</span>
                                        @else
                                            <span class="text-yellow-600 italic">
                                                <i class="fas fa-exclamation-triangle text-xs mr-1"></i>
                                                Chưa cập nhật
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Card thông tin hủy (nếu có) -->
                        @if ($booking->trang_thai === 'da_huy')
                            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                                <div class="p-4 border-b border-gray-200">
                                    <h3 class="text-lg font-medium text-gray-900">Thông tin hủy đặt phòng</h3>
                                </div>
                                <div class="p-4">
                                    <div class="space-y-3">
                                        <p class="text-sm text-gray-600">Ngày hủy: <span
                                                class="font-medium">{{ date('d/m/Y H:i', strtotime($booking->ngay_huy)) }}</span>
                                        </p>
                                        <p class="text-sm text-gray-600">Lý do hủy: <span class="font-medium">
                                                @php
                                                    $reasons = [
                                                        'thay_doi_lich_trinh' => 'Thay đổi lịch trình',
                                                        'thay_doi_ke_hoach' => 'Thay đổi kế hoạch',
                                                        'khong_phu_hop' => 'Không phù hợp với yêu cầu',
                                                        'ly_do_khac' => 'Lý do khác',
                                                    ];
                                                @endphp
                                                {{ $reasons[$booking->ly_do_huy] ?? $booking->ly_do_huy }}
                                            </span></p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- SIDEBAR (RIGHT) --}}
                <div class="lg:col-span-6 mt-6 lg:mt-0">
                    <div class="sticky top-6 space-y-6">

                        {{-- THANH TOÁN --}}
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Thanh toán
                                </h2>
                            </div>
                            <div class="p-6">
                                @if ($booking->voucher)
                                    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                                        <p class="text-sm font-medium text-green-900">Mã giảm giá</p>
                                        <p class="text-lg font-bold text-green-700">
                                            {{ $booking->voucher->ma_voucher }}</p>
                                        <p class="text-sm text-green-600 mt-1">Giảm {{ $booking->voucher->gia_tri }}%
                                        </p>
                                    </div>
                                @endif

                                {{-- Danh sách dịch vụ --}}
                                @php
                                    $bookingServices = \App\Models\BookingService::with('service')
                                        ->where('dat_phong_id', $booking->id)
                                        ->orderBy('used_at')
                                        ->get();
                                @endphp

                                @if ($bookingServices->count() > 0)
                                    @php
                                        $totalServicePrice = $bookingServices->sum(function ($s) {
                                            return $s->quantity * $s->unit_price;
                                        });
                                    @endphp
                                    <div class="mb-4 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                                        <p class="text-sm font-medium text-gray-900 mb-2 flex items-center">
                                            <i class="fas fa-concierge-bell text-purple-600 mr-2"></i>
                                            Tổng tiền dịch vụ
                                        </p>
                                        <p class="text-lg font-bold text-purple-700">
                                            {{ number_format($totalServicePrice, 0, ',', '.') }} VNĐ</p>
                                        <p class="text-xs text-gray-600 mt-2">Số dịch vụ:
                                            {{ $bookingServices->count() }}</p>
                                    </div>
                                @endif

                                @php
                                    // Always calculate from current LoaiPhong prices (not invoice) to ensure accuracy
                                    $checkin = new DateTime($booking->ngay_nhan);
                                    $checkout = new DateTime($booking->ngay_tra);
                                    $nights = max(1, $checkin->diff($checkout)->days);

                                    $tienPhong = 0;
                                    foreach ($booking->getRoomTypes() as $rt) {
                                        $loaiPhong = \App\Models\LoaiPhong::find($rt['loai_phong_id']);
                                        if ($loaiPhong) {
                                            $giaGoc = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban;
                                            $tienPhong += $giaGoc * $rt['so_luong'] * $nights;
                                        }
                                    }

                                    // Tính tiền dịch vụ
                                    $tienDichVu = \App\Models\BookingService::where(
                                        'dat_phong_id',
                                        $booking->id,
                                    )->sum(\DB::raw('quantity * unit_price'));

                                    // Tính giảm giá: áp dụng cho tiền phòng, kiểm tra loai_phong_id
                                    $giamGia = 0;
                                    if ($booking->voucher) {
                                        $voucher = $booking->voucher;
                                        $discountValue = floatval($voucher->gia_tri ?? 0);
                                        
                                        // Determine applicable room subtotal based on voucher's loai_phong_id
                                        $applicableTotal = 0;
                                        foreach ($booking->getRoomTypes() as $rt) {
                                            $loaiPhong = \App\Models\LoaiPhong::find($rt['loai_phong_id']);
                                            if (!$loaiPhong) continue;
                                            $unit = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban;
                                            $subtotal = $unit * ($rt['so_luong'] ?? 0) * $nights;
                                            
                                            // Nếu voucher không có loai_phong_id (NULL) hoặc khớp với room type này
                                            if (empty($voucher->loai_phong_id) || $voucher->loai_phong_id === null || $voucher->loai_phong_id == $rt['loai_phong_id']) {
                                                $applicableTotal += $subtotal;
                                            }
                                        }
                                        
                                        // Tính discount: percent nếu <= 100, fixed amount nếu > 100
                                        if ($applicableTotal > 0 && $discountValue > 0) {
                                            if ($discountValue <= 100) {
                                                $giamGia = round($applicableTotal * ($discountValue / 100));
                                            } else {
                                                $giamGia = min(round($discountValue), $applicableTotal);
                                            }
                                        }
                                    }
                                @endphp

                                <dl class="space-y-3">
                                    <div class="flex justify-between text-sm">
                                        <dt class="text-gray-600">Tổng tiền phòng</dt>
                                        <dd class="font-medium text-gray-900">
                                            {{ number_format($tienPhong, 0, ',', '.') }} VNĐ
                                        </dd>
                                    </div>



                                    @if ($giamGia > 0) 
                                        <div class="flex justify-between text-sm text-red-600">
                                            <dt>Giảm giá @if ($booking->voucher)
                                                    ({{ $booking->voucher->ma_voucher }} -
                                                    {{ $booking->voucher->gia_tri }}%)
                                                @endif
                                            </dt>
                                            <dd class="font-medium">-{{ number_format($giamGia, 0, ',', '.') }} VNĐ
                                            </dd>
                                        </div>
                                    @endif

                                    <div class="pt-3 border-t border-gray-200">
                                        <div class="flex justify-between">
                                            <dt class="text-base font-semibold text-gray-900">Tổng thanh toán</dt>
                                            <dd class="text-xl font-bold text-blue-600">
                                                {{ number_format((float) $booking->tong_tien, 0, ',', '.') }} VNĐ</dd>
                                        </div>
                                    </div>
                                </dl>

                                @if ($booking->invoice)
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <p class="text-sm text-gray-600 mb-2">Trạng thái thanh toán</p>
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                        @if ($booking->invoice->trang_thai === 'da_thanh_toan') bg-green-100 text-green-800
                                        @elseif($booking->invoice->trang_thai === 'cho_thanh_toan') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                            @if ($booking->invoice->trang_thai === 'da_thanh_toan')
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Đã thanh toán
                                            @elseif($booking->invoice->trang_thai === 'cho_thanh_toan')
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Chờ thanh toán
                                            @else
                                                Hoàn tiền
                                            @endif
                                        </span>

                                        @if ($booking->invoice->phuong_thuc)
                                            <p class="text-sm text-gray-600 mt-2">
                                                Phương thức:
                                                <span class="font-medium">
                                                    @if ($booking->invoice->phuong_thuc === 'vnpay')
                                                        VNPay
                                                    @elseif($booking->invoice->phuong_thuc === 'tien_mat')
                                                        Tiền mặt
                                                    @elseif($booking->invoice->phuong_thuc === 'chuyen_khoan')
                                                        Chuyển khoản
                                                    @else
                                                        {{ $booking->invoice->phuong_thuc }}
                                                    @endif
                                                </span>
                                            </p>
                                        @endif
                                    </div>

                                    @if ($booking->invoice->trang_thai === 'cho_thanh_toan' && $booking->trang_thai === 'da_xac_nhan')
                                        <form action="{{ route('admin.dat_phong.mark_paid', $booking->id) }}"
                                            method="POST" class="mt-4">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit"
                                                class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                                                Đánh dấu đã thanh toán
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <!-- Nút thao tác -->
                        <div class="flex justify-between space-x-3">
                            <a href="{{ route('admin.dat_phong.index') }}"
                                class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Quay lại
                            </a>
                            @if ($booking->trang_thai === 'cho_xac_nhan')
                                <a href="{{ route('admin.dat_phong.edit', $booking->id) }}"
                                    class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Sửa thông tin
                                </a>
                                <a href="{{ route('admin.dat_phong.cancel', $booking->id) }}"
                                    class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Hủy đặt phòng
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
