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
        <div class="flex justify-start ml-8">
            <a href="{{ route('admin.loai_phong.create') }}"
               class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-2 rounded-full shadow transition">
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

    {{-- Filter form --}}
    <form method="GET" class="mb-6 p-4 bg-gray-50 rounded-lg">
        <div class="flex flex-wrap gap-4 items-center">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lọc theo trạng thái:</label>
                <select name="trang_thai" class="border rounded-lg p-2">
                    <option value="">-- Tất cả --</option>
                    <option value="hoat_dong" {{ request('trang_thai') == 'hoat_dong' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="ngung" {{ request('trang_thai') == 'ngung' ? 'selected' : '' }}>Ngừng</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                    Lọc
                </button>
                <a href="{{ route('admin.loai_phong.index') }}" class="ml-2 px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                    Xóa bộ lọc
                </a>
            </div>
        </div>
    </form>

    {{-- Debug info --}}
    <div class="mb-4 p-3 bg-blue-50 rounded-lg">
        <p class="text-sm text-blue-800">
            <strong>Debug:</strong> Tổng số loại phòng: {{ $loaiPhongs->count() }}
        </p>
    </div>

    {{-- Bảng dữ liệu --}}
    <div class="overflow-x-auto w-full">
        <table class="w-full text-sm text-gray-700 border border-gray-200 rounded-lg shadow-sm">
            <thead class="bg-gray-100 text-gray-800 text-xs uppercase font-semibold">
                <tr>
                    <th class="px-6 py-3 text-center border-b">#</th>
                    <th class="px-6 py-3 text-center border-b">Hình ảnh</th>
                    <th class="px-6 py-3 text-center border-b">Tên loại phòng</th>
                    <th class="px-6 py-3 text-center border-b">Giá cơ bản</th>
                    <th class="px-6 py-3 text-center border-b">Trạng thái</th>
                    <th class="px-6 py-3 text-center border-b">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($loaiPhongs as $loai)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-center">{{ $loop->iteration }}</td>
                        <!-- Debug: ID={{ $loai->id }}, Name={{ $loai->ten_loai }}, Status={{ $loai->trang_thai }} -->

                        {{-- Cột hình ảnh --}}
                        <td class="px-6 py-4 text-center">
                            @if ($loai->anh && file_exists(public_path($loai->anh)))
                                <img src="{{ asset($loai->anh) }}"
                                     alt="{{ $loai->ten_loai }}"
                                     class="w-20 h-16 object-cover rounded-lg shadow-sm border border-gray-200 mx-auto">
                            @else
                                <div class="w-20 h-16 bg-gray-100 rounded-lg border border-gray-200 mx-auto flex items-center justify-center">
                                    <span class="text-gray-400 text-xs">Không có ảnh</span>
                                </div>
                            @endif
                        </td>

                        <td class="px-6 py-4 font-medium">
                            <div class="text-gray-900 font-semibold">{{ $loai->ten_loai }}</div>
                            @if($loai->mo_ta)
                                <div class="text-xs text-gray-500 mt-1">{{ Str::limit($loai->mo_ta, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-blue-600 font-semibold text-center">
                            {{ number_format($loai->gia_co_ban, 0, ',', '.') }}₫
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if ($loai->trang_thai === 'hoat_dong')
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                    Hoạt động
                                </span>
                            @else
                                <span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-full">
                                    Ngừng
                                </span>
                            @endif
                        </td>

                        {{-- Cột thao tác --}}
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center items-center gap-4">
                                <a href="{{ route('admin.loai_phong.edit', $loai->id) }}"
                                   class="text-amber-600 hover:text-amber-700 flex items-center gap-1 transition">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form action="{{ route('admin.loai_phong.destroy', $loai->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Xác nhận xóa loại phòng này?')">
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
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="bi bi-door-open text-4xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500 text-lg mb-2">Chưa có loại phòng nào</p>
                                <p class="text-gray-400 text-sm">Hãy thêm loại phòng đầu tiên để bắt đầu</p>
                                <a href="{{ route('admin.loai_phong.create') }}" 
                                   class="mt-4 inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg transition">
                                    <i class="bi bi-plus-circle"></i>
                                    Thêm loại phòng
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">
        {{ $loaiPhongs->appends(request()->query())->links() }}
    </div>
</div>
@endsection
