<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

// Bootstrap the application (console kernel) so Eloquent and Schema are available
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DatPhong;
use Illuminate\Support\Facades\Schema;

$testPhongId = 1; // adjust if needed

echo "Running smoke test for DatPhong::whereContainsPhongId({$testPhongId})\n";

try {
    $exists = DatPhong::whereContainsPhongId($testPhongId)
        ->where(function ($q) {
            $q->where('ngay_tra', '>', now())
                ->where('ngay_nhan', '<', now()->addDay());
        })
        ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
        ->exists();

    echo "Safe scope query executed successfully. exists=" . ($exists ? 'true' : 'false') . "\n";
} catch (Throwable $e) {
    echo "Safe scope query threw exception: " . get_class($e) . " - " . $e->getMessage() . "\n";
}

// Also try a direct whereJsonContains to show behavior when column missing
try {
    if (Schema::hasColumn('dat_phong', 'phong_ids')) {
        $exists2 = DatPhong::whereJsonContains('phong_ids', $testPhongId)->exists();
        echo "Direct whereJsonContains OK. exists=" . ($exists2 ? 'true' : 'false') . "\n";
    } else {
        echo "Column 'phong_ids' does not exist — skipping direct whereJsonContains test.\n";
    }
} catch (Throwable $e) {
    echo "Direct whereJsonContains threw exception: " . get_class($e) . " - " . $e->getMessage() . "\n";
}
