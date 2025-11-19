# Hướng Dẫn Sử Dụng Check-in/Check-out & Dịch Vụ

## 📋 Tổng Quan

Hệ thống quản lý check-in/check-out và dịch vụ phát sinh cho phép:
- Check-in khách khi đến khách sạn
- Thêm dịch vụ trong thời gian khách ở
- Check-out và tính toán phụ phí (nếu có)
- Tự động cập nhật hóa đơn

## 🔄 Luồng Hoạt Động

```
1. BOOKING ONLINE
   ├─ Khách đặt phòng
   ├─ Thanh toán tiền phòng (VNPay)
   └─ Booking: da_xac_nhan

2. CHECK-IN
   ├─ Khách đến khách sạn
   ├─ Staff verify thông tin
   ├─ Click "Check-in Ngay"
   ├─ Ghi nhận: thoi_gian_checkin, nguoi_checkin
   └─ Room status: dang_thue

3. TRONG THỜI GIAN Ở
   ├─ Khách dùng dịch vụ (gọi điện/nhắn tin)
   ├─ Staff thêm dịch vụ vào booking
   ├─ Tự động cộng vào tổng tiền
   └─ Không cần workflow phức tạp

4. CHECK-OUT
   ├─ Staff kiểm tra phòng
   ├─ Nhập phụ phí (nếu có)
   ├─ Click "Check-out Ngay"
   ├─ Tính tổng: Tiền phòng + Dịch vụ + Phụ phí
   ├─ Ghi nhận: thoi_gian_checkout, nguoi_checkout
   └─ Room status: dang_don
```

## 🎯 Hướng Dẫn Chi Tiết

### 1. Check-in

**Điều kiện:**
- Booking phải ở trạng thái `da_xac_nhan` (đã thanh toán)
- Chưa check-in trước đó

**Các bước:**
1. Vào trang chi tiết booking
2. Tìm section "Quản Lý Check-in / Check-out"
3. Nhập ghi chú (tùy chọn): Ví dụ "Khách yêu cầu phòng tầng cao"
4. Click "Check-in Ngay"

**Kết quả:**
- Ghi nhận thời gian check-in
- Ghi nhận nhân viên xử lý
- Phòng chuyển sang trạng thái "Đang thuê"
- Có thể thêm dịch vụ

### 2. Thêm Dịch Vụ

**Điều kiện:**
- Booking đã check-in
- Chưa check-out

**Các bước:**
1. Trong trang chi tiết booking, tìm section "Dịch Vụ Phát Sinh"
2. Click "Thêm Dịch Vụ"
3. Chọn dịch vụ từ dropdown (giá tự động điền)
4. Nhập số lượng
5. Thêm ghi chú (tùy chọn): Ví dụ "Phòng 101, giao lúc 14:00"
6. Click "Thêm"

**Kết quả:**
- Dịch vụ được thêm vào booking
- Tự động cộng vào tổng tiền
- Cập nhật hóa đơn

**Xóa dịch vụ:**
- Click icon thùng rác bên cạnh dịch vụ
- Xác nhận xóa
- Tự động trừ khỏi tổng tiền

### 3. Check-out

**Điều kiện:**
- Booking đã check-in
- Chưa check-out

**Các bước:**
1. Trong section "Quản Lý Check-in / Check-out"
2. Nhập phụ phí phát sinh (nếu có): Ví dụ hư hỏng đồ đạc
3. Nhập lý do phụ phí
4. Nhập ghi chú check-out: Tình trạng phòng
5. Click "Check-out Ngay"

**Phụ phí check-out muộn (Tự động):**
- Sau 12:00 đến 18:00: 50% giá phòng
- Sau 18:00: 100% giá phòng

**Kết quả:**
- Ghi nhận thời gian check-out
- Ghi nhận nhân viên xử lý
- Tính tổng phụ phí (manual + check-out muộn)
- Cập nhật hóa đơn
- Phòng chuyển sang "Đang dọn"
- Booking chuyển sang "Đã trả phòng"

## 💰 Tính Toán Hóa Đơn

### Công Thức

```
Tổng tiền = (Tiền phòng - Giảm giá) + Tiền dịch vụ + Phụ phí

Trong đó:
- Tiền phòng: Số đêm × Giá phòng × Số lượng
- Giảm giá: Voucher (chỉ áp dụng cho tiền phòng)
- Tiền dịch vụ: Tổng các dịch vụ đã dùng
- Phụ phí: Phụ phí manual + Phụ phí check-out muộn
```

### Ví Dụ

```
Booking: 2 đêm, 1 phòng Deluxe (2,000,000đ/đêm)
Voucher: Giảm 10%

Tiền phòng: 2 × 2,000,000 = 4,000,000đ
Giảm giá: 4,000,000 × 10% = 400,000đ
Tiền phòng sau giảm: 3,600,000đ

Dịch vụ:
- Bữa sáng: 2 người × 150,000 = 300,000đ
- Massage: 1 giờ × 300,000 = 300,000đ
Tổng dịch vụ: 600,000đ

Phụ phí:
- Hư hỏng minibar: 200,000đ
- Check-out muộn 3 giờ: 2,000,000đ (50%)
Tổng phụ phí: 2,200,000đ

TỔNG CỘNG: 3,600,000 + 600,000 + 2,200,000 = 6,400,000đ
```

## 📊 Trạng Thái Phòng

| Trạng thái | Mô tả | Khi nào |
|------------|-------|---------|
| `trong` | Phòng trống | Chưa có booking hoặc đã dọn xong |
| `dang_thue` | Đang thuê | Sau khi check-in |
| `dang_don` | Đang dọn | Sau khi check-out |
| `bao_tri` | Bảo trì | Admin set manual |

## 🔐 Quyền Hạn

### Admin
- ✅ Check-in booking
- ✅ Check-out booking
- ✅ Thêm/xóa dịch vụ
- ✅ Xem tất cả booking

### Client
- ❌ Không thể check-in/check-out
- ❌ Không thể thêm dịch vụ trực tiếp
- ✅ Xem booking của mình
- ✅ Gọi điện yêu cầu dịch vụ

## 🛠️ Quản Lý Dịch Vụ

### Thêm Dịch Vụ Mới

1. Vào **Admin → Services**
2. Click "Thêm dịch vụ"
3. Nhập thông tin:
   - Tên dịch vụ
   - Giá
   - Đơn vị (người, giờ, lần, kg...)
   - Loại (ăn uống, giặt ủi, spa, vận chuyển, khác)
   - Mô tả
4. Upload ảnh (tùy chọn)
5. Lưu

### Sửa/Xóa Dịch Vụ

- Click "Sửa" để cập nhật thông tin
- Click "Ngừng hoạt động" để tạm ẩn (không xóa)
- Dịch vụ đã dùng trong booking không thể xóa

## 📱 API Endpoints

### Check-in
```
POST /admin/dat_phong/{id}/checkin
Body: {
  "ghi_chu_checkin": "Optional note"
}
```

### Check-out
```
POST /admin/dat_phong/{id}/checkout
Body: {
  "phi_phat_sinh": 200000,
  "ly_do_phi": "Hư hỏng minibar",
  "ghi_chu_checkout": "Phòng sạch sẽ"
}
```

### Thêm Dịch Vụ
```
POST /admin/booking-services
Body: {
  "dat_phong_id": 123,
  "service_id": 5,
  "quantity": 2,
  "unit_price": 150000,
  "ghi_chu": "Phòng 101"
}
```

### Xóa Dịch Vụ
```
DELETE /admin/booking-services/{id}
```

## ⚠️ Lưu Ý Quan Trọng

1. **Chỉ check-in khi khách đã thanh toán**
   - Booking phải ở trạng thái `da_xac_nhan`

2. **Không thể sửa sau khi check-out**
   - Booking đã check-out không thể thêm/xóa dịch vụ

3. **Phụ phí check-out muộn tự động**
   - Hệ thống tự tính dựa trên giờ check-out thực tế

4. **Dịch vụ tính vào hóa đơn ngay**
   - Không cần "xác nhận" hay "hoàn thành"
   - Staff ghi nhận = đã dùng

5. **Phòng tự động chuyển trạng thái**
   - Check-in → `dang_thue`
   - Check-out → `dang_don`
   - Cần set manual về `trong` sau khi dọn xong

## 🐛 Troubleshooting

### Không thể check-in
- ✅ Kiểm tra booking đã thanh toán chưa
- ✅ Kiểm tra đã check-in trước đó chưa

### Không thể thêm dịch vụ
- ✅ Kiểm tra đã check-in chưa
- ✅ Kiểm tra đã check-out chưa
- ✅ Kiểm tra dịch vụ còn hoạt động không

### Tổng tiền không đúng
- ✅ Kiểm tra BookingPriceCalculator
- ✅ Kiểm tra invoice có cập nhật không
- ✅ Reload trang để xem số mới nhất

### Phòng không chuyển trạng thái
- ✅ Kiểm tra model events trong DatPhong
- ✅ Kiểm tra log để xem lỗi

## 📞 Support

Nếu gặp vấn đề, kiểm tra:
1. **Log files**: `storage/logs/laravel.log`
2. **Database**: Kiểm tra trực tiếp trong DB
3. **Browser Console**: Xem lỗi JavaScript

---

**Version**: 1.0
**Last Updated**: 2024-11-19
**Laravel**: 12.x
