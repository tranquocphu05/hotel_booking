@extends('layouts.admin')

@section('title', 'Đặt phòng mới')

@push('styles')
<style>
    /* Custom dropdown arrow */
    select.appearance-none {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 12px;
        padding-right: 2.5rem;
    }

    /* TomSelect: đồng bộ với Tailwind input */
    #services_select + .ts-wrapper .ts-control{
        border-radius: 0.75rem !important;
        border-color: #e5e7eb !important;
        min-height: 44px !important;
        padding: .55rem .75rem !important;
        box-shadow: none !important;
    }
    #services_select + .ts-wrapper .ts-control:focus-within{
        border-color:#3b82f6 !important;
        box-shadow: 0 0 0 4px rgba(59,130,246,.15) !important;
    }
</style>
@endpush

@section('admin_content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
                    <h1 class="text-2xl font-bold text-gray-900">Đặt phòng mới</h1>
                    <p class="mt-1 text-sm text-gray-600">Tạo một đặt phòng mới cho khách</p>
                </div>

                <div class="p-6">
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Có lỗi xảy ra:</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul class="list-disc list-inside space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-sm text-green-800">{{ session('success') }}</p>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-800">{{ session('error') }}</p>
                        </div>
                    @endif

                    <form action="{{ route('admin.dat_phong.store') }}" method="POST" id="bookingForm">
                        @csrf
                        <div class="space-y-8">

                            <!-- Chọn loại phòng -->
                            <section>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">Chọn loại phòng</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="roomTypesContainer">
                                    @foreach ($loaiPhongs as $loaiPhong)
                                        @if($loaiPhong->so_luong_trong > 0)
                                        <div class="room-type-card relative">
                                            <input type="checkbox" name="room_types[]" id="loai_phong_{{ $loaiPhong->id }}"
                                                value="{{ $loaiPhong->id }}" class="sr-only peer room-type-checkbox"
                                                data-price="{{ $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban }}"
                                                data-base-price="{{ $loaiPhong->gia_co_ban }}"
                                                data-available="{{ $loaiPhong->so_luong_trong }}"
                                                onchange="toggleRoomType(this, {{ $loaiPhong->id }})">
                                            <label for="loai_phong_{{ $loaiPhong->id }}"
                                                class="block p-4 bg-white border-2 border-gray-200 rounded-xl cursor-pointer transition-all duration-300
                                                    peer-checked:border-blue-500 peer-checked:ring-2 peer-checked:ring-blue-500 peer-checked:bg-blue-50
                                                    hover:bg-gray-50 hover:border-gray-300 hover:shadow-md">
                                                <div class="space-y-2">
                                                    <img src="{{ asset($loaiPhong->anh ?? '/img/room/default.jpg') }}"
                                                        alt="{{ $loaiPhong->ten_loai }}"
                                                        class="w-full h-40 object-cover rounded-lg mb-2">
                                                    <h4 class="font-semibold text-gray-900">{{ $loaiPhong->ten_loai }}</h4>
                                                    <p class="text-xs text-gray-600 line-clamp-2">
                                                        {{ $loaiPhong->mo_ta ?? '' }}</p>
                                                    <div class="flex items-center justify-between">
                                                        @if ($loaiPhong->gia_khuyen_mai)
                                                            <div>
                                                                <p class="text-sm font-medium text-red-600">
                                                                    {{ number_format($loaiPhong->gia_khuyen_mai, 0, ',', '.') }}
                                                                    VNĐ/đêm
                                                                </p>
                                                                <p class="text-xs text-gray-500 line-through">
                                                                    {{ number_format($loaiPhong->gia_co_ban, 0, ',', '.') }}
                                                                    VNĐ
                                                                </p>
                                                            </div>
                                                        @else
                                                            <p class="text-sm font-medium text-blue-600">
                                                                {{ number_format($loaiPhong->gia_co_ban, 0, ',', '.') }}
                                                                VNĐ/đêm
                                                            </p>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center space-x-2 text-sm">
                                                        <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">
                                                            {{ $loaiPhong->trang_thai === 'hoat_dong' ? 'Khả dụng' : 'Không khả dụng' }}
                                                        </span>
                                                        <span class="text-xs text-gray-600 availability-text"
                                                            id="availability_text_{{ $loaiPhong->id }}"
                                                            data-loai-phong-id="{{ $loaiPhong->id }}">
                                                            Còn {{ $loaiPhong->so_luong_trong }} phòng
                                                        </span>
                                                    </div>
                                                </div>
                                            </label>

                                            {{-- Hidden inputs luôn được submit --}}
                                            <input type="hidden" name="rooms[{{ $loaiPhong->id }}][so_luong]"
                                                id="quantity_hidden_{{ $loaiPhong->id }}" value="0">
                                            <input type="hidden" name="rooms[{{ $loaiPhong->id }}][loai_phong_id]"
                                                value="{{ $loaiPhong->id }}">
                                            <input type="hidden" name="rooms[{{ $loaiPhong->id }}][so_nguoi]"
                                                id="adults_hidden_{{ $loaiPhong->id }}" value="0">
                                            <input type="hidden" name="rooms[{{ $loaiPhong->id }}][so_tre_em]"
                                                id="children_hidden_{{ $loaiPhong->id }}" value="0">
                                            <input type="hidden" name="rooms[{{ $loaiPhong->id }}][so_em_be]"
                                                id="infants_hidden_{{ $loaiPhong->id }}" value="0">

                                            {{-- Số lượng phòng với design mới (ẩn mặc định) --}}
                                            <div class="room-quantity-container mt-3 hidden"
                                                id="quantity_container_{{ $loaiPhong->id }}">
                                                <div class="flex items-center justify-between bg-white border border-gray-300 rounded-xl p-2 shadow-sm">
                                                    <label for="quantity_{{ $loaiPhong->id }}"
                                                        class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">
                                                        Số lượng
                                                    </label>
                                                    <div class="flex items-center gap-3">
                                                        <div class="inline-flex items-center border border-gray-200 rounded-lg overflow-hidden bg-gray-50">
                                                            <button type="button"
                                                                class="w-8 h-8 flex items-center justify-center hover:bg-red-50 hover:text-red-600 text-gray-500 transition-colors"
                                                                onclick="decreaseQuantity({{ $loaiPhong->id }})"
                                                                tabindex="-1">
                                                                <i class="fas fa-minus text-[10px]"></i>
                                                            </button>
                                                            <input type="text" id="quantity_{{ $loaiPhong->id }}"
                                                                class="room-quantity-input w-10 text-center border-none bg-transparent focus:ring-0 focus:outline-none text-sm font-bold text-gray-800 p-0"
                                                                value="1"
                                                                onchange="updateQuantityHidden({{ $loaiPhong->id }})"
                                                                oninput="validateQuantity(this, {{ $loaiPhong->id }})"
                                                                readonly>
                                                            <button type="button"
                                                                class="w-8 h-8 flex items-center justify-center hover:bg-green-50 hover:text-green-600 text-gray-500 transition-colors"
                                                                onclick="increaseQuantity({{ $loaiPhong->id }})"
                                                                tabindex="-1">
                                                                <i class="fas fa-plus text-[10px]"></i>
                                                            </button>
                                                        </div>
                                                        <span class="text-xs text-gray-400 font-medium mr-1">
                                                            / <span id="max_available_{{ $loaiPhong->id }}"
                                                                data-max="{{ $loaiPhong->so_luong_trong }}">{{ $loaiPhong->so_luong_trong }}</span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <p class="text-xs text-red-600 mt-1 hidden"
                                                    id="quantity_error_{{ $loaiPhong->id }}">
                                                    Số lượng không được vượt quá <span
                                                        id="max_available_error_{{ $loaiPhong->id }}">{{ $loaiPhong->so_luong_trong }}</span>
                                                    phòng
                                                </p>

                                                {{-- Số khách cho loại phòng này --}}
{{-- Số khách cho loại phòng này --}}
<div class="mt-4 max-w-lg">
    <div class="flex items-center gap-2 mb-3">
        <div class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center">
            <i class="fas fa-users text-xs"></i>
        </div>
        <p class="text-sm font-semibold text-gray-800">
            Số khách cho loại phòng này
        </p>
    </div>

    <div class="grid grid-cols-3 gap-2">
        {{-- Người lớn --}}
        <div class="rounded-xl border border-blue-200 bg-blue-50 px-2 py-2 text-center">
            <div class="flex items-center justify-center gap-1 mb-1">
                <i class="fas fa-user-tie text-blue-600 text-xs"></i>
                <span class="text-xs font-semibold text-blue-700">Người lớn</span>
            </div>

            <div class="flex items-center justify-center gap-1">
                <button type="button"
                    onclick="changeRoomGuestCount('adults', {{ $loaiPhong->id }}, -1)"
                    class="w-6 h-6 rounded-md border border-blue-300 text-blue-600 hover:bg-blue-600 hover:text-white text-xs">
                    −
                </button>

                <input
                    id="room_adults_{{ $loaiPhong->id }}"
                    type="number"
                    readonly
                    value="2"
                    class="w-8 text-center text-sm font-bold bg-white border border-blue-200 rounded-md">

                <button type="button"
                    onclick="changeRoomGuestCount('adults', {{ $loaiPhong->id }}, 1)"
                    class="w-6 h-6 rounded-md border border-blue-300 text-blue-600 hover:bg-blue-600 hover:text-white text-xs">
                    +
                </button>
            </div>
        </div>

        {{-- Trẻ em --}}
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-2 py-2 text-center">
            <div class="flex items-center justify-center gap-1 mb-1">
                <i class="fas fa-child text-emerald-600 text-xs"></i>
                <span class="text-xs font-semibold text-emerald-700">Trẻ em</span>
            </div>

            <div class="flex items-center justify-center gap-1">
                <button type="button"
                    onclick="changeRoomGuestCount('children', {{ $loaiPhong->id }}, -1)"
                    class="w-6 h-6 rounded-md border border-emerald-300 text-emerald-600 hover:bg-emerald-600 hover:text-white text-xs">
                    −
                </button>

                <input
                    id="room_children_{{ $loaiPhong->id }}"
                    type="number"
                    readonly
                    value="0"
                    class="w-8 text-center text-sm font-bold bg-white border border-emerald-200 rounded-md">

                <button type="button"
                    onclick="changeRoomGuestCount('children', {{ $loaiPhong->id }}, 1)"
                    class="w-6 h-6 rounded-md border border-emerald-300 text-emerald-600 hover:bg-emerald-600 hover:text-white text-xs">
                    +
                </button>
            </div>
        </div>

        {{-- Em bé --}}
        <div class="rounded-xl border border-pink-200 bg-pink-50 px-2 py-2 text-center">
            <div class="flex items-center justify-center gap-1 mb-1">
                <i class="fas fa-baby text-pink-600 text-xs"></i>
                <span class="text-xs font-semibold text-pink-700">Em bé</span>
            </div>

            <div class="flex items-center justify-center gap-1">
                <button type="button"
                    onclick="changeRoomGuestCount('infants', {{ $loaiPhong->id }}, -1)"
                    class="w-6 h-6 rounded-md border border-pink-300 text-pink-600 hover:bg-pink-600 hover:text-white text-xs">
                    −
                </button>

                <input
                    id="room_infants_{{ $loaiPhong->id }}"
                    type="number"
                    readonly
                    value="0"
                    class="w-8 text-center text-sm font-bold bg-white border border-pink-200 rounded-md">

                <button type="button"
                    onclick="changeRoomGuestCount('infants', {{ $loaiPhong->id }}, 1)"
                    class="w-6 h-6 rounded-md border border-pink-300 text-pink-600 hover:bg-pink-600 hover:text-white text-xs">
                    +
                </button>
            </div>
        </div>
    </div>
</div>

                                            </div>

                                            {{-- Danh sách các phòng cụ thể có sẵn --}}
                                            <div id="available_rooms_{{ $loaiPhong->id }}" class="available-rooms mt-3 grid grid-cols-3 gap-2 hidden"></div>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                                @error('room_types')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <div id="room_types_error" class="mt-2 text-sm text-red-600 hidden">
                                    Vui lòng chọn ít nhất một loại phòng
                                </div>
                            </section>

                            <!-- Thông tin đặt phòng -->
                            <section>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">Thông tin đặt phòng</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="ngay_nhan" class="block text-sm font-medium text-gray-700">Ngày nhận phòng</label>
                                        <input type="date" name="ngay_nhan" id="ngay_nhan"
                                            value="{{ old('ngay_nhan', \Carbon\Carbon::today()->format('Y-m-d')) }}"
                                            min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        @error('ngay_nhan')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="ngay_tra" class="block text-sm font-medium text-gray-700">Ngày trả phòng</label>
                                        <input type="date" name="ngay_tra" id="ngay_tra"
                                            value="{{ old('ngay_tra', \Carbon\Carbon::tomorrow()->format('Y-m-d')) }}"
                                            min="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        @error('ngay_tra')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Chọn số khách (chung) --}}
<div class="bg-gray-50 border border-gray-200 rounded-2xl p-5">
    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">
        Cấu hình khách hàng
    </p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Người lớn --}}
            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 flex flex-col justify-between">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div>
                    <p class="font-bold text-blue-900 text-sm">Người lớn</p>
                    <p class="text-[10px] text-blue-500 uppercase">Trên 12 tuổi</p>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button type="button"
                    onclick="changeGuestCount('adults', -1)"
                    class="w-11 h-11 rounded-xl border border-blue-300 text-blue-600 hover:bg-blue-600 hover:text-white transition">
                    −
                </button>

                <input id="total_adults" name="so_nguoi" readonly
                    class="w-16 h-11 text-center text-2xl font-extrabold bg-white border border-blue-200 rounded-xl"
                    value="{{ old('so_nguoi', 2) }}">

                <button type="button"
                    onclick="changeGuestCount('adults', 1)"
                    class="w-11 h-11 rounded-xl border border-blue-300 text-blue-600 hover:bg-blue-600 hover:text-white transition">
                    +
                </button>
            </div>
        </div>

        {{-- Trẻ em --}}
            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 flex flex-col justify-between">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center">
                    <i class="fas fa-child"></i>
                </div>
                <div>
                    <p class="font-bold text-emerald-900 text-sm">Trẻ em</p>
                    <p class="text-[10px] text-emerald-500 uppercase">6 – 12 tuổi</p>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button type="button"
                    onclick="changeGuestCount('children', -1)"
                    class="w-11 h-11 rounded-xl border border-emerald-300 text-emerald-600 hover:bg-emerald-600 hover:text-white transition">
                    −
                </button>

                <input id="total_children" name="so_tre_em" readonly
                    class="w-16 h-11 text-center text-2xl font-extrabold bg-white border border-emerald-200 rounded-xl"
                    value="{{ old('so_tre_em', 0) }}">

                <button type="button"
                    onclick="changeGuestCount('children', 1)"
                    class="w-11 h-11 rounded-xl border border-emerald-300 text-emerald-600 hover:bg-emerald-600 hover:text-white transition">
                    +
                </button>
            </div>
        </div>

        {{-- Em bé --}}
            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 flex flex-col justify-between">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-pink-600 text-white flex items-center justify-center">
                    <i class="fas fa-baby"></i>
                </div>
                <div>
                    <p class="font-bold text-pink-900 text-sm">Em bé</p>
                    <p class="text-[10px] text-pink-500 uppercase">Dưới 6 tuổi</p>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button type="button"
                    onclick="changeGuestCount('infants', -1)"
                    class="w-11 h-11 rounded-xl border border-pink-300 text-pink-600 hover:bg-pink-600 hover:text-white transition">
                    −
                </button>

                <input id="total_infants" name="so_em_be" readonly
                    class="w-16 h-11 text-center text-2xl font-extrabold bg-white border border-pink-200 rounded-xl"
                    value="{{ old('so_em_be', 0) }}">

                <button type="button"
                    onclick="changeGuestCount('infants', 1)"
                    class="w-11 h-11 rounded-xl border border-pink-300 text-pink-600 hover:bg-pink-600 hover:text-white transition">
                    +
                </button>
            </div>
        </div>
    </div>
</div>

                            </section>

                            <!-- ✅ Chọn mã giảm giá (UI ĐẸP) -->
                            <section class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                                    <h3 class="text-base font-semibold text-gray-900">Chọn mã giảm giá</h3>
                                    <span class="text-xs text-gray-500">Tùy chọn</span>
                                </div>

                                <div class="p-6">
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
@foreach ($vouchers as $voucher)
@php
    $isDisabled = $voucher->status !== 'con_han';
@endphp

<div class="relative">
    <input type="radio"
        name="voucher_radio"
        id="voucher_{{ $voucher->id }}"
        class="peer hidden voucher-radio"
        value="{{ $voucher->ma_voucher }}"
        data-value="{{ $voucher->gia_tri }}"
        data-loai-phong="{{ $voucher->loai_phong_id }}"
        data-min-condition="{{ $voucher->dieu_kien }}"
        data-start="{{ $voucher->ngay_bat_dau }}"
        data-end="{{ $voucher->ngay_ket_thuc }}"
        {{ $isDisabled ? 'disabled' : '' }}>

    <label for="voucher_{{ $voucher->id }}"
        class="block h-full rounded-2xl border bg-white p-4 cursor-pointer transition
            hover:shadow-md hover:border-gray-300
            peer-checked:border-green-500 peer-checked:ring-2 peer-checked:ring-green-300
            {{ $isDisabled ? 'opacity-50 cursor-not-allowed' : '' }}">

        <div class="flex justify-between items-start mb-3">
            <p class="font-extrabold tracking-wide text-sm text-gray-900">
                {{ $voucher->ma_voucher }}
            </p>
            <span class="text-xs font-bold px-2 py-1 rounded-full bg-green-100 text-green-700">
                {{ $voucher->gia_tri <= 100 ? $voucher->gia_tri.'%' : number_format($voucher->gia_tri,0,',','.').'₫' }}
            </span>
        </div>

        <p class="text-xs text-gray-600 mb-2">
            {{ $voucher->gia_tri <= 100 ? 'Giảm '.$voucher->gia_tri.'%' : 'Giảm '.number_format($voucher->gia_tri,0,',','.').' VNĐ' }}
        </p>

        @if($voucher->dieu_kien)
            <p class="text-xs bg-gray-100 rounded-lg p-2 mb-2 text-gray-600">
                {{ $voucher->dieu_kien }}
            </p>
        @endif

        <div class="flex justify-between text-xs text-gray-500">
            <span>Còn {{ $voucher->so_luong }}</span>
            <span>HSD {{ date('d/m/Y', strtotime($voucher->ngay_ket_thuc)) }}</span>
        </div>
    </label>
</div>
@endforeach
</div>

                                </div>
                            </section>

                            <!-- ✅ Chọn dịch vụ (UI ĐẸP) -->
                            <section class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                                    <h3 class="text-base font-semibold text-gray-900">Chọn dịch vụ kèm theo</h3>
                                    <span class="text-xs text-gray-500">Nhiều lựa chọn</span>
                                </div>

                                <div class="p-6">
                                    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css" rel="stylesheet">

                                    <label for="services_select" class="block text-sm font-medium text-gray-700 mb-2">
                                        Dịch vụ
                                    </label>

                                    <select id="services_select" placeholder="Chọn 1 hoặc nhiều dịch vụ..." multiple>
                                        @foreach ($services as $service)
                                            <option value="{{ $service->id }}" data-price="{{ $service->price }}" data-unit="{{ $service->unit ?? '' }}">
                                                {{ $service->name }} - {{ number_format($service->price,0,',','.') }} VNĐ
                                            </option>
                                        @endforeach
                                    </select>

                                    <div id="selected_services_list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4"></div>
                                </div>
                            </section>

                            <input type="hidden" name="tong_tien" id="tong_tien_input" value="0">
                            <input type="hidden" name="voucher" id="voucher_input" value="">
                            <input type="hidden" name="voucher_id" id="voucher_id_input" value="">

                            <!-- ✅ Tổng thanh toán (UI ĐẸP) -->
                            <section class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                                <div class="px-6 py-4 border-b border-gray-100">
                                    <h3 class="text-base font-semibold text-gray-900">Tổng thanh toán</h3>
                                    <p class="text-xs text-gray-500 mt-1">Tự động cập nhật theo lựa chọn</p>
                                </div>

                                <div class="p-6 space-y-4">
                                    <div id="guest_summary" class="hidden">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                                            Tổng số khách
                                        </p>
                                        <div class="flex flex-wrap gap-2 items-center">
                                            <div id="total_adults_display"
                                                 class="hidden px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-900 text-white">
                                                Người lớn: <span id="adults_count">0</span>
                                            </div>
                                            <div id="total_children_display"
                                                 class="hidden px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-600 text-white">
                                                Trẻ em: <span id="children_count">0</span>
                                            </div>
                                            <div id="total_infants_display"
                                                 class="hidden px-3 py-1.5 rounded-full text-xs font-semibold bg-pink-600 text-white">
                                                Em bé: <span id="infants_count">0</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="surcharge_summary" class="hidden space-y-2">
                                        <div id="extra_adult_fee" class="hidden text-sm text-amber-700 flex justify-between">
                                            <span>Thêm người lớn</span>
                                            <span class="font-semibold">+<span id="extra_adult_fee_amount">0</span> VNĐ</span>
                                        </div>
                                        <div id="child_fee" class="hidden text-sm text-emerald-700 flex justify-between">
                                            <span>Thêm trẻ em</span>
                                            <span class="font-semibold">+<span id="child_fee_amount">0</span> VNĐ</span>
                                        </div>
                                        <div id="infant_fee" class="hidden text-sm text-pink-700 flex justify-between">
                                            <span>Thêm em bé</span>
                                            <span class="font-semibold">+<span id="infant_fee_amount">0</span> VNĐ</span>
                                        </div>
                                    </div>

                                    <div id="discount_info" class="hidden border-t border-gray-100 pt-4 space-y-2">
                                        <div class="flex justify-between text-sm text-gray-600">
                                            <span>Giá gốc</span>
                                            <span id="original_price" class="line-through text-gray-400">0 VNĐ</span>
                                        </div>
                                        <div class="flex justify-between text-sm text-emerald-700 font-semibold">
                                            <span>Giảm giá</span>
                                            <span id="discount_amount">-0 VNĐ</span>
                                        </div>
                                    </div>

                                    <div class="border-t border-gray-100 pt-4 flex items-end justify-between">
                                        <span class="text-sm font-semibold text-gray-900">Tổng cộng</span>
                                        <span id="total_price" class="text-2xl font-extrabold text-red-600">0 VNĐ</span>
                                    </div>

                                    <div id="pricing_multiplier_info" class="hidden text-xs text-gray-500"></div>
                                </div>
                            </section>

                            <!-- ✅ Thông tin người đặt (UI ĐẸP) -->
                            <section class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                                <div class="px-6 py-4 border-b border-gray-100">
                                    <h3 class="text-base font-semibold text-gray-900">Thông tin người đặt</h3>
                                </div>

                                <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Họ và tên</label>
                                            <input type="text" name="username" id="username"
                                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none transition
                                                       focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                                value="{{ old('username') }}" required>
                                            @error('username')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                            <input type="text" name="email" id="email"
                                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none transition
                                                       focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                                value="{{ old('email') }}" required>
                                            @error('email')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="sdt" class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại</label>
                                            <input type="text" name="sdt" id="sdt"
                                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none transition
                                                       focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                                value="{{ old('sdt') }}" required>
                                            @error('sdt')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="cccd" class="block text-sm font-medium text-gray-700 mb-2">CCCD/CMND</label>
                                            <input type="text" name="cccd" id="cccd"
                                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none transition
                                                       focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                                value="{{ old('cccd') }}" required>
                                            @error('cccd')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div id="validation-errors" class="hidden mt-4 p-4 bg-red-50 border border-red-200 rounded-2xl">
                                        <div class="flex gap-3">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-sm font-semibold text-red-800">Thông tin không hợp lệ</h3>
                                                <div class="mt-2 text-sm text-red-700">
                                                    <ul id="error-list" class="list-disc list-inside space-y-1"></ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- ✅ Form Actions (UI ĐẸP) -->
                            <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                                <a href="{{ route('admin.dat_phong.index') }}"
                                    class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-700 font-medium hover:bg-gray-50 transition">
                                    Hủy bỏ
                                </a>
                                <button type="submit"
                                    class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-sm hover:shadow transition">
                                    Đặt phòng
                                </button>
                            </div>

                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let roomIndex = 0;
            const allLoaiPhongs = @json($loaiPhongs);

            function changeGuestCount(type, delta) {
                const inputId = type === 'adults' ? 'total_adults' : (type === 'children' ? 'total_children' : 'total_infants');
                const input = document.getElementById(inputId);
                if (input) {
                    let newValue = parseInt(input.value) + delta;
                    const min = parseInt(input.getAttribute('min')) || 0;
                    const max = parseInt(input.getAttribute('max')) || 10;

                    if (newValue >= min && newValue <= max) {
                        input.value = newValue;
                        const event = new Event('change');
                        input.dispatchEvent(event);
                        if (typeof updateTotalPrice === 'function') updateTotalPrice();
                    }
                }
            }

            function formatCurrency(amount) {
                return new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(amount).replace('₫', 'VNĐ');
            }

            function changeRoomGuestCount(type, roomTypeId, delta) {
                const inputId = 'room_' + type + '_' + roomTypeId;
                const input = document.getElementById(inputId);
                if (!input) {
                    console.warn('changeRoomGuestCount: input not found', inputId);
                    return;
                }

                const quantityInput = document.getElementById('quantity_' + roomTypeId);
                const roomQuantity = parseInt(quantityInput?.value) || 1;

                let maxPerRoom = 3;
                if (type === 'children') maxPerRoom = 2;
                if (type === 'infants') maxPerRoom = 2;

                const max = roomQuantity * maxPerRoom;
                const min = type === 'adults' ? 1 : 0;

                let newValue = parseInt(input.value) + delta;

                if (newValue >= min && newValue <= max) {
                    input.value = newValue;

                    const hiddenId = type + '_hidden_' + roomTypeId;
                    const hiddenInput = document.getElementById(hiddenId);
                    if (hiddenInput) hiddenInput.value = newValue;

                    syncGlobalGuestCounts();

                    if (typeof updateTotalPrice === 'function') updateTotalPrice();
                }
            }

            function syncGlobalGuestCounts() {
                let totalAdults = 0;
                let totalChildren = 0;
                let totalInfants = 0;

                const selectedRoomTypes = Array.from(document.querySelectorAll('.room-type-checkbox:checked'));

                selectedRoomTypes.forEach(checkbox => {
                    const roomTypeId = checkbox.value;

                    const adultsInput = document.getElementById('room_adults_' + roomTypeId);
                    const childrenInput = document.getElementById('room_children_' + roomTypeId);
                    const infantsInput = document.getElementById('room_infants_' + roomTypeId);

                    if (adultsInput) totalAdults += parseInt(adultsInput.value) || 0;
                    if (childrenInput) totalChildren += parseInt(childrenInput.value) || 0;
                    if (infantsInput) totalInfants += parseInt(infantsInput.value) || 0;

                    const adultsHidden = document.getElementById('adults_hidden_' + roomTypeId);
                    const childrenHidden = document.getElementById('children_hidden_' + roomTypeId);
                    const infantsHidden = document.getElementById('infants_hidden_' + roomTypeId);

                    if (adultsHidden && adultsInput) adultsHidden.value = adultsInput.value;
                    if (childrenHidden && childrenInput) childrenHidden.value = childrenInput.value;
                    if (infantsHidden && infantsInput) infantsHidden.value = infantsInput.value;
                });

                const totalAdultsInput = document.getElementById('total_adults');
                const totalChildrenInput = document.getElementById('total_children');
                const totalInfantsInput = document.getElementById('total_infants');

                if (totalAdultsInput) totalAdultsInput.value = totalAdults;
                if (totalChildrenInput) totalChildrenInput.value = totalChildren;
                if (totalInfantsInput) totalInfantsInput.value = totalInfants;

                if (typeof updateTotalPrice === 'function') updateTotalPrice();
            }

            function toggleRoomType(checkbox, roomTypeId) {
                const quantityContainer = document.getElementById('quantity_container_' + roomTypeId);
                const quantityInput = document.getElementById('quantity_' + roomTypeId);

                if (checkbox.checked) {
                    quantityContainer.classList.remove('hidden');
                    quantityInput.required = true;
                    const hiddenInput = document.getElementById('quantity_hidden_' + roomTypeId);
                    if (hiddenInput) hiddenInput.value = quantityInput.value || 1;

                    const roomAdultsInput = document.getElementById('room_adults_' + roomTypeId);
                    const roomChildrenInput = document.getElementById('room_children_' + roomTypeId);
                    const roomInfantsInput = document.getElementById('room_infants_' + roomTypeId);
                    if (roomAdultsInput) roomAdultsInput.value = 2;
                    if (roomChildrenInput) roomChildrenInput.value = 0;
                    if (roomInfantsInput) roomInfantsInput.value = 0;

                    syncGlobalGuestCounts();

                    const roomsContainer = document.getElementById('available_rooms_' + roomTypeId);
                    if (roomsContainer) roomsContainer.classList.remove('hidden');
                    try { updateRoomAvailability(roomTypeId); } catch(e){}
                } else {
                    quantityContainer.classList.add('hidden');
                    quantityInput.required = false;
                    quantityInput.value = 1;

                    const hiddenInput = document.getElementById('quantity_hidden_' + roomTypeId);
                    if (hiddenInput) hiddenInput.value = 0;

                    const adultsHidden = document.getElementById('adults_hidden_' + roomTypeId);
                    const childrenHidden = document.getElementById('children_hidden_' + roomTypeId);
                    const infantsHidden = document.getElementById('infants_hidden_' + roomTypeId);
                    if (adultsHidden) adultsHidden.value = 0;
                    if (childrenHidden) childrenHidden.value = 0;
                    if (infantsHidden) infantsHidden.value = 0;

                    const roomAdultsInput = document.getElementById('room_adults_' + roomTypeId);
                    const roomChildrenInput = document.getElementById('room_children_' + roomTypeId);
                    const roomInfantsInput = document.getElementById('room_infants_' + roomTypeId);
                    if (roomAdultsInput) roomAdultsInput.value = 2;
                    if (roomChildrenInput) roomChildrenInput.value = 0;
                    if (roomInfantsInput) roomInfantsInput.value = 0;

                    syncGlobalGuestCounts();

                    const roomsContainer = document.getElementById('available_rooms_' + roomTypeId);
                    if (roomsContainer) { roomsContainer.classList.add('hidden'); roomsContainer.innerHTML = ''; }
                }

                updateVoucherAvailability();
                updateTotalPrice();
            }

            function updateQuantityHidden(roomTypeId) {
                const displayInput = document.getElementById('quantity_' + roomTypeId);
                const hiddenInput = document.getElementById('quantity_hidden_' + roomTypeId);
                if (displayInput && hiddenInput) {
                    hiddenInput.value = displayInput.value;
                    syncAllGuests();
                    updateTotalPrice();
                }
            }

            function enforceRoomSelectionLimit(loaiPhongId) {
                const boxes = Array.from(document.querySelectorAll('#available_rooms_' + loaiPhongId + ' input.available-room-checkbox'));
                boxes.forEach(b => {
                    b.onchange = function(e) {
                        const wantNow = parseInt(document.getElementById('quantity_' + loaiPhongId)?.value || 0);
                        const boxesNow = Array.from(document.querySelectorAll('#available_rooms_' + loaiPhongId + ' input.available-room-checkbox'));
                        const checkedNow = boxesNow.filter(x=>x.checked).length;
                        if (checkedNow > wantNow) {
                            this.checked = false;
                            alert('Bạn chỉ được chọn tối đa ' + wantNow + ' phòng cho loại này');
                        }
                        updateServiceRoomLists();
                    };
                });
            }

            function updateServiceRoomLists() {
                const selectedRoomInputs = Array.from(document.querySelectorAll('.available-room-checkbox:checked'));
                document.querySelectorAll('[data-service-id]').forEach(card => {
                    const serviceId = card.getAttribute('data-service-id');
                    const roomCheckboxContainer = document.getElementById('room_checkboxes_' + serviceId);
                    if (roomCheckboxContainer) {
                        roomCheckboxContainer.innerHTML = '';
                        selectedRoomInputs.forEach(inp => {
                            const wrapper = document.createElement('div');
                            wrapper.className = 'flex items-center gap-2 py-1';

                            const checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.className = 'service-room-checkbox';
                            checkbox.setAttribute('data-room-id', inp.value);
                            checkbox.value = inp.value;
                            checkbox.onchange = () => syncHiddenEntries(serviceId);

                            const label = document.createElement('label');
                            label.className = 'text-xs cursor-pointer';
                            label.textContent = inp.getAttribute('data-room-name') || ('Phòng ' + inp.value);

                            wrapper.appendChild(checkbox);
                            wrapper.appendChild(label);
                            roomCheckboxContainer.appendChild(wrapper);
                        });
                    }

                    const rows = card.querySelectorAll('.service-date-row');
                    const specificRadio = card.querySelector('input[name="service_room_mode_' + serviceId + '"][value="specific"]');
                    const isSpecific = specificRadio ? specificRadio.checked : false;

                    rows.forEach((r, idx) => {
                        let entryRoomContainer = r.querySelector('.entry-room-container');
                        if (!entryRoomContainer) {
                            entryRoomContainer = document.createElement('div');
                            entryRoomContainer.className = 'entry-room-container mt-2 pl-2 border-l';
                            r.appendChild(entryRoomContainer);
                        }
                        entryRoomContainer.innerHTML = '';

                        if (!isSpecific) {
                            entryRoomContainer.classList.add('hidden');
                            return;
                        } else {
                            entryRoomContainer.classList.remove('hidden');
                        }

                        selectedRoomInputs.forEach(inp => {
                            const ewrap = document.createElement('div'); ewrap.className = 'inline-flex items-center gap-1 mr-2';
                            const ecb = document.createElement('input'); ecb.type = 'checkbox'; ecb.className = 'entry-room-checkbox'; ecb.setAttribute('data-room-id', inp.value);
                            ecb.value = inp.value; ecb.checked = true;
                            ecb.onchange = () => {
                                const serviceId = card.getAttribute('data-service-id');
                                setTimeout(() => {
                                    try {
                                        if (typeof window.syncHiddenEntries === 'function') {
                                            window.syncHiddenEntries(serviceId);
                                        }
                                    } catch(e) { console.error('syncHiddenEntries error:', e); }
                                    try { updateTotalPrice(); } catch(e) { console.error('updateTotalPrice error:', e); }
                                }, 0);
                            };
                            const elbl = document.createElement('label'); elbl.className='text-xs'; elbl.textContent = inp.getAttribute('data-room-name') || ('Phòng ' + inp.value);
                            ewrap.appendChild(ecb); ewrap.appendChild(elbl);
                            entryRoomContainer.appendChild(ewrap);
                        });
                    });
                });
            }

            function updateQuantityHidden(roomTypeId) {
                const displayInput = document.getElementById('quantity_' + roomTypeId);
                const hiddenInput = document.getElementById('quantity_hidden_' + roomTypeId);
                if (displayInput && hiddenInput) {
                    hiddenInput.value = displayInput.value;
                }
            }

            function getMaxAvailable(roomTypeId) {
                const maxElement = document.getElementById('max_available_' + roomTypeId);
                return maxElement ? parseInt(maxElement.textContent) || 0 : 0;
            }

            function validateQuantity(input, roomTypeId) {
                const maxAvailable = getMaxAvailable(roomTypeId);
                const value = parseInt(input.value) || 0;
                const errorElement = document.getElementById('quantity_error_' + roomTypeId);
                const hiddenInput = document.getElementById('quantity_hidden_' + roomTypeId);
                const maxErrorElement = document.getElementById('max_available_error_' + roomTypeId);
                if (maxErrorElement) maxErrorElement.textContent = maxAvailable;

                if (maxAvailable > 0 && value > maxAvailable) {
                    errorElement?.classList.remove('hidden');
                    input.value = maxAvailable;
                    if (hiddenInput) hiddenInput.value = maxAvailable;
                } else if (value < 1) {
                    errorElement?.classList.add('hidden');
                    input.value = 1;
                    if (hiddenInput) hiddenInput.value = 1;
                } else {
                    errorElement?.classList.add('hidden');
                    if (hiddenInput) hiddenInput.value = value;
                }

                try { enforceRoomSelectionLimit(roomTypeId); } catch(e){}

                try {
                    const want = parseInt(document.getElementById('quantity_' + roomTypeId)?.value || 0);
                    const boxes = Array.from(document.querySelectorAll('#available_rooms_' + roomTypeId + ' input.available-room-checkbox'));
                    if (boxes.length > 0) {
                        const checkedBoxes = boxes.filter(b => b.checked);
                        const checkedCount = checkedBoxes.length;

                        if (want > checkedCount) {
                            const uncheckedBoxes = boxes.filter(b => !b.checked);
                            const toCheck = want - checkedCount;
                            for (let i = 0; i < Math.min(toCheck, uncheckedBoxes.length); i++) uncheckedBoxes[i].checked = true;
                        } else if (want < checkedCount) {
                            const toUncheck = checkedCount - want;
                            for (let i = 0; i < toUncheck; i++) checkedBoxes[checkedCount - 1 - i].checked = false;
                        }
                        updateServiceRoomLists();
                    }
                } catch(e){ console.error('Sync room checkboxes error:', e); }

                try { updateServiceRoomLists(); } catch(e){}
                syncAllGuests();
                updateTotalPrice();
            }

            function decreaseQuantity(roomTypeId) {
                try {
                    const input = document.getElementById('quantity_' + roomTypeId);
                    if (!input) return;
                    const currentValue = parseInt(input.value) || 1;
                    if (currentValue > 1) {
                        const oldValue = currentValue;
                        input.value = currentValue - 1;
                        updateQuantityHidden(roomTypeId);
                        validateQuantity(input, roomTypeId);
                        
                        // Tự động giảm số khách theo tỷ lệ số lượng phòng
                        updateGuestsByRoomQuantity(oldValue, currentValue - 1);
                    }
                } catch (e) {
                    console.error('decreaseQuantity error for', roomTypeId, e);
                }
            }

            function increaseQuantity(roomTypeId) {
                try {
                    const input = document.getElementById('quantity_' + roomTypeId);
                    if (!input) return;
                    let maxAvailable = getMaxAvailable(roomTypeId);
                    if (!maxAvailable || maxAvailable <= 0) maxAvailable = Infinity;
                    const currentValue = parseInt(input.value) || 1;
                    if (currentValue < maxAvailable) {
                        const oldValue = currentValue;
                        input.value = currentValue + 1;
                        updateQuantityHidden(roomTypeId);
                        validateQuantity(input, roomTypeId);
                        
                        // Tự động tăng số khách theo tỷ lệ số lượng phòng
                        updateGuestsByRoomQuantity(oldValue, currentValue + 1);
                    }
                } catch (e) {
                    console.error('increaseQuantity error for', roomTypeId, e);
                }
            }
            
            // Hàm tự động cập nhật số khách khi số lượng phòng thay đổi
            function updateGuestsByRoomQuantity(oldQuantity, newQuantity) {
                if (oldQuantity <= 0 || newQuantity <= 0) return;
                
                const ratio = newQuantity / oldQuantity;
                
                // Lấy số khách hiện tại
                const adultsInput = document.getElementById('total_adults');
                const childrenInput = document.getElementById('total_children');
                const infantsInput = document.getElementById('total_infants');
                
                if (adultsInput) {
                    const currentAdults = parseInt(adultsInput.value) || 2;
                    const newAdults = Math.max(1, Math.round(currentAdults * ratio));
                    // Giới hạn tối đa: 3 người lớn/phòng
                    const maxAdults = newQuantity * 3;
                    adultsInput.value = Math.min(newAdults, maxAdults);
                }
                
                if (childrenInput) {
                    const currentChildren = parseInt(childrenInput.value) || 0;
                    const newChildren = Math.max(0, Math.round(currentChildren * ratio));
                    // Giới hạn tối đa: 2 trẻ em/phòng
                    const maxChildren = newQuantity * 2;
                    childrenInput.value = Math.min(newChildren, maxChildren);
                }
                
                if (infantsInput) {
                    const currentInfants = parseInt(infantsInput.value) || 0;
                    const newInfants = Math.max(0, Math.round(currentInfants * ratio));
                    // Giới hạn tối đa: 2 em bé/phòng
                    const maxInfants = newQuantity * 2;
                    infantsInput.value = Math.min(newInfants, maxInfants);
                }
                
                // Cập nhật lại phân bổ cho tất cả loại phòng
                syncAllGuests();
            }
            // Functions for managing total guests (chung cho tất cả loại phòng) - dùng dropdown
            
            // Phân bổ số khách từ trường tổng cho tất cả các loại phòng đã chọn
            function updateAllRoomGuests(type) {
                const totalInput = document.getElementById('total_' + type);
                if (!totalInput) return;

                let adultsVal = parseInt(document.getElementById('total_adults')?.value || 0);
                let childrenVal = parseInt(document.getElementById('total_children')?.value || 0);
                let infantsVal = parseInt(document.getElementById('total_infants')?.value || 0);
                const selectedRoomTypes = Array.from(document.querySelectorAll('.room-type-checkbox:checked'));

                if (selectedRoomTypes.length === 0) {
                    document.querySelectorAll('input[id*="_hidden_' + type + '"]').forEach(input => {
                        if (input.id.includes(type + '_hidden_')) input.value = 0;
                    });
                    document.getElementById('guest_limit_error')?.classList.add('hidden');
                    updateTotalPrice();
                    return;
                }

                let totalQuantity = 0;
                selectedRoomTypes.forEach(checkbox => {
                    const roomTypeId = checkbox.value;
                    const quantityInput = document.getElementById('quantity_' + roomTypeId);
                    const quantity = parseInt(quantityInput?.value || 1);
                    totalQuantity += quantity;
                });

                const inputVal = parseInt(totalInput.value) || 0;

                if (type === 'adults') adultsVal = inputVal;
                if (type === 'children') childrenVal = inputVal;
                if (type === 'infants') infantsVal = inputVal;

                document.getElementById('guest_limit_error')?.classList.add('hidden');

                selectedRoomTypes.forEach(checkbox => {
                    const roomTypeId = checkbox.value;
                    const hiddenInput = document.getElementById(type + '_hidden_' + roomTypeId);
                    if (hiddenInput) {
                        const quantityInput = document.getElementById('quantity_' + roomTypeId);
                        const quantity = parseInt(quantityInput?.value || 1);

                        let totalValueToDistribute = (type === 'adults') ? adultsVal : (type === 'children' ? childrenVal : infantsVal);
                        const guestForRoom = totalQuantity > 0 ? Math.round((totalValueToDistribute * quantity) / totalQuantity) : 0;
                        hiddenInput.value = guestForRoom;

                        const inputType = type === 'adults' ? 'room_adults_' : (type === 'children' ? 'room_children_' : 'room_infants_');
                        const roomGuestInput = document.getElementById(inputType + roomTypeId);
                        if (roomGuestInput) roomGuestInput.value = guestForRoom;
                    }
                });

                updateTotalPrice();
            }

            function syncAllGuests() {
                ['adults', 'children', 'infants'].forEach(type => updateAllRoomGuests(type));
            }

            function updateRoomAvailability(loaiPhongId) {
                const checkin = document.getElementById('ngay_nhan').value;
                const checkout = document.getElementById('ngay_tra').value;

                if (!checkin || !checkout) return;

                fetch('{{ route('admin.dat_phong.available_count') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            loai_phong_id: loaiPhongId,
                            checkin: checkin,
                            checkout: checkout,
                            include_rooms: 1
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const availableCount = data.available_count || 0;
                            const maxElement = document.getElementById('max_available_' + loaiPhongId);
                            const availabilityText = document.getElementById('availability_text_' + loaiPhongId);
                            const quantityInput = document.getElementById('quantity_' + loaiPhongId);
                            const maxErrorElement = document.getElementById('max_available_error_' + loaiPhongId);
                            
                            // Tìm card phòng để ẩn/hiện
                            const roomCard = document.querySelector(`.room-type-card input[value="${loaiPhongId}"]`)?.closest('.room-type-card');
                            const roomCheckbox = document.getElementById('loai_phong_' + loaiPhongId);

                            if (maxElement) {
                                maxElement.textContent = availableCount;
                                maxElement.setAttribute('data-max', availableCount);
                            }

                            if (availabilityText) {
                                if (availableCount > 0) {
                                    availabilityText.textContent = `Còn ${availableCount} phòng`;
                                    availabilityText.className = 'text-xs text-gray-600';
                                } else {
                                    availabilityText.textContent = 'Hết phòng';
                                    availabilityText.className = 'text-xs text-red-600 font-medium';
                                }
                            }

                            if (maxErrorElement) maxErrorElement.textContent = availableCount;

                            // Ẩn card phòng nếu hết phòng (availableCount === 0)
                            if (availableCount === 0) {
                                if (roomCard) {
                                    roomCard.style.display = 'none';
                                }
                                // Bỏ chọn checkbox nếu đang được chọn
                                if (roomCheckbox && roomCheckbox.checked) {
                                    roomCheckbox.checked = false;
                                    toggleRoomType(roomCheckbox, loaiPhongId);
                                }
                                if (quantityInput) {
                                    quantityInput.value = 0;
                                    updateQuantityHidden(loaiPhongId);
                                }
                            } else {
                                // Hiện lại card phòng nếu có phòng
                                if (roomCard) {
                                    roomCard.style.display = '';
                                }
                                if (quantityInput && parseInt(quantityInput.value) > availableCount) {
                                    quantityInput.value = availableCount;
                                    updateQuantityHidden(loaiPhongId);
                                    validateQuantity(quantityInput, loaiPhongId);
                                }
                            }

                            const roomsContainer = document.getElementById('available_rooms_' + loaiPhongId);
                            if (roomsContainer) {
                                roomsContainer.innerHTML = '';
                                if (Array.isArray(data.rooms) && data.rooms.length > 0 && document.getElementById('loai_phong_' + loaiPhongId)?.checked) {
                                    roomsContainer.classList.remove('hidden');

                                    data.rooms.forEach((r, idx) => {
                                        const wrap = document.createElement('div');
                                        wrap.className = 'flex items-center gap-2 py-1';

                                        const cb = document.createElement('input');
                                        cb.type = 'checkbox';
                                        cb.name = `rooms[${loaiPhongId}][phong_ids][]`;
                                        cb.value = r.id;
                                        cb.className = 'available-room-checkbox';
                                        cb.setAttribute('data-room-name', r.so_phong || r.ten_phong || r.name || r.id);

                                        const lbl = document.createElement('label');
                                        lbl.className = 'text-sm';
                                        lbl.textContent = r.so_phong ? ('Phòng ' + r.so_phong) : (r.ten_phong || ('Phòng ' + r.id));

                                        wrap.appendChild(cb);
                                        wrap.appendChild(lbl);
                                        roomsContainer.appendChild(wrap);
                                    });

                                    const want = parseInt(quantityInput?.value || 0);
                                    if (want > 0) {
                                        const boxes = Array.from(roomsContainer.querySelectorAll('input.available-room-checkbox'));
                                        for (let i = 0; i < Math.min(want, boxes.length); i++) boxes[i].checked = true;
                                    }

                                    try { enforceRoomSelectionLimit(loaiPhongId); } catch(e){}
                                    try { updateServiceRoomLists(); } catch(e){}
                                } else {
                                    roomsContainer.classList.add('hidden');
                                }
                            }
                        }
                    })
                    .catch(error => console.error('Error updating availability:', error));
            }

            function updateAllRoomAvailability() {
                const checkin = document.getElementById('ngay_nhan').value;
                const checkout = document.getElementById('ngay_tra').value;

                if (!checkin || !checkout) return;

                document.querySelectorAll('.room-type-card').forEach(card => {
                    const checkbox = card.querySelector('.room-type-checkbox');
                    if (checkbox) updateRoomAvailability(checkbox.value);
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                const ngayNhanInput = document.getElementById('ngay_nhan');
                const ngayTraInput = document.getElementById('ngay_tra');

                const today = new Date().toISOString().split('T')[0];
                ngayNhanInput.setAttribute('min', today);
                if (!ngayNhanInput.value) ngayNhanInput.value = today;

                if (!ngayTraInput.value) {
                    const tomorrow = new Date();
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    ngayTraInput.value = tomorrow.toISOString().split('T')[0];
                }

                if (ngayNhanInput.value) {
                    const minCheckout = new Date(ngayNhanInput.value);
                    minCheckout.setDate(minCheckout.getDate() + 1);
                    ngayTraInput.setAttribute('min', minCheckout.toISOString().split('T')[0]);
                    if (ngayTraInput.value && ngayTraInput.value <= ngayNhanInput.value) {
                        ngayTraInput.value = minCheckout.toISOString().split('T')[0];
                    }
                } else {
                    const tomorrow = new Date();
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    ngayTraInput.setAttribute('min', tomorrow.toISOString().split('T')[0]);
                }

                ngayNhanInput.addEventListener('change', function() {
                    ngayTraInput.setAttribute('min', this.value);
                    if (ngayTraInput.value && ngayTraInput.value < this.value) ngayTraInput.value = this.value;
                    updateTotalPrice();
                    updateAllRoomAvailability();
                });

                ngayTraInput.addEventListener('change', function() {
                    updateTotalPrice();
                    updateAllRoomAvailability();
                });

                setTimeout(() => {
                    if (ngayNhanInput.value && ngayTraInput.value) updateAllRoomAvailability();
                }, 300);

                document.querySelectorAll('.room-type-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const loaiId = this.value;
                        if (!this.checked) {
                            try {
                                const boxes = Array.from(document.querySelectorAll('#available_rooms_' + loaiId + ' input.available-room-checkbox'));
                                boxes.forEach(b => { b.checked = false; });
                            } catch(e){}
                        }

                        try { enforceRoomSelectionLimit(loaiId); } catch(e){}
                        try { updateServiceRoomLists(); } catch(e){}
                        try { updateVoucherAvailability(); } catch(e){}
                        try { updateTotalPrice(); } catch(e){}
                    });
                });

                ngayNhanInput.addEventListener('change', updateVoucherAvailability);
                ngayTraInput.addEventListener('change', updateVoucherAvailability);

                try { setupVoucherEventSystem(); } catch(e) { console.error('setupVoucherEventSystem error', e); }

                syncAllGuests();

                document.getElementById('bookingForm')?.addEventListener('submit', function() {
                    syncAllGuests();
                    updateTotalPrice();
                });

                document.getElementById('total_adults')?.addEventListener('input', function() { updateAllRoomGuests('adults'); });
                document.getElementById('total_children')?.addEventListener('input', function() { updateAllRoomGuests('children'); });
                document.getElementById('total_infants')?.addEventListener('input', function() { updateAllRoomGuests('infants'); });

                syncAllGuests();

                const totalAdultsSelect = document.getElementById('total_adults');
                const totalChildrenSelect = document.getElementById('total_children');
                const totalInfantsSelect = document.getElementById('total_infants');

                if (totalAdultsSelect) totalAdultsSelect.addEventListener('input', function() { updateAllRoomGuests('adults'); });
                if (totalChildrenSelect) totalChildrenSelect.addEventListener('input', function() { updateAllRoomGuests('children'); });
                if (totalInfantsSelect) totalInfantsSelect.addEventListener('input', function() { updateAllRoomGuests('infants'); });

                updateTotalPrice();
                updateVoucherAvailability();
            });

            function setupVoucherEventSystem() {
                document.querySelectorAll('.voucher-radio').forEach(radio => {
                    radio.addEventListener('change', handleVoucherChange);
                });

                document.querySelectorAll('.voucher-label').forEach(label => {
                    label.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const radio = this.closest('.voucher-container').querySelector('.voucher-radio');
                        if (!radio) return;
                        if (radio.disabled) return;

                        const newState = !radio.checked;
                        radio.checked = newState;
                        radio.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                });
            }

            function handleVoucherChange() {
                const voucherIdInput = document.getElementById('voucher_id_input');
                const voucherMirror = document.getElementById('voucher_input');

                if (this.checked) {
                    const container = this.closest('.voucher-container');
                    const voucherId = container?.getAttribute('data-voucher-id') || this.value;
                    if (voucherIdInput) voucherIdInput.value = voucherId;
                    if (voucherMirror) voucherMirror.value = this.value || voucherId;
                } else {
                    if (voucherIdInput) voucherIdInput.value = '';
                    if (voucherMirror) voucherMirror.value = '';
                }

                updateTotalPrice();

                try { if (typeof updateServiceRoomLists === 'function') updateServiceRoomLists(); } catch (e) {}

                try {
                    document.querySelectorAll('#selected_services_list [data-service-id]').forEach(card => {
                        const sid = card.getAttribute('data-service-id');
                        if (sid && typeof syncHiddenEntries === 'function') {
                            try { syncHiddenEntries(sid); } catch (er) {}
                        }
                    });
                } catch (e) {}
            }

            function updateVoucherAvailability() {
                const selectedRoomTypes = Array.from(document.querySelectorAll('.room-type-checkbox:checked')).map(cb => cb.value);
                const voucherInputs = document.querySelectorAll('.voucher-radio');

                const checkinDate = document.getElementById('ngay_nhan')?.value || '';
                const checkoutDate = document.getElementById('ngay_tra')?.value || '';

                if (!checkinDate || !checkoutDate) {
                    document.querySelectorAll('.voucher-container').forEach(container => {
                        const radio = container.querySelector('.voucher-radio');
                        if (radio) {
                            radio.checked = false;
                            radio.disabled = true;
                        }
                    });
                    const voucherMirror = document.getElementById('voucher_input');
                    const voucherIdInput = document.getElementById('voucher_id_input');
                    if (voucherMirror) voucherMirror.value = '';
                    if (voucherIdInput) voucherIdInput.value = '';
                    return;
                }

                // Calculate current room total (to check against voucher min condition)
                let currentRoomSubtotal = 0;
                const startDate = new Date(checkinDate);
                const endDate = new Date(checkoutDate);
                if (!isNaN(startDate.getTime()) && !isNaN(endDate.getTime()) && endDate > startDate) {
                    document.querySelectorAll('.room-type-checkbox:checked').forEach(checkbox => {
                        const roomTypeId = checkbox.value;
                        const quantityInput = document.getElementById('quantity_' + roomTypeId);
                        const quantity = parseInt(quantityInput?.value || 1);
                        const basePrice = parseFloat(checkbox.dataset.price || 0);
                        let current = new Date(startDate.getTime());
                        while (current < endDate) {
                            currentRoomSubtotal += basePrice * getMultiplierForDateJS(current) * quantity;
                            current.setDate(current.getDate() + 1);
                        }
                    });
                }

                voucherInputs.forEach(radio => {
                    const voucherRoomType = radio.dataset.loaiPhong;
                    const vStart = radio.dataset.start || '';
                    const vEnd = radio.dataset.end || '';
                    const minConditionStr = radio.dataset.minCondition || '';
                    const container = radio.closest('.voucher-container');

                    // 1. Check Date
                    let dateOk = true;
                    if (checkinDate && vStart && vEnd) {
                        const checkin = new Date(checkinDate + 'T00:00:00Z');
                        const start = new Date(vStart + 'T00:00:00Z');
                        const end = new Date(vEnd + 'T00:00:00Z');
                        if (checkin < start || checkin > end) dateOk = false;
                    }

                    // 2. Check Room Type
                    const roomOk = (!voucherRoomType || voucherRoomType === 'null' || voucherRoomType === '') ||
                        (selectedRoomTypes.length > 0 && selectedRoomTypes.includes(voucherRoomType));

                    // 3. Check Min Condition
                    let minCondition = 0;
                    const matches = minConditionStr.replace(/[.,]/g, '').match(/(\d+)/);
                    if (matches) minCondition = parseFloat(matches[1]);
                    const conditionOk = currentRoomSubtotal >= minCondition;

                    const isAvailable = dateOk && roomOk && conditionOk;
                    radio.disabled = !isAvailable;

                    // Support for UI feedback
                    const card = radio.closest('label') || radio.nextElementSibling;
                    if (card) {
                        if (!isAvailable) {
                            card.classList.add('opacity-50', 'bg-gray-50');
                            card.classList.remove('bg-white');
                        } else {
                            card.classList.remove('opacity-50', 'bg-gray-50');
                            card.classList.add('bg-white');
                        }
                    }

                    // If currently checked but no longer available, uncheck it
                    if (radio.checked && !isAvailable) {
                        radio.checked = false;
                        // Trigger recalculation
                        const voucherIdInput = document.getElementById('voucher_id_input');
                        const voucherMirror = document.getElementById('voucher_input');
                        if (voucherIdInput) voucherIdInput.value = '';
                        if (voucherMirror) voucherMirror.value = '';
                        if (typeof updateTotalPrice === 'function') updateTotalPrice();
                    }
                });
            }

            function isHolidayJS(date) {
                const d = new Date(date.getTime());
                const year = d.getFullYear();
                const holidays = [
                    new Date(year, 0, 1),
                    new Date(year, 3, 30),
                    new Date(year, 4, 1),
                    new Date(year, 8, 2),
                ];
                return holidays.some(h => h.getDate() === d.getDate() && h.getMonth() === d.getMonth());
            }

            function getMultiplierForDateJS(date) {
                if (isHolidayJS(date)) return 1.25;
                const day = date.getDay();
                if (day === 0 || day === 6) return 1.15;
                return 1.0;
            }

            function toggleService(checkbox, serviceId) {
                const quantityContainer = document.getElementById('service_quantity_container_' + serviceId);
                const hiddenInput = document.getElementById('service_quantity_hidden_' + serviceId);
                const quantityInput = document.getElementById('service_quantity_' + serviceId);

                if (checkbox.checked) {
                    quantityContainer.classList.remove('hidden');
                    hiddenInput.value = quantityInput.value || 1;
                } else {
                    quantityContainer.classList.add('hidden');
                    hiddenInput.value = 0;
                    quantityInput.value = 1;
                }

                updateTotalPrice();
            }

            function increaseServiceQuantity(serviceId) {
                const input = document.getElementById('service_quantity_' + serviceId);
                input.value = parseInt(input.value) + 1;
                updateServiceQuantityHidden(serviceId);
                updateTotalPrice();
            }

            function decreaseServiceQuantity(serviceId) {
                const input = document.getElementById('service_quantity_' + serviceId);
                const newValue = Math.max(1, parseInt(input.value) - 1);
                input.value = newValue;
                updateServiceQuantityHidden(serviceId);
                updateTotalPrice();
            }

            function updateServiceQuantityHidden(serviceId) {
                const displayInput = document.getElementById('service_quantity_' + serviceId);
                const hiddenInput = document.getElementById('service_quantity_hidden_' + serviceId);
                if (hiddenInput && displayInput) hiddenInput.value = displayInput.value;
                updateTotalPrice();
            }

            // === GIỮ NGUYÊN HÀM updateTotalPrice() CỦA BẠN ===
            var updateTotalPrice = function() {
                console.log('=== updateTotalPrice NEW VERSION CALLED ===');
                const ngayNhan = document.getElementById('ngay_nhan').value;
                const ngayTra = document.getElementById('ngay_tra').value;

                if (!ngayNhan || !ngayTra) {
                    document.getElementById('total_price').textContent = formatCurrency(0);
                    document.getElementById('tong_tien_input').value = 0;
                    return;
                }

                const startDate = new Date(ngayNhan);
                const endDate = new Date(ngayTra);

                if (isNaN(startDate.getTime()) || isNaN(endDate.getTime()) || endDate <= startDate) {
                    document.getElementById('total_price').textContent = formatCurrency(0);
                    document.getElementById('tong_tien_input').value = 0;
                    return;
                }

                const maxAdultsPerRoom = 2;

                let roomTotalByType = {};
                let roomTotal = 0;
                let totalExtraFee = 0;
                let totalChildFee = 0;
                let totalInfantFee = 0;
                let totalAdults = 0;
                let totalChildren = 0;
                let totalInfants = 0;
                let weekdayNights = 0;
                let weekendNights = 0;
                let holidayNights = 0;

                const checkedRooms = document.querySelectorAll('.room-type-checkbox:checked');
                if (checkedRooms.length === 0) {
                    document.getElementById('total_price').textContent = formatCurrency(0);
                    document.getElementById('tong_tien_input').value = 0;
                    document.getElementById('guest_summary').classList.add('hidden');
                    document.getElementById('surcharge_summary').classList.add('hidden');
                    return;
                }

                checkedRooms.forEach(checkbox => {
                    const roomTypeId = checkbox.value;
                    const quantityInput = document.getElementById('quantity_' + roomTypeId);
                    const quantity = parseInt(quantityInput?.value || 1);
                    const basePrice = parseFloat(checkbox.dataset.price || 0);

                    if (!basePrice || basePrice <= 0 || !quantity || quantity <= 0) return;

                    const adultsHidden = document.getElementById('adults_hidden_' + roomTypeId);
                    const childrenHidden = document.getElementById('children_hidden_' + roomTypeId);
                    const infantsHidden = document.getElementById('infants_hidden_' + roomTypeId);

                    const adults = parseInt(adultsHidden?.value || maxAdultsPerRoom * quantity);
                    const children = parseInt(childrenHidden?.value || 0);
                    const infants = parseInt(infantsHidden?.value || 0);

                    totalAdults += adults;
                    totalChildren += children;
                    totalInfants += infants;

                    let current = new Date(startDate.getTime());
                    let totalForType = 0;

                    while (current < endDate) {
                        const multiplier = getMultiplierForDateJS(current);
                        const priceForDay = basePrice * multiplier;
                        totalForType += priceForDay * quantity;

                        if (isHolidayJS(current)) holidayNights += 1;
                        else {
                            const day = current.getDay();
                            if (day === 0 || day === 6) weekendNights += 1;
                            else weekdayNights += 1;
                        }

                        current.setDate(current.getDate() + 1);
                    }

                    if (totalForType > 0) {
                        roomTotalByType[roomTypeId] = totalForType;
                        roomTotal += totalForType;
                    }
                });

                const guestSummary = document.getElementById('guest_summary');
                if (totalAdults > 0 || totalChildren > 0 || totalInfants > 0) {
                    guestSummary.classList.remove('hidden');
                    if (totalAdults > 0) {
                        document.getElementById('adults_count').textContent = totalAdults;
                        document.getElementById('total_adults_display').classList.remove('hidden');
                    } else document.getElementById('total_adults_display').classList.add('hidden');

                    if (totalChildren > 0) {
                        document.getElementById('children_count').textContent = totalChildren;
                        document.getElementById('total_children_display').classList.remove('hidden');
                    } else document.getElementById('total_children_display').classList.add('hidden');

                    if (totalInfants > 0) {
                        document.getElementById('infants_count').textContent = totalInfants;
                        document.getElementById('total_infants_display').classList.remove('hidden');
                    } else document.getElementById('total_infants_display').classList.add('hidden');
                } else guestSummary.classList.add('hidden');

                const totalSoLuong = Array.from(document.querySelectorAll('.room-type-checkbox:checked')).reduce((acc, cb) => {
                    const q = parseInt(document.getElementById('quantity_' + cb.value)?.value || 1);
                    return acc + q;
                }, 0);

                const unitsTotal = (totalAdults * 2) + totalChildren + totalInfants;
                const baseUnits = totalSoLuong * 4;

                let extraAdultsCount = 0;
                let extraChildrenCount = 0;
                let extraInfantsCount = 0;

                totalExtraFee = 0;
                totalChildFee = 0;
                totalInfantFee = 0;

                if (unitsTotal > baseUnits && totalSoLuong > 0) {
                    const adultsUnits = totalAdults * 2;
                    const adultsExtraUnits = Math.max(0, adultsUnits - baseUnits);
                    let unitsRemainingBase = Math.max(0, baseUnits - adultsUnits);

                    const childrenAssignedUnits = Math.min(totalChildren, unitsRemainingBase);
                    const childrenExtra = Math.max(0, totalChildren - childrenAssignedUnits);
                    unitsRemainingBase -= childrenAssignedUnits;

                    const infantsAssignedUnits = Math.min(totalInfants, unitsRemainingBase);
                    const infantsExtra = Math.max(0, totalInfants - infantsAssignedUnits);

                    extraAdultsCount = Math.floor(adultsExtraUnits / 2);
                    extraChildrenCount = childrenExtra;
                    extraInfantsCount = infantsExtra;

                    const oneDayMs = 24 * 60 * 60 * 1000;
                    const nights = Math.max(1, Math.round((endDate - startDate) / oneDayMs));

                    totalExtraFee = extraAdultsCount * 300000 * nights;
                    totalChildFee = extraChildrenCount * 150000 * nights;
                    totalInfantFee = 0;
                }

                const surchargeSummary = document.getElementById('surcharge_summary');
                if (totalExtraFee > 0 || totalChildFee > 0 || totalInfantFee > 0) {
                    surchargeSummary.classList.remove('hidden');
                    if (totalExtraFee > 0) {
                        document.getElementById('extra_adult_fee_amount').textContent = formatCurrency(totalExtraFee);
                        document.getElementById('extra_adult_fee').classList.remove('hidden');
                    } else document.getElementById('extra_adult_fee').classList.add('hidden');

                    if (totalChildFee > 0) {
                        document.getElementById('child_fee_amount').textContent = formatCurrency(totalChildFee);
                        document.getElementById('child_fee').classList.remove('hidden');
                    } else document.getElementById('child_fee').classList.add('hidden');

                    if (totalInfantFee > 0) {
                        document.getElementById('infant_fee_amount').textContent = formatCurrency(totalInfantFee);
                        document.getElementById('infant_fee').classList.remove('hidden');
                    } else document.getElementById('infant_fee').classList.add('hidden');
                } else surchargeSummary.classList.add('hidden');

                const roomTotalWithSurcharges = roomTotal + totalExtraFee + totalChildFee + totalInfantFee;

                const selectedVoucher = document.querySelector('.voucher-radio:checked');
                let discount = 0;
                let discountedRoomTotal = roomTotal;

                if (selectedVoucher) {
                    const discountValue = parseFloat(selectedVoucher.dataset.value || 0);
                    const voucherLoaiPhongId = selectedVoucher.dataset.loaiPhong;

                    let applicableTotal = 0;
                    if (!voucherLoaiPhongId || voucherLoaiPhongId === 'null') applicableTotal = roomTotal;
                    else applicableTotal = roomTotalByType[voucherLoaiPhongId] || 0;

                    if (discountValue <= 100) discount = (applicableTotal * discountValue) / 100;
                    else discount = Math.min(discountValue, applicableTotal);

                    discountedRoomTotal = roomTotal - discount;
                }

                let roomNetTotal = discountedRoomTotal + totalExtraFee + totalChildFee + totalInfantFee;

                let totalServicePrice = 0;

                function getTotalBookedRooms() {
                    let total = 0;
                    document.querySelectorAll('.room-type-checkbox:checked').forEach(cb => {
                        const id = cb.value;
                        const hidden = document.getElementById('quantity_hidden_' + id);
                        total += parseInt(hidden?.value || 0) || 0;
                    });
                    if (total === 0) total = document.querySelectorAll('.available-room-checkbox:checked').length || 0;
                    return total;
                }

                document.querySelectorAll('.service-checkbox:checked').forEach(checkbox => {
                    const serviceId = checkbox.value;
                    const price = parseFloat(checkbox.dataset.price || 0) || 0;
                    const card = document.querySelector('[data-service-id="' + serviceId + '"]');
                    if (!card) return;

                    const mode = card.querySelector('input[name="service_room_mode_' + serviceId + '"]:checked')?.value || 'global';
                    const rows = Array.from(card.querySelectorAll('#service_dates_' + serviceId + ' .service-date-row'));

                    rows.forEach(r => {
                        const qty = parseInt(r.querySelector('input[type=number]')?.value || 1) || 0;
                        if (qty <= 0) return;

                        if (mode === 'global') {
                            const roomsCount = getTotalBookedRooms();
                            totalServicePrice += price * qty * roomsCount;
                        } else {
                            const entryRooms = r.querySelectorAll('.entry-room-checkbox:checked').length || 0;
                            totalServicePrice += price * qty * entryRooms;
                        }
                    });
                });

                const finalTotal = roomNetTotal + totalServicePrice;

                const pricingInfoDiv = document.getElementById('pricing_multiplier_info');
                if (pricingInfoDiv && (weekdayNights + weekendNights + holidayNights) > 0) {
                    pricingInfoDiv.textContent = `Giá đã áp dụng: ${weekdayNights} đêm thường, ${weekendNights} đêm cuối tuần (+15%), ${holidayNights} đêm lễ (+25%).`;
                    pricingInfoDiv.classList.remove('hidden');
                } else if (pricingInfoDiv) pricingInfoDiv.classList.add('hidden');

                const totalPriceElement = document.getElementById('total_price');
                const tongTienInput = document.getElementById('tong_tien_input');
                const originalPriceElement = document.getElementById('original_price');
                const discountAmountElement = document.getElementById('discount_amount');
                const discountInfoElement = document.getElementById('discount_info');

                totalPriceElement.textContent = formatCurrency(finalTotal);
                tongTienInput.value = finalTotal;

                if (selectedVoucher) {
                    originalPriceElement.textContent = formatCurrency(roomTotalWithSurcharges);
                    discountAmountElement.textContent = '-' + formatCurrency(discount);
                    discountInfoElement.classList.remove('hidden');
                } else {
                    discountInfoElement.classList.add('hidden');
                }
            };

            // Tom Select init + renderSelectedServices (giữ nguyên code của bạn)
            function loadTomSelectAndInit(cb) {
                if (window.TomSelect) return cb();
                var s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js';
                s.onload = cb;
                document.head.appendChild(s);
            }

            document.addEventListener('DOMContentLoaded', function() {
                loadTomSelectAndInit(function() {
                    try {
                        const selectEl = document.getElementById('services_select');
                        if (!selectEl) return;

                        const ts = new TomSelect(selectEl, {
                            plugins: ['remove_button'],
                            persist: false,
                            create: false,
                            onChange: function(values) {
                                renderSelectedServices(values || []);
                            }
                        });

                        function getRangeDates() {
                            const start = document.getElementById('ngay_nhan')?.value;
                            const end = document.getElementById('ngay_tra')?.value;
                            if (!start || !end) return [];
                            const a = [];
                            const s = new Date(start);
                            const e = new Date(end);
                            for (let d = new Date(s); d <= e; d.setDate(d.getDate() + 1)) a.push(new Date(d).toISOString().split('T')[0]);
                            return a;
                        }

                        function normalizeServiceDates() {
                            const range = getRangeDates();
                            if (!range.length) return;
                            document.querySelectorAll('[data-service-id]').forEach(card => {
                                const id = card.getAttribute('data-service-id');
                                const rows = Array.from(card.querySelectorAll('.service-date-row'));
                                if (rows.length > range.length) {
                                    for (let i = rows.length - 1; i >= range.length; i--) rows[i].remove();
                                }
                                const nowRows = Array.from(card.querySelectorAll('.service-date-row'));
                                nowRows.forEach((r, idx) => {
                                    const d = r.querySelector('input[type=date]');
                                    if (d) d.value = range[Math.min(idx, range.length-1)];
                                });
                                try { const ev = new Event('service_range_changed'); document.getElementById('service_dates_'+id)?.dispatchEvent(ev); } catch(e){}
                            });
                        }

                        function renderSelectedServices(values) {
                            const container = document.getElementById('selected_services_list');

                            const prevCounts = {};
                            Array.from(container.querySelectorAll('[data-service-id]')).forEach(card => {
                                const id = card.getAttribute('data-service-id');
                                prevCounts[id] = card.querySelectorAll('.service-date-row')?.length || 0;
                            });

                            container.innerHTML = '';
                            const range = getRangeDates();

                            (values || []).forEach(val => {
                                const option = selectEl.querySelector('option[value="' + val + '"]');
                                if (!option) return;
                                const serviceId = val;
                                const serviceName = option.textContent || option.innerText;
                                const servicePrice = parseFloat(option.dataset.price || 0);
                                const unit = option.dataset.unit || 'cái';

                                const card = document.createElement('div');
                                card.className = 'service-card-custom';
                                card.setAttribute('data-service-id', serviceId);

                                const header = document.createElement('div');
                                header.className = 'service-card-header';
                                const title = document.createElement('div');
                                title.innerHTML = `<div class="service-title">${serviceName.split(' - ')[0]}</div>`;
                                const price = document.createElement('div');
                                price.className = 'service-price';
                                price.innerHTML = `${new Intl.NumberFormat('vi-VN').format(servicePrice)}/${unit}`;
                                header.appendChild(title);
                                header.appendChild(price);
                                card.appendChild(header);

                                const roomSection = document.createElement('div');
                                roomSection.className = 'bg-blue-50 p-3 rounded-lg mt-3 border border-blue-200';
                                roomSection.id = 'room_selection_' + serviceId;

                                const roomToggle = document.createElement('div');
                                roomToggle.className = 'flex gap-2 mb-2';
                                const globalRadio = document.createElement('input');
                                globalRadio.type = 'radio';
                                globalRadio.name = 'service_room_mode_' + serviceId;
                                globalRadio.value = 'global';
                                globalRadio.checked = true;
                                globalRadio.id = 'global_' + serviceId;
                                const globalLabel = document.createElement('label');
                                globalLabel.htmlFor = 'global_' + serviceId;
                                globalLabel.className = 'text-sm flex items-center gap-2 cursor-pointer';
                                globalLabel.innerHTML = '<span>Áp dụng tất cả phòng</span>';

                                const specificRadio = document.createElement('input');
                                specificRadio.type = 'radio';
                                specificRadio.name = 'service_room_mode_' + serviceId;
                                specificRadio.value = 'specific';
                                specificRadio.id = 'specific_' + serviceId;

                                const specificLabel = document.createElement('label');
                                specificLabel.htmlFor = 'specific_' + serviceId;
                                specificLabel.className = 'text-sm flex items-center gap-2 cursor-pointer';
                                specificLabel.innerHTML = '<span>Chọn phòng riêng</span>';

                                roomToggle.appendChild(globalRadio);
                                roomToggle.appendChild(globalLabel);
                                roomToggle.appendChild(specificRadio);
                                roomToggle.appendChild(specificLabel);
                                roomSection.appendChild(roomToggle);

                                globalRadio.onchange = () => {
                                    card.querySelectorAll('.entry-room-container').forEach(c => {
                                        c.classList.add('hidden');
                                        Array.from(c.querySelectorAll('input[type=checkbox]')).forEach(cb => cb.checked = false);
                                    });
                                    try { updateServiceRoomLists(); } catch(e){}
                                    syncHiddenEntries(serviceId);
                                    try { updateTotalPrice(); } catch(e){}
                                };

                                specificRadio.onchange = () => {
                                    card.querySelectorAll('.entry-room-container').forEach(c => c.classList.remove('hidden'));
                                    try { updateServiceRoomLists(); } catch(e){}
                                    syncHiddenEntries(serviceId);
                                    try { updateTotalPrice(); } catch(e){}
                                };

                                roomSection.appendChild(document.createElement('div'));
                                card.appendChild(roomSection);

                                const rows = document.createElement('div');
                                rows.id = 'service_dates_' + serviceId;

                                function buildDateRow(dateVal) {
                                    const r = document.createElement('div'); r.className = 'service-date-row';
                                    const d = document.createElement('input'); d.type = 'date'; d.className = 'border rounded p-1'; d.value = dateVal || '';
                                    const rg = getRangeDates(); if (rg.length) { d.min = rg[0]; d.max = rg[rg.length-1]; }
                                    d.addEventListener('focus', function(){ this.dataset.prev = this.value || ''; });
                                    d.addEventListener('change', function(){
                                        const val = this.value || '';
                                        if (!val) { syncHiddenEntries(serviceId); return; }
                                        const others = Array.from(document.querySelectorAll('#service_dates_'+serviceId+' input[type=date]'))
                                            .filter(i=>i !== this)
                                            .map(i=>i.value);
                                        if (others.includes(val)) {
                                            const prev = this.dataset.prev || '';
                                            this.value = prev;
                                            alert('Ngày này đã được chọn cho dịch vụ này. Vui lòng chọn ngày khác.');
                                            return;
                                        }
                                        syncHiddenEntries(serviceId);
                                    });
                                    const q = document.createElement('input'); q.type = 'number'; q.min = 1; q.value = 1; q.className = 'w-24 border rounded p-1 text-center'; q.onchange = () => syncHiddenEntries(serviceId);
                                    const rem = document.createElement('button'); rem.type='button'; rem.className='service-remove-btn ml-2'; rem.textContent='Xóa'; rem.onclick = ()=>{ r.remove(); syncHiddenEntries(serviceId); };
                                    r.appendChild(d); r.appendChild(q); r.appendChild(rem);
                                    const entryRoomPlaceholder = document.createElement('div'); entryRoomPlaceholder.className = 'entry-room-container mt-2 pl-2 border-l';
                                    r.appendChild(entryRoomPlaceholder);
                                    return r;
                                }

                                const want = Math.max(1, prevCounts[serviceId] || 1);
                                const maxRows = Math.min(want, range.length || 1);
                                for (let i=0;i<maxRows;i++) {
                                    const dateVal = (range.length && range[i]) ? range[i] : '';
                                    rows.appendChild(buildDateRow(dateVal));
                                }

                                const addBtn = document.createElement('button'); addBtn.type='button'; addBtn.className='service-add-day mt-2'; addBtn.textContent='Thêm ngày';
                                addBtn.onclick = function(){
                                    const used = Array.from(rows.querySelectorAll('input[type="date"]')).map(i=>i.value);
                                    const avail = getRangeDates().find(d=>!used.includes(d));
                                    if (avail) {
                                        rows.appendChild(buildDateRow(avail));
                                        try { updateServiceRoomLists(); } catch(e){}
                                        syncHiddenEntries(serviceId);
                                    }
                                };

                                card.appendChild(rows);
                                card.appendChild(addBtn);

                                const hcb = document.createElement('input'); hcb.type='checkbox'; hcb.className='service-checkbox'; hcb.name='services[]'; hcb.value=serviceId; hcb.setAttribute('data-price', servicePrice); hcb.style.display='none'; hcb.checked=true;
                                const hsum = document.createElement('input'); hsum.type='hidden'; hsum.name='services_data['+serviceId+'][so_luong]'; hsum.id='service_quantity_hidden_'+serviceId; hsum.value='1';
                                const hdv = document.createElement('input'); hdv.type='hidden'; hdv.name='services_data['+serviceId+'][dich_vu_id]'; hdv.value=serviceId;

                                container.appendChild(card);
                                container.appendChild(hcb);
                                container.appendChild(hsum);
                                container.appendChild(hdv);

                                function syncHiddenEntries(id){
                                    // If no rows remain for this service, remove the service selection entirely
                                    const rowsNow = Array.from(document.querySelectorAll('#service_dates_'+id+' .service-date-row'));
                                    if (rowsNow.length === 0) {
                                        try { ts.removeItem(id); } catch(e){
                                            // fallback: remove DOM nodes
                                            const card = document.querySelector('[data-service-id="'+id+'"]'); if(card) card.remove();
                                            Array.from(document.querySelectorAll('input[name="services[]"][value="'+id+'"]')).forEach(n=>n.remove());
                                        }
                                        updateTotalPrice();
                                        return;
                                    }

                                    // remove existing entry-hidden inputs for this id
                                    Array.from(document.querySelectorAll('input.entry-hidden[data-service="'+id+'"]')).forEach(n=>n.remove());
                                    
                                    // remove existing phong_ids hidden inputs for this service
                                    Array.from(document.querySelectorAll('input[name="services_data['+id+'][entries][]][phong_ids][]"]')).forEach(n => {
                                        n.remove();
                                    });

                                    // Determine current mode
                                    const card = document.querySelector('[data-service-id="'+id+'"]');
                                    const mode = card?.querySelector('input[name="service_room_mode_' + id + '"]:checked')?.value || 'global';

                                    let total=0;
                                    rowsNow.forEach((r, idx)=>{
                                        const dateVal = r.querySelector('input[type=date]')?.value || '';
                                        const qty = parseInt(r.querySelector('input[type=number]')?.value || 1);
                                        
                                        // Collect per-entry selected rooms (from entry-room-checkboxes inside this row)
                                        const entryRoomChecks = Array.from(r.querySelectorAll('.entry-room-checkbox:checked'));
                                        
                                        // If in specific mode and no rooms checked, skip this entry entirely
                                        if (mode === 'specific' && entryRoomChecks.length === 0) {
                                            console.log('syncHiddenEntries service', id, 'entry', idx, 'specific mode but NO rooms checked - SKIP');
                                            return;
                                        }
                                        
                                        total += qty;
                                        
                                        // Create hidden inputs for this entry
                                        const hNgay = document.createElement('input'); hNgay.type='hidden'; hNgay.name='services_data['+id+'][entries]['+idx+'][ngay]'; hNgay.value=dateVal; hNgay.className='entry-hidden'; hNgay.setAttribute('data-service', id);
                                        const hSo = document.createElement('input'); hSo.type='hidden'; hSo.name='services_data['+id+'][entries]['+idx+'][so_luong]'; hSo.value=qty; hSo.className='entry-hidden'; hSo.setAttribute('data-service', id);
                                        container.appendChild(hNgay); container.appendChild(hSo);

                                        // Only add phong_ids if in specific mode
                                        if (mode === 'specific') {
                                            entryRoomChecks.forEach((erc) => {
                                                const hRoom = document.createElement('input');
                                                hRoom.type = 'hidden';
                                                hRoom.name = 'services_data['+id+'][entries]['+idx+'][phong_ids][]';
                                                hRoom.value = erc.getAttribute('data-room-id') || erc.value;
                                                hRoom.className = 'entry-hidden';
                                                hRoom.setAttribute('data-service', id);
                                                container.appendChild(hRoom);
                                            });
                                            console.log('syncHiddenEntries service', id, 'entry', idx, 'specific mode, checked rooms:', entryRoomChecks.length, entryRoomChecks.map(e => e.value));
                                        } else {
                                            console.log('syncHiddenEntries service', id, 'entry', idx, 'global mode - apply to all');
                                        }
                                    });
                                    const sumEl = document.getElementById('service_quantity_hidden_'+id); if(sumEl) sumEl.value = total;
                                    updateTotalPrice(); updateAddBtnState(id);
                                }

                                function updateAddBtnState(id){ const rowsNow = document.querySelectorAll('#service_dates_'+id+' .service-date-row'); const used = Array.from(rowsNow).map(r=>r.querySelector('input[type=date]').value); const avail = getRangeDates().find(d=>!used.includes(d)); addBtn.disabled = !avail; addBtn.style.opacity = addBtn.disabled? '0.6':'1'; }

                                // Export to window so it can be called from other closures
                                window.syncHiddenEntries = syncHiddenEntries;

                                // call sync initially
                                syncHiddenEntries(serviceId);
                                updateAddBtnState(serviceId);
                            });

                            // Ensure totals recalc
                            updateTotalPrice();
                        }

                        // allow global removal
                        window.removeServiceFromSelect = function(id) {
                            ts.removeItem(id);
                        };

                        

                        // When increase/decrease functions run, they already call updateServiceQuantityHidden
                        // but we must ensure hidden inputs exist - renderSelectedServices created them.

                        // Render current initial selection if any
                        renderSelectedServices(ts.getValue() || []);
                        // Ensure service cards know about currently selected rooms
                        try { updateServiceRoomLists(); } catch(e){}

                        // Re-render / normalize service date-rows when global date range changes
                        const ngayNhanEl = document.getElementById('ngay_nhan');
                        const ngayTraEl = document.getElementById('ngay_tra');
                        if (ngayNhanEl && ngayTraEl) {
                            ngayNhanEl.addEventListener('change', function(){ normalizeServiceDates(); renderSelectedServices(ts.getValue() || []); });
                            ngayTraEl.addEventListener('change', function(){ normalizeServiceDates(); renderSelectedServices(ts.getValue() || []); });
                        }

                        // Voucher label deselection handled centrally in setupVoucherEventSystem()

                    } catch (e) {
                        console.error('TomSelect init error', e);
                    }
                });
            });
            // Initial update
            updateVoucherAvailability();
        </script>
    @endpush
@endsection
