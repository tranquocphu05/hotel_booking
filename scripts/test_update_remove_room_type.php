<?php

/**
 * Test script: Bỏ chọn loại phòng khi update booking
 * Verify phòng được giải phóng đúng cách
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
echo "       TEST: BỎ CHỌN LOẠI PHÒNG KHI UPDATE - PHÒNG PHẢI ĐƯỢC GIẢI PHÓNG\n";
echo "================================================================================\n\n";

// STEP 1: Tạo booking với 2 loại phòng
echo "STEP 1: Tạo booking với 2 loại phòng (Standard + Deluxe)...\n";

$user = User::where('vai_tro', 'khach_hang')->first();
if (!$user) {
    echo "  ✗ Không tìm thấy user khách hàng\n";
    exit(1);
}

$loaiPhongStandard = LoaiPhong::where('ten_loai', 'LIKE', '%Standard%')->first();
$loaiPhongDeluxe = LoaiPhong::where('ten_loai', 'LIKE', '%Deluxe%')->first();

if (!$loaiPhongStandard || !$loaiPhongDeluxe) {
    echo "  ✗ Không tìm thấy loại phòng Standard/Deluxe\n";
    exit(1);
}

$ngayNhan = Carbon::tomorrow()->addDays(10)->format('Y-m-d');
$ngayTra = Carbon::tomorrow()->addDays(12)->format('Y-m-d');

// Tạo booking với 2 loại phòng
$booking = DB::transaction(function () use ($user, $loaiPhongStandard, $loaiPhongDeluxe, $ngayNhan, $ngayTra) {
    $booking = DatPhong::create([
        'nguoi_dung_id' => $user->id,
        'loai_phong_id' => $loaiPhongStandard->id,
        'so_luong_da_dat' => 2, // 1 Standard + 1 Deluxe
        'ngay_dat' => now(),
        'ngay_nhan' => $ngayNhan,
        'ngay_tra' => $ngayTra,
        'so_nguoi' => 4,
        'trang_thai' => 'cho_xac_nhan',
        'tong_tien' => 3000000,
        'username' => $user->ho_ten,
        'email' => $user->email,
        'sdt' => $user->sdt,
    ]);
    
    // Sync 2 loại phòng
    $booking->syncRoomTypes([
        $loaiPhongStandard->id => ['so_luong' => 1, 'gia_rieng' => 1500000],
        $loaiPhongDeluxe->id => ['so_luong' => 1, 'gia_rieng' => 1500000],
    ]);
    
    // Gán phòng
    $phongStandard = Phong::findAvailableRooms($loaiPhongStandard->id, $ngayNhan, $ngayTra, 1)->first();
    $phongDeluxe = Phong::findAvailableRooms($loaiPhongDeluxe->id, $ngayNhan, $ngayTra, 1)->first();
    
    if (!$phongStandard || !$phongDeluxe) {
        throw new Exception("Không đủ phòng để test");
    }
    
    $booking->syncPhongs([$phongStandard->id, $phongDeluxe->id]);
    
    return $booking;
});

$phongIds = $booking->getPhongIds();
$roomTypes = $booking->getRoomTypes();

echo "  ✓ Booking #{$booking->id} đã tạo\n";
echo "  ✓ Số loại phòng: " . count($roomTypes) . " (Standard + Deluxe)\n";
echo "  ✓ Số phòng đã gán: " . count($phongIds) . "\n";
echo "  ✓ Phòng IDs: " . implode(', ', $phongIds) . "\n";

foreach ($roomTypes as $rt) {
    $lp = LoaiPhong::find($rt['loai_phong_id']);
    echo "    - {$lp->ten_loai}: {$rt['so_luong']} phòng\n";
}
echo "\n";

// STEP 2: Kiểm tra phòng trong pivot table
echo "STEP 2: Kiểm tra pivot table trước update...\n";
$pivotRooms = DB::table('booking_rooms')->where('dat_phong_id', $booking->id)->count();
$pivotRoomTypes = DB::table('booking_room_types')->where('dat_phong_id', $booking->id)->count();
echo "  ✓ booking_rooms: {$pivotRooms} records\n";
echo "  ✓ booking_room_types: {$pivotRoomTypes} records\n";
echo "\n";

// STEP 3: Update booking - BỎ loại phòng Deluxe, chỉ giữ Standard
echo "STEP 3: Update booking - BỎ loại phòng Deluxe (chỉ giữ Standard)...\n";

$booking = DatPhong::find($booking->id);
$oldPhongIds = $booking->getPhongIds();
$oldDeluxePhong = null;

// Tìm phòng Deluxe trong danh sách
foreach ($oldPhongIds as $phongId) {
    $phong = Phong::find($phongId);
    if ($phong && $phong->loai_phong_id == $loaiPhongDeluxe->id) {
        $oldDeluxePhong = $phong;
        break;
    }
}

if (!$oldDeluxePhong) {
    echo "  ✗ Không tìm thấy phòng Deluxe\n";
    exit(1);
}

echo "  ℹ Phòng Deluxe cũ: #{$oldDeluxePhong->so_phong} (ID: {$oldDeluxePhong->id})\n";
echo "  ℹ Trạng thái trước: {$oldDeluxePhong->trang_thai}\n";

// Simulate update logic (giống như AdminDatPhongController::update)
DB::transaction(function () use ($booking, $loaiPhongStandard, $ngayNhan, $ngayTra, $oldPhongIds) {
    // Detach TẤT CẢ phòng cũ
    $booking->phongs()->detach();
    
    // Giải phóng trang_thai
    foreach ($oldPhongIds as $phongId) {
        $phong = Phong::find($phongId);
        if ($phong && $phong->trang_thai === 'dang_thue') {
            // Check no other booking
            $hasOtherBooking = DatPhong::where('id', '!=', $booking->id)
                ->whereHas('phongs', function($q) use ($phongId) {
                    $q->where('phong_id', $phongId);
                })
                ->where(function ($q) use ($ngayNhan, $ngayTra) {
                    $q->where('ngay_tra', '>', $ngayNhan)
                        ->where('ngay_nhan', '<', $ngayTra);
                })
                ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                ->exists();
            
            if (!$hasOtherBooking) {
                $phong->update(['trang_thai' => 'trong']);
            }
        }
    }
    
    // Gán lại chỉ phòng Standard
    $newPhongIds = [];
    $phongStandard = Phong::findAvailableRooms($loaiPhongStandard->id, $ngayNhan, $ngayTra, 1, $booking->id)->first();
    
    if ($phongStandard) {
        $newPhongIds[] = $phongStandard->id;
    }
    
    // Update booking
    $booking->update([
        'loai_phong_id' => $loaiPhongStandard->id,
        'so_luong_da_dat' => 1,
        'tong_tien' => 1500000,
    ]);
    
    // Sync rooms mới
    $booking->syncPhongs($newPhongIds);
    
    // Sync room types mới (chỉ Standard)
    $booking->syncRoomTypes([
        $loaiPhongStandard->id => ['so_luong' => 1, 'gia_rieng' => 1500000],
    ]);
});

echo "  ✓ Update hoàn thành\n";
echo "\n";

// STEP 4: Verify kết quả
echo "STEP 4: Verify kết quả sau update...\n";

$booking->refresh();
$newPhongIds = $booking->getPhongIds();
$newRoomTypes = $booking->getRoomTypes();

echo "  ✓ Số loại phòng: " . count($newRoomTypes) . " (chỉ Standard)\n";
echo "  ✓ Số phòng đã gán: " . count($newPhongIds) . "\n";
echo "  ✓ Phòng IDs mới: " . implode(', ', $newPhongIds) . "\n";

foreach ($newRoomTypes as $rt) {
    $lp = LoaiPhong::find($rt['loai_phong_id']);
    echo "    - {$lp->ten_loai}: {$rt['so_luong']} phòng\n";
}

// Kiểm tra pivot tables
$pivotRoomsAfter = DB::table('booking_rooms')->where('dat_phong_id', $booking->id)->count();
$pivotRoomTypesAfter = DB::table('booking_room_types')->where('dat_phong_id', $booking->id)->count();

echo "\n";
echo "  Pivot tables sau update:\n";
echo "    • booking_rooms: {$pivotRoomsAfter} records (trước: {$pivotRooms})\n";
echo "    • booking_room_types: {$pivotRoomTypesAfter} records (trước: {$pivotRoomTypes})\n";

// Kiểm tra phòng Deluxe cũ đã được giải phóng chưa
$oldDeluxePhong->refresh();
echo "\n";
echo "  Phòng Deluxe cũ (#{$oldDeluxePhong->so_phong}):\n";
echo "    • Trạng thái: {$oldDeluxePhong->trang_thai}\n";
echo "    • Có trong pivot? ";

$stillInPivot = DB::table('booking_rooms')
    ->where('dat_phong_id', $booking->id)
    ->where('phong_id', $oldDeluxePhong->id)
    ->exists();

if ($stillInPivot) {
    echo "❌ CÒN (BUG!)\n";
} else {
    echo "✅ KHÔNG (ĐÚNG!)\n";
}

// Kiểm tra Deluxe room type còn trong pivot không
$deluxeRoomTypeInPivot = DB::table('booking_room_types')
    ->where('dat_phong_id', $booking->id)
    ->where('loai_phong_id', $loaiPhongDeluxe->id)
    ->exists();

echo "    • Loại Deluxe còn trong pivot? ";
if ($deluxeRoomTypeInPivot) {
    echo "❌ CÒN (BUG!)\n";
} else {
    echo "✅ KHÔNG (ĐÚNG!)\n";
}

echo "\n";

// SUMMARY
echo "================================================================================\n";
echo "                                KẾT QUẢ\n";
echo "================================================================================\n\n";

$success = true;

// Check 1: Số phòng giảm từ 2 → 1
if (count($newPhongIds) === 1 && count($oldPhongIds) === 2) {
    echo "✅ CHECK 1: Số phòng giảm từ 2 → 1 (PASS)\n";
} else {
    echo "❌ CHECK 1: Số phòng không giảm đúng (FAIL)\n";
    $success = false;
}

// Check 2: Số loại phòng giảm từ 2 → 1
if (count($newRoomTypes) === 1 && count($roomTypes) === 2) {
    echo "✅ CHECK 2: Số loại phòng giảm từ 2 → 1 (PASS)\n";
} else {
    echo "❌ CHECK 2: Số loại phòng không giảm đúng (FAIL)\n";
    $success = false;
}

// Check 3: Phòng Deluxe KHÔNG còn trong pivot
if (!$stillInPivot) {
    echo "✅ CHECK 3: Phòng Deluxe đã được remove khỏi pivot table (PASS)\n";
} else {
    echo "❌ CHECK 3: Phòng Deluxe VẪN CÒN trong pivot table (FAIL)\n";
    $success = false;
}

// Check 4: Loại Deluxe KHÔNG còn trong pivot
if (!$deluxeRoomTypeInPivot) {
    echo "✅ CHECK 4: Loại Deluxe đã được remove khỏi pivot table (PASS)\n";
} else {
    echo "❌ CHECK 4: Loại Deluxe VẪN CÒN trong pivot table (FAIL)\n";
    $success = false;
}

// Check 5: Phòng Deluxe được giải phóng (trang_thai = 'trong')
if ($oldDeluxePhong->trang_thai === 'trong') {
    echo "✅ CHECK 5: Phòng Deluxe đã được giải phóng (trang_thai = 'trong') (PASS)\n";
} else {
    echo "❌ CHECK 5: Phòng Deluxe CHƯA được giải phóng (trang_thai = '{$oldDeluxePhong->trang_thai}') (FAIL)\n";
    $success = false;
}

echo "\n";

if ($success) {
    echo "🎉 TẤT CẢ CHECKS PASS! Bug đã được fix!\n";
    echo "\nKết luận:\n";
    echo "  ✅ Khi bỏ chọn loại phòng, phòng được giải phóng đúng cách\n";
    echo "  ✅ Pivot tables được cập nhật chính xác\n";
    echo "  ✅ Trạng thái phòng được reset về 'trong'\n";
} else {
    echo "⚠️ MỘT SỐ CHECKS FAILED! Vẫn còn bug.\n";
}

// Cleanup
DB::transaction(function() use ($booking) {
    $booking->phongs()->detach();
    $booking->roomTypes()->detach();
    $booking->delete();
});

echo "\n✓ Đã cleanup test data\n";

echo "\n================================================================================\n";
echo "                              KẾT THÚC TEST\n";
echo "================================================================================\n";

exit($success ? 0 : 1);




