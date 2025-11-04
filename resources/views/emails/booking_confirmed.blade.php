<div style="font-family: Arial, Helvetica, sans-serif; font-size:14px; color:#111">
    <h2>Chúc mừng bạn đã đặt phòng thành công!</h2>
    <p>Xin chào {{ $booking->username }},</p>
    <p>Đơn đặt phòng của bạn đã được xác nhận.</p>
    <ul>
        <li>Loại phòng: {{ $booking->loaiPhong->ten_loai ?? '—' }}</li>
        <li>Nhận phòng: {{ \Carbon\Carbon::parse($booking->ngay_nhan)->format('d/m/Y') }}</li>
        <li>Trả phòng: {{ \Carbon\Carbon::parse($booking->ngay_tra)->format('d/m/Y') }}</li>
        <li>Số người: {{ $booking->so_nguoi }}</li>
        <li>Tổng tiền: {{ number_format($booking->tong_tien, 0, ',', '.') }} VNĐ</li>
    </ul>
    <p>Chúc bạn có kỳ nghỉ tuyệt vời tại khách sạn của chúng tôi!</p>
</div>









