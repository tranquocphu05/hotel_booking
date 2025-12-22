<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DatPhong;
use App\Models\LoaiPhong;
use App\Models\Phong;
use App\Models\Voucher;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\StayGuest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Truncate tables to ensure fresh start
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('stay_guests')->truncate();
        DB::table('invoice_items')->truncate();
        DB::table('thanh_toan')->truncate();
        DB::table('hoa_don')->truncate();
        DB::table('booking_room_types')->truncate();
        DB::table('booking_rooms')->truncate();
        DB::table('dat_phong')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Get dependencies
        $customers = User::where('vai_tro', 'khach_hang')->get();
        
        // Fallback: Create customers if they don't exist
        if ($customers->isEmpty()) {
            $this->command->info('⚠️ No customers found, creating test customers...');
            for ($i = 1; $i <= 5; $i++) {
                User::create([
                    'ho_ten' => "Khách hàng $i",
                    'email' => "khachhang$i@example.com",
                    'username' => "khachhang$i",
                    'password' => \Illuminate\Support\Facades\Hash::make('123456'),
                    'sdt' => '098765432' . $i,
                    'cccd' => '12345678' . $i,
                    'vai_tro' => 'khach_hang',
                    'trang_thai' => 'hoat_dong',
                ]);
            }
            $customers = User::where('vai_tro', 'khach_hang')->get();
        }

        $admin = User::where('vai_tro', 'admin')->first();
        $loaiPhongs = LoaiPhong::all();
        $vouchers = Voucher::where('trang_thai', 'con_han')->get();

        if ($customers->isEmpty() || $loaiPhongs->isEmpty()) {
            $this->command->error('❌ Need room types to seed bookings.');
            return;
        }


        $scenarios = [
            // Quá khứ: Đã trả phòng
            [
                'label' => 'Quá khứ: Đã trả phòng',
                'trang_thai' => 'da_tra',
                'inv_status' => 'da_thanh_toan',
                'days_ago' => 10,
                'duration' => 3,
                'count' => 3,
            ],
            // Hiện tại: Đang ở (da_nhan_phong / da_xac_nhan + rooms assigned)
            [
                'label' => 'Hiện tại: Đang ở',
                'trang_thai' => 'da_nhan_phong',
                'inv_status' => 'da_thanh_toan',
                'days_ago' => 1,
                'duration' => 2,
                'count' => 5,
            ],
            // Tương lai: Đã xác nhận
            [
                'label' => 'Tương lai: Đã xác nhận',
                'trang_thai' => 'da_xac_nhan',
                'inv_status' => 'da_thanh_toan',
                'days_ago' => -5,
                'duration' => 2,
                'count' => 4,
            ],
            // Tương lai: Chờ xác nhận
            [
                'label' => 'Tương lai: Chờ xác nhận',
                'trang_thai' => 'cho_xac_nhan',
                'inv_status' => 'cho_thanh_toan',
                'days_ago' => -10,
                'duration' => 3,
                'count' => 3,
            ],
            // Đã hủy
            [
                'label' => 'Đã hủy',
                'trang_thai' => 'da_huy',
                'inv_status' => 'cho_thanh_toan',
                'days_ago' => 2,
                'duration' => 2,
                'count' => 2,
                'ly_do_huy' => 'Khách thay đổi kế hoạch du lịch',
            ],
        ];

        foreach ($scenarios as $scen) {
            for ($i = 0; $i < $scen['count']; $i++) {
                DB::transaction(function () use ($scen, $customers, $loaiPhongs, $vouchers, $admin) {
                    $user = $customers->random();
                    $loaiPhong = $loaiPhongs->random();
                    $ngayNhan = Carbon::today()->subDays($scen['days_ago']);
                    $ngayTra = (clone $ngayNhan)->addDays($scen['duration']);
                    $soLuong = rand(1, 2);
                    
                    // Check logic for nights
                    $nights = max(1, $ngayNhan->diffInDays($ngayTra));
                    $pricePerNight = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban;
                    $subTotal = $pricePerNight * $nights * $soLuong;
                    
                    $voucher = rand(0, 1) ? $vouchers->random() : null;
                    $discount = $voucher ? ($subTotal * $voucher->gia_tri / 100) : 0;
                    $totalPrice = $subTotal - $discount;

                    // 1. Create DatPhong
                    $booking = DatPhong::create([
                        'nguoi_dung_id' => $user->id,
                        'loai_phong_id' => $loaiPhong->id, // legacy
                        'so_luong_da_dat' => $soLuong,
                        'ngay_dat' => (clone $ngayNhan)->subDays(rand(5, 15)),
                        'ngay_nhan' => $ngayNhan,
                        'ngay_tra' => $ngayTra,
                        'so_nguoi' => $soLuong * 2,
                        'trang_thai' => $scen['trang_thai'],
                        'tong_tien' => $totalPrice,
                        'voucher_id' => $voucher ? $voucher->id : null,
                        'username' => $user->ho_ten,
                        'email' => $user->email,
                        'sdt' => $user->sdt,
                        'cccd' => $user->cccd,
                        'ly_do_huy' => $scen['ly_do_huy'] ?? null,
                        'ngay_huy' => isset($scen['ly_do_huy']) ? Carbon::now() : null,
                        'thoi_gian_checkin' => ($scen['trang_thai'] === 'da_tra' || $scen['trang_thai'] === 'da_nhan_phong') ? (clone $ngayNhan)->setTime(14, 0) : null,
                        'thoi_gian_checkout' => ($scen['trang_thai'] === 'da_tra') ? (clone $ngayTra)->setTime(11, 0) : null,
                    ]);

                    // 2. Sync Pivot Table Room Types
                    $booking->syncRoomTypes([
                        $loaiPhong->id => [
                            'so_luong' => $soLuong,
                            'gia_rieng' => $subTotal,
                        ]
                    ]);

                    // 3. Create Invoice
                    $invoice = Invoice::create([
                        'dat_phong_id' => $booking->id,
                        'tien_phong' => $subTotal,
                        'tien_dich_vu' => 0,
                        'giam_gia' => $discount,
                        'tong_tien' => $totalPrice,
                        'trang_thai' => $scen['inv_status'],
                        'phuong_thuc' => $scen['inv_status'] === 'da_thanh_toan' ? 'vnpay' : null,
                    ]);

                    // 4. Create Payment Record if paid
                    if ($scen['inv_status'] === 'da_thanh_toan') {
                        \App\Models\ThanhToan::create([
                            'hoa_don_id' => $invoice->id,
                            'loai' => 'thanh_toan',
                            'so_tien' => $totalPrice,
                            'ngay_thanh_toan' => (clone $ngayNhan)->subHours(rand(1, 12)),
                            'trang_thai' => 'thanh_cong',
                        ]);
                    }

                    // 5. Create Invoice Item (Room Charge)

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'type' => 'room',
                        'description' => "Cước phòng " . $loaiPhong->ten_loai . " ($soLuong phòng x $nights đêm)",
                        'quantity' => $soLuong,
                        'unit_price' => $pricePerNight,
                        'days' => $nights,
                        'amount' => $subTotal,
                        'start_date' => $ngayNhan,
                        'end_date' => $ngayTra,
                    ]);

                    // 5. Assign Rooms & Gán khách
                    if (in_array($scen['trang_thai'], ['da_nhan_phong', 'da_tra', 'da_xac_nhan'])) {
                        $rooms = Phong::where('loai_phong_id', $loaiPhong->id)
                            ->where('trang_thai', 'trong')
                            ->take($soLuong)
                            ->get();

                        if ($rooms->count() >= $soLuong) {
                            $roomIds = $rooms->pluck('id')->toArray();
                            $booking->syncPhongs($roomIds);

                            foreach ($rooms as $room) {
                                // Update room status
                                if ($scen['trang_thai'] === 'da_nhan_phong') {
                                    $room->update(['trang_thai' => 'dang_thue']);
                                } elseif ($scen['trang_thai'] === 'da_tra') {
                                    $room->update(['trang_thai' => 'dang_don']);
                                } elseif ($scen['trang_thai'] === 'da_xac_nhan') {
                                    // Normally not occupied yet, but for seeding we might want to show assigned
                                    $room->update(['trang_thai' => 'da_dat']);
                                }

                                // 6. Create StayGuest
                                StayGuest::create([
                                    'dat_phong_id' => $booking->id,
                                    'phong_id' => $room->id,
                                    'full_name' => "Khách đi kèm của " . $user->ho_ten,
                                    'ten_khach' => "Khách đi kèm",
                                    'dob' => Carbon::today()->subYears(rand(20, 40)),
                                    'created_by' => $admin ? $admin->id : null,
                                    'created_at' => Carbon::now(),
                                    'start_date' => $ngayNhan,
                                    'end_date' => $ngayTra,
                                ]);
                            }
                        }
                    }
                });
            }
        }

        $this->command->info('✅ Seeded detailed bookings with invoices, items, and stay guests.');
    }
}
