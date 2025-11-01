@extends('layouts.admin')

@section('title', 'Edit User')

@section('admin_content')
    <div class="max-w-3xl mx-auto bg-white shadow-md rounded-2xl p-8 relative">
        <div class="flex items-center justify-between mb-6 border-b pb-3">
            <h1 class="text-2xl font-semibold text-gray-800">✏️ Chỉnh sửa người dùng</h1>
            <a href="{{ route('admin.users.index') }}"
                class="text-sm text-blue-600 hover:underline hover:text-blue-800">← Quay lại danh sách</a>
        </div>

        <form id="user-form" method="POST" action="{{ route('admin.users.update', $user) }}" autocomplete="off"
            class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Username --}}
            <div>
                <label class="block font-medium text-gray-700 mb-1">Tên đăng nhập</label>
                <input name="username" value="{{ old('username', $user->username) }}"
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('username') border-red-500 @enderror">
                @error('username')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="block font-medium text-gray-700 mb-1">Email</label>
                <input name="email" value="{{ old('email', $user->email) }}"
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label class="block font-medium text-gray-700 mb-1">Mật khẩu (để trống nếu không đổi)</label>
                <input name="password" type="password"
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Full name --}}
            <div>
                <label class="block font-medium text-gray-700 mb-1">Họ và tên</label>
                <input name="ho_ten" value="{{ old('ho_ten', $user->ho_ten) }}"
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('ho_ten') border-red-500 @enderror">
                @error('ho_ten')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Phone --}}
            <div>
                <label class="block font-medium text-gray-700 mb-1">Số điện thoại</label>
                <input name="sdt" value="{{ old('sdt', $user->sdt) }}"
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('sdt') border-red-500 @enderror">
                @error('sdt')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- CCCD --}}
            <div>
                <label class="block font-medium text-gray-700 mb-1">CCCD</label>
                <input name="cccd" value="{{ old('cccd', $user->cccd) }}"
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('cccd') border-red-500 @enderror">
                @error('cccd')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Address --}}
            <div>
                <label class="block font-medium text-gray-700 mb-1">Địa chỉ</label>
                <input name="dia_chi" value="{{ old('dia_chi', $user->dia_chi) }}"
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
                    <option value="admin" {{ old('vai_tro', $user->vai_tro) == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="nhan_vien" {{ old('vai_tro', $user->vai_tro) == 'nhan_vien' ? 'selected' : '' }}>Nhân viên</option>
                    <option value="khach_hang" {{ old('vai_tro', $user->vai_tro) == 'khach_hang' ? 'selected' : '' }}>Khách hàng</option>
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
                    <option value="hoat_dong" {{ old('trang_thai', $user->trang_thai) == 'hoat_dong' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="khoa" {{ old('trang_thai', $user->trang_thai) == 'khoa' ? 'selected' : '' }}>Khóa</option>
                </select>
                @error('trang_thai')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <div class="pt-4">
                <button
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition duration-200 transform hover:scale-[1.01]">
                    ✅ Cập nhật người dùng
                </button>
            </div>
        </form>
    </div>

    {{-- Thanh cố định khi trang dài --}}
    {{-- <div class="fixed bottom-0 left-0 right-0 bg-white border-t p-3 shadow-lg z-50">
        <div class="max-w-7xl mx-auto flex justify-end">
            <button form="user-form" type="submit"
                class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md transition duration-200">
                💾 Lưu thay đổi
            </button>
        </div>
    </div> --}}
@endsection
