# Quy Tắc Kinh Doanh - Hệ Thống Đặt Phòng Khách Sạn

## 1. Quy Tắc Đặt Phòng (Booking Rules)

### 1.1 Quy Tắc Cơ Bản
- **Đặt theo loại phòng**: Khách hàng đặt phòng theo loại, không chọn phòng cụ thể
- **Tự động gán phòng**: Hệ thống tự động gán phòng trống khi tạo booking
- **Đặt nhiều loại phòng**: Một booking có thể chứa nhiều loại phòng khác nhau
- **Thời gian tối thiểu**: Booking tối thiểu 1 đêm
- **Thời gian tối đa**: Không giới hạn thời gian đặt phòng

### 1.2 Validation Rules
```php
// Validation cho booking form
'rooms' => 'required|array|min:1',
'rooms.*.loai_phong_id' => 'required|integer|exists:loai_phong,id',
'rooms.*.so_luong' => 'required|integer|min:1|max:10',
'ngay_nhan' => 'required|date|after_or_equal:today',
'ngay_tra' => 'required|date|after:ngay_nhan',
'so_nguoi' => 'required|integer|min:1',
'username' => 'required|string|max:255|regex:/^[\p{L}\s]+$/u',
'email' => 'required|email:rfc,dns|max:255',
'sdt' => 'required|regex:/^0[0-9]{9}$/',
'cccd' => 'required|regex:/^[0-9]{12}$/',
```

### 1.3 Data Storage Structure
**Pivot Tables** (thay vì JSON):
- **`booking_rooms`**: Lưu danh sách phòng được gán cho booking
  - Columns: `dat_phong_id`, `phong_id`, `created_at`, `updated_at`
- **`booking_room_types`**: Lưu danh sách loại phòng trong booking
  - Columns: `dat_phong_id`, `loai_phong_id`, `so_luong`, `gia_rieng`, `created_at`, `updated_at`

**Legacy Support**:
- `loai_phong_id`: Loại phòng chính (backward compatibility)
- `phong_id`: Phòng cụ thể (chỉ dùng khi booking có 1 phòng duy nhất)
- Các method `getRoomTypes()` và `getPhongIds()` tự động fallback về legacy nếu pivot table rỗng

### 1.4 Availability Rules
**Nguyên tắc kiểm tra tính khả dụng**:
1. **Conflict Detection**: Sử dụng logic conflict thay vì dựa vào room status
2. **Time Overlap Logic**: 
   ```
   Conflict nếu: existing.ngay_tra > new.ngay_nhan AND existing.ngay_nhan < new.ngay_tra
   ```
3. **Status Exclusion**: Loại trừ phòng đang bảo trì (`bao_tri`)
4. **Booking Status Filter**: Chỉ tính bookings có status `cho_xac_nhan` hoặc `da_xac_nhan`
5. **Pivot Table Check**: Kiểm tra conflict qua pivot table `booking_rooms` (không dùng JSON)
6. **Exclude Current Booking**: Khi đang chỉnh sửa booking, loại trừ booking hiện tại khỏi conflict check

## 2. Quy Tắc Trạng Thái (Status Rules)

### 2.1 Booking Status Workflow
```
cho_xac_nhan (Chờ xác nhận)
    ↓ (Thanh toán thành công)
da_xac_nhan (Đã xác nhận)
    ↓ (Check-out)
da_tra (Đã trả phòng)

Các trạng thái hủy:
- da_huy (Đã hủy)
- tu_choi (Từ chối)
- thanh_toan_that_bai (Thanh toán thất bại)

Trạng thái đặc biệt:
- da_chong (Đã chống) - Admin chặn booking (có thể unblock về da_xac_nhan)
```

### 2.2 Room Status Workflow
```
trong (Trống)
    ↓ (Booking confirmed - da_xac_nhan)
dang_thue (Đang thuê)
    ↓ (Check-out - da_tra)
dang_don (Đang dọn)
    ↓ (Cleaning done - sau 1 ngày hoặc không có booking conflict)
trong (Trống)

Trạng thái đặc biệt:
- bao_tri (Bảo trì) - Không thể đặt, LUÔN không khả dụng
```

### 2.3 Status Transition Rules
**Booking Status Changes**:
- `cho_xac_nhan` → `da_xac_nhan`: Khi thanh toán thành công
- `cho_xac_nhan` → `da_huy`: Auto-cancel sau 5 phút hoặc manual cancel
- `cho_xac_nhan` → `tu_choi`: Admin từ chối
- `cho_xac_nhan` → `thanh_toan_that_bai`: Thanh toán thất bại
- `da_xac_nhan` → `da_tra`: Khi check-out
- `da_xac_nhan` → `da_huy`: Admin cancel (có thể có phí hoàn tiền)
- `da_xac_nhan` → `da_chong`: Admin chặn booking
- `da_chong` → `da_xac_nhan`: Admin unblock booking

**Terminal States** (không thể thay đổi):
- `da_tra`, `da_huy`, `tu_choi`, `thanh_toan_that_bai`

**Validation Rules**:
- Không thể hủy booking đã check-in (phải checkout trước)
- Không thể set `da_tra` mà chưa có `thoi_gian_checkout`

**Room Status Changes** (Tự động via Model Events):
- Booking `da_xac_nhan`: Phòng → `dang_thue`
- Booking `da_huy`/`tu_choi`/`thanh_toan_that_bai`: Phòng → `trong` (nếu không có booking khác conflict)
- Booking `da_tra`: Phòng → `dang_don` (luôn luôn, không phụ thuộc booking tương lai)

## 3. Quy Tắc Thanh Toán (Payment Rules)

### 3.1 Payment Timing
- **Immediate Payment**: Phải thanh toán ngay sau khi đặt phòng
- **Auto-Cancel**: Tự động hủy booking sau 5 phút (300 giây) nếu chưa thanh toán
- **Payment Methods**: Chỉ hỗ trợ VNPay gateway
- **Invoice Creation**: Invoice được tạo ngay khi booking được tạo với status `cho_thanh_toan`

### 3.2 Payment Validation
```php
// VNPay validation rules
- Verify HMAC SHA512 signature (hash_hmac('sha512', $hashData, $hashSecret))
- Validate payment amount matches invoice (prevent tampering)
- Check for duplicate payments (invoice status = 'da_thanh_toan')
- Build hash data: Sort parameters, URL encode, join with '&'
```

### 3.3 Payment Status Handling
- **Success (00)**: 
  - Update invoice to `da_thanh_toan`
  - Update booking to `da_xac_nhan`
  - Create payment record (`ThanhToan`) với status `success`
- **Cancelled (24)**: 
  - Keep booking `cho_xac_nhan`
  - Create payment record với status `cancelled`
  - Allow retry payment
- **Failed (other codes)**: 
  - Log error
  - Create payment record với status `fail`
  - Allow retry payment
- **Amount Mismatch**: 
  - Cancel transaction
  - Log warning
  - Require new payment attempt

## 4. Quy Tắc Voucher (Discount Rules)

### 4.1 Voucher Application
```php
// Voucher validation
'ma_voucher' => 'exists:voucher,ma_voucher',
'trang_thai' => 'con_han',
'so_luong' => '> 0',
'ngay_ket_thuc' => '>= today',
```

### 4.2 Voucher Restrictions
- **Usage Limit**: Mỗi voucher có số lượng sử dụng giới hạn
- **Expiry Date**: Kiểm tra ngày hết hạn
- **Minimum Order**: Có thể có điều kiện đơn hàng tối thiểu
- **Room Type Specific**: Có thể áp dụng cho loại phòng cụ thể
- **Auto Decrement**: Tự động giảm số lượng khi áp dụng
- **Auto Restore**: Tự động hoàn trả (`increment('so_luong')`) khi booking bị hủy
- **Proportional Discount**: Discount được phân bổ tỉ lệ cho các loại phòng trong booking

## 5. Quy Tắc Concurrency (Race Condition Rules)

### 5.1 Database Locking
```php
// MUST use lockForUpdate() before checking availability
$loaiPhong = LoaiPhong::lockForUpdate()->find($id);
$phong = Phong::lockForUpdate()->find($id);
```

### 5.2 Transaction Rules
**MUST use transactions for**:
- Booking creation + room assignment
- Payment processing
- Voucher application
- Multi-table updates
- Status changes
- Room assignment via pivot tables

**Transaction Pattern**:
```php
DB::transaction(function () use ($data) {
    // 1. Lock records FIRST
    $model = Model::lockForUpdate()->find($id);
    
    // 2. Validate business rules
    if (!$model->isValid()) {
        throw ValidationException::withMessages([...]);
    }
    
    // 3. Perform updates
    $model->update($data);
    
    return $model;
});
```

## 6. Quy Tắc Auto-Tracking

### 6.1 Room Availability Tracking (`so_luong_trong`)
- **Real-time Updates**: `so_luong_trong` được cập nhật real-time khi booking status thay đổi
- **Calculation Method**: 
  - Đếm phòng có `trang_thai = 'trong'`
  - Cộng thêm phòng `dang_don` KHÔNG có booking conflict trong 7 ngày tới
  - Formula: `so_luong_trong = trong_count + dang_don_available_count`
- **No Manual Increment/Decrement**: Tránh race conditions - chỉ tính lại dựa trên status thực tế
- **Recalculation Triggers**: 
  - Khi booking status thay đổi (via Model Events)
  - Khi booking bị xóa
  - Khi phòng status thay đổi

### 6.2 Model Event Rules
```php
// DatPhong Model Events
static::updating(function ($booking) {
    if ($booking->isDirty('trang_thai')) {
        // Validate status transition
        $booking->validateStatusTransition($newStatus, $oldStatus);
    }
});

static::updated(function ($booking) {
    if ($booking->isDirty('trang_thai')) {
        // Recalculate so_luong_trong cho TẤT CẢ loại phòng trong booking
        // Update room status
        // Handle voucher restoration (nếu hủy)
    }
});

static::deleted(function ($booking) {
    // Recalculate so_luong_trong cho tất cả loại phòng
    // Free up rooms
});
```

### 6.3 Auto-Cancel Mechanism
- **Middleware**: `AutoCancelExpiredBookings` chạy mỗi request
- **Check Frequency**: Mỗi 30 giây (cache-based để tránh overload)
- **Cancellation Criteria**: 
  - Booking status = `cho_xac_nhan`
  - Invoice status = `cho_thanh_toan`
  - `ngay_dat` <= (now - 5 phút)
- **Actions on Cancel**:
  - Set booking status = `da_huy`
  - Set `ly_do_huy` = 'Tự động hủy do không thanh toán sau 5 phút'
  - Detach rooms from pivot table
  - Free up rooms (nếu không có booking khác)
  - Restore voucher
  - Recalculate `so_luong_trong`

### 6.4 Auto-Clean Rooms
- **Mechanism**: Tự động chuyển phòng từ `dang_don` về `trong`
- **Check Frequency**: Mỗi 30 giây (via middleware)
- **Criteria**: 
  - Phòng có `trang_thai = 'dang_don'`
  - Đã qua 1 ngày kể từ `thoi_gian_checkout` của booking gần nhất
  - KHÔNG có booking conflict trong tương lai (ngay_nhan > today)
- **Conflict Check**: Kiểm tra qua pivot table `booking_rooms`

## 7. Quy Tắc Hủy Phòng (Cancellation Rules)

### 7.1 Cancellation Policy
**Refund Policy dựa trên số ngày trước check-in**:
- **≥ 7 ngày**: Hoàn 100% tiền đã thanh toán
- **3-6 ngày**: Hoàn 50% tiền đã thanh toán (phí hủy 50%)
- **1-2 ngày**: Hoàn 25% tiền đã thanh toán (phí hủy 75%)
- **Trong ngày**: Không hoàn tiền (phí hủy 100%)

### 7.2 Cancellation Restrictions
- **Không thể hủy**: Booking đã check-in (phải checkout trước)
- **Có thể hủy**: 
  - Booking `cho_xac_nhan` (không cần hoàn tiền vì chưa thanh toán)
  - Booking `da_xac_nhan` chưa check-in (áp dụng refund policy)
- **Refund Calculation**: Dựa trên `invoice->tong_tien` (nếu có) hoặc `booking->tong_tien`

### 7.3 Cancellation Process
1. Validate cancellation eligibility
2. Calculate refund policy
3. Update booking status = `da_huy`
4. Update invoice status = `hoan_tien` (nếu đã thanh toán)
5. Create payment record với số tiền âm (refund)
6. Detach rooms from pivot tables
7. Free up rooms (nếu không có booking khác)
8. Restore voucher
9. Recalculate `so_luong_trong` cho tất cả loại phòng

## 8. Quy Tắc Bảo Mật (Security Rules)

### 8.1 Input Validation
- **All user input MUST be validated**
- **Vietnamese error messages** cho user experience
- **Parameterized queries** để tránh SQL injection
- **CSRF protection** trên tất cả forms

### 8.2 Authorization Rules
- **Ownership Check**: User chỉ xem được booking của mình
- **Role-based Access**: Admin vs Client permissions
- **Payment Verification**: Verify VNPay HMAC SHA512 signatures
- **Amount Validation**: Prevent payment tampering (compare với invoice amount)

### 8.3 Data Protection
```php
// Sensitive data handling
- Hash passwords with bcrypt
- Encrypt payment data
- Log payment transactions securely
- Don't expose sensitive data in logs
- Verify VNPay signatures before processing
```

## 9. Quy Tắc Performance

### 9.1 Database Optimization
- **Eager Loading**: Use `with()` để tránh N+1 queries
- **Indexes**: Trên các trường thường query (`dat_phong_id`, `phong_id`, `loai_phong_id`)
- **Pagination**: Cho large datasets
- **Select Specific Columns**: Chỉ lấy columns cần thiết
- **Pivot Table Queries**: Sử dụng `whereHas('phongs')` thay vì load toàn bộ

### 9.2 Caching Rules
- **Auto-Cancel Check**: Cache 30 giây để tránh check quá thường xuyên
- **Auto-Clean Check**: Cache 30 giây
- **Room Availability**: Không cache (tính real-time để đảm bảo accuracy)

## 10. Quy Tắc Error Handling

### 10.1 Exception Handling
```php
// Error handling pattern
try {
    // Business logic
} catch (ValidationException $e) {
    // Return validation errors to user
} catch (Exception $e) {
    // Log error with context
    // Return generic error message
}
```

### 10.2 Logging Rules
- **Log Levels**: Info, Warning, Error appropriately
- **Context Information**: Include relevant data (booking_id, user_id, etc.)
- **No Sensitive Data**: Don't log passwords, payment details, full credit card numbers
- **Non-blocking**: Email failures shouldn't break main flow
- **Payment Logging**: Log VNPay callbacks, amount mismatches, signature failures

## 11. Quy Tắc Testing

### 11.1 Critical Test Cases
- **Race Conditions**: Concurrent booking attempts
- **Payment Flow**: All VNPay response scenarios (00, 24, other codes)
- **Auto-Cancel**: Expired booking cleanup (5 phút)
- **Availability Logic**: Complex conflict scenarios
- **Voucher Usage**: Edge cases và limits
- **Status Transitions**: All valid và invalid transitions
- **Cancellation Policy**: All refund percentage scenarios
- **Pivot Table Operations**: Room assignment, room type storage

### 11.2 Test Data Rules
```php
// Use Factories for consistent test data
- BookingFactory with realistic data
- RoomFactory with proper relationships
- VoucherFactory with valid constraints
- InvoiceFactory with proper booking relationships
```

## 12. Quy Tắc Deployment

### 12.1 Environment Configuration
- **Separate Configs**: Development vs Production
- **VNPay Endpoints**: Sandbox vs Production
- **Database Settings**: Connection pooling, timeouts
- **Cache Configuration**: Redis/Memcached for auto-cancel checks

### 12.2 Migration Rules
- **Backup Database**: Before major changes
- **Test Migrations**: On staging environment first
- **Rollback Plan**: Always have rollback strategy
- **Data Integrity**: Verify after migrations
- **Pivot Table Migration**: Ensure data migrated from JSON to pivot tables correctly

## 13. Quy Tắc Monitoring

### 13.1 Key Metrics
- **Booking Success Rate**: Percentage of successful bookings
- **Payment Success Rate**: VNPay transaction success
- **Auto-Cancel Rate**: Frequency of expired bookings
- **Room Occupancy**: Utilization rates
- **System Performance**: Response times, error rates
- **Availability Accuracy**: `so_luong_trong` vs actual available rooms

### 13.2 Alert Rules
- **High Error Rates**: > 5% error rate
- **Payment Failures**: > 10% payment failure rate
- **Database Issues**: Connection timeouts, slow queries
- **Auto-Cancel Spikes**: Unusual cancellation patterns
- **Amount Mismatches**: VNPay amount validation failures

---

## Tóm Tắt Quy Tắc Quan Trọng

### ⚠️ CRITICAL RULES (Không được vi phạm)
1. **ALWAYS use transactions** cho booking creation và payment
2. **ALWAYS use lockForUpdate()** trước khi check availability
3. **NEVER decrement so_luong_trong manually** - để model tự động tính lại dựa trên status
4. **ALWAYS verify VNPay signatures** (HMAC SHA512) và validate amounts
5. **NEVER expose sensitive data** trong logs hoặc error messages
6. **ALWAYS get room types BEFORE detaching** pivot relationships (để có data cho recalculation)

### 🔄 AUTO-PROCESSING RULES
1. **Auto-cancel bookings** sau 5 phút (300 giây) nếu chưa thanh toán (check mỗi 30 giây)
2. **Auto-update room status** khi booking status thay đổi (via Model Events)
3. **Auto-restore vouchers** khi booking bị hủy
4. **Auto-recalculate availability** (`so_luong_trong`) khi có thay đổi
5. **Auto-clean rooms** từ `dang_don` về `trong` sau 1 ngày (nếu không có conflict)

### 📊 DATA INTEGRITY RULES
1. **Conflict-based availability** thay vì dựa vào room status
2. **Pivot table storage** (`booking_rooms`, `booking_room_types`) thay vì JSON
3. **Legacy support** cho `loai_phong_id` và `phong_id` (backward compatibility)
4. **Audit trail** cho tất cả payment transactions
5. **Consistent state** giữa booking và room status
6. **Calculate so_luong_trong** = `trong` + `dang_don` (không conflict trong 7 ngày)

### 🔐 SECURITY RULES
1. **VNPay Signature Verification**: HMAC SHA512 với hash secret
2. **Amount Validation**: So sánh payment amount với invoice amount
3. **Duplicate Payment Prevention**: Check invoice status trước khi process
4. **Input Validation**: Tất cả user input phải được validate

---

*Tài liệu này định nghĩa các quy tắc kinh doanh cốt lõi của hệ thống. Mọi thay đổi phải tuân thủ các quy tắc này để đảm bảo tính nhất quán và bảo mật của hệ thống.*
