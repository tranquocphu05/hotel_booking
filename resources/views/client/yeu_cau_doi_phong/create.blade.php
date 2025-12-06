@extends('layouts.client')

@section('title', 'Yêu cầu đổi phòng')

@section('fullwidth_content')
    {{-- Hero giống trang phòng nghỉ --}}
    <div class="relative w-full bg-cover bg-center bg-no-repeat -mt-2"
         style="background-image: url('{{ asset('img/blog/blog-11.jpg') }}');">

        <div class="absolute inset-0 bg-black bg-opacity-50"></div>

        <div class="relative py-24 md:py-28 px-4 text-center text-white">
            <nav class="text-sm text-gray-200 mb-4">
                <a href="{{ url('/') }}" class="hover:text-[#D4AF37] transition-colors">Trang chủ</a> /
                <a href="{{ route('client.phong') }}" class="hover:text-[#D4AF37] transition-colors">Phòng nghỉ</a> /
                <span class="text-[#FFD700] font-semibold">Yêu cầu đổi phòng</span>
            </nav>

            <h1 class="text-4xl md:text-6xl font-bold mb-4">Yêu cầu đổi phòng</h1>
            <p class="text-base md:text-lg text-gray-100 max-w-3xl mx-auto">
                Nếu bạn chưa thật sự hài lòng với phòng hiện tại, hãy gửi yêu cầu đổi phòng
                và chúng tôi sẽ hỗ trợ trong thời gian sớm nhất.
            </p>
        </div>
    </div>
@endsection

@section('client_content')
<div class="bg-gradient-to-b from-gray-50 via-white to-white py-12 min-h-screen">
    <div class="max-w-5xl mx-auto px-4">

        @if (session('error'))
            <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-xl border border-red-100">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid md:grid-cols-2 gap-6 mb-8">
            {{-- Thông tin loại phòng + ảnh --}}
            <div
                class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col md:flex-row md:items-stretch">
                <div class="md:w-1/2 relative h-48 md:h-auto">
                    @php
                        $roomType = $booking->loaiPhong;
                        $roomImg = (!empty($roomType?->anh)) ? asset($roomType->anh) : asset('img/room/room-1.jpg');
                    @endphp
                    <img src="{{ $roomImg }}" alt="{{ $roomType->ten_loai ?? 'Phòng' }}"
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>
                    <div class="absolute bottom-4 left-4 right-4 flex flex-col gap-1">
                        <span
                            class="inline-flex items-center justify-between bg-black/70 text-white px-3 py-1.5 rounded-full text-xs md:text-sm">
                            <span class="font-semibold">
                                {{ $roomType->ten_loai ?? 'Loại phòng' }}
                            </span>
                            <span class="text-[#FFD700] font-bold">
                                {{ number_format($roomType->gia_hien_thi ?? $roomType->gia_co_ban ?? 0, 0, ',', '.') }}
                                <span class="text-[11px] font-normal text-gray-200">VNĐ / đêm</span>
                            </span>
                        </span>
                    </div>
                </div>
                <div class="flex-1 p-5 space-y-3 text-sm text-gray-600">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Mã đặt phòng</p>
                    <p class="text-lg font-semibold text-gray-900 mb-2">#{{ $booking->id }}</p>

                    <p class="flex justify-between">
                        <span>Loại phòng:</span>
                        <strong class="text-gray-900">{{ $roomType->ten_loai ?? 'N/A' }}</strong>
                    </p>
                    <p class="flex justify-between">
                        <span>Ngày nhận:</span>
                        <strong class="text-gray-900">{{ \Carbon\Carbon::parse($booking->ngay_nhan)->format('d/m/Y') }}</strong>
                    </p>
                    <p class="flex justify-between">
                        <span>Ngày trả:</span>
                        <strong class="text-gray-900">{{ \Carbon\Carbon::parse($booking->ngay_tra)->format('d/m/Y') }}</strong>
                    </p>
                    @if($phongHienTai)
                        <p class="flex justify-between">
                            <span>Phòng hiện tại:</span>
                            <strong class="text-gray-900">{{ $phongHienTai->ten_phong ?? ('Phòng #' . $phongHienTai->id) }}</strong>
                        </p>
                    @endif
                </div>
            </div>

            <div class="p-5 bg-[#FFF7E6] rounded-2xl border border-[#FDE3A7]">
                <h3 class="text-base font-semibold text-orange-600 mb-3">Lưu ý</h3>
                <ul class="text-sm text-gray-600 space-y-2">
                    <li>• Chỉ có thể đổi sang phòng cùng loại <strong>{{ $booking->loaiPhong->ten_loai ?? '' }}</strong>.</li>
                    <li>• Yêu cầu áp dụng cho đặt phòng đã check-in, chưa check-out.</li>
                    <li>• Vui lòng mô tả rõ lý do để chúng tôi hỗ trợ nhanh nhất.</li>
                </ul>
            </div>
        </div>

        <form method="POST" action="{{ route('client.yeu_cau_doi_phong.store', $booking->id) }}"
              class="bg-white rounded-2xl shadow-sm p-8 border border-gray-100 space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Phòng hiện tại</label>
                <select name="phong_cu_id" class="w-full border-gray-200 rounded-xl px-4 py-3 focus:ring focus:ring-orange-200 focus:border-orange-400 bg-gray-50">
                    @foreach ($booking->phongs as $p)
                        <option value="{{ $p->id }}"
                            {{ old('phong_cu_id', optional($phongHienTai)->id) == $p->id ? 'selected' : '' }}>
                            {{ $p->ten_phong ?? ('Phòng #' . $p->id) }}
                        </option>
                    @endforeach
                </select>
                @error('phong_cu_id')
                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Phòng muốn đổi sang</label>
                <select name="phong_moi_id" class="w-full border-gray-200 rounded-xl px-4 py-3 focus:ring focus:ring-orange-200 focus:border-orange-400 bg-white">
                    <option value="">-- Chọn phòng mới --</option>
                    @foreach ($availableRooms as $room)
                        <option value="{{ $room->id }}" {{ old('phong_moi_id') == $room->id ? 'selected' : '' }}>
                            {{ $room->ten_phong ?? ('Phòng #' . $room->id) }}
                            @if($room->tang) - Tầng {{ $room->tang }} @endif
                        </option>
                    @endforeach
                </select>
                @error('phong_moi_id')
                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror
                @if($availableRooms->isEmpty())
                    <p class="text-xs text-gray-500 mt-2">
                        Hiện không còn phòng trống cùng loại trong khoảng thời gian này.
                    </p>
                @else
                    <p class="text-xs text-gray-500 mt-2">
                        Danh sách chỉ hiển thị các phòng cùng loại <strong>{{ $booking->loaiPhong->ten_loai ?? '' }}</strong> đang còn trống.
                    </p>
                @endif
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Lý do đổi phòng</label>
                <textarea name="ly_do" rows="4"
                          class="w-full border-gray-200 rounded-xl px-4 py-3 focus:ring focus:ring-orange-200 focus:border-orange-400">{{ old('ly_do') }}</textarea>
                @error('ly_do')
                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-2">
                    Ví dụ: Phòng có vấn đề về tiếng ồn, muốn tầng cao hơn, muốn giường đơn/đôi,...
                </p>
            </div>

            <div class="flex gap-3 justify-end pt-4">
                <a href="{{ route('profile.edit') }}"
                   class="px-5 py-3 bg-gray-100 rounded-xl hover:bg-gray-200 text-gray-700 transition">
                    Quay lại
                </a>
                <button type="submit"
                        class="px-5 py-3 bg-orange-500 text-white rounded-xl shadow hover:bg-orange-600 transition">
                    Gửi yêu cầu
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
