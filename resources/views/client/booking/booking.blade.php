@extends('layouts.client')

@section('title', $loaiPhong->ten_loai ?? 'Đặt phòng')

@push('styles')
    @vite(['resources/css/booking.css'])
    @vite(['resources/css/contact-form.css'])
@endpush

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
        $gia_mot_dem = $loaiPhong->gia_khuyen_mai ?? ($loaiPhong->gia_co_ban ?? 0);
        $tong_tien_initial = $gia_mot_dem * $so_dem; // Tổng tiền ban đầu tính bằng PHP

    @endphp
    <main class="booking-room-page">
        <div class="booking-shell container mx-auto px-4">
            <form action="{{ route('booking.submit') }}" method="POST" id="finalBookingForm" class="space-y-10"
                data-booking-context="true" data-gia-mot-dem="{{ $gia_mot_dem }}"
                data-loai-phong-id="{{ $loaiPhong->id }}">
                @csrf
                @if (isset($errors) && $errors->any())
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
                <input type="hidden" name="tong_tien_dat_phong" id="finalBookingPrice" value="{{ $tong_tien_initial }}">

                <!-- Hidden inputs for room card selections -->
                <div id="roomCardHiddenInputs"></div>

                <section class="block booking-filter">
                    <div class="filter-form">
                        <div class="filter-field">
                            <label for="ngay_nhan_input" class="filter-label">Ngày nhận *</label>
                            <div class="filter-input">
                                <span class="filter-icon"><i class="fas fa-calendar-alt"></i></span>
                                <input type="date" name="ngay_nhan"
                                    value="{{ old('ngay_nhan', isset($checkin) ? $checkin : $ngay_nhan_carbon->format('Y-m-d')) }}"
                                    class="@error('ngay_nhan') border-red-500 @enderror" id="ngay_nhan_input">
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
                                    class="@error('ngay_tra') border-red-500 @enderror" id="ngay_tra_input">
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
                        $hotelDescription =
                            'ORIZA Hotel là điểm đến lý tưởng cho du khách muốn khám phá vẻ đẹp của thành phố với dịch vụ lưu trú cao cấp, không gian sang trọng và đội ngũ nhân viên chuyên nghiệp. Khách sạn nằm ở vị trí thuận tiện, dễ dàng di chuyển đến các điểm tham quan nổi tiếng.';
                        $hotelRating = 4.8;
                        $hotelReviewCount = 1250;
                        $hotelRatingLabel = 'Xuất sắc';
                        $hotelImage = asset('img/blog/blog-11.jpg'); // Main hotel image
                        $hotelFeatures = [
                            ['icon' => 'fas fa-wifi', 'text' => 'Wi-Fi tốc độ cao'],
                            ['icon' => 'fas fa-utensils', 'text' => 'Nhà hàng sang trọng'],
                            ['icon' => 'fas fa-spa', 'text' => 'Spa & Wellness'],
                            ['icon' => 'fas fa-swimming-pool', 'text' => 'Hồ bơi vô cực'],
                            ['icon' => 'fas fa-concierge-bell', 'text' => 'Dịch vụ 24/7'],
                            ['icon' => 'fas fa-shield-alt', 'text' => 'Bảo mật cao cấp'],
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
                                @foreach ($hotelFeatures as $feature)
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
                        @php
                            $errorObj = isset($errors) && is_object($errors) ? $errors : null;
                        @endphp
                        @if ($errorObj && method_exists($errorObj, 'has') && $errorObj->has('error'))
                            <div class="alert alert-error">
                                <strong class="font-bold">Lỗi!</strong>
                                <span class="block">{{ method_exists($errorObj, 'first') ? $errorObj->first('error') : '' }}</span>
                            </div>
                        @endif

                        @if ($errorObj && method_exists($errorObj, 'has') && $errorObj->has('rooms'))
                            <div class="alert alert-error">
                                <strong class="font-bold">Lỗi!</strong>
                                <ul class="list-disc list-inside mt-1">
                                    @foreach ((method_exists($errorObj, 'get')) ? $errorObj->get('rooms') : [] as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($errorObj && method_exists($errorObj, 'has') && $errorObj->has('rooms.*.loai_phong_id'))
                            <div class="alert alert-error">
                                <strong class="font-bold">Lỗi!</strong>
                                <ul class="list-disc list-inside mt-1">
                                    @foreach ((method_exists($errorObj, 'get')) ? $errorObj->get('rooms.*.loai_phong_id') : [] as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($errorObj && method_exists($errorObj, 'has') && $errorObj->has('rooms.*.so_luong'))
                            <div class="alert alert-error">
                                <strong class="font-bold">Lỗi!</strong>
                                <ul class="list-disc list-inside mt-1">
                                    @foreach ((method_exists($errorObj, 'get')) ? $errorObj->get('rooms.*.so_luong') : [] as $error)
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
                                        <span class="room-selection-note">Có {{ count($allLoaiPhongs ?? []) }} loại phòng
                                            cho khung thời gian này</span>
                                    </div>

                                    <div class="room-selection-grid" id="roomSelectionGrid">
                                        @foreach ($allLoaiPhongs as $option)
                                            @php
                                                $optionPrice = $option->gia_khuyen_mai ?? ($option->gia_co_ban ?? 0);
                                                $optionImage = $option->anh
                                                    ? asset($option->anh)
                                                    : '/img/room/room-1.jpg';

                                                // Lấy dữ liệu đánh giá cho loại phòng này
                                                $averageRating = \App\Models\Comment::where('loai_phong_id', $option->id)
                                                    ->where('trang_thai', 'hien_thi')
                                                    ->avg('so_sao') ?? 0;

                                                $totalReviews = \App\Models\Comment::where('loai_phong_id', $option->id)
                                                    ->where('trang_thai', 'hien_thi')
                                                    ->count();

                                                // Lấy 5 đánh giá gần nhất
                                                $recentReviews = \App\Models\Comment::where('loai_phong_id', $option->id)
                                                    ->where('trang_thai', 'hien_thi')
                                                    ->with('user')
                                                    ->latest('ngay_danh_gia')
                                                    ->limit(5)
                                                    ->get()
                                                    ->map(function ($comment) {
                                                        return [
                                                            'id' => $comment->id,
                                                            'user_id' => $comment->nguoi_dung_id,
                                                            'user_name' => $comment->user ? $comment->user->ho_ten : 'Anonymous',
                                                            'rating' => $comment->so_sao,
                                                            'comment' => $comment->noi_dung,
                                                            'created_at' => $comment->ngay_danh_gia ? $comment->ngay_danh_gia->format('d/m/Y') : null,
                                                            'image' => $comment->img ? asset('storage/' . $comment->img) : null
                                                        ];
                                                    });
                                            @endphp
                                            <article
                                                class="room-card {{ $option->id === ($loaiPhong->id ?? null) ? 'room-card--active' : '' }}">
                                                <div class="room-card__left">
                                                    <div class="room-card__media">
                                                        <img src="{{ $optionImage }}" alt="{{ $option->ten_loai }}">
                                                    </div>
                                                    @php
                                                        $initialAvailable = isset($roomAvailabilityMap) && isset($roomAvailabilityMap[$option->id])
                                                            ? max(0, (int)$roomAvailabilityMap[$option->id])
                                                            : (int)($option->so_luong_phong ?? 0);
                                                    @endphp
                                                    <div class="room-card__selection-area">
                                                        <div class="room-selection-wrapper">
                                                            <div class="room-quantity-card p-2.5">
                                                                <label class="flex items-center gap-1.5 mb-2 text-xs font-semibold text-gray-700" for="room_card_quantity_{{ $option->id }}">
                                                                    <div class="flex items-center justify-center w-6 h-6 rounded-md bg-blue-500 text-white shadow-sm">
                                                                        <i class="fas fa-door-open text-xs"></i>
                                                                    </div>
                                                                    <span>Số lượng phòng</span>
                                                                </label>
                                                                <div class="relative">
                                                                    <select
                                                                        id="room_card_quantity_{{ $option->id }}"
                                                                        class="room-card-quantity-modern appearance-none w-full bg-white border-2 border-blue-300 rounded-md px-3 py-2 pr-10 text-xs font-semibold text-gray-800 shadow-sm transition-all duration-200 hover:border-blue-500 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-500 cursor-pointer"
                                                                        min="0"
                                                                        max="{{ $option->so_luong_phong }}"
                                                                        data-room-id="{{ $option->id }}"
                                                                        data-room-name="{{ $option->ten_loai }}"
                                                                        data-room-price="{{ $optionPrice }}"
                                                                        data-max-quantity="{{ $initialAvailable }}"
                                                                        onchange="updateRoomCardQuantity('{{ $option->id }}')">
                                                                        @php
                                                                            $isPreselected = $option->id === ($loaiPhong->id ?? null);
                                                                        @endphp
                                                                        @for ($q = 0; $q <= $initialAvailable; $q++)
                                                                            <option value="{{ $q }}" {{ ($isPreselected && $q === 1) ? 'selected' : '' }}>{{ $q }} Phòng</option>
                                                                        @endfor
                                                                    </select>
                                                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                                        <i class="fas fa-chevron-down text-blue-500 text-xs transition-transform duration-200 dropdown-chevron"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div id="room_card_guest_rows_{{ $option->id }}" class="mt-2 hidden space-y-2">
                                                                <!-- JS will render per-room guest selectors here when quantity > 0 -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="room-card__content">
                                                    <div class="room-card__header">
                                                        <h4>{{ $option->ten_loai }}</h4>
                                                        <span
                                                            class="room-card__tag">{{ $option->so_luong_nguoi_toi_da ?? 'Phù hợp 2-3 khách' }}</span>
                                                    </div>
                                                    <p class="room-card__desc">
                                                        {{ Str::limit($option->mo_ta ?? 'Không gian hiện đại với đầy đủ tiện nghi cho kỳ nghỉ dưỡng thư thái.', 120) }}
                                                    </p>
                                                    <div class="room-card__amenities">
                                                        <span><i class="fas fa-wifi"></i> Wifi</span>
                                                        <span><i class="fas fa-utensils"></i> Ẩm thực</span>
                                                        <span><i class="fas fa-sun"></i> View đẹp</span>
                                                    </div>
                                                    <button type="button" class="view-room-details room-details-link"
                                                        data-room-id="{{ $option->id }}"
                                                        data-room-name="{{ $option->ten_loai }}"
                                                        data-room-description="{{ $option->mo_ta ?? 'Không gian hiện đại...' }}"
                                                        data-room-image="{{ $optionImage }}"
                                                        data-room-price="{{ $optionPrice }}"
                                                        data-room-size="{{ $option->dien_tich ?? '' }}m²"
                                                        data-room-amenities="{{ json_encode(explode(',', $option->tien_ich ?? '')) }}"
                                                        data-average-rating="{{ round($averageRating, 1) }}"
                                                        data-total-reviews="{{ $totalReviews }}"
                                                        data-recent-reviews="{{ $recentReviews->toJson() }}">
                                                        Xem tất cả tiện nghi
                                                    </button>
                                                    <div class="room-card__footer">
                                                        <div class="room-card__footer-top flex items-center justify-between gap-4">
                                                            <div class="room-price-section">
                                                                <p class="room-card__note text-xs text-gray-600 mb-1">Giá chỉ từ</p>
                                                                <p class="room-card__price text-lg font-bold text-orange-500">
                                                                    {{ number_format($optionPrice, 0, ',', '.') }} <span class="text-xs font-normal text-gray-600">VNĐ / đêm</span></p>
                                                            </div>
                                                        </div>
                                                        <div class="room-availability-info mt-auto" id="availability_info_{{ $option->id }}">
                                                            <div class="availability-status">
                                                                <i class="fas fa-bed text-green-500"></i>
                                                                <span class="availability-text" id="availability_{{ $option->id }}">
                                                                    Còn {{ $initialAvailable }} phòng
                                                                </span>
                                                            </div>
                                                            <div class="date-range-info" id="date_range_{{ $option->id }}">
                                                                <small class="text-gray-500">
                                                                    <i class="fas fa-calendar-alt"></i>
                                                                    Vui lòng chọn ngày để xem phòng trống
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>

                                    @if ($errorObj && method_exists($errorObj, 'has') && $errorObj->has('rooms.*.loai_phong_id'))
                                        <div class="form-field-error mt-4">
                                            Vui lòng chọn loại phòng hợp lệ cho từng thẻ.
                                        </div>
                                    @endif

                                    @if ($errorObj && method_exists($errorObj, 'has') && $errorObj->has('rooms.*.so_luong'))
                                        <div class="form-field-error">
                                            Kiểm tra lại số lượng phòng đã nhập.
                                        </div>
                                    @endif
                                </section>

                                <section class="tab-panel" data-panel="voucher">
                                    <div class="voucher-panel">
                                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Ưu đãi & Mã giảm giá</h3>
                                        <p class="text-sm text-gray-600 mb-4">Chọn hoặc nhập mã ưu đãi để áp dụng cho đơn
                                            đặt phòng của bạn.</p>
                                        <a href="#" id="openVoucherLink" class="voucher-link">
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

                    <div class="right-column-wrapper">
                        {{-- FORM THÔNG TIN LIÊN HỆ - ĐÃ DI CHUYỂN LÊN TRÊN --}}
                        <section class="block block-contact-info">
                            <div class="contact-info-header">
                                <div class="contact-header-icon">
                                    <i class="fas fa-user-edit"></i>
                                </div>
                                <div class="contact-header-content">
                                    <h3 class="contact-section-title">Thông tin liên hệ</h3>
                                    <p class="contact-section-subtitle">Vui lòng điền đầy đủ thông tin để hoàn tất đặt phòng</p>
                                </div>
                            </div>

                            <div class="contact-form-container">
                                <div class="contact-form-group @error('first_name') contact-form-group--error @enderror">
                                    <div class="contact-form-label">
                                        <div class="contact-label-wrapper">
                                            <div class="contact-label-icon">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="contact-label-content">
                                                <label for="contact_first_name" class="contact-label-title">Họ và tên <span class="required-star">*</span></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="contact-input-wrapper">
                                        <input type="text"
                                               id="contact_first_name"
                                               name="first_name"
                                               value="{{ old('first_name', auth()->check() ? auth()->user()->ho_ten : '') }}"
                                               class="contact-form-input @error('first_name') contact-form-input--error @enderror"
                                               placeholder="Nhập giống giấy tờ tùy thân"
                                               autocomplete="name">
                                        <div class="contact-input-border"></div>
                                    </div>
                                    @error('first_name')
                                        <div class="contact-form-error">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <span>{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>

                                <div class="contact-form-group @error('email') contact-form-group--error @enderror">
                                    <div class="contact-form-label">
                                        <div class="contact-label-wrapper">
                                            <div class="contact-label-icon">
                                                <i class="fas fa-envelope"></i>
                                            </div>
                                            <div class="contact-label-content">
                                                <label for="contact_email" class="contact-label-title">Địa chỉ email <span class="required-star">*</span></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="contact-input-wrapper">
                                        <input type="text"
                                               id="contact_email"
                                               name="email"
                                               value="{{ old('email', auth()->user()->email ?? '') }}"
                                               class="contact-form-input @error('email') contact-form-input--error @enderror"
                                               placeholder="Nhận xác nhận đặt phòng"
                                               autocomplete="email">
                                        <div class="contact-input-border"></div>
                                    </div>
                                    @error('email')
                                        <div class="contact-form-error">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <span>{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>

                                <div class="contact-form-group @error('phone') contact-form-group--error @enderror">
                                    <div class="contact-form-label">
                                        <div class="contact-label-wrapper">
                                            <div class="contact-label-icon">
                                                <i class="fas fa-phone-alt"></i>
                                            </div>
                                            <div class="contact-label-content">
                                                <label for="contact_phone" class="contact-label-title">Số điện thoại <span class="required-star">*</span></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="contact-input-wrapper">
                                        <input type="text"
                                               id="contact_phone"
                                               name="phone"
                                               value="{{ old('phone', auth()->user()->sdt ?? '') }}"
                                               class="contact-form-input @error('phone') contact-form-input--error @enderror"
                                               placeholder="Chúng tôi sẽ liên hệ khi cần"
                                               autocomplete="tel">
                                        <div class="contact-input-border"></div>
                                    </div>
                                    @error('phone')
                                        <div class="contact-form-error">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <span>{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>

                                <div class="contact-form-group @error('cccd') contact-form-group--error @enderror">
                                    <div class="contact-form-label">
                                        <div class="contact-label-wrapper">
                                            <div class="contact-label-icon">
                                                <i class="fas fa-id-card"></i>
                                            </div>
                                            <div class="contact-label-content">
                                                <label for="contact_cccd" class="contact-label-title">CCCD/CMND <span class="required-star">*</span></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="contact-input-wrapper">
                                        <input type="text"
                                               id="contact_cccd"
                                               name="cccd"
                                               value="{{ old('cccd', auth()->user()->cccd ?? '') }}"
                                               class="contact-form-input @error('cccd') contact-form-input--error @enderror"
                                               placeholder="Thông tin phục vụ thủ tục nhận phòng">
                                        <div class="contact-input-border"></div>
                                    </div>
                                    @error('cccd')
                                        <div class="contact-form-error">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <span>{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="contact-form-footer">
                                <div class="contact-security-note">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Thông tin của bạn được bảo mật tuyệt đối</span>
                                </div>
                            </div>
                        </section>

                        <aside class="block-info-book">
                            <div class="summary-box summary-stay">
                                <div>
                                    <p class="summary-eyebrow">Chi tiết đặt phòng</p>
                                    <p class="summary-date">{{ $checkinToUse ?? $ngay_nhan_carbon->format('d/m/Y') }} -
                                        {{ $checkoutToUse ?? $ngay_tra_carbon->format('d/m/Y') }} ({{ $so_dem }}
                                        đêm)</p>
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
                                <div id="extraGuestFeeDisplay" class="text-sm text-gray-700 mb-1 hidden"></div>
                                <div id="discountAmountDisplay" class="text-sm text-green-600 mb-1 hidden"></div>
                                <div class="total-line">
                                    <span>Tổng cộng</span>
                                    <span id="totalAfterDiscount"
                                        class="total-amount">{{ number_format($tong_tien_initial) }} VNĐ</span>
                                </div>
                            </div>

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
                </div> <input type="hidden" name="voucherCode" id="voucherCode" value="">
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
    <div id="roomDetailsModal" class="modal-overlay hidden">
        <div class="modal-container">
            <button type="button" class="modal-close-btn" id="closeModalBtn">
                <i class="fas fa-times"></i>
            </button>

            <div class="modal-body-wrapper">
                {{-- Cột trái: Ảnh + Rating + Reviews gần đây --}}
                <div class="modal-left-column">
                    {{-- Ảnh phòng --}}
                    <div class="modal-media">
                        <img id="modalRoomImage" src="" alt="Hình ảnh phòng">
                    </div>

                    {{-- Rating Summary ngay dưới ảnh --}}
                    <div class="modal-rating-summary" id="modalRatingSummary">
                        <div class="rating-display">
                            <div class="rating-stars" id="modalRatingStars">
                                {{-- Stars sẽ được load bằng JS --}}
                            </div>
                            <span class="rating-score" id="modalRatingScore">0.0/5</span>
                            <span class="rating-count" id="modalRatingCount">(0 đánh giá)</span>
                        </div>
                    </div>

                    {{-- Reviews gần đây --}}
                    <div class="modal-recent-reviews">
                        <h5 class="recent-reviews-title">Đánh giá gần đây</h5>
                        <div id="modalRecentReviews">
                            {{-- Recent reviews sẽ được load bằng JS --}}
                            <div class="no-reviews">
                                <p class="text-gray-500 text-sm">Chưa có đánh giá nào</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Cột phải: Header + Mô tả + Tiện ích + Form --}}
                <div class="modal-right-column">
                    {{-- Header với tên, kích thước và giá --}}
                    <div class="modal-header-details">
                        <h3 id="modalRoomName">Phòng Đôi</h3>
                        <div class="room-meta">
                            <span id="modalRoomSize" class="room-size"></span>
                            <span class="room-price" id="modalRoomPrice">0 VNĐ/đêm</span>
                        </div>
                    </div>

                    {{-- Mô tả ngắn gọn --}}
                    <div class="modal-description-section">
                        <p id="modalRoomDescription" class="modal-description">Giường cỡ King cao cấp,Bồn tắm năm & phòng tắm kính,Sofa phòng khách rộng rãi,Minibar, TV Smart 65 inch,Dịch vụ Butler theo yêu cầu...</p>
                        <button class="description-toggle" onclick="toggleDescription()">Xem thêm</button>
                    </div>

                    {{-- Tiện ích 3 cột --}}
                    <div class="modal-amenities-section">
                        <h4 class="amenities-title">Tiện ích trong phòng:</h4>
                        <div class="modal-amenities-grid">
                            <div class="amenity-item">
                                <i class="fas fa-door-closed"></i>
                                <span>Tủ quần áo</span>
                            </div>
                            <div class="amenity-item">
                                <i class="fas fa-smoking-ban"></i>
                                <span>Không hút thuốc</span>
                            </div>
                            <div class="amenity-item">
                                <i class="fas fa-snowflake"></i>
                                <span>Điều hòa</span>
                            </div>
                            <div class="amenity-item">
                                <i class="fas fa-wind"></i>
                                <span>Máy sấy tóc</span>
                            </div>
                            <div class="amenity-item">
                                <i class="fas fa-lock"></i>
                                <span>Két sắt</span>
                            </div>
                            <div class="amenity-item">
                                <i class="fas fa-soap"></i>
                                <span>Đồ tắm</span>
                            </div>
                            <div class="amenity-item">
                                <i class="fas fa-phone-alt"></i>
                                <span>Điện thoại</span>
                            </div>
                            <div class="amenity-item">
                                <i class="fas fa-tv"></i>
                                <span>TV cáp</span>
                            </div>
                            <div class="amenity-item">
                                <i class="fas fa-bed"></i>
                                <span>Giường đôi</span>
                            </div>
                            <div class="amenity-item">
                                <i class="fas fa-shower"></i>
                                <span>Vòi sen</span>
                            </div>
                            <div class="amenity-item">
                                <i class="fas fa-wifi"></i>
                                <span>Wifi</span>
                            </div>
                            <div class="amenity-item">
                                <i class="fas fa-coffee"></i>
                                <span>Minibar</span>
                            </div>
                        </div>
                    </div>

                    {{-- Form đánh giá có thể collapse --}}
                    <div class="modal-review-form-section">
                        <button class="review-form-toggle" onclick="toggleReviewForm()">
                            <i class="fas fa-edit"></i>
                            <span>Viết đánh giá của bạn</span>
                            <i class="fas fa-chevron-down toggle-icon"></i>
                        </button>
                        <div class="review-form-container" style="display: none;">
                            @if(isset($loaiPhong))
                                @include('client.content.comment', ['room' => $loaiPhong])
                            @else
                                <div class="no-room-data">
                                    <i class="fas fa-info-circle"></i>
                                    <p>Không thể tải form đánh giá.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
        window.bookingConfig.userId = {{ auth()->check() ? auth()->id() : 'null' }};
    </script>
    @vite('resources/js/booking.js')
@endpush
