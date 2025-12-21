<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn đặt phòng đã được nhận</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', sans-serif; color:#111827; }
        .card { max-width: 640px; margin: 0 auto; border:1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
        .header { background: #10B981; color: #fff; padding:16px 20px; font-weight: 600; }
        .content { padding: 20px; }
        .muted { color:#6b7280; }
        .row { margin-bottom: 8px; }
        .label { width: 160px; display:inline-block; color:#374151; }
        .value { font-weight:600; }
        .footer { padding: 16px 20px; color:#6b7280; border-top:1px solid #e5e7eb; font-size: 12px; }
        .info-box { background: #F3F4F6; padding: 16px; border-radius: 8px; margin: 16px 0; }
        .warning-box { background: #FEF3C7; padding: 16px; border-radius: 8px; margin: 16px 0; border-left: 4px solid #F59E0B; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            Đơn đặt phòng của bạn đã được nhận
        </div>
        <div class="content">
            <p class="muted" style="margin-top:0">Xin chào {{ $booking->username }},</p>
            <p>Cảm ơn bạn đã đặt phòng tại khách sạn của chúng tôi!</p>
            
            <div class="info-box">
                <h3 style="margin-top:0; color:#111827;">Thông tin đặt phòng:</h3>
                <div class="row"><span class="label">Mã đơn:</span> <span class="value">#{{ $booking->id }}</span></div>
                <div class="row"><span class="label">Loại phòng:</span> <span class="value">{{ $booking->loaiPhong->ten_loai ?? '—' }}</span></div>
                <div class="row"><span class="label">Nhận phòng:</span> <span class="value">{{ \Carbon\Carbon::parse($booking->ngay_nhan)->format('d/m/Y') }}</span></div>
                <div class="row"><span class="label">Trả phòng:</span> <span class="value">{{ \Carbon\Carbon::parse($booking->ngay_tra)->format('d/m/Y') }}</span></div>
                <div class="row"><span class="label">Số người:</span> <span class="value">{{ $booking->so_nguoi }}</span></div>
                <div class="row"><span class="label">Tổng tiền:</span> <span class="value">{{ number_format($booking->tong_tien, 0, ',', '.') }} VNĐ</span></div>
            </div>

            <div class="warning-box">
                <p style="margin:0; color:#92400E;"><strong>⚠️ Lưu ý quan trọng:</strong></p>
                <p style="margin:8px 0 0 0; color:#92400E;">Đơn đặt phòng của bạn đang ở trạng thái <strong>chờ xác nhận</strong>. Vui lòng hoàn tất thanh toán để xác nhận đặt phòng. Đơn đặt phòng sẽ tự động hủy nếu không thanh toán trong vòng 5 phút.</p>
            </div>

            <p style="margin-top: 20px;">Chúng tôi sẽ gửi email xác nhận khi đơn đặt phòng của bạn được xác nhận sau khi thanh toán thành công.</p>
        </div>
        <div class="footer">
            <p style="margin:0;">Trân trọng,<br>Đội ngũ Khách sạn</p>
        </div>
    </div>
</body>
</html>

