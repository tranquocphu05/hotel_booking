@extends('layouts.admin')

@section('title', 'Quản lý phòng')

@section('admin_content')
<div class="bg-white rounded-2xl shadow p-6 mt-8 mb-8 w-full">
    <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
        <h2 class="text-3xl font-semibold text-blue-600 flex items-center gap-2">
            <i class="bi bi-building"></i> Quản lý phòng
        </h2>
        @hasPermission('phong.create')
        <div class="flex gap-3">
            <a href="{{ route('admin.phong.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-2 rounded-full shadow transition">
                <i class="fas fa-plus"></i>
                Thêm phòng
            </a>
        </div>
        @endhasPermission
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-100 text-green-800 text-sm font-medium shadow-sm">
            {{ session('success') }}
        </div>
    @endif
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
            <option value="dang_thue" {{ request('trang_thai')=='dang_thue'?'selected':'' }}>Đang thuê</option>
            <option value="dang_don" {{ request('trang_thai')=='dang_don'?'selected':'' }}>Đang dọn</option>
            <option value="bao_tri" {{ request('trang_thai')=='bao_tri'?'selected':'' }}>Bảo trì</option>
        </select>
        
        <input type="text" name="search" placeholder="Tìm theo số phòng, tên phòng..." 
               value="{{ request('search') }}" class="border rounded-lg p-2 flex-1">
        
        <input type="number" name="tang" placeholder="Tầng" 
               value="{{ request('tang') }}" class="border rounded-lg p-2 w-24">

        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">Lọc</button>
    </form>

    <div class="w-full">
    <table class="w-full text-sm text-gray-700 border border-gray-200 rounded-lg shadow-sm">
        <thead class="bg-gray-100 text-gray-800 text-xs uppercase font-semibold">
            <tr>
                <th class="px-3 py-3 text-center border-b whitespace-nowrap">STT</th>
                <th class="px-4 py-3 text-center border-b whitespace-nowrap">Số phòng</th>
                <th class="px-4 py-3 text-center border-b">Tên phòng</th>
                <th class="px-4 py-3 text-center border-b">Loại phòng</th>
                <th class="px-3 py-3 text-center border-b whitespace-nowrap w-16">Tầng</th>
                <th class="px-4 py-3 text-center border-b">Hướng cửa sổ</th>
                <th class="px-4 py-3 text-center border-b">Tiện ích</th> {{-- Cột này giữ nguyên để có thể xuống dòng/cuộn nếu có nhiều badge --}}
                <th class="px-4 py-3 text-center border-b whitespace-nowrap">Giá riêng</th>
                <th class="px-4 py-3 text-center border-b whitespace-nowrap">Trạng thái</th>
                <th class="px-4 py-3 text-center border-b whitespace-nowrap">Thao tác</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($phongs as $phong)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-3 py-3 text-center whitespace-nowrap">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3 text-center font-semibold text-blue-600 whitespace-nowrap">{{ $phong->so_phong }}</td>
                    <td class="px-4 py-3 text-center">{{ $phong->ten_phong ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">{{ $phong->loaiPhong->ten_loai ?? '-' }}</td>
                    <td class="px-3 py-3 text-center whitespace-nowrap w-16">{{ $phong->tang ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($phong->huong_cua_so)
                            @php
                                $huongMap = ['bien' => 'Biển', 'nui' => 'Núi', 'thanh_pho' => 'Thành phố', 'san_vuon' => 'Sân vườn'];
                            @endphp
                            <span class="text-xs text-gray-600 whitespace-nowrap">{{ $huongMap[$phong->huong_cua_so] ?? $phong->huong_cua_so }}</span>
                        @else
                            <span class="text-gray-400 text-xs whitespace-nowrap">-</span>
                        @endif
                    </td>
                    {{-- Cột Tiện ích: giữ nguyên flex-wrap để tránh quá rộng nếu có nhiều tiện ích --}}
                    <td class="px-6 py-3 text-center">
                        <div class="flex flex-wrap justify-center gap-1">
                            @if($phong->co_ban_cong)
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded whitespace-nowrap">Ban công</span>
                            @endif
                            @if($phong->co_view_dep)
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded whitespace-nowrap">View đẹp</span>
                            @endif
                            @if(!$phong->co_ban_cong && !$phong->co_view_dep)
                                <span class="text-gray-400 text-xs whitespace-nowrap">-</span>
                            @endif
                        </div>
                    </td>
                    
                    <td class="px-6 py-3 text-center whitespace-nowrap">
                        @if($phong->gia_rieng)
                            <span class="text-blue-600 font-semibold whitespace-nowrap">{{ number_format($phong->gia_rieng, 0, ',', '.') }} VNĐ</span>
                        @else
                            <span class="text-gray-400 text-xs whitespace-nowrap">-</span>
                        @endif
                    </td>
                    
                    <td class="px-6 py-3 text-center whitespace-nowrap">
                        @if ($phong->trang_thai === 'trong')
                            <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full whitespace-nowrap">Trống</span>
                        @elseif ($phong->trang_thai === 'dang_thue')
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full whitespace-nowrap">Đang thuê</span>
                        @elseif ($phong->trang_thai === 'dang_don')
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-medium rounded-full whitespace-nowrap">Đang dọn</span>
                        @else
                            <span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-full whitespace-nowrap">Bảo trì</span>
                        @endif
                    </td>
                    
                    <td class="px-6 py-3 text-center whitespace-nowrap">
                        <div class="flex justify-center items-center gap-2">
                            <a href="{{ route('admin.phong.show', $phong->id) }}" 
                               class="text-blue-600 hover:text-blue-700 flex items-center gap-1 transition text-xs" 
                               title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>
                            {{-- Nút xác nhận hoàn tất dọn phòng - chỉ hiển thị khi phòng đang dọn --}}
                            @if($phong->trang_thai == 'dang_don')
                                @hasPermission('phong.update_status')
                                    <form action="{{ route('admin.phong.update_status', $phong->id) }}" method="POST" class="inline" onsubmit="return confirm('Xác nhận phòng {{ $phong->so_phong }} đã dọn xong?')">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="trang_thai" value="trong">
                                        <button type="submit" 
                                                class="text-green-600 hover:text-green-700 flex items-center gap-1 transition text-xs"
                                                title="Hoàn tất dọn phòng">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    </form>
                                @endhasPermission
                            @endif
                            @hasPermission('phong.edit')
                            <a href="{{ route('admin.phong.edit', $phong->id) }}" 
                               class="text-yellow-500 hover:text-yellow-600 flex items-center gap-1 transition text-xs"
                               title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endhasPermission
                            @hasPermission('phong.delete')
                            <form action="{{ route('admin.phong.destroy', $phong->id) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa phòng {{ $phong->so_phong }}?')"
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="text-red-600 hover:text-red-700 flex items-center gap-1 transition text-xs"
                                        title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endhasPermission
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="px-6 py-6 text-center text-gray-500">
                        Chưa có phòng nào được thêm.
                        <a href="{{ route('admin.phong.create') }}" class="text-blue-600 hover:underline ml-2">Thêm phòng mới</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
    <div class="mt-6">
        {{ $phongs->appends(request()->query())->links() }}
    </div>
</div>
@endsection

