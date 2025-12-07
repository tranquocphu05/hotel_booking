<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DatPhong;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class NormalizeBookingRoomTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'normalize:room-types {--id= : Optional booking ID to normalize only that booking} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normalize room_types JSON for bookings by merging duplicate loai_phong_id entries.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $bookingId = $this->option('id');
        
        $this->info('Starting normalization' . ($dry ? ' (dry-run)' : '') . ($bookingId ? " for booking #$bookingId" : ''));

        $query = $bookingId ? DatPhong::where('id', $bookingId) : DatPhong::query();
        $updated = 0;

        foreach ($query->cursor() as $booking) {
            $roomTypes = $booking->room_types ?? $booking->getRoomTypes();
            if (!is_array($roomTypes) || empty($roomTypes)) {
                continue;
            }

            $normalized = [];
            foreach ($roomTypes as $entry) {
                if (empty($entry['loai_phong_id'])) {
                    continue;
                }
                $id = (int) $entry['loai_phong_id'];
                if (!isset($normalized[$id])) {
                    $normalized[$id] = [
                        'loai_phong_id' => $id,
                        'so_luong' => 0,
                    ];
                }

                $normalized[$id]['so_luong'] += isset($entry['so_luong']) ? (int) $entry['so_luong'] : 0;

                if (!empty($entry['phong_ids']) && is_array($entry['phong_ids'])) {
                    $existing = $normalized[$id]['phong_ids'] ?? [];
                    $normalized[$id]['phong_ids'] = array_values(array_unique(array_merge($existing, $entry['phong_ids'])));
                } elseif (!empty($entry['phong_id'])) {
                    $existing = $normalized[$id]['phong_ids'] ?? [];
                    $existing[] = $entry['phong_id'];
                    $normalized[$id]['phong_ids'] = array_values(array_unique($existing));
                }

                if (isset($entry['gia_rieng'])) {
                    $normalized[$id]['gia_rieng'] = $entry['gia_rieng'];
                }
            }

            $mergedList = array_values($normalized);

            if (json_encode($mergedList, JSON_UNESCAPED_UNICODE) !== json_encode($roomTypes, JSON_UNESCAPED_UNICODE)) {
                $this->line("Booking #{$booking->id} needs update");

                if (!$dry) {
                    $backupDir = storage_path('app/room_types_backups');
                    if (!is_dir($backupDir)) {
                        mkdir($backupDir, 0755, true);
                    }

                    $backupPath = $backupDir . DIRECTORY_SEPARATOR . 'booking_' . $booking->id . '_' . time() . '.json';
                    file_put_contents($backupPath, json_encode($roomTypes, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

                    DB::transaction(function () use ($booking, $mergedList) {
                        $booking->room_types = $mergedList;
                        $booking->save();
                    });
                }

                $updated++;
            }
        }

        $this->info("Done. Updated bookings: $updated");
        return 0;
    }
}
