@extends('layouts.admin')

@section('title','Chi tiết Hóa đơn')

@section('admin_content')

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Thông tin Hóa đơn</h3>
                            <dl class="mt-4 space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">ID Hóa đơn</dt>
                                    <dd class="text-sm text-gray-900">{{ $invoice->id }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Ngày tạo</dt>
                                    <dd class="text-sm text-gray-900">{{ $invoice->ngay_tao }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Tổng tiền</dt>
                                    <dd class="text-sm text-gray-900 font-bold">{{ number_format($invoice->tong_tien, 0, ',', '.') }} VNĐ</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Phương thức thanh toán</dt>
                                    <dd class="text-sm text-gray-900">{{ $invoice->phuong_thuc }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Trạng thái</dt>
                                    <dd class="text-sm text-gray-900">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $invoice->trang_thai == 'da_thanh_toan' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $invoice->trang_thai }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Thông tin Khách hàng</h3>
                            <dl class="mt-4 space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Họ và tên</dt>
                                    <dd class="text-sm text-gray-900">{{ $invoice->datPhong->nguoiDung->ho_ten }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="text-sm text-gray-900">{{ $invoice->datPhong->nguoiDung->email }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Số điện thoại</dt>
                                    <dd class="text-sm text-gray-900">{{ $invoice->datPhong->nguoiDung->sdt }}</dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Thông tin Đặt phòng</h3>
                            <dl class="mt-4 space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Phòng</dt>
                                    <dd class="text-sm text-gray-900">{{ $invoice->datPhong->phong->ten_phong }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Loại phòng</dt>
                                    <dd class="text-sm text-gray-900">{{ $invoice->datPhong->phong->loaiPhong->ten_loai }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Ngày nhận</dt>
                                    <dd class="text-sm text-gray-900">{{ $invoice->datPhong->ngay_nhan }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Ngày trả</dt>
                                    <dd class="text-sm text-gray-900">{{ $invoice->datPhong->ngay_tra }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <a href="{{ route('admin.invoices.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


