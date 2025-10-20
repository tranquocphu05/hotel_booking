# Hướng dẫn cấu hình Google OAuth

## Bước 1: Tạo Google OAuth Credentials

1. Truy cập [Google Cloud Console](https://console.cloud.google.com/)
2. Tạo project mới hoặc chọn project hiện có
3. Vào **APIs & Services** > **Credentials**
4. Click **Create Credentials** > **OAuth client ID**
5. Chọn **Application type**: Web application
6. Điền thông tin:
   - **Name**: Hotel Booking App
   - **Authorized JavaScript origins**: `http://127.0.0.1:8000`
   - **Authorized redirect URIs**: `http://127.0.0.1:8000/auth/google/callback`
7. Click **Create**
8. Sao chép **Client ID** và **Client Secret**

## Bước 2: Cấu hình .env

Thêm các dòng sau vào file `.env`:

```env
GOOGLE_CLIENT_ID=1015420459451-b4ro1kpfjf17qnsge1rbbcdp4diohiki.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=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
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

```

**Thay thế:**
- `your-google-client-id-here` bằng Client ID bạn vừa sao chép
- `your-google-client-secret-here` bằng Client Secret bạn vừa sao chép

## Bước 3: Xóa cache

Chạy lệnh sau để xóa cache:

```bash
php artisan config:clear
php artisan cache:clear
```

## Bước 4: Test

1. Mở trình duyệt và truy cập: `http://127.0.0.1:8000/login`
2. Click vào nút **"Đăng nhập với Google"**
3. Chọn tài khoản Google để đăng nhập
4. Chấp nhận quyền truy cập
5. Bạn sẽ được redirect về trang chủ và đăng nhập thành công

## Lưu ý

- Đảm bảo email trong Google Console đã được xác thực
- Nếu lỗi redirect_uri_mismatch, kiểm tra lại URL trong Google Console
- Với production, thay `http://127.0.0.1:8000` bằng domain thật của bạn
- Avatar từ Google sẽ tự động được tải về và lưu vào `public/uploads/avatars/`

