@extends('layouts.admin')

@section('title', 'Sửa đặt phòng')

@section('admin_content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
                    <h1 class="text-2xl font-bold text-gray-900">Sửa đặt phòng #{{ $booking->id }}</h1>
                    <p class="mt-1 text-sm text-gray-600">Chỉnh sửa thông tin đặt phòng của khách</p>
                </div>
                <div class="p-6">
                    @php
                        $roomTypes = $booking->getRoomTypes();
                    @endphp

                    <form id="bookingForm" method="POST" action="{{ route('admin.dat_phong.update', $booking->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Display success message -->
                        @if (session()->has('success'))
                            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                                <div class="text-sm font-semibold text-green-900">✓ {{ session('success') }}</div>
                            </div>
                        @endif

                        <!-- Display validation errors from session -->
                        @if (session()->has('errors') && session('errors')->any())
                            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                                <h4 class="text-sm font-semibold text-red-900 mb-2">Lỗi xác thực:</h4>
                                <ul class="text-sm text-red-700 space-y-1">
                                    @foreach (session('errors')->all() as $error)
                                        <li>• {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="space-y-8">

                            <!-- Quản lý loại phòng - Redesigned -->
                            <section>
                                <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        <i class="fas fa-bed mr-2 text-blue-600"></i>Loại phòng được đặt
                                    </h3>
                                    <button type="button" id="btnAddRoom" onclick="addRoom()"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
                                        <i class="fas fa-plus mr-2"></i> Thêm loại phòng
                                    </button>
                                </div>
                                
                                <div id="roomTypesContainer" class="space-y-4">
                                    @foreach ($roomTypes as $rt)
                                        @php
                                            $rtLoaiPhongId = $rt['loai_phong_id'] ?? null;
                                            $rtSoLuong = $rt['so_luong'] ?? 1;
                                            $rtLoaiPhong = $loaiPhongs->firstWhere('id', $rtLoaiPhongId);
                                            $rtTen = $rtLoaiPhong ? $rtLoaiPhong->ten_loai : ($rt['ten_loai'] ?? 'Loại phòng');
                                            $rtGia = $rtLoaiPhong ? ($rtLoaiPhong->gia_khuyen_mai ?? $rtLoaiPhong->gia_co_ban) : ($rt['gia_rieng'] ?? 0);
                                            $rtPhongIds = $rt['phong_ids'] ?? [];
                                        @endphp
                                        <div class="room-item bg-gradient-to-r from-blue-50 to-white border border-blue-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow"
                                            data-room-index="{{ $loop->index }}">
                                            
                                            <!-- Header với tên và nút xóa -->
                                            <div class="flex justify-between items-center mb-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                                        <i class="fas fa-door-open text-white"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-bold text-gray-900 text-lg room-title">{{ $rtTen }}</h4>
                                                        <p class="text-sm text-blue-600 font-medium quantity-text">{{ $rtSoLuong }} phòng</p>
                                                    </div>
                                                </div>
                                                <button type="button" onclick="removeRoom({{ $loop->index }})"
                                                    class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition"
                                                    title="Xóa loại phòng này">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                            
                                            <!-- Form controls -->
                                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                                                <!-- Chọn loại phòng -->
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Loại phòng</label>
                                                    <select name="room_types[{{ $loop->index }}][loai_phong_id]"
                                                        class="room-type-select w-full px-3 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white"
                                                        onchange="handleRoomTypeChange({{ $loop->index }}, this.value)"
                                                        required>
                                                        <option value="">-- Chọn loại phòng --</option>
                                                        @foreach ($loaiPhongs as $lp)
                                                            <option value="{{ $lp->id }}"
                                                                data-price="{{ $lp->gia_khuyen_mai ?? $lp->gia_co_ban }}"
                                                                data-suc-chua="{{ $lp->suc_chua ?? 2 }}"
                                                                data-suc-chua-tre-em="{{ $lp->suc_chua_tre_em ?? 1 }}"
                                                                data-suc-chua-em-be="{{ $lp->suc_chua_em_be ?? 1 }}"
                                                                {{ $rtLoaiPhongId == $lp->id ? 'selected' : '' }}>
                                                                {{ $lp->ten_loai }} - {{ number_format($lp->gia_khuyen_mai ?? $lp->gia_co_ban, 0, ',', '.') }} VNĐ/đêm ({{ $lp->suc_chua ?? 2 }} người)
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <p id="availability_text_{{ $loop->index }}" class="mt-1 text-xs text-green-600 font-medium"></p>
                                                    @php
                                                        $sucChua = $rtLoaiPhong ? ($rtLoaiPhong->suc_chua ?? 2) : 2;
                                                        $sucChuaTreEm = $rtLoaiPhong ? ($rtLoaiPhong->suc_chua_tre_em ?? 1) : 1;
                                                        $sucChuaEmBe = $rtLoaiPhong ? ($rtLoaiPhong->suc_chua_em_be ?? 1) : 1;
                                                    @endphp
                                                    <p id="capacity_text_{{ $loop->index }}" class="mt-1 text-xs text-gray-500">
                                                        <i class="fas fa-users mr-1"></i> Tối đa: {{ $sucChua }} người lớn, {{ $sucChuaTreEm }} trẻ em, {{ $sucChuaEmBe }} em bé
                                                    </p>
                                                </div>
                                                
                                                <!-- Số lượng -->
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-600 mb-2 uppercase tracking-wide text-[11px]">Cấu hình số lượng</label>
                                                    <div class="flex items-center gap-6">
                                                        <div class="relative flex items-center bg-blue-50/50 rounded-2xl p-1.5 border border-blue-100 shadow-inner w-fit group">
                                                            <button type="button" onclick="decreaseQuantity({{ $loop->index }})"
                                                                class="w-10 h-10 rounded-xl flex items-center justify-center bg-white text-blue-600 shadow-sm hover:bg-red-500 hover:text-white transition-all transform active:scale-95 border border-blue-50">
                                                                <i class="fas fa-minus text-xs"></i>
                                                            </button>
                                                            
                                                            <div class="px-5 text-center flex flex-col items-center">
                                                                <input type="number" name="room_types[{{ $loop->index }}][so_luong]"
                                                                    class="room-quantity-input w-12 bg-transparent border-none text-center font-black text-xl text-blue-900 focus:ring-0 p-0 leading-none h-6"
                                                                    value="{{ $rtSoLuong }}" min="1" max="10"
                                                                    data-room-index="{{ $loop->index }}"
                                                                    onchange="updateRoomQuantity({{ $loop->index }})" required>
                                                                <p class="text-[9px] font-bold text-blue-400 uppercase tracking-tighter mt-1">Phòng</p>
                                                            </div>

                                                            <button type="button" onclick="increaseQuantity({{ $loop->index }})"
                                                                class="w-10 h-10 rounded-xl flex items-center justify-center bg-white text-blue-600 shadow-sm hover:bg-green-500 hover:text-white transition-all transform active:scale-95 border border-blue-50">
                                                                <i class="fas fa-plus text-xs"></i>
                                                            </button>
                                                        </div>

                                                        <div class="flex flex-col border-l border-gray-200 pl-6">
                                                            <span class="text-[10px] uppercase tracking-wider text-gray-400 font-extrabold">Giá Loại Phòng</span>
                                                            <div class="flex items-baseline gap-1">
                                                                <span id="room_price_{{ $loop->index }}" class="font-black text-blue-700 text-xl tracking-tight">
                                                                    {{ number_format($rtGia, 0, ',', '.') }}
                                                                </span>
                                                                <span class="text-xs font-bold text-blue-400">VNĐ</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Guest Selection for this room group -->
                                            <div class="mb-6 p-4 bg-white/50 border border-blue-100 rounded-2xl shadow-sm">
                                                <label class="block text-sm font-bold text-blue-900 mb-3 uppercase tracking-wider flex items-center gap-2">
                                                    <i class="fas fa-users text-blue-500"></i>
                                                    Số khách cho loại phòng này
                                                </label>
                                                <div class="grid grid-cols-3 gap-4">
                                                    <!-- Adults -->
                                                    <div class="flex flex-col items-center p-3 bg-blue-50/50 rounded-xl border border-blue-100 transition-all hover:bg-blue-50">
                                                        <div class="flex items-center gap-2 mb-2">
                                                            <i class="fas fa-user-tie text-blue-500 text-xs"></i>
                                                            <span class="text-[10px] font-bold text-blue-700 uppercase">Người lớn</span>
                                                        </div>
                                                        <div class="flex items-center gap-3">
                                                            <button type="button" onclick="changeRoomGuestCount('adults', {{ $loop->index }}, -1)" class="w-7 h-7 flex items-center justify-center rounded-lg bg-white border border-blue-200 text-blue-600 hover:bg-blue-600 hover:text-white transition-all active:scale-90">
                                                                <i class="fas fa-minus text-[10px]"></i>
                                                            </button>
                                                            <input type="number" 
                                                                class="room-adults-input w-8 bg-transparent border-none text-center font-bold text-lg text-blue-900 focus:ring-0 p-0" 
                                                                value="{{ $rt['so_nguoi'] ?? 2 }}" min="0" readonly
                                                                onchange="syncGlobalGuestCounts()">
                                                            <button type="button" onclick="changeRoomGuestCount('adults', {{ $loop->index }}, 1)" class="w-7 h-7 flex items-center justify-center rounded-lg bg-white border border-blue-200 text-blue-600 hover:bg-blue-600 hover:text-white transition-all active:scale-90">
                                                                <i class="fas fa-plus text-[10px]"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <!-- Children -->
                                                    <div class="flex flex-col items-center p-3 bg-emerald-50/50 rounded-xl border border-emerald-100 transition-all hover:bg-emerald-50">
                                                        <div class="flex items-center gap-2 mb-2">
                                                            <i class="fas fa-child text-emerald-500 text-xs"></i>
                                                            <span class="text-[10px] font-bold text-emerald-700 uppercase">Trẻ em</span>
                                                        </div>
                                                        <div class="flex items-center gap-3">
                                                            <button type="button" onclick="changeRoomGuestCount('children', {{ $loop->index }}, -1)" class="w-7 h-7 flex items-center justify-center rounded-lg bg-white border border-emerald-200 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all active:scale-90">
                                                                <i class="fas fa-minus text-[10px]"></i>
                                                            </button>
                                                            <input type="number" 
                                                                class="room-children-input w-8 bg-transparent border-none text-center font-bold text-lg text-emerald-900 focus:ring-0 p-0" 
                                                                value="{{ $rt['so_tre_em'] ?? 0 }}" min="0" readonly
                                                                onchange="syncGlobalGuestCounts()">
                                                            <button type="button" onclick="changeRoomGuestCount('children', {{ $loop->index }}, 1)" class="w-7 h-7 flex items-center justify-center rounded-lg bg-white border border-emerald-200 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all active:scale-90">
                                                                <i class="fas fa-plus text-[10px]"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <!-- Infants -->
                                                    <div class="flex flex-col items-center p-3 bg-pink-50/50 rounded-xl border border-pink-100 transition-all hover:bg-pink-50">
                                                        <div class="flex items-center gap-2 mb-2">
                                                            <i class="fas fa-baby text-pink-500 text-xs"></i>
                                                            <span class="text-[10px] font-bold text-pink-700 uppercase">Em bé</span>
                                                        </div>
                                                        <div class="flex items-center gap-3">
                                                            <button type="button" onclick="changeRoomGuestCount('infants', {{ $loop->index }}, -1)" class="w-7 h-7 flex items-center justify-center rounded-lg bg-white border border-pink-200 text-pink-600 hover:bg-pink-600 hover:text-white transition-all active:scale-90">
                                                                <i class="fas fa-minus text-[10px]"></i>
                                                            </button>
                                                            <input type="number" 
                                                                class="room-infants-input w-8 bg-transparent border-none text-center font-bold text-lg text-pink-900 focus:ring-0 p-0" 
                                                                value="{{ $rt['so_em_be'] ?? 0 }}" min="0" readonly
                                                                onchange="syncGlobalGuestCounts()">
                                                            <button type="button" onclick="changeRoomGuestCount('infants', {{ $loop->index }}, 1)" class="w-7 h-7 flex items-center justify-center rounded-lg bg-white border border-pink-200 text-pink-600 hover:bg-pink-600 hover:text-white transition-all active:scale-90">
                                                                <i class="fas fa-plus text-[10px]"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                            
                                            <!-- Chọn phòng cụ thể -->
                                            <div id="available_rooms_{{ $loop->index }}" class="mt-4 pt-4 border-t border-blue-100">
                                                <div class="flex items-center justify-between mb-3">
                                                    <label class="text-sm font-medium text-gray-700 flex items-center gap-2">
                                                        <i class="fas fa-check-square text-blue-500"></i>
                                                        Chọn phòng cụ thể
                                                    </label>
                                                    <button type="button" onclick="loadAvailableRooms({{ $loop->index }})"
                                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                                        <i class="fas fa-sync-alt mr-1"></i> Tải danh sách phòng
                                                    </button>
                                                </div>
                                                <div id="room_list_{{ $loop->index }}" class="checkbox-container flex flex-wrap gap-2 p-3 bg-gray-50 rounded-lg min-h-[50px]">
                                                    <p class="text-xs text-gray-400 italic">Nhấn "Tải danh sách phòng" để xem các phòng trống</p>
                                                </div>
                                            </div>
                                            
                                            <input type="hidden" name="room_types[{{ $loop->index }}][gia_rieng]"
                                                id="room_gia_rieng_{{ $loop->index }}" value="{{ $rtSoLuong * $rtGia }}">
                                        </div>
                                    @endforeach
                                </div>
                                
                                <!-- Empty state -->
                                <div id="emptyRoomState" class="hidden text-center py-8 bg-gray-50 rounded-xl border-2 border-dashed border-gray-300">
                                    <i class="fas fa-bed text-4xl text-gray-300 mb-3"></i>
                                    <p class="text-gray-500">Chưa có loại phòng nào được chọn</p>
                                    <button type="button" onclick="document.getElementById('btnAddRoom').click()"
                                        class="mt-3 text-blue-600 hover:text-blue-700 font-medium text-sm">
                                        + Thêm loại phòng đầu tiên
                                    </button>
                                </div>
                            </section>

                            <!-- Ngày nhận, ngày trả, số người -->
                            <section>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">Thông tin
                                    đặt phòng</h3>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div>
                                        <label for="ngay_nhan" class="block text-sm font-medium text-gray-700 mb-2">Ngày
                                            nhận phòng</label>
                                        <input type="date" name="ngay_nhan" id="ngay_nhan"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                            value="{{ old('ngay_nhan', $booking->ngay_nhan ? date('Y-m-d', strtotime($booking->ngay_nhan)) : '') }}"
                                            required>
                                        @error('ngay_nhan')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="ngay_tra" class="block text-sm font-medium text-gray-700 mb-2">Ngày trả
                                            phòng</label>
                                        <input type="date" name="ngay_tra" id="ngay_tra"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                            value="{{ old('ngay_tra', $booking->ngay_tra ? date('Y-m-d', strtotime($booking->ngay_tra)) : '') }}"
                                            required>
                                        @error('ngay_tra')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="md:col-span-4 mt-2">
                                        <label class="block text-sm font-semibold text-gray-600 mb-6 uppercase tracking-wide text-[11px]">Cấu hình khách hàng</label>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
                                            {{-- Người lớn --}}
                                            <div class="bg-blue-50/50 border border-blue-100 rounded-[2.5rem] p-6 transition-all hover:shadow-md group text-center sm:text-left">
                                                <div class="flex items-center gap-5 mb-6">
                                                    <div class="w-16 h-16 bg-blue-600 rounded-[1.25rem] flex items-center justify-center text-white shadow-xl shadow-blue-200/50 group-hover:scale-110 transition-transform duration-300">
                                                        <i class="fas fa-user-tie text-2xl"></i>
                                                    </div>
                                                    <div class="flex-1 text-left">
                                                        <h4 class="font-black text-blue-900 text-xl leading-tight">Người lớn</h4>
                                                        <p class="text-[10px] text-blue-400 font-black uppercase tracking-widest mt-0.5">Trên 12 tuổi</p>
                                                    </div>
                                                </div>
                                                <div class="relative bg-white rounded-[2rem] h-32 flex items-center justify-center shadow-sm border border-blue-50/50">
                                                    <div class="absolute left-8 h-full py-6 flex flex-col justify-between items-center z-10">
                                                        <button type="button" onclick="changeGuestCount('adults', -1)"
                                                            class="text-blue-300 hover:text-red-500 transition-colors transform active:scale-90 p-1">
                                                            <i class="fas fa-minus text-xl"></i>
                                                        </button>
                                                        <button type="button" onclick="changeGuestCount('adults', 1)"
                                                            class="text-blue-300 hover:text-blue-600 transition-colors transform active:scale-90 p-1">
                                                            <i class="fas fa-plus text-xl"></i>
                                                        </button>
                                                    </div>
                                                    <input type="number" name="so_nguoi" id="total_adults"
                                                        class="w-full border-none bg-transparent text-center font-black text-5xl text-blue-900 focus:ring-0 p-0 relative z-0"
                                                        value="{{ old('so_nguoi', $booking->so_nguoi ?? 1) }}" min="1" max="10" readonly
                                                        onchange="updateAllRoomGuests('adults'); if (window.computeTotals) window.computeTotals();">
                                                </div>
                                            </div>

                                            {{-- Trẻ em --}}
                                            <div class="bg-emerald-50/50 border border-emerald-100 rounded-[2.5rem] p-6 transition-all hover:shadow-md group text-center sm:text-left">
                                                <div class="flex items-center gap-5 mb-6">
                                                    <div class="w-16 h-16 bg-emerald-500 rounded-[1.25rem] flex items-center justify-center text-white shadow-xl shadow-emerald-200/50 group-hover:scale-110 transition-transform duration-300">
                                                        <i class="fas fa-child-reaching text-2xl"></i>
                                                    </div>
                                                    <div class="flex-1 text-left">
                                                        <h4 class="font-black text-emerald-900 text-xl leading-tight">Trẻ em</h4>
                                                        <p class="text-[10px] text-emerald-400 font-black uppercase tracking-widest mt-0.5">6 - 12 tuổi</p>
                                                    </div>
                                                </div>
                                                <div class="relative bg-white rounded-[2rem] h-32 flex items-center justify-center shadow-sm border border-emerald-50/50">
                                                    <div class="absolute left-8 h-full py-6 flex flex-col justify-between items-center z-10">
                                                        <button type="button" onclick="changeGuestCount('children', -1)"
                                                            class="text-emerald-300 hover:text-red-500 transition-colors transform active:scale-90 p-1">
                                                            <i class="fas fa-minus text-xl"></i>
                                                        </button>
                                                        <button type="button" onclick="changeGuestCount('children', 1)"
                                                            class="text-emerald-300 hover:text-emerald-600 transition-colors transform active:scale-90 p-1">
                                                            <i class="fas fa-plus text-xl"></i>
                                                        </button>
                                                    </div>
                                                    <input type="number" name="so_tre_em" id="total_children"
                                                        class="w-full border-none bg-transparent text-center font-black text-5xl text-emerald-900 focus:ring-0 p-0 relative z-0"
                                                        value="{{ old('so_tre_em', $booking->so_tre_em ?? 0) }}" min="0" max="10" readonly
                                                        onchange="updateAllRoomGuests('children'); if (window.computeTotals) window.computeTotals();">
                                                </div>
                                            </div>

                                            {{-- Em bé --}}
                                            <div class="bg-pink-50/50 border border-pink-100 rounded-[2.5rem] p-6 transition-all hover:shadow-md group text-center sm:text-left">
                                                <div class="flex items-center gap-5 mb-6">
                                                    <div class="w-16 h-16 bg-pink-500 rounded-[1.25rem] flex items-center justify-center text-white shadow-xl shadow-pink-200/50 group-hover:scale-110 transition-transform duration-300">
                                                        <i class="fas fa-baby text-2xl"></i>
                                                    </div>
                                                    <div class="flex-1 text-left">
                                                        <h4 class="font-black text-pink-900 text-xl leading-tight">Em bé</h4>
                                                        <p class="text-[10px] text-pink-400 font-black uppercase tracking-widest mt-0.5">Dưới 6 tuổi</p>
                                                    </div>
                                                </div>
                                                <div class="relative bg-white rounded-[2rem] h-32 flex items-center justify-center shadow-sm border border-pink-50/50">
                                                    <div class="absolute left-8 h-full py-6 flex flex-col justify-between items-center z-10">
                                                        <button type="button" onclick="changeGuestCount('infants', -1)"
                                                            class="text-pink-300 hover:text-red-500 transition-colors transform active:scale-90 p-1">
                                                            <i class="fas fa-minus text-xl"></i>
                                                        </button>
                                                        <button type="button" onclick="changeGuestCount('infants', 1)"
                                                            class="text-pink-300 hover:text-pink-600 transition-colors transform active:scale-90 p-1">
                                                            <i class="fas fa-plus text-xl"></i>
                                                        </button>
                                                    </div>
                                                    <input type="number" name="so_em_be" id="total_infants"
                                                        class="w-full border-none bg-transparent text-center font-black text-5xl text-pink-900 focus:ring-0 p-0 relative z-0"
                                                        value="{{ old('so_em_be', $booking->so_em_be ?? 0) }}" min="0" max="10" readonly
                                                        onchange="updateAllRoomGuests('infants'); if (window.computeTotals) window.computeTotals();">
                                                </div>
                                            </div>
                                        </div>
                                        <p id="guest_limit_error" class="mt-8 text-sm text-red-600 font-bold hidden bg-red-50 p-4 rounded-2xl border border-red-100 flex items-center gap-3">
                                            <i class="fas fa-exclamation-circle text-xl"></i>
                                            <span>Giới hạn khách thực tế có thể thay đổi tùy theo loại phòng đã chọn.</span>
                                        </p>
                                    </div>
                                </div>
                            </section>

                            <!-- Chọn dịch vụ -->
                            <section>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">Chọn
                                    dịch vụ</h3>
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
                                    @foreach ($services as $service)
                                        @php
                                            $sid = $service->id;
                                            // Determine inclusion from actual booking services (BookingService records)
                                            // This is more reliable than checking the JS-friendly array which may be malformed.
                                            $isIncluded =
                                                isset($bookingServices) &&
                                                $bookingServices->pluck('service_id')->contains($sid);
                                        @endphp
                                        <div
                                            class="flex flex-col items-start gap-1 p-2 bg-white rounded border border-blue-100">
                                            <input type="checkbox" id="service_select_{{ $sid }}"
                                                name="services_select[]" value="{{ $sid }}"
                                                {{ $isIncluded ? 'checked' : '' }} class="cursor-pointer" />
                                            <label for="service_select_{{ $sid }}" class="cursor-pointer">
                                                <div class="font-medium text-gray-900 text-xs">{{ $service->name }}</div>
                                                <div class="text-xs text-gray-600">
                                                    {{ number_format($service->price, 0, ',', '.') }} VNĐ</div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                        </div>

                        <style>
                            .service-card-custom {
                                border-radius: 10px;
                                background: linear-gradient(135deg, #e0f2fe 0%, #bfdbfe 100%);
                                border: 1.5px solid #2563eb;
                                /* blue-600 */
                                padding: 0.875rem;
                                box-shadow: 0 6px 18px rgba(37, 99, 235, 0.06);
                            }

                            /* responsive grid for cards */
                            .service-card-grid {
                                display: grid;
                                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                                gap: 0.75rem
                            }

                            .service-card-header {
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                margin-bottom: 0.5rem;
                                padding-bottom: 0.4rem;
                                border-bottom: 1.5px solid #bfdbfe
                            }

                            .service-card-header .service-title {
                                color: #1e40af;
                                font-weight: 600;
                                font-size: 0.95rem
                            }

                            .service-card-header .service-price {
                                color: #1e3a8a;
                                font-weight: 600;
                                font-size: 0.85rem
                            }

                            .service-date-row {
                                display: flex;
                                gap: 0.5rem;
                                align-items: center;
                                margin-top: 0.5rem;
                                padding: 0.4rem;
                                background: #ffffff;
                                border-radius: 6px;
                                border: 1px solid #bfdbfe
                            }

                            .service-date-row input[type=date] {
                                border: 1px solid #93c5fd;
                                padding: 0.35rem 0.5rem;
                                border-radius: 5px;
                                background: #eff6ff;
                                font-size: 0.85rem;
                                flex: 1
                            }

                            .service-date-row input[type=number] {
                                border: 1px solid #93c5fd;
                                padding: 0.35rem 0.5rem;
                                border-radius: 5px;
                                background: #eff6ff;
                                width: 64px;
                                text-align: center;
                                font-size: 0.85rem
                            }

                            .service-add-day {
                                background: linear-gradient(135deg, #93c5fd 0%, #2563eb 100%);
                                color: #08203a;
                                padding: 0.4rem 0.6rem;
                                border-radius: 6px;
                                border: 1.5px solid #60a5fa;
                                cursor: pointer;
                                font-weight: 600;
                                font-size: 0.85rem
                            }

                            .service-add-day:hover {
                                background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
                                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.12)
                            }

                            .service-remove-btn {
                                background: #fee2e2;
                                color: #991b1b;
                                padding: 0.3rem 0.5rem;
                                border-radius: 5px;
                                border: 1px solid #fecaca;
                                cursor: pointer;
                                font-weight: 600;
                                font-size: 0.8rem
                            }

                            .service-remove-btn:hover {
                                background: #fca5a5;
                                box-shadow: 0 3px 10px rgba(185, 28, 28, 0.12)
                            }

                            /* selected list item hover */
                            #selected_services_list .service-card-custom {
                                transition: all .18s ease
                            }

                            #selected_services_list .service-card-custom:hover {
                                transform: translateY(-4px);
                                box-shadow: 0 10px 26px rgba(37, 99, 235, 0.12)
                            }

                            /* Room mode selection */
                            .service-room-mode {
                                display: flex;
                                gap: 0.75rem;
                                margin-top: 0.75rem;
                                padding: 0.75rem;
                                background: #f0fdf4;
                                border: 1.5px solid #86efac;
                                border-radius: 8px
                            }

                            .service-room-mode label {
                                display: flex;
                                align-items: center;
                                gap: 0.4rem;
                                font-size: 0.85rem;
                                color: #166534;
                                font-weight: 600;
                                cursor: pointer
                            }

                            .service-room-mode input[type=radio] {
                                cursor: pointer
                            }

                            /* Room container for per-entry selection */
                            .entry-room-container {
                                margin-top: 0.75rem;
                                padding: 0.75rem;
                                background: #f8f4ff;
                                border: 1.5px solid #d8b4fe;
                                border-radius: 6px
                            }

                            .entry-room-container .checkbox-group {
                                display: flex;
                                flex-wrap: wrap;
                                gap: 0.5rem
                            }

                            .entry-room-container .checkbox-group label {
                                display: flex;
                                align-items: center;
                                gap: 0.35rem;
                                background: white;
                                padding: 0.3rem 0.5rem;
                                border: 1px solid #d8b4fe;
                                border-radius: 5px;
                                font-size: 0.8rem;
                                cursor: pointer;
                                transition: all 0.15s ease
                            }

                            .entry-room-container .checkbox-group label:hover {
                                background: #faf5ff;
                                border-color: #c084fc
                            }

                            .entry-room-container .checkbox-group input[type=checkbox] {
                                cursor: pointer;
                                width: 14px;
                                height: 14px
                            }

                            .entry-room-container .checkbox-group input[type=checkbox]:checked+span {
                                color: #7e22ce;
                                font-weight: 600
                            }

                            .entry-room-container .checkbox-group span {
                                color: #6b21a8;
                                font-size: 0.8rem;
                                font-weight: 500
                            }
                        </style>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Chi tiết dịch vụ đã chọn</label>
                            <div class="service-card-grid grid grid-cols-1 md:grid-cols-3 gap-6 mt-4"
                                id="services_details_container">
                                {{-- Service cards will be rendered here by JavaScript based on selected services --}}
                            </div>
                        </div>

                        @if ($bookingServices->count() > 0)
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                                <h4 class="text-base font-semibold text-gray-900 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 17v-2h6v2m-7 4h8a2 2 0 002-2v-4a2 2 0 00-2-2H8a2 2 0 00-2 2v4a2 2 0 002 2zm3-13h4l1 2h3a1 1 0 011 1v3h-2v-2h-3.382l-.724-1.447A1 1 0 0013.943 8H12v2H9V8h3z" />
                                    </svg>
                                    Dịch vụ hiện có
                                </h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left font-medium text-gray-600">Dịch vụ</th>
                                                <th class="px-4 py-2 text-center font-medium text-gray-600">Ngày dùng</th>
                                                <th class="px-4 py-2 text-center font-medium text-gray-600">Số phòng</th>
                                                <th class="px-4 py-2 text-center font-medium text-gray-600">Số lượng</th>
                                                <th class="px-4 py-2 text-center font-medium text-gray-600">Đơn giá</th>
                                                <th class="px-4 py-2 text-right font-medium text-gray-600">Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @php
                                                // Reload booking services từ DB để lấy dữ liệu mới nhất (không phải cached data)
                                                $freshBookingServices = \App\Models\BookingService::with([
                                                    'service',
                                                    'phong',
                                                ])
                                                    ->where('dat_phong_id', $booking->id)
                                                    ->orderBy('used_at', 'asc')
                                                    ->get();
                                            @endphp
                                            @forelse ($freshBookingServices as $serviceLine)
                                                @php
                                                    $name =
                                                        $serviceLine->service->name ??
                                                        ($serviceLine->service_name ?? 'Dịch vụ');
                                                    $usedAt = $serviceLine->used_at
                                                        ? \Carbon\Carbon::parse($serviceLine->used_at)->format('d/m/Y')
                                                        : '-';
                                                    $phongNum = $serviceLine->phong
                                                        ? $serviceLine->phong->so_phong ?? $serviceLine->phong->id
                                                        : '-';
                                                    $qty = $serviceLine->quantity ?? 0;
                                                    $unit = $serviceLine->unit_price ?? 0;
                                                    $subtotal = $qty * $unit;
                                                @endphp
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-2 text-gray-900">{{ $name }}</td>
                                                    <td class="px-4 py-2 text-center text-gray-600">{{ $usedAt }}
                                                    </td>
                                                    <td class="px-4 py-2 text-center text-gray-600">{{ $phongNum }}
                                                    </td>
                                                    <td class="px-4 py-2 text-center text-gray-600">{{ $qty }}
                                                    </td>
                                                    <td class="px-4 py-2 text-center text-gray-600">
                                                        {{ number_format($unit, 0, ',', '.') }} VNĐ</td>
                                                    <td class="px-4 py-2 text-right font-semibold text-gray-900">
                                                        {{ number_format($subtotal, 0, ',', '.') }} VNĐ</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="px-4 py-2 text-center text-gray-500">Chưa có
                                                        dịch vụ nào</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3 pt-3 border-t border-gray-200 flex justify-between text-sm font-medium">
                                    <span class="text-gray-700">Tổng tiền dịch vụ hiện tại</span>
                                    <span class="text-purple-600">
                                        {{ number_format($bookingServices->sum(function ($s) {return ($s->quantity ?? 0) * ($s->unit_price ?? 0);}),0,',','.') }}
                                        VNĐ
                                    </span>
                                </div>
                            </div>
                        @endif

                        <!-- Voucher Selection -->
                        <div class="bg-purple-50 p-4 rounded-lg my-6 border border-purple-200">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Chọn mã giảm giá (nếu có)</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                                @foreach ($vouchers as $voucher)
                                    @php
                                        $isDisabled = $voucher->status !== 'con_han';
                                        $overlayText =
                                            $voucher->status === 'het_han'
                                                ? 'Hết hạn'
                                                : ($voucher->status === 'huy'
                                                    ? 'Đã hủy'
                                                    : '');
                                    @endphp

                                    <div class="relative voucher-container" data-voucher-id="{{ $voucher->id }}">
                                        <input type="radio" name="voucher_radio" id="voucher_{{ $voucher->id }}"
                                            value="{{ $voucher->ma_voucher }}" class="sr-only peer voucher-radio"
                                            data-value="{{ $voucher->gia_tri }}"
                                            data-loai-phong="{{ $voucher->loai_phong_id }}"
                                            data-start="{{ $voucher->ngay_bat_dau ? date('Y-m-d', strtotime($voucher->ngay_bat_dau)) : '' }}"
                                            data-end="{{ $voucher->ngay_ket_thuc ? date('Y-m-d', strtotime($voucher->ngay_ket_thuc)) : '' }}"
                                            {{ $booking->voucher_id === $voucher->id ? 'checked' : '' }}> <label
                                            for="voucher_{{ $voucher->id }}"
                                            class="block p-4 bg-gray-50 border-2 border-gray-200 rounded-xl cursor-pointer relative transition-all duration-300 ease-in-out
                                            peer-checked:bg-white peer-checked:border-green-500 peer-checked:ring-2 peer-checked:ring-green-400 peer-checked:shadow-lg
                                            hover:bg-gray-100 hover:border-gray-300 hover:shadow-md
                                            z-0 peer-checked:z-10 voucher-label {{ $isDisabled ? 'opacity-50 cursor-not-allowed' : '' }}">

                                            {{-- Overlay hiển thị trạng thái --}}
                                            @if ($isDisabled)
                                                <div
                                                    class="voucher-overlay-server absolute inset-0 bg-opacity-70 flex items-center justify-center rounded-xl pointer-events-none">
                                                    <span
                                                        class="text-gray-700 text-sm font-medium">{{ $overlayText }}</span>
                                                </div>
                                            @endif

                                            <div class="space-y-2">
                                                <div class="flex items-center justify-between">
                                                    <p class="text-sm font-bold text-green-600">
                                                        {{ $voucher->ma_voucher }}</p>
                                                    <span
                                                        class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                                        @if ($voucher->gia_tri <= 100)
                                                            {{ $voucher->gia_tri }}%
                                                        @else
                                                            {{ number_format($voucher->gia_tri, 0, ',', '.') }}₫
                                                        @endif
                                                    </span>
                                                </div>
                                                <p class="text-xs text-gray-600">
                                                    @if ($voucher->gia_tri <= 100)
                                                        Giảm {{ $voucher->gia_tri }}%
                                                    @else
                                                        Giảm
                                                        {{ number_format($voucher->gia_tri, 0, ',', '.') }}₫
                                                    @endif
                                                </p>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Hiển thị tổng tiền chi tiết (mirrors create view) -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                            {{-- Hiển thị danh sách loại phòng đã chọn --}}
                            <div id="selected_rooms_summary" class="mb-3 pb-3 border-b border-blue-200">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-bed mr-1 text-blue-600"></i> Loại phòng đã chọn:
                                </h4>
                                <div id="selected_rooms_list" class="space-y-1 text-sm">
                                    <!-- Sẽ được cập nhật bằng JavaScript -->
                                </div>
                            </div>

                             {{-- Hiển thị số khách --}}
                             <div id="guest_summary" class="mb-3 pb-3 border-b border-blue-200">
                                 <div class="flex flex-wrap gap-2 items-center">
                                     <div id="total_adults_display" class="flex items-center bg-blue-100 text-blue-700 px-3 py-1.5 rounded-full text-xs font-bold border border-blue-200">
                                         <i class="fas fa-user-tie mr-1.5"></i> <span id="adults_count">0</span> <span class="ml-1">Người lớn</span>
                                     </div>
                                     <div id="total_children_display" class="flex items-center bg-green-100 text-green-700 px-3 py-1.5 rounded-full text-xs font-bold border border-green-200 hidden">
                                         <i class="fas fa-child mr-1.5"></i> <span id="children_count">0</span> <span class="ml-1">Trẻ em</span>
                                     </div>
                                     <div id="total_infants_display" class="flex items-center bg-pink-100 text-pink-700 px-3 py-1.5 rounded-full text-xs font-bold border border-pink-200 hidden">
                                         <i class="fas fa-baby mr-1.5"></i> <span id="infants_count">0</span> <span class="ml-1">Em bé</span>
                                     </div>
                                 </div>
                             </div>

                            {{-- Hiển thị phụ phí --}}
                            <div id="surcharge_summary" class="mb-3 space-y-1 hidden">
                                <div id="extra_adult_fee" class="text-sm text-amber-700 hidden">
                                    <i class="fas fa-user-plus mr-1"></i>Thêm người lớn: <span class="font-semibold">+<span id="extra_adult_fee_amount">0</span> VNĐ</span>
                                </div>
                                <div id="child_fee" class="text-sm text-green-700 hidden">
                                    <i class="fas fa-child mr-1"></i>Thêm trẻ em: <span class="font-semibold">+<span id="child_fee_amount">0</span> VNĐ</span>
                                </div>
                                <div id="infant_fee" class="text-sm text-pink-700 hidden">
                                    <i class="fas fa-baby mr-1"></i>Thêm em bé: <span class="font-semibold">+<span id="infant_fee_amount">0</span> VNĐ</span>
                                </div>
                            </div>

                            {{-- Tổng cộng --}}
                            <div class="flex justify-between items-center mb-3 pt-3 border-t border-blue-200">
                                <span class="text-gray-900 font-semibold">Tổng cộng</span>
                                <span id="total_price" class="text-2xl font-bold text-red-600">0 VNĐ</span>
                            </div>

                            {{-- Giá đã áp dụng --}}
                            <div id="pricing_multiplier_info" class="text-xs text-gray-600 mt-2 hidden"></div>

                            {{-- Discount info --}}
                            <div id="discount_info" class="text-sm text-gray-700 hidden pt-3 border-t border-blue-200">
                                <div class="flex justify-between mb-1">
                                    <span>Giá gốc:</span>
                                    <span id="original_price" class="line-through text-gray-500">0 VNĐ</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-green-600">Giảm giá:</span>
                                    <span id="discount_amount" class="text-green-600 font-semibold">-0 VNĐ</span>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="tong_tien" id="tong_tien_input" value="{{ old('tong_tien', $booking->tong_tien ?? 0) }}">
                        <input type="hidden" name="voucher_id" id="voucher_id_input" value="{{ old('voucher_id', $booking->voucher_id ?? '') }}">
                        <!-- Mirror field name expected by backend: 'voucher' -->
                        <input type="hidden" name="voucher" id="voucher_input" value="{{ old('voucher', optional($booking->voucher)->ma_voucher ?? '') }}">

                        <!-- Thông tin khách hàng -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Thông tin khách hàng</h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="username" class="block text-sm font-medium text-gray-700">Tên khách
                                        hàng</label>
                                    <input type="text" name="username" id="username"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        value="{{ old('username', $booking->username) }}" required>
                                    @error('username')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" name="email" id="email"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        value="{{ old('email', $booking->email) }}" required>
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="sdt" class="block text-sm font-medium text-gray-700">Số điện
                                            thoại</label>
                                        <input type="text" name="sdt" id="sdt"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            value="{{ old('sdt', $booking->sdt) }}" required>
                                        @error('sdt')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="cccd"
                                            class="block text-sm font-medium text-gray-700">CCCD/CMND</label>
                                        <input type="text" name="cccd" id="cccd"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            value="{{ old('cccd', $booking->cccd) }}"
                                            placeholder="{{ $booking->cccd ? '' : 'Chưa cập nhật CCCD' }}" required>
                                        @if (!$booking->cccd)
                                            <p class="mt-1 text-xs text-yellow-600">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                Đặt phòng cũ chưa có CCCD, vui lòng cập nhật
                                            </p>
                                        @endif
                                        @error('cccd')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>



                        <input type="hidden" name="trang_thai" id="trang_thai_input"
                            value="{{ old('trang_thai', $booking->trang_thai ?? '') }}">

                        <div class="pt-5">
                            <div class="flex justify-between items-center gap-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.dat_phong.index') }}"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                        </svg>
                                        Quay lại
                                    </a>
                                </div>

                                <!-- Group buttons -->
                                <div class="flex items-center">

                                    <!-- Confirm -->
                                    <button type="button" title="Xác nhận" onclick="if(confirm('Xác nhận đặt phòng #{{ $booking->id }}?')){ const f=document.getElementById('bookingForm'); let inp=document.getElementById('confirm_and_save_input'); if(!inp){ inp=document.createElement('input'); inp.type='hidden'; inp.name='confirm_and_save'; inp.id='confirm_and_save_input'; inp.value='1'; f.appendChild(inp);} f.submit(); }"
                                        class="inline mx-1 inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        Xác nhận
                                    </button>

                                    <!-- Cancel: Chỉ Admin -->
                                    @hasRole('admin')
                                        <a href="{{ route('admin.dat_phong.cancel', $booking->id) }}" title="Hủy"
                                            class="inline-flex items-center px-4 py-2 mx-1 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700"
                                            onclick="if(!confirm('Bạn có chắc chắn muốn hủy đặt phòng #{{ $booking->id }}?')) return false;">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Hủy đặt phòng
                                        </a>
                                    @endhasRole

                                    <!-- Update -->
                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 mx-1 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        Cập nhật
                                    </button>

                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            console.log('=== EDIT BOOKING SCRIPT LOADED ===');
            
            // Get booking dates
            function getRangeDates() {
                const start = document.getElementById('ngay_nhan')?.value;
                const end = document.getElementById('ngay_tra')?.value;
                if (!start || !end) return [];
                const dates = [];
                const s = new Date(start);
                const e = new Date(end);
                for (let d = new Date(s); d <= e; d.setDate(d.getDate() + 1)) {
                    dates.push(new Date(d).toISOString().split('T')[0]);
                }
                return dates;
            }

            // Initialize service rendering
            const allServices = @json($services ?? []);
            const bookingServicesData = @json($bookingServicesServer ?? []);
            // make these mutable so client-side changes reflect immediately
            let assignedPhongIds = @json($assignedPhongIds ?? []);
            let roomMapData = @json($roomMap ?? []);
            // (flag removed) previously used to distinguish user-triggered updates

            // Sync assignedPhongIds and roomMapData from current room checkboxes
            function syncAssignedPhongIdsFromCheckboxes() {
                try {
                    const checked = Array.from(document.querySelectorAll('input.available-room-checkbox:checked'))
                        .map(cb => parseInt(cb.value)).filter(Boolean);
                    assignedPhongIds = Array.from(new Set(checked));

                    // Ensure roomMapData has labels for any known checkboxes
                    Array.from(document.querySelectorAll('input.available-room-checkbox')).forEach(cb => {
                        const id = parseInt(cb.value);
                        const name = cb.getAttribute('data-room-name') || cb.getAttribute('data-room-id') || id;
                        if (id && !(id in roomMapData)) {
                            roomMapData[id] = name;
                        }
                    });

                    // Re-render service room lists to reflect updated assigned rooms
                    updateServiceRoomLists();

                    // Auto-switch service room mode: if any rooms selected -> specific, if none -> global
                    document.querySelectorAll('[data-service-id]').forEach(card => {
                        const sid = card.getAttribute('data-service-id');
                        const globalRadio = card.querySelector('input[name="service_room_mode_' + sid +
                            '"][value="global"]');
                        const specificRadio = card.querySelector('input[name="service_room_mode_' + sid +
                            '"][value="specific"]');
                        if (assignedPhongIds && assignedPhongIds.length > 0) {
                            if (specificRadio && !specificRadio.checked) {
                                specificRadio.checked = true;
                                if (typeof specificRadio.onchange === 'function') specificRadio.onchange();
                            }
                        } else {
                            if (globalRadio && !globalRadio.checked) {
                                globalRadio.checked = true;
                                if (typeof globalRadio.onchange === 'function') globalRadio.onchange();
                            }
                        }
                    });
                } catch (e) {
                    console.error('syncAssignedPhongIdsFromCheckboxes error', e);
                }
            }

            function buildDateRow(serviceId, dateVal = '') {
                const idx = 'new_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                const r = document.createElement('div');
                r.className = 'service-date-row';

                const d = document.createElement('input');
                d.type = 'date';
                d.className = 'border rounded p-1';
                d.value = dateVal || '';
                const rg = getRangeDates();
                if (rg.length) {
                    d.min = rg[0];
                    d.max = rg[rg.length - 1];
                }
                d.addEventListener('focus', function() {
                    this.dataset.prev = this.value || '';
                });
                d.addEventListener('change', function() {
                    const val = this.value || '';
                    if (!val) {
                        syncHiddenEntries(serviceId);
                        return;
                    }
                    const others = Array.from(document.querySelectorAll('#service_dates_' + serviceId +
                            ' input[type=date]'))
                        .filter(i => i !== this)
                        .map(i => i.value);
                    if (others.includes(val)) {
                        const prev = this.dataset.prev || '';
                        this.value = prev;
                        alert('Ngày này đã được chọn cho dịch vụ này. Vui lòng chọn ngày khác.');
                        return;
                    }
                    syncHiddenEntries(serviceId);
                });

                const q = document.createElement('input');
                q.type = 'number';
                q.min = 1;
                q.value = 1;
                q.className = 'w-24 border rounded p-1 text-center';
                q.onchange = () => syncHiddenEntries(serviceId);

                const rem = document.createElement('button');
                rem.type = 'button';
                rem.className = 'service-remove-btn ml-2';
                rem.textContent = 'Xóa';
                rem.onclick = () => {
                    r.remove();
                    syncHiddenEntries(serviceId);
                    window.computeTotals && window.computeTotals();
                };

                r.appendChild(d);
                r.appendChild(q);
                r.appendChild(rem);

                // Entry room container for per-room selection - INLINE
                const entryRoomContainer = document.createElement('div');
                entryRoomContainer.className = 'entry-room-container';
                entryRoomContainer.style.display = 'none';
                r.appendChild(entryRoomContainer);

                return r;
            }

            // Update room lists in service cards - render room checkboxes inline
            function updateServiceRoomLists(filterServiceId) {
                const selectedRoomInputs = Array.from(document.querySelectorAll('.available-room-checkbox:checked'));

                document.querySelectorAll('[data-service-id]').forEach(card => {
                    const serviceId = card.getAttribute('data-service-id');

                    // If filterServiceId is provided, only update that service
                    if (filterServiceId && parseInt(serviceId) !== parseInt(filterServiceId)) {
                        return;
                    }

                    const specificRadio = card.querySelector('input[name="service_room_mode_' + serviceId +
                        '"][value="specific"]');
                    const isSpecific = specificRadio ? specificRadio.checked : false;
                    console.log('updateServiceRoomLists: service', serviceId, 'isSpecific=', isSpecific,
                        'assignedPhongIds=', assignedPhongIds);

                    const rows = card.querySelectorAll('.service-date-row');
                    rows.forEach((r) => {
                        const dateValRow = r.querySelector('input[type=date]')?.value || '';
                        let entryRoomContainer = r.querySelector('.entry-room-container');
                        if (!entryRoomContainer) {
                            entryRoomContainer = document.createElement('div');
                            entryRoomContainer.className = 'entry-room-container';
                            r.appendChild(entryRoomContainer);
                        }
                        entryRoomContainer.innerHTML = '';

                        if (!isSpecific) {
                            entryRoomContainer.style.display = 'none';
                            return;
                        } else {
                            entryRoomContainer.style.display = '';
                        }

                        // Get union of assigned rooms + all rooms from service data (to show even unassigned rooms that have service data)
                        const roomsToShow = new Set(assignedPhongIds);

                        // Build entriesByDate for this service so we can pre-check boxes per row date
                        const entriesByDate = {};
                        if (bookingServicesData[serviceId]) {
                            bookingServicesData[serviceId]['entries'].forEach(entry => {
                                const day = entry['ngay'] || '';
                                if (!entriesByDate[day]) entriesByDate[day] = {
                                    phong_ids: []
                                };
                                if (entry['phong_ids'] && Array.isArray(entry['phong_ids'])) {
                                    entriesByDate[day].phong_ids = Array.from(new Set([
                                        ...entriesByDate[day].phong_ids,
                                        ...entry['phong_ids']
                                    ]));
                                    entry['phong_ids'].forEach(pid => roomsToShow.add(pid));
                                }
                            });
                        }

                        // Sort room IDs for consistent display
                        const roomsArray = Array.from(roomsToShow).sort((a, b) => a - b);
                        console.log('updateServiceRoomLists: service', serviceId, 'roomsArray=', roomsArray,
                            'rowDate=', dateValRow);

                        roomsArray.forEach(pid => {
                            const pidInt = parseInt(pid);
                            const label = roomMapData[pid] || pid;
                            const ewrap = document.createElement('div');
                            ewrap.className = 'inline-flex items-center gap-1 mr-2';

                            const ecb = document.createElement('input');
                            ecb.type = 'checkbox';
                            ecb.className = 'entry-room-checkbox';
                            ecb.setAttribute('data-room-id', pid);
                            ecb.value = pid;

                            ecb.onchange = () => {
                                syncHiddenEntries(serviceId);
                                window.computeTotals && window.computeTotals();
                            };

                            // Pre-check if bookingServicesData has this room for this row date
                            try {
                                const phongIdsForDate = (entriesByDate[dateValRow]?.phong_ids || [])
                                    .map(p => parseInt(p));
                                // Only pre-check if this service entry explicitly contains this room
                                // (i.e. the room was saved for this service in DB). Do NOT auto-check
                                // when admin merely selects rooms in the room list — user should
                                // manually choose service rooms.
                                if (phongIdsForDate.includes(pidInt)) {
                                    ecb.checked = true;
                                    console.log('Pre-checked service', serviceId, 'date', dateValRow,
                                        'room', pidInt);
                                }
                            } catch (e) {
                                // ignore parsing issues
                            }

                            const elbl = document.createElement('label');
                            elbl.className = 'text-xs cursor-pointer';
                            elbl.textContent = label;

                            ewrap.appendChild(ecb);
                            ewrap.appendChild(elbl);
                            entryRoomContainer.appendChild(ewrap);
                            // debug marker
                            //console.log('Appended checkbox for service', serviceId, 'room', pid, 'row date', r.querySelector('input[type=date]')?.value);
                        });
                    });

                    // After rebuilding entry-room-checkboxes for this service, ensure hidden inputs reflect current state
                    try {
                        syncHiddenEntries(serviceId);
                        window.computeTotals && window.computeTotals();
                    } catch (e) {
                        // ignore
                    }
                });
            }

            function renderServiceCard(service) {
                const sid = service.id;
                const existing = bookingServicesData[sid] ? bookingServicesData[sid]['entries'] || [] : [];
                const hasSpecific = existing.some(e => e['phong_ids'] && e['phong_ids'].length > 0);

                // Card wrapper
                const card = document.createElement('div');
                card.className = 'service-card-custom';
                card.setAttribute('data-service-id', sid);

                // Header
                const header = document.createElement('div');
                header.className = 'service-card-header';
                const title = document.createElement('div');
                title.innerHTML = `<div class="service-title">${service.name}</div>`;
                const price = document.createElement('div');
                price.className = 'service-price';
                price.innerHTML = `${new Intl.NumberFormat('vi-VN').format(service.price)}/${service.unit || 'cái'}`;
                header.appendChild(title);
                header.appendChild(price);
                card.appendChild(header);

                // Room selection radios
                const roomSection = document.createElement('div');
                roomSection.className = 'service-room-mode';

                const globalRadio = document.createElement('input');
                globalRadio.type = 'radio';
                globalRadio.name = 'service_room_mode_' + sid;
                globalRadio.value = 'global';
                globalRadio.checked = !hasSpecific;
                globalRadio.id = 'global_' + sid;

                const globalLabel = document.createElement('label');
                globalLabel.htmlFor = 'global_' + sid;
                globalLabel.className = 'text-sm flex items-center gap-2 cursor-pointer';
                globalLabel.innerHTML = '<span>Áp dụng tất cả phòng</span>';

                const specificRadio = document.createElement('input');
                specificRadio.type = 'radio';
                specificRadio.name = 'service_room_mode_' + sid;
                specificRadio.value = 'specific';
                specificRadio.checked = hasSpecific;
                specificRadio.id = 'specific_' + sid;

                const specificLabel = document.createElement('label');
                specificLabel.htmlFor = 'specific_' + sid;
                specificLabel.className = 'text-sm flex items-center gap-2 cursor-pointer';
                specificLabel.innerHTML = '<span>Chọn phòng riêng</span>';

                roomSection.appendChild(globalRadio);
                roomSection.appendChild(globalLabel);
                roomSection.appendChild(specificRadio);
                roomSection.appendChild(specificLabel);

                globalRadio.onchange = () => {
                    card.querySelectorAll('.entry-room-container').forEach(c => {
                        c.style.display = 'none';
                        Array.from(c.querySelectorAll('input[type=checkbox]')).forEach(cb => cb.checked = false);
                    });
                    updateServiceRoomLists();
                    syncHiddenEntries(sid);
                    window.computeTotals && window.computeTotals();
                };

                specificRadio.onchange = () => {
                    updateServiceRoomLists();
                    syncHiddenEntries(sid);
                    window.computeTotals && window.computeTotals();
                };

                card.appendChild(roomSection);

                // Date rows container
                const rows = document.createElement('div');
                rows.id = 'service_dates_' + sid;

                // Render existing entries - GROUP BY DATE to avoid duplicates
                if (existing.length > 0) {
                    const entriesByDate = {};
                    existing.forEach((entry, idx) => {
                        const day = entry['ngay'] || '';
                        if (!entriesByDate[day]) {
                            entriesByDate[day] = {
                                so_luong: entry['so_luong'] || 1,
                                phong_ids: entry['phong_ids'] || [],
                                first_idx: idx
                            };
                        } else {
                            // Merge phong_ids if multiple entries for same date
                            entriesByDate[day].phong_ids = Array.from(new Set([
                                ...entriesByDate[day].phong_ids,
                                ...(entry['phong_ids'] || [])
                            ]));
                        }
                    });

                    Object.entries(entriesByDate).forEach(([day, data]) => {
                        const row = buildDateRow(sid, day);
                        // Set quantity
                        row.querySelector('input[type=number]').value = data.so_luong || 1;
                        rows.appendChild(row);
                    });
                }

                card.appendChild(rows);

                // Populate checkboxes and let updateServiceRoomLists handle pre-checking
                updateServiceRoomLists();

                // Add day button
                const addBtn = document.createElement('button');
                addBtn.type = 'button';
                addBtn.className = 'service-add-day mt-3';
                addBtn.textContent = '+ Thêm ngày';
                addBtn.onclick = () => {
                    const used = Array.from(rows.querySelectorAll('input[type="date"]')).map(i => i.value);
                    const avail = getRangeDates().find(d => !used.includes(d));
                    if (avail) {
                        const newRow = buildDateRow(sid, avail);
                        rows.appendChild(newRow);
                        updateServiceRoomLists();
                        syncHiddenEntries(sid);
                        updateAddBtnState();
                        window.computeTotals && window.computeTotals();
                    } else {
                        alert('Đã chọn đủ ngày. Không thể thêm ngày nữa.');
                    }
                };

                card.appendChild(addBtn);

                // Hidden service marker
                const hcb = document.createElement('input');
                hcb.type = 'checkbox';
                hcb.className = 'service-checkbox';
                hcb.name = 'services[]';
                hcb.value = sid;
                hcb.setAttribute('data-price', service.price);
                hcb.style.display = 'none';
                hcb.checked = true;
                card.appendChild(hcb);

                // Hidden total quantity
                const hsum = document.createElement('input');
                hsum.type = 'hidden';
                hsum.name = 'services_data[' + sid + '][so_luong]';
                hsum.id = 'service_quantity_hidden_' + sid;
                hsum.value = '1';
                card.appendChild(hsum);

                // Hidden service ID
                const hdv = document.createElement('input');
                hdv.type = 'hidden';
                hdv.name = 'services_data[' + sid + '][dich_vu_id]';
                hdv.value = sid;
                card.appendChild(hdv);

                return card;
            }

            function syncHiddenEntries(serviceId) {
                const container = document.getElementById('services_details_container');

                // Get all entry rows
                const rowsNow = Array.from(document.querySelectorAll('#service_dates_' + serviceId + ' .service-date-row'));

                // Remove existing hidden entry inputs for this service
                Array.from(document.querySelectorAll('input.entry-hidden[data-service="' + serviceId + '"]')).forEach(n => {
                    n.remove();
                });

                // Determine current mode
                const card = document.querySelector('[data-service-id="' + serviceId + '"]');
                const mode = card?.querySelector('input[name="service_room_mode_' + serviceId + '"]:checked')?.value ||
                    'global';

                let total = 0;
                rowsNow.forEach((r, idx) => {
                    const dateVal = r.querySelector('input[type=date]')?.value || '';
                    const qty = parseInt(r.querySelector('input[type=number]')?.value || 1);

                    // Collect per-entry selected rooms
                    const entryRoomChecks = Array.from(r.querySelectorAll('.entry-room-checkbox:checked'));

                    // If specific mode but no rooms checked, skip this entry
                    if (mode === 'specific' && entryRoomChecks.length === 0) {
                        return;
                    }

                    total += qty;

                    // Create hidden inputs for this entry
                    const hNgay = document.createElement('input');
                    hNgay.type = 'hidden';
                    hNgay.name = 'services_data[' + serviceId + '][entries][' + idx + '][ngay]';
                    hNgay.value = dateVal;
                    hNgay.className = 'entry-hidden';
                    hNgay.setAttribute('data-service', serviceId);
                    container.appendChild(hNgay);

                    const hSo = document.createElement('input');
                    hSo.type = 'hidden';
                    hSo.name = 'services_data[' + serviceId + '][entries][' + idx + '][so_luong]';
                    hSo.value = qty;
                    hSo.className = 'entry-hidden';
                    hSo.setAttribute('data-service', serviceId);
                    container.appendChild(hSo);

                    // Add room IDs if specific mode
                    if (mode === 'specific') {
                        entryRoomChecks.forEach((erc) => {
                            const hRoom = document.createElement('input');
                            hRoom.type = 'hidden';
                            hRoom.name = 'services_data[' + serviceId + '][entries][' + idx + '][phong_ids][]';
                            hRoom.value = erc.getAttribute('data-room-id') || erc.value;
                            hRoom.className = 'entry-hidden';
                            hRoom.setAttribute('data-service', serviceId);
                            container.appendChild(hRoom);
                        });
                    }
                });

                const sumEl = document.getElementById('service_quantity_hidden_' + serviceId);
                if (sumEl) sumEl.value = total;
            }

            document.addEventListener('DOMContentLoaded', function() {
                const container = document.getElementById('services_details_container');
                const serviceSelects = document.querySelectorAll('input[name="services_select[]"]');

                function updateServiceCards() {
                    window.updateServiceCards = updateServiceCards;
                    const selectedServiceIds = Array.from(serviceSelects)
                        .filter(cb => cb.checked)
                        .map(cb => cb.value);

                    // Remove cards for unselected services
                    container.querySelectorAll('.service-card-container').forEach(card => {
                        const sid = card.getAttribute('data-service-id');
                        if (!selectedServiceIds.includes(sid)) {
                            card.remove();
                        }
                    });

                    // Add cards for newly selected services
                    selectedServiceIds.forEach(sid => {
                        if (!container.querySelector(`[data-service-id="${sid}"]`)) {
                            const service = allServices.find(s => s.id == sid);
                            if (service) {
                                const card = renderServiceCard(service);
                                card.className = 'service-card-custom service-card-container';
                                container.appendChild(card);
                                syncHiddenEntries(sid);
                                updateServiceRoomLists();
                            }
                        }
                    });

                    window.computeTotals && window.computeTotals();
                }

                serviceSelects.forEach(checkbox => {
                    checkbox.addEventListener('change', updateServiceCards);
                });

                // Initial render
                updateServiceCards();
                updateServiceRoomLists();
            });
            @if (config('app.debug'))
                // Debug: print booking services data on load
                console.log('DEBUG bookingServicesServer:', @json($bookingServicesServer ?? []));
                console.log('DEBUG assignedPhongIds:', @json($assignedPhongIds ?? []));
                console.log('DEBUG roomMap:', @json($roomMap ?? []));
                console.log('DEBUG booking.room_types:', @json($booking->room_types ?? []));
                console.log('DEBUG booking.phong_ids:', @json($booking->phong_ids ?? []));
            @endif
            // Per-night booking calculations
            const allLoaiPhongsRaw = @json($loaiPhongs);
            const allLoaiPhongs = Array.isArray(allLoaiPhongsRaw) ? allLoaiPhongsRaw : Object.values(allLoaiPhongsRaw);
            const currentBookingId = {{ $booking->id ?? 'null' }};
            const selectedRoomsByLoaiPhong = @json($selectedRoomsByLoaiPhong ?? []);
            let roomIndex = {{ count($roomTypes) }};

            document.addEventListener('DOMContentLoaded', function() {
                const ngayNhanInput = document.getElementById('ngay_nhan');
                const ngayTraInput = document.getElementById('ngay_tra');

                // Only set up date listeners if inputs exist
                if (ngayNhanInput && ngayTraInput) {
                    const today = new Date().toISOString().split('T')[0];
                    ngayNhanInput.setAttribute('min', today);

                    ngayNhanInput.addEventListener('change', function() {
                        ngayTraInput.setAttribute('min', this.value);
                        if (ngayTraInput.value && ngayTraInput.value < this.value) {
                            ngayTraInput.value = this.value;
                        }
                        updateAllRoomAvailability();
                    });

                    ngayTraInput.addEventListener('change', function() {
                        updateAllRoomAvailability();
                    });

                    // Initialize prices and availability for existing rooms
                    updateAllRoomAvailability();
                }
                // derive per-night unit prices for existing rows (edit page stores totals)
                initializeRoomUnitPrices();

                // Auto-load available rooms for existing rows when page loads
                try {
                    const ngayNhanVal = document.getElementById('ngay_nhan')?.value;
                    const ngayTraVal = document.getElementById('ngay_tra')?.value;
                    if (ngayNhanVal && ngayTraVal) {
                        document.querySelectorAll('.room-item').forEach(item => {
                            const idx = item.getAttribute('data-room-index');
                            const sel = item.querySelector('.room-type-select');
                            if (sel && sel.value) {
                                // load rooms for this row
                                loadAvailableRooms(idx);
                            }
                        });
                    }
                } catch (e) {
                    console.error('Auto-load rooms error', e);
                }

                // Setup unified voucher event system
                setupVoucherEventSystem();
                updateVoucherAvailability();

                if (window.computeTotals) window.computeTotals();
                if (window.syncAllGuests) window.syncAllGuests();
            });

            function formatCurrency(amount) {
                return new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(amount).replace('₫', 'VNĐ');
            }

            function addRoom() {
                try {
                    // Check if no room type is selected before adding - prevent adding empty room rows
                    const selects = document.querySelectorAll('.room-type-select');
                    let hasEmpty = false;
                    selects.forEach(sel => {
                        if (!sel.value) hasEmpty = true;
                    });
                    if (hasEmpty) {
                        alert('Vui lòng chọn loại phòng cho các dòng trống trước khi thêm dòng mới');
                        return;
                    }

                    const container = document.getElementById('roomTypesContainer');

                    // Build select options from allLoaiPhongs with capacity info
                    let selectOptions = '<option value="">-- Chọn loại phòng --</option>';
                    allLoaiPhongs.forEach(lp => {
                        const price = lp.gia_khuyen_mai ?? lp.gia_co_ban;
                        const formattedPrice = new Intl.NumberFormat('vi-VN').format(price);
                        const sucChua = lp.suc_chua || 2;
                        selectOptions +=
                            `<option value="${lp.id}" data-price="${price}" data-suc-chua="${sucChua}" data-suc-chua-tre-em="${lp.suc_chua_tre_em || 1}" data-suc-chua-em-be="${lp.suc_chua_em_be || 1}">${lp.ten_loai} - ${formattedPrice} VNĐ/đêm (${sucChua} người)</option>`;
                    });

                    const newRoomHtml = `
                <div class="room-item bg-gradient-to-r from-blue-50 to-white border border-blue-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow" data-room-index="${roomIndex}">
                    
                    <!-- Header với tên và nút xóa -->
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-door-open text-white"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 text-lg room-title">Loại phòng mới</h4>
                                <p class="text-sm text-blue-600 font-medium quantity-text">1 phòng</p>
                            </div>
                        </div>
                        <button type="button" onclick="removeRoom(${roomIndex})" 
                            class="text-red-600 hover:text-red-800 text-sm font-medium">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </button>
                    </div>
                    
                    <!-- Form controls -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                        <!-- Chọn loại phòng -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Loại phòng</label>
                            <select name="room_types[${roomIndex}][loai_phong_id]"
                                class="room-type-select w-full px-3 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white"
                            <label class="block text-sm font-medium text-gray-700 mb-2">Loại phòng</label>
                            <select name="room_types[${roomIndex}][loai_phong_id]"
                                class="room-type-select w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                onchange="handleRoomTypeChange(${roomIndex}, this.value)"
                                required>
                                ${selectOptions}
                            </select>
                            <p id="availability_text_${roomIndex}" class="mt-1 text-xs text-green-600 font-medium"></p>
                            <p id="capacity_text_${roomIndex}" class="mt-1 text-xs text-gray-500"></p>
                        </div>
                        
                        <!-- Số lượng -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-2 uppercase tracking-wide text-[11px]">Cấu hình số lượng</label>
                            <div class="flex items-center gap-6">
                                <div class="relative flex items-center bg-blue-50/50 rounded-2xl p-1.5 border border-blue-100 shadow-inner w-fit group">
                                    <button type="button" onclick="decreaseQuantity(${roomIndex})"
                                        class="w-10 h-10 rounded-xl flex items-center justify-center bg-white text-blue-600 shadow-sm hover:bg-red-500 hover:text-white transition-all transform active:scale-95 border border-blue-50">
                                        <i class="fas fa-minus text-xs"></i>
                                    </button>
                                    
                                    <div class="px-5 text-center flex flex-col items-center">
                                        <input type="number" name="room_types[${roomIndex}][so_luong]"
                                            class="room-quantity-input w-12 bg-transparent border-none text-center font-black text-xl text-blue-900 focus:ring-0 p-0 leading-none h-6"
                                            value="1" min="1" max="10"
                                            data-room-index="${roomIndex}"
                                            onchange="updateRoomQuantity(${roomIndex})" required>
                                        <p class="text-[9px] font-bold text-blue-400 uppercase tracking-tighter mt-1">Phòng</p>
                                    </div>

                                    <button type="button" onclick="increaseQuantity(${roomIndex})"
                                        class="w-10 h-10 rounded-xl flex items-center justify-center bg-white text-blue-600 shadow-sm hover:bg-green-500 hover:text-white transition-all transform active:scale-95 border border-blue-50">
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
                                </div>

                                <div class="flex flex-col border-l border-gray-200 pl-6">
                                    <span class="text-[10px] uppercase tracking-wider text-gray-400 font-extrabold">Giá Loại Phòng</span>
                                    <div class="flex items-baseline gap-1">
                                        <span id="room_price_${roomIndex}" class="font-black text-blue-700 text-xl tracking-tight">0</span>
                                        <span class="text-xs font-bold text-blue-400">VNĐ</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Guest Selection for this room group -->
                    <div class="mb-6 p-4 bg-white/50 border border-blue-100 rounded-2xl shadow-sm">
                        <label class="block text-sm font-bold text-blue-900 mb-3 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-users text-blue-500"></i>
                            Số khách cho loại phòng này
                        </label>
                        <div class="grid grid-cols-3 gap-4">
                            <!-- Adults -->
                            <div class="flex flex-col items-center p-3 bg-blue-50/50 rounded-xl border border-blue-100 transition-all hover:bg-blue-50">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="fas fa-user-tie text-blue-500 text-xs"></i>
                                    <span class="text-[10px] font-bold text-blue-700 uppercase">Người lớn</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button type="button" onclick="changeRoomGuestCount('adults', ${roomIndex}, -1)" class="w-7 h-7 flex items-center justify-center rounded-lg bg-white border border-blue-200 text-blue-600 hover:bg-blue-600 hover:text-white transition-all active:scale-90">
                                        <i class="fas fa-minus text-[10px]"></i>
                                    </button>
                                    <input type="number" 
                                        class="room-adults-input w-8 bg-transparent border-none text-center font-bold text-lg text-blue-900 focus:ring-0 p-0" 
                                        value="2" min="0" readonly
                                        onchange="syncGlobalGuestCounts()">
                                    <button type="button" onclick="changeRoomGuestCount('adults', ${roomIndex}, 1)" class="w-7 h-7 flex items-center justify-center rounded-lg bg-white border border-blue-200 text-blue-600 hover:bg-blue-600 hover:text-white transition-all active:scale-90">
                                        <i class="fas fa-plus text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- Children -->
                            <div class="flex flex-col items-center p-3 bg-emerald-50/50 rounded-xl border border-emerald-100 transition-all hover:bg-emerald-50">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="fas fa-child text-emerald-500 text-xs"></i>
                                    <span class="text-[10px] font-bold text-emerald-700 uppercase">Trẻ em</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button type="button" onclick="changeRoomGuestCount('children', ${roomIndex}, -1)" class="w-7 h-7 flex items-center justify-center rounded-lg bg-white border border-emerald-200 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all active:scale-90">
                                        <i class="fas fa-minus text-[10px]"></i>
                                    </button>
                                    <input type="number" 
                                        class="room-children-input w-8 bg-transparent border-none text-center font-bold text-lg text-emerald-900 focus:ring-0 p-0" 
                                        value="0" min="0" readonly
                                        onchange="syncGlobalGuestCounts()">
                                    <button type="button" onclick="changeRoomGuestCount('children', ${roomIndex}, 1)" class="w-7 h-7 flex items-center justify-center rounded-lg bg-white border border-emerald-200 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all active:scale-90">
                                        <i class="fas fa-plus text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- Infants -->
                            <div class="flex flex-col items-center p-3 bg-pink-50/50 rounded-xl border border-pink-100 transition-all hover:bg-pink-50">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="fas fa-baby text-pink-500 text-xs"></i>
                                    <span class="text-[10px] font-bold text-pink-700 uppercase">Em bé</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button type="button" onclick="changeRoomGuestCount('infants', ${roomIndex}, -1)" class="w-7 h-7 flex items-center justify-center rounded-lg bg-white border border-pink-200 text-pink-600 hover:bg-pink-600 hover:text-white transition-all active:scale-90">
                                        <i class="fas fa-minus text-[10px]"></i>
                                    </button>
                                    <input type="number" 
                                        class="room-infants-input w-8 bg-transparent border-none text-center font-bold text-lg text-pink-900 focus:ring-0 p-0" 
                                        value="0" min="0" readonly
                                        onchange="syncGlobalGuestCounts()">
                                    <button type="button" onclick="changeRoomGuestCount('infants', ${roomIndex}, 1)" class="w-7 h-7 flex items-center justify-center rounded-lg bg-white border border-pink-200 text-pink-600 hover:bg-pink-600 hover:text-white transition-all active:scale-90">
                                        <i class="fas fa-plus text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <!-- Chọn phòng cụ thể -->
                    <div id="available_rooms_${roomIndex}" class="mt-4 pt-4 border-t border-blue-100">
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-sm font-medium text-gray-700 flex items-center gap-2">
                                <i class="fas fa-check-square text-blue-500"></i>
                                Chọn phòng cụ thể
                            </label>
                            <button type="button" onclick="loadAvailableRooms(${roomIndex})"
                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                <i class="fas fa-sync-alt mr-1"></i> Tải danh sách phòng
                            </button>
                        </div>
                        <div id="room_list_${roomIndex}" class="checkbox-container flex flex-wrap gap-2 p-3 bg-gray-50 rounded-lg min-h-[50px]">
                            <p class="text-xs text-gray-400 italic">Nhấn "Tải danh sách phòng" để xem các phòng trống</p>
                        </div>
                    </div>
                    
                    <input type="hidden" name="room_types[${roomIndex}][gia_rieng]"
                        id="room_gia_rieng_${roomIndex}" value="0">
                </div>
                `;
                    container.insertAdjacentHTML('beforeend', newRoomHtml);
                    roomIndex++;
                    updateAllRoomAvailability();
                    if (window.computeTotals) window.computeTotals();
                } catch (error) {
                    console.error('Error in addRoom:', error);
                    alert('Lỗi khi thêm loại phòng: ' + error.message);
                }
            }

            function changeGuestCount(type, delta) {
                // When using the global summary buttons, we'll apply the change to the first room item
                // so that the summary (which is a sum calculated by syncGlobalGuestCounts) updates correctly.
                const firstRoom = document.querySelector('.room-item');
                if (firstRoom) {
                    const roomIndex = firstRoom.getAttribute('data-room-index');
                    if (window.changeRoomGuestCount) {
                        window.changeRoomGuestCount(type, roomIndex, delta);
                    }
                } else {
                    // Fallback if no rooms added yet (though unlikely in edit page)
                    const inputId = type === 'adults' ? 'total_adults' : (type === 'children' ? 'total_children' : 'total_infants');
                    const input = document.getElementById(inputId);
                    if (input) {
                        let newValue = parseInt(input.value) + delta;
                        if (newValue < 0) newValue = 0;
                        input.value = newValue;
                        input.dispatchEvent(new Event('change'));
                    }
                }
            }

            function removeRoom(index) {
                const roomItem = document.querySelector(`[data-room-index="${index}"]`);
                if (roomItem) {
                    roomItem.remove();
                    if (window.computeTotals) window.computeTotals();
                }
            }

            function handleRoomTypeChange(index, loaiPhongId) {
                // Check for duplicate room types
                if (loaiPhongId) {
                    const allSelects = document.querySelectorAll('.room-type-select');
                    let duplicateCount = 0;
                    allSelects.forEach(sel => {
                        if (sel.value === loaiPhongId) duplicateCount++;
                    });
                    if (duplicateCount > 1) {
                        alert('Loại phòng này đã được chọn. Vui lòng không chọn trùng lặp.');
                        const sel = document.querySelector(`.room-item[data-room-index="${index}"] .room-type-select`);
                        if (sel) sel.value = '';
                        return;
                    }

                    // updateServiceCards is now managed globally via window.updateServiceCards
                }

                const loaiPhong = allLoaiPhongs.find(lp => lp.id == loaiPhongId);
                if (loaiPhong) {
                    const priceInput = document.getElementById(`room_gia_rieng_${index}`);
                    const priceDisplay = document.getElementById(`room_price_${index}`);
                    const qtyInput = document.querySelector(`input[data-room-index="${index}"]`);
                    const quantityText = document.querySelector(`.room-item[data-room-index="${index}"] .quantity-text`);
                    const roomTitle = document.querySelector(`.room-item[data-room-index="${index}"] .room-title`);
                    const capacityText = document.getElementById(`capacity_text_${index}`);
                    const inputQty = qtyInput ? parseInt(qtyInput.value) || 1 : 1;

                    // Update room title
                    if (roomTitle) {
                        roomTitle.textContent = loaiPhong.ten_loai;
                    }

                    // Update quantity text display
                    if (quantityText) {
                        quantityText.textContent = `${inputQty} phòng`;
                    }

                    // Update capacity info
                    if (capacityText) {
                        const sucChua = loaiPhong.suc_chua || 2;
                        const sucChuaTreEm = loaiPhong.suc_chua_tre_em || 1;
                        const sucChuaEmBe = loaiPhong.suc_chua_em_be || 1;
                        capacityText.innerHTML = `<i class="fas fa-users mr-1"></i> Tối đa: ${sucChua} người lớn, ${sucChuaTreEm} trẻ em, ${sucChuaEmBe} em bé`;
                    }

                    if (priceInput && priceDisplay) {
                        // derive unitPerNight from dataset or option price
                        let unitPerNight = parseFloat(priceInput.dataset.unitPerNight) || 0;
                        const sel = document.querySelector(`.room-item[data-room-index="${index}"] .room-type-select`);
                        if (sel) {
                            const opt = sel.options[sel.selectedIndex];
                            const optPrice = opt ? parseFloat(opt.dataset.price || 0) : 0;
                            if (optPrice && optPrice > 0) {
                                unitPerNight = optPrice;
                            }
                        }

                        const nights = getNights();
                        const subtotal = unitPerNight * nights * inputQty;
                        priceInput.dataset.unitPerNight = unitPerNight;
                        priceInput.value = subtotal;
                        priceDisplay.textContent = formatCurrency(subtotal);

                        if (window.computeTotals) window.computeTotals();
                        // Re-render service room lists so they reflect current selection
                        document.querySelectorAll('[data-service-id]').forEach(card => {
                            const sid = card.getAttribute('data-service-id');
                            if (sid) window.updateServiceRoomLists && window.updateServiceRoomLists(sid);
                        });
                        // Update voucher availability based on new room type selection
                        try {
                            updateVoucherAvailability();
                        } catch (e) {
                            // updateVoucherAvailability not yet defined
                        }
                    }
                }

                // fetch availability for this room type and update UI
                fetch('{{ route('admin.dat_phong.available_count') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            loai_phong_id: loaiPhongId,
                            ngay_nhan: document.getElementById('ngay_nhan')?.value,
                            ngay_tra: document.getElementById('ngay_tra')?.value,
                            booking_id: currentBookingId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        const availabilityText = document.getElementById(`availability_text_${index}`);
                        if (availabilityText) {
                            const availableCount = data.available_count || 0;
                            // determine requested qty for this room row
                            const rowEl = document.querySelector(`[data-room-index="${index}"]`);
                            const qtyInput = rowEl ? rowEl.querySelector('input[data-room-index]') : null;
                            const requested = qtyInput ? (parseInt(qtyInput.value) || 1) : 1;

                            let msg = '';
                            let colorClass = 'text-gray-500';
                            if (availableCount <= 0) {
                                msg = 'Hết phòng';
                                colorClass = 'text-red-600';
                            } else if (availableCount < requested) {
                                msg = `Còn ${availableCount} phòng trống (yêu cầu ${requested})`;
                                colorClass = 'text-red-600';
                            } else {
                                msg = `Còn ${availableCount} phòng trống`;
                                colorClass = 'text-green-600';
                            }

                            availabilityText.textContent = msg;
                            availabilityText.className = `mt-1 text-xs ${colorClass}`;
                        }

                        // If controller returned specific room list, render checkboxes for admin selection
                        if (data.rooms && Array.isArray(data.rooms) && data.rooms.length > 0) {
                            const roomsContainer = document.getElementById(`available_rooms_${index}`);
                            if (roomsContainer) {
                                roomsContainer.classList.remove('hidden');
                                // Find the div inside that will hold the checkboxes
                                const checkboxContainer = roomsContainer.querySelector('.checkbox-container') || roomsContainer.querySelector('div.space-y-2');
                                if (checkboxContainer) {
                                    checkboxContainer.innerHTML = '';
                                    data.rooms.forEach((r, idx) => {
                                        const wrap = document.createElement('div');
                                        wrap.className = 'flex items-center gap-2';

                                        const cb = document.createElement('input');
                                        cb.type = 'checkbox';
                                        cb.name = `rooms[${loaiPhongId}][phong_ids][]`;
                                        cb.value = r.id;
                                        cb.id = `room_checkbox_${loaiPhongId}_${r.id}`;
                                        cb.className = 'available-room-checkbox';
                                        cb.setAttribute('data-room-name', r.so_phong || r.ten_phong || r.id);
                                        cb.setAttribute('data-room-id', r.id);

                                        const lbl = document.createElement('label');
                                        lbl.className = 'text-sm cursor-pointer';
                                        lbl.htmlFor = cb.id;
                                        lbl.textContent = r.so_phong ? ('Phòng ' + r.so_phong) : (r.ten_phong || (
                                            'Phòng ' + r.id));

                                        wrap.appendChild(cb);
                                        wrap.appendChild(lbl);
                                        checkboxContainer.appendChild(wrap);
                                        // attach change listener so services update when admin toggles rooms
                                        cb.addEventListener('change', syncAssignedPhongIdsFromCheckboxes);
                                    });

                                    // Pre-check boxes: prefer previously selected room IDs for this booking, fall back to first N
                                    const want = parseInt(document.querySelector(`input[data-room-index="${index}"]`)
                                        ?.value || 0);
                                    const boxes = checkboxContainer.querySelectorAll('input.available-room-checkbox');
                                    if (boxes && boxes.length > 0) {
                                        // Normalize preselected ids to numbers
                                        const pre = (selectedRoomsByLoaiPhong && selectedRoomsByLoaiPhong[loaiPhongId]) ?
                                            selectedRoomsByLoaiPhong[loaiPhongId].map(x => parseInt(x)) : [];
                                        let checkedCount = 0;
                                        boxes.forEach(cb => {
                                            const id = parseInt(cb.value);
                                            if (pre && pre.includes(id)) {
                                                cb.checked = true;
                                                checkedCount++;
                                            }
                                            // attach listener to newly created boxes
                                            cb.addEventListener('change', syncAssignedPhongIdsFromCheckboxes);
                                        });

                                        // If still need more to reach want, check the first unchecked boxes
                                        for (let i = 0; i < boxes.length && checkedCount < want; i++) {
                                            if (!boxes[i].checked) {
                                                boxes[i].checked = true;
                                                checkedCount++;
                                            }
                                        }

                                        // After pre-checking, sync the assigned list so services reflect the current state
                                        syncAssignedPhongIdsFromCheckboxes();
                                    }
                                }
                            }
                        } else {
                            const roomsContainer = document.getElementById(`available_rooms_${index}`);
                            if (roomsContainer) {
                                roomsContainer.classList.add('hidden');
                                const checkboxContainer = roomsContainer.querySelector('div.space-y-2');
                                if (checkboxContainer) checkboxContainer.innerHTML = '';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }

            // Fetch availability and rooms for a room row
            function updateRoomAvailability(index, loaiPhongId) {
                if (!loaiPhongId) return;
                const ngay_nhan = document.getElementById('ngay_nhan')?.value;
                const ngay_tra = document.getElementById('ngay_tra')?.value;
                const availabilityText = document.getElementById(`availability_text_${index}`);
                if (!ngay_nhan || !ngay_tra) {
                    if (availabilityText) {
                        availabilityText.textContent = 'Vui lòng chọn ngày nhận/trả để kiểm tra phòng trống';
                        availabilityText.className = 'mt-1 text-xs text-gray-500';
                    }
                    return;
                }

                fetch('{{ route('admin.dat_phong.available_count') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            loai_phong_id: loaiPhongId,
                            ngay_nhan: ngay_nhan,
                            ngay_tra: ngay_tra,
                            booking_id: currentBookingId,
                            include_rooms: true
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (availabilityText) {
                            const availableCount = data.available_count || 0;
                            availabilityText.textContent = availableCount > 0 ? `Còn ${availableCount} phòng trống` :
                                'Hết phòng';
                            availabilityText.className =
                                `mt-1 text-xs ${availableCount > 0 ? 'text-green-600' : 'text-red-600'}`;
                        }

                        // Always show the rooms container (so admin can pick or see 'no rooms')
                        const roomsContainer = document.getElementById(`available_rooms_${index}`);
                        if (roomsContainer) {
                            roomsContainer.classList.remove('hidden');
                            const checkboxContainer = roomsContainer.querySelector('.checkbox-container');
                            if (checkboxContainer) {
                                checkboxContainer.innerHTML = '';
                                if (data.rooms && Array.isArray(data.rooms) && data.rooms.length > 0) {
                                    // Render a checkbox per room
                                    data.rooms.forEach((r) => {
                                        const wrap = document.createElement('div');
                                        wrap.className = 'flex items-center gap-2';

                                        const cb = document.createElement('input');
                                        cb.type = 'checkbox';
                                        cb.name = `rooms[${loaiPhongId}][phong_ids][]`;
                                        cb.value = r.id;
                                        cb.className = 'available-room-checkbox';
                                        cb.id = `room_checkbox_${loaiPhongId}_${r.id}`;
                                        cb.setAttribute('data-room-name', r.so_phong || r.ten_phong || r.id);
                                        cb.setAttribute('data-room-id', r.id);

                                        // ensure toggle updates assigned list and service cards
                                        cb.addEventListener('change', syncAssignedPhongIdsFromCheckboxes);

                                        const lbl = document.createElement('label');
                                        lbl.className = 'text-sm cursor-pointer';
                                        lbl.htmlFor = cb.id;
                                        lbl.textContent = r.so_phong ? ('Phòng ' + r.so_phong) : (r.ten_phong || (
                                            'Phòng ' + r.id));

                                        wrap.appendChild(cb);
                                        wrap.appendChild(lbl);
                                        checkboxContainer.appendChild(wrap);
                                    });

                                    // Pre-check previously selected rooms for this loai, else auto-fill first N
                                    const want = parseInt(document.querySelector(`input[data-room-index=\"${index}\"]`)
                                        ?.value || 0);
                                    const boxes = checkboxContainer.querySelectorAll('input.available-room-checkbox');
                                    const pre = (selectedRoomsByLoaiPhong && selectedRoomsByLoaiPhong[loaiPhongId]) ?
                                        selectedRoomsByLoaiPhong[loaiPhongId].map(x => parseInt(x)) : [];
                                    let checkedCount = 0;
                                    boxes.forEach(cb => {
                                        const id = parseInt(cb.value);
                                        if (pre && pre.includes(id)) {
                                            cb.checked = true;
                                            checkedCount++;
                                        }
                                    });
                                    for (let i = 0; i < boxes.length && checkedCount < want; i++) {
                                        if (!boxes[i].checked) {
                                            boxes[i].checked = true;
                                            checkedCount++;
                                        }
                                    }
                                    // attach listeners to all boxes so future toggles update services
                                    boxes.forEach(cb => cb.addEventListener('change', syncAssignedPhongIdsFromCheckboxes));

                                    // After pre-checking, sync the assigned list so services reflect the current state
                                    syncAssignedPhongIdsFromCheckboxes();
                                } else {
                                    // No rooms available: show informative message
                                    const p = document.createElement('div');
                                    p.className = 'text-xs text-gray-500 italic';
                                    p.textContent =
                                        'Không có phòng trống cho loại phòng này trong khoảng thời gian đã chọn.';
                                    checkboxContainer.appendChild(p);
                                }
                            }
                        }

                        // Backward-compatible assignSelect population (if present)
                        if (data.rooms && Array.isArray(data.rooms)) {
                            const assignSelect = document.getElementById('phong_id_assign');
                            if (assignSelect) {
                                const placeholder = document.createElement('option');
                                placeholder.value = '';
                                placeholder.textContent = '-- Chọn phòng --';
                                assignSelect.innerHTML = '';
                                assignSelect.appendChild(placeholder);
                                data.rooms.forEach(r => {
                                    const opt = document.createElement('option');
                                    opt.value = r.id;
                                    opt.textContent = r.so_phong + (r.ten_phong ? (' (' + r.ten_phong + ')') : '');
                                    assignSelect.appendChild(opt);
                                });
                            }
                        }
                    })
                    .catch(e => console.error('availability error', e));
            }

            // Helper to load available rooms for a row by reading the selected loai_phong
            function loadAvailableRooms(index) {
                try {
                    const row = document.querySelector(`.room-item[data-room-index="${index}"]`);
                    if (!row) return;
                    const sel = row.querySelector('.room-type-select');
                    if (!sel || !sel.value) {
                        alert('Vui lòng chọn loại phòng trước khi tải danh sách phòng.');
                        return;
                    }
                    updateRoomAvailability(index, sel.value);
                } catch (e) {
                    console.error('loadAvailableRooms error', e);
                }
            }

            function updateAllRoomAvailability() {
                const roomItems = document.querySelectorAll('.room-item');
                roomItems.forEach(item => {
                    const index = item.getAttribute('data-room-index');
                    const select = item.querySelector('.room-type-select');
                    if (select && select.value) {
                        updateRoomAvailability(index, select.value);
                    }
                });
                // Update voucher availability when room selection changes
                updateVoucherAvailability();
            }

            function increaseQuantity(index) {
                const input = document.querySelector(`input[data-room-index="${index}"]`);
                if (input) {
                    const currentValue = parseInt(input.value) || 1;
                    const maxValue = parseInt(input.getAttribute('max')) || 10;
                    const select = document.querySelector(`.room-item[data-room-index="${index}"] .room-type-select`);
                    const loaiPhongId = select ? select.value : null;

                    if (!loaiPhongId) {
                        alert('Vui lòng chọn loại phòng trước');
                        return;
                    }

                    // Get available count for this room type
                    fetch('{{ route('admin.dat_phong.available_count') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                loai_phong_id: loaiPhongId,
                                ngay_nhan: document.getElementById('ngay_nhan')?.value,
                                ngay_tra: document.getElementById('ngay_tra')?.value,
                                booking_id: currentBookingId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            const availableCount = data.available_count || 0;
                            const newValue = currentValue + 1;

                            if (newValue > availableCount) {
                                alert(`Không thể tăng số lượng! Chỉ còn ${availableCount} phòng trống.`);
                                return;
                            }

                            if (newValue <= maxValue) {
                                input.value = newValue;
                                updateRoomQuantity(index);
                                // refresh availability and room list for this row
                                if (select && select.value) updateRoomAvailability(index, select.value);
                            }
                        })
                        .catch(error => console.error('Error checking availability:', error));
                }
            }

            function decreaseQuantity(index) {
                const input = document.querySelector(`input[data-room-index="${index}"]`);
                if (input) {
                    const currentValue = parseInt(input.value) || 1;
                    if (currentValue > 1) {
                        input.value = currentValue - 1;
                        updateRoomQuantity(index);
                        const select = document.querySelector(`.room-item[data-room-index="${index}"] .room-type-select`);
                        if (select && select.value) updateRoomAvailability(index, select.value);
                    }
                }
            }

            function updateRoomQuantity(index) {
                const input = document.querySelector(`input[data-room-index="${index}"]`);
                const priceInput = document.getElementById(`room_gia_rieng_${index}`);
                const priceDisplay = document.getElementById(`room_price_${index}`);
                const quantityText = document.querySelector(`.room-item[data-room-index="${index}"] .quantity-text`);

                if (input && priceInput && priceDisplay) {
                    const quantity = parseInt(input.value) || 1;

                    // Update quantity text
                    if (quantityText) {
                        quantityText.textContent = `${quantity} phòng`;
                    }

                    const nights = getNights();
                    // use per-night unit stored on dataset (derived on load or set when room type changes)
                    const unitPerNight = parseFloat(priceInput.dataset.unitPerNight) || 0;
                    const subtotal = unitPerNight * nights * quantity;
                    // update hidden stored total for this row
                    priceInput.value = subtotal;
                    priceDisplay.textContent = formatCurrency(subtotal);
                    // recalc global totals
                    if (window.computeTotals) window.computeTotals();
                    // Khi thay đổi số lượng phòng, cập nhật lại selectedRoomsByLoaiPhong
                    // Tìm loai_phong_id
                    const roomItem = input.closest('.room-item');
                    const select = roomItem ? roomItem.querySelector('.room-type-select') : null;
                    const loaiPhongId = select ? select.value : null;
                    // Lấy danh sách phòng đang chọn cho loại phòng này
                    let phongIds = [];
                    // Nếu có checkbox phòng, lấy các phòng đang được tích
                    // (nếu có logic chọn phòng riêng)
                    // Nếu không, chỉ cập nhật số lượng (giả sử phòng sẽ được chọn tự động)
                    if (loaiPhongId) {
                        // Cập nhật số lượng và refresh availability
                        updateRoomAvailability(index, loaiPhongId);
                    }
                }
            }

            function initializeRoomUnitPrices() {
                const nights = getNights();
                document.querySelectorAll('.room-item').forEach(item => {
                    const idx = item.getAttribute('data-room-index');
                    const priceInput = document.getElementById(`room_gia_rieng_${idx}`);
                    const qtyInput = item.querySelector('input[data-room-index]');
                    const priceDisplay = document.getElementById(`room_price_${idx}`);
                    const qty = qtyInput ? parseInt(qtyInput.value) || 1 : 1;
                    if (priceInput) {
                        // Prefer explicit unit set on dataset or selected loai_phong option price (promotional price)
                        let unitPerNight = parseFloat(priceInput.dataset.unitPerNight) || 0;
                        const selectEl = item.querySelector('.room-type-select');
                        if (selectEl) {
                            const opt = selectEl.options[selectEl.selectedIndex];
                            const optPrice = opt ? parseFloat(opt.dataset.price || 0) : 0;
                            if (optPrice && optPrice > 0) unitPerNight = optPrice;
                        }
                        if (!unitPerNight || unitPerNight <= 0) {
                            const storedTotal = parseFloat(priceInput.value) || 0;
                            unitPerNight = (qty > 0 && nights > 0) ? (storedTotal / (qty * nights)) : 0;
                        }
                        priceInput.dataset.unitPerNight = unitPerNight;
                        const subtotal = unitPerNight * nights * qty;
                        if (priceDisplay) priceDisplay.textContent = formatCurrency(subtotal);
                        priceInput.value = subtotal;
                    }
                });
            }

            function getNights() {
                const start = document.getElementById('ngay_nhan')?.value;
                const end = document.getElementById('ngay_tra')?.value;
                if (!start || !end) return 1;
                const s = new Date(start);
                const e = new Date(end);
                const diff = Math.ceil((e - s) / (1000 * 60 * 60 * 24));
                const result = Math.max(1, diff);
                return result;
            }

            // ========================================
            // VOUCHER SYSTEM - UNIFIED EVENT HANDLER
            // ========================================

            function updateVoucherAvailability() {
                const selectedRoomTypes = Array.from(document.querySelectorAll('.room-type-select')).map(s => s.value).filter(
                    v => v);
                const voucherInputs = document.querySelectorAll('.voucher-radio');

                // Get check-in and check-out dates for date range filtering
                const checkinDate = document.getElementById('ngay_nhan')?.value || '';
                const checkoutDate = document.getElementById('ngay_tra')?.value || '';

                // If either date is missing, hide all voucher UI and clear selection
                if (!checkinDate || !checkoutDate) {
                    document.querySelectorAll('.voucher-container').forEach(container => {
                        container.style.display = 'none';
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

                // show voucher containers when dates are set
                document.querySelectorAll('.voucher-container').forEach(container => container.style.display = '');

                // Disable all vouchers first
                voucherInputs.forEach(v => v.disabled = true);

                // Enable/disable vouchers based on room type compatibility AND date range
                voucherInputs.forEach(radio => {
                    const voucherRoomType = radio.dataset.loaiPhong;
                    const vStart = radio.dataset.start || '';
                    const vEnd = radio.dataset.end || '';
                    const container = radio.closest('.voucher-container');

                    // Date check: check-in date must be within [start, end] inclusive
                    let dateOk = true;
                    if (checkinDate && vStart && vEnd) {
                        const checkin = new Date(checkinDate + 'T00:00:00Z');
                        const start = new Date(vStart + 'T00:00:00Z');
                        const end = new Date(vEnd + 'T00:00:00Z');
                        if (checkin < start || checkin > end) dateOk = false;
                    }

                    // If date is not ok, hide the voucher completely
                    if (!dateOk) {
                        if (container) {
                            container.style.display = 'none';
                            radio.checked = false;
                            radio.disabled = true;
                        }
                        return;
                    } else {
                        if (container) container.style.display = '';
                    }

                    // If voucher has no loai_phong_id restriction or matches selected room type
                    const roomOk = (!voucherRoomType || voucherRoomType === 'null' || voucherRoomType === '') || (
                        selectedRoomTypes.length === 0) || selectedRoomTypes.includes(voucherRoomType);

                    // Enable/disable based on room type only (no visible message)
                    radio.disabled = !roomOk;

                    if (container) {
                        const label = container.querySelector('.voucher-label');
                        if (label) label.classList.remove('opacity-50', 'cursor-not-allowed');
                        const overlays = container.querySelectorAll('.voucher-overlay-client, .voucher-overlay-server');
                        overlays.forEach(o => o.remove());
                    }
                });
            }

            // Setup unified voucher event system (DOMContentLoaded)
            function setupVoucherEventSystem() {
                // Attach change handler to radios so 'change' triggers totals update immediately
                document.querySelectorAll('.voucher-radio').forEach(radio => {
                    radio.addEventListener('change', handleVoucherChange);
                });

                // Label click handler (toggle behavior: select/deselect)
                document.querySelectorAll('.voucher-label').forEach(label => {
                    label.addEventListener('click', function(e) {
                        // Prevent native label toggle to avoid double-change events
                        e.preventDefault();
                        e.stopPropagation();
                        const radio = this.closest('.voucher-container').querySelector('.voucher-radio');
                        if (!radio) return;

                        // Don't allow interaction on disabled radios
                        if (radio.disabled) {
                            e.preventDefault();
                            return;
                        }

                        // Toggle selected state and dispatch change so handler runs
                        const newState = !radio.checked;
                        radio.checked = newState;
                        radio.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    });
                });
            }

            // Unified handler for all voucher changes
            function handleVoucherChange() {
                const voucherIdInput = document.getElementById('voucher_id_input');
                const voucherMirror = document.getElementById('voucher_input');

                if (this.checked) {
                    // Voucher selected: update hidden input with voucher ID
                    const container = this.closest('.voucher-container');
                    const voucherId = container?.getAttribute('data-voucher-id') || this.value;
                    if (voucherIdInput) voucherIdInput.value = voucherId;
                    // Mirror the voucher code expected by backend (radio.value is ma_voucher)
                    if (voucherMirror) voucherMirror.value = this.value || voucherId;
                    console.log('Voucher selected:', voucherId);
                } else {
                    // Voucher deselected: clear hidden inputs
                    if (voucherIdInput) voucherIdInput.value = '';
                    if (voucherMirror) voucherMirror.value = '';
                    console.log('Voucher deselected');
                }

                // Recalculate totals immediately
                updateTotalPrice();

                // Also refresh service UI and hidden inputs so services remain consistent
                try {
                    if (typeof updateServiceRoomLists === 'function') {
                        updateServiceRoomLists();
                    }
                } catch (e) {
                    console.error('Error running updateServiceRoomLists after voucher change', e);
                }

                // Recreate hidden inputs for all service cards
                try {
                    document.querySelectorAll('#services_details_container [data-service-id]').forEach(card => {
                        const sid = card.getAttribute('data-service-id');
                        if (sid && typeof syncHiddenEntries === 'function') {
                            try {
                                syncHiddenEntries(sid);
                            } catch (er) {
                                console.error('syncHiddenEntries error', er);
                            }
                        }
                    });
                } catch (e) {
                    console.error('Error syncing hidden service entries after voucher change', e);
                }

                // Re-run service card update (safe no-op if not present)
                if (typeof window.updateServiceCards === 'function') {
                    try {
                        window.updateServiceCards();
                    } catch (e) {
                        console.error('updateServiceCards error', e);
                    }
                }
            }

            // Cập nhật danh sách loại phòng đã chọn trong summary
            function updateSelectedRoomsSummary() {
                const listContainer = document.getElementById('selected_rooms_list');
                if (!listContainer) return;

                const roomItems = document.querySelectorAll('.room-item');
                let html = '';

                if (roomItems.length === 0) {
                    html = '<p class="text-gray-400 italic">Chưa chọn loại phòng nào</p>';
                } else {
                    roomItems.forEach(item => {
                        const select = item.querySelector('.room-type-select');
                        const qtyInput = item.querySelector('.room-quantity-input');
                        
                        if (!select || !select.value) return;

                        const selectedOption = select.options[select.selectedIndex];
                        const roomTypeName = selectedOption ? selectedOption.text.split(' - ')[0] : 'Loại phòng';
                        const quantity = parseInt(qtyInput?.value || 1);
                        
                        // Lấy thông tin sức chứa từ data attributes
                        const sucChua = selectedOption?.dataset?.sucChua || 2;
                        const sucChuaTreEm = selectedOption?.dataset?.sucChuaTreEm || 1;
                        const sucChuaEmBe = selectedOption?.dataset?.sucChuaEmBe || 1;

                        // Lấy loại phòng từ allLoaiPhongs để có thông tin đầy đủ
                        const loaiPhong = allLoaiPhongs.find(lp => lp.id == select.value);
                        const maxAdults = loaiPhong?.suc_chua || sucChua;
                        const maxChildren = loaiPhong?.suc_chua_tre_em || sucChuaTreEm;
                        const maxInfants = loaiPhong?.suc_chua_em_be || sucChuaEmBe;

                        html += `
                            <div class="flex justify-between items-center py-1">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-door-open text-blue-500"></i>
                                    <span class="font-medium text-gray-800">${roomTypeName}</span>
                                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">${quantity} phòng</span>
                                </div>
                                <div class="text-xs text-gray-500">
                                    <i class="fas fa-users mr-1"></i>${maxAdults} người lớn, ${maxChildren} trẻ em, ${maxInfants} em bé
                                </div>
                            </div>
                        `;
                    });
                }

                if (!html) {
                    html = '<p class="text-gray-400 italic">Chưa chọn loại phòng nào</p>';
                }

                listContainer.innerHTML = html;
            }

            // Cập nhật tổng tiền bao gồm cả phòng + voucher + dịch vụ (tính lại từ đầu)
            var updateTotalPrice = function() {
                const ngayNhan = document.getElementById('ngay_nhan').value;
                const ngayTra = document.getElementById('ngay_tra').value;

                // Cập nhật danh sách loại phòng đã chọn
                updateSelectedRoomsSummary();

                if (!ngayNhan || !ngayTra) {
                    document.getElementById('total_price').textContent = formatCurrency(0);
                    return;
                }

                const startDate = new Date(ngayNhan);
                const endDate = new Date(ngayTra);
                const nights = Math.max(1, Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)));

                // 1. Tính tiền phòng theo loại phòng
                let roomTotalByType = {}; // { loaiPhongId: totalPrice }
                let roomTotal = 0;
                document.querySelectorAll('.room-item').forEach(item => {
                    const idx = item.getAttribute('data-room-index');
                    const select = item.querySelector('.room-type-select');
                    const qtyInput = item.querySelector('input[data-room-index]');

                    if (!select || !select.value) return;

                    const roomTypeId = select.value;
                    const quantity = parseInt(qtyInput?.value || 1);

                    // Get price from price input (already calculated as total for this row)
                    const priceInput = document.getElementById(`room_gia_rieng_${idx}`);
                    let price = 0;

                    if (priceInput) {
                        // priceInput stores total price (already multiplied by nights * qty)
                        price = parseFloat(priceInput.value || 0);
                        // But we need per-night price for recalculation, stored in dataset
                        const unitPerNight = parseFloat(priceInput.dataset.unitPerNight || 0);
                        if (unitPerNight > 0) {
                            price = unitPerNight * nights * quantity;
                        }
                    }

                    if (price > 0 && quantity > 0) {
                        roomTotalByType[roomTypeId] = price;
                        roomTotal += price;
                    }
                });

                // 2. Tính discount từ voucher - chỉ áp dụng cho tiền phòng, respecting loai_phong_id filter
                const selectedVoucher = document.querySelector('.voucher-radio:checked');
                let discount = 0;
                let discountedRoomTotal = roomTotal;

                if (selectedVoucher) {
                    const discountValue = parseFloat(selectedVoucher.dataset.value || 0);
                    const voucherLoaiPhongId = selectedVoucher.dataset.loaiPhong;

                    let applicableTotal = 0;
                    if (!voucherLoaiPhongId || voucherLoaiPhongId === 'null' || voucherLoaiPhongId === '') {
                        // Voucher applies to all room types
                        applicableTotal = roomTotal;
                    } else {
                        // Voucher applies only to specific room type
                        applicableTotal = roomTotalByType[voucherLoaiPhongId] || 0;
                    }

                    if (discountValue <= 100) {
                        // Discount is percentage
                        discount = (applicableTotal * discountValue) / 100;
                    } else {
                        // Discount is fixed amount
                        discount = Math.min(discountValue, applicableTotal);
                    }

                    discountedRoomTotal = roomTotal - discount;
                }

                // Compute guest totals and surcharges (mirror create page units method)
                let totalAdults = parseInt(document.getElementById('total_adults')?.value || '0');
                let totalChildren = parseInt(document.getElementById('total_children')?.value || '0');
                let totalInfants = parseInt(document.getElementById('total_infants')?.value || '0');

                const totalSoLuong = Array.from(document.querySelectorAll('.room-item')).reduce((acc, item) => {
                    const qty = parseInt(item.querySelector('.room-quantity-input')?.value || 0) || 0;
                    return acc + qty;
                }, 0);

                const maxAdultsPerRoom = 2;

                let totalExtraFee = 0;
                let totalChildFee = 0;
                let totalInfantFee = 0;

                const unitsTotal = (totalAdults * 2) + totalChildren + totalInfants;
                const baseUnits = totalSoLuong * 4;

                if (unitsTotal > baseUnits && totalSoLuong > 0) {
                    const adultsUnits = totalAdults * 2;
                    const adultsExtraUnits = Math.max(0, adultsUnits - baseUnits);
                    let unitsRemainingBase = Math.max(0, baseUnits - adultsUnits);

                    const childrenAssignedUnits = Math.min(totalChildren, unitsRemainingBase);
                    const childrenExtra = Math.max(0, totalChildren - childrenAssignedUnits);
                    unitsRemainingBase -= childrenAssignedUnits;

                    const infantsAssignedUnits = Math.min(totalInfants, unitsRemainingBase);
                    const infantsExtra = Math.max(0, totalInfants - infantsAssignedUnits);

                    const extraAdultsCount = Math.floor(adultsExtraUnits / 2);
                    const extraChildrenCount = childrenExtra;
                    const extraInfantsCount = infantsExtra;

                    // Tính phụ phí cố định theo số đêm, không áp dụng multiplier hay % giá phòng
                    totalExtraFee = extraAdultsCount * 300000 * nights;
                    totalChildFee = extraChildrenCount * 150000 * nights;
                    totalInfantFee = 0;
                }

                // Update surcharge UI
                const guestSummary = document.getElementById('guest_summary');
                if (totalAdults > 0 || totalChildren > 0 || totalInfants > 0) {
                    guestSummary.classList.remove('hidden');
                    if (totalAdults > 0) {
                        document.getElementById('adults_count').textContent = totalAdults;
                        document.getElementById('total_adults_display').classList.remove('hidden');
                    } else {
                        document.getElementById('total_adults_display').classList.add('hidden');
                    }
                    if (totalChildren > 0) {
                        document.getElementById('children_count').textContent = totalChildren;
                        document.getElementById('total_children_display').classList.remove('hidden');
                    } else {
                        document.getElementById('total_children_display').classList.add('hidden');
                    }
                    if (totalInfants > 0) {
                        document.getElementById('infants_count').textContent = totalInfants;
                        document.getElementById('total_infants_display').classList.remove('hidden');
                    } else {
                        document.getElementById('total_infants_display').classList.add('hidden');
                    }
                } else {
                    guestSummary.classList.add('hidden');
                }

                const surchargeSummary = document.getElementById('surcharge_summary');
                if (totalExtraFee > 0 || totalChildFee > 0 || totalInfantFee > 0) {
                    surchargeSummary.classList.remove('hidden');
                    if (totalExtraFee > 0) {
                        document.getElementById('extra_adult_fee_amount').textContent = formatCurrency(totalExtraFee);
                        document.getElementById('extra_adult_fee').classList.remove('hidden');
                    } else {
                        document.getElementById('extra_adult_fee').classList.add('hidden');
                    }
                    if (totalChildFee > 0) {
                        document.getElementById('child_fee_amount').textContent = formatCurrency(totalChildFee);
                        document.getElementById('child_fee').classList.remove('hidden');
                    } else {
                        document.getElementById('child_fee').classList.add('hidden');
                    }
                    if (totalInfantFee > 0) {
                        document.getElementById('infant_fee_amount').textContent = formatCurrency(totalInfantFee);
                        document.getElementById('infant_fee').classList.remove('hidden');
                    } else {
                        document.getElementById('infant_fee').classList.add('hidden');
                    }
                } else {
                    surchargeSummary.classList.add('hidden');
                }

                // Include surcharges in the room net total
                let roomNetTotal = discountedRoomTotal + totalExtraFee + totalChildFee + totalInfantFee;

                // 3. Tính tiền dịch vụ (không bị ảnh hưởng bởi voucher)
                let totalServicePrice = 0;

                function getTotalBookedRooms() {
                    let total = 0;
                    document.querySelectorAll('.room-item').forEach(item => {
                        const idx = item.getAttribute('data-room-index');
                        const qtyInput = item.querySelector('input[data-room-index]');
                        total += parseInt(qtyInput?.value || 0) || 0;
                    });
                    if (total === 0) {
                        total = Array.isArray(assignedPhongIds) ? assignedPhongIds.length : 0;
                    }
                    return total;
                }

                document.querySelectorAll('#services_details_container .service-card-custom').forEach(card => {
                    const serviceId = card.getAttribute('data-service-id');
                    const priceElem = card.querySelector('.service-price');
                    let price = 0;
                    if (priceElem) {
                        const priceText = priceElem.textContent || '';
                        const match = priceText.match(/[\d,.]+/);
                        if (match) {
                            price = parseFloat(match[0].replace(/[,.]/g, '')) || 0;
                        }
                    }

                    const mode = card.querySelector(`input[name="service_room_mode_${serviceId}"]:checked`)
                        ?.value || 'global';
                    const rows = Array.from(card.querySelectorAll(`.service-date-row`));

                    rows.forEach(r => {
                        const qty = parseInt(r.querySelector('input[type=number]')?.value || 1) || 0;
                        if (qty <= 0) return;

                        if (mode === 'global') {
                            const roomsCount = getTotalBookedRooms();
                            totalServicePrice += price * qty * roomsCount;
                            console.log('service', serviceId, 'global mode, qty=', qty, 'rooms=',
                                roomsCount, 'add=', price * qty * roomsCount);
                        } else {
                            const entryRooms = r.querySelectorAll('.entry-room-checkbox:checked').length ||
                                0;
                            totalServicePrice += price * qty * entryRooms;
                            console.log('service', serviceId, 'specific mode, qty=', qty, 'checked rooms=',
                                entryRooms, 'add=', price * qty * entryRooms);
                        }
                    });
                });

                // 4. Cộng tất cả: (phòng - discount) + dịch vụ
                const finalTotal = roomNetTotal + totalServicePrice;

                // Update UI
                const totalPriceElement = document.getElementById('total_price');
                const tongTienInput = document.getElementById('tong_tien_input');
                const roomPriceElement = document.getElementById('total_room_price');
                const discountAmountElement = document.getElementById('discount_amount');
                const roomAfterElement = document.getElementById('room_after_discount');
                const servicePriceElement = document.getElementById('total_service_price');
                const discountInfoEl = document.getElementById('discount_info');
                const originalPriceEl = document.getElementById('original_price');

                if (roomPriceElement) roomPriceElement.textContent = formatCurrency(roomTotal);

                // Toggle discount info visibility to match create page UX
                if (discount > 0) {
                    if (originalPriceEl) originalPriceEl.textContent = formatCurrency(roomTotal);
                    if (discountAmountElement) discountAmountElement.textContent = '-' + formatCurrency(discount);
                    if (discountInfoEl) discountInfoEl.classList.remove('hidden');
                } else {
                    if (discountAmountElement) discountAmountElement.textContent = formatCurrency(0);
                    if (discountInfoEl) discountInfoEl.classList.add('hidden');
                }

                if (roomAfterElement) roomAfterElement.textContent = formatCurrency(roomNetTotal);
                if (servicePriceElement) servicePriceElement.textContent = formatCurrency(totalServicePrice);
                totalPriceElement.textContent = formatCurrency(finalTotal);
                tongTienInput.value = finalTotal;
            };

            // Small helper: update summaries and trigger re-calculates
            function updateAllRoomGuests(type) {
                try {
                    // The "Total" cards at top are now sums of the per-room inputs.
                    // So we just ensure they are synced and then update price.
                    syncGlobalGuestCounts();
                    
                    // Recompute totals live (price calculation depends on total_adults/children/infants)
                    if (window.computeTotals) window.computeTotals();
                } catch (e) {
                    console.debug('updateAllRoomGuests error', e);
                }
            }

            // Window alias for compatibility
            window.computeTotals = updateTotalPrice;
            window.updateServiceRoomLists = updateServiceRoomLists;

            // Sync per-type hidden room guest inputs before submit so server receives a rooms[] snapshot
            function syncAllGuests() {
                // Remove any previously injected hidden inputs
                document.querySelectorAll('.rooms-hidden-input').forEach(n => n.remove());

                const roomItems = Array.from(document.querySelectorAll('.room-item'));
                if (!roomItems.length) return;

                roomItems.forEach((item, idx) => {
                    const loaiSelect = item.querySelector('.room-type-select');
                    const loaiId = loaiSelect ? loaiSelect.value : null;
                    const soLuong = parseInt(item.querySelector('.room-quantity-input')?.value || '0');
                    if (!loaiId) return;

                    // Read manual inputs instead of calculating
                    const a = parseInt(item.querySelector('.room-adults-input')?.value || '0');
                    const c = parseInt(item.querySelector('.room-children-input')?.value || '0');
                    const f = parseInt(item.querySelector('.room-infants-input')?.value || '0');

                    const form = document.getElementById('bookingForm');
                    if (!form) return;

                    const inputs = [
                        {name: `rooms[${loaiId}][so_nguoi]`, value: a},
                        {name: `rooms[${loaiId}][so_tre_em]`, value: c},
                        {name: `rooms[${loaiId}][so_em_be]`, value: f},
                        {name: `rooms[${loaiId}][so_luong]`, value: soLuong},
                        {name: `rooms[${loaiId}][loai_phong_id]`, value: loaiId}
                    ];

                    inputs.forEach(({name, value}) => {
                        const el = document.createElement('input');
                        el.type = 'hidden';
                        el.name = name;
                        el.value = value;
                        el.className = 'rooms-hidden-input';
                        form.appendChild(el);
                    });
                });

                // Also update the Global Summary Cards to show the combined totals
                syncGlobalGuestCounts();
            }

            // New helper to sum up all per-room guests and update global cards
            function syncGlobalGuestCounts() {
                let totalA = 0, totalC = 0, totalF = 0;
                document.querySelectorAll('.room-item').forEach(item => {
                    totalA += parseInt(item.querySelector('.room-adults-input')?.value || '0');
                    totalC += parseInt(item.querySelector('.room-children-input')?.value || '0');
                    totalF += parseInt(item.querySelector('.room-infants-input')?.value || '0');
                });

                const globalA = document.getElementById('total_adults');
                const globalC = document.getElementById('total_children');
                const globalF = document.getElementById('total_infants');

                if (globalA && parseInt(globalA.value) !== totalA) {
                    globalA.value = totalA;
                    globalA.dispatchEvent(new Event('change'));
                }
                if (globalC && parseInt(globalC.value) !== totalC) {
                    globalC.value = totalC;
                    globalC.dispatchEvent(new Event('change'));
                }
                if (globalF && parseInt(globalF.value) !== totalF) {
                    globalF.value = totalF;
                    globalF.dispatchEvent(new Event('change'));
                }
            }

            // New helper function for increment/decrement in room item
            window.changeRoomGuestCount = function(type, index, delta) {
                const item = document.querySelector(`.room-item[data-room-index="${index}"]`);
                if (!item) return;

                const input = item.querySelector(`.room-${type}-input`);
                if (!input) return;

                let val = parseInt(input.value || '0') + delta;
                if (val < 0) val = 0;
                
                // Capacity validation (rough check against room quantity * capacity)
                const loaiSelect = item.querySelector('.room-type-select');
                const opt = loaiSelect?.options[loaiSelect.selectedIndex];
                const q = parseInt(item.querySelector('.room-quantity-input')?.value || '1');
                
                if (delta > 0 && opt) {
                    // Get capacity from data attrs
                    let max = 2;
                    if (type === 'adults') max = parseInt(opt.getAttribute('data-suc-chua') || '2');
                    else if (type === 'children') max = parseInt(opt.getAttribute('data-suc-chua-tre-em') || '1');
                    else if (type === 'infants') max = parseInt(opt.getAttribute('data-suc-chua-em-be') || '1');
                    
                    if (val > (max * q + 2)) { // Allow some slack or exact? Let's use max * q
                         // console.warn('Exceeds room capacity');
                         // Maybe show a hint instead of hard block
                    }
                }

                input.value = val;
                input.dispatchEvent(new Event('change'));
                
                // Sync to global cards
                syncGlobalGuestCounts();
            }


            // Note: Confirm/Cancel actions use dedicated routes (form/link) like the index page.

            function completeBooking() {
                if (confirm('Bạn có chắc chắn muốn xác nhận trả phòng này không?')) {
                    const form = document.getElementById('bookingForm');
                    const statusInput = document.getElementById('trang_thai_input');
                    if (form && statusInput) {
                        statusInput.value = 'da_tra';
                        form.submit();
                    } else {
                        alert('Có lỗi xảy ra. Vui lòng thử lại!');
                    }
                }
            }
            // Services are fully server-rendered as of this refactor.
            // Per-service checkboxes and date rows are rendered by Blade templates with proper form names.
            // Form submission automatically captures all service_data[serviceId][entries][idx][...] inputs.
            // Minimal client-side behavior: only date validation and room mode radio toggle effects.

            // Enhanced diagnostic: intercept form submit, capture the request, log the response
            document.addEventListener('DOMContentLoaded', function() {
                try {
                    const form = document.getElementById('bookingForm');
                    const submitBtn = form ? form.querySelector('button[type="submit"]') : null;

                    if (form && submitBtn) {
                        // Override form submission to intercept with fetch
                        form.addEventListener('submit', function(ev) {
                            console.log('DEBUG [submit event]: form submit triggered for bookingForm');

                            // Create a temporary message div
                            const statusMsg = document.createElement('div');
                            statusMsg.style.position = 'fixed';
                            statusMsg.style.top = '20px';
                            statusMsg.style.right = '20px';
                            statusMsg.style.zIndex = '9999';
                            statusMsg.style.padding = '12px 16px';
                            statusMsg.style.backgroundColor = '#fbbf24';
                            statusMsg.style.color = '#1f2937';
                            statusMsg.style.borderRadius = '6px';
                            statusMsg.style.fontSize = '14px';
                            statusMsg.style.fontWeight = 'bold';
                            statusMsg.textContent = 'Đang gửi yêu cầu...';
                            document.body.appendChild(statusMsg);

                            // Prevent default form submission
                            ev.preventDefault();

                            // Ensure per-room and per-type guest inputs are in sync before collecting data
                            try {
                                // Distribute and sync hidden inputs
                                updateAllRoomGuests('adults');
                                updateAllRoomGuests('children');
                                updateAllRoomGuests('infants');
                                syncAllGuests && syncAllGuests();

                                // Recompute totals one last time
                                if (window.computeTotals) window.computeTotals();
                            } catch (e) {
                                console.warn('SYNC WARNING before submit:', e);
                            }

                            // Collect form data
                            const formData = new FormData(form);
                            const action = form.getAttribute('action');
                            const method = form.getAttribute('method') || 'POST';

                            // Additional debug: log the important keys explicitly
                            console.log('DEBUG [form data snapshot]:', {
                                action: action,
                                method: method,
                                so_nguoi: formData.get('so_nguoi'),
                                so_tre_em: formData.get('so_tre_em'),
                                so_em_be: formData.get('so_em_be'),
                                rooms_present: Array.from(formData.keys()).some(k => k.startsWith('rooms[')),
                                computed_surcharges: {
                                    adult: document.getElementById('extra_adult_fee_amount')?.textContent || null,
                                    child: document.getElementById('child_fee_amount')?.textContent || null,
                                    infant: document.getElementById('infant_fee_amount')?.textContent || null,
                                }
                            });

                            console.log('DEBUG [form data]: ', {
                                action: action,
                                method: method,
                                formDataEntries: Array.from(formData.entries()).slice(0,
                                    20) // log first 20 entries for better diagnostics
                            });

                            // Send via fetch
                            fetch(action, {
                                    method: method,
                                    body: formData,
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                })
                                .then(async response => {
                                    console.log('DEBUG [fetch response]:', {
                                        status: response.status,
                                        statusText: response.statusText,
                                        headers: Object.fromEntries(response.headers.entries())
                                    });

                                    const text = await response.text();
                                    console.log('DEBUG [fetch response body (first 500 chars)]:', text
                                        .substring(0, 500));

                                    statusMsg.textContent =
                                        `Response: ${response.status} ${response.statusText}`;
                                    statusMsg.style.backgroundColor = response.ok ? '#86efac' :
                                        '#f87171';

                                    // If successful, redirect after a short delay
                                    if (response.ok) {
                                        setTimeout(() => {
                                            window.location.href = response.url || window
                                                .location.href;
                                        }, 1500);
                                    } else {
                                        // If error, show the response for inspection
                                        console.error('DEBUG [error response body]:', text);
                                        setTimeout(() => statusMsg.remove(), 5000);
                                    }
                                })
                                .catch(err => {
                                    console.error('DEBUG [fetch error]:', err);
                                    statusMsg.textContent = `Error: ${err.message}`;
                                    statusMsg.style.backgroundColor = '#f87171';
                                    setTimeout(() => statusMsg.remove(), 5000);
                                });
                        }, false);

                        // Also log click on submit button
                        submitBtn.addEventListener('click', function(ev) {
                            console.log('DEBUG [submit click]:', {
                                disabled: submitBtn.disabled,
                                eventType: ev.type
                            });
                        }, false);
                    }
                } catch (e) {
                    console.error('DEBUG [setup error]:', e);
                }
            });

            // Function to submit confirm form (separate from bookingForm)
            window.submitConfirmForm = function(event) {
                event.preventDefault();

                // Show confirmation dialog
                if (!confirm('Xác nhận đặt phòng #{{ $booking->id }}?')) {
                    return false;
                }

                // If user confirms, submit form using standard form.submit()
                const form = document.getElementById('confirmForm');
                if (form) {
                    form.submit();
                }
            };

            // Client-side validation: ensure admin selected exact rooms per room-type before submit
            function validateBookingForm(event) {
                // Remove previous inline errors
                document.querySelectorAll('.room-error').forEach(el => el.remove());

                const roomItems = document.querySelectorAll('.room-item');
                let valid = true;
                let firstInvalid = null;

                roomItems.forEach(item => {
                    const idx = item.getAttribute('data-room-index');
                    const qtyInput = item.querySelector('input[data-room-index]');
                    const required = qtyInput ? (parseInt(qtyInput.value) || 1) : 1;

                    const checkboxContainer = item.querySelector(`#room_list_${idx}`) || item.querySelector(
                        '.space-y-2');
                    const checkboxes = item.querySelectorAll('input.available-room-checkbox');

                    if (!checkboxes || checkboxes.length === 0) {
                        // If there are no checkboxes rendered, require admin to load/select rooms
                        valid = false;
                        const err = document.createElement('div');
                        err.className = 'room-error text-sm text-red-600 mt-2';
                        err.textContent = `Vui lòng tải danh sách phòng và chọn ${required} phòng cho loại này.`;
                        (checkboxContainer || item).appendChild(err);
                        if (!firstInvalid) firstInvalid = item;
                        return;
                    }

                    const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
                    if (checkedCount !== required) {
                        valid = false;
                        const err = document.createElement('div');
                        err.className = 'room-error text-sm text-red-600 mt-2';
                        err.textContent = `Vui lòng chọn đúng ${required} phòng (đã chọn ${checkedCount}).`;
                        (checkboxContainer || item).appendChild(err);
                        if (!firstInvalid) firstInvalid = item;
                    }
                });

                if (!valid) {
                    event.preventDefault();
                    if (firstInvalid) firstInvalid.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    return false;
                }
                return true;
            }

            // Attach validator and submit-time sync to booking form submit
            try {
                const bookingForm = document.getElementById('bookingForm');
                if (bookingForm) {
                    // Ensure per-room hidden guest inputs reflect top-level totals and preview
                    bookingForm.addEventListener('submit', function (ev) {
                        try {
                            syncAllGuests && syncAllGuests();
                        } catch (e) {
                            console.error('syncAllGuests error', e);
                        }
                        try {
                            if (typeof updateTotalPrice === 'function') updateTotalPrice();
                        } catch (e) {
                            console.error('updateTotalPrice error', e);
                        }
                    }, { capture: true });

                    // Validation runs after sync so it can check rooms.* inputs if necessary
                    bookingForm.addEventListener('submit', validateBookingForm, {
                        capture: true
                    });
                }
            } catch (e) {
                console.error('Failed to attach booking form validator', e);
            }
        </script>
    @endpush
@endsection
