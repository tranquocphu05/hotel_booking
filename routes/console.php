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
