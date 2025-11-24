<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DatPhong;
use App\Models\Phong;
use Illuminate\Support\Facades\DB;

echo "================================================================================\n";
echo "                 FIX DOUBLE BOOKING DATA - CANCEL CONFLICT\n";
echo "================================================================================\n\n";

// Find the two conflict bookings
$booking19 = DatPhong::find(19);
$booking21 = DatPhong::find(21);

if (!$booking19 || !$booking21) {
    echo "Không tìm thấy booking #19 hoặc #21\n";
    exit(1);
}

echo "TRƯỚC KHI FIX:\n";
echo "─────────────────────────────────────────────────────────────────\n";
echo "Booking #19:\n";
echo "  • Trạng thái: {$booking19->trang_thai}\n";
echo "  • Ngày: {$booking19->ngay_nhan} → {$booking19->ngay_tra}\n";
echo "  • Số người: {$booking19->so_nguoi}\n";
echo "  • Phòng: " . implode(', ', $booking19->getPhongIds()) . "\n\n";

echo "Booking #21:\n";
echo "  • Trạng thái: {$booking21->trang_thai}\n";
echo "  • Ngày: {$booking21->ngay_nhan} → {$booking21->ngay_tra}\n";
echo "  • Số người: {$booking21->so_nguoi}\n";
echo "  • Phòng: " . implode(', ', $booking21->getPhongIds()) . "\n\n";

// Quyết định hủy booking nào (chọn booking có ít người hơn)
$bookingToCancel = $booking19->so_nguoi > $booking21->so_nguoi ? $booking21 : $booking19;
$bookingToKeep = $bookingToCancel->id == 19 ? $booking21 : $booking19;

echo "QUYẾT ĐỊNH:\n";
echo "  • Giữ lại: Booking #{$bookingToKeep->id} (nhiều người hơn hoặc được tạo trước)\n";
echo "  • Hủy: Booking #{$bookingToCancel->id}\n\n";

echo "Đang hủy booking #{$bookingToCancel->id}...\n";

DB::transaction(function() use ($bookingToCancel) {
    $phongIds = $bookingToCancel->getPhongIds();
    
    // 1. Detach all rooms from pivot table
    $bookingToCancel->phongs()->detach();
    echo "  ✓ Đã detach phòng khỏi pivot table\n";
    
    // 2. Detach all room types from pivot table
    $bookingToCancel->roomTypes()->detach();
    echo "  ✓ Đã detach loại phòng khỏi pivot table\n";
    
    // 3. Update booking status to cancelled
    $bookingToCancel->update([
        'trang_thai' => 'da_huy',
        'ly_do_huy' => 'Hủy do double booking - Lỗi hệ thống đã được fix'
    ]);
    echo "  ✓ Đã cập nhật trạng thái booking: da_huy\n";
    
    // 4. Check và update room status nếu cần
    foreach ($phongIds as $phongId) {
        $phong = Phong::find($phongId);
        if ($phong) {
            // Check if this room has any other active booking
            $hasOtherBooking = DatPhong::whereHas('phongs', function($q) use ($phongId) {
                    $q->where('phong_id', $phongId);
                })
                ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                ->where('ngay_tra', '>', now())
                ->exists();
            
            if (!$hasOtherBooking && $phong->trang_thai == 'dang_thue') {
                $phong->update(['trang_thai' => 'trong']);
                echo "  ✓ Đã cập nhật phòng #{$phong->so_phong} → 'trong'\n";
            } else if ($hasOtherBooking) {
                echo "  ℹ Phòng #{$phong->so_phong} vẫn 'dang_thue' (có booking khác)\n";
            }
        }
    }
});

echo "\n";

echo "SAU KHI FIX:\n";
echo "─────────────────────────────────────────────────────────────────\n";

$booking19->refresh();
$booking21->refresh();

echo "Booking #19:\n";
echo "  • Trạng thái: {$booking19->trang_thai}\n";
echo "  • Phòng: " . implode(', ', $booking19->getPhongIds()) . "\n\n";

echo "Booking #21:\n";
echo "  • Trạng thái: {$booking21->trang_thai}\n";
echo "  • Phòng: " . implode(', ', $booking21->getPhongIds()) . "\n\n";

// Verify no more conflicts
echo "KIỂM TRA CONFLICTS:\n";
$oceanViewPhongs = Phong::where('loai_phong_id', 6)->get();

$conflicts = false;
foreach ($oceanViewPhongs as $phong) {
    $bookingsForThisRoom = DatPhong::whereHas('phongs', function($q) use ($phong) {
            $q->where('phong_id', $phong->id);
        })
        ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
        ->where(function($q) {
            $q->where('ngay_tra', '>', '2025-11-21')
              ->where('ngay_nhan', '<', '2025-11-22');
        })
        ->count();
    
    if ($bookingsForThisRoom > 1) {
        echo "  ✗ Phòng #{$phong->so_phong} vẫn có {$bookingsForThisRoom} bookings conflict!\n";
        $conflicts = true;
    }
}

if (!$conflicts) {
    echo "  ✅ KHÔNG CÒN CONFLICT!\n";
    echo "  ✅ Mỗi phòng chỉ thuộc 1 booking duy nhất\n";
}

echo "\n";
echo "================================================================================\n";
echo "                              HOÀN THÀNH\n";
echo "================================================================================\n\n";

if (!$conflicts) {
    echo "✅ Đã fix xong double booking data!\n";
    echo "✅ Hệ thống đã sẵn sàng hoạt động bình thường\n\n";
    exit(0);
} else {
    echo "⚠️ Vẫn còn conflicts - cần kiểm tra thêm\n\n";
    exit(1);
}




