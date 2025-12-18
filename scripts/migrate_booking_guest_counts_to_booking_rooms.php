<?php
// Usage: php scripts/migrate_booking_guest_counts_to_booking_rooms.php
require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;
use App\Models\DatPhong;

// Boot Laravel environment
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$bookings = DatPhong::with('phongs')->get();
foreach ($bookings as $b) {
    $checked = $b->getCheckedInPhongs();
    if ($checked->isEmpty()) continue;

    $adults = (int)($b->so_nguoi ?? 0);
    $children = (int)($b->so_tre_em ?? 0);
    $infants = (int)($b->so_em_be ?? 0);

    $n = $checked->count();
    if ($n <= 0) continue;

    // Distribute each category evenly across checked-in rooms
    $baseAdults = intdiv($adults, $n);
    $remAdults = $adults % $n;

    $baseChildren = intdiv($children, $n);
    $remChildren = $children % $n;

    $baseInfants = intdiv($infants, $n);
    $remInfants = $infants % $n;

    foreach ($checked as $idx => $p) {
        $a = $baseAdults + ($idx < $remAdults ? 1 : 0);
        $c = $baseChildren + ($idx < $remChildren ? 1 : 0);
        $i = $baseInfants + ($idx < $remInfants ? 1 : 0);

        $now = date('Y-m-d H:i:s');
        DB::statement(
            'INSERT INTO booking_rooms (dat_phong_id, phong_id, so_nguoi_lon, so_tre_em, so_em_be, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?) ' .
            'ON DUPLICATE KEY UPDATE so_nguoi_lon = VALUES(so_nguoi_lon), so_tre_em = VALUES(so_tre_em), so_em_be = VALUES(so_em_be), updated_at = VALUES(updated_at)',
            [$b->id, $p->id, $a, $c, $i, $now, $now]
        );
        echo "Booking {$b->id} room {$p->id} set counts A={$a} C={$c} I={$i}\n";
    }
}

echo "Migration complete. Please review results and run tests.\n";
