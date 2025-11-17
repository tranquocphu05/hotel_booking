<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\DatPhong;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate data from JSON to pivot tables
        $bookings = DB::table('dat_phong')->get();
        
        foreach ($bookings as $booking) {
            // Migrate room_types JSON to booking_room_types table
            if ($booking->room_types) {
                $roomTypes = json_decode($booking->room_types, true);
                if (is_array($roomTypes)) {
                    foreach ($roomTypes as $roomType) {
                        DB::table('booking_room_types')->insert([
                            'dat_phong_id' => $booking->id,
                            'loai_phong_id' => $roomType['loai_phong_id'],
                            'so_luong' => $roomType['so_luong'] ?? 1,
                            'gia_rieng' => $roomType['gia_rieng'] ?? 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
            
            // Migrate phong_ids JSON to booking_rooms table
            if ($booking->phong_ids) {
                $phongIds = json_decode($booking->phong_ids, true);
                if (is_array($phongIds)) {
                    foreach ($phongIds as $phongId) {
                        // Check if phong exists
                        $phongExists = DB::table('phong')->where('id', $phongId)->exists();
                        if ($phongExists) {
                            DB::table('booking_rooms')->insert([
                                'dat_phong_id' => $booking->id,
                                'phong_id' => $phongId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }
            
            // Fallback: If no room_types but has loai_phong_id (legacy single room booking)
            if (!$booking->room_types && $booking->loai_phong_id) {
                // Get price from loai_phong
                $loaiPhong = DB::table('loai_phong')->find($booking->loai_phong_id);
                if ($loaiPhong) {
                    DB::table('booking_room_types')->insert([
                        'dat_phong_id' => $booking->id,
                        'loai_phong_id' => $booking->loai_phong_id,
                        'so_luong' => $booking->so_luong_da_dat ?? 1,
                        'gia_rieng' => $booking->tong_tien ?? $loaiPhong->gia_co_ban,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            // Fallback: If no phong_ids but has phong_id (legacy single room)
            if (!$booking->phong_ids && $booking->phong_id) {
                $phongExists = DB::table('phong')->where('id', $booking->phong_id)->exists();
                if ($phongExists) {
                    DB::table('booking_rooms')->insert([
                        'dat_phong_id' => $booking->id,
                        'phong_id' => $booking->phong_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        
        echo "Migrated " . $bookings->count() . " bookings from JSON to pivot tables.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear pivot tables
        DB::table('booking_rooms')->truncate();
        DB::table('booking_room_types')->truncate();
        
        echo "Cleared pivot tables. JSON data still intact in dat_phong table.\n";
    }
};
