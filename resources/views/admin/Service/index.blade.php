@extends('layouts.admin')

@section('title', 'Quản lý Dịch vụ')

@section('admin_content')
    <div class="bg-white rounded-2xl shadow p-6 mt-8 mb-8 w-full">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
            <h2 class="text-3xl font-semibold text-blue-600 flex items-center gap-2"><i class="bi bi-building"></i>Danh sách
                dịch vụ</h2>
            <div class="flex gap-3"></div>
            <a href="{{ route('admin.service.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-2 rounded-full shadow transition">
                <i class="fas fa-plus"></i>Thêm dịch vụ
            </a>
        </div>



        {{-- Bảng --}}
        <table class="text-center min-w-full text-sm text-gray-600 border border-gray-200 rounded-lg">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">Tên dịch vụ</th>
                    <th class="px-4 py-3">Giá</th>
                    <th class="px-4 py-3">Đơn vị</th>
                    <th class="px-4 py-3">Mô tả</th>
                    <th class="px-4 py-3">Trạng thái</th>
                    <th class="px-4 py-3 text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($services as $service)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $service->id }}</td>
                        <td class="px-4 py-2">{{ $service->name }}</td>
                        <td class="px-4 py-2">{{ number_format($service->price, 0, ',', '.') }} VNĐ</td>
                        <td class="px-4 py-2">{{ $service->unit }}</td>
                        <td class="px-4 py-2">{{ $service->describe }}</td>
                        <td class="px-4 py-2">
                            <span
                                class="px-2 py-1 rounded text-xs {{ $service->status === 'hoat_dong' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700' }}">
                                {{ $service->status === 'hoat_dong' ? 'Hoạt động' : 'Ngừng' }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            {{-- 🟡 Nút sửa (full-page edit) --}}
                            <a href="{{ route('admin.service.edit', $service->id) }}" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 inline-block">
                                Sửa
                            </a>

                            <form method="POST" action="{{ route('admin.service.update', $service->id) }}" style="display:inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="toggle" value="1">
                                <button type="submit" class="px-3 py-1 rounded text-white {{ $service->status === 'hoat_dong' ? 'bg-gray-500 hover:bg-gray-600' : 'bg-green-600 hover:bg-green-700' }}">
                                    {{ $service->status === 'hoat_dong' ? 'Ngừng' : 'Kích hoạt' }}
                                </button>
                            </form>

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endsection

{{-- No modal JS: create/edit use full-page forms now. --}}
