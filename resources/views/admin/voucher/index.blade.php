@extends('layouts.admin')

@section('title', 'Vouchers')

@section('admin_content')

    <div class="mt-6">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-xl font-bold text-gray-700 flex items-center">
                    <span class="text-orange-500 mr-2">📜</span> Danh sách phiếu giảm giá
                </h1>
                <a href="{{ route('admin.voucher.create') }}"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-md transition font-medium">
                    + Thêm Voucher
                </a>
            </div>

            <div class="mb-6">
                <form action="{{ route('admin.voucher.index') }}" method="GET" class="flex flex-wrap gap-4">
                    {{-- Lọc theo Loại phòng áp dụng --}}
                    <div class="relative">
                        <select name="loai_phong_id"
                            class="appearance-none border border-gray-300 rounded-lg px-3 pr-8 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Loại phòng áp dụng --</option>
                            @if (isset($loaiPhongs))
                                @foreach ($loaiPhongs as $lp)
                                    <option value="{{ $lp->id }}"
                                        {{ request('loai_phong_id') == $lp->id ? 'selected' : '' }}>
                                        {{ $lp->ten_loai }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    {{-- Lọc theo Trạng thái --}}
                    <div class="relative">
                        <select name="trang_thai"
                            class="appearance-none border border-gray-300 rounded-lg px-3 pr-8 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Trạng thái --</option>
                            <option value="con_han" {{ request('trang_thai') == 'con_han' ? 'selected' : '' }}>Còn hạn
                            </option>
                            <option value="het_han" {{ request('trang_thai') == 'het_han' ? 'selected' : '' }}>Hết hạn
                            </option>
                            <option value="huy" {{ request('trang_thai') == 'huy' ? 'selected' : '' }}>Hủy</option>
                        </select>
                    </div>

                    {{-- Nút Lọc --}}
                    <button type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg font-medium hover:bg-blue-600 transition shadow-sm">
                        Lọc
                    </button>
                </form>
            </div>
            {{-- Bảng danh sách Voucher --}}
            <div class="rounded-xl overflow-x-auto border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">MÃ
                                VOUCHER</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">GIẢM
                                (%)</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">NGÀY
                                BẮT ĐẦU</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">NGÀY
                                KẾT THÚC</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">SỐ
                                LƯỢNG</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">ĐIỀU
                                KIỆN</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">LOẠI
                                PHÒNG ÁP DỤNG</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">TRẠNG
                                THÁI</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">HÀNH
                                ĐỘNG</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($vouchers as $voucher)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 font-medium text-gray-800 whitespace-nowrap">{{ $voucher->ma_voucher }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                                    {{ rtrim(rtrim($voucher->gia_tri, '0'), '.') }}%</td>
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $voucher->ngay_bat_dau }}</td>
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $voucher->ngay_ket_thuc }}</td>
                                <td class="px-4 py-3 text-center text-gray-600 whitespace-nowrap">{{ $voucher->so_luong }}
                                </td>

                                {{-- Điều kiện --}}
                                <td class="px-4 py-3 text-gray-600 max-w-[180px] overflow-hidden text-ellipsis">
                                    {{ $voucher->dieu_kien ?? 'Không có' }}
                                </td>

                                {{-- Loại phòng áp dụng (Hiển thị tên loại phòng) --}}
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                    {{ $voucher->loaiPhong->ten_loai ?? 'Tất cả' }}
                                </td>

                                <td class="px-4 py-3">
                                    @php
                                        $statusClass =
                                            [
                                                'con_han' => 'text-green-600',
                                                'het_han' => 'text-red-600',
                                                'huy' => 'text-gray-800',
                                            ][$voucher->trang_thai] ?? 'text-gray-600';
                                        $statusText =
                                            [
                                                'con_han' => 'Còn hạn',
                                                'het_han' => 'Hết hạn',
                                                'huy' => 'Hủy',
                                            ][$voucher->trang_thai] ?? 'Không rõ';
                                    @endphp
                                    <span class="font-medium text-sm {{ $statusClass }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <a href="{{ route('admin.voucher.edit', $voucher) }}"
                                        class="text-blue-500 hover:text-blue-700 font-medium text-sm mr-2">
                                        Sửa
                                    </a>
                                    <form method="POST" action="{{ route('admin.voucher.destroy', $voucher) }}"
                                        class="inline" onsubmit="return confirm('Xóa voucher này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-sm">
                                            Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                    Chưa có phiếu giảm giá nào được thêm.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $vouchers->links() }}
            </div>


        </div>
    @endsection
