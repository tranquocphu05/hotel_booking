# Hướng dẫn cấu hình Email cho chức năng Quên Mật Khẩu

## Bước 1: Mở file `.env`
Tìm và mở file `.env` ở thư mục gốc của project

## Bước 2: Tìm và thay thế các dòng MAIL_* 
Tìm các dòng bắt đầu với `MAIL_` và thay thế bằng:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=ptran5545@gmail.com
MAIL_PASSWORD="oebi uwrm efpq zasf"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=ptran5545@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Bước 3: Xóa cache config
Chạy lệnh sau trong terminal:

```bash
php artisan config:clear
php artisan cache:clear
```

## Bước 4: Test chức năng
1. Truy cập: `http://127.0.0.1:8000/forgot-password`
2. Nhập email đã đăng ký
3. Click "Gửi Link Đặt Lại Mật Khẩu"
4. Kiểm tra email inbox (hoặc spam folder)
5. Click vào link trong email
6. Nhập mật khẩu mới
7. Đăng nhập với mật khẩu mới

## ⚠️ LƯU Ý BẢO MẬT:
- Không chia sẻ mật khẩu ứng dụng (app password) công khai
- Nên thay đổi mật khẩu ứng dụng sau khi hoàn thành test
- File `.env` không được commit lên Git

## Nếu gặp lỗi:
- Kiểm tra xem Gmail có bật "Less secure app access" hoặc dùng App Password
- Kiểm tra firewall có chặn port 587 không
- Kiểm tra log file: `storage/logs/laravel.log`

