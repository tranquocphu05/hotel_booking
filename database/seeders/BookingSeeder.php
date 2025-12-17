<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DatPhong;
use App\Models\LoaiPhong;
use App\Models\Phong;
use App\Models\Voucher;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get test data
        $users = User::where('vai_tro', 'khach_hang')->get();
        $loaiPhongs = LoaiPhong::where('trang_thai', 'hoat_dong')->get();
        $voucher = Voucher::where('ma_voucher', 'WELCOME2024')->first();

        if ($users->isEmpty()) {
            $this->command->warn('⚠️  No customers found. Creating test customer...');
            $users = collect([
                User::create([
                    'username' => 'customer1',
                    'email' => 'customer1@example.com',
                    'password' => bcrypt('password'),
                    'ho_ten' => 'Nguyễn Văn A',
                    'sdt' => '0912345678',
                    'dia_chi' => 'Hà Nội',
                    'cccd' => '001234567890',
                    'vai_tro' => 'khach_hang',
                    'trang_thai' => 'hoat_dong',
                ])
            ]);
        }

        if ($loaiPhongs->isEmpty()) {
            $this->command->error('❌ No room types found. Please run LoaiPhongSeeder first.');
            return;
        }

        $bookingsData = [];

        // 1. Booking đã xác nhận (paid)
        $bookingsData[] = [
            'type' => 'confirmed',
            'user' => $users->random(),
            'loai_phong' => $loaiPhongs->random(),
            'ngay_nhan' => Carbon::now()->addDays(5),
            'ngay_tra' => Carbon::now()->addDays(8),
            'trang_thai' => 'da_xac_nhan',
            'invoice_status' => 'da_thanh_toan',
            'so_luong' => 2,
        ];

        // 2. Booking chờ xác nhận (pending payment)
        $bookingsData[] = [
            'type' => 'pending',
            'user' => $users->random(),
            'loai_phong' => $loaiPhongs->random(),
            'ngay_nhan' => Carbon::now()->addDays(10),
            'ngay_tra' => Carbon::now()->addDays(12),
            'trang_thai' => 'cho_xac_nhan',
            'invoice_status' => 'cho_thanh_toan',
            'so_luong' => 1,
        ];

        // 3. Booking đã hủy
        $bookingsData[] = [
            'type' => 'cancelled',
            'user' => $users->random(),
            'loai_phong' => $loaiPhongs->random(),
            'ngay_nhan' => Carbon::now()->addDays(15),
            'ngay_tra' => Carbon::now()->addDays(17),
            'trang_thai' => 'da_huy',
            'invoice_status' => 'cho_thanh_toan',
            'so_luong' => 1,
            'ly_do_huy' => 'Khách hàng thay đổi kế hoạch',
            'ngay_huy' => Carbon::now()->subDays(1),
        ];

        // 4. Booking đã hoàn thành (checked out)
        $bookingsData[] = [
            'type' => 'completed',
            'user' => $users->random(),
            'loai_phong' => $loaiPhongs->random(),
            'ngay_nhan' => Carbon::now()->subDays(5),
            'ngay_tra' => Carbon::now()->subDays(2),
            'trang_thai' => 'da_tra',
            'invoice_status' => 'da_thanh_toan',
            'so_luong' => 1,
            'thoi_gian_checkin' => Carbon::now()->subDays(5)->setTime(14, 0),
            'thoi_gian_checkout' => Carbon::now()->subDays(2)->setTime(12, 0),
        ];

        // 5. Multi-room booking (nhiều loại phòng)
        if ($loaiPhongs->count() >= 2) {
            $bookingsData[] = [
                'type' => 'multi_room',
                'user' => $users->random(),
                'loai_phong' => $loaiPhongs->first(),
                'ngay_nhan' => Carbon::now()->addDays(20),
                'ngay_tra' => Carbon::now()->addDays(25),
                'trang_thai' => 'da_xac_nhan',
                'invoice_status' => 'da_thanh_toan',
                'so_luong' => 3,
                'multi_room_types' => [
                    $loaiPhongs->first()->id => 2,
                    $loaiPhongs->skip(1)->first()->id => 1,
                ],
            ];
        }

        // 6. Booking với voucher
        if ($voucher) {
            $bookingsData[] = [
                'type' => 'with_voucher',
                'user' => $users->random(),
                'loai_phong' => $loaiPhongs->random(),
                'ngay_nhan' => Carbon::now()->addDays(30),
                'ngay_tra' => Carbon::now()->addDays(33),
                'trang_thai' => 'da_xac_nhan',
                'invoice_status' => 'da_thanh_toan',
                'so_luong' => 2,
                'voucher_id' => $voucher->id,
            ];
        }

        // Create bookings
        foreach ($bookingsData as $data) {
            DB::transaction(function () use ($data) {
                $user = $data['user'];
                $loaiPhong = $data['loai_phong'];
                $nights = Carbon::parse($data['ngay_nhan'])->diffInDays(Carbon::parse($data['ngay_tra']));
                $nights = max(1, $nights);

                // Calculate price
                $pricePerNight = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban;
                $totalPrice = $pricePerNight * $nights * $data['so_luong'];

                // Apply voucher discount
                if (isset($data['voucher_id'])) {
                    $voucher = Voucher::find($data['voucher_id']);
                    if ($voucher) {
                        $totalPrice = $totalPrice * (1 - $voucher->gia_tri / 100);
                    }
                }

                // Create booking
                $booking = DatPhong::create([
                    'nguoi_dung_id' => $user->id,
                    'loai_phong_id' => $loaiPhong->id,
                    'so_luong_da_dat' => $data['so_luong'],
                    'ngay_dat' => Carbon::now()->subHours(rand(1, 48)),
                    'ngay_nhan' => $data['ngay_nhan'],
                    'ngay_tra' => $data['ngay_tra'],
                    'so_nguoi' => $data['so_luong'] * 2,
                    'trang_thai' => $data['trang_thai'],
                    'tong_tien' => $totalPrice,
                    'voucher_id' => $data['voucher_id'] ?? null,
                    'username' => $user->ho_ten,
                    'email' => $user->email,
                    'sdt' => $user->sdt,
                    'cccd' => $user->cccd,
                    'ly_do_huy' => $data['ly_do_huy'] ?? null,
                    'ngay_huy' => $data['ngay_huy'] ?? null,
                    'thoi_gian_checkin' => $data['thoi_gian_checkin'] ?? null,
                    'thoi_gian_checkout' => $data['thoi_gian_checkout'] ?? null,
                ]);

                // Sync room types to pivot table
                if (isset($data['multi_room_types'])) {
                    // Multi-room booking
                    $roomTypesData = [];
                    foreach ($data['multi_room_types'] as $loaiPhongId => $soLuong) {
                        $lp = LoaiPhong::find($loaiPhongId);
                        $price = ($lp->gia_khuyen_mai ?? $lp->gia_co_ban) * $nights * $soLuong;
                        $roomTypesData[$loaiPhongId] = [
                            'so_luong' => $soLuong,
                            'gia_rieng' => $price,
                        ];
                    }
                    $booking->syncRoomTypes($roomTypesData);
                } else {
                    // Single room type
                    $booking->syncRoomTypes([
                        $loaiPhong->id => [
                            'so_luong' => $data['so_luong'],
                            'gia_rieng' => $totalPrice,
                        ],
                    ]);
                }

                // Assign rooms if confirmed or completed
                if (in_array($data['trang_thai'], ['da_xac_nhan', 'da_tra'])) {
                    $roomsToAssign = [];
                    
                    if (isset($data['multi_room_types'])) {
                        // Assign rooms for each room type
                        foreach ($data['multi_room_types'] as $loaiPhongId => $soLuong) {
                            $rooms = Phong::where('loai_phong_id', $loaiPhongId)
                                ->where('trang_thai', 'trong')
                                ->take($soLuong)
                                ->get();
                            
                            foreach ($rooms as $room) {
                                $roomsToAssign[] = $room->id;
                                // Update room status
                                if ($data['trang_thai'] === 'da_xac_nhan') {
                                    $room->update(['trang_thai' => 'dang_thue']);
                                } elseif ($data['trang_thai'] === 'da_tra') {
                                    $room->update(['trang_thai' => 'dang_don']);
                                }
                            }
                        }
                    } else {
                        // Single room type
                        $rooms = Phong::where('loai_phong_id', $loaiPhong->id)
                            ->where('trang_thai', 'trong')
                            ->take($data['so_luong'])
                            ->get();
                        
                        foreach ($rooms as $room) {
                            $roomsToAssign[] = $room->id;
                            // Update room status
                            if ($data['trang_thai'] === 'da_xac_nhan') {
                                $room->update(['trang_thai' => 'dang_thue']);
                            } elseif ($data['trang_thai'] === 'da_tra') {
                                $room->update(['trang_thai' => 'dang_don']);
                            }
                        }
                    }

                    // Sync rooms to pivot table
                    if (!empty($roomsToAssign)) {
                        $booking->syncPhongs($roomsToAssign);
                        
                        // Update legacy phong_id if only one room
                        if (count($roomsToAssign) === 1) {
                            $booking->update(['phong_id' => $roomsToAssign[0]]);
                        }
                    }
                }

                // Create invoice
                Invoice::create([
                    'dat_phong_id' => $booking->id,
                    'tien_phong' => $totalPrice,
                    'tien_dich_vu' => 0,
                    'giam_gia' => isset($data['voucher_id']) ? ($pricePerNight * $nights * $data['so_luong'] - $totalPrice) : 0,
                    'tong_tien' => $totalPrice,
                    'trang_thai' => $data['invoice_status'],
                    'phuong_thuc' => $data['invoice_status'] === 'da_thanh_toan' ? 'vnpay' : null,
                ]);

                $this->command->info("✅ Created {$data['type']} booking #{$booking->id}");
            });
        }

        $this->command->info('✅ Created ' . count($bookingsData) . ' bookings with pivot table data');
    }
}
