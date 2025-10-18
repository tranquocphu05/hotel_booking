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
            <div class="rounded-xl border border-gray-200 overflow-x-auto">
                <table class="voucher-table w-full">
                    <thead>
                        <tr>
                            <th>MÃ VOUCHER</th>
                            <th>GIẢM (%)</th>
                            <th>NGÀY BẮT ĐẦU</th>
                            <th>NGÀY KẾT THÚC</th>
                            <th>SỐ LƯỢNG</th>
                            <th>ĐIỀU KIỆN</th>
                            <th>LOẠI PHÒNG ÁP DỤNG</th>
                            <th>TRẠNG THÁI</th>
                            <th>HÀNH ĐỘNG</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($vouchers as $voucher)
                            <tr>
                                <td>{{ $voucher->ma_voucher }}</td>
                                <td>{{ rtrim(rtrim($voucher->gia_tri, '0'), '.') }}%</td>
                                <td>{{ $voucher->ngay_bat_dau }}</td>
                                <td>{{ $voucher->ngay_ket_thuc }}</td>
                                <td>{{ $voucher->so_luong }}</td>
                                <td>{{ $voucher->dieu_kien ?? 'Không có' }}</td>
                                <td>{{ $voucher->loaiPhong->ten_loai ?? 'Tất cả' }}</td>
                                <td>
                                    @php
                                        $statusClass = [
                                            'con_han' => 'text-green-600',
                                            'het_han' => 'text-red-600',
                                            'huy' => 'text-gray-800',
                                        ][$voucher->trang_thai] ?? 'text-gray-600';
                                        
                                        $statusText = [
                                            'con_han' => 'Còn hạn',
                                            'het_han' => 'Hết hạn',
                                            'huy' => 'Hủy',
                                        ][$voucher->trang_thai] ?? 'Không rõ';
                                    @endphp
                                    <span class="font-medium {{ $statusClass }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.voucher.edit', $voucher) }}"
                                        class="text-blue-500 hover:text-blue-700 font-medium mr-2">
                                        Sửa
                                    </a>
                                    <form method="POST" action="{{ route('admin.voucher.destroy', $voucher) }}"
                                        class="inline" onsubmit="return confirm('Xóa voucher này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-medium">
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
