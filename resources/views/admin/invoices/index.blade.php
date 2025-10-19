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
          <option value="">Tất cả Trạng thái</option>
          <option value="cho_thanh_toan"  @selected(request('status')=='cho_thanh_toan')>Chờ thanh toán</option>
          <option value="da_thanh_toan"   @selected(request('status')=='da_thanh_toan')>Đã thanh toán</option>
          <option value="hoan_tien"       @selected(request('status')=='hoan_tien')>Hoàn tiền</option>
        </select>
      </div>

      <div class="flex items-end">
        <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium transition-all hover:scale-105 shadow-sm">
          <i class="fas fa-filter mr-2"></i>Lọc
        </button>
      </div>
    </form>
  </div>

  {{-- Table --}}
  <div class="overflow-x-auto w-full">
    <table class="w-full text-sm text-gray-700 border border-gray-200 rounded-lg shadow-sm">
      <thead class="bg-gray-100 text-gray-800 text-xs uppercase font-semibold">
        <tr>
          <th class="px-6 py-3 text-center border-b">ID</th>
          <th class="px-6 py-3 text-center border-b">Khách hàng</th>
          <th class="px-6 py-3 text-center border-b">CCCD</th>
          <th class="px-6 py-3 text-center border-b">Loại Phòng</th>
          <th class="px-6 py-3 text-center border-b">Tên Phòng</th>
          <th class="px-6 py-3 text-center border-b">Tổng tiền</th>
          <th class="px-6 py-3 text-center border-b">Phương thức</th>
          <th class="px-6 py-3 text-center border-b">Trạng thái</th>
          <th class="px-6 py-3 text-center border-b">Ngày tạo</th>
          <th class="px-6 py-3 text-center border-b">Thao Tác</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100">
        @forelse($invoices as $inv)
          <tr class="hover:bg-gray-50 transition">
            <td class="px-6 py-4 text-center font-semibold text-gray-900">#{{ $inv->id }}</td>

            <td class="px-6 py-4 text-center font-medium">
              {{ $inv->datPhong->user->ho_ten ?? 'N/A' }}
            </td>

            <td class="px-6 py-4 text-center text-gray-600">
              {{ $inv->datPhong->user->cccd ?? 'N/A' }}
            </td>

            <td class="px-6 py-4 text-center font-medium">
              {{ $inv->datPhong->phong->loaiPhong->ten_loai ?? 'N/A' }}
            </td>

            <td class="px-6 py-4 text-center font-medium">
              {{ $inv->datPhong->phong->ten_phong ?? 'N/A' }}
            </td>

            <td class="px-6 py-4 text-center text-blue-600 font-semibold">
              {{ number_format($inv->tong_tien, 0, ',', '.') }} VNĐ
            </td>

            {{-- Phương thức --}}
            @php($pm = $inv->phuong_thuc_ui)
            <td class="px-6 py-4 text-center align-middle">
              <x-badge :label="$pm['label']" :bg="$pm['bg']" :text="$pm['text']" min="min-w-[105px]" />
            </td>

            {{-- Trạng thái --}}
            @php($st = $inv->trang_thai_ui)
            <td class="px-6 py-4 text-center align-middle">
              <x-badge :label="$st['label']" :bg="$st['bg']" :text="$st['text']" :icon="$st['icon']" min="min-w-[140px]" />
            </td>

            <td class="px-6 py-4 text-center text-gray-600">
              <div class="flex flex-col">
                <span>{{ $inv->ngay_tao->format('d/m/Y') }}</span>
                <span class="text-xs text-gray-400">{{ $inv->ngay_tao->format('H:i') }}</span>
              </div>
            </td>

            <td class="px-6 py-4 text-center">
              <div class="flex justify-center items-center gap-2">
                <a href="{{ route('admin.invoices.show', $inv->id) }}" class="text-blue-600 hover:text-blue-700 text-xs inline-flex items-center gap-1">
                  <i class="fas fa-eye"></i>
                </a>
                <a href="{{ route('admin.invoices.edit', $inv->id) }}" class="text-amber-600 hover:text-amber-700 text-xs inline-flex items-center gap-1">
                  <i class="fas fa-edit"></i>
                </a>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="10" class="px-6 py-12 text-center">
              <div class="flex flex-col items-center justify-center">
                <i class="fas fa-file-invoice text-gray-300 text-6xl mb-4"></i>
                <p class="text-gray-500 text-lg font-medium">Không có hóa đơn nào</p>
                <p class="text-gray-400 text-sm mt-2">Các hóa đơn sẽ xuất hiện ở đây khi có đặt phòng</p>
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
