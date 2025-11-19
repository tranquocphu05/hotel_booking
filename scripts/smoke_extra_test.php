<?php
// Temporary smoke-test: simulate creating an EXTRA invoice inside a DB transaction and roll back.
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Invoice;
use App\Models\BookingService;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

echo "Starting smoke test for EXTRA invoice (transactional, will roll back)...\n";

$invoice = Invoice::where('trang_thai', 'da_thanh_toan')
    ->where(function($q){
        $q->whereNull('invoice_type')->orWhere('invoice_type', '!=', 'EXTRA');
    })->first();

if (!$invoice) {
    echo "No paid non-EXTRA invoice found. Aborting.\n";
    exit(0);
}

$booking = $invoice->datPhong;
if (!$booking) {
    echo "Invoice has no associated booking. Aborting.\n";
    exit(0);
}

$service = Service::where('status', 'hoat_dong')->first();
if (!$service) {
    echo "No active Service found. Aborting.\n";
    exit(0);
}

$beforeCount = BookingService::where('dat_phong_id', $booking->id)->whereNull('invoice_id')->count();

DB::beginTransaction();
try {
    $req = new Illuminate\Http\Request([
        'services_data' => [
            $service->id => [
                'entries' => [
                    ['ngay' => date('Y-m-d'), 'so_luong' => 1]
                ]
            ]
        ]
    ]);

    $ctrl = new App\Http\Controllers\Admin\InvoiceController();
    $resp = $ctrl->storeExtra($req, $invoice);

    $afterCount = BookingService::where('dat_phong_id', $booking->id)->whereNull('invoice_id')->count();
    $newInvoice = Invoice::where('dat_phong_id', $booking->id)->where('invoice_type', 'EXTRA')->latest()->first();
    $createdInvoiceId = $newInvoice ? $newInvoice->id : 'N/A';
    $invoiceScopedCount = $createdInvoiceId !== 'N/A' ? BookingService::where('dat_phong_id', $booking->id)->where('invoice_id', $createdInvoiceId)->count() : 0;

    echo "Before booking-level services: {$beforeCount}\n";
    echo "After booking-level services:  {$afterCount}\n";
    echo "Created EXTRA invoice id:     {$createdInvoiceId}\n";
    echo "Invoice-scoped services:      {$invoiceScopedCount}\n";

    DB::rollBack();
    echo "DB rolled back — no changes persisted.\n";
} catch (Throwable $e) {
    DB::rollBack();
    echo "Exception during test: " . $e->getMessage() . "\n";
}

echo "Smoke test complete.\n";
