@extends('layouts.admin')

@section('title', 'Vouchers')

@section('admin_content')
    <div class="flex items-center justify-between mb-4 mt-6">
        <h1 class="text-xl font-semibold">Phiếu giảm giá</h1>
        <a href="{{ route('admin.voucher.create') }}" class="px-3 py-1 bg-blue-600 text-white rounded">Thêm Voucher</a>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã voucher
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giảm (%)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày bắt đầu
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày kết thúc
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                    
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Điều kiện
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại phòng áp dụng</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hành động
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($vouchers as $voucher)
                    <tr>
                        <td class="px-6 py-4 font-semibold">{{ $voucher->ma_voucher }}</td>
                        <td class="px-6 py-4">{{ rtrim(rtrim($voucher->gia_tri, '0'), '.') }}%</td>
                        <td class="px-6 py-4">{{ $voucher->ngay_bat_dau }}</td>
                        <td class="px-6 py-4">{{ $voucher->ngay_ket_thuc }}</td>
                        <td class="px-6 py-4">{{ $voucher->so_luong }}</td>
                        <td class="px-6 py-4">
                            @if ($voucher->dieu_kien)
                                {{ $voucher->dieu_kien }}
                            @else
                                Không có
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if ($voucher->loaiPhong)
                                Áp dụng cho: {{ $voucher->loaiPhong->ten_loai }}
                            @else
                                Tất cả loại phòng
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if ($voucher->trang_thai === 'con_han')
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Còn
                                    hạn</span>
                            @elseif($voucher->trang_thai === 'het_han')
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Hết
                                    hạn</span>
                            @else
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Hủy</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.voucher.edit', $voucher) }}" class="text-blue-600 mr-2">Sửa</a>
                            <form method="POST" action="{{ route('admin.voucher.destroy', $voucher) }}"
                                style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600"
                                    onclick="return confirm('Xóa voucher này?')">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
