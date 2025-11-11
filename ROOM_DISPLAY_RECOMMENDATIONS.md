# Tư Vấn: Đặc Điểm Phòng Nên Hiển Thị trong Phần "Phòng Có Tỷ Lệ Đặt Thấp"

## Tổng Quan

Phần hiển thị "Phòng Có Tỷ Lệ Đặt Thấp" được thiết kế để thu hút khách hàng bằng cách hiển thị các phòng có ít lượt đặt trong thời gian gần đây. Đây là cơ hội để tăng doanh thu từ các phòng đang ít được quan tâm.

## Đặc Điểm Phòng Nên Hiển Thị

### 1. **Tiêu Chí Lọc Phòng (Đã Implement)**

#### A. Tỷ Lệ Đặt Phòng Thấp
- **Tiêu chí**: Tỷ lệ đặt phòng <= 30% trong 30 ngày qua
- **Lý do**: Phòng có tỷ lệ đặt thấp nghĩa là có nhiều phòng trống, cần được quảng bá để tăng occupancy rate
- **Công thức tính**: 
  ```
  Tỷ lệ đặt = (Tổng số đêm đã được đặt) / (Số phòng × Số ngày) × 100%
  ```

#### B. Phòng Đang Hoạt Động và Còn Trống
- **Tiêu chí**: 
  - `trang_thai = 'hoat_dong'`
  - `so_luong_trong > 0`
- **Lý do**: Chỉ hiển thị phòng có sẵn và đang hoạt động

#### C. Giới Hạn Số Lượng
- **Số lượng hiển thị**: Tối đa 6 phòng
- **Lý do**: Quá nhiều lựa chọn có thể gây phân tâm, 6 phòng là số lượng tối ưu cho slider

### 2. **Đặc Điểm Nên Ưu Tiên Hiển Thị**

#### A. Phòng Có Giá Khuyến Mãi (Ưu Tiên Cao)
- **Lý do**: Giá khuyến mãi tạo động lực mua hàng mạnh mẽ
- **Hiển thị**: Badge "GIẢM X%" với màu đỏ nổi bật
- **Khuyến nghị**: 
  - Nên đặt giá khuyến mãi cho các phòng có tỷ lệ đặt thấp
  - Mức giảm giá lý tưởng: 10-30%

#### B. Phòng Có Đánh Giá Cao (>= 4.8 sao)
- **Lý do**: Đánh giá cao tạo niềm tin và tăng tỷ lệ chuyển đổi
- **Hiển thị**: Badge "Xuất sắc" với màu vàng
- **Khuyến nghị**: 
  - Ưu tiên hiển thị phòng có đánh giá >= 4.5 sao
  - Hiển thị số lượng đánh giá để tăng độ tin cậy

#### C. Phòng Có Nhiều Phòng Trống
- **Lý do**: Phòng trống nhiều = cơ hội đặt phòng cao = khách hàng dễ dàng đặt được
- **Hiển thị**: "Còn X/Y phòng trống" với màu xanh lá
- **Khuyến nghị**: 
  - Ưu tiên phòng có `so_luong_trong >= 2` để đảm bảo còn phòng sau khi khách xem

### 3. **Thông Tin Nên Hiển Thị Rõ Ràng**

#### A. Thông Tin Cơ Bản
- ✅ Tên loại phòng
- ✅ Ảnh đại diện (chất lượng cao, hấp dẫn)
- ✅ Giá (ưu tiên hiển thị giá khuyến mãi nếu có)
- ✅ Đánh giá sao và số lượng đánh giá
- ✅ Số phòng trống

#### B. Thông Tin Bổ Sung (Đã Implement)
- ✅ **Tỷ lệ đặt phòng**: Hiển thị tỷ lệ đặt trong 30 ngày qua
- ✅ **Badge "Tỷ lệ đặt thấp"**: Màu xanh lá, tạo cảm giác "cơ hội hiếm"
- ✅ **Badge giảm giá**: Màu đỏ, thu hút sự chú ý
- ✅ **Badge đánh giá cao**: Màu vàng, tăng niềm tin

### 4. **Chiến Lược Hiển Thị**

#### A. Thứ Tự Sắp Xếp
1. **Ưu tiên 1**: Tỷ lệ đặt thấp nhất (booking_rate tăng dần)
2. **Ưu tiên 2**: Có giá khuyến mãi
3. **Ưu tiên 3**: Đánh giá cao
4. **Ưu tiên 4**: Nhiều phòng trống

#### B. Fallback Strategy (Đã Implement)
- Nếu không có phòng nào có tỷ lệ đặt thấp:
  - Hiển thị các phòng có giá khuyến mãi
  - Sắp xếp theo đánh giá giảm dần
- Đảm bảo luôn có nội dung để hiển thị

### 5. **Điều Chỉnh Có Thể Thực Hiện**

#### A. Thay Đổi Ngưỡng Tỷ Lệ Đặt
```php
// Trong DashboardController, có thể điều chỉnh:
$phongsUuDai = LoaiPhong::getRoomsWithLowBookingRate(
    days: 30,      // Có thể thay đổi: 14, 30, 60 ngày
    maxRate: 30.0, // Có thể thay đổi: 20%, 25%, 35%, 40%
    limit: 6       // Có thể thay đổi: 4, 6, 8 phòng
);
```

#### B. Thêm Tiêu Chí Lọc
Có thể thêm các tiêu chí như:
- Phòng có giá trong khoảng nhất định
- Phòng có diện tích lớn
- Phòng có view đẹp
- Phòng có tiện nghi đặc biệt

### 6. **Khuyến Nghị Cho Quản Trị Viên**

#### A. Quản Lý Giá Khuyến Mãi
- **Khi nào đặt giá khuyến mãi**: 
  - Phòng có tỷ lệ đặt < 30% trong 30 ngày
  - Mùa thấp điểm
  - Phòng mới đưa vào hoạt động
- **Mức giảm giá hợp lý**: 10-25% (không quá cao để tránh làm khách nghi ngờ)

#### B. Theo Dõi Hiệu Quả
- Monitor số lượng booking từ phần "Phòng Có Tỷ Lệ Đặt Thấp"
- Điều chỉnh ngưỡng tỷ lệ đặt nếu cần
- Cập nhật giá khuyến mãi theo từng giai đoạn

#### C. Tối Ưu Hóa Hình Ảnh
- Sử dụng ảnh chất lượng cao, ánh sáng tốt
- Ảnh nên thể hiện rõ điểm mạnh của phòng
- Cập nhật ảnh thường xuyên

### 7. **Đặc Điểm Phòng Lý Tưởng để Hiển Thị**

#### A. Phòng Lý Tưởng Nhất
- ✅ Tỷ lệ đặt: 0-20%
- ✅ Có giá khuyến mãi: 15-25%
- ✅ Đánh giá: >= 4.5 sao
- ✅ Số phòng trống: >= 3
- ✅ Ảnh đẹp, chất lượng cao

#### B. Phòng Vẫn Có Thể Hiển Thị
- ✅ Tỷ lệ đặt: 20-30%
- ✅ Có giá khuyến mãi: 10-15%
- ✅ Đánh giá: >= 4.0 sao
- ✅ Số phòng trống: >= 2

### 8. **Lưu Ý Kỹ Thuật**

#### A. Cache
- Dữ liệu được cache 15 phút để giảm tải server
- Cache key: `dashboard_phongs_low_booking_rate`
- Clear cache khi cần cập nhật ngay lập tức

#### B. Performance
- Method `getBookingRate()` được tối ưu với query có điều kiện
- Sử dụng `whereHas()` để filter booking đã thanh toán
- Giới hạn số lượng phòng để tránh query quá nhiều

#### C. Tính Chính Xác
- Chỉ tính booking có trạng thái `da_xac_nhan` hoặc `da_tra`
- Chỉ tính booking đã thanh toán (`invoice.trang_thai = 'da_thanh_toan'`)
- Tính dựa trên số đêm thực tế (ngày check-out - ngày check-in)

## Kết Luận

Phần "Phòng Có Tỷ Lệ Đặt Thấp" là công cụ mạnh mẽ để:
1. **Tăng doanh thu** từ các phòng đang ít được đặt
2. **Cải thiện occupancy rate** của khách sạn
3. **Tạo cơ hội** cho khách hàng tìm được phòng với giá tốt
4. **Tối ưu hóa** việc sử dụng tài sản khách sạn

**Khuyến nghị cuối cùng**: 
- Thường xuyên theo dõi và điều chỉnh ngưỡng tỷ lệ đặt
- Đặt giá khuyến mãi hợp lý cho các phòng có tỷ lệ đặt thấp
- Cập nhật ảnh và thông tin phòng thường xuyên
- Phân tích hiệu quả và điều chỉnh chiến lược

