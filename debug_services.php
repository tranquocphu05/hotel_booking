<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Booking 111 services:\n";
$services = \Illuminate\Support\Facades\DB::table('booking_services')->where('dat_phong_id', 111)->orderBy('used_at')->get();
echo "Total: " . count($services) . "\n";
foreach($services as $s) {
    $date = date('Y-m-d', strtotime($s->used_at));
    echo "- $date: phong_id=" . ($s->phong_id ?? 'NULL') . ", qty=" . $s->quantity . "\n";
}
