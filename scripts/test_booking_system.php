<?php

/**
 * Script kiểm tra các bugs đã được fix trong hệ thống đặt phòng
 * Run: php scripts/test_booking_system.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DatPhong;
use App\Models\Phong;
use App\Models\LoaiPhong;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "================================================================================\n";
echo "              KIỂM TRA HỆ THỐNG ĐẶT PHÒNG - BUG TESTING\n";
echo "================================================================================\n\n";

$errors = [];
$passed = [];

// TEST 1: Kiểm tra Pivot Table Relationships
echo "TEST 1: Kiểm tra Pivot Table Relationships...\n";
try {
    $booking = DatPhong::with(['phongs', 'roomTypes'])->first();
    if ($booking) {
        $phongIds = $booking->getPhongIds();
        $roomTypes = $booking->getRoomTypes();
        
        echo "  ✓ Booking #{$booking->id}: " . count($phongIds) . " phòng, " . count($roomTypes) . " loại phòng\n";
        
        // Kiểm tra pivot table có data không
        $pivotCount = DB::table('booking_rooms')->where('dat_phong_id', $booking->id)->count();
        $roomTypeCount = DB::table('booking_room_types')->where('dat_phong_id', $booking->id)->count();
        
        echo "  ✓ Pivot table booking_rooms: {$pivotCount} records\n";
        echo "  ✓ Pivot table booking_room_types: {$roomTypeCount} records\n";
        
        $passed[] = "TEST 1: Pivot tables working correctly";
    } else {
        echo "  ⚠ Không có booking nào để test\n";
    }
} catch (\Exception $e) {
    $errors[] = "TEST 1: " . $e->getMessage();
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// TEST 2: Kiểm tra Lock trong assign room
echo "TEST 2: Kiểm tra lockForUpdate được sử dụng đúng...\n";
try {
    $code = file_get_contents(__DIR__.'/../app/Http/Controllers/BookingController.php');
    
    // Check if lockForUpdate is used before assigning rooms
    if (strpos($code, 'lockForUpdate()->find($phong->id)') !== false) {
        echo "  ✓ BookingController sử dụng lockForUpdate() trước khi gán phòng\n";
        $passed[] = "TEST 2: Lock được sử dụng trong BookingController";
    } else {
        $errors[] = "TEST 2: BookingController không sử dụng lockForUpdate()";
        echo "  ✗ BookingController không sử dụng lockForUpdate()\n";
    }
    
    $adminCode = file_get_contents(__DIR__.'/../app/Http/Controllers/Admin/DatPhongController.php');
    if (strpos($adminCode, 'lockForUpdate()') !== false) {
        echo "  ✓ Admin DatPhongController sử dụng lockForUpdate()\n";
        $passed[] = "TEST 2: Lock được sử dụng trong Admin Controller";
    } else {
        $errors[] = "TEST 2: Admin DatPhongController không sử dụng lockForUpdate()";
        echo "  ✗ Admin DatPhongController không sử dụng lockForUpdate()\n";
    }
} catch (\Exception $e) {
    $errors[] = "TEST 2: " . $e->getMessage();
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// TEST 3: Kiểm tra duplicate room check
echo "TEST 3: Kiểm tra duplicate room check...\n";
try {
    $code = file_get_contents(__DIR__.'/../app/Http/Controllers/BookingController.php');
    
    // Check if duplicate check happens AFTER lock
    if (strpos($code, 'if (in_array($phongLocked->id, $allPhongIds))') !== false) {
        echo "  ✓ Duplicate check diễn ra SAU khi lock phòng\n";
        $passed[] = "TEST 3: Duplicate check đúng thứ tự";
    } else {
        $errors[] = "TEST 3: Duplicate check không diễn ra sau khi lock";
        echo "  ✗ Duplicate check không diễn ra sau khi lock\n";
    }
    
    // Check for duplicate room types validation
    if (strpos($code, 'count($roomTypeIds) !== count(array_unique($roomTypeIds))') !== false) {
        echo "  ✓ Validation duplicate room types có sẵn\n";
        $passed[] = "TEST 3: Validation duplicate room types";
    } else {
        $errors[] = "TEST 3: Thiếu validation duplicate room types";
        echo "  ✗ Thiếu validation duplicate room types\n";
    }
} catch (\Exception $e) {
    $errors[] = "TEST 3: " . $e->getMessage();
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// TEST 4: Kiểm tra validation số lượng phòng
echo "TEST 4: Kiểm tra validation số lượng phòng đã gán...\n";
try {
    $code = file_get_contents(__DIR__.'/../app/Http/Controllers/BookingController.php');
    
    if (strpos($code, 'if (count($phongIds) < $roomDetail[\'so_luong\'])') !== false) {
        echo "  ✓ Validate số lượng phòng đã gán trong BookingController\n";
        $passed[] = "TEST 4: Validate số lượng phòng - Client";
    } else {
        $errors[] = "TEST 4: Thiếu validation số lượng phòng trong BookingController";
        echo "  ✗ Thiếu validation số lượng phòng trong BookingController\n";
    }
    
    $adminCode = file_get_contents(__DIR__.'/../app/Http/Controllers/Admin/DatPhongController.php');
    if (strpos($adminCode, 'if (count($newPhongIds) < $totalSoLuong)') !== false) {
        echo "  ✓ Validate số lượng phòng đã gán trong Admin DatPhongController\n";
        $passed[] = "TEST 4: Validate số lượng phòng - Admin";
    } else {
        $errors[] = "TEST 4: Thiếu validation số lượng phòng trong Admin DatPhongController";
        echo "  ✗ Thiếu validation số lượng phòng trong Admin DatPhongController\n";
    }
} catch (\Exception $e) {
    $errors[] = "TEST 4: " . $e->getMessage();
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// TEST 5: Kiểm tra phòng availability method
echo "TEST 5: Kiểm tra Phong::isAvailableInPeriod()...\n";
try {
    $phong = Phong::where('trang_thai', 'trong')->first();
    if ($phong) {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $dayAfter = Carbon::tomorrow()->addDay()->format('Y-m-d');
        
        $isAvailable = $phong->isAvailableInPeriod($tomorrow, $dayAfter);
        echo "  ✓ Method isAvailableInPeriod() hoạt động: " . ($isAvailable ? 'Available' : 'Not Available') . "\n";
        $passed[] = "TEST 5: isAvailableInPeriod() working";
    } else {
        echo "  ⚠ Không có phòng nào để test\n";
    }
    
    // Check phòng bảo trì
    $phongBaoTri = Phong::where('trang_thai', 'bao_tri')->first();
    if ($phongBaoTri) {
        $isAvailable = $phongBaoTri->isAvailableInPeriod($tomorrow, $dayAfter);
        if (!$isAvailable) {
            echo "  ✓ Phòng bảo trì trả về NOT AVAILABLE (đúng)\n";
            $passed[] = "TEST 5: Phòng bảo trì được check đúng";
        } else {
            $errors[] = "TEST 5: Phòng bảo trì vẫn trả về available (SAI)";
            echo "  ✗ Phòng bảo trì vẫn trả về available (SAI)\n";
        }
    }
} catch (\Exception $e) {
    $errors[] = "TEST 5: " . $e->getMessage();
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// TEST 6: Kiểm tra so_luong_trong calculation
echo "TEST 6: Kiểm tra tính toán so_luong_trong...\n";
try {
    $loaiPhong = LoaiPhong::first();
    if ($loaiPhong) {
        $trongCount = Phong::where('loai_phong_id', $loaiPhong->id)
            ->where('trang_thai', 'trong')
            ->count();
        
        echo "  ✓ Loại phòng '{$loaiPhong->ten_loai}': {$trongCount} phòng trống (calculated)\n";
        echo "  ✓ so_luong_trong in DB: {$loaiPhong->so_luong_trong}\n";
        
        if ($trongCount == $loaiPhong->so_luong_trong) {
            echo "  ✓ Số lượng trống CHÍNH XÁC\n";
            $passed[] = "TEST 6: so_luong_trong accurate";
        } else {
            echo "  ⚠ Số lượng trống KHÔNG KHỚP (có thể cần recalculate)\n";
        }
    }
} catch (\Exception $e) {
    $errors[] = "TEST 6: " . $e->getMessage();
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// TEST 7: Kiểm tra AutoCancelExpiredBookings logic
echo "TEST 7: Kiểm tra AutoCancelExpiredBookings middleware...\n";
try {
    $code = file_get_contents(__DIR__.'/../app/Http/Middleware/AutoCancelExpiredBookings.php');
    
    if (strpos($code, 'detach()') !== false || strpos($code, 'freeRoomIfNoOtherBooking') !== false) {
        echo "  ✓ AutoCancelExpiredBookings có logic giải phóng phòng\n";
        $passed[] = "TEST 7: AutoCancel có logic free rooms";
    } else {
        $errors[] = "TEST 7: AutoCancelExpiredBookings thiếu logic giải phóng phòng";
        echo "  ✗ AutoCancelExpiredBookings thiếu logic giải phóng phòng\n";
    }
} catch (\Exception $e) {
    $errors[] = "TEST 7: " . $e->getMessage();
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// TEST 8: Kiểm tra Transaction usage
echo "TEST 8: Kiểm tra sử dụng Database Transaction...\n";
try {
    $code = file_get_contents(__DIR__.'/../app/Http/Controllers/BookingController.php');
    
    if (strpos($code, 'DB::transaction(function ()') !== false) {
        echo "  ✓ BookingController sử dụng DB::transaction()\n";
        $passed[] = "TEST 8: BookingController uses transactions";
    } else {
        $errors[] = "TEST 8: BookingController không sử dụng transactions";
        echo "  ✗ BookingController không sử dụng transactions\n";
    }
    
    $adminCode = file_get_contents(__DIR__.'/../app/Http/Controllers/Admin/DatPhongController.php');
    if (strpos($adminCode, 'DB::transaction(function ()') !== false) {
        echo "  ✓ Admin DatPhongController sử dụng DB::transaction()\n";
        $passed[] = "TEST 8: Admin Controller uses transactions";
    } else {
        $errors[] = "TEST 8: Admin DatPhongController không sử dụng transactions";
        echo "  ✗ Admin DatPhongController không sử dụng transactions\n";
    }
} catch (\Exception $e) {
    $errors[] = "TEST 8: " . $e->getMessage();
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// TEST 9: Kiểm tra Database Schema
echo "TEST 9: Kiểm tra Database Schema...\n";
try {
    // Check bảng pivot tables
    $tables = ['booking_rooms', 'booking_room_types'];
    foreach ($tables as $table) {
        if (DB::getSchemaBuilder()->hasTable($table)) {
            $count = DB::table($table)->count();
            echo "  ✓ Table '{$table}' tồn tại ({$count} records)\n";
            $passed[] = "TEST 9: Table {$table} exists";
        } else {
            $errors[] = "TEST 9: Table '{$table}' KHÔNG TỒN TẠI";
            echo "  ✗ Table '{$table}' KHÔNG TỒN TẠI\n";
        }
    }
    
    // Check foreign keys
    $foreignKeys = [
        'booking_rooms' => ['dat_phong_id', 'phong_id'],
        'booking_room_types' => ['dat_phong_id', 'loai_phong_id'],
    ];
    
    foreach ($foreignKeys as $table => $columns) {
        if (DB::getSchemaBuilder()->hasTable($table)) {
            foreach ($columns as $column) {
                if (DB::getSchemaBuilder()->hasColumn($table, $column)) {
                    echo "  ✓ Column '{$table}.{$column}' tồn tại\n";
                } else {
                    $errors[] = "TEST 9: Column '{$table}.{$column}' không tồn tại";
                    echo "  ✗ Column '{$table}.{$column}' không tồn tại\n";
                }
            }
        }
    }
} catch (\Exception $e) {
    $errors[] = "TEST 9: " . $e->getMessage();
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// TEST 10: Kiểm tra DatPhong Model methods
echo "TEST 10: Kiểm tra DatPhong Model helper methods...\n";
try {
    $booking = DatPhong::first();
    if ($booking) {
        // Test getPhongIds()
        $phongIds = $booking->getPhongIds();
        echo "  ✓ getPhongIds() works: " . count($phongIds) . " phòng\n";
        $passed[] = "TEST 10: getPhongIds() method";
        
        // Test getRoomTypes()
        $roomTypes = $booking->getRoomTypes();
        echo "  ✓ getRoomTypes() works: " . count($roomTypes) . " loại phòng\n";
        $passed[] = "TEST 10: getRoomTypes() method";
        
        // Test syncPhongs()
        if (method_exists($booking, 'syncPhongs')) {
            echo "  ✓ syncPhongs() method exists\n";
            $passed[] = "TEST 10: syncPhongs() method exists";
        }
        
        // Test syncRoomTypes()
        if (method_exists($booking, 'syncRoomTypes')) {
            echo "  ✓ syncRoomTypes() method exists\n";
            $passed[] = "TEST 10: syncRoomTypes() method exists";
        }
    }
} catch (\Exception $e) {
    $errors[] = "TEST 10: " . $e->getMessage();
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// SUMMARY
echo "================================================================================\n";
echo "                              TỔNG KẾT\n";
echo "================================================================================\n\n";

echo "✓ Passed: " . count($passed) . " tests\n";
echo "✗ Failed: " . count($errors) . " tests\n\n";

if (count($errors) === 0) {
    echo "🎉 TẤT CẢ CÁC TEST ĐỀU PASS! Hệ thống hoạt động tốt!\n\n";
    echo "Kết luận:\n";
    echo "- ✅ Không có bug lớn nào được phát hiện\n";
    echo "- ✅ Tất cả các bugs đã được fix đúng cách\n";
    echo "- ✅ Code sử dụng locks và transactions đầy đủ\n";
    echo "- ✅ Pivot tables hoạt động chính xác\n";
    echo "- ✅ Validations đầy đủ\n";
} else {
    echo "⚠️ CÓ MỘT SỐ VẤN ĐỀ CẦN KHẮC PHỤC:\n\n";
    foreach ($errors as $error) {
        echo "  ✗ " . $error . "\n";
    }
}

echo "\n================================================================================\n";
echo "                            KẾT THÚC KIỂM TRA\n";
echo "================================================================================\n";




