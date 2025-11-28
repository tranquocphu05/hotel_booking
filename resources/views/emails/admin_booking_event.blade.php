<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo đặt phòng</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', sans-serif; color:#111827; }
        .card { max-width: 640px; margin: 0 auto; border:1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
        .header { background: #2563EB; color: #fff; padding:16px 20px; font-weight: 600; }
        .content { padding: 20px; }
        .muted { color:#6b7280; }
        .row { margin-bottom: 8px; }
        .label { width: 160px; display:inline-block; color:#374151; }
        .value { font-weight:600; }
        .footer { padding: 16px 20px; color:#6b7280; border-top:1px solid #e5e7eb; font-size: 12px; }
    </style>
    </head>
<body>
    <div class="card">
        <div class="header">
            @php
                $titleMap = [
                    'created' => 'Có đơn đặt phòng mới',
                    'pending' => 'Đơn đặt phòng chờ xác nhận',
                    'paid' => 'Khách hàng đã thanh toán thành công',
                ];
            @endphp
            {{ data_get($titleMap, $eventType, 'Thông báo đặt phòng') }}
        </div>
        <div class="content">
            <p class="muted" style="margin-top:0">Xin chào Admin,</p>
            @if($eventType === 'created')
                <p>Có <strong>đơn đặt phòng mới</strong> vừa được tạo.</p>
            @elseif($eventType === 'pending')
                <p>Một đơn đặt phòng đang ở trạng thái <strong>chờ xác nhận</strong>.</p>
            @elseif($eventType === 'paid')
                <p>Một đơn đặt phòng vừa được <strong>thanh toán thành công</strong>.</p>
            @else
                <p>Có cập nhật liên quan đến đơn đặt phòng.</p>
            @endif

            <div style="margin-top: 12px;">
                <div class="row"><span class="label">Mã đơn:</span> <span class="value">#{{ $booking->id }}</span></div>
                <div class="row"><span class="label">Khách hàng:</span> <span class="value">{{ $booking->username ?? 'N/A' }}</span></div>
                <div class="row"><span class="label">Email:</span> <span class="value">{{ $booking->email ?? 'N/A' }}</span></div>
                <div class="row"><span class="label">SĐT:</span> <span class="value">{{ $booking->sdt ?? 'N/A' }}</span></div>
                <div class="row"><span class="label">Loại phòng:</span> <span class="value">{{ optional($booking->loaiPhong)->ten_loai ?? 'N/A' }}</span></div>
                <div class="row"><span class="label">Ngày nhận - trả:</span> <span class="value">{{ date('d/m/Y', strtotime($booking->ngay_nhan)) }} → {{ date('d/m/Y', strtotime($booking->ngay_tra)) }}</span></div>
                <div class="row"><span class="label">Tổng tiền:</span> <span class="value">{{ number_format($booking->tong_tien, 0, ',', '.') }}₫</span></div>
                <div class="row"><span class="label">Trạng thái:</span> <span class="value">{{ $booking->trang_thai_label }}</span></div>
            </div>

            <p class="muted" style="margin-top: 16px;">Vui lòng truy cập trang quản trị để xem chi tiết và xử lý.</p>
        </div>
        <div class="footer">
            Email tự động từ hệ thống Ozia Hotel. Vui lòng không trả lời email này.
        </div>
    </div>
</body>
</html>


