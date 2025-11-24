<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tự động hủy các booking chưa thanh toán sau 5 phút
// Chạy mỗi phút để kiểm tra và hủy booking quá hạn
Schedule::command('bookings:cancel-expired --minutes=5')
    ->everyMinute()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/booking-auto-cancel.log'));

// Tự động chuyển phòng từ 'dang_don' về 'trong' sau khi ngày checkout đã qua
// Chạy mỗi giờ để kiểm tra và cập nhật trạng thái phòng
Schedule::command('rooms:auto-clean')
    ->hourly()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/room-auto-clean.log'));
