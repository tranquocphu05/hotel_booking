@echo off
REM Performance Optimization Script for Windows
REM Chạy script này để tối ưu hiệu năng website

echo 🚀 Bắt đầu tối ưu hiệu năng...

REM 1. Clear all caches
echo 📦 Đang xóa cache cũ...
php artisan optimize:clear

REM 2. Run migrations for indexes
echo 🗄️  Đang thêm database indexes...
php artisan migrate --force

REM 3. Build production assets
echo 📦 Đang build assets cho production...
call npm run build

REM 4. Cache Laravel
echo 💾 Đang cache Laravel...
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

REM 5. Optimize autoloader
echo ⚡ Đang tối ưu autoloader...
composer dump-autoload -o

REM 6. Clear and rebuild cache
echo 🔄 Đang rebuild cache...
php artisan cache:clear
php artisan optimize

echo ✅ Hoàn thành tối ưu hiệu năng!
echo.
echo 📊 Kết quả mong đợi:
echo    - Giảm thời gian load: 50-70%%
echo    - Giảm số lượng database queries: 60-80%%
echo    - Giảm kích thước assets: 30-40%%
echo.
echo 💡 Lưu ý:
echo    - Đảm bảo OPcache được enable trong PHP
echo    - Sử dụng Redis cho cache nếu có thể
echo    - Setup CDN cho static assets

pause

