<?php
// Debug script to inspect booking 205 and available rooms per room type
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DatPhong;
use App\Models\Phong;

$bookingId = 205;
$booking = DatPhong::find($bookingId);
if (!$booking) {
    echo "Booking not found: $bookingId\n";
    exit(1);
}

$output = [];
$output['booking'] = $booking->only(['id','ngay_nhan','ngay_tra','room_types','phong_ids','so_luong_da_dat']);

foreach ($booking->getRoomTypes() as $rt) {
    $lid = $rt['loai_phong_id'] ?? null;
    $needed = $rt['so_luong'] ?? 0;
    $rooms = Phong::findAvailableRooms($lid, $booking->ngay_nhan, $booking->ngay_tra, 999, $booking->id);
    $output['room_types'][] = [
        'loai_phong_id' => $lid,
        'so_luong' => $needed,
        'available_count' => $rooms->count(),
        'rooms' => $rooms->map(function($r){
            return ['id'=>$r->id, 'so_phong'=>$r->so_phong, 'tang'=>$r->tang, 'trang_thai'=>$r->trang_thai];
        })->values()->toArray(),
    ];
}

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), PHP_EOL;
