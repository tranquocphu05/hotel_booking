# Pull Request: Hệ Thống Check-in/Check-out & Quản Lý Dịch Vụ

## 📋 Tổng Quan

Thêm chức năng quản lý check-in/check-out và dịch vụ phát sinh cho hệ thống đặt phòng khách sạn.

## ✨ Tính Năng Mới

### 1. Check-in/Check-out
- ✅ Check-in khách khi đến khách sạn
- ✅ Ghi nhận thời gian và nhân viên xử lý
- ✅ Tự động cập nhật trạng thái phòng
- ✅ Check-out với tính phụ phí tự động
- ✅ Phụ phí check-out muộn (50% hoặc 100%)

### 2. Quản Lý Dịch Vụ
- ✅ Thêm dịch vụ khi khách đang ở
- ✅ 12 loại dịch vụ mẫu (ăn uống, giặt ủi, spa, vận chuyển...)
- ✅ Tự động cập nhật tổng tiền
- ✅ Xóa dịch vụ (nếu chưa check-out)
- ✅ UI thân thiện với AJAX

### 3. Tính Toán Hóa Đơn
- ✅ Tự động tính: Tiền phòng + Dịch vụ + Phụ phí
- ✅ Hỗ trợ thanh toán nhiều lần
- ✅ Tracking số tiền đã thanh toán và còn lại

## 🗄️ Database Changes

### Migrations (5 files)
1. `add_checkin_checkout_to_dat_phong_table` - 7 columns mới
2. `enhance_services_table` - Thêm loại và ảnh
3. `enhance_booking_services_table` - Thêm ghi_chu
4. `enhance_hoa_don_table` - 3 columns mới
5. `enhance_thanh_toan_table` - Thêm loại thanh toán

### Schema Updates
```sql
-- dat_phong
+ thoi_gian_checkin DATETIME
+ thoi_gian_checkout DATETIME
+ nguoi_checkin VARCHAR(255)
+ nguoi_checkout VARCHAR(255)
+ phi_phat_sinh DECIMAL(10,2)
+ ghi_chu_checkin TEXT
+ ghi_chu_checkout TEXT

-- services
+ loai ENUM('an_uong', 'giat_ui', 'spa', 'van_chuyen', 'khac')
+ anh VARCHAR(255)

-- booking_services
+ ghi_chu TEXT

-- hoa_don
+ phi_phat_sinh DECIMAL(10,2)
+ da_thanh_toan DECIMAL(15,2)
+ con_lai DECIMAL(15,2)

-- thanh_toan
+ loai ENUM('dat_coc', 'tien_phong', 'dich_vu', 'phi_phat_sinh', 'hoan_tien')
```

## 💻 Code Changes

### Models (5 files)
- `DatPhong.php` - Thêm 3 methods: canCheckin(), canCheckout(), canRequestService()
- `Service.php` - Thêm scope hoatDong()
- `BookingService.php` - Thêm casts
- `Invoice.php` - Thêm 3 fields mới
- `ThanhToan.php` - Thêm loại

### Controllers (2 files)
- `DatPhongController.php` - 2 methods mới: checkin(), checkout()
- `BookingServiceController.php` - Cập nhật validation

### Services (1 file)
- `BookingPriceCalculator.php` - Cập nhật tính toán bao gồm phụ phí

### Views (3 files)
- `_checkin_checkout.blade.php` - UI check-in/check-out
- `_booking_services.blade.php` - UI quản lý dịch vụ
- `show.blade.php` - Include 2 partials mới

### Routes (6 routes)
```php
POST   /admin/dat_phong/{id}/checkin
POST   /admin/dat_phong/{id}/checkout
GET    /admin/booking-services/{datPhongId}
POST   /admin/booking-services
PUT    /admin/booking-services/{id}
DELETE /admin/booking-services/{id}
```

### Seeders (1 file)
- `ServiceSeeder.php` - 12 dịch vụ mẫu

## 📖 Documentation
- `CHECKIN_CHECKOUT_GUIDE.md` - Hướng dẫn sử dụng đầy đủ

## 🧪 Testing

### Manual Testing Checklist
- [x] Check-in booking đã thanh toán
- [x] Thêm dịch vụ khi đang ở
- [x] Xóa dịch vụ
- [x] Check-out với phụ phí
- [x] Check-out muộn (tính phí tự động)
- [x] Tổng tiền cập nhật đúng
- [x] Trạng thái phòng chuyển đúng

### Edge Cases Tested
- [x] Không thể check-in khi chưa thanh toán
- [x] Không thể thêm dịch vụ khi chưa check-in
- [x] Không thể thêm dịch vụ sau check-out
- [x] Phụ phí check-out muộn tính đúng

## 🔒 Security
- ✅ Validation đầy đủ
- ✅ Authorization checks (chỉ admin)
- ✅ Transaction safety
- ✅ CSRF protection

## 📊 Performance
- ✅ Eager loading relationships
- ✅ AJAX cho thêm/xóa dịch vụ (không reload page)
- ✅ Optimized queries

## 🐛 Bug Fixes
- ✅ Fix conflicts trong merge
- ✅ Fix syntax errors
- ✅ Fix validation messages

## 📝 Breaking Changes
Không có breaking changes. Tất cả thay đổi đều backward compatible.

## 🚀 Deployment Notes

### Migration
```bash
php artisan migrate
php artisan db:seed --class=ServiceSeeder
```

### Cache Clear
```bash
php artisan optimize:clear
```

## 📸 Screenshots

### Check-in/Check-out UI
- Form check-in với ghi chú
- Form check-out với phụ phí
- Hiển thị thông tin đã hoàn thành

### Dịch Vụ UI
- Dropdown chọn dịch vụ
- Danh sách dịch vụ đã thêm
- Tổng tiền tự động

## 👥 Reviewers
@tranquocphu05

## 📌 Related Issues
Closes #[issue_number]

## ✅ Checklist
- [x] Code follows project conventions
- [x] All tests pass
- [x] Documentation updated
- [x] No console errors
- [x] Database migrations tested
- [x] Backward compatible
- [x] Security reviewed
- [x] Performance optimized

---

**Branch**: `phu`  
**Target**: `main`  
**Type**: Feature  
**Priority**: High
