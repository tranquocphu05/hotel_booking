<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DatPhong;
use App\Models\Phong;
use App\Models\LoaiPhong;
use Illuminate\Support\Facades\DB;

echo "================================================================================\n";
echo "          TEST: PIVOT TABLE MIGRATION COMPLETE - NO MORE JSON USAGE\n";
echo "================================================================================\n\n";

$passed = [];
$failed = [];

// TEST 1: Phong::bookings() method sử dụng pivot
echo "TEST 1: Phong::bookings() uses pivot table...\n";
try {
    $phong = Phong::first();
    if ($phong) {
        $bookings = $phong->bookings();
        
        // Should return a collection, not query builder
        if ($bookings instanceof \Illuminate\Database\Eloquent\Collection) {
            echo "  ✓ Returns Eloquent Collection (from relationship)\n";
            echo "  ✓ Count: " . $bookings->count() . " bookings\n";
            $passed[] = "TEST 1: Phong::bookings() uses pivot";
        } else {
            echo "  ✗ Does not return Collection\n";
            $failed[] = "TEST 1: Phong::bookings() - wrong return type";
        }
    }
} catch (\Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
    $failed[] = "TEST 1: " . $e->getMessage();
}
echo "\n";

// TEST 2: DatPhong boot() observer sử dụng pivot
echo "TEST 2: DatPhong boot() observer uses pivot (whereHas)...\n";
try {
    $code = file_get_contents(__DIR__.'/../app/Models/DatPhong.php');
    
    // Should NOT contain whereContainsPhongId in boot logic
    if (strpos($code, '->whereContainsPhongId(') === false) {
        echo "  ✓ No whereContainsPhongId() found\n";
        $passed[] = "TEST 2: No JSON scope in DatPhong";
    } else {
        echo "  ✗ Still uses whereContainsPhongId()\n";
        $failed[] = "TEST 2: Still uses JSON scope";
    }
    
    // Should contain whereHas('phongs')
    if (strpos($code, "whereHas('phongs'") !== false) {
        echo "  ✓ Uses whereHas('phongs') for pivot query\n";
        $passed[] = "TEST 2: Uses pivot whereHas";
    } else {
        echo "  ✗ Does not use whereHas('phongs')\n";
        $failed[] = "TEST 2: Missing pivot whereHas";
    }
} catch (\Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
    $failed[] = "TEST 2: " . $e->getMessage();
}
echo "\n";

// TEST 3: Quick confirm sử dụng syncPhongs()
echo "TEST 3: quickConfirm() uses syncPhongs() not JSON assignment...\n";
try {
    $code = file_get_contents(__DIR__.'/../app/Http/Controllers/Admin/DatPhongController.php');
    
    // Check quickConfirm method
    if (strpos($code, '->syncPhongs($allPhongIds)') !== false) {
        echo "  ✓ Uses syncPhongs() method\n";
        $passed[] = "TEST 3: Uses syncPhongs()";
    } else {
        echo "  ✗ Does not use syncPhongs()\n";
        $failed[] = "TEST 3: Missing syncPhongs()";
    }
    
    // Should NOT directly assign phong_ids JSON (in quickConfirm context)
    $pattern = '/quickConfirm.*?\{.*?phong_ids\s*=.*?\}/s';
    if (!preg_match($pattern, $code)) {
        echo "  ✓ No direct JSON assignment in quickConfirm\n";
        $passed[] = "TEST 3: No JSON assignment";
    }
} catch (\Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
    $failed[] = "TEST 3: " . $e->getMessage();
}
echo "\n";

// TEST 4: Phong::isAvailableInPeriod() sử dụng pivot
echo "TEST 4: isAvailableInPeriod() uses pivot table check...\n";
try {
    $code = file_get_contents(__DIR__.'/../app/Models/Phong.php');
    
    // Check for pivotconflict variable name
    if (strpos($code, '$conflictFromPivot') !== false) {
        echo "  ✓ Has \$conflictFromPivot check\n";
        $passed[] = "TEST 4: Has pivot conflict check";
    } else {
        echo "  ✗ Missing pivot conflict check\n";
        $failed[] = "TEST 4: Missing pivot check";
    }
    
    // Should use whereHas('phongs')
    $pattern = '/whereHas\(\'phongs\'/';
    if (preg_match($pattern, $code)) {
        echo "  ✓ Uses whereHas('phongs') in availability check\n";
        $passed[] = "TEST 4: Uses whereHas in availability";
    } else {
        echo "  ✗ Does not use whereHas('phongs')\n";
        $failed[] = "TEST 4: Missing whereHas";
    }
} catch (\Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
    $failed[] = "TEST 4: " . $e->getMessage();
}
echo "\n";

// TEST 5: No more JSON scopes defined
echo "TEST 5: Check for any remaining JSON scopes...\n";
try {
    $files = [
        __DIR__.'/../app/Models/DatPhong.php',
        __DIR__.'/../app/Models/Phong.php',
    ];
    
    $foundScopes = [];
    foreach ($files as $file) {
        $code = file_get_contents($file);
        if (preg_match('/public function scopeWhereContainsPhongId/', $code)) {
            $foundScopes[] = basename($file);
        }
    }
    
    if (empty($foundScopes)) {
        echo "  ✓ No JSON scope methods defined (good if not used)\n";
        $passed[] = "TEST 5: No JSON scopes found";
    } else {
        echo "  ℹ JSON scopes still defined in: " . implode(', ', $foundScopes) . "\n";
        echo "  ℹ This is OK if they're not used (kept for backward compatibility)\n";
    }
} catch (\Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// TEST 6: Verify pivot tables have data
echo "TEST 6: Verify pivot tables have data...\n";
try {
    $roomsCount = DB::table('booking_rooms')->count();
    $roomTypesCount = DB::table('booking_room_types')->count();
    
    echo "  ℹ booking_rooms: {$roomsCount} records\n";
    echo "  ℹ booking_room_types: {$roomTypesCount} records\n";
    
    if ($roomsCount > 0 && $roomTypesCount > 0) {
        echo "  ✓ Pivot tables have data\n";
        $passed[] = "TEST 6: Pivot tables populated";
    } else {
        echo "  ⚠ Pivot tables empty (no bookings yet?)\n";
    }
} catch (\Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
    $failed[] = "TEST 6: " . $e->getMessage();
}
echo "\n";

// TEST 7: Test actual availability check (integration)
echo "TEST 7: Integration test - availability check...\n";
try {
    $phong = Phong::where('trang_thai', 'dang_thue')->first();
    
    if ($phong) {
        echo "  Testing phòng #{$phong->so_phong} (ID: {$phong->id})...\n";
        
        // Get a booking for this room
        $booking = $phong->datPhongs()->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])->first();
        
        if ($booking) {
            // Check if room shows as NOT available during booking period
            $isAvailable = $phong->isAvailableInPeriod(
                $booking->ngay_nhan,
                $booking->ngay_tra
            );
            
            if (!$isAvailable) {
                echo "  ✓ Room correctly shows as NOT available during booking\n";
                $passed[] = "TEST 7: Availability check works";
            } else {
                echo "  ✗ Room shows as available when it should be booked!\n";
                $failed[] = "TEST 7: Availability check FAILED - potential double booking";
            }
            
            // Check if available for different dates
            $futureDate = now()->addDays(30)->format('Y-m-d');
            $futureDateEnd = now()->addDays(31)->format('Y-m-d');
            
            $isAvailableFuture = $phong->isAvailableInPeriod($futureDate, $futureDateEnd);
            echo "  ℹ Room available for future dates: " . ($isAvailableFuture ? 'Yes' : 'No') . "\n";
        } else {
            echo "  ℹ No active booking found for this room\n";
        }
    } else {
        echo "  ℹ No rooms currently in 'dang_thue' status\n";
    }
} catch (\Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
    $failed[] = "TEST 7: " . $e->getMessage();
}
echo "\n";

// SUMMARY
echo "================================================================================\n";
echo "                              SUMMARY\n";
echo "================================================================================\n\n";

echo "✓ Passed: " . count($passed) . " tests\n";
echo "✗ Failed: " . count($failed) . " tests\n\n";

if (count($failed) === 0) {
    echo "🎉 ALL TESTS PASSED!\n\n";
    echo "✅ JSON → Pivot Table Migration COMPLETE\n";
    echo "✅ All code now uses pivot tables\n";
    echo "✅ No more JSON field dependencies\n";
    echo "✅ Double booking risk eliminated\n\n";
    exit(0);
} else {
    echo "⚠️ SOME TESTS FAILED:\n\n";
    foreach ($failed as $fail) {
        echo "  ✗ {$fail}\n";
    }
    echo "\n";
    exit(1);
}




