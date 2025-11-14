@extends('layouts.admin')

@section('title', 'Thêm Dịch vụ mới')

@section('admin_content')
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-green-700">Thêm Dịch vụ mới</h2>
            <a href="{{ route('admin.service.index') }}"
                class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Quay lại
            </a>
        </div>

        <div class="bg-white shadow rounded p-8">
            <form action="{{ route('admin.service.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Tên dịch vụ:</label>
                        <input type="text" name="name" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700 placeholder-gray-400"
                            value="{{ old('name') }}" placeholder="Nhập tên dịch vụ">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Giá (VNĐ):</label>
                        <input type="number" name="price" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700 placeholder-gray-400"
                            value="{{ old('price') }}" min="0" placeholder="Nhập giá dịch vụ">
                        @error('price')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Đơn vị:</label>
                        <input type="text" name="unit" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700 placeholder-gray-400"
                            value="{{ old('unit') }}" placeholder="ví dụ: cái, suất">
                        @error('unit')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Trạng thái:</label>
                        <select name="status" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700">
                            <option value="hoat_dong" {{ old('status', 'hoat_dong') == 'hoat_dong' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="ngung" {{ old('status') == 'ngung' ? 'selected' : '' }}>Ngừng</option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-700">Mô tả:</label>
                    <textarea name="describe" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 hover:border-gray-300 bg-white text-gray-700 placeholder-gray-400 resize-none" placeholder="Mô tả ngắn về dịch vụ">{{ old('describe') }}</textarea>
                    @error('describe')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <button type="button" onclick="window.location='{{ route('admin.service.index') }}'"
                        class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-xl shadow-sm bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-300 hover:scale-105 font-medium">
                        Hủy
                    </button>
                    <button type="submit"
                        class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl shadow-lg hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-300 hover:scale-105 font-medium">
                        <i class="bi bi-plus-circle mr-2"></i>Thêm
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
