<?php

/**
 * Script để recalculate so_luong_trong cho tất cả loại phòng
 * Đồng bộ giá trị trong DB với số phòng trống thực tế
 * 
 * Run: php scripts/recalculate_so_luong_trong.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\LoaiPhong;
use App\Models\Phong;
use Illuminate\Support\Facades\DB;

echo "================================================================================\n";
echo "              RECALCULATE SO_LUONG_TRONG CHO TẤT CẢ LOẠI PHÒNG\n";
echo "================================================================================\n\n";

echo "Đang kiểm tra tất cả loại phòng...\n\n";

$loaiPhongs = LoaiPhong::all();
$totalUpdated = 0;
$totalCorrect = 0;
$details = [];

foreach ($loaiPhongs as $loaiPhong) {
    // Tính số phòng trống thực tế
    $trongCountActual = Phong::where('loai_phong_id', $loaiPhong->id)
        ->where('trang_thai', 'trong')
        ->count();
    
    // Lấy giá trị hiện tại trong DB
    $trongCountDB = $loaiPhong->so_luong_trong;
    
    $status = '';
    $needUpdate = false;
    
    if ($trongCountActual == $trongCountDB) {
        $status = '✓ OK';
        $totalCorrect++;
    } else {
        $status = '✗ SAI - Cần cập nhật';
        $needUpdate = true;
        $totalUpdated++;
    }
    
    $details[] = [
        'id' => $loaiPhong->id,
        'ten_loai' => $loaiPhong->ten_loai,
        'old_value' => $trongCountDB,
        'new_value' => $trongCountActual,
        'status' => $status,
        'need_update' => $needUpdate,
    ];
    
    echo sprintf(
        "%-30s | DB: %2d | Actual: %2d | %s\n",
        substr($loaiPhong->ten_loai, 0, 28),
        $trongCountDB,
        $trongCountActual,
        $status
    );
}

echo "\n";
echo "Tổng quan:\n";
echo "  • Tổng số loại phòng: " . count($loaiPhongs) . "\n";
echo "  • Đã chính xác: {$totalCorrect}\n";
echo "  • Cần cập nhật: {$totalUpdated}\n";
echo "\n";

if ($totalUpdated > 0) {
    echo "Bạn có muốn cập nhật các giá trị không chính xác? (yes/no): ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($line) === 'yes' || strtolower($line) === 'y') {
        echo "\nĐang cập nhật...\n\n";
        
        DB::transaction(function () use ($details) {
            foreach ($details as $detail) {
                if ($detail['need_update']) {
                    LoaiPhong::where('id', $detail['id'])
                        ->update(['so_luong_trong' => $detail['new_value']]);
                    
                    echo sprintf(
                        "  ✓ Updated: %-30s | %2d → %2d\n",
                        substr($detail['ten_loai'], 0, 28),
                        $detail['old_value'],
                        $detail['new_value']
                    );
                }
            }
        });
        
        echo "\n";
        echo "✅ Đã cập nhật thành công {$totalUpdated} loại phòng!\n";
        
        // Verify lại
        echo "\nVerifying...\n";
        $verified = 0;
        $failed = 0;
        
        foreach ($details as $detail) {
            if ($detail['need_update']) {
                $loaiPhong = LoaiPhong::find($detail['id']);
                $trongCountActual = Phong::where('loai_phong_id', $loaiPhong->id)
                    ->where('trang_thai', 'trong')
                    ->count();
                
                if ($loaiPhong->so_luong_trong == $trongCountActual) {
                    $verified++;
                } else {
                    $failed++;
                    echo "  ✗ Failed: {$detail['ten_loai']}\n";
                }
            }
        }
        
        echo "\n";
        echo "Verification Results:\n";
        echo "  ✓ Verified: {$verified}/{$totalUpdated}\n";
        if ($failed > 0) {
            echo "  ✗ Failed: {$failed}/{$totalUpdated}\n";
        }
        
    } else {
        echo "\nHủy cập nhật. Không có thay đổi nào được thực hiện.\n";
    }
} else {
    echo "✅ Tất cả các giá trị đã chính xác! Không cần cập nhật.\n";
}

echo "\n";
echo "================================================================================\n";
echo "                              KẾT THÚC\n";
echo "================================================================================\n";




