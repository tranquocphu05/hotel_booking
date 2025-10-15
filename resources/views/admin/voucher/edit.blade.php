@extends('layouts.admin')

@section('title', 'Chỉnh sửa Voucher')

@section('admin_content')
    <div class="max-w-lg mx-auto bg-white shadow rounded p-6 mt-6">
        <h2 class="text-lg font-semibold mb-4">Chỉnh sửa Voucher</h2>

        <form action="{{ route('admin.voucher.update', $voucher->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Mã voucher --}}
            <div class="mb-4">
                <label class="block mb-1 font-medium">Mã voucher:</label>
                <input type="text" name="ma_voucher" class="w-full border rounded px-3 py-2"
                    value="{{ old('ma_voucher', $voucher->ma_voucher) }}">
                @error('ma_voucher')
                    <p class="error-text mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Giảm (%) --}}
            <div class="mb-4">
                <label class="block mb-1 font-medium">Giảm (%):</label>
                <input type="number" name="gia_tri" class="w-full border rounded px-3 py-2"
                    value="{{ old('gia_tri', $voucher->gia_tri) }}" min="1" max="100">
                @error('gia_tri')
                    <p class="error-text mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Ngày bắt đầu --}}
            <div class="mb-4">
                <label class="block mb-1 font-medium">Ngày bắt đầu: </label>
                <input type="date" name="ngay_bat_dau" class="w-full border rounded px-3 py-2"
                    value="{{ old('ngay_bat_dau', $voucher->ngay_bat_dau) }}">
                @error('ngay_bat_dau')
                    <p class="error-text mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Ngày kết thúc --}}
            <div class="mb-4">
                <label class="block mb-1 font-medium">Ngày kết thúc:</label>
                <input type="date" name="ngay_ket_thuc" class="w-full border rounded px-3 py-2"
                    value="{{ old('ngay_ket_thuc', $voucher->ngay_ket_thuc) }}">
                @error('ngay_ket_thuc')
                    <p class="error-text mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Số lượng --}}
            <div class="mb-4">
                <label class="block mb-1 font-medium">Số lượng:</label>
                <input type="number" name="so_luong" class="w-full border rounded px-3 py-2"
                    value="{{ old('so_luong', $voucher->so_luong) }}" min="1">
                @error('so_luong')
                    <p class="error-text mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Loại phòng --}}
            <div class="mb-4">
                <label class="block mb-1 font-medium">Loại phòng áp dụng:</label>
                <select name="loai_phong_id" class="w-full border rounded px-3 py-2">
                    <option value="">Tất cả loại phòng</option>
                    @foreach ($loaiPhongs as $lp)
                        <option value="{{ $lp->id }}"
                            {{ old('loai_phong_id', $voucher->loai_phong_id) == $lp->id ? 'selected' : '' }}>
                            {{ $lp->ten_loai }}
                        </option>
                    @endforeach
                </select>
                @error('loai_phong_id')
                    <p class="error-text mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Điều kiện --}}
            <div class="mb-4">
                <label class="block mb-1 font-medium">Điều kiện:</label>
                <input type="text" name="dieu_kien" class="w-full border rounded px-3 py-2"
                    value="{{ old('dieu_kien', $voucher->dieu_kien) }}">
                @error('dieu_kien')
                    <p class="error-text mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Trạng thái --}}
            <div class="mb-4">
                <label class="block mb-1 font-medium">Trạng thái:</label>
                <select name="trang_thai" class="w-full border rounded px-3 py-2">
                    <option value="con_han" {{ old('trang_thai', $voucher->trang_thai) == 'con_han' ? 'selected' : '' }}>
                        Còn hạn</option>
                    <option value="het_han" {{ old('trang_thai', $voucher->trang_thai) == 'het_han' ? 'selected' : '' }}>
                        Hết hạn</option>
                    <option value="huy" {{ old('trang_thai', $voucher->trang_thai) == 'huy' ? 'selected' : '' }}>Hủy
                    </option>
                </select>
                @error('trang_thai')
                    <p class="error-text mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nút lưu --}}
            <button class="bg-blue-600 text-white px-4 py-2 rounded">Cập nhật</button>
            <a href="{{ route('admin.voucher.index') }}" class="ml-2 text-gray-600">Quay lại</a>
        </form>
    </div>
@endsection
