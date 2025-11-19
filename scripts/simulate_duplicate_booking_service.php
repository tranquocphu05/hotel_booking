<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Invoice;
use App\Models\DatPhong;
use App\Models\Service;
use App\Models\BookingService;
use Illuminate\Support\Facades\Log;

$booking = DatPhong::first();
$service = Service::first();
if (!$booking || !$service) {
    echo "Need at least one booking and one service in DB.\n";
    exit(0);
}

// Create a temporary EXTRA invoice
$new = Invoice::create([
    'dat_phong_id' => $booking->id,
    'tong_tien' => 0,
    'phuong_thuc' => null,
    'trang_thai' => 'cho_thanh_toan',
    'invoice_type' => 'EXTRA',
    'ngay_tao' => now(),
]);

$used_at = date('Y-m-d', strtotime('+1 day'));

function tryCreate($new, $booking, $service, $qty, $used_at) {
    try {
        BookingService::create([
            'invoice_id' => $new->id,
            'dat_phong_id' => $booking->id,
            'service_id' => $service->id,
            'quantity' => $qty,
            'unit_price' => $service->price ?? 0,
            'used_at' => $used_at,
        ]);
        echo "Created booking_service qty={$qty}\n";
    } catch (\Illuminate\Database\QueryException $e) {
        echo "Caught QueryException: " . $e->getMessage() . "\n";
        // fallback logic
        $fallback = BookingService::where('dat_phong_id', $booking->id)
            ->where('service_id', $service->id)
            ->where('used_at', $used_at)
            ->where('invoice_id', $new->id)
            ->first();
        if ($fallback) {
            $fallback->quantity = ($fallback->quantity ?? 0) + $qty;
            $fallback->unit_price = $service->price ?? $fallback->unit_price;
            $fallback->save();
            echo "Merged into fallback, new qty={$fallback->quantity}\n";
        } else {
            echo "No fallback row found, rethrowing.\n";
            throw $e;
        }
    }
}

// First insert
tryCreate($new, $booking, $service, 1, $used_at);
// Second insert (duplicate) should be merged
tryCreate($new, $booking, $service, 2, $used_at);

// Show final row
$row = BookingService::where('dat_phong_id', $booking->id)->where('service_id', $service->id)->where('invoice_id', $new->id)->where('used_at', $used_at)->first();
if ($row) {
    echo "Final row: id={$row->id}, qty={$row->quantity}, unit_price={$row->unit_price}, used_at={$row->used_at}\n";
} else {
    echo "No final row found.\n";
}

// Cleanup: delete created invoice and booking services
BookingService::where('invoice_id', $new->id)->delete();
$new->delete();

echo "Done.\n";
