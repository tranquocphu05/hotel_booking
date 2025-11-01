<div style="font-family: Arial, Helvetica, sans-serif; font-size:14px; color:#111">
    <h2>Hóa đơn thanh toán</h2>
    <p>Xin chào {{ $booking->username }},</p>
    <p>Chúng tôi đã nhận được thanh toán cho đơn đặt phòng của bạn.</p>
    <ul>
        <li>Phòng: {{ $booking->phong->ten_phong ?? '—' }}</li>
        <li>Nhận phòng: {{ \Carbon\Carbon::parse($booking->ngay_nhan)->format('d/m/Y') }}</li>
        <li>Trả phòng: {{ \Carbon\Carbon::parse($booking->ngay_tra)->format('d/m/Y') }}</li>
        <li>Số người: {{ $booking->so_nguoi }}</li>
        <li>Tổng tiền: {{ number_format($booking->tong_tien, 0, ',', '.') }} VNĐ</li>
        <li>Trạng thái hóa đơn: Đã thanh toán</li>
    </ul>
    <p>Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của chúng tôi.</p>
</div>









