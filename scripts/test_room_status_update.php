<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Phong;
use App\Models\LoaiPhong;
use Illuminate\Support\Facades\DB;

echo "🧪 TEST CẬP NHẬT SO_LUONG_TRONG KHI THAY ĐỔI TRẠNG THÁI PHÒNG\n";
echo str_repeat('=', 60) . "\n\n";

// Lấy một loại phòng để test
$loaiPhong = LoaiPhong::where('trang_thai', 'hoat_dong')->first();

if (!$loaiPhong) {
    echo "❌ Không tìm thấy loại phòng nào để test\n";
    exit(1);
}

echo "📋 Loại phòng: {$loaiPhong->ten_loai} (ID: {$loaiPhong->id})\n";
echo "   - Tổng số phòng: {$loaiPhong->so_luong_phong}\n";
echo "   - Số phòng trống (trước): {$loaiPhong->so_luong_trong}\n\n";

// Đếm số phòng thực tế có trang_thai = 'trong'
$trongCountActual = Phong::where('loai_phong_id', $loaiPhong->id)
    ->where('trang_thai', 'trong')
    ->count();

echo "📊 Số phòng 'trong' thực tế: {$trongCountActual}\n";

if ($trongCountActual != $loaiPhong->so_luong_trong) {
    echo "⚠️  Phát hiện không khớp! Đang cập nhật...\n";
    $loaiPhong->update(['so_luong_trong' => $trongCountActual]);
    echo "✅ Đã cập nhật so_luong_trong = {$trongCountActual}\n\n";
} else {
    echo "✅ Giá trị đã chính xác\n\n";
}

// Lấy một phòng để test
$phong = Phong::where('loai_phong_id', $loaiPhong->id)->first();

if (!$phong) {
    echo "❌ Không tìm thấy phòng nào để test\n";
    exit(1);
}

echo "🔍 Test với phòng: {$phong->so_phong} (ID: {$phong->id})\n";
echo "   - Trạng thái hiện tại: {$phong->trang_thai}\n\n";

// Test 1: Chuyển từ 'trong' sang 'dang_don'
if ($phong->trang_thai === 'trong') {
    echo "📝 TEST 1: Chuyển từ 'trong' → 'dang_don'\n";
    $oldTrongCount = $loaiPhong->fresh()->so_luong_trong;

    $phong->update(['trang_thai' => 'dang_don']);
    $loaiPhong->refresh();
    $newTrongCount = $loaiPhong->so_luong_trong;

    echo "   - Số phòng trống trước: {$oldTrongCount}\n";
    echo "   - Số phòng trống sau: {$newTrongCount}\n";

    if ($newTrongCount == $oldTrongCount - 1) {
        echo "   ✅ PASS: Số phòng trống đã giảm đúng\n\n";
    } else {
        echo "   ❌ FAIL: Số phòng trống không giảm đúng\n\n";
    }

    // Chuyển lại về 'trong' để test tiếp
    $phong->update(['trang_thai' => 'trong']);
    $loaiPhong->refresh();
}

// Test 2: Chuyển từ 'trong' sang 'bao_tri'
if ($phong->trang_thai === 'trong') {
    echo "📝 TEST 2: Chuyển từ 'trong' → 'bao_tri'\n";
    $oldTrongCount = $loaiPhong->fresh()->so_luong_trong;

    $phong->update(['trang_thai' => 'bao_tri']);
    $loaiPhong->refresh();
    $newTrongCount = $loaiPhong->so_luong_trong;

    echo "   - Số phòng trống trước: {$oldTrongCount}\n";
    echo "   - Số phòng trống sau: {$newTrongCount}\n";

    if ($newTrongCount == $oldTrongCount - 1) {
        echo "   ✅ PASS: Số phòng trống đã giảm đúng\n\n";
    } else {
        echo "   ❌ FAIL: Số phòng trống không giảm đúng\n\n";
    }

    // Chuyển lại về 'trong'
    $phong->update(['trang_thai' => 'trong']);
    $loaiPhong->refresh();
}

// Test 3: Chuyển từ 'dang_don' về 'trong'
if ($phong->trang_thai === 'trong') {
    $phong->update(['trang_thai' => 'dang_don']);
    $loaiPhong->refresh();

    echo "📝 TEST 3: Chuyển từ 'dang_don' → 'trong'\n";
    $oldTrongCount = $loaiPhong->fresh()->so_luong_trong;

    $phong->update(['trang_thai' => 'trong']);
    $loaiPhong->refresh();
    $newTrongCount = $loaiPhong->so_luong_trong;

    echo "   - Số phòng trống trước: {$oldTrongCount}\n";
    echo "   - Số phòng trống sau: {$newTrongCount}\n";

    if ($newTrongCount == $oldTrongCount + 1) {
        echo "   ✅ PASS: Số phòng trống đã tăng đúng\n\n";
    } else {
        echo "   ❌ FAIL: Số phòng trống không tăng đúng\n\n";
    }
}

// Kiểm tra lại giá trị cuối cùng
$loaiPhong->refresh();
$finalTrongCount = Phong::where('loai_phong_id', $loaiPhong->id)
    ->where('trang_thai', 'trong')
    ->count();

echo "📊 KẾT QUẢ CUỐI CÙNG:\n";
echo "   - Số phòng 'trong' thực tế: {$finalTrongCount}\n";
echo "   - so_luong_trong trong DB: {$loaiPhong->so_luong_trong}\n";

if ($finalTrongCount == $loaiPhong->so_luong_trong) {
    echo "   ✅ PASS: Giá trị đã đồng bộ chính xác!\n";
} else {
    echo "   ❌ FAIL: Giá trị chưa đồng bộ!\n";
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "✅ Hoàn tất test!\n";

