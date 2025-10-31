@extends('layouts.admin')

@section('title', 'Hủy đặt phòng')

@section('admin_content')
    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Hủy đặt phòng</h2>

                    <div class="mb-6">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <span class="px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    Phòng {{ $booking->phong->ten_phong }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-600">
                                Ngày đặt: {{ date('d/m/Y H:i', strtotime($booking->ngay_dat)) }}
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.dat_phong.cancel.submit', $booking->id) }}" method="POST" novalidate>
                        @csrf
                        <div class="space-y-4">
                            <p class="text-sm font-medium text-gray-700 mb-3">Vui lòng chọn lý do hủy đặt phòng:</p>

                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <input id="reason1" name="ly_do" type="radio" value="thay_doi_lich_trinh" class="h-4 w-4 text-blue-600 border-gray-300" required>
                                    <label for="reason1" class="ml-3 block text-sm font-medium text-gray-700">
                                        Thay đổi lịch trình
                                    </label>
                                </div>

                                <div class="flex items-center">
                                    <input id="reason2" name="ly_do" type="radio" value="thay_doi_ke_hoach" class="h-4 w-4 text-blue-600 border-gray-300">
                                    <label for="reason2" class="ml-3 block text-sm font-medium text-gray-700">
                                        Thay đổi kế hoạch
                                    </label>
                                </div>

                                <div class="flex items-center">
                                    <input id="reason3" name="ly_do" type="radio" value="khong_phu_hop" class="h-4 w-4 text-blue-600 border-gray-300">
                                    <label for="reason3" class="ml-3 block text-sm font-medium text-gray-700">
                                        Không phù hợp với yêu cầu
                                    </label>
                                </div>

                                <div class="flex items-center">
                                    <input id="reason4" name="ly_do" type="radio" value="ly_do_khac" class="h-4 w-4 text-blue-600 border-gray-300">
                                    <label for="reason4" class="ml-3 block text-sm font-medium text-gray-700">
                                        Lý do khác
                                    </label>
                                </div>
                            </div>

                            @error('ly_do')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror

                            <div class="mt-6 flex items-center justify-end space-x-3">
                                <a href="{{ route('admin.dat_phong.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Quay lại
                                </a>
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                    Xác nhận hủy
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
