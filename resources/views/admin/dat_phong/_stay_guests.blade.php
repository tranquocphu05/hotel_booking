@php
    $stayGuests = $booking->stayGuests ?? collect();
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    {{-- Header --}}
    <div class="px-5 py-4 border-b flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">Danh sách khách được thêm</h3>
        @if ($booking->thoi_gian_checkin && !$booking->thoi_gian_checkout)
            <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-700 font-medium">
                Đang lưu trú
            </span>
        @endif
    </div>

    <div class="p-5 space-y-6">
        {{-- Guest list --}}
        @if ($stayGuests->isEmpty())
            <div class="text-center py-6 text-sm text-gray-500">
                Chưa có khách bổ sung nào được thêm.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3  rounded-lg p-2">
    @foreach ($stayGuests as $g)
        <div class="flex items-center justify-between px-4 py-2 border rounded hover:bg-gray-50">
            
            <!-- Bên trái -->
            <div class="flex items-center gap-2 text-sm text-gray-800 truncate">
                <span class="font-medium truncate">
                    {{ $g->full_name ?? 'Khách' }}
                </span>

                <span class="text-gray-400">•</span>

                <span class="text-gray-600 whitespace-nowrap">
                    {{ $g->age ? $g->age.' tuổi' : 'Tuổi ?' }}
                </span>

                <span class="text-gray-400">•</span>

                <span class="text-gray-600 whitespace-nowrap">
                    Phòng {{ $g->phong?->so_phong ?? 'N/A' }}
                </span>
            </div>

            <!-- Bên phải -->
            <div class="flex items-center gap-4 whitespace-nowrap">
                <span class="font-semibold text-blue-600 text-sm">
                    {{ number_format($g->phi_them_nguoi ?? $g->extra_fee ?? $g->phu_phi_them ?? 0, 0, ',', '.') }}đ
                </span>

                <form
                    action="{{ route('admin.dat_phong.stay_guests.destroy', [$booking->id, $g->id]) }}"
                    method="POST">
                    @csrf
                    @method('DELETE')
                    <button class="text-xs text-red-500 hover:text-red-700">
                        Xoá
                    </button>
                </form>
            </div>
        </div>
    @endforeach
</div>

        @endif

        {{-- Add guest form --}}
        @if ($booking->thoi_gian_checkin && !$booking->thoi_gian_checkout)
            <div class="mt-8 border-t pt-6">
    <h4 class="text-sm font-semibold text-gray-800 mb-4">
        ➕ Thêm khách đang ở
    </h4>

    <form
        action="{{ route('admin.dat_phong.stay_guests.store', $booking->id) }}"
        method="POST"
        class="bg-gray-50 p-4 rounded-xl space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="text-xs text-gray-600">Họ và tên</label>
                <input name="full_name" required
                    class="mt-1 w-full rounded-lg border-gray-300 p-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="text-xs text-gray-600">Ngày sinh</label>
                <input name="dob" type="date"
                    class="mt-1 w-full rounded-lg border-gray-300 p-2">
            </div>

            <div>
                <label class="text-xs text-gray-600">Phòng</label>
                <select name="phong_id" required
                    class="mt-1 w-full rounded-lg border-gray-300 p-2">
                    @php $checkedInRooms = $booking->getCheckedInPhongs(); @endphp
                    @if ($checkedInRooms->isEmpty())
                        <option disabled>Không có phòng đang lưu trú</option>
                    @else
                        @foreach ($checkedInRooms as $p)
                            <option value="{{ $p->id }}">{{ $p->so_phong }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div>
                <label class="text-xs text-gray-600">Lý do</label>
                <input name="reason"
                    class="mt-1 w-full rounded-lg border-gray-300 p-2">
            </div>
        </div>

        <div class="flex justify-end">
            <button
                class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold">
                + Thêm khách
            </button>
        </div>
    </form>
</div>

        @endif
    </div>
</div>
