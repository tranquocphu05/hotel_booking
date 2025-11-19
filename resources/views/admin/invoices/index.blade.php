@extends('layouts.admin')
@section('title', 'Danh sách Hóa đơn')

@section('admin_content')
<div class="bg-white rounded-2xl shadow p-6 mt-8 mb-8 w-full">
  {{-- Header --}}
  <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
    <h2 class="text-3xl font-semibold text-gray-800 flex items-center gap-2">
      <i class="fas fa-file-invoice text-blue-600 text-3xl"></i> Danh sách Hóa đơn
    </h2>
  </div>

  {{-- Success --}}
  @if(session('success'))
    <div class="mb-6 p-4 rounded-lg bg-green-100 text-green-800 text-sm font-medium shadow-sm">
      {{ session('success') }}
    </div>
  @endif

  {{-- Filter --}}
  <div class="mb-6 bg-gray-50 p-4 rounded-lg">
    <form action="{{ route('admin.invoices.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
      <div class="flex-1">
        <label class="block text-sm font-medium text-gray-700 mb-2">Khách hàng</label>
        <select name="user_id" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-700">
          <option value="">Tất cả Khách hàng</option>
          @foreach($users as $u)
            <option value="{{ $u->id }}" @selected(request('user_id')==$u->id)>{{ $u->ho_ten ?? $u->username }}</option>
          @endforeach
        </select>
      </div>

      <div class="flex-1">
        <label class="block text-sm font-medium text-gray-700 mb-2">Trạng thái</label>
        <select name="status" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-700">
          <option value="" {{ request('status') == '' ? 'selected' : '' }}>Tất cả Trạng thái</option>
          <option value="cho_thanh_toan" {{ request('status') == 'cho_thanh_toan' ? 'selected' : '' }}>Chờ thanh toán</option>
          <option value="da_thanh_toan" {{ request('status') == 'da_thanh_toan' ? 'selected' : '' }}>Đã thanh toán</option>
          <option value="hoan_tien" {{ request('status') == 'hoan_tien' ? 'selected' : '' }}>Hoàn tiền</option>
        </select>
      </div>

      <div class="flex items-end">
        <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium transition-all hover:scale-105 shadow-sm">
          <i class="fas fa-filter mr-2"></i>Lọc
        </button>
      </div>
    </form>
  </div>

  {{-- Statistics Cards --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 border-l-4 border-yellow-500 rounded-lg p-4 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs font-medium text-yellow-700 uppercase">Chờ thanh toán</p>
          <p class="text-2xl font-bold text-yellow-900 mt-1">{{ $invoices->where('trang_thai', 'cho_thanh_toan')->count() }}</p>
        </div>
        <div class="bg-yellow-200 rounded-full p-3">
          <i class="fas fa-clock text-yellow-700 text-xl"></i>
        </div>
      </div>
    </div>

    <div class="bg-gradient-to-br from-green-50 to-green-100 border-l-4 border-green-500 rounded-lg p-4 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs font-medium text-green-700 uppercase">Đã thanh toán</p>
          <p class="text-2xl font-bold text-green-900 mt-1">{{ $invoices->where('trang_thai', 'da_thanh_toan')->count() }}</p>
        </div>
        <div class="bg-green-200 rounded-full p-3">
          <i class="fas fa-check-circle text-green-700 text-xl"></i>
        </div>
      </div>
    </div>

    <div class="bg-gradient-to-br from-red-50 to-red-100 border-l-4 border-red-500 rounded-lg p-4 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs font-medium text-red-700 uppercase">Hoàn tiền</p>
          <p class="text-2xl font-bold text-red-900 mt-1">{{ $invoices->where('trang_thai', 'hoan_tien')->count() }}</p>
        </div>
        <div class="bg-red-200 rounded-full p-3">
          <i class="fas fa-rotate-left text-red-700 text-xl"></i>
        </div>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="overflow-x-auto w-full">
    <table class="w-full text-sm text-gray-700 border border-gray-200 rounded-lg shadow-sm">
      <thead class="bg-gradient-to-r from-gray-50 to-gray-100 text-gray-800 text-xs uppercase font-semibold">
        <tr>
          <th class="px-4 py-3 text-left border-b">ID</th>
          <th class="px-4 py-3 text-left border-b">Khách hàng</th>
          <th class="px-4 py-3 text-left border-b">Loại Phòng</th>
          <th class="px-4 py-3 text-center border-b">Số lượng</th>
          <th class="px-4 py-3 text-right border-b">Tổng tiền</th>
          <th class="px-4 py-3 text-center border-b">Phương thức</th>
          <th class="px-4 py-3 text-center border-b">Trạng thái</th>
          <th class="px-4 py-3 text-center border-b">Ngày tạo</th>
          <th class="px-4 py-3 text-center border-b">Thao tác</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100">
        @forelse($invoices as $inv)
          @php
            $booking = $inv->datPhong;
            $st = $inv->trang_thai_ui;
            $pm = $inv->phuong_thuc_ui;
          @endphp
          <tr class="hover:bg-blue-50 transition-colors">
            {{-- ID --}}
            <td class="px-4 py-3">
              <span class="font-bold text-gray-900">#{{ $inv->id }}</span>
            </td>

            {{-- Khách hàng --}}
            <td class="px-4 py-3">
              <div class="flex flex-col">
                <span class="font-medium text-gray-900">
                  {{ $booking ? ($booking->username ?? ($booking->user->ho_ten ?? 'N/A')) : 'N/A' }}
                </span>
                <span class="text-xs text-gray-500">
                  <i class="fas fa-id-card mr-1"></i>
                  {{ $booking ? ($booking->cccd ?? ($booking->user->cccd ?? 'N/A')) : 'N/A' }}
                </span>
              </div>
            </td>

            {{-- Loại phòng --}}
            <td class="px-4 py-3">
              @if($booking)
                @php
                  $roomTypes = $booking->getRoomTypes();
                @endphp
                @if(count($roomTypes) > 1)
                  <div class="flex items-center gap-2">
                    <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded">
                      {{ count($roomTypes) }} loại phòng
                    </span>
                  </div>
                @else
                  <span class="text-gray-900 font-medium">
                    {{ $booking->loaiPhong ? $booking->loaiPhong->ten_loai : 'N/A' }}
                  </span>
                @endif
              @else
                <span class="text-gray-400">N/A</span>
              @endif
            </td>

            {{-- Số lượng --}}
            <td class="px-4 py-3 text-center">
              <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded-full">
                {{ $booking ? ($booking->so_luong_da_dat ?? 1) : 1 }} phòng
              </span>
            </td>

            {{-- Tổng tiền --}}
            <td class="px-4 py-3 text-right">
              <span class="text-base font-bold text-blue-600">
                {{ number_format($inv->tong_tien, 0, ',', '.') }}₫
              </span>
            </td>

            {{-- Phương thức --}}
            <td class="px-4 py-3 text-center">
              <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $pm['bg'] }} {{ $pm['text'] }}">
                {{ $pm['label'] }}
              </span>
            </td>

            {{-- Trạng thái --}}
            <td class="px-4 py-3 text-center">
              <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold {{ $st['bg'] }} {{ $st['text'] }}">
                <i class="fas {{ $st['icon'] }}"></i>
                {{ $st['label'] }}
              </span>
            </td>

            {{-- Ngày tạo --}}
            <td class="px-4 py-3 text-center">
              <div class="flex flex-col">
                <span class="text-sm font-medium text-gray-900">{{ $inv->ngay_tao->format('d/m/Y') }}</span>
                <span class="text-xs text-gray-500">{{ $inv->ngay_tao->format('H:i') }}</span>
              </div>
            </td>

            {{-- Thao tác --}}
            <td class="px-4 py-3">
              <div class="flex justify-center items-center gap-2">
                <a href="{{ route('admin.invoices.show', $inv->id) }}" 
                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors"
                   title="Xem chi tiết">
                  <i class="fas fa-eye text-sm"></i>
                </a>
                @if($inv->trang_thai === 'cho_thanh_toan')
                  <a href="{{ route('admin.invoices.edit', $inv->id) }}" 
                     class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-yellow-100 text-yellow-600 hover:bg-yellow-200 transition-colors"
                     title="Chỉnh sửa">
                    <i class="fas fa-edit text-sm"></i>
                  </a>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="px-6 py-12 text-center">
              <div class="flex flex-col items-center justify-center">
                <div class="bg-gray-100 rounded-full p-6 mb-4">
                  <i class="fas fa-file-invoice text-gray-400 text-5xl"></i>
                </div>
                <p class="text-gray-600 text-lg font-semibold mb-2">Không có hóa đơn nào</p>
                <p class="text-gray-400 text-sm">Các hóa đơn sẽ xuất hiện ở đây khi có đặt phòng</p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  @if($invoices->hasPages())
    <div class="mt-6">{{ $invoices->links() }}</div>
  @endif
</div>
@endsection
