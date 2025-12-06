<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Phong;
use App\Models\DatPhong;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoCleanRooms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rooms:auto-clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động chuyển phòng từ dang_don về trong sau khi ngày checkout đã qua';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        
        // Tìm các phòng đang ở trạng thái 'dang_don'
        $dangDonRooms = Phong::where('trang_thai', 'dang_don')->get();
        
        $cleanedCount = 0;
        
        foreach ($dangDonRooms as $phong) {
            // Tìm booking gần nhất đã checkout cho phòng này
            $lastCheckout = DatPhong::whereHas('phongs', function($q) use ($phong) {
                    $q->where('phong_id', $phong->id);
                })
                ->where('trang_thai', 'da_tra')
                ->whereNotNull('thoi_gian_checkout')
                ->orderBy('thoi_gian_checkout', 'desc')
                ->first();
            
            if ($lastCheckout) {
                $checkoutDate = Carbon::parse($lastCheckout->thoi_gian_checkout)->startOfDay();
                
                // Nếu đã qua ngày checkout (ví dụ: sau 1 ngày), chuyển về 'trong'
                // Kiểm tra xem có booking conflict trong tương lai không
                $hasFutureBooking = DatPhong::whereHas('phongs', function($q) use ($phong) {
                        $q->where('phong_id', $phong->id);
                    })
                    ->where(function($q) use ($today) {
                        $q->where('ngay_tra', '>', $today)
                          ->where('ngay_nhan', '>', $today);
                    })
                    ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                    ->exists();
                
                // Chuyển về 'trong' nếu:
                // 1. Đã qua ngày checkout (sau 1 ngày)
                // 2. Không có booking conflict trong tương lai
                if ($today->gt($checkoutDate->copy()->addDay()) && !$hasFutureBooking) {
                    DB::transaction(function () use ($phong, $lastCheckout) {
                        $phong->update(['trang_thai' => 'trong']);
                        
                        // Recalculate so_luong_trong cho loại phòng
                        $loaiPhongId = $phong->loai_phong_id;
                        $this->recalculateSoLuongTrong($loaiPhongId);
                    });
                    
                    $cleanedCount++;
                    Log::info("Auto cleaned room {$phong->so_phong} (ID: {$phong->id}) from dang_don to trong");
                }
            } else {
                // Nếu không tìm thấy booking checkout, có thể là phòng bị stuck ở dang_don
                // Chuyển về 'trong' nếu không có booking conflict
                $hasConflict = DatPhong::whereHas('phongs', function($q) use ($phong) {
                        $q->where('phong_id', $phong->id);
                    })
                    ->where(function($q) use ($today) {
                        $q->where('ngay_tra', '>', $today);
                    })
                    ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                    ->exists();
                
                if (!$hasConflict) {
                    DB::transaction(function () use ($phong) {
                        $phong->update(['trang_thai' => 'trong']);
                        
                        $loaiPhongId = $phong->loai_phong_id;
                        $this->recalculateSoLuongTrong($loaiPhongId);
                    });
                    
                    $cleanedCount++;
                    Log::info("Auto cleaned stuck room {$phong->so_phong} (ID: {$phong->id}) from dang_don to trong");
                }
            }
        }
        
        $this->info("Đã tự động chuyển {$cleanedCount} phòng từ 'dang_don' về 'trong'");
        
        return 0;
    }
    
    /**
     * Recalculate so_luong_trong for a room type
     */
    private function recalculateSoLuongTrong(int $loaiPhongId): void
    {
        $trongCount = Phong::where('loai_phong_id', $loaiPhongId)
            ->where('trang_thai', 'trong')
            ->count();
        
        \App\Models\LoaiPhong::where('id', $loaiPhongId)
            ->update(['so_luong_trong' => $trongCount]);
    }
}

