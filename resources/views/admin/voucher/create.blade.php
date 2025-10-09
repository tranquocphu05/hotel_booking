{{-- filepath: resources/views/admin/voucher/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Thêm Voucher')

@section('admin_content')
    <div class="max-w-lg mx-auto bg-white shadow rounded p-6 mt-6">
        <h2 class="text-lg font-semibold mb-4">Thêm Voucher</h2>
        <form action="{{ route('admin.voucher.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block mb-1 font-medium">Mã voucher</label>
                <input type="text" name="ma_voucher" class="w-full border rounded px-3 py-2" required
                    value="{{ old('ma_voucher') }}">
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-medium">Giảm (%)</label>
                <input type="number" name="gia_tri" class="w-full border rounded px-3 py-2" min="1" max="100"
                    required value="{{ old('gia_tri') }}">
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-medium">Ngày bắt đầu</label>
                <input type="date" name="ngay_bat_dau" class="w-full border rounded px-3 py-2"
                    value="{{ old('ngay_bat_dau') }}">
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-medium">Ngày kết thúc</label>
                <input type="date" name="ngay_ket_thuc" class="w-full border rounded px-3 py-2"
                    value="{{ old('ngay_ket_thuc') }}">
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-medium">Số lượng</label>
                <input type="number" name="so_luong" class="w-full border rounded px-3 py-2" min="1"
                    value="{{ old('so_luong') }}">
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-medium">Loại phòng áp dụng</label>
                <select name="loai_phong_id" class="w-full border rounded px-3 py-2" required>
                    <option value="">-- Chọn loại phòng --</option>
                     @foreach ($loaiPhongs as $lp)
                        <option value="{{ $lp->id }}"
                            {{ old('loai_phong_id', $voucher->loai_phong_id ?? '') == $lp->id ? 'selected' : '' }}>
                            {{ $lp->ten_loai }}
                        </option>
                    @endforeach 
                </select>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-medium">Điều kiện</label>
                <input type="text" name="dieu_kien" class="w-full border rounded px-3 py-2"
                    value="{{ old('dieu_kien') }}">
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-medium">Trạng thái</label>
                <select name="trang_thai" class="w-full border rounded px-3 py-2">
                    <option value="con_han" {{ old('trang_thai') == 'con_han' ? 'selected' : '' }}>Còn hạn</option>
                    <option value="het_han" {{ old('trang_thai') == 'het_han' ? 'selected' : '' }}>Hết hạn</option>
                    <option value="huy" {{ old('trang_thai') == 'huy' ? 'selected' : '' }}>Hủy</option>
                </select>
            </div>
            <button class="bg-blue-600 text-white px-4 py-2 rounded">Lưu</button>
            <a href="{{ route('admin.voucher.index') }}" class="ml-2 text-gray-600">Quay lại</a>
        </form>
    </div>
@endsection
