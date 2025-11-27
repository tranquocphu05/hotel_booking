<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DatPhong;
use App\Models\Phong;
use App\Models\LoaiPhong;
use Illuminate\Support\Facades\DB;

echo "================================================================================\n";
echo "            KIỂM TRA DOUBLE BOOKING - OCEAN VIEW 21-22/11/2025\n";
echo "================================================================================\n\n";

// 1. Tìm loại phòng Ocean View
$oceanView = LoaiPhong::where('ten_loai', 'LIKE', '%Ocean%')->first();

if (!$oceanView) {
    echo "Không tìm thấy loại phòng Ocean View\n";
    exit(1);
}

echo "THÔNG TIN LOẠI PHÒNG:\n";
echo "  • ID: {$oceanView->id}\n";
echo "  • Tên: {$oceanView->ten_loai}\n";
echo "  • Tổng số phòng: {$oceanView->so_luong_phong}\n";
echo "  • Phòng trống (DB): {$oceanView->so_luong_trong}\n\n";

// 2. Lấy tất cả phòng Ocean View
$rooms = Phong::where('loai_phong_id', $oceanView->id)->get();

echo "DANH SÁCH PHÒNG OCEAN VIEW:\n";
foreach ($rooms as $room) {
    echo sprintf("  • Phòng #%-5s (ID: %-3d) - %s\n", 
        $room->so_phong, 
        $room->id, 
        $room->trang_thai
    );
}
echo "\n";

// 3. Tìm tất cả bookings cho Ocean View trong khoảng 21-22/11/2025
$bookings = DatPhong::where(function($q) {
        $q->where('ngay_tra', '>', '2025-11-21')
          ->where('ngay_nhan', '<', '2025-11-22');
    })
    ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
    ->with(['phongs', 'roomTypes'])
    ->get();

// Filter chỉ lấy bookings có Ocean View
$oceanViewBookings = $bookings->filter(function($booking) use ($oceanView) {
    $roomTypes = $booking->getRoomTypes();
    foreach ($roomTypes as $rt) {
        if ($rt['loai_phong_id'] == $oceanView->id) {
            return true;
        }
    }
    return false;
});

echo "BOOKINGS CHO OCEAN VIEW TRONG 21-22/11/2025:\n";
echo "Tìm thấy: " . $oceanViewBookings->count() . " booking(s)\n\n";

if ($oceanViewBookings->isEmpty()) {
    echo "Không có booking nào.\n";
    exit(0);
}

$allAssignedRooms = [];
$conflicts = [];

foreach ($oceanViewBookings as $booking) {
    $phongIds = $booking->getPhongIds();
    $roomTypes = $booking->getRoomTypes();
    
    // Tìm Ocean View room type
    $oceanViewRoomType = collect($roomTypes)->first(function($rt) use ($oceanView) {
        return $rt['loai_phong_id'] == $oceanView->id;
    });
    
    echo "─────────────────────────────────────────────────────────────────\n";
    echo "Booking #{$booking->id}\n";
    echo "  • Trạng thái: {$booking->trang_thai}\n";
    echo "  • Ngày: {$booking->ngay_nhan} → {$booking->ngay_tra}\n";
    echo "  • Số người: {$booking->so_nguoi}\n";
    echo "  • Ocean View: " . ($oceanViewRoomType['so_luong'] ?? 0) . " phòng\n";
    echo "  • Tổng phòng đã gán: " . count($phongIds) . "\n";
    echo "  • Phòng IDs: " . implode(', ', $phongIds) . "\n";
    
    // Lấy phòng Ocean View được gán
    $oceanViewPhongIds = [];
    foreach ($phongIds as $phongId) {
        $phong = Phong::find($phongId);
        if ($phong && $phong->loai_phong_id == $oceanView->id) {
            $oceanViewPhongIds[] = $phongId;
            echo "    → Phòng #{$phong->so_phong} (ID: {$phong->id}) - Ocean View\n";
            
            // Check conflict
            if (in_array($phongId, $allAssignedRooms)) {
                $conflicts[] = [
                    'phong_id' => $phongId,
                    'phong_so' => $phong->so_phong,
                    'booking_id' => $booking->id,
                ];
                echo "      ⚠️ CONFLICT: Phòng này đã được gán cho booking khác!\n";
            }
            
            $allAssignedRooms[] = $phongId;
        }
    }
    
    // Verify số lượng
    $expectedCount = $oceanViewRoomType['so_luong'] ?? 0;
    $actualCount = count($oceanViewPhongIds);
    
    if ($expectedCount != $actualCount) {
        echo "  ⚠️ MISMATCH: Cần {$expectedCount} phòng nhưng chỉ gán {$actualCount} phòng\n";
    }
    
    echo "\n";
}

echo "================================================================================\n";
echo "                              PHÂN TÍCH\n";
echo "================================================================================\n\n";

// Kiểm tra duplicate phòng
$duplicates = [];
$counted = array_count_values($allAssignedRooms);
foreach ($counted as $phongId => $count) {
    if ($count > 1) {
        $phong = Phong::find($phongId);
        $duplicates[] = [
            'phong_id' => $phongId,
            'phong_so' => $phong ? $phong->so_phong : 'N/A',
            'count' => $count,
        ];
    }
}

if (!empty($duplicates)) {
    echo "🚨 PHÁT HIỆN DOUBLE BOOKING:\n\n";
    foreach ($duplicates as $dup) {
        echo "  ❌ Phòng #{$dup['phong_so']} (ID: {$dup['phong_id']})\n";
        echo "     → Được gán cho {$dup['count']} bookings khác nhau!\n\n";
        
        // Tìm các bookings conflict
        echo "     Các bookings conflict:\n";
        foreach ($oceanViewBookings as $booking) {
            if (in_array($dup['phong_id'], $booking->getPhongIds())) {
                echo "       • Booking #{$booking->id} - {$booking->trang_thai}\n";
            }
        }
        echo "\n";
    }
} else {
    echo "✅ KHÔNG CÓ DOUBLE BOOKING\n";
    echo "   Mỗi phòng chỉ được gán cho 1 booking.\n\n";
}

// Kiểm tra tổng số phòng cần vs có
$totalNeeded = 0;
foreach ($oceanViewBookings as $booking) {
    $roomTypes = $booking->getRoomTypes();
    foreach ($roomTypes as $rt) {
        if ($rt['loai_phong_id'] == $oceanView->id) {
            $totalNeeded += $rt['so_luong'];
        }
    }
}

$totalAvailable = $oceanView->so_luong_phong;
$totalAssigned = count(array_unique($allAssignedRooms));

echo "TỔNG KẾT SỐ LƯỢNG:\n";
echo "  • Tổng phòng Ocean View có: {$totalAvailable}\n";
echo "  • Tổng phòng cần: {$totalNeeded}\n";
echo "  • Tổng phòng đã gán (unique): {$totalAssigned}\n";

if ($totalNeeded > $totalAvailable) {
    echo "  ❌ OVERBOOKING: Đặt quá số lượng phòng có!\n";
} elseif ($totalAssigned < $totalNeeded) {
    echo "  ⚠️ UNDER-ASSIGNED: Chưa gán đủ phòng!\n";
} else {
    echo "  ✅ Số lượng hợp lý\n";
}

echo "\n";

// KẾT LUẬN
echo "================================================================================\n";
echo "                              KẾT LUẬN\n";
echo "================================================================================\n\n";

if (!empty($duplicates)) {
    echo "🚨 PHÁT HIỆN LỖI NGHIÊM TRỌNG: DOUBLE BOOKING\n\n";
    echo "Nguyên nhân có thể:\n";
    echo "  1. Race condition - Hai request đặt phòng cùng lúc\n";
    echo "  2. Logic kiểm tra phòng trống bị lỗi\n";
    echo "  3. Lock không hoạt động đúng\n";
    echo "  4. Admin gán thủ công không kiểm tra conflict\n\n";
    echo "Hành động cần thiết:\n";
    echo "  • Kiểm tra lại logic findAvailableRooms()\n";
    echo "  • Kiểm tra lại logic isAvailableInPeriod()\n";
    echo "  • Verify lockForUpdate() hoạt động\n";
    echo "  • Check transaction rollback\n\n";
    exit(1);
} else {
    echo "✅ Không phát hiện double booking.\n";
    echo "   Tuy nhiên nếu có 2 bookings, cần kiểm tra xem có đủ phòng không.\n\n";
    
    if ($totalNeeded > $totalAvailable) {
        echo "⚠️ Nhưng có OVERBOOKING - đặt quá số phòng có!\n";
        exit(1);
    }
    
    exit(0);
}




