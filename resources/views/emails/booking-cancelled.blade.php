<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Báo Hủy Đặt Phòng</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #dc2626;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .booking-info {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border-left: 4px solid #dc2626;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #6b7280;
        }
        .value {
            color: #111827;
        }
        .refund-box {
            background-color: #fef3c7;
            border: 2px solid #fbbf24;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: center;
        }
        .refund-amount {
            font-size: 24px;
            font-weight: bold;
            color: #92400e;
            margin: 10px 0;
        }
        .no-refund-box {
            background-color: #fee2e2;
            border: 2px solid #ef4444;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: center;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Thông Báo Hủy Đặt Phòng</h1>
    </div>

    <div class="content">
        <p>Kính gửi <strong>{{ $booking->username }}</strong>,</p>

        <p>Chúng tôi xin thông báo đơn đặt phòng của bạn đã được hủy bởi quản trị viên.</p>

        <div class="booking-info">
            <h3 style="margin-top: 0; color: #dc2626;">Thông Tin Đặt Phòng</h3>
            
            <div class="info-row">
                <span class="label">Mã đặt phòng:</span>
                <span class="value">#{{ $booking->id }}</span>
            </div>

            <div class="info-row">
                <span class="label">Loại phòng:</span>
                <span class="value">{{ $booking->loaiPhong->ten_loai ?? 'N/A' }}</span>
            </div>

            <div class="info-row">
                <span class="label">Ngày nhận phòng:</span>
                <span class="value">{{ \Carbon\Carbon::parse($booking->ngay_nhan)->format('d/m/Y') }}</span>
            </div>

            <div class="info-row">
                <span class="label">Ngày trả phòng:</span>
                <span class="value">{{ \Carbon\Carbon::parse($booking->ngay_tra)->format('d/m/Y') }}</span>
            </div>

            <div class="info-row">
                <span class="label">Tổng tiền:</span>
                <span class="value">{{ number_format($booking->tong_tien, 0, ',', '.') }} VNĐ</span>
            </div>

            @if($booking->ly_do_huy)
            <div class="info-row">
                <span class="label">Lý do hủy:</span>
                <span class="value">{{ $booking->ly_do_huy }}</span>
            </div>
            @endif
        </div>

        @if($refundInfo && $refundInfo['refund_amount'] > 0)
            <div class="refund-box">
                <h3 style="margin-top: 0; color: #92400e;">Thông Tin Hoàn Tiền</h3>
                <p>Theo chính sách hủy phòng, bạn sẽ được hoàn:</p>
                <div class="refund-amount">
                    {{ number_format($refundInfo['refund_amount'], 0, ',', '.') }} VNĐ
                </div>
                <p style="margin-bottom: 0;">
                    ({{ $refundInfo['refund_percentage'] }}% tổng tiền đã thanh toán)
                </p>
                <p style="font-size: 14px; color: #92400e; margin-top: 10px;">
                    Số tiền sẽ được hoàn lại vào tài khoản của bạn trong vòng 5-7 ngày làm việc.
                </p>
            </div>
        @elseif($refundInfo && $refundInfo['refund_amount'] == 0)
            <div class="no-refund-box">
                <h3 style="margin-top: 0; color: #991b1b;">Không Hoàn Tiền</h3>
                <p style="margin-bottom: 0;">
                    Theo chính sách hủy phòng, đơn đặt phòng của bạn không được hoàn tiền do hủy quá gần ngày nhận phòng.
                </p>
            </div>
        @endif

        <p>Nếu bạn có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi qua:</p>
        <ul>
            <li>Email: support@hotel.com</li>
            <li>Hotline: 1900 xxxx</li>
        </ul>

        <p>Chúng tôi rất tiếc vì sự bất tiện này và hy vọng được phục vụ bạn trong tương lai.</p>

        <div style="text-align: center;">
            <a href="{{ url('/client/dashboard') }}" class="button">Xem Lịch Sử Đặt Phòng</a>
        </div>
    </div>

    <div class="footer">
        <p>Email này được gửi tự động, vui lòng không trả lời.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>
