@extends('layouts.admin')

@section('title', 'Quản lý loại phòng')

@section('admin_content')
<div class="bg-white rounded-2xl shadow p-6 mt-8 mb-8 w-full">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
        <h2 class="text-3xl font-semibold text-dark-600 flex items-center gap-2">
            <i class="bi bi-door-open-fill text-blue-600 text-3xl"></i>
            Quản lý loại phòng
        </h2>
        <div class="flex justify-start ml-8 ">
            <a href="{{ route('admin.loai_phong.create') }}"
            class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-dark font-medium px-6 py-2 rounded-full shadow transition">
                + Add
            </a>
        </div>

    </div>

    {{-- Thông báo thành công --}}
    @if (session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-100 text-green-800 text-sm font-medium shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Bảng dữ liệu --}}
    <div class="overflow-x-auto w-full">
        <table class="w-full text-sm text-gray-700 border border-gray-200 rounded-lg shadow-sm">
            <thead class="bg-gray-100 text-gray-800 text-xs uppercase font-semibold">
                <tr>
                    <th class="px-6 py-3 text-center border-b">Id</th>
                    <th class="px-6 py-3 text-center border-b">Tên loại phòng</th>
                    <th class="px-6 py-3 text-center border-b">Giá cơ bản</th>
                    <th class="px-6 py-3 text-center border-b">Trạng thái</th>
                    <th class="px-6 py-3 text-center border-b">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($loaiPhongs as $loai)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 font-medium">{{strtoupper($loai->ten_loai) }}</td>
                        <td class="px-6 py-4 text-blue-600 font-semibold">{{ number_format($loai->gia_co_ban, 0, ',', '.') }}₫</td>
                        <td class="px-6 py-4">
                            @if ($loai->trang_thai === 'hoat_dong')
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">Hoạt động</span>
                            @else
                                <span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-full">Ngừng</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center items-center gap-4">
                                <a href="{{ route('admin.loai_phong.edit', $loai->id) }}"
                                   class="text-amber-600 hover:text-amber-700 flex items-center gap-1 transition">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form action="{{ route('admin.loai_phong.destroy', $loai->id) }}" method="POST" onsubmit="return confirm('Xác nhận xóa loại phòng này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-700 flex items-center gap-1 transition">
                                        <i class="bi bi-trash3"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-6 text-center text-gray-500">
                            Chưa có loại phòng nào được thêm.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
