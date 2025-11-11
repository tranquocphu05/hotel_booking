@extends('layouts.admin')

@section('title', 'Quản lý Dịch vụ')

@section('admin_content')
    <div class="bg-white rounded-2xl shadow p-6 mt-8 mb-8 w-full">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
            <h2 class="text-3xl font-semibold text-blue-600 flex items-center gap-2"><i class="bi bi-building"></i>Danh sách
                dịch vụ</h2>
            <div class="flex gap-3"></div>
            <button id="btnAddService"
                class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-2 rounded-full shadow transition">
                <i class="fas fa-plus"></i>Thêm dịch vụ
            </button>
            {{-- ✅ Popup form --}}
            <div id="serviceModal"
                class="flex fixed inset-0 bg-gray-800 bg-opacity-50 items-center justify-center z-50">
                <div class="bg-white p-6 rounded-xl w-96 shadow-lg relative">
                    <h3 id="modalTitle" class="text-lg font-semibold mb-4">Thêm dịch vụ</h3>

                    <form id="serviceForm" method="POST" action="">
                        @csrf
                        <input type="hidden" id="service_id" name="id">

                        {{-- 🔹 Tên dịch vụ --}}
                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-1">Tên dịch vụ</label>
                            <input name="name" id="name" type="text"
                                class="border w-full px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-200">
                            <p class="text-red-500 text-sm mt-1 error-name hidden"></p>
                        </div>

                        {{-- 🔹 Giá --}}
                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-1">Giá (VNĐ)</label>
                            <input name="price" id="price" type="text"
                                class="border w-full px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-200">
                            <p class="text-red-500 text-sm mt-1 error-price hidden"></p>
                        </div>

                        {{-- 🔹 Đơn vị --}}
                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-1">Đơn vị</label>
                            <input name="unit" id="unit" type="text"
                                class="border w-full px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-200">
                            <p class="text-red-500 text-sm mt-1 error-unit hidden"></p>
                        </div>

                        {{-- 🔹 Mô tả --}}
                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-1">Mô tả</label>
                            <input name="describe" id="describe" type="text"
                                class="border w-full px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-200">
                            <p class="text-red-500 text-sm mt-1 error-describe hidden"></p>
                        </div>
                        {{-- 🔹 Trạng thái --}}
                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-1">Trạng thái</label>
                            <select name="status" id="status"
                                class="border w-full px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-200">
                                <option value="hoat_dong">Hoạt động</option>
                                <option value="ngung">Ngừng</option>
                            </select>
                            <p class="text-red-500 text-sm mt-1 error-status hidden"></p>
                        </div>

                        {{-- 🔹 Nút --}}
                        <div class="flex justify-end gap-2 mt-4">
                            <button type="button" id="btnCloseModal"
                                class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">Hủy</button>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Lưu</button>
                        </div>
                    </form>
                </div>
            </div>
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
                            {{-- 🟡 Nút sửa --}}
                            <button class="btn-edit bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600"
                                data-id="{{ $service->id }}" data-name="{{ $service->name }}"
                                data-price="{{ $service->price }}" data-unit="{{ $service->unit }}"
                                data-describe="{{ $service->describe }}" data-status="{{ $service->status }}">
                                Sửa
                            </button>

                            <button
                                class="btn-toggle-status px-3 py-1 rounded text-white 
        {{ $service->status === 'hoat_dong' ? 'bg-gray-500 hover:bg-gray-600' : 'bg-green-600 hover:bg-green-700' }}"
                                data-id="{{ $service->id }}" data-status="{{ $service->status }}">
                                {{ $service->status === 'hoat_dong' ? 'Ngừng' : 'Kích hoạt' }}
                            </button>

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('serviceModal');
            const btnAdd = document.getElementById('btnAddService');
            const closeBtn = document.getElementById('btnCloseModal');
            const form = document.getElementById('serviceForm');
            const title = document.getElementById('modalTitle');

            // Ẩn thông báo lỗi
            function clearErrors() {
                document.querySelectorAll('[class^="error-"]').forEach(e => {
                    e.classList.add('hidden');
                    e.textContent = '';
                });
            }

            // 🟢 Mở modal thêm mới
            btnAdd.addEventListener('click', () => {
                title.textContent = 'Thêm dịch vụ';
                form.reset();
                clearErrors();
                form.action = "{{ route('admin.service.store') }}"; // route store
                document.getElementById('service_id').value = '';
                modal.classList.remove('hidden');
            });

            // 🔴 Đóng modal
            closeBtn.addEventListener('click', () => {
                modal.classList.add('hidden');
            });

            // 🟡 Mở modal sửa
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', () => {
                    title.textContent = 'Sửa dịch vụ';
                    clearErrors();
                    const id = btn.dataset.id;

                    // 🧩 Sửa lại route đúng (chắc chắn hoạt động)
                    form.action = "{{ route('admin.service.update', ':id') }}".replace(':id', id);

                    document.getElementById('service_id').value = id;
                    document.getElementById('name').value = btn.dataset.name ?? '';
                    document.getElementById('price').value = btn.dataset.price ?? '';
                    document.getElementById('unit').value = btn.dataset.unit ?? '';
                    document.getElementById('describe').value = btn.dataset.describe ?? '';
                    document.getElementById('status').value = btn.dataset.status ?? 'hoat_dong';
                    modal.classList.remove('hidden');
                });
            });

            // 🧠 Xử lý gửi form (Thêm & Cập nhật)
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                clearErrors();

                const action = form.action;
                const formData = new FormData(form);
                const serviceId = document.getElementById('service_id').value;

                // Nếu là cập nhật → spoof method PUT
                if (serviceId) {
                    formData.set('_method', 'PUT');
                }

                try {
                    const response = await fetch(action, {
                        method: 'POST', // luôn POST vì Laravel đọc _method
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')
                                .value,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    const contentType = response.headers.get('content-type') || '';
                    const result = contentType.includes('application/json') ?
                        await response.json() : {
                            message: 'Lỗi máy chủ, phản hồi không hợp lệ.'
                        };

                    if (!response.ok) {
                        if (response.status === 422 && result.errors) {
                            Object.keys(result.errors).forEach(field => {
                                const errorElem = document.querySelector(`.error-${field}`);
                                if (errorElem) {
                                    errorElem.textContent = result.errors[field][0];
                                    errorElem.classList.remove('hidden');
                                }
                            });
                            return;
                        }

                        alert(result.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                        return;
                    }

                    alert(result.message || 'Thực hiện thành công!');
                    modal.classList.add('hidden');
                    location.reload();

                } catch (error) {
                    console.error(error);
                    alert('Có lỗi kết nối, vui lòng thử lại.');
                }
            });
        });
        // Chuyển trạng thái dịch vụ (hoạt động ↔ ngừng)
        document.querySelectorAll('.btn-toggle-status').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.dataset.id;
                const currentStatus = btn.dataset.status;

                const confirmText = currentStatus === 'hoat_dong' ?
                    'Bạn có chắc muốn NGỪNG dịch vụ này không?' :
                    'Bạn có chắc muốn KÍCH HOẠT lại dịch vụ này không?';

                if (!confirm(confirmText)) return;

                // Route chuẩn RESTful: PUT /admin/services/{id}
                const url = "{{ route('admin.service.update', ':id') }}".replace(':id', id);

                const formData = new FormData();
                formData.append('_method', 'PUT');
                formData.append('toggle', '1'); // flag báo là chuyển trạng thái

                try {
                    const response = await fetch(url, {
                        method: 'POST', // Laravel sẽ hiểu PUT nhờ _method
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')
                                .value,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        alert(result.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                        return;
                    }

                    alert(result.message || 'Cập nhật trạng thái thành công!');
                    location.reload();

                } catch (error) {
                    console.error(error);
                    alert('Không thể kết nối máy chủ, vui lòng thử lại.');
                }
            });
        });
    </script>
@endpush
