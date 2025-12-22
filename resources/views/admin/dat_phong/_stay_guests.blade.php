@php
    $stayGuests = $booking->stayGuests ?? collect();
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mt-6">
    {{-- Header --}}
    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fas fa-users text-blue-600"></i>
            <h3 class="text-base font-bold text-gray-900">Khách bổ sung</h3>
        </div>
        @if ($booking->thoi_gian_checkin && !$booking->thoi_gian_checkout)
            <span class="flex items-center gap-1.5 px-2.5 py-1 text-[10px] rounded-full bg-green-50 text-green-700 font-bold border border-green-200 uppercase tracking-wider">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                Đang lưu trú
            </span>
        @endif
    </div>

    <div class="p-5">
        {{-- Guest list --}}
        @if ($stayGuests->isEmpty())
            <div class="text-center py-8 bg-gray-50/30 border-2 border-dashed border-gray-100 rounded-xl">
                <p class="text-sm text-gray-400 font-medium">Chưa có khách bổ sung nào.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($stayGuests as $g)
                    <div class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-lg hover:border-blue-200 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">
                                {{ substr($g->full_name, 0, 1) }}
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-800">{{ $g->full_name }}</div>
                                <div class="text-[10px] text-gray-500 font-medium mt-0.5">
                                    {{ $g->age ? $g->age.' tuổi' : '?' }} • Phòng {{ $g->phong?->so_phong ?? 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">Phụ phí</div>
                                <div class="text-xs font-bold text-blue-600">
                                    {{ number_format($g->phi_them_nguoi ?? $g->extra_fee ?? 0, 0, ',', '.') }}đ
                                </div>
                            </div>

                            <form action="{{ route('admin.dat_phong.stay_guests.destroy', [$booking->id, $g->id]) }}" method="POST" onsubmit="return confirm('Xoá khách này?')">
                                @csrf
                                @method('DELETE')
                                <button class="p-1.5 text-gray-300 hover:text-red-500 transition-colors">
                                    <i class="fas fa-trash-alt text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Add guest form --}}
        @if ($booking->thoi_gian_checkin && !$booking->thoi_gian_checkout)
            <div class="mt-8 pt-6 border-t border-gray-100">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="fas fa-plus-circle"></i> Đăng ký thêm khách
                </h4>

                <form action="{{ route('admin.dat_phong.stay_guests.store', $booking->id) }}" method="POST" class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-1">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1 ml-1">Họ và tên</label>
                            <input name="full_name" required placeholder="Tên khách"
                                class="w-full rounded-lg border-gray-200 bg-white p-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1 ml-1">Ngày sinh</label>
                            <input name="dob" type="date"
                                class="w-full rounded-lg border-gray-200 bg-white p-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1 ml-1">Phòng</label>
                            <select name="phong_id" required
                                class="w-full rounded-lg border-gray-200 bg-white p-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none appearance-none cursor-pointer">
                                @php $checkedInRooms = $booking->getCheckedInPhongs(); @endphp
                                @foreach ($checkedInRooms as $p)
                                    <option value="{{ $p->id }}">{{ $p->so_phong }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1 ml-1">Ghi chú</label>
                            <input name="reason" placeholder="Lý do..."
                                class="w-full rounded-lg border-gray-200 bg-white p-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                        </div>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button class="px-6 py-2 bg-blue-600 hover:bg-gray-900 text-white rounded-lg text-xs font-bold shadow-md shadow-blue-100 transition-all flex items-center gap-2">
                            <i class="fas fa-check"></i> Xác nhận thêm
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</div>
