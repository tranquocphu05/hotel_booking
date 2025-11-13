@extends('layouts.client')



@section('title', $loaiPhong->ten_loai ?? 'Đặt phòng')

@section('client_content')
    @php
        use Carbon\Carbon;
        use Illuminate\Support\Str;

        // Lấy ngày từ old() hoặc biến truyền vào, chuyển thành Carbon
        $ngay_nhan = old('ngay_nhan', isset($checkin) ? $checkin : now()->format('Y-m-d'));
        $ngay_tra = old('ngay_tra', isset($checkout) ? $checkout : now()->addDay()->format('Y-m-d'));

        // Đảm bảo $ngay_nhan và $ngay_tra là đối tượng Carbon
        try {
            $ngay_nhan_carbon = Carbon::parse($ngay_nhan);
            $ngay_tra_carbon = Carbon::parse($ngay_tra);
        } catch (\Exception $e) {
            $ngay_nhan_carbon = now();
            $ngay_tra_carbon = now()->addDay();
        }


        // Tính số đêm (chỉ tính > 0, mặc định là 1 nếu ngày trả <= ngày nhận)
        $so_dem = $ngay_tra_carbon->greaterThan($ngay_nhan_carbon)
            ? $ngay_nhan_carbon->diffInDays($ngay_tra_carbon)
            : 1;

        // Use promotional price if available, otherwise use base price
        $gia_mot_dem = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban ?? 0;
        $tong_tien_initial = $gia_mot_dem * $so_dem; // Tổng tiền ban đầu tính bằng PHP

    @endphp
    <main class="booking-room-page">
        <div class="booking-shell container mx-auto px-4">
            <form action="{{ route('booking.submit') }}" method="POST" id="finalBookingForm"
                class="space-y-10"
                data-booking-context="true"
                data-gia-mot-dem="{{ $gia_mot_dem }}"
                data-loai-phong-id="{{ $loaiPhong->id }}">
                @csrf
                @if ($errors->any())
                    <div class="form-error-panel">
                        <div class="form-error-header">
                            <span><i class="fas fa-exclamation-triangle"></i></span>
                            <div>
                                <p>Vui lòng kiểm tra lại thông tin</p>
                                <small>Có {{ $errors->count() }} trường cần được điều chỉnh</small>
                            </div>
                        </div>
                        <ul class="form-error-list">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <input type="hidden" name="tong_tien_dat_phong" id="finalBookingPrice"
                    value="{{ $tong_tien_initial }}">

                <section class="block booking-filter">
                    <div class="filter-form">
                        <div class="filter-field">
                            <label for="ngay_nhan_input" class="filter-label">Ngày nhận *</label>
                            <div class="filter-input">
                                <span class="filter-icon"><i class="fas fa-calendar-alt"></i></span>
                                <input type="date" name="ngay_nhan"
                                    value="{{ old('ngay_nhan', isset($checkin) ? $checkin : $ngay_nhan_carbon->format('Y-m-d')) }}"
                                    class="@error('ngay_nhan') border-red-500 @enderror"
                                    id="ngay_nhan_input">
                            </div>
                            @error('ngay_nhan')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="filter-field">
                            <label for="ngay_tra_input" class="filter-label">Ngày trả *</label>
                            <div class="filter-input">
                                <span class="filter-icon"><i class="fas fa-calendar-check"></i></span>
                                <input type="date" name="ngay_tra"
                                    value="{{ old('ngay_tra', isset($checkout) ? $checkout : $ngay_tra_carbon->format('Y-m-d')) }}"
                                    class="@error('ngay_tra') border-red-500 @enderror"
                                    id="ngay_tra_input">
                            </div>
                            @error('ngay_tra')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="filter-field">
                            <label for="so_nguoi_input" class="filter-label">Số người</label>
                            <div class="filter-input">
                                <span class="filter-icon"><i class="fas fa-user-friends"></i></span>
                                <input type="text" name="so_nguoi" id="so_nguoi_input"
                                    value="{{ old('so_nguoi', isset($guests) ? $guests : 1) }}"
                                    class="@error('so_nguoi') border-red-500 @enderror">
                            </div>
                            @error('so_nguoi')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="filter-field filter-field--wide">
                            <label class="filter-label">Mã khuyến mãi / Voucher</label>
                            <div class="voucher-inline">
                                <div class="voucher-placeholder">
                                    <span class="filter-icon"><i class="fas fa-ticket-alt"></i></span>
                                    <span class="voucher-text">Nhập mã khuyến mãi / Voucher</span>
                                </div>
                                <button type="button" id="openVoucherInline" class="voucher-trigger">
                                    <i class="fas fa-ticket-alt"></i> Chọn mã
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="block block-main-hotel">
                    @php
                        // Hotel information - static content for hotel introduction
                        $hotelName = 'ORIZA HOTEL';
                        $hotelTagline = 'Trải Nghiệm Lưu Trú Đẳng Cấp Tại Trung Tâm Thành Phố';
                        $hotelDescription = 'ORIZA Hotel là điểm đến lý tưởng cho du khách muốn khám phá vẻ đẹp của thành phố với dịch vụ lưu trú cao cấp, không gian sang trọng và đội ngũ nhân viên chuyên nghiệp. Khách sạn nằm ở vị trí thuận tiện, dễ dàng di chuyển đến các điểm tham quan nổi tiếng.';
                        $hotelRating = 4.8;
                        $hotelReviewCount = 1250;
                        $hotelRatingLabel = 'Xuất sắc';
                        $hotelImage = asset('img/hero/hero-1.jpg'); // Main hotel image
                        $hotelFeatures = [
                            ['icon' => 'fas fa-wifi', 'text' => 'Wi-Fi tốc độ cao'],
                            ['icon' => 'fas fa-utensils', 'text' => 'Nhà hàng sang trọng'],
                            ['icon' => 'fas fa-spa', 'text' => 'Spa & Wellness'],
                            ['icon' => 'fas fa-swimming-pool', 'text' => 'Hồ bơi vô cực'],
                            ['icon' => 'fas fa-concierge-bell', 'text' => 'Dịch vụ 24/7'],
                            ['icon' => 'fas fa-shield-alt', 'text' => 'Bảo mật cao cấp']
                        ];
                    @endphp
                    <div class="featured-hotel-card">
                        <div class="featured-hotel-media">
                            <div class="featured-hotel-media__image">
                                <img src="{{ $hotelImage }}" alt="ORIZA Hotel - Khách sạn cao cấp">
                            </div>
                            <div class="featured-hotel-media__badges">
                                <span class="badge badge--luxury">
                                    <i class="fas fa-crown"></i> 5 Sao
                                </span>
                                <span class="badge badge--location">
                                    <i class="fas fa-map-marker-alt"></i> Trung tâm
                                </span>
                            </div>
                        </div>

                        <div class="featured-hotel-content">
                            <div class="featured-hotel-header">
                                <div>
                                    <p class="featured-hotel-eyebrow">{{ $hotelName }}</p>
                                    <h2>{{ $hotelTagline }}</h2>
                                </div>
                                <div class="featured-hotel-rating">
                                    <span class="featured-hotel-rating__badge">{{ number_format($hotelRating, 1) }}</span>
                                    <div>
                                        <p>{{ $hotelRatingLabel }}</p>
                                        <small>{{ number_format($hotelReviewCount) }} đánh giá</small>
                                    </div>
                                </div>
                            </div>

                            <p class="featured-hotel-desc">
                                {{ $hotelDescription }}
                            </p>

                            <div class="featured-hotel-meta">
                                <div>
                                    <p class="meta-label">Vị trí</p>
                                    <p class="meta-value">Trung tâm TP.HCM</p>
                                </div>
                                <div>
                                    <p class="meta-label">Lịch lưu trú</p>
                                    <p class="meta-value" id="so-dem-luu-tru">Số đêm: {{ $so_dem }} đêm</p>
                                </div>
                            </div>

                            <div class="featured-hotel-features">
                                @foreach($hotelFeatures as $feature)
                                    <span><i class="{{ $feature['icon'] }}"></i> {{ $feature['text'] }}</span>
                                @endforeach
                            </div>

                            <div class="featured-hotel-actions">
                                <button type="button" class="scroll-room-btn" id="scrollToRoomsBtn">
                                    <i class="fas fa-arrow-down"></i> Khám phá phòng nghỉ
                                </button>
                                <button type="button" class="hotel-info-btn" onclick="showHotelDetails()">
                                    <i class="fas fa-info-circle"></i> Tìm hiểu thêm
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
                @if (session('status'))
                    <div class="bg-green-100 text-green-800 p-4 rounded-lg">{{ session('status') }}</div>
                @endif

                <div class="block block-list-room">
                    <div class="list-rooms">
                        @if ($errors->has('error'))
                            <div class="alert alert-error">
                                <strong class="font-bold">Lỗi!</strong>
                                <span class="block">{{ $errors->first('error') }}</span>
                            </div>
                        @endif

                        @if ($errors->has('rooms'))
                            <div class="alert alert-error">
                                <strong class="font-bold">Lỗi!</strong>
                                <ul class="list-disc list-inside mt-1">
                                    @foreach ($errors->get('rooms') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($errors->has('rooms.*.loai_phong_id'))
                            <div class="alert alert-error">
                                <strong class="font-bold">Lỗi!</strong>
                                <ul class="list-disc list-inside mt-1">
                                    @foreach ($errors->get('rooms.*.loai_phong_id') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($errors->has('rooms.*.so_luong'))
                            <div class="alert alert-error">
                                <strong class="font-bold">Lỗi!</strong>
                                <ul class="list-disc list-inside mt-1">
                                    @foreach ($errors->get('rooms.*.so_luong') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="booking-tabs">
                            <input type="radio" name="booking-tabs" id="tabRooms" class="booking-tab-input" checked>
                            <input type="radio" name="booking-tabs" id="tabVoucher" class="booking-tab-input">

                            <div class="tab-labels">
                                <label for="tabRooms">Loại phòng</label>
                                <label for="tabVoucher">Ưu đãi & Voucher</label>
                            </div>

                            <div class="tab-panels">
                                <section class="tab-panel" data-panel="rooms">
                                    <div class="room-selection-intro">
                                        <div>
                                            <p class="room-selection-eyebrow">Bạn muốn nghỉ dưỡng ở đâu?</p>
                                            <h3 class="room-selection-title">Danh sách phòng khả dụng</h3>
                                        </div>
                                        <span class="room-selection-note">Có {{ count($allLoaiPhongs ?? []) }} loại phòng cho khung thời gian này</span>
                                    </div>

                                    <div class="room-selection-grid" id="roomSelectionGrid">
                                        @foreach ($allLoaiPhongs as $option)
                                            @php
                                                $optionPrice = $option->gia_khuyen_mai ?? $option->gia_co_ban ?? 0;
                                                $optionImage = $option->anh ? asset($option->anh) : '/img/room/room-1.jpg';
                                            @endphp
                                            <article class="room-card {{ $option->id === ($loaiPhong->id ?? null) ? 'room-card--active' : '' }}">
                                                <div class="room-card__media">
                                                    <img src="{{ $optionImage }}" alt="{{ $option->ten_loai }}">
                                                </div>
                                                <div class="room-card__content">
                                                    <div class="room-card__header">
                                                        <h4>{{ $option->ten_loai }}</h4>
                                                        <span class="room-card__tag">{{ $option->so_luong_nguoi_toi_da ?? 'Phù hợp 2-3 khách' }}</span>
                                                    </div>
                                                    <p class="room-card__desc">
                                                        {{ Str::limit($option->mo_ta ?? 'Không gian hiện đại với đầy đủ tiện nghi cho kỳ nghỉ dưỡng thư thái.', 120) }}
                                                    </p>
                                                    <div class="room-card__amenities">
                                                        <span><i class="fas fa-wifi"></i> Wifi</span>
                                                        <span><i class="fas fa-utensils"></i> Ẩm thực</span>
                                                        <span><i class="fas fa-sun"></i> View đẹp</span>
                                                    </div>
                                                    <div class="room-card__footer">
                                                        <div>
                                                            <p class="room-card__note">Giá chỉ từ</p>
                                                            <p class="room-card__price">{{ number_format($optionPrice, 0, ',', '.') }} <span>VNĐ / đêm</span></p>
                                                        </div>
                                                        <button type="button" class="choose-room-btn"
                                                                data-room-id="{{ $option->id }}"
                                                                data-room-name="{{ $option->ten_loai }}"
                                                                data-room-image="{{ $optionImage }}"
                                                                data-room-price="{{ $optionPrice }}">
                                                            Chọn phòng
                                                        </button>
                                                    </div>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>

                                    <div class="selected-room-wrapper">
                                        <div class="selected-room-header">
                                            <div>
                                                <h3>Phòng bạn đã chọn</h3>
                                                <p class="text-sm text-gray-500">Điều chỉnh số lượng hoặc thêm loại phòng khác</p>
                                            </div>
                                        </div>
                        <div id="roomsContainer" class="selected-room-list">
                            {{-- First room (default selected) --}}
                            @php
                                $displayPrice = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban;
                                                $basePrice = $loaiPhong->gia_co_ban;
                                            @endphp
                                            <div class="room-item selected-room-card selected-room-card--filled" data-room-index="0" id="room_item_0">
                                                <div class="selected-room-card__inner">
                                                    <div class="selected-room-card__header">
                                                        <h4>Loại phòng 1</h4>
                                                        <button type="button" class="selected-room-card__remove" onclick="removeRoom(0)" data-room-index="0">
                                                            <i class="fas fa-times"></i> Xóa
                                                        </button>
                                                    </div>
                                                    <div class="selected-room-card__body">
                                                        <div class="selected-room-card__details selected-room-details" id="room_details_0">
                                                            <div class="selected-room-card__media">
                                                                @if($loaiPhong->anh)
                                                                    <img src="{{ asset($loaiPhong->anh) }}" alt="{{ $loaiPhong->ten_loai }}">
                                                                @else
                                                                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                                                        <i class="fas fa-image text-gray-400 text-4xl"></i>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="selected-room-card__info">
                                                                <h5>{{ $loaiPhong->ten_loai }}</h5>
                                                                <div class="selected-room-card__price">
                                                                    @if($loaiPhong->gia_khuyen_mai)
                                                                        <span class="price-highlight">{{ number_format($loaiPhong->gia_khuyen_mai, 0, ',', '.') }} VNĐ</span>
                                                                        <span class="line-through">{{ number_format($loaiPhong->gia_co_ban, 0, ',', '.') }} VNĐ</span>
                                                                    @else
                                                                        <span class="price-highlight">{{ number_format($loaiPhong->gia_co_ban, 0, ',', '.') }} VNĐ</span>
                                                                    @endif
                                                                    <small>/ đêm</small>
                                                                </div>
                                                                <p class="text-sm text-gray-600" id="room_availability_0">
                                                                    <i class="fas fa-bed text-blue-500"></i>
                                                                    @if(isset($availableCount))
                                                                        Còn {{ max(0, $availableCount) }} phòng trống
                                                                        <span class="text-blue-500 text-xs">(từ {{ isset($checkinToUse) ? date('d/m/Y', strtotime($checkinToUse)) : '...' }} đến {{ isset($checkoutToUse) ? date('d/m/Y', strtotime($checkoutToUse)) : '...' }})</span>
                                                                    @else
                                                                        Còn {{ max(0, $loaiPhong->so_luong_phong) }} phòng (vui lòng chọn ngày để xem số phòng trống)
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <div class="quantity-section mt-4">
                                                            <label class="block text-sm font-medium mb-2">Số lượng phòng *</label>
                                                            <div class="flex items-center gap-3">
                                                                <button type="button"
                                                                    class="w-10 h-10 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md transition-colors font-bold text-lg"
                                                                    onclick="decreaseRoomQuantity(0)"
                                                                    tabindex="-1">
                                                                    −
                                                                </button>
                                                                <input type="text"
                                                                    name="rooms[0][so_luong]"
                                                                    id="room_quantity_0"
                                                                    value="{{ old('rooms.0.so_luong', 1) }}"
                                                                    data-max="{{ isset($availableCount) ? max(0, $availableCount) : max(0, $loaiPhong->so_luong_phong) }}"
                                                                    class="room-quantity w-20 text-center border-2 border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-semibold @error('rooms.0.so_luong') border-red-500 @enderror"
                                                                    onchange="updateRoomQuantity(0)">
                                                                <button type="button"
                                                                    class="w-10 h-10 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md transition-colors font-bold text-lg"
                                                                    onclick="increaseRoomQuantity(0)"
                                                                    tabindex="-1">
                                                                    +
                                                                </button>
                                                                <span class="text-sm text-gray-600 ml-2">
                                                                    / <span id="max_quantity_0">{{ isset($availableCount) ? max(0, $availableCount) : max(0, $loaiPhong->so_luong_phong) }}</span> phòng
                                                                </span>
                                                            </div>
                                                            @error('rooms.0.so_luong')
                                                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                                            @enderror
                                                            <p class="text-xs text-red-600 mt-1 hidden" id="quantity_error_0">
                                                                Số lượng không được vượt quá {{ isset($availableCount) ? max(0, $availableCount) : max(0, $loaiPhong->so_luong_phong) }} phòng
                                                            </p>
                                                        </div>

                                                        <div class="mt-3 text-sm text-gray-700">
                                                            <span class="room-subtotal font-medium">Giá: <span id="room_subtotal_0">0</span></span>
                                                        </div>

                                                        <input type="hidden" id="room_0_summary_name" value="{{ $loaiPhong->ten_loai }}">

                                                        {{-- Hidden input for room type ID --}}
                                                        <input type="hidden" name="rooms[0][loai_phong_id]" value="{{ $loaiPhong->id }}"
                                                            data-price="{{ $displayPrice }}"
                                                            data-base-price="{{ $basePrice }}"
                                                            data-room-type-name="{{ $loaiPhong->ten_loai }}"
                                                            data-room-image="{{ $loaiPhong->anh ?? '' }}"
                                                            class="room-type-select">
                                                        @error('rooms.0.loai_phong_id')
                                                            <div class="form-field-error mt-2">{{ $message }}</div>
                                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <template id="roomTypeOptionsTemplate">
                        <option value="">-- Chọn loại phòng --</option>
                        @foreach ($allLoaiPhongs as $lp)
                            @php
                                $lpDisplayPrice = $lp->gia_khuyen_mai ?? $lp->gia_co_ban ?? 0;
                            @endphp
                            <option value="{{ $lp->id }}"
                                data-price="{{ $lpDisplayPrice }}"
                                data-base-price="{{ $lp->gia_co_ban ?? 0 }}"
                                data-ten-loai="{{ $lp->ten_loai }}"
                                data-anh="{{ $lp->anh ?? '' }}"
                                data-so-luong-trong="{{ $lp->so_luong_phong }}"
                                data-gia-khuyen-mai="{{ $lp->gia_khuyen_mai ?? 0 }}"
                                data-gia-co-ban="{{ $lp->gia_co_ban ?? 0 }}">
                                {{ $lp->ten_loai }} - {{ number_format($lpDisplayPrice, 0, ',', '.') }} VND/đêm
                            </option>
                        @endforeach
                    </template>

                    @if ($errors->has('rooms.*.loai_phong_id'))
                                        <div class="form-field-error mt-4">
                                            Vui lòng chọn loại phòng hợp lệ cho từng thẻ.
                                        </div>
                                    @endif

                                    @if ($errors->has('rooms.*.so_luong'))
                                        <div class="form-field-error">
                                            Kiểm tra lại số lượng phòng đã nhập.
                                        </div>
                                    @endif
                                </section>

                                <section class="tab-panel" data-panel="voucher">
                                    <div class="voucher-panel">
                                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Ưu đãi & Mã giảm giá</h3>
                                        <p class="text-sm text-gray-600 mb-4">Chọn hoặc nhập mã ưu đãi để áp dụng cho đơn đặt phòng của bạn.</p>
                                        <a href="#" id="openVoucherLink"
                                            class="voucher-link">
                                            <span class="icon"><i class="fas fa-bolt"></i></span>
                                            <span id="voucherActionText">Chọn hoặc nhập mã giảm giá</span>
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                        <div id="voucherDisplay" class="mt-4 hidden"></div>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>

                    <aside class="block-info-book">
                        <div class="summary-box summary-stay">
                            <div>
                                <p class="summary-eyebrow">Chi tiết đặt phòng</p>
                                <p class="summary-date">{{ $checkinToUse ?? $ngay_nhan_carbon->format('d/m/Y') }} - {{ $checkoutToUse ?? $ngay_tra_carbon->format('d/m/Y') }} ({{ $so_dem }} đêm)</p>
                            </div>
                            <div id="selectedRoomsSummary" class="summary-chip hidden">
                                <span id="summaryRoomCount">0</span> phòng
                            </div>
                        </div>

                        <div class="summary-box">
                            <p class="summary-section-title">Phòng bạn đã chọn</p>
                            <div id="roomsSummaryList" class="summary-room-list summary-room-list--empty">
                                <p class="summary-room-empty">Chưa có phòng nào</p>
                            </div>
                        </div>

                        <div class="summary-box">
                            <div id="totalBeforeDiscount" class="text-sm text-gray-600 mb-1 hidden"></div>
                            <div id="discountAmountDisplay" class="text-sm text-green-600 mb-1 hidden"></div>
                            <div class="total-line">
                                <span>Tổng cộng</span>
                                <span id="totalAfterDiscount" class="total-amount">{{ number_format($tong_tien_initial) }} VNĐ</span>
                            </div>
                        </div>

                        <button type="submit" class="btn-booking-submit">
                        <button type="submit" class="btn-booking-submit">
                            <div class="btn-booking-surface">
                                <div class="btn-booking-text">
                                    <span class="btn-booking-eyebrow">Sẵn sàng hoàn tất</span>
                                    <span class="btn-booking-title">Xác nhận &amp; đặt phòng</span>
                                </div>
                            </div>
                        </button>
                        <a href="{{ url()->previous() }}" class="back-link">Quay lại</a>
                    </aside>
                </div>

                <section class="block block-contact-info">
                    <h3 class="section-title">Thông tin liên hệ</h3>
                    <div class="contact-grid">
                        <div class="contact-field @error('first_name') contact-field--error @enderror">
                            <div class="contact-label-card">
                                <span class="contact-label-icon"><i class="fas fa-user"></i></span>
                                <div>
                                    <p class="contact-label-title">Họ và tên*</p>
                                    <p class="contact-label-desc">Nhập giống giấy tờ tùy thân</p>
                                </div>
                            </div>
                            <input type="text" id="contact_first_name" name="first_name"
                                value="{{ old('first_name', auth()->check() ? auth()->user()->ho_ten : '') }}"
                                class="contact-input @error('first_name') border-red-500 @enderror" placeholder="Nhập họ tên đầy đủ">
                            @error('first_name')
                                <div class="form-field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="contact-field @error('email') contact-field--error @enderror">
                            <div class="contact-label-card">
                                <span class="contact-label-icon"><i class="fas fa-envelope"></i></span>
                                <div>
                                    <p class="contact-label-title">Địa chỉ email *</p>
                                    <p class="contact-label-desc">Nhận xác nhận đặt phòng</p>
                                </div>
                            </div>
                            <input type="text" id="contact_email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}"
                                class="contact-input @error('email') border-red-500 @enderror" placeholder="example@mail.com">
                            @error('email')
                                <div class="form-field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="contact-field @error('phone') contact-field--error @enderror">
                            <div class="contact-label-card">
                                <span class="contact-label-icon"><i class="fas fa-phone-alt"></i></span>
                                <div>
                                    <p class="contact-label-title">Số điện thoại</p>
                                    <p class="contact-label-desc">Chúng tôi sẽ liên hệ khi cần</p>
                                </div>
                            </div>
                            <input type="text" id="contact_phone" name="phone" value="{{ old('phone', auth()->user()->sdt ?? '') }}"
                                class="contact-input @error('phone') border-red-500 @enderror" placeholder="Nhập số liên hệ">
                            @error('phone')
                                <div class="form-field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="contact-field @error('cccd') contact-field--error @enderror">
                            <div class="contact-label-card">
                                <span class="contact-label-icon"><i class="fas fa-id-card"></i></span>
                                <div>
                                    <p class="contact-label-title">CCCD/CMND *</p>
                                    <p class="contact-label-desc">Thông tin phục vụ thủ tục nhận phòng</p>
                                </div>
                            </div>
                            <input type="text" id="contact_cccd" name="cccd" value="{{ old('cccd', auth()->user()->cccd ?? '') }}"
                                class="contact-input @error('cccd') border-red-500 @enderror"
                                placeholder="Nhập số CCCD/CMND">
                            @error('cccd')
                                <div class="form-field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </section>

                <input type="hidden" name="voucherCode" id="voucherCode" value="">
                <input type="hidden" name="discountValue" id="discountValue" value="0">
            </form>

            <section class="booking-guarantee">
                <div class="guarantee-item">
                    <i class="fas fa-shield-alt"></i>
                    <div>
                        <p>Đảm bảo giá tốt nhất</p>
                        <span>Đặt trực tiếp với hệ thống Mường Thanh</span>
                    </div>
                </div>
                <div class="guarantee-item">
                    <i class="fas fa-headset"></i>
                    <div>
                        <p>Hỗ trợ 24/7</p>
                        <span>Đội ngũ tư vấn luôn sẵn sàng</span>
                    </div>
                </div>
                <div class="guarantee-item">
                    <i class="fas fa-credit-card"></i>
                    <div>
                        <p>Thanh toán linh hoạt</p>
                        <span>An toàn - nhanh chóng - bảo mật</span>
                    </div>
                </div>
            </section>
        </div>
    </main>

@endsection

@push('styles')
    @vite('resources/css/booking.css')
@endpush

@push('scripts')
    <script>
        window.bookingConfig = window.bookingConfig || {};
        window.bookingConfig.routes = {
            availableCount: '{{ route('booking.available_count') }}'
        };
        window.bookingConfig.csrfToken = '{{ csrf_token() }}';
        window.bookingConfig.defaultRoomCount = {{ $loaiPhong->so_luong_phong ?? 0 }};
    </script>
    @vite('resources/js/booking.js')
@endpush
