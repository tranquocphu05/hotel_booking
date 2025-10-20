# 🚀 Hướng Dẫn Tối Ưu Hiệu Suất - Ozia Hotel Booking

## 📋 Tổng Quan

Tài liệu này mô tả các tối ưu hóa hiệu suất đã được triển khai cho website Ozia Hotel để tăng tốc độ tải trang.

## ✅ Các Tối Ưu Hóa Đã Triển Khai

### 1. **Code Splitting & Minification**
- ✅ Tách code thành các chunks nhỏ hơn (vendor, utils)
- ✅ Minify JavaScript với Terser
- ✅ Loại bỏ console.log và debugger trong production
- ✅ CSS Code Splitting

**File:** `vite.config.js`

### 2. **Lazy Loading Images**
- ✅ Intersection Observer API để lazy load images
- ✅ Loading placeholder với animation
- ✅ Lazy load background images
- ✅ WebP support detection

**Files:** 
- `resources/js/lazyload.js`
- `resources/css/app.css`

**Cách sử dụng:**
```html
<!-- Thay vì: -->
<img src="/img/room-1.jpg" alt="Room">

<!-- Sử dụng: -->
<img data-src="/img/room-1.jpg" alt="Room" class="lazy">
```

### 3. **Service Worker Caching**
- ✅ Cache static assets (CSS, JS, images, fonts)
- ✅ Offline support
- ✅ Cache versioning
- ✅ Automatic cache cleanup

**Files:** 
- `public/sw.js`
- `resources/js/sw-register.js`

### 4. **Resource Hints**
- ✅ Preconnect to CDN domains
- ✅ DNS Prefetch
- ✅ Preload critical CSS
- ✅ Async loading Font Awesome

**File:** `resources/views/layouts/base.blade.php`

### 5. **Asset Compression**
- ✅ Gzip compression
- ✅ Brotli compression (if available)
- ✅ Automatic compression for text files

**File:** `public/.htaccess`

### 6. **Browser Caching**
- ✅ Long-term caching (1 year) for static assets
- ✅ Cache-Control headers
- ✅ ETag removal for better performance

**File:** `public/.htaccess`

### 7. **Security Headers**
- ✅ X-Content-Type-Options
- ✅ X-Frame-Options
- ✅ X-XSS-Protection
- ✅ Referrer-Policy
- ✅ Permissions-Policy

**File:** `app/Http/Middleware/OptimizeResponse.php`

### 8. **CSS Optimization**
- ✅ Tailwind CSS purge unused classes
- ✅ CSS minification
- ✅ Critical CSS inline (trong base.blade.php)

**File:** `tailwind.config.js`

## 🚀 Cài Đặt

### 1. Cài đặt dependencies mới:

```bash
npm install
```

### 2. Build assets cho production:

```bash
npm run build
```

### 3. Tối ưu Laravel cache:

```bash
npm run cache
# Hoặc chạy từng lệnh:
php artisan optimize
php artisan view:cache
php artisan route:cache
php artisan config:cache
```

### 4. Đảm bảo Apache modules được bật:

```bash
# Enable mod_deflate (Gzip)
a2enmod deflate

# Enable mod_expires (Browser Caching)
a2enmod expires

# Enable mod_headers
a2enmod headers

# Enable mod_rewrite
a2enmod rewrite

# Restart Apache
systemctl restart apache2
```

## 📊 Kết Quả Mong Đợi

Sau khi triển khai các tối ưu hóa:

- ⚡ **First Contentful Paint (FCP):** Giảm 40-50%
- ⚡ **Largest Contentful Paint (LCP):** Giảm 30-40%
- ⚡ **Time to Interactive (TTI):** Giảm 35-45%
- ⚡ **Total Page Size:** Giảm 50-60%
- ⚡ **Number of Requests:** Giảm 30-40%

## 🔧 Cấu Hình Nâng Cao

### WebP Images

Để tự động convert images sang WebP:

1. Cài đặt package:
```bash
composer require intervention/image
```

2. Thêm vào .env:
```env
WEBP_CONVERSION=true
IMAGE_QUALITY=85
```

### CDN Configuration

Để sử dụng CDN:

1. Cập nhật .env:
```env
CDN_ENABLED=true
CDN_URL=https://cdn.example.com
```

2. Build assets với CDN:
```bash
ASSET_URL=https://cdn.example.com npm run build
```

### HTTPS & HTTP/2

Để tận dụng tối đa hiệu suất:

1. Enable HTTPS
2. Enable HTTP/2 trong Apache:
```bash
a2enmod http2
```

3. Thêm vào VirtualHost:
```apache
Protocols h2 h2c http/1.1
```

## 🧪 Testing

### 1. Test Performance với Lighthouse:

```bash
# Chrome DevTools > Lighthouse > Run audit
```

### 2. Test với PageSpeed Insights:

Truy cập: https://pagespeed.web.dev/

### 3. Test với GTmetrix:

Truy cập: https://gtmetrix.com/

### 4. Test Service Worker:

1. Mở Chrome DevTools
2. Vào tab Application > Service Workers
3. Kiểm tra status "activated and running"

## 📝 Best Practices

### Images

```html
<!-- ✅ Good: Lazy loading với kích thước cụ thể -->
<img data-src="/img/room.jpg" alt="Room" width="800" height="600" class="lazy">

<!-- ❌ Bad: Không có lazy loading -->
<img src="/img/room.jpg" alt="Room">
```

### Scripts

```html
<!-- ✅ Good: Defer non-critical scripts -->
<script defer src="/js/non-critical.js"></script>

<!-- ❌ Bad: Blocking scripts -->
<script src="/js/non-critical.js"></script>
```

### Fonts

```html
<!-- ✅ Good: Preload critical fonts -->
<link rel="preload" href="/fonts/font.woff2" as="font" type="font/woff2" crossorigin>

<!-- ✅ Good: font-display swap -->
@font-face {
    font-family: 'MyFont';
    font-display: swap;
}
```

## 🔄 Maintenance

### Clear Cache

Để clear tất cả cache:

```bash
# Laravel cache
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:clear

# Service Worker cache
# Tăng version trong public/sw.js:
const CACHE_VERSION = 'v1.0.1'; // Tăng version
```

### Update Assets

Khi cập nhật CSS/JS:

```bash
npm run build
```

Service Worker sẽ tự động detect và update cache.

## 📚 Resources

- [Web.dev - Performance](https://web.dev/performance/)
- [Google Lighthouse](https://developers.google.com/web/tools/lighthouse)
- [Vite Performance Guide](https://vitejs.dev/guide/build.html)
- [Service Worker API](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API)

## 🐛 Troubleshooting

### Service Worker không hoạt động

1. Kiểm tra HTTPS (Service Worker chỉ hoạt động với HTTPS hoặc localhost)
2. Kiểm tra Console cho errors
3. Clear browser cache và reload

### Images không lazy load

1. Kiểm tra `data-src` attribute
2. Kiểm tra Console cho errors
3. Verify Intersection Observer support

### Compression không hoạt động

1. Kiểm tra Apache modules: `apache2ctl -M | grep deflate`
2. Kiểm tra .htaccess được load
3. Test với: `curl -H "Accept-Encoding: gzip" -I https://yourdomain.com`

## 📞 Support

Nếu có vấn đề, hãy kiểm tra:
1. Browser Console (F12)
2. Network tab trong DevTools
3. Apache error logs: `/var/log/apache2/error.log`

---

**Last Updated:** 2025-10-20
**Version:** 1.0.0

