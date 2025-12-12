@extends('layouts.admin')

@section('title', 'Create User')

@section('admin_content')
    <div class="max-w-3xl mx-auto bg-white shadow-md rounded-2xl p-8">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6 border-b pb-3">🧑‍💼 Tạo tài khoản người dùng</h1>

        <form method="POST" action="{{ route('admin.users.store') }}" autocomplete="off" class="space-y-6">
            @csrf

            {{-- Username --}}
            <div>
                <label class="block font-medium text-gray-700 mb-1">Tên đăng nhập</label>
                <input type="text" name="username" value="{{ old('username') }}"
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('username') border-red-500 @enderror">
                @error('username')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="block font-medium text-gray-700 mb-1">Email</label>
                <input type="text" name="email" value="{{ old('email') }}"
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label class="block font-medium text-gray-700 mb-1">Mật khẩu</label>
                <input type="password" name="password"
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Full name --}}
            <div>
                <label class="block font-medium text-gray-700 mb-1">Họ và tên</label>
                <input type="text" name="ho_ten" value="{{ old('ho_ten') }}"
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('ho_ten') border-red-500 @enderror">
                @error('ho_ten')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Phone --}}
            <div>
                <label class="block font-medium text-gray-700 mb-1">Số điện thoại</label>
                <input type="text" name="sdt" value="{{ old('sdt') }}"
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('sdt') border-red-500 @enderror">
                @error('sdt')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- CCCD --}}
            <div>
                <label class="block font-medium text-gray-700 mb-1">CCCD</label>
                <input type="text" name="cccd" value="{{ old('cccd') }}"
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('cccd') border-red-500 @enderror">
                @error('cccd')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Address --}}
            <div>
                <label class="block font-medium text-gray-700 mb-1">Địa chỉ</label>
                <input type="text" name="dia_chi" value="{{ old('dia_chi') }}"
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('dia_chi') border-red-500 @enderror">
                @error('dia_chi')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Role --}}
            <div>
                <label class="block font-medium text-gray-700 mb-1">Vai trò</label>
                <select name="vai_tro"
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('vai_tro') border-red-500 @enderror">
                    <option value="admin" {{ old('vai_tro') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="nhan_vien" {{ old('vai_tro') == 'nhan_vien' ? 'selected' : '' }}>Nhân viên</option>
                    <option value="le_tan" {{ old('vai_tro') == 'le_tan' ? 'selected' : '' }}>Lễ tân</option>
                    <option value="khach_hang" {{ old('vai_tro', 'khach_hang') == 'khach_hang' ? 'selected' : '' }}>Khách hàng</option>
                </select>
                @error('vai_tro')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status --}}
            <div>
                <label class="block font-medium text-gray-700 mb-1">Trạng thái</label>
                <select name="trang_thai"
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('trang_thai') border-red-500 @enderror">
                    <option value="hoat_dong" {{ old('trang_thai') == 'hoat_dong' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="khoa" {{ old('trang_thai') == 'khoa' ? 'selected' : '' }}>Khóa</option>
                </select>
                @error('trang_thai')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit button --}}
            <div class="pt-4">
                <button
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition duration-200 transform hover:scale-[1.01]">
                    💾 Lưu người dùng
                </button>
            </div>
        </form>
    </div>
@endsection
