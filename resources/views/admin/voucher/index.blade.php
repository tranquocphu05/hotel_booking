@extends('layouts.admin')

@section('title', 'Vouchers')

@section('admin_content')
    <div class="mt-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-700">📜 Danh sách phiếu giảm giá</h1>
            <a href="{{ route('admin.voucher.create') }}"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm transition">
                + Thêm Voucher
            </a>
        </div>

        <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gradient-to-r from-blue-50 to-indigo-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase">Mã voucher</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase">Giảm (%)</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase">Ngày bắt đầu</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase">Ngày kết thúc</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase">Số lượng</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase">Điều kiện</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase">Loại phòng áp dụng</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase">Trạng thái</th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-600 uppercase">Hành động</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach ($vouchers as $voucher)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-semibold text-gray-800">{{ $voucher->ma_voucher }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ rtrim(rtrim($voucher->gia_tri, '0'), '.') }}%</td>
                            <td class="px-6 py-4">{{ $voucher->ngay_bat_dau }}</td>
                            <td class="px-6 py-4">{{ $voucher->ngay_ket_thuc }}</td>
                            <td class="px-6 py-4 text-center">{{ $voucher->so_luong }}</td>

                            <td class="px-6 py-4 text-gray-600">
                                {{ $voucher->dieu_kien ?? 'Không có' }}
                            </td>

                            <td class="px-6 py-4 text-gray-600">
                                {{ $voucher->loaiPhong->ten_loai ?? 'Tất cả loại phòng' }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if ($voucher->trang_thai === 'con_han')
                                    <span
                                        class="inline-flex items-center justify-center px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full whitespace-nowrap min-w-[80px]">
                                        Còn hạn
                                    </span>
                                @elseif($voucher->trang_thai === 'het_han')
                                    <span
                                        class="inline-flex items-center justify-center px-3 py-1 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded-full whitespace-nowrap min-w-[80px]">
                                        Hết hạn
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center justify-center px-3 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full whitespace-nowrap min-w-[80px]">
                                        Hủy
                                    </span>
                                @endif
                            </td>


                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.voucher.edit', $voucher) }}"
                                    style="display:inline-block;padding:4px 10px;margin:2px;border-radius:6px;
               text-decoration:none;font-weight:500;color:#0d6efd;
               background-color:#e7f1ff;border:1px solid #b6d4fe;">
                                    Sửa
                                </a>

                                <form method="POST" action="{{ route('admin.voucher.destroy', $voucher) }}" class="inline"
                                    style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Xóa voucher này?')"
                                        style="display:inline-block;padding:4px 10px;margin:2px;border-radius:6px;
                   text-decoration:none;font-weight:500;color:#dc3545;
                   background-color:#f8d7da;border:1px solid #f5c2c7;cursor:pointer;">
                                        Xóa
                                    </button>
                                </form>
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
