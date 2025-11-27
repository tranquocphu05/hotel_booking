<?php

/**
 * Script Test Hủy Booking và Xử Lý Hoàn Tiền
 * 
 * Script này test các trường hợp hủy booking mà không cần tạo database test riêng
 * Chạy: php scripts/test_booking_cancellation.php
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Models\DatPhong;
use App\Models\Invoice;
use App\Models\LoaiPhong;
use App\Models\Phong;
use App\Models\ThanhToan;
use App\Models\User;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "========================================\n";
echo "TEST HỦY BOOKING VÀ XỬ LÝ HOÀN TIỀN\n";
echo "========================================\n\n";

$passed = 0;
$failed = 0;
$tests = [];

/**
 * Helper function để test
 */
function test($name, $callback) {
    global $passed, $failed, $tests;
    
    echo "🧪 Test: $name\n";
    try {
        $result = $callback();
        if ($result === true) {
            echo "   ✅ PASSED\n\n";
            $passed++;
            $tests[] = ['name' => $name, 'status' => 'passed'];
        } else {
            echo "   ❌ FAILED: $result\n\n";
            $failed++;
            $tests[] = ['name' => $name, 'status' => 'failed', 'error' => $result];
        }
    } catch (\Exception $e) {
        echo "   ❌ FAILED: " . $e->getMessage() . "\n";
        echo "   Stack trace: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
        $failed++;
        $tests[] = ['name' => $name, 'status' => 'failed', 'error' => $e->getMessage()];
    }
}

/**
 * Test Case 1: Tính toán chính sách hoàn tiền - Hủy trước 7 ngày (100%)
 */
test("Tính toán hoàn tiền - Hủy trước 7 ngày (100%)", function() {
    $checkinDate = Carbon::now()->addDays(10);
    $booking = new DatPhong();
    $booking->ngay_nhan = $checkinDate->format('Y-m-d');
    $booking->tong_tien = 2000000;
    
    $controller = new \App\Http\Controllers\Admin\DatPhongController();
    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('calculateCancellationPolicy');
    $method->setAccessible(true);
    
    $policy = $method->invoke($controller, $booking);
    
    if ($policy['refund_percentage'] !== 100) {
        return "Expected 100%, got {$policy['refund_percentage']}%";
    }
    if (abs($policy['refund_amount'] - 2000000) > 0.01) {
        return "Expected 2,000,000, got {$policy['refund_amount']}";
    }
    return true;
});

/**
 * Test Case 2: Tính toán chính sách hoàn tiền - Hủy 3-6 ngày (50%)
 */
test("Tính toán hoàn tiền - Hủy 3-6 ngày (50%)", function() {
    $checkinDate = Carbon::now()->addDays(5);
    $booking = new DatPhong();
    $booking->ngay_nhan = $checkinDate->format('Y-m-d');
    $booking->tong_tien = 2000000;
    
    $controller = new \App\Http\Controllers\Admin\DatPhongController();
    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('calculateCancellationPolicy');
    $method->setAccessible(true);
    
    $policy = $method->invoke($controller, $booking);
    
    if ($policy['refund_percentage'] !== 50) {
        return "Expected 50%, got {$policy['refund_percentage']}%";
    }
    if (abs($policy['refund_amount'] - 1000000) > 0.01) {
        return "Expected 1,000,000, got {$policy['refund_amount']}";
    }
    return true;
});

/**
 * Test Case 3: Tính toán chính sách hoàn tiền - Hủy 1-2 ngày (25%)
 */
test("Tính toán hoàn tiền - Hủy 1-2 ngày (25%)", function() {
    $checkinDate = Carbon::now()->addDays(2);
    $booking = new DatPhong();
    $booking->ngay_nhan = $checkinDate->format('Y-m-d');
    $booking->tong_tien = 2000000;
    
    $controller = new \App\Http\Controllers\Admin\DatPhongController();
    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('calculateCancellationPolicy');
    $method->setAccessible(true);
    
    $policy = $method->invoke($controller, $booking);
    
    if ($policy['refund_percentage'] !== 25) {
        return "Expected 25%, got {$policy['refund_percentage']}%";
    }
    if (abs($policy['refund_amount'] - 500000) > 0.01) {
        return "Expected 500,000, got {$policy['refund_amount']}";
    }
    return true;
});

/**
 * Test Case 4: Tính toán chính sách hoàn tiền - Hủy trong ngày (0%)
 */
test("Tính toán hoàn tiền - Hủy trong ngày (0%)", function() {
    $checkinDate = Carbon::today();
    $booking = new DatPhong();
    $booking->ngay_nhan = $checkinDate->format('Y-m-d');
    $booking->tong_tien = 2000000;
    
    $controller = new \App\Http\Controllers\Admin\DatPhongController();
    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('calculateCancellationPolicy');
    $method->setAccessible(true);
    
    $policy = $method->invoke($controller, $booking);
    
    if ($policy['refund_percentage'] !== 0) {
        return "Expected 0%, got {$policy['refund_percentage']}%";
    }
    if ($policy['refund_amount'] !== 0) {
        return "Expected 0, got {$policy['refund_amount']}";
    }
    return true;
});

/**
 * Test Case 5: Validation - Không thể hủy booking đã check-in
 */
test("Validation - Không thể hủy booking đã check-in", function() {
    $booking = new DatPhong();
    $booking->trang_thai = 'da_xac_nhan';
    $booking->thoi_gian_checkin = Carbon::now()->subHours(2);
    
    try {
        $booking->validateStatusTransition('da_huy');
        return "Expected validation error but none was thrown";
    } catch (\Illuminate\Validation\ValidationException $e) {
        $errors = $e->errors();
        if (isset($errors['trang_thai']) && 
            str_contains($imploded = implode(' ', $errors['trang_thai']), 'check-in')) {
            return true;
        }
        return "Expected error about check-in, got: " . json_encode($errors);
    }
});

/**
 * Test Case 6: Validation - Không thể thay đổi trạng thái terminal
 */
test("Validation - Không thể thay đổi trạng thái terminal", function() {
    $booking = new DatPhong();
    $booking->trang_thai = 'da_tra'; // Terminal state
    
    try {
        $booking->validateStatusTransition('da_huy', 'da_tra');
        return "Expected validation error but none was thrown";
    } catch (\Illuminate\Validation\ValidationException $e) {
        $errors = $e->errors();
        if (isset($errors['trang_thai']) && 
            str_contains($imploded = implode(' ', $errors['trang_thai']), 'terminal') ||
            str_contains($imploded, 'cuối cùng')) {
            return true;
        }
        return "Expected error about terminal state, got: " . json_encode($errors);
    }
});

/**
 * Test Case 7: Validation - Chuyển đổi hợp lệ từ cho_xac_nhan sang da_huy
 */
test("Validation - Chuyển đổi hợp lệ từ cho_xac_nhan sang da_huy", function() {
    $booking = new DatPhong();
    $booking->trang_thai = 'cho_xac_nhan';
    $booking->thoi_gian_checkin = null; // Chưa check-in
    
    try {
        $result = $booking->validateStatusTransition('da_huy', 'cho_xac_nhan');
        if ($result === true) {
            return true;
        }
        return "Expected true, got: " . var_export($result, true);
    } catch (\Exception $e) {
        return "Unexpected error: " . $e->getMessage();
    }
});

/**
 * Test Case 8: Validation - Chuyển đổi hợp lệ từ cho_xac_nhan sang da_xac_nhan
 */
test("Validation - Chuyển đổi hợp lệ từ cho_xac_nhan sang da_xac_nhan", function() {
    $booking = new DatPhong();
    $booking->trang_thai = 'cho_xac_nhan';
    
    try {
        $result = $booking->validateStatusTransition('da_xac_nhan', 'cho_xac_nhan');
        if ($result === true) {
            return true;
        }
        return "Expected true, got: " . var_export($result, true);
    } catch (\Exception $e) {
        return "Unexpected error: " . $e->getMessage();
    }
});

/**
 * Test Case 9: Validation - Không thể chuyển từ cho_xac_nhan sang da_tra
 */
test("Validation - Không thể chuyển từ cho_xac_nhan sang da_tra", function() {
    $booking = new DatPhong();
    $booking->trang_thai = 'cho_xac_nhan';
    
    try {
        $booking->validateStatusTransition('da_tra', 'cho_xac_nhan');
        return "Expected validation error but none was thrown";
    } catch (\Illuminate\Validation\ValidationException $e) {
        return true; // Expected error
    }
});

/**
 * Test Case 10: canCheckout() - Kiểm tra trạng thái
 */
test("canCheckout() - Kiểm tra trạng thái da_xac_nhan", function() {
    $booking = new DatPhong();
    $booking->trang_thai = 'da_xac_nhan';
    $booking->thoi_gian_checkin = Carbon::now();
    $booking->thoi_gian_checkout = null;
    
    if ($booking->canCheckout() !== true) {
        return "Expected canCheckout() to return true";
    }
    
    // Test với trạng thái không hợp lệ
    $booking->trang_thai = 'cho_xac_nhan';
    if ($booking->canCheckout() !== false) {
        return "Expected canCheckout() to return false for cho_xac_nhan status";
    }
    
    return true;
});

/**
 * Test Case 11: Kiểm tra logic tính số ngày
 */
test("Logic tính số ngày trước check-in", function() {
    $controller = new \App\Http\Controllers\Admin\DatPhongController();
    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('calculateCancellationPolicy');
    $method->setAccessible(true);
    
    // Test với 10 ngày
    $checkinDate = Carbon::now()->addDays(10);
    $booking = new DatPhong();
    $booking->ngay_nhan = $checkinDate->format('Y-m-d');
    $booking->tong_tien = 2000000;
    
    $policy = $method->invoke($controller, $booking);
    
    if ($policy['days_until_checkin'] < 7) {
        return "Expected days_until_checkin >= 7 for 10 days ahead";
    }
    
    // Test với 1 ngày - sử dụng startOfDay để đảm bảo tính chính xác
    $checkinDate = Carbon::today()->addDays(1)->startOfDay();
    $booking->ngay_nhan = $checkinDate->format('Y-m-d');
    $policy = $method->invoke($controller, $booking);
    
    // diffInDays có thể trả về số thập phân, làm tròn để so sánh
    $days = round($policy['days_until_checkin']);
    if ($days < 0 || $days > 2) {
        return "Expected days_until_checkin between 0-2 for 1 day ahead, got: {$policy['days_until_checkin']} (rounded: $days)";
    }
    
    return true;
});

// Tổng kết
echo "========================================\n";
echo "KẾT QUẢ TEST\n";
echo "========================================\n";
echo "✅ Passed: $passed\n";
echo "❌ Failed: $failed\n";
echo "📊 Total: " . ($passed + $failed) . "\n\n";

if ($failed > 0) {
    echo "Chi tiết các test failed:\n";
    foreach ($tests as $test) {
        if ($test['status'] === 'failed') {
            echo "  - {$test['name']}: {$test['error']}\n";
        }
    }
    echo "\n";
}

exit($failed > 0 ? 1 : 0);

