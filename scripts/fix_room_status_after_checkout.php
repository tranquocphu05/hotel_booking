<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DatPhong;
use App\Models\Phong;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "🔧 FIX ROOM STATUS AFTER CHECKOUT\n";
echo str_repeat('=', 60) . "\n\n";

// Lấy booking ID từ command line hoặc hardcode
$bookingId = $argv[1] ?? 9; // Default to booking 9 from the screenshot

echo "📋 Kiểm tra booking ID: {$bookingId}\n\n";

$booking = DatPhong::with(['phongs', 'loaiPhong'])->find($bookingId);

if (!$booking) {
    echo "❌ Không tìm thấy booking với ID: {$bookingId}\n";
    exit(1);
}

echo "📌 Thông tin booking:\n";
echo "   - Trạng thái: {$booking->trang_thai}\n";
echo "   - Ngày nhận: {$booking->ngay_nhan}\n";
echo "   - Ngày trả: {$booking->ngay_tra}\n";
echo "   - Thời gian checkout: " . ($booking->thoi_gian_checkout ?? 'NULL') . "\n";
echo "   - Phòng đã gán: " . $booking->getAssignedPhongs()->count() . " phòng\n\n";

if ($booking->trang_thai !== 'da_tra') {
    echo "⚠️  Booking chưa checkout (trạng thái: {$booking->trang_thai})\n";
    exit(1);
}

echo "🔍 Kiểm tra trạng thái phòng:\n";
$assignedPhongs = $booking->getAssignedPhongs();
$today = Carbon::today();

foreach ($assignedPhongs as $phong) {
    echo "\n   Phòng #{$phong->so_phong} (ID: {$phong->id}):\n";
    echo "   - Trạng thái hiện tại: {$phong->trang_thai}\n";

    // Kiểm tra booking conflict
    $hasFutureBooking = DatPhong::where('id', '!=', $booking->id)
        ->whereHas('phongs', function($q) use ($phong) {
            $q->where('phong_id', $phong->id);
        })
        ->where(function($q) use ($today) {
            // Kiểm tra booking trong tương lai (ngay_nhan > today) hoặc đang diễn ra (ngay_nhan <= today và ngay_tra > today)
            $q->where(function($subQ) use ($today) {
                // Booking trong tương lai
                $subQ->where('ngay_nhan', '>', $today)
                     ->where('ngay_tra', '>', $today);
            })
            ->orWhere(function($subQ) use ($today) {
                // Booking đang diễn ra
                $subQ->where('ngay_nhan', '<=', $today)
                     ->where('ngay_tra', '>', $today);
            });
        })
        ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
        ->get();

    if ($hasFutureBooking->count() > 0) {
        echo "   - ⚠️  Có {$hasFutureBooking->count()} booking conflict:\n";
        foreach ($hasFutureBooking as $conflictBooking) {
            echo "     * Booking #{$conflictBooking->id}: {$conflictBooking->ngay_nhan} → {$conflictBooking->ngay_tra} ({$conflictBooking->trang_thai})\n";
        }
        echo "   - ✅ Giữ trạng thái 'dang_thue' (phòng sẽ được dùng tiếp)\n";
    } else {
        echo "   - ✅ Không có booking conflict\n";

        if ($phong->trang_thai !== 'trong') {
            echo "   - 🔧 Đang chuyển phòng về 'trong'...\n";

            DB::transaction(function() use ($phong, $booking) {
                $phong->update(['trang_thai' => 'trong']);

                // Recalculate so_luong_trong
                $loaiPhongId = $phong->loai_phong_id;
                $trongCount = Phong::where('loai_phong_id', $loaiPhongId)
                    ->where('trang_thai', 'trong')
                    ->count();

                \App\Models\LoaiPhong::where('id', $loaiPhongId)
                    ->update(['so_luong_trong' => $trongCount]);

                echo "   - ✅ Đã chuyển phòng #{$phong->so_phong} về 'trong'\n";
                echo "   - ✅ Đã cập nhật so_luong_trong cho loại phòng ID: {$loaiPhongId}\n";
            });
        } else {
            echo "   - ✅ Phòng đã ở trạng thái 'trong'\n";
        }
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "✅ Hoàn tất!\n";

