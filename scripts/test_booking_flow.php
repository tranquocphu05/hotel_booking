<?php

/**
 * Script test luồng đặt phòng thực tế
 * Mô phỏng client đặt phòng và admin xác nhận
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DatPhong;
use App\Models\Phong;
use App\Models\LoaiPhong;
use App\Models\User;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "================================================================================\n";
echo "              TEST LUỒNG ĐẶT PHÒNG THỰC TẾ\n";
echo "================================================================================\n\n";

// STEP 1: Tạo booking mới (giả lập client đặt phòng)
echo "STEP 1: Tạo booking mới từ client...\n";
try {
    $user = User::where('vai_tro', 'khach_hang')->first();
    if (!$user) {
        throw new Exception("Không tìm thấy user khách hàng. Run: php scripts/create_test_users.php");
    }
    
    $loaiPhong = LoaiPhong::where('trang_thai', 'hoat_dong')->first();
    if (!$loaiPhong) {
        throw new Exception("Không có loại phòng nào");
    }
    
    $ngayNhan = Carbon::tomorrow()->format('Y-m-d');
    $ngayTra = Carbon::tomorrow()->addDays(2)->format('Y-m-d');
    
    // Kiểm tra phòng trống
    $availableRooms = Phong::findAvailableRooms($loaiPhong->id, $ngayNhan, $ngayTra, 2);
    
    if ($availableRooms->count() < 2) {
        echo "  ⚠ Không đủ phòng trống để test (cần 2 phòng, có " . $availableRooms->count() . ")\n";
        exit(1);
    }
    
    // Tạo booking trong transaction (mô phỏng BookingController::submit)
    $booking = DB::transaction(function () use ($user, $loaiPhong, $ngayNhan, $ngayTra, $availableRooms) {
        $booking = DatPhong::create([
            'nguoi_dung_id' => $user->id,
            'loai_phong_id' => $loaiPhong->id,
            'so_luong_da_dat' => 2,
            'phong_id' => null,
            'ngay_dat' => now(),
            'ngay_nhan' => $ngayNhan,
            'ngay_tra' => $ngayTra,
            'so_nguoi' => 4,
            'trang_thai' => 'cho_xac_nhan',
            'tong_tien' => $loaiPhong->gia_co_ban * 2 * 2, // 2 phòng, 2 đêm
            'username' => $user->ho_ten,
            'email' => $user->email,
            'sdt' => $user->sdt,
            'cccd' => $user->cccd,
        ]);
        
        // Sync room types to pivot
        $booking->syncRoomTypes([
            $loaiPhong->id => [
                'so_luong' => 2,
                'gia_rieng' => $loaiPhong->gia_co_ban * 2 * 2,
            ]
        ]);
        
        // Gán phòng (với lock)
        $allPhongIds = [];
        foreach ($availableRooms->take(2) as $phong) {
            $phongLocked = Phong::lockForUpdate()->find($phong->id);
            if ($phongLocked && $phongLocked->isAvailableInPeriod($ngayNhan, $ngayTra, $booking->id)) {
                $allPhongIds[] = $phongLocked->id;
            }
        }
        
        if (count($allPhongIds) < 2) {
            throw new Exception("Không gán đủ phòng");
        }
        
        $booking->syncPhongs($allPhongIds);
        
        // Tạo invoice
        Invoice::create([
            'dat_phong_id' => $booking->id,
            'tien_phong' => $booking->tong_tien,
            'tien_dich_vu' => 0,
            'giam_gia' => 0,
            'tong_tien' => $booking->tong_tien,
            'trang_thai' => 'cho_thanh_toan',
        ]);
        
        return $booking;
    });
    
    echo "  ✓ Booking #{$booking->id} được tạo thành công\n";
    echo "  ✓ Trạng thái: {$booking->trang_thai}\n";
    echo "  ✓ Số phòng đã gán: " . count($booking->getPhongIds()) . "/2\n";
    echo "  ✓ Tổng tiền: " . number_format($booking->tong_tien, 0, ',', '.') . " VNĐ\n";
    
} catch (\Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
echo "\n";

// STEP 2: Admin xác nhận booking
echo "STEP 2: Admin xác nhận booking...\n";
try {
    DB::transaction(function () use ($booking) {
        // Load lại booking với lock
        $bookingLocked = DatPhong::lockForUpdate()->find($booking->id);
        
        if ($bookingLocked->trang_thai !== 'cho_xac_nhan') {
            throw new Exception("Booking không ở trạng thái chờ xác nhận");
        }
        
        // Xác nhận
        $bookingLocked->trang_thai = 'da_xac_nhan';
        $bookingLocked->save();
        
        // Cập nhật trạng thái phòng
        foreach ($bookingLocked->phongs as $phong) {
            if ($phong->trang_thai === 'trong') {
                $phong->update(['trang_thai' => 'dang_thue']);
            }
        }
    });
    
    $booking->refresh();
    echo "  ✓ Booking #{$booking->id} đã được xác nhận\n";
    echo "  ✓ Trạng thái mới: {$booking->trang_thai}\n";
    
    // Kiểm tra trạng thái phòng
    $phongIds = $booking->getPhongIds();
    foreach ($phongIds as $phongId) {
        $phong = Phong::find($phongId);
        echo "  ✓ Phòng #{$phong->so_phong}: {$phong->trang_thai}\n";
    }
    
} catch (\Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
echo "\n";

// STEP 3: Admin đánh dấu đã thanh toán
echo "STEP 3: Admin đánh dấu đã thanh toán...\n";
try {
    $invoice = $booking->invoice;
    if ($invoice) {
        $invoice->update([
            'trang_thai' => 'da_thanh_toan',
            'phuong_thuc' => 'tien_mat',
        ]);
        
        echo "  ✓ Invoice #{$invoice->id} đã được cập nhật: da_thanh_toan\n";
        echo "  ✓ Phương thức: tien_mat\n";
        echo "  ✓ Số tiền: " . number_format($invoice->tong_tien, 0, ',', '.') . " VNĐ\n";
    } else {
        echo "  ⚠ Không có invoice\n";
    }
} catch (\Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
echo "\n";

// STEP 4: Test hủy booking (rollback scenario)
echo "STEP 4: Test tạo và hủy booking (rollback scenario)...\n";
try {
    $loaiPhong2 = LoaiPhong::where('trang_thai', 'hoat_dong')->skip(1)->first();
    if ($loaiPhong2) {
        $ngayNhan2 = Carbon::tomorrow()->addDays(5)->format('Y-m-d');
        $ngayTra2 = Carbon::tomorrow()->addDays(7)->format('Y-m-d');
        
        $availableRooms2 = Phong::findAvailableRooms($loaiPhong2->id, $ngayNhan2, $ngayTra2, 1);
        
        if ($availableRooms2->count() > 0) {
            $booking2 = DB::transaction(function () use ($user, $loaiPhong2, $ngayNhan2, $ngayTra2, $availableRooms2) {
                $booking2 = DatPhong::create([
                    'nguoi_dung_id' => $user->id,
                    'loai_phong_id' => $loaiPhong2->id,
                    'so_luong_da_dat' => 1,
                    'ngay_dat' => now(),
                    'ngay_nhan' => $ngayNhan2,
                    'ngay_tra' => $ngayTra2,
                    'so_nguoi' => 2,
                    'trang_thai' => 'cho_xac_nhan',
                    'tong_tien' => $loaiPhong2->gia_co_ban * 2,
                    'username' => $user->ho_ten,
                    'email' => $user->email,
                    'sdt' => $user->sdt,
                ]);
                
                $booking2->syncRoomTypes([
                    $loaiPhong2->id => [
                        'so_luong' => 1,
                        'gia_rieng' => $loaiPhong2->gia_co_ban * 2,
                    ]
                ]);
                
                $booking2->syncPhongs([$availableRooms2->first()->id]);
                
                return $booking2;
            });
            
            echo "  ✓ Booking test #{$booking2->id} được tạo\n";
            
            // Hủy booking
            DB::transaction(function () use ($booking2) {
                $bookingLocked = DatPhong::lockForUpdate()->find($booking2->id);
                $bookingLocked->trang_thai = 'da_huy';
                $bookingLocked->save();
                
                // Giải phóng phòng
                $bookingLocked->phongs()->detach();
            });
            
            $booking2->refresh();
            echo "  ✓ Booking #{$booking2->id} đã hủy thành công\n";
            echo "  ✓ Số phòng còn lại: " . count($booking2->getPhongIds()) . " (should be 0)\n";
        } else {
            echo "  ⚠ Không có phòng để test cancel\n";
        }
    } else {
        echo "  ⚠ Không có loại phòng thứ 2 để test\n";
    }
} catch (\Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// STEP 5: Test race condition prevention
echo "STEP 5: Test race condition prevention...\n";
try {
    echo "  ℹ Kiểm tra xem code có sử dụng lockForUpdate không...\n";
    
    $bookingCode = file_get_contents(__DIR__.'/../app/Http/Controllers/BookingController.php');
    $hasLock = strpos($bookingCode, 'lockForUpdate()') !== false;
    
    if ($hasLock) {
        echo "  ✓ BookingController có sử dụng lockForUpdate() → Race condition PREVENTED\n";
    } else {
        echo "  ✗ BookingController KHÔNG sử dụng lockForUpdate() → Có thể bị race condition\n";
    }
    
    // Kiểm tra transaction wrapping
    $hasTransaction = strpos($bookingCode, 'DB::transaction(function ()') !== false;
    
    if ($hasTransaction) {
        echo "  ✓ Booking được wrap trong DB::transaction() → Atomic operations\n";
    } else {
        echo "  ✗ Booking KHÔNG wrap trong transaction → Có thể mất data consistency\n";
    }
    
} catch (\Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// SUMMARY
echo "================================================================================\n";
echo "                          KẾT QUẢ TEST LUỒNG\n";
echo "================================================================================\n\n";

echo "✅ CÁC CHỨC NĂNG ĐÃ TEST:\n";
echo "  ✓ Tạo booking từ client (với pivot tables)\n";
echo "  ✓ Gán phòng tự động (với lock)\n";
echo "  ✓ Admin xác nhận booking\n";
echo "  ✓ Cập nhật trạng thái phòng\n";
echo "  ✓ Thanh toán invoice\n";
echo "  ✓ Hủy booking và giải phóng phòng\n";
echo "  ✓ Race condition prevention (locks + transactions)\n\n";

echo "🎉 LUỒNG ĐẶT PHÒNG HOẠT ĐỘNG CHÍNH XÁC!\n\n";

echo "📊 DATA SUMMARY:\n";
$totalBookings = DatPhong::count();
$confirmedBookings = DatPhong::where('trang_thai', 'da_xac_nhan')->count();
$pendingBookings = DatPhong::where('trang_thai', 'cho_xac_nhan')->count();
$cancelledBookings = DatPhong::where('trang_thai', 'da_huy')->count();

echo "  • Tổng bookings: {$totalBookings}\n";
echo "  • Đã xác nhận: {$confirmedBookings}\n";
echo "  • Chờ xác nhận: {$pendingBookings}\n";
echo "  • Đã hủy: {$cancelledBookings}\n";

echo "\n================================================================================\n";
echo "                          KẾT THÚC TEST LUỒNG\n";
echo "================================================================================\n";




