# Báo cáo Rà soát Bug Luồng Thanh Toán

**Giới thiệu:**
Sau khi rà soát luồng đặt phòng và thanh toán, tôi đã phát hiện 7 bug tiềm ẩn, tập trung vào các vấn đề về logic nghiệp vụ, xử lý lỗi và bảo mật. Các bug này có thể gây ra rủi ro tài chính, trải nghiệm người dùng kém và lỗ hổng an ninh.

**Danh sách Bug:**

| STT | Vị trí | Mô tả Bug | Mức độ nghiêm trọng | Tác động | Đề xuất khắc phục |
|-----|--------|-----------|----------------------|----------|-------------------|
| 1 | `BookingController@submit` | **Race Condition khi áp dụng Voucher:** Hệ thống kiểm tra và giảm số lượng voucher (`decrement('so_luong')`) không được bảo vệ khỏi các yêu cầu đồng thời. Nhiều người dùng có thể sử dụng cùng một mã voucher cuối cùng cùng lúc, dẫn đến số lượng voucher bị âm. | **Cao** | - Vượt quá số lượng voucher khuyến mãi.<br>- Gây thất thoát doanh thu. | Sử dụng `DB::transaction` kết hợp với `lockForUpdate()` khi truy vấn voucher để đảm bảo tính toàn vẹn dữ liệu. |
| 2 | `ThanhToanController@vnpay_return` | **Thiếu xác thực số tiền giao dịch:** Logic chỉ kiểm tra mã `vnp_ResponseCode` là '00' (thành công) mà không so sánh số tiền nhận được từ VNPAY (`vnp_Amount`) với tổng tiền của hóa đơn (`$invoice->tong_tien`). Kẻ tấn công có thể sửa đổi callback URL để gửi một số tiền nhỏ hơn nhưng vẫn được hệ thống ghi nhận là thanh toán thành công. | **Critial** | - Rủi ro mất mát tài chính nghiêm trọng.<br>- Kẻ gian có thể đặt phòng với giá rất thấp. | Trước khi cập nhật trạng thái hóa đơn, hãy thêm bước kiểm tra: `if ((float)$amount !== (float)$invoice->tong_tien) { // Xử lý lỗi }`. |
| 3 | `ThanhToanController@vnpay_return` | **Thiếu xác thực trạng thái hóa đơn:** Khi xử lý callback từ VNPAY, hệ thống không kiểm tra trạng thái hiện tại của hóa đơn. Nếu người dùng thanh toán thành công, sau đó quay lại trang thanh toán và thử lại (ví dụ: double-click), hệ thống có thể ghi nhận một giao dịch `ThanhToan` thành công thứ hai cho cùng một hóa đơn. | **Trung bình** | - Dữ liệu thanh toán bị trùng lặp, gây khó khăn cho việc đối soát.<br>- Có thể gây nhầm lẫn cho bộ phận kế toán. | Thêm điều kiện kiểm tra `if ($invoice->trang_thai === 'da_thanh_toan') { // Bỏ qua xử lý }` ngay sau khi tìm thấy hóa đơn. |
| 4 | `ThanhToanController@create_vnpay_payment` | **Mã giao dịch (`vnp_TxnRef`) có thể đoán được:** Hệ thống sử dụng ID của đơn đặt phòng (`$datPhong->id`) làm mã tham chiếu giao dịch. ID này thường là một số tự tăng, dễ đoán. Kẻ tấn công có thể thử các ID khác nhau để truy vấn trạng thái giao dịch của người khác (nếu cổng thanh toán có API hỗ trợ). | **Thấp** | - Lộ thông tin về sự tồn tại của một đơn hàng.<br>- Không gây mất mát tài chính trực tiếp nhưng là một rủi ro bảo mật. | Tạo một mã tham chiếu giao dịch ngẫu nhiên, duy nhất (ví dụ: sử dụng UUID) và lưu nó vào bảng `invoices`. Sử dụng mã này thay vì ID. |
| 5 | `ThanhToanController@vnpay_return` | **Không cập nhật trạng thái `DatPhong` khi thanh toán thành công:** Logic chỉ cập nhật trạng thái của `Invoice` thành `da_thanh_toan` mà quên cập nhật trạng thái của `DatPhong` (ví dụ: từ `cho_xac_nhan` thành `da_xac_nhan`). | **Cao** | - Đơn đặt phòng vẫn ở trạng thái chờ, có thể bị hủy nhầm bởi hệ thống hoặc admin.<br>- Người dùng không nhận được xác nhận cuối cùng, gây trải nghiệm kém. | Trong hàm `handleSuccessfulPayment`, thêm logic để cập nhật `DatPhong`: `$invoice->datPhong()->update(['trang_thai' => 'da_xac_nhan']);`. |
| 6 | `BookingController@submit` | **Kiểm tra phòng đã đặt không đủ chặt chẽ:** Logic `whereNotIn('trang_thai', ['da_huy', 'tu_choi'])` bỏ qua trạng thái `cho_thanh_toan`. Nếu một người dùng đặt phòng và đang ở trang thanh toán (chưa hoàn tất), một người dùng khác vẫn có thể đặt cùng phòng trong cùng khoảng thời gian đó. | **Cao** | - Overbooking: Hai người dùng đặt cùng một phòng.<br>- Gây xung đột vận hành và trải nghiệm khách hàng tồi tệ. | Thêm trạng thái `cho_thanh_toan` vào danh sách loại trừ, hoặc thay đổi logic để chỉ cho phép các đơn hàng có trạng thái `da_xac_nhan` mới thực sự giữ phòng. |
| 7 | `ThanhToanController@processVnpayPayment` | **Xử lý ngoại lệ quá chung chung:** Khối `catch (\Exception $e)` bắt tất cả các lỗi (ví dụ: không tìm thấy đơn hàng, lỗi database) và chỉ trả về một thông báo lỗi chung chung. Điều này làm cho việc gỡ lỗi trở nên khó khăn. | **Trung bình** | - Admin không biết được nguyên nhân gốc rễ của lỗi (ví dụ: lỗi kết nối DB, lỗi logic).<br>- Người dùng nhận được thông báo không hữu ích. | Phân tách các loại ngoại lệ khác nhau. Ví dụ: sử dụng `ModelNotFoundException` để xử lý trường hợp không tìm thấy `DatPhong` và đưa ra thông báo lỗi cụ thể hơn. |

**Đề xuất Test Case:**

1.  **Test Case cho Bug #1 (Race Condition):**
    *   Tạo một voucher chỉ có `so_luong` là 1.
    *   Sử dụng một công cụ kiểm thử tải (ví dụ: Apache JMeter, k6) để gửi 10 yêu cầu đặt phòng đồng thời sử dụng cùng một mã voucher.
    *   **Kết quả mong đợi:** Chỉ có 1 yêu cầu thành công, 9 yêu cầu còn lại thất bại do voucher đã hết. Số lượng voucher trong DB không được là số âm.

2.  **Test Case cho Bug #2 (Thiếu xác thực số tiền):**
    *   Tạo một đơn đặt phòng với tổng tiền là 1,000,000 VND.
    *   Đi đến trang thanh toán VNPAY, nhưng không thanh toán.
    *   Mô phỏng một callback từ VNPAY bằng cách gọi trực tiếp URL `vnpay_return` với `vnp_ResponseCode=00` nhưng `vnp_Amount=10000` (tương đương 100 VND).
    *   **Kết quả mong đợi:** Hệ thống báo lỗi "Số tiền không khớp" và hóa đơn vẫn ở trạng thái `cho_thanh_toan`.

3.  **Test Case cho Bug #5 (Không cập nhật `DatPhong`):**
    *   Thực hiện một luồng thanh toán VNPAY thành công.
    *   Kiểm tra trạng thái của `Invoice` trong database. **Mong đợi:** `da_thanh_toan`.
    *   Kiểm tra trạng thái của `DatPhong` tương ứng. **Mong đợi:** `da_xac_nhan`.

**Kết luận:**
Tổng cộng có 7 bug được xác định, trong đó có 1 bug ở mức độ **Critial** và 4 bug ở mức độ **Cao**. Ưu tiên hàng đầu là khắc phục các bug **#2, #5, #1, và #6** vì chúng ảnh hưởng trực tiếp đến rủi ro tài chính và vận hành. Khuyến nghị đội ngũ phát triển thực hiện các thay đổi đã đề xuất và triển khai bộ test tự động để bao phủ các kịch bản này.
