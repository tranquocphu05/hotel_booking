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

                    {{-- Chính sách hoàn tiền (chỉ hiển thị cho booking đã xác nhận) --}}
                    @if(isset($cancellationPolicy) && $cancellationPolicy)
                        <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1">
                                    <h3 class="text-sm font-semibold text-blue-900 mb-2">
                                        Chính sách hoàn tiền
                                    </h3>
                                    <div class="text-sm text-blue-800 space-y-2">
                                        <div class="flex justify-between items-center bg-white p-3 rounded">
                                            <div>
                                                <p class="font-medium">Ngày nhận phòng</p>
                                                <p class="text-xs text-gray-600">{{ date('d/m/Y', strtotime($booking->ngay_nhan)) }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-medium">Còn lại</p>
                                                <p class="text-lg font-bold text-blue-600">{{ $cancellationPolicy['days_until_checkin'] }} ngày</p>
                                            </div>
                                        </div>
                                        
                                        <div class="bg-white p-3 rounded">
                                            <p class="font-medium mb-2">Nếu hủy ngay bây giờ:</p>
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-700">Hoàn lại cho khách:</span>
                                                <span class="text-lg font-bold text-green-600">
                                                    {{ number_format($cancellationPolicy['refund_amount'], 0, ',', '.') }}₫
                                                    <span class="text-sm font-normal">({{ $cancellationPolicy['refund_percentage'] }}%)</span>
                                                </span>
                                            </div>
                                            @if($cancellationPolicy['penalty_amount'] > 0)
                                                <div class="flex justify-between items-center mt-2 pt-2 border-t border-gray-200">
                                                    <span class="text-gray-700">Phí hủy:</span>
                                                    <span class="text-lg font-bold text-red-600">
                                                        {{ number_format($cancellationPolicy['penalty_amount'], 0, ',', '.') }}₫
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        <p class="text-xs text-blue-700 italic">
                                            <strong>Lưu ý:</strong> Số tiền hoàn lại được tính theo chính sách dựa trên thời gian hủy.
                                        </p>
                                    </div>
                                </div>
                            </div>
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

                            {{-- Lý do chi tiết --}}
                            <div class="mt-4">
                                <label for="ly_do_chi_tiet" class="block text-sm font-medium text-gray-700 mb-2">
                                    Lý do chi tiết (tùy chọn)
                                </label>
                                <textarea name="ly_do_chi_tiet" id="ly_do_chi_tiet" rows="3"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="Nhập lý do chi tiết nếu cần...">{{ old('ly_do_chi_tiet') }}</textarea>
                                @error('ly_do_chi_tiet')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

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
