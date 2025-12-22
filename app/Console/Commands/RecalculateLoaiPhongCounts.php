<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LoaiPhong;
use App\Models\Phong;
use Illuminate\Support\Facades\DB;

class RecalculateLoaiPhongCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loai-phong:recalculate-counts 
                            {--force : Force update without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate so_luong_phong and so_luong_trong for all room types based on actual rooms in phong table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('================================================================================');
        $this->info('     RECALCULATE SO_LUONG_PHONG VÀ SO_LUONG_TRONG CHO TẤT CẢ LOẠI PHÒNG');
        $this->info('================================================================================');
        $this->newLine();

        $loaiPhongs = LoaiPhong::all();

        if ($loaiPhongs->isEmpty()) {
            $this->error('Không tìm thấy loại phòng nào!');
            return 1;
        }

        $this->info('Đang kiểm tra ' . $loaiPhongs->count() . ' loại phòng...');
        $this->newLine();

        $details = [];
        $totalUpdated = 0;

        foreach ($loaiPhongs as $loaiPhong) {
            // Tính số phòng thực tế thuộc loại này
            $totalPhongsActual = Phong::where('loai_phong_id', $loaiPhong->id)->count();
            
            // Tính số phòng trống thực tế
            $trongCountActual = Phong::where('loai_phong_id', $loaiPhong->id)
                ->whereIn('trang_thai', ['trong', 'dang_don'])
                ->count();
            
            $totalPhongsDB = $loaiPhong->so_luong_phong ?? 0;
            $trongCountDB = $loaiPhong->so_luong_trong ?? 0;
            
            $needUpdate = ($totalPhongsActual != $totalPhongsDB) || ($trongCountActual != $trongCountDB);
            
            if ($needUpdate) {
                $totalUpdated++;
                $this->line(sprintf(
                    "<fg=yellow>%-30s</> | Tổng: DB=<fg=red>%2d</>/Actual=<fg=green>%2d</> | Trống: DB=<fg=red>%2d</>/Actual=<fg=green>%2d</> | <fg=yellow>✗ Cần cập nhật</>",
                    substr($loaiPhong->ten_loai, 0, 28),
                    $totalPhongsDB,
                    $totalPhongsActual,
                    $trongCountDB,
                    $trongCountActual
                ));
            } else {
                $this->line(sprintf(
                    "<fg=cyan>%-30s</> | Tổng: %2d | Trống: %2d | <fg=green>✓ OK</>",
                    substr($loaiPhong->ten_loai, 0, 28),
                    $totalPhongsActual,
                    $trongCountActual
                ));
            }
            
            $details[] = [
                'id' => $loaiPhong->id,
                'ten_loai' => $loaiPhong->ten_loai,
                'old_total' => $totalPhongsDB,
                'new_total' => $totalPhongsActual,
                'old_trong' => $trongCountDB,
                'new_trong' => $trongCountActual,
                'need_update' => $needUpdate,
            ];
        }

        $this->newLine();
        $this->info('Tổng quan:');
        $this->line("  • Tổng số loại phòng: <fg=cyan>" . count($loaiPhongs) . "</>");
        $this->line("  • Cần cập nhật: <fg=yellow>{$totalUpdated}</>");
        $this->newLine();

        if ($totalUpdated > 0) {
            if (!$this->option('force')) {
                if (!$this->confirm('Bạn có muốn cập nhật các giá trị không chính xác?', true)) {
                    $this->warn('Hủy cập nhật. Không có thay đổi nào được thực hiện.');
                    return 0;
                }
            }

            $this->info('Đang cập nhật...');
            $this->newLine();

            DB::transaction(function () use ($details) {
                foreach ($details as $detail) {
                    if ($detail['need_update']) {
                        LoaiPhong::where('id', $detail['id'])
                            ->update([
                                'so_luong_phong' => $detail['new_total'],
                                'so_luong_trong' => $detail['new_trong']
                            ]);
                        
                        $this->line(sprintf(
                            "  <fg=green>✓</> Updated: <fg=cyan>%-30s</> | Tổng: <fg=red>%2d</> → <fg=green>%2d</> | Trống: <fg=red>%2d</> → <fg=green>%2d</>",
                            substr($detail['ten_loai'], 0, 28),
                            $detail['old_total'],
                            $detail['new_total'],
                            $detail['old_trong'],
                            $detail['new_trong']
                        ));
                    }
                }
            });

            $this->newLine();
            $this->info("✅ Đã cập nhật thành công {$totalUpdated} loại phòng!");
        } else {
            $this->info('✅ Tất cả các giá trị đã chính xác! Không cần cập nhật.');
        }

        $this->newLine();
        $this->info('================================================================================');
        $this->info('                              HOÀN THÀNH');
        $this->info('================================================================================');

        return 0;
    }
}
