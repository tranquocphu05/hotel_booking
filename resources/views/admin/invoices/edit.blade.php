@extends('layouts.admin')

@section('title', 'Cập nhật Hóa đơn')

@section('admin_content')
<div class="container mx-auto px-4 sm:px-8">
    <div class="py-8">
        <div>
            <h2 class="text-2xl font-semibold leading-tight">Cập nhật Hóa đơn #{{ $invoice->id }}</h2>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="-mx-4 sm:-mx-8 px-4 sm:px-8 py-4 overflow-x-auto">
            <div class="inline-block min-w-full shadow rounded-lg overflow-hidden">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('admin.invoices.update', $invoice->id) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <p><strong>Khách hàng:</strong> {{ $invoice->datPhong ? ($invoice->datPhong->username ?? ($invoice->datPhong->user->ho_ten ?? 'N/A')) : 'N/A' }}</p>
                            <p><strong>Tổng tiền:</strong> {{ number_format($invoice->tong_tien, 0, ',', '.') }} VNĐ</p>
                            <p><strong>Ngày tạo:</strong> {{ $invoice->ngay_tao->format('d/m/Y H:i') }}</p>
                        </div>

                        <div class="mb-4">
                            <label for="trang_thai" class="block text-gray-700 text-sm font-bold mb-2">Trạng thái thanh toán:</label>
                            <select name="trang_thai" id="trang_thai" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="cho_thanh_toan" {{ $invoice->trang_thai == 'cho_thanh_toan' ? 'selected' : '' }}>Chờ thanh toán</option>
                                <option value="da_thanh_toan" {{ $invoice->trang_thai == 'da_thanh_toan' ? 'selected' : '' }}>Đã thanh toán</option>
                                <option value="hoan_tien" {{ $invoice->trang_thai == 'hoan_tien' ? 'selected' : '' }}>Hoàn tiền</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-between">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Cập nhật
                            </button>
                            <a href="{{ route('admin.invoices.index') }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                                Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
