@extends('layouts.admin')

@section('title', 'Quản lý phòng')

@section('admin_content')
<div class="bg-white rounded-2xl shadow p-6 mt-8 mb-8 w-full">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-3xl font-semibold text-blue-600 flex items-center gap-2">
            <i class="bi bi-building"></i> Quản lý phòng
        </h2>
        <a href="{{ route('admin.phong.create') }}"
           class="px-5 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition">
            + Thêm phòng
        </a>
    </div>

    {{-- Bộ lọc --}}
    <form method="GET" class="flex flex-wrap gap-4 mb-6">
        <select name="loai_phong_id" class="border rounded-lg p-2">
            <option value="">-- Loại phòng --</option>
            @foreach($loaiPhongs as $loai)
                <option value="{{ $loai->id }}" {{ request('loai_phong_id') == $loai->id ? 'selected' : '' }}>
                    {{ $loai->ten_loai }}
                </option>
            @endforeach
        </select>

        <select name="trang_thai" class="border rounded-lg p-2">
            <option value="">-- Trạng thái --</option>
            <option value="trong" {{ request('trang_thai')=='trong'?'selected':'' }}>Trống</option>
            <option value="da_dat" {{ request('trang_thai')=='da_dat'?'selected':'' }}>Đã đặt</option>
            <option value="bao_tri" {{ request('trang_thai')=='bao_tri'?'selected':'' }}>Bảo trì</option>
        </select>

        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
            Lọc
        </button>
    </form>

    {{-- Thông báo --}}
    @if (session('success'))
        <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Danh sách --}}
    <div class="overflow-x-auto w-full">
        <table class="w-full text-sm text-gray-700 border border-gray-200 rounded-lg shadow-sm">
            <thead class="bg-gray-100 text-gray-800 text-xs uppercase font-semibold">
                <tr>
                    <th class="px-4 py-3 text-center border-b">ID</th>
                    <th class="px-4 py-3 text-center border-b">Tên phòng</th>
                    <th class="px-4 py-3 text-center border-b">Loại</th>
                    <th class="px-4 py-3 text-center border-b">Giá</th>
                    <th class="px-4 py-3 text-center border-b">Ảnh</th>
                    <th class="px-4 py-3 text-center border-b">Trạng thái</th>
                    <th class="px-4 py-3 text-center border-b">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($phongs as $phong)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-center">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 text-center font-medium">{{ $phong->ten_phong }}</td>
                        <td class="px-4 py-3 text-center">{{ $phong->loaiPhong->ten_loai ?? '-' }}</td>
                        <td class="px-4 py-3 text-center text-blue-600 font-semibold">
                            {{ number_format($phong->gia, 0, ',', '.') }}₫
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($phong->img)
                                <img src="{{ asset($phong->img) }}" alt="Ảnh phòng"
                                     class="w-14 h-14 object-cover rounded-lg mx-auto shadow">
                            @else
                                <span class="text-gray-400 text-xs italic">Không có ảnh</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center capitalize">
                            @if ($phong->trang_thai === 'trong')
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                    Trống
                                </span>
                            @elseif ($phong->trang_thai === 'da_dat')
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-medium rounded-full">
                                    Đã đặt
                                </span>
                            @else
                                <span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-full">
                                    Bảo trì
                                </span>
                            @endif
                        </td>
<td class="px-6 py-4 text-center">
    <div class="flex justify-center items-center gap-4">
        {{-- Nút sửa --}}
        <a href="{{ route('admin.phong.edit', $phong->id) }}"
           class="text-amber-600 hover:text-amber-700 flex items-center gap-1 transition">
            <i class="bi bi-pencil-square"></i> Edit
        </a>

        {{-- Nút xóa --}}
        <form action="{{ route('admin.phong.destroy', $phong->id) }}" method="POST"
              onsubmit="return confirm('Xác nhận xóa phòng này?')" class="inline-block">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="text-red-600 hover:text-red-700 flex items-center gap-1 transition">
                <i class="bi bi-trash3"></i> Delete
            </button>
        </form>
    </div>
</td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-6 text-center text-gray-500">
                            Chưa có phòng nào được thêm.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
