<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LoaiPhong;
use App\Models\Phong;
use App\Models\DatPhong;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FourTypesTwentyRoomsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Create or update 4 room types
            $types = [
                [
                    'ten_loai' => 'Phòng Standard',
                    'mo_ta' => 'Phòng tiêu chuẩn, tiện nghi cơ bản',
                    'gia_co_ban' => 400000,
                    'gia_khuyen_mai' => 350000,
                    'so_luong_phong' => 5,
                    'so_luong_trong' => 5,
                    'trang_thai' => 'hoat_dong',
                    'anh' => 'img/room/room-1.jpg',
                ],
                [
                    'ten_loai' => 'Phòng Deluxe',
                    'mo_ta' => 'Phòng Deluxe rộng rãi',
                    'gia_co_ban' => 700000,
                    'gia_khuyen_mai' => 650000,
                    'so_luong_phong' => 5,
                    'so_luong_trong' => 5,
                    'trang_thai' => 'hoat_dong',
                    'anh' => 'img/room/room-2.jpg',
                ],
                [
                    'ten_loai' => 'Phòng Suite',
                    'mo_ta' => 'Phòng Suite cao cấp',
                    'gia_co_ban' => 1200000,
                    'gia_khuyen_mai' => 1000000,
                    'so_luong_phong' => 5,
                    'so_luong_trong' => 5,
                    'trang_thai' => 'hoat_dong',
                    'anh' => 'img/room/room-3.jpg',
                ],
                [
                    'ten_loai' => 'Phòng Family',
                    'mo_ta' => 'Phòng cho gia đình',
                    'gia_co_ban' => 900000,
                    'gia_khuyen_mai' => 850000,
                    'so_luong_phong' => 5,
                    'so_luong_trong' => 5,
                    'trang_thai' => 'hoat_dong',
                    'anh' => 'img/room/room-4.jpg',
                ],
            ];

            $createdTypes = [];

            foreach ($types as $type) {
                $loai = LoaiPhong::updateOrCreate(
                    ['ten_loai' => $type['ten_loai']],
                    $type
                );
                $createdTypes[] = $loai;
            }

            // Remove any existing rooms that are not part of the newly created types
            Phong::whereNotIn('loai_phong_id', array_map(fn($t) => $t->id, $createdTypes))->delete();

            // Create 20 rooms across these 4 types (5 rooms each)
            $roomCount = 20;
            $index = 1;
            foreach ($createdTypes as $loai) {
                for ($i = 0; $i < 5; $i++) {
                    $soPhong = 100 * ($index) + ($i + 1); // e.g., 101, 102, ... 501
                    $soPhongStr = str_pad($soPhong, 3, '0', STR_PAD_LEFT);

                    Phong::updateOrCreate(
                        ['so_phong' => $soPhongStr],
                        [
                            'loai_phong_id' => $loai->id,
                            'so_phong' => $soPhongStr,
                            'ten_phong' => $loai->ten_loai . ' ' . $soPhongStr,
                            'tang' => $index,
                            'huong_cua_so' => 'thanh_pho',
                            'co_ban_cong' => in_array($loai->ten_loai, ['Phòng Deluxe', 'Phòng Suite']),
                            'co_view_dep' => false,
                            'gia_rieng' => null,
                            'gia_bo_sung' => 0,
                            'trang_thai' => 'trong',
                            'ghi_chu' => null,
                        ]
                    );
                }
                $index++;
            }

            // Ensure we have at least one customer user to assign bookings to
            $user = User::where('vai_tro', 'khach_hang')->first();
            if (!$user) {
                $user = User::create([
                    'username' => 'seed_customer',
                    'email' => 'seed_customer@example.com',
                    'password' => bcrypt('password'),
                    'ho_ten' => 'Khách Seed',
                    'sdt' => '0900000000',
                    'dia_chi' => 'Hà Nội',
                    'cccd' => '000000000',
                    'vai_tro' => 'khach_hang',
                    'trang_thai' => 'hoat_dong',
                ]);
            }

            // Create 20 bookings (DatPhong) linked to these rooms (one booking per room)
            $rooms = Phong::whereIn('loai_phong_id', array_map(fn($t) => $t->id, $createdTypes))->get();

            $count = 0;
            foreach ($rooms as $room) {
                if ($count >= 20) break;

                // Randomly choose confirmed or pending
                $status = $count % 2 === 0 ? 'da_xac_nhan' : 'cho_xac_nhan';

                $ngay_nhan = Carbon::today()->addDays(rand(1, 30));
                $ngay_tra = (clone $ngay_nhan)->addDays(rand(1, 5));
                $loaiPhong = $room->loaiPhong;
                $pricePerNight = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban;
                $nights = $ngay_nhan->diffInDays($ngay_tra);
                $totalPrice = $pricePerNight * max(1, $nights);

                $bookingPayload = [
                    'nguoi_dung_id' => $user->id,
                    'loai_phong_id' => $loaiPhong->id,
                    'so_luong_da_dat' => 1,
                    'ngay_dat' => Carbon::now()->subDays(rand(1, 10)),
                    'ngay_nhan' => $ngay_nhan,
                    'ngay_tra' => $ngay_tra,
                    'so_nguoi' => 2,
                    'trang_thai' => $status,
                    'tong_tien' => $totalPrice,
                    'username' => $user->ho_ten,
                    'email' => $user->email,
                    'sdt' => $user->sdt,
                    'cccd' => $user->cccd,
                ];

                // Use phong_id if table uses phong_id column only
                if (!\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'loai_phong_id') && \Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phong_id')) {
                    // Remove loai_phong_id payload and set phong_id instead
                    unset($bookingPayload['loai_phong_id']);
                    $bookingPayload['phong_id'] = $room->id;
                }

                $booking = DatPhong::create($bookingPayload);

                // If confirmed, assign the room and update status
                if ($status === 'da_xac_nhan') {
                    $booking->syncPhongs([$room->id]);
                    $room->update(['trang_thai' => 'dang_thue']);
                }

                $count++;
            }

            $this->command->info('✅ Seeded 4 room types, 20 rooms, and up to 20 bookings.');
        });
    }
}
