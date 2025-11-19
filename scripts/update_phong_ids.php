<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DatPhong;

// Try booking ID 74 first (as in your error), otherwise pick first
$targetId = 74;
$booking = DatPhong::find($targetId);
if (!$booking) {
    $booking = DatPhong::first();
    if (!$booking) {
        echo "No DatPhong rows in database.\n";
        exit(0);
    }
    echo "Booking 74 not found; using booking id={$booking->id}\n";
} else {
    echo "Found booking id={$booking->id}\n";
}

try {
    $booking->phong_ids = [4];
    $booking->save();
    echo "Saved phong_ids for booking id={$booking->id}: ";
    print_r($booking->phong_ids);
    echo "\n";
} catch (Throwable $e) {
    echo "Exception saving phong_ids: " . get_class($e) . " - " . $e->getMessage() . "\n";
}
