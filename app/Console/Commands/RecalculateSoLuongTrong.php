<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LoaiPhong;
use App\Models\Phong;
use Illuminate\Support\Facades\DB;

class RecalculateSoLuongTrong extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rooms:recalculate-available 
                            {--force : Force update without confirmation}
                            {--loai-phong-id= : Recalculate for specific room type only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate so_luong_trong for all room types based on actual available rooms';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('================================================================================');
        $this->info('           RECALCULATE SO_LUONG_TRONG CHO LOẠI PHÒNG');
        $this->info('================================================================================');
        $this->newLine();

        // Get room types to process
        $loaiPhongs = $this->option('loai-phong-id') 
            ? LoaiPhong::where('id', $this->option('loai-phong-id'))->get()
            : LoaiPhong::all();

        if ($loaiPhongs->isEmpty()) {
            $this->error('Không tìm thấy loại phòng nào!');
            return 1;
        }

        $this->info('Đang kiểm tra ' . $loaiPhongs->count() . ' loại phòng...');
        $this->newLine();

        $details = [];
        $totalUpdated = 0;
        $totalCorrect = 0;

        // Check each room type
        foreach ($loaiPhongs as $loaiPhong) {
            $trongCountActual = Phong::where('loai_phong_id', $loaiPhong->id)
                ->whereIn('trang_thai', ['trong', 'dang_don'])
                ->count();
            
            $trongCountDB = $loaiPhong->so_luong_trong;
            
            $needUpdate = ($trongCountActual != $trongCountDB);
            
            if ($needUpdate) {
                $totalUpdated++;
                $this->line(sprintf(
                    "<fg=yellow>%-30s</> | DB: <fg=red>%2d</> | Actual: <fg=green>%2d</> | <fg=yellow>✗ Cần cập nhật</>",
                    substr($loaiPhong->ten_loai, 0, 28),
                    $trongCountDB,
                    $trongCountActual
                ));
            } else {
                $totalCorrect++;
                $this->line(sprintf(
                    "<fg=cyan>%-30s</> | DB: %2d | Actual: %2d | <fg=green>✓ OK</>",
                    substr($loaiPhong->ten_loai, 0, 28),
                    $trongCountDB,
                    $trongCountActual
                ));
            }
            
            $details[] = [
                'id' => $loaiPhong->id,
                'ten_loai' => $loaiPhong->ten_loai,
                'old_value' => $trongCountDB,
                'new_value' => $trongCountActual,
                'need_update' => $needUpdate,
            ];
        }

        $this->newLine();
        $this->info('Tổng quan:');
        $this->line("  • Tổng số loại phòng: <fg=cyan>" . count($loaiPhongs) . "</>");
        $this->line("  • Đã chính xác: <fg=green>{$totalCorrect}</>");
        $this->line("  • Cần cập nhật: <fg=yellow>{$totalUpdated}</>");
        $this->newLine();

        if ($totalUpdated > 0) {
            // Ask for confirmation unless --force is used
            if (!$this->option('force')) {
                if (!$this->confirm('Bạn có muốn cập nhật các giá trị không chính xác?', true)) {
                    $this->warn('Hủy cập nhật. Không có thay đổi nào được thực hiện.');
                    return 0;
                }
            }

            $this->info('Đang cập nhật...');
            $this->newLine();

            $progressBar = $this->output->createProgressBar($totalUpdated);
            $progressBar->start();

            DB::transaction(function () use ($details, $progressBar) {
                foreach ($details as $detail) {
                    if ($detail['need_update']) {
                        LoaiPhong::where('id', $detail['id'])
                            ->update(['so_luong_trong' => $detail['new_value']]);
                        
                        $progressBar->advance();
                    }
                }
            });

            $progressBar->finish();
            $this->newLine(2);

            // Display updated records
            foreach ($details as $detail) {
                if ($detail['need_update']) {
                    $this->line(sprintf(
                        "  <fg=green>✓</> Updated: <fg=cyan>%-30s</> | <fg=red>%2d</> → <fg=green>%2d</>",
                        substr($detail['ten_loai'], 0, 28),
                        $detail['old_value'],
                        $detail['new_value']
                    ));
                }
            }

            $this->newLine();
            $this->info("✅ Đã cập nhật thành công {$totalUpdated} loại phòng!");

            // Verify
            $this->info('Verifying...');
            $verified = 0;
            $failed = 0;

            foreach ($details as $detail) {
                if ($detail['need_update']) {
                    $loaiPhong = LoaiPhong::find($detail['id']);
                    $trongCountActual = Phong::where('loai_phong_id', $loaiPhong->id)
                        ->whereIn('trang_thai', ['trong', 'dang_don'])
                        ->count();
                    
                    if ($loaiPhong->so_luong_trong == $trongCountActual) {
                        $verified++;
                    } else {
                        $failed++;
                        $this->error("  ✗ Failed: {$detail['ten_loai']}");
                    }
                }
            }

            $this->newLine();
            $this->info('Verification Results:');
            $this->line("  <fg=green>✓</> Verified: <fg=green>{$verified}/{$totalUpdated}</>");
            
            if ($failed > 0) {
                $this->line("  <fg=red>✗</> Failed: <fg=red>{$failed}/{$totalUpdated}</>");
                return 1;
            }

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

