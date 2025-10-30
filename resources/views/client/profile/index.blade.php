@extends('layouts.client')

@section('title', 'Thông tin cá nhân')

@section('client_content')

    <div class="relative w-full bg-cover bg-center bg-no-repeat"
        style="background-image: url('{{ asset('img/hero/thongtin.jpg') }}');">

        {{-- Overlay tối --}}
        <div class="absolute inset-0 bg-black bg-opacity-40"></div>

        <div class="relative py-28 px-4 text-center text-white">
            <nav class="text-sm text-gray-200 mb-4">
                <a href="{{ route('client.dashboard') }}" class="hover:text-[#D4AF37] transition-colors">Trang chủ</a>
                /
                <span class="text-[#FFD700] font-semibold">Thông tin cá nhân</span>
            </nav>

            <h1 class="text-5xl md:text-6xl font-bold mb-6">Thông Tin Cá Nhân</h1>

            <p class="text-lg md:text-xl text-gray-100 leading-relaxed max-w-3xl mx-auto">
                Quản lý thông tin và lịch sử đặt phòng của bạn
            </p>
        </div>
    </div>


    <section class="bg-gray-50 py-16 w-full">
        <div class="max-w-7xl mx-auto px-4">

            @if (session('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-8">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                        <p class="text-green-800 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-8">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
                        <p class="text-red-800 font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <div class="grid lg:grid-cols-3 gap-8">

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden sticky top-4">
                        <!-- Profile Header -->
                        <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-6 text-white text-center">
                            <div class="relative w-24 h-24 mx-auto mb-4">
                                @if ($user->img)
                                    <img src="{{ asset($user->img) }}" alt="{{ $user->ho_ten }}"
                                        class="w-24 h-24 rounded-full object-cover border-4 border-white/30">
                                @else
                                    <div
                                        class="w-24 h-24 rounded-full bg-white/20 flex items-center justify-center text-4xl font-bold border-4 border-white/30">
                                        {{ strtoupper(substr($user->ho_ten ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                                <label for="avatar-upload"
                                    class="absolute bottom-0 right-0 bg-white text-blue-600 rounded-full p-2 cursor-pointer hover:bg-blue-50 transition-colors shadow-lg">
                                    <i class="fas fa-camera text-sm"></i>
                                    <input type="file" id="avatar-upload" class="hidden" accept="image/*"
                                        onchange="uploadAvatar(event)">
                                </label>
                            </div>
                            <h3 class="text-xl font-bold">{{ $user->ho_ten ?? 'Người dùng' }}</h3>
                            <p class="text-blue-100 text-sm mt-1">{{ $user->email }}</p>
                        </div>

                        <!-- Navigation Menu -->
                        <nav class="p-4">
                            <a href="#thong-tin"
                                class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors mb-2">
                                <i class="fas fa-user mr-3 w-5"></i>
                                <span class="font-medium">Thông tin cá nhân</span>
                            </a>
                            <a href="#mat-khau"
                                class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors mb-2">
                                <i class="fas fa-lock mr-3 w-5"></i>
                                <span class="font-medium">Đổi mật khẩu</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                    <i class="fas fa-sign-out-alt mr-3 w-5"></i>
                                    <span class="font-medium">Đăng xuất</span>
                                </button>
                            </form>
                        </nav>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">

                    <!-- Thông tin cá nhân -->
                    <div id="thong-tin" class="bg-white rounded-lg shadow-md p-8">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-900">Thông Tin Cá Nhân</h2>
                            <button type="button" onclick="toggleEdit()"
                                class="text-blue-600 hover:text-blue-700 font-medium flex items-center">
                                <i class="fas fa-edit mr-2"></i>
                                Chỉnh sửa
                            </button>
                        </div>

                        @if ($errors->any())
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                                <div class="flex items-start">
                                    <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3 mt-0.5"></i>
                                    <div>
                                        <h3 class="text-red-800 font-semibold mb-2">Có lỗi xảy ra:</h3>
                                        <ul class="list-disc list-inside text-red-700 text-sm">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('profile.update') }}" id="profileForm">
                            @csrf
                            @method('PATCH')
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Họ và tên</label>
                                    <input type="text" name="ho_ten" value="{{ old('ho_ten', $user->ho_ten) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        disabled id="ho_ten">
                                    @error('ho_ten')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        disabled id="email">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại</label>
                                    <input type="text" name="sdt" value="{{ old('sdt', $user->sdt) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        disabled id="sdt">
                                    @error('sdt')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">CCCD</label>
                                    <input type="text" name="cccd" value="{{ old('cccd', $user->cccd) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        disabled id="cccd">
                                    @error('cccd')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Địa chỉ</label>
                                    <textarea name="dia_chi" rows="3"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        disabled id="dia_chi">{{ old('dia_chi', $user->dia_chi) }}</textarea>
                                    @error('dia_chi')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-6 hidden" id="saveButtons">
                                <div class="flex space-x-4">
                                    <button type="submit"
                                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                        <i class="fas fa-save mr-2"></i>
                                        Lưu thay đổi
                                    </button>
                                    <button type="button" onclick="cancelEdit()"
                                        class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                                        Hủy
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Hidden form for avatar upload -->
                        <form id="avatarForm" method="POST" action="{{ route('profile.avatar.update') }}"
                            enctype="multipart/form-data" class="hidden">
                            @csrf
                            @method('POST')
                            <input type="file" name="avatar" id="avatar-file">
                        </form>
                    </div>

                    <script>
                        function toggleEdit() {
                            document.querySelectorAll('#profileForm input, #profileForm textarea').forEach(el => el.disabled = false);
                            document.getElementById('saveButtons').classList.remove('hidden');
                        }

                        function cancelEdit() {
                            document.querySelectorAll('#profileForm input, #profileForm textarea').forEach(el => el.disabled = true);
                            document.getElementById('saveButtons').classList.add('hidden');
                        }

                        // ✅ Nếu có lỗi validate, tự bật chế độ chỉnh sửa luôn
                        @if ($errors->any())
                            window.addEventListener('DOMContentLoaded', () => {
                                toggleEdit();
                            });
                        @endif
                    </script>


                    <!-- Lịch sử đặt phòng -->
                    <div id="lich-su" class="bg-white rounded-lg shadow-md p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Lịch Sử Đặt Phòng</h2>

                        @if ($bookings->count() > 0)
                            <div class="space-y-6">
                                @foreach ($bookings as $booking)
                                    <div class="booking-item bg-gradient-to-r from-white to-gray-50 border-l-4 
                                @if ($booking->trang_thai == 'da_xac_nhan') border-green-500
                                @elseif($booking->trang_thai == 'da_huy') border-red-500
                                @elseif($booking->trang_thai == 'da_tra') border-blue-500
                                @else border-yellow-500 @endif
                                rounded-lg shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden"
                                        data-booking-id="{{ $booking->id }}"
                                        data-room-name="{{ $booking->phong->ten_phong ?? 'N/A' }}"
                                        data-room-type="{{ $booking->phong->loaiPhong->ten_loai ?? 'N/A' }}"
                                        data-room-price="{{ number_format($booking->phong->gia ?? 0, 0, ',', '.') }}"
                                        data-room-desc="{{ strip_tags($booking->phong->mo_ta ?? '') }}"
                                        data-room-img="{{ asset($booking->phong->img ?? 'img/room/room-1.jpg') }}"
                                        data-checkin="{{ \Carbon\Carbon::parse($booking->ngay_nhan)->format('d/m/Y') }}"
                                        data-checkout="{{ \Carbon\Carbon::parse($booking->ngay_tra)->format('d/m/Y') }}"
                                        data-booking-date="{{ \Carbon\Carbon::parse($booking->ngay_dat)->format('d/m/Y H:i') }}"
                                        data-guests="{{ $booking->so_nguoi }}"
                                        data-total="{{ number_format($booking->tong_tien, 0, ',', '.') }}"
                                        data-status="{{ $booking->trang_thai }}"
                                        data-cancel-reason="{{ $booking->ly_do_huy ?? '' }}"
                                        data-cancel-date="{{ $booking->ngay_huy ? \Carbon\Carbon::parse($booking->ngay_huy)->format('d/m/Y H:i') : '' }}">

                                        <!-- Header -->
                                        <div class="p-6 border-b border-gray-100">
                                            <div
                                                class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-3 mb-2">
                                                        <div
                                                            class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                                            <i class="fas fa-hotel text-blue-600 text-xl"></i>
                                                        </div>
                                                        <div>
                                                            <h3 class="text-xl font-bold text-gray-900">
                                                                {{ $booking->phong->ten_phong ?? 'N/A' }}</h3>
                                                            <p class="text-sm text-gray-600">
                                                                <i class="fas fa-bed-alt mr-1"></i>
                                                                {{ $booking->phong->loaiPhong->ten_loai ?? 'N/A' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-4">
                                                    <div class="text-right">
                                                        <p class="text-sm text-gray-500 mb-1">Tổng tiền</p>
                                                        <p class="text-2xl font-bold text-blue-600">
                                                            {{ number_format($booking->tong_tien, 0, ',', '.') }} đ
                                                        </p>
                                                    </div>
                                                    <span
                                                        class="px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap
                                                @if ($booking->trang_thai == 'da_xac_nhan') bg-green-100 text-green-800
                                                @elseif($booking->trang_thai == 'da_huy') bg-red-100 text-red-800
                                                @elseif($booking->trang_thai == 'da_tra') bg-blue-100 text-blue-800
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                        @if ($booking->trang_thai == 'cho_xac_nhan')
                                                            <i class="fas fa-clock mr-1"></i> Chờ xác nhận
                                                        @elseif($booking->trang_thai == 'da_xac_nhan')
                                                            <i class="fas fa-check-circle mr-1"></i> Đã xác nhận
                                                        @elseif($booking->trang_thai == 'da_huy')
                                                            <i class="fas fa-times-circle mr-1"></i> Đã hủy
                                                        @elseif($booking->trang_thai == 'da_tra')
                                                            <i class="fas fa-check-double mr-1"></i> Đã trả phòng
                                                        @else
                                                            {{ $booking->trang_thai }}
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Body -->
                                        <div class="p-6">
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                                <!-- Check-in -->
                                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                    <div class="flex items-center gap-3">
                                                        <div
                                                            class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                            <i class="fas fa-calendar-check text-green-600"></i>
                                                        </div>
                                                        <div>
                                                            <p class="text-xs text-gray-500 mb-1">Nhận phòng</p>
                                                            <p class="text-base font-bold text-gray-900">
                                                                {{ \Carbon\Carbon::parse($booking->ngay_nhan)->format('d/m/Y') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Check-out -->
                                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                    <div class="flex items-center gap-3">
                                                        <div
                                                            class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                                            <i class="fas fa-calendar-times text-red-600"></i>
                                                        </div>
                                                        <div>
                                                            <p class="text-xs text-gray-500 mb-1">Trả phòng</p>
                                                            <p class="text-base font-bold text-gray-900">
                                                                {{ \Carbon\Carbon::parse($booking->ngay_tra)->format('d/m/Y') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Booking date -->
                                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                    <div class="flex items-center gap-3">
                                                        <div
                                                            class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                            <i class="fas fa-receipt text-blue-600"></i>
                                                        </div>
                                                        <div>
                                                            <p class="text-xs text-gray-500 mb-1">Ngày đặt</p>
                                                            <p class="text-base font-bold text-gray-900">
                                                                {{ \Carbon\Carbon::parse($booking->ngay_dat)->format('d/m/Y') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @if ($booking->ly_do_huy)
                                                <!-- Lý do hủy -->
                                                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                                                    <div class="flex items-start gap-3">
                                                        <i class="fas fa-info-circle text-red-500 mt-1"></i>
                                                        <div>
                                                            <p class="text-sm font-semibold text-red-800 mb-1">Lý do hủy
                                                                phòng:</p>
                                                            <p class="text-sm text-red-700">{{ $booking->ly_do_huy }}</p>
                                                            @if ($booking->ngay_huy)
                                                                <p class="text-xs text-red-600 mt-1">Ngày hủy:
                                                                    {{ \Carbon\Carbon::parse($booking->ngay_huy)->format('d/m/Y H:i') }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Action buttons -->
                                            <div class="flex justify-end gap-3">
                                                <!-- Nút xem chi tiết (luôn hiển thị) -->
                                                <button onclick="showBookingDetail({{ $booking->id }})"
                                                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors font-medium flex items-center gap-2">
                                                    <i class="fas fa-eye"></i>
                                                    Xem chi tiết
                                                </button>

                                                <!-- Nút hủy phòng (chỉ cho phòng chờ xác nhận) -->
                                                @if ($booking->trang_thai === 'cho_xac_nhan')
                                                    <button onclick="showCancelModal({{ $booking->id }})"
                                                        class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg transition-colors font-medium flex items-center gap-2">
                                                        <i class="fas fa-times-circle"></i>
                                                        Hủy đặt phòng
                                                    </button>
                                                @endif
                                            </div>

                                            @if ($booking->trang_thai === 'da_xac_nhan')
                                                <div
                                                    class="mt-3 bg-blue-50 border border-blue-200 rounded-lg px-4 py-2 text-blue-700 text-sm">
                                                    <i class="fas fa-info-circle mr-2"></i>
                                                    Đặt phòng đã được xác nhận, không thể hủy
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if ($bookings->hasPages())
                                <div class="mt-8">
                                    {{ $bookings->links('pagination.custom') }}
                                </div>
                            @endif
                        @else
                            <div class="text-center py-12">
                                <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                                <p class="text-gray-500 text-lg">Bạn chưa có lịch sử đặt phòng nào</p>
                                <a href="{{ route('client.phong') }}"
                                    class="inline-block mt-4 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    Khám phá phòng ngay
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Đổi mật khẩu -->
                    <div id="mat-khau" class="bg-white rounded-lg shadow-md p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Đổi Mật Khẩu</h2>

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu hiện tại</label>
                                    <input type="password" name="current_password"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    @error('current_password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu mới</label>
                                    <input type="password" name="password"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Xác nhận mật khẩu
                                        mới</label>
                                    <input type="password" name="password_confirmation"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <div class="mt-6">
                                <button type="submit"
                                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                    <i class="fas fa-key mr-2"></i>
                                    Đổi mật khẩu
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- Modal chi tiết đặt phòng -->
    <div id="detailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50"
        onclick="closeDetailModalOnOutsideClick(event)">
        <div class="bg-white rounded-lg shadow-2xl max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto"
            onclick="event.stopPropagation()">
            <div
                class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4 rounded-t-lg flex items-center justify-between sticky top-0 z-10">
                <h3 class="text-xl font-bold flex items-center gap-2">
                    <i class="fas fa-info-circle"></i>
                    Chi tiết đặt phòng
                </h3>
                <button onclick="closeDetailModal()" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <div id="detailContent" class="p-6">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>

    <!-- Modal hủy đặt phòng -->
    <div id="cancelModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50"
        onclick="closeCancelModalOnOutsideClick(event)">
        <div class="bg-white rounded-lg shadow-2xl max-w-md w-full mx-4" onclick="event.stopPropagation()">
            <div class="bg-red-500 text-white px-6 py-4 rounded-t-lg flex items-center justify-between">
                <h3 class="text-xl font-bold flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle"></i>
                    Hủy đặt phòng
                </h3>
                <button onclick="closeCancelModal()" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <form id="cancelForm" method="POST" action="">
                @csrf
                @method('POST')

                <div class="p-6">
                    <p class="text-gray-700 mb-4">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        Bạn có chắc chắn muốn hủy đặt phòng này không? Vui lòng nhập lý do hủy phòng.
                    </p>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Lý do hủy phòng <span class="text-red-500">*</span>
                        </label>
                        <textarea name="ly_do_huy" id="ly_do_huy" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none"
                            placeholder="Vui lòng nhập lý do hủy đặt phòng..." required></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-lightbulb text-yellow-500 mr-1"></i>
                            Ví dụ: Thay đổi kế hoạch, tìm được khách sạn khác, v.v.
                        </p>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-end gap-3">
                    <button type="button" onclick="closeCancelModal()"
                        class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Quay lại
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors font-medium">
                        <i class="fas fa-check mr-2"></i>
                        Xác nhận hủy
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleEdit() {
            const fields = ['ho_ten', 'email', 'sdt', 'cccd', 'dia_chi'];
            fields.forEach(field => {
                document.getElementById(field).disabled = false;
                document.getElementById(field).classList.add('bg-blue-50');
            });
            document.getElementById('saveButtons').classList.remove('hidden');
        }

        function cancelEdit() {
            const fields = ['ho_ten', 'email', 'sdt', 'cccd', 'dia_chi'];
            fields.forEach(field => {
                document.getElementById(field).disabled = true;
                document.getElementById(field).classList.remove('bg-blue-50');
            });
            document.getElementById('saveButtons').classList.add('hidden');
            document.getElementById('profileForm').reset();
        }

        // Upload avatar
        function uploadAvatar(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file type
            if (!file.type.match('image.*')) {
                alert('Vui lòng chọn file ảnh!');
                return;
            }

            // Validate file size (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('Kích thước ảnh không được vượt quá 2MB!');
                return;
            }

            if (confirm('Bạn có muốn cập nhật ảnh đại diện?')) {
                // Create FormData
                const formData = new FormData();
                formData.append('avatar', file);
                formData.append('_token', '{{ csrf_token() }}');

                // Show loading
                const avatarUploadLabel = document.querySelector('label[for="avatar-upload"]');
                avatarUploadLabel.innerHTML = '<i class="fas fa-spinner fa-spin text-sm"></i>';

                // Upload via AJAX
                fetch('{{ route('profile.avatar.update') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload page to show new avatar
                            location.reload();
                        } else {
                            alert(data.message || 'Có lỗi xảy ra!');
                            avatarUploadLabel.innerHTML = '<i class="fas fa-camera text-sm"></i>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi upload ảnh!');
                        avatarUploadLabel.innerHTML = '<i class="fas fa-camera text-sm"></i>';
                    });
            }
        }

        // Smooth scroll to section
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Cancel booking modal functions
        function showCancelModal(bookingId) {
            const modal = document.getElementById('cancelModal');
            const form = document.getElementById('cancelForm');
            const textarea = document.getElementById('ly_do_huy');

            // Set form action
            form.action = `/profile/booking/${bookingId}/cancel`;

            // Clear textarea
            textarea.value = '';

            // Show modal
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Focus on textarea
            setTimeout(() => {
                textarea.focus();
            }, 100);

            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        }

        function closeCancelModal() {
            const modal = document.getElementById('cancelModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            // Restore body scroll
            document.body.style.overflow = '';
        }

        function closeCancelModalOnOutsideClick(event) {
            if (event.target.id === 'cancelModal') {
                closeCancelModal();
            }
        }

        // Close modal on ESC key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeCancelModal();
                closeDetailModal();
            }
        });

        // Booking detail modal functions
        function showBookingDetail(bookingId) {
            const bookingItem = document.querySelector(`.booking-item[data-booking-id="${bookingId}"]`);
            if (!bookingItem) return;

            const data = bookingItem.dataset;
            const statusMap = {
                'cho_xac_nhan': {
                    text: 'Chờ xác nhận',
                    class: 'bg-yellow-100 text-yellow-800',
                    icon: 'fa-clock'
                },
                'da_xac_nhan': {
                    text: 'Đã xác nhận',
                    class: 'bg-green-100 text-green-800',
                    icon: 'fa-check-circle'
                },
                'da_huy': {
                    text: 'Đã hủy',
                    class: 'bg-red-100 text-red-800',
                    icon: 'fa-times-circle'
                },
                'da_tra': {
                    text: 'Đã trả phòng',
                    class: 'bg-blue-100 text-blue-800',
                    icon: 'fa-check-double'
                }
            };

            const status = statusMap[data.status] || statusMap['cho_xac_nhan'];

            let content = `
        <div class="space-y-6">
            <!-- Hình ảnh phòng -->
            <div class="rounded-lg overflow-hidden">
                <img src="${data.roomImg}" alt="${data.roomName}" class="w-full h-64 object-cover">
            </div>
            
            <!-- Thông tin phòng -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">${data.roomName}</h3>
                <p class="text-gray-600 mb-4"><i class="fas fa-bed mr-2"></i>${data.roomType}</p>
                ${data.roomDesc ? `<p class="text-gray-700 text-sm leading-relaxed">${data.roomDesc}</p>` : ''}
            </div>
            
            <!-- Trạng thái -->
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                <span class="text-gray-700 font-medium">Trạng thái:</span>
                <span class="px-4 py-2 rounded-full text-sm font-semibold ${status.class}">
                    <i class="fas ${status.icon} mr-1"></i> ${status.text}
                </span>
            </div>
            
            <!-- Chi tiết đặt phòng -->
            <div class="grid md:grid-cols-2 gap-4">
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border-l-4 border-green-500">
                    <p class="text-xs text-green-700 mb-1">Ngày nhận phòng</p>
                    <p class="text-lg font-bold text-green-900"><i class="fas fa-calendar-check mr-2"></i>${data.checkin}</p>
                </div>
                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-4 border-l-4 border-red-500">
                    <p class="text-xs text-red-700 mb-1">Ngày trả phòng</p>
                    <p class="text-lg font-bold text-red-900"><i class="fas fa-calendar-times mr-2"></i>${data.checkout}</p>
                </div>
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border-l-4 border-blue-500">
                    <p class="text-xs text-blue-700 mb-1">Ngày đặt</p>
                    <p class="text-lg font-bold text-blue-900"><i class="fas fa-receipt mr-2"></i>${data.bookingDate}</p>
                </div>
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border-l-4 border-purple-500">
                    <p class="text-xs text-purple-700 mb-1">Số người</p>
                    <p class="text-lg font-bold text-purple-900"><i class="fas fa-users mr-2"></i>${data.guests} người</p>
                </div>
            </div>
            
            ${data.cancelReason ? `
                            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                                <p class="text-sm font-semibold text-red-800 mb-2"><i class="fas fa-info-circle mr-2"></i>Lý do hủy:</p>
                                <p class="text-sm text-red-700">${data.cancelReason}</p>
                                ${data.cancelDate ? `<p class="text-xs text-red-600 mt-2">Ngày hủy: ${data.cancelDate}</p>` : ''}
                            </div>
                            ` : ''}
            
            <!-- Tổng tiền -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg p-6 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90 mb-1">Mã đặt phòng: #${data.bookingId}</p>
                        <p class="text-lg font-semibold">Giá phòng: ${data.roomPrice} đ/đêm</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm opacity-90 mb-1">Tổng thanh toán</p>
                        <p class="text-3xl font-bold">${data.total} đ</p>
                    </div>
                </div>
            </div>
        </div>
    `;

            document.getElementById('detailContent').innerHTML = content;
            document.getElementById('detailModal').classList.remove('hidden');
            document.getElementById('detailModal').classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeDetailModal() {
            document.getElementById('detailModal').classList.add('hidden');
            document.getElementById('detailModal').classList.remove('flex');
            document.body.style.overflow = '';
        }

        function closeDetailModalOnOutsideClick(event) {
            if (event.target.id === 'detailModal') {
                closeDetailModal();
            }
        }
    </script>

@endsection
