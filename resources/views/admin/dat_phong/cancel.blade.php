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
                                    @php
                                        $roomTypes = $booking->getRoomTypes();
                                    @endphp
                                    @if(count($roomTypes) > 1)
                                        {{ count($roomTypes) }} loại phòng
                                    @else
                                        Loại phòng {{ $booking->loaiPhong->ten_loai ?? 'N/A' }}
                                    @endif
                                </span>
                            </div>
                            <div class="text-sm text-gray-600">
                                Ngày đặt: {{ date('d/m/Y H:i', strtotime($booking->ngay_dat)) }}
                            </div>
                        </div>
                    </div>

                    {{-- Hiển thị thông tin chính sách hủy nếu booking đã thanh toán --}}
                    @if(isset($cancellationPolicy) && $cancellationPolicy)
                        <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-yellow-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Chính sách hủy phòng
                            </h3>

                            @if($cancellationPolicy['can_cancel'])
                                <div class="grid md:grid-cols-2 gap-4 mb-4">
                                    <div class="bg-white p-4 rounded-lg border border-yellow-200">
                                        <p class="text-sm text-gray-600 mb-1">Thời gian còn lại</p>
                                        <p class="text-2xl font-bold text-blue-600">
                                            @if($cancellationPolicy['days_until_checkin'] < 0)
                                                Đã qua ngày
                                            @else
                                                {{ max(0, (int)$cancellationPolicy['days_until_checkin']) }} ngày
                                            @endif
                                        </p>
                                    </div>
                                    <div class="bg-white p-4 rounded-lg border border-yellow-200">
                                        <p class="text-sm text-gray-600 mb-1">Số tiền hoàn lại</p>
                                        <p class="text-2xl font-bold text-green-600">
                                            {{ number_format($cancellationPolicy['refund_amount'], 0, ',', '.') }}₫
                                        </p>
                                        <p class="text-xs text-gray-500">({{ $cancellationPolicy['refund_percentage'] }}%)</p>
                                    </div>
                                </div>

                                @if($cancellationPolicy['penalty_amount'] > 0)
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                                        <p class="text-sm text-gray-700 mb-1">Phí hủy:</p>
                                        <p class="text-lg font-bold text-red-600">
                                            {{ number_format($cancellationPolicy['penalty_amount'], 0, ',', '.') }}₫
                                        </p>
                                    </div>
                                @endif

                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <p class="text-sm font-semibold text-gray-700 mb-2">Chính sách:</p>
                                    <p class="text-sm text-gray-600">{{ $cancellationPolicy['message'] }}</p>
                                </div>
                            @else
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <p class="text-red-700 font-semibold">{{ $cancellationPolicy['message'] }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

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
