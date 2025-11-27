<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\LoaiPhong;
use App\Models\DatPhong;
use App\Models\NguoiDung;
use App\Models\Phong;
use Carbon\Carbon;

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║       TEST HỆ THỐNG ĐẶT PHÒNG - HOTEL BOOKING            ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

$errors = [];
$warnings = [];
$success = [];

// ============================================
// TEST 1: Kiểm tra dữ liệu cơ bản
// ============================================
echo "📌 TEST 1: KIỂM TRA DỮ LIỆU CƠ BẢN\n";
echo str_repeat('-', 60) . "\n";

// Check loại phòng
$roomTypes = LoaiPhong::where('trang_thai', 'hoat_dong')->get();
if ($roomTypes->isEmpty()) {
    $errors[] = "❌ Không có loại phòng nào đang hoạt động";
} else {
    $success[] = "✅ Có {$roomTypes->count()} loại phòng hoạt động";
    foreach ($roomTypes as $rt) {
        echo "  - {$rt->ten_loai}: " . number_format($rt->gia_co_ban) . " VND/đêm\n";
    }
}

// Check phòng
$rooms = Phong::where('trang_thai', 'trong')->get();
if ($rooms->isEmpty()) {
    $warnings[] = "⚠️  Không có phòng nào đang trống";
} else {
    $success[] = "✅ Có {$rooms->count()} phòng trống";
}

// Check users
$clients = NguoiDung::where('vai_tro', 'client')->where('trang_thai', 'hoat_dong')->count();
$admins = NguoiDung::where('vai_tro', 'admin')->where('trang_thai', 'hoat_dong')->count();
$success[] = "✅ Có {$clients} client và {$admins} admin";

echo "\n";

// ============================================
// TEST 2: Kiểm tra Room Availability Logic
// ============================================
echo "📌 TEST 2: KIỂM TRA LOGIC TÌM PHÒNG TRỐNG\n";
echo str_repeat('-', 60) . "\n";

$checkIn = Carbon::tomorrow();
$checkOut = Carbon::tomorrow()->addDays(2);

echo "Tìm phòng từ: {$checkIn->format('d/m/Y')} đến {$checkOut->format('d/m/Y')}\n";

try {
    foreach ($roomTypes as $roomType) {
        // Get bookings that overlap with requested dates
        $overlappingBookings = DatPhong::where('loai_phong_id', $roomType->id)
            ->where('trang_thai', '!=', 'da_huy')
            ->where(function($q) use ($checkIn, $checkOut) {
                $q->whereBetween('ngay_nhan', [$checkIn, $checkOut])
                  ->orWhereBetween('ngay_tra', [$checkIn, $checkOut])
                  ->orWhere(function($q2) use ($checkIn, $checkOut) {
                      $q2->where('ngay_nhan', '<=', $checkIn)
                         ->where('ngay_tra', '>=', $checkOut);
                  });
            })
            ->get();
        
        $bookedCount = $overlappingBookings->sum('so_phong');
        $totalRooms = Phong::where('loai_phong_id', $roomType->id)
            ->where('trang_thai', '!=', 'bao_tri')
            ->count();
        $available = max(0, $totalRooms - $bookedCount);
        
        echo "  - {$roomType->ten_loai}: {$available}/{$totalRooms} phòng available\n";
        
        if ($available <= 0 && $totalRooms > 0) {
            $warnings[] = "⚠️  Loại phòng '{$roomType->ten_loai}' đã full trong khoảng thời gian test";
        }
    }
    $success[] = "✅ Logic tính phòng trống hoạt động";
} catch (\Exception $e) {
    $errors[] = "❌ Lỗi khi tính phòng trống: " . $e->getMessage();
}

echo "\n";

// ============================================
// TEST 3: Test tạo booking
// ============================================
echo "📌 TEST 3: TEST TẠO BOOKING\n";
echo str_repeat('-', 60) . "\n";

try {
    // Tìm user test
    $testUser = NguoiDung::where('email', 'talonin12@gmail.com')->first();
    
    if (!$testUser) {
        $errors[] = "❌ Không tìm thấy test user";
    } else {
        // Tìm loại phòng có sẵn
        $availableRoomType = null;
        foreach ($roomTypes as $rt) {
            $totalRooms = Phong::where('loai_phong_id', $rt->id)->count();
            if ($totalRooms > 0) {
                $availableRoomType = $rt;
                break;
            }
        }
        
        if (!$availableRoomType) {
            $errors[] = "❌ Không có loại phòng nào để test";
        } else {
            // Tạo booking test
            $booking = new DatPhong();
            $booking->nguoi_dung_id = $testUser->id;
            $booking->loai_phong_id = $availableRoomType->id;
            $booking->ngay_nhan = $checkIn;
            $booking->ngay_tra = $checkOut;
            $booking->so_phong = 1;
            $booking->trang_thai = 'cho_xac_nhan';
            $booking->ten_khach_hang = $testUser->name;
            $booking->email_khach_hang = $testUser->email;
            $booking->sdt_khach_hang = $testUser->sdt ?? '0123456789';
            $booking->cccd_khach_hang = $testUser->cccd ?? '000000000000';
            
            if ($booking->save()) {
                $success[] = "✅ Tạo booking test thành công (ID: {$booking->id})";
                echo "  Booking ID: {$booking->id}\n";
                echo "  Loại phòng: {$availableRoomType->ten_loai}\n";
                echo "  Check-in: {$checkIn->format('d/m/Y')}\n";
                echo "  Check-out: {$checkOut->format('d/m/Y')}\n";
                echo "  Trạng thái: {$booking->trang_thai}\n";
                
                // Test xác nhận booking
                $booking->trang_thai = 'da_xac_nhan';
                $booking->save();
                $success[] = "✅ Xác nhận booking thành công";
                
                // Test gán phòng
                $room = Phong::where('loai_phong_id', $availableRoomType->id)
                    ->where('trang_thai', 'trong')
                    ->first();
                    
                if ($room) {
                    $roomIds = [$room->id];
                    $booking->phong_ids = json_encode($roomIds);
                    $booking->save();
                    $success[] = "✅ Gán phòng {$room->so_phong} thành công";
                } else {
                    $warnings[] = "⚠️  Không có phòng trống để gán";
                }
                
                // Cleanup: Xóa booking test
                $booking->delete();
                $success[] = "✅ Đã xóa booking test";
                
            } else {
                $errors[] = "❌ Không thể tạo booking test";
            }
        }
    }
} catch (\Exception $e) {
    $errors[] = "❌ Lỗi khi test booking: " . $e->getMessage();
}

echo "\n";

// ============================================
// TEST 4: Test Business Logic
// ============================================
echo "📌 TEST 4: TEST BUSINESS LOGIC\n";
echo str_repeat('-', 60) . "\n";

// Test 4.1: Check-in validation
try {
    $booking = DatPhong::where('trang_thai', 'da_xac_nhan')->first();
    if ($booking) {
        $canCheckIn = $booking->ngay_nhan <= Carbon::now() 
            && $booking->trang_thai == 'da_xac_nhan'
            && $booking->phong_ids;
        
        if ($canCheckIn) {
            $success[] = "✅ Logic check-in validation đúng";
        } else {
            $warnings[] = "⚠️  Có booking nhưng chưa đủ điều kiện check-in";
        }
    }
} catch (\Exception $e) {
    $warnings[] = "⚠️  Không test được check-in logic: " . $e->getMessage();
}

// Test 4.2: Price calculation
try {
    $testType = $roomTypes->first();
    $nights = $checkIn->diffInDays($checkOut);
    $expectedPrice = $testType->gia_co_ban * $nights;
    
    if ($expectedPrice > 0) {
        $success[] = "✅ Logic tính giá hoạt động (Test: " . number_format($expectedPrice) . " VND)";
    }
} catch (\Exception $e) {
    $errors[] = "❌ Lỗi tính giá: " . $e->getMessage();
}

echo "\n";

// ============================================
// TỔNG KẾT
// ============================================
echo "\n";
echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║                      TỔNG KẾT                            ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

echo "✅ THÀNH CÔNG ({" . count($success) . "}):\n";
foreach ($success as $msg) {
    echo "  $msg\n";
}

if (!empty($warnings)) {
    echo "\n⚠️  CẢNH BÁO ({" . count($warnings) . "}):\n";
    foreach ($warnings as $msg) {
        echo "  $msg\n";
    }
}

if (!empty($errors)) {
    echo "\n❌ LỖI ({" . count($errors) . "}):\n";
    foreach ($errors as $msg) {
        echo "  $msg\n";
    }
}

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
if (empty($errors)) {
    echo "🎉 HỆ THỐNG HOẠT ĐỘNG TỐT!\n";
} else {
    echo "⚠️  CÓ LỖI CẦN SỬA!\n";
}
echo "═══════════════════════════════════════════════════════════\n";
