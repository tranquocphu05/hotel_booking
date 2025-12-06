<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LoaiPhong;
use App\Models\DatPhong;
use App\Models\NguoiDung;
use App\Models\Phong;
use Carbon\Carbon;

class TestBookingSystem extends Command
{
    protected $signature = 'test:booking';
    protected $description = 'Test hệ thống đặt phòng';

    public function handle()
    {
        $this->info('╔═══════════════════════════════════════════════════════════╗');
        $this->info('║       TEST HỆ THỐNG ĐẶT PHÒNG - HOTEL BOOKING            ║');
        $this->info('╚═══════════════════════════════════════════════════════════╝');
        $this->newLine();

        $errors = [];
        $warnings = [];
        $success = [];

        // TEST 1: Kiểm tra dữ liệu cơ bản
        $this->info('📌 TEST 1: KIỂM TRA DỮ LIỆU CƠ BẢN');
        $this->line(str_repeat('-', 60));

        $roomTypes = LoaiPhong::where('trang_thai', 'hoat_dong')->get();
        if ($roomTypes->isEmpty()) {
            $errors[] = "Không có loại phòng nào đang hoạt động";
            $this->error('❌ Không có loại phòng nào đang hoạt động');
        } else {
            $success[] = "Có {$roomTypes->count()} loại phòng hoạt động";
            $this->info("✅ Có {$roomTypes->count()} loại phòng hoạt động");
            foreach ($roomTypes as $rt) {
                $this->line("  - {$rt->ten_loai}: " . number_format($rt->gia_co_ban) . " VND/đêm");
            }
        }

        $rooms = Phong::where('trang_thai', 'trong')->get();
        if ($rooms->isEmpty()) {
            $warnings[] = "Không có phòng nào đang trống";
            $this->warn('⚠️  Không có phòng nào đang trống');
        } else {
            $success[] = "Có {$rooms->count()} phòng trống";
            $this->info("✅ Có {$rooms->count()} phòng trống");
        }

        $clients = NguoiDung::where('vai_tro', 'client')->where('trang_thai', 'hoat_dong')->count();
        $admins = NguoiDung::where('vai_tro', 'admin')->where('trang_thai', 'hoat_dong')->count();
        $success[] = "Có {$clients} client và {$admins} admin";
        $this->info("✅ Có {$clients} client và {$admins} admin");

        $this->newLine();

        // TEST 2: Kiểm tra Room Availability Logic
        $this->info('📌 TEST 2: KIỂM TRA LOGIC TÌM PHÒNG TRỐNG');
        $this->line(str_repeat('-', 60));

        $checkIn = Carbon::tomorrow();
        $checkOut = Carbon::tomorrow()->addDays(2);

        $this->line("Tìm phòng từ: {$checkIn->format('d/m/Y')} đến {$checkOut->format('d/m/Y')}");

        try {
            foreach ($roomTypes as $roomType) {
                $overlappingBookings = DatPhong::where('loai_phong_id', $roomType->id)
                    ->where('trang_thai', '!=', 'da_huy')
                    ->where(function($q) use ($checkIn, $checkOut) {
                        $q->whereBetween('ngay_nhan', [$checkIn, $checkOut])
                          ->orWhereBetween('ngay_tra', [$checkIn, $checkOut])
                          ->orWhere(function($q2) use ($checkIn, $checkOut) {
                              $q2->where('ngay_nhan', '<=', $checkIn)
                                 ->where('ngay_tra', '>=', $checkOut);
                          });
                    })
                    ->get();
                
                $bookedCount = $overlappingBookings->sum('so_phong');
                $totalRooms = Phong::where('loai_phong_id', $roomType->id)
                    ->where('trang_thai', '!=', 'bao_tri')
                    ->count();
                $available = max(0, $totalRooms - $bookedCount);
                
                $this->line("  - {$roomType->ten_loai}: {$available}/{$totalRooms} phòng available");
            }
            $success[] = "Logic tính phòng trống hoạt động";
            $this->info('✅ Logic tính phòng trống hoạt động');
        } catch (\Exception $e) {
            $errors[] = "Lỗi khi tính phòng trống: " . $e->getMessage();
            $this->error('❌ Lỗi khi tính phòng trống: ' . $e->getMessage());
        }

        $this->newLine();

        // TEST 3: Test tạo booking
        $this->info('📌 TEST 3: TEST TẠO BOOKING');
        $this->line(str_repeat('-', 60));

        try {
            $testUser = NguoiDung::where('email', 'talonin12@gmail.com')->first();
            
            if (!$testUser) {
                $errors[] = "Không tìm thấy test user";
                $this->error('❌ Không tìm thấy test user');
            } else {
                $availableRoomType = $roomTypes->first();
                
                if (!$availableRoomType) {
                    $errors[] = "Không có loại phòng nào để test";
                    $this->error('❌ Không có loại phòng nào để test');
                } else {
                    $booking = new DatPhong();
                    $booking->nguoi_dung_id = $testUser->id;
                    $booking->loai_phong_id = $availableRoomType->id;
                    $booking->ngay_nhan = $checkIn;
                    $booking->ngay_tra = $checkOut;
                    $booking->so_phong = 1;
                    $booking->trang_thai = 'cho_xac_nhan';
                    $booking->ten_khach_hang = $testUser->name;
                    $booking->email_khach_hang = $testUser->email;
                    $booking->sdt_khach_hang = $testUser->sdt ?? '0123456789';
                    $booking->cccd_khach_hang = $testUser->cccd ?? '000000000000';
                    
                    if ($booking->save()) {
                        $success[] = "Tạo booking test thành công (ID: {$booking->id})";
                        $this->info("✅ Tạo booking test thành công (ID: {$booking->id})");
                        $this->line("  Booking ID: {$booking->id}");
                        $this->line("  Loại phòng: {$availableRoomType->ten_loai}");
                        $this->line("  Check-in: {$checkIn->format('d/m/Y')}");
                        $this->line("  Check-out: {$checkOut->format('d/m/Y')}");
                        
                        // Cleanup
                        $booking->delete();
                        $success[] = "Đã xóa booking test";
                        $this->info('✅ Đã xóa booking test');
                    }
                }
            }
        } catch (\Exception $e) {
            $errors[] = "Lỗi khi test booking: " . $e->getMessage();
            $this->error('❌ Lỗi khi test booking: ' . $e->getMessage());
        }

        $this->newLine();

        // TỔNG KẾT
        $this->info('╔═══════════════════════════════════════════════════════════╗');
        $this->info('║                      TỔNG KẾT                            ║');
        $this->info('╚═══════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->info("✅ THÀNH CÔNG (" . count($success) . "):");
        foreach ($success as $msg) {
            $this->line("  ✅ $msg");
        }

        if (!empty($warnings)) {
            $this->newLine();
            $this->warn("⚠️  CẢNH BÁO (" . count($warnings) . "):");
            foreach ($warnings as $msg) {
                $this->line("  ⚠️  $msg");
            }
        }

        if (!empty($errors)) {
            $this->newLine();
            $this->error("❌ LỖI (" . count($errors) . "):");
            foreach ($errors as $msg) {
                $this->line("  ❌ $msg");
            }
        }

        $this->newLine();
        $this->line(str_repeat('═', 60));
        if (empty($errors)) {
            $this->info('🎉 HỆ THỐNG HOẠT ĐỘNG TỐT!');
        } else {
            $this->warn('⚠️  CÓ LỖI CẦN SỬA!');
        }
        $this->line(str_repeat('═', 60));

        return empty($errors) ? 0 : 1;
    }
}
