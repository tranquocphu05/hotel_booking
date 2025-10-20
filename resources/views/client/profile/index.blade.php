@extends('layouts.client')

@section('title', 'Thông tin cá nhân')

@section('client_content')

<div class="bg-white py-16 w-full">
    <div class="w-full px-4 text-center">
        <nav class="text-sm text-gray-500 mb-8">
            <a href="{{ route('client.dashboard') }}" class="hover:text-gray-700 transition-colors">Trang chủ</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900">Thông tin cá nhân</span>
        </nav>
        
        <h1 class="text-5xl md:text-6xl font-bold text-black mb-4">Thông Tin Cá Nhân</h1>
        <p class="text-xl text-gray-600">Quản lý thông tin và lịch sử đặt phòng của bạn</p>
    </div>
</div>

<section class="bg-gray-50 py-16 w-full">
    <div class="max-w-7xl mx-auto px-4">
        
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-8">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                    <p class="text-green-800 font-medium">{{ session('success') }}</p>
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
                            @if($user->img)
                                <img src="{{ asset($user->img) }}" alt="{{ $user->ho_ten }}" class="w-24 h-24 rounded-full object-cover border-4 border-white/30">
                            @else
                                <div class="w-24 h-24 rounded-full bg-white/20 flex items-center justify-center text-4xl font-bold border-4 border-white/30">
                                    {{ strtoupper(substr($user->ho_ten ?? 'U', 0, 1)) }}
                                </div>
                            @endif
                            <label for="avatar-upload" class="absolute bottom-0 right-0 bg-white text-blue-600 rounded-full p-2 cursor-pointer hover:bg-blue-50 transition-colors shadow-lg">
                                <i class="fas fa-camera text-sm"></i>
                                <input type="file" id="avatar-upload" class="hidden" accept="image/*" onchange="uploadAvatar(event)">
                            </label>
                        </div>
                        <h3 class="text-xl font-bold">{{ $user->ho_ten ?? 'Người dùng' }}</h3>
                        <p class="text-blue-100 text-sm mt-1">{{ $user->email }}</p>
                    </div>
                    
                    <!-- Navigation Menu -->
                    <nav class="p-4">
                        <a href="#thong-tin" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors mb-2">
                            <i class="fas fa-user mr-3 w-5"></i>
                            <span class="font-medium">Thông tin cá nhân</span>
                        </a>
                        <a href="#mat-khau" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors mb-2">
                            <i class="fas fa-lock mr-3 w-5"></i>
                            <span class="font-medium">Đổi mật khẩu</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
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
                        <button type="button" onclick="toggleEdit()" class="text-blue-600 hover:text-blue-700 font-medium flex items-center">
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
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                    <i class="fas fa-save mr-2"></i>
                                    Lưu thay đổi
                                </button>
                                <button type="button" onclick="cancelEdit()" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                                    Hủy
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Hidden form for avatar upload -->
                    <form id="avatarForm" method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data" class="hidden">
                        @csrf
                        @method('POST')
                        <input type="file" name="avatar" id="avatar-file">
                    </form>
                </div>

                <!-- Lịch sử đặt phòng -->
                <div id="lich-su" class="bg-white rounded-lg shadow-md p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Lịch Sử Đặt Phòng</h2>
                    
                    @if($bookings->count() > 0)
                        <div class="space-y-6">
                            @foreach($bookings as $booking)
                            <div class="bg-gradient-to-r from-white to-gray-50 border-l-4 
                                @if($booking->trang_thai == 'da_xac_nhan') border-green-500
                                @elseif($booking->trang_thai == 'da_huy') border-red-500
                                @elseif($booking->trang_thai == 'da_tra') border-blue-500
                                @else border-yellow-500
                                @endif
                                rounded-lg shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden">
                                
                                <!-- Header -->
                                <div class="p-6 border-b border-gray-100">
                                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-hotel text-blue-600 text-xl"></i>
                                                </div>
                                                <div>
                                                    <h3 class="text-xl font-bold text-gray-900">{{ $booking->phong->ten_phong ?? 'N/A' }}</h3>
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
                                            <span class="px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap
                                                @if($booking->trang_thai == 'da_xac_nhan') bg-green-100 text-green-800
                                                @elseif($booking->trang_thai == 'da_huy') bg-red-100 text-red-800
                                                @elseif($booking->trang_thai == 'da_tra') bg-blue-100 text-blue-800
                                                @else bg-yellow-100 text-yellow-800
                                                @endif">
                                                @if($booking->trang_thai == 'cho_xac_nhan') 
                                                    <i class="fas fa-clock mr-1"></i> Chờ xác nhận
                                                @elseif($booking->trang_thai == 'da_xac_nhan') 
                                                    <i class="fas fa-check-circle mr-1"></i> Đã xác nhận
                                                @elseif($booking->trang_thai == 'da_huy') 
                                                    <i class="fas fa-times-circle mr-1"></i> Đã hủy
                                                @elseif($booking->trang_thai == 'da_tra') 
                                                    <i class="fas fa-check-double mr-1"></i> Đã trả phòng
                                                @else {{ $booking->trang_thai }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Body -->
                                <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <!-- Check-in -->
                                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
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
                                                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
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
                                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
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
                                </div>
                            </div>
                            @endforeach
                        </div>

                        @if($bookings->hasPages())
                            <div class="mt-6">
                                {{ $bookings->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                            <p class="text-gray-500 text-lg">Bạn chưa có lịch sử đặt phòng nào</p>
                            <a href="{{ route('client.phong') }}" class="inline-block mt-4 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
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
                                <label class="block text-sm font-medium text-gray-700 mb-2">Xác nhận mật khẩu mới</label>
                                <input type="password" name="password_confirmation" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors font-medium">
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
        fetch('{{ route("profile.avatar.update") }}', {
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
    anchor.addEventListener('click', function (e) {
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
</script>

@endsection
