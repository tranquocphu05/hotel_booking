<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DatPhong;
use App\Models\Invoice;
use App\Models\LoaiPhong;
use App\Models\Phong;
use App\Models\ThanhToan;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Carbon\Carbon;

class BookingCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected LoaiPhong $loaiPhong;
    protected Phong $phong;

    protected function setUp(): void
    {
        parent::setUp();

        // Tạo admin user
        $this->admin = User::create([
            'ho_ten' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'vai_tro' => 'admin',
            'trang_thai' => 'hoat_dong',
        ]);

        // Tạo loại phòng
        $this->loaiPhong = LoaiPhong::create([
            'ten_loai' => 'Phòng Deluxe',
            'trang_thai' => 'hoat_dong',
            'gia_co_ban' => 1000000,
            'so_luong_phong' => 10,
            'so_luong_trong' => 10,
        ]);

        // Tạo phòng
        $this->phong = Phong::create([
            'loai_phong_id' => $this->loaiPhong->id,
            'so_phong' => '101',
            'trang_thai' => 'trong',
        ]);
    }

    /**
     * Test Case 1: Hủy booking chưa thanh toán (không có hoàn tiền)
     */
    public function test_cancel_unpaid_booking_no_refund(): void
    {
        Mail::fake();

        // Tạo booking chưa thanh toán
        $booking = DatPhong::create([
            'loai_phong_id' => $this->loaiPhong->id,
            'so_luong_da_dat' => 1,
            'trang_thai' => 'cho_xac_nhan',
            'ngay_dat' => now(),
            'ngay_nhan' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'ngay_tra' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'tong_tien' => 2000000,
            'username' => 'Test User',
            'email' => 'test@example.com',
            'sdt' => '0123456789',
            'cccd' => '123456789012',
        ]);

        // Gán phòng
        $booking->phongs()->attach($this->phong->id);

        // Tạo invoice chưa thanh toán
        $invoice = Invoice::create([
            'dat_phong_id' => $booking->id,
            'tong_tien' => 2000000,
            'tien_phong' => 2000000,
            'tien_dich_vu' => 0,
            'giam_gia' => 0,
            'trang_thai' => 'cho_thanh_toan',
        ]);

        // Admin hủy booking
        $response = $this->actingAs($this->admin)
            ->post(route('admin.dat_phong.cancel.submit', $booking->id), [
                'ly_do' => 'thay_doi_lich_trinh',
            ]);

        // Assertions
        $response->assertRedirect(route('admin.dat_phong.index'));
        $response->assertSessionHas('success');

        // Kiểm tra booking đã được hủy
        $booking->refresh();
        $this->assertEquals('da_huy', $booking->trang_thai);
        $this->assertNotNull($booking->ngay_huy);

        // Kiểm tra invoice không thay đổi (vẫn chưa thanh toán)
        $invoice->refresh();
        $this->assertEquals('cho_thanh_toan', $invoice->trang_thai);

        // Kiểm tra không có bản ghi refund
        $refundCount = ThanhToan::where('hoa_don_id', $invoice->id)
            ->where('trang_thai', 'refunded')
            ->count();
        $this->assertEquals(0, $refundCount);

        // Kiểm tra phòng đã được giải phóng
        $this->phong->refresh();
        $this->assertEquals('trong', $this->phong->trang_thai);

        // Kiểm tra email không được gửi (vì không có hoàn tiền)
        Mail::assertNothingSent();
    }

    /**
     * Test Case 2: Hủy booking đã thanh toán trước 7 ngày (hoàn 100%)
     */
    public function test_cancel_paid_booking_7_days_before_full_refund(): void
    {
        Mail::fake();

        // Tạo booking đã thanh toán, check-in sau 10 ngày
        $checkinDate = Carbon::now()->addDays(10);
        $booking = DatPhong::create([
            'loai_phong_id' => $this->loaiPhong->id,
            'so_luong_da_dat' => 1,
            'trang_thai' => 'da_xac_nhan',
            'ngay_dat' => now(),
            'ngay_nhan' => $checkinDate->format('Y-m-d'),
            'ngay_tra' => $checkinDate->copy()->addDays(2)->format('Y-m-d'),
            'tong_tien' => 2000000,
            'username' => 'Test User',
            'email' => 'test@example.com',
            'sdt' => '0123456789',
            'cccd' => '123456789012',
        ]);

        // Gán phòng
        $booking->phongs()->attach($this->phong->id);
        $this->phong->update(['trang_thai' => 'dang_thue']);

        // Tạo invoice đã thanh toán
        $invoice = Invoice::create([
            'dat_phong_id' => $booking->id,
            'tong_tien' => 2000000,
            'tien_phong' => 2000000,
            'tien_dich_vu' => 0,
            'giam_gia' => 0,
            'trang_thai' => 'da_thanh_toan',
            'da_thanh_toan' => 2000000,
            'con_lai' => 0,
        ]);

        // Tạo bản ghi thanh toán
        ThanhToan::create([
            'hoa_don_id' => $invoice->id,
            'so_tien' => 2000000,
            'ngay_thanh_toan' => now(),
            'trang_thai' => 'success',
        ]);

        // Admin hủy booking
        $response = $this->actingAs($this->admin)
            ->post(route('admin.dat_phong.cancel.submit', $booking->id), [
                'ly_do' => 'thay_doi_lich_trinh',
            ]);

        // Assertions
        $response->assertRedirect(route('admin.dat_phong.index'));
        $response->assertSessionHas('success');

        // Kiểm tra booking đã được hủy
        $booking->refresh();
        $this->assertEquals('da_huy', $booking->trang_thai);
        $this->assertNotNull($booking->ngay_huy);
        $this->assertStringContainsString('100%', $booking->ghi_chu_hoan_tien ?? '');

        // Kiểm tra invoice đã chuyển sang hoàn tiền
        $invoice->refresh();
        $this->assertEquals('hoan_tien', $invoice->trang_thai);
        $this->assertEquals(0, $invoice->con_lai);

        // Kiểm tra có bản ghi refund
        $refund = ThanhToan::where('hoa_don_id', $invoice->id)
            ->where('trang_thai', 'refunded')
            ->first();
        $this->assertNotNull($refund);
        $this->assertEquals(-2000000, $refund->so_tien); // Số âm để thể hiện hoàn tiền
        $this->assertStringContainsString('100%', $refund->ghi_chu ?? '');

        // Kiểm tra phòng đã được giải phóng
        $this->phong->refresh();
        $this->assertEquals('trong', $this->phong->trang_thai);

        // Kiểm tra email đã được gửi với thông tin hoàn tiền
        Mail::assertSent(\App\Mail\BookingCancelled::class, function ($mail) use ($booking) {
            return $mail->booking->id === $booking->id
                && $mail->refundInfo !== null
                && $mail->refundInfo['refund_percentage'] === 100
                && $mail->refundInfo['refund_amount'] === 2000000;
        });
    }

    /**
     * Test Case 3: Hủy booking đã thanh toán 3-6 ngày (hoàn 50%)
     */
    public function test_cancel_paid_booking_3_to_6_days_before_50_percent_refund(): void
    {
        Mail::fake();

        // Tạo booking đã thanh toán, check-in sau 5 ngày
        $checkinDate = Carbon::now()->addDays(5);
        $booking = DatPhong::create([
            'loai_phong_id' => $this->loaiPhong->id,
            'so_luong_da_dat' => 1,
            'trang_thai' => 'da_xac_nhan',
            'ngay_dat' => now(),
            'ngay_nhan' => $checkinDate->format('Y-m-d'),
            'ngay_tra' => $checkinDate->copy()->addDays(2)->format('Y-m-d'),
            'tong_tien' => 2000000,
            'username' => 'Test User',
            'email' => 'test@example.com',
            'sdt' => '0123456789',
            'cccd' => '123456789012',
        ]);

        $booking->phongs()->attach($this->phong->id);
        $this->phong->update(['trang_thai' => 'dang_thue']);

        $invoice = Invoice::create([
            'dat_phong_id' => $booking->id,
            'tong_tien' => 2000000,
            'tien_phong' => 2000000,
            'tien_dich_vu' => 0,
            'giam_gia' => 0,
            'trang_thai' => 'da_thanh_toan',
            'da_thanh_toan' => 2000000,
            'con_lai' => 0,
        ]);

        ThanhToan::create([
            'hoa_don_id' => $invoice->id,
            'so_tien' => 2000000,
            'ngay_thanh_toan' => now(),
            'trang_thai' => 'success',
        ]);

        // Admin hủy booking
        $response = $this->actingAs($this->admin)
            ->post(route('admin.dat_phong.cancel.submit', $booking->id), [
                'ly_do' => 'thay_doi_ke_hoach',
            ]);

        // Assertions
        $response->assertRedirect(route('admin.dat_phong.index'));
        $response->assertSessionHas('success');

        $booking->refresh();
        $this->assertEquals('da_huy', $booking->trang_thai);
        $this->assertStringContainsString('50%', $booking->ghi_chu_hoan_tien ?? '');

        $invoice->refresh();
        $this->assertEquals('hoan_tien', $invoice->trang_thai);

        // Kiểm tra refund amount = 50% = 1,000,000
        $refund = ThanhToan::where('hoa_don_id', $invoice->id)
            ->where('trang_thai', 'refunded')
            ->first();
        $this->assertNotNull($refund);
        $this->assertEquals(-1000000, $refund->so_tien); // 50% của 2,000,000

        // Kiểm tra email với thông tin hoàn tiền 50%
        Mail::assertSent(\App\Mail\BookingCancelled::class, function ($mail) use ($booking) {
            return $mail->booking->id === $booking->id
                && $mail->refundInfo !== null
                && $mail->refundInfo['refund_percentage'] === 50
                && $mail->refundInfo['refund_amount'] === 1000000;
        });
    }

    /**
     * Test Case 4: Hủy booking đã thanh toán 1-2 ngày (hoàn 25%)
     */
    public function test_cancel_paid_booking_1_to_2_days_before_25_percent_refund(): void
    {
        Mail::fake();

        // Tạo booking đã thanh toán, check-in sau 2 ngày
        $checkinDate = Carbon::now()->addDays(2);
        $booking = DatPhong::create([
            'loai_phong_id' => $this->loaiPhong->id,
            'so_luong_da_dat' => 1,
            'trang_thai' => 'da_xac_nhan',
            'ngay_dat' => now(),
            'ngay_nhan' => $checkinDate->format('Y-m-d'),
            'ngay_tra' => $checkinDate->copy()->addDays(2)->format('Y-m-d'),
            'tong_tien' => 2000000,
            'username' => 'Test User',
            'email' => 'test@example.com',
            'sdt' => '0123456789',
            'cccd' => '123456789012',
        ]);

        $booking->phongs()->attach($this->phong->id);
        $this->phong->update(['trang_thai' => 'dang_thue']);

        $invoice = Invoice::create([
            'dat_phong_id' => $booking->id,
            'tong_tien' => 2000000,
            'tien_phong' => 2000000,
            'tien_dich_vu' => 0,
            'giam_gia' => 0,
            'trang_thai' => 'da_thanh_toan',
            'da_thanh_toan' => 2000000,
            'con_lai' => 0,
        ]);

        ThanhToan::create([
            'hoa_don_id' => $invoice->id,
            'so_tien' => 2000000,
            'ngay_thanh_toan' => now(),
            'trang_thai' => 'success',
        ]);

        // Admin hủy booking
        $response = $this->actingAs($this->admin)
            ->post(route('admin.dat_phong.cancel.submit', $booking->id), [
                'ly_do' => 'khong_phu_hop',
            ]);

        // Assertions
        $response->assertRedirect(route('admin.dat_phong.index'));
        $response->assertSessionHas('success');

        $booking->refresh();
        $this->assertEquals('da_huy', $booking->trang_thai);
        $this->assertStringContainsString('25%', $booking->ghi_chu_hoan_tien ?? '');

        // Kiểm tra refund amount = 25% = 500,000
        $refund = ThanhToan::where('hoa_don_id', $invoice->id)
            ->where('trang_thai', 'refunded')
            ->first();
        $this->assertNotNull($refund);
        $this->assertEquals(-500000, $refund->so_tien); // 25% của 2,000,000

        // Kiểm tra email với thông tin hoàn tiền 25%
        Mail::assertSent(\App\Mail\BookingCancelled::class, function ($mail) use ($booking) {
            return $mail->booking->id === $booking->id
                && $mail->refundInfo !== null
                && $mail->refundInfo['refund_percentage'] === 25
                && $mail->refundInfo['refund_amount'] === 500000;
        });
    }

    /**
     * Test Case 5: Hủy booking đã thanh toán trong ngày (không hoàn)
     */
    public function test_cancel_paid_booking_same_day_no_refund(): void
    {
        Mail::fake();

        // Tạo booking đã thanh toán, check-in hôm nay
        $checkinDate = Carbon::today();
        $booking = DatPhong::create([
            'loai_phong_id' => $this->loaiPhong->id,
            'so_luong_da_dat' => 1,
            'trang_thai' => 'da_xac_nhan',
            'ngay_dat' => now(),
            'ngay_nhan' => $checkinDate->format('Y-m-d'),
            'ngay_tra' => $checkinDate->copy()->addDays(2)->format('Y-m-d'),
            'tong_tien' => 2000000,
            'username' => 'Test User',
            'email' => 'test@example.com',
            'sdt' => '0123456789',
            'cccd' => '123456789012',
        ]);

        $booking->phongs()->attach($this->phong->id);
        $this->phong->update(['trang_thai' => 'dang_thue']);

        $invoice = Invoice::create([
            'dat_phong_id' => $booking->id,
            'tong_tien' => 2000000,
            'tien_phong' => 2000000,
            'tien_dich_vu' => 0,
            'giam_gia' => 0,
            'trang_thai' => 'da_thanh_toan',
            'da_thanh_toan' => 2000000,
            'con_lai' => 0,
        ]);

        ThanhToan::create([
            'hoa_don_id' => $invoice->id,
            'so_tien' => 2000000,
            'ngay_thanh_toan' => now(),
            'trang_thai' => 'success',
        ]);

        // Admin hủy booking
        $response = $this->actingAs($this->admin)
            ->post(route('admin.dat_phong.cancel.submit', $booking->id), [
                'ly_do' => 'ly_do_khac',
            ]);

        // Assertions
        $response->assertRedirect(route('admin.dat_phong.index'));
        $response->assertSessionHas('success');

        $booking->refresh();
        $this->assertEquals('da_huy', $booking->trang_thai);
        $this->assertStringContainsString('0%', $booking->ghi_chu_hoan_tien ?? '');

        $invoice->refresh();
        $this->assertEquals('hoan_tien', $invoice->trang_thai);

        // Kiểm tra refund amount = 0
        $refund = ThanhToan::where('hoa_don_id', $invoice->id)
            ->where('trang_thai', 'refunded')
            ->first();
        $this->assertNotNull($refund);
        $this->assertEquals(0, $refund->so_tien);

        // Kiểm tra email với thông tin không hoàn tiền
        Mail::assertSent(\App\Mail\BookingCancelled::class, function ($mail) use ($booking) {
            return $mail->booking->id === $booking->id
                && $mail->refundInfo !== null
                && $mail->refundInfo['refund_percentage'] === 0
                && $mail->refundInfo['refund_amount'] === 0;
        });
    }

    /**
     * Test Case 6: Không thể hủy booking đã check-in
     */
    public function test_cannot_cancel_checked_in_booking(): void
    {
        Mail::fake();

        // Tạo booking đã check-in
        $booking = DatPhong::create([
            'loai_phong_id' => $this->loaiPhong->id,
            'so_luong_da_dat' => 1,
            'trang_thai' => 'da_xac_nhan',
            'ngay_dat' => now(),
            'ngay_nhan' => Carbon::now()->subDays(1)->format('Y-m-d'),
            'ngay_tra' => Carbon::now()->addDays(1)->format('Y-m-d'),
            'tong_tien' => 2000000,
            'thoi_gian_checkin' => Carbon::now()->subHours(2), // Đã check-in 2 giờ trước
            'username' => 'Test User',
            'email' => 'test@example.com',
            'sdt' => '0123456789',
            'cccd' => '123456789012',
        ]);

        $booking->phongs()->attach($this->phong->id);
        $this->phong->update(['trang_thai' => 'dang_thue']);

        $invoice = Invoice::create([
            'dat_phong_id' => $booking->id,
            'tong_tien' => 2000000,
            'tien_phong' => 2000000,
            'tien_dich_vu' => 0,
            'giam_gia' => 0,
            'trang_thai' => 'da_thanh_toan',
        ]);

        // Admin cố gắng hủy booking đã check-in
        $response = $this->actingAs($this->admin)
            ->post(route('admin.dat_phong.cancel.submit', $booking->id), [
                'ly_do' => 'thay_doi_lich_trinh',
            ]);

        // Assertions - Phải bị từ chối
        $response->assertRedirect(route('admin.dat_phong.index'));
        $response->assertSessionHas('error');
        $response->assertSessionHas('error', function ($message) {
            return str_contains($message, 'check-in') || str_contains($message, 'Không thể hủy');
        });

        // Kiểm tra booking KHÔNG bị hủy
        $booking->refresh();
        $this->assertEquals('da_xac_nhan', $booking->trang_thai);
        $this->assertNull($booking->ngay_huy);

        // Kiểm tra invoice không thay đổi
        $invoice->refresh();
        $this->assertEquals('da_thanh_toan', $invoice->trang_thai);

        // Kiểm tra không có bản ghi refund
        $refundCount = ThanhToan::where('hoa_don_id', $invoice->id)
            ->where('trang_thai', 'refunded')
            ->count();
        $this->assertEquals(0, $refundCount);

        // Kiểm tra email không được gửi
        Mail::assertNothingSent();
    }

    /**
     * Test Case 7: Hủy booking với voucher - voucher được hoàn trả
     */
    public function test_cancel_booking_with_voucher_returns_voucher(): void
    {
        Mail::fake();

        // Tạo voucher
        $voucher = Voucher::create([
            'ma_voucher' => 'TEST100',
            'ten_voucher' => 'Test Voucher',
            'gia_tri' => 10,
            'so_luong' => 5,
            'trang_thai' => 'con_han',
            'ngay_bat_dau' => Carbon::now()->subDays(10),
            'ngay_ket_thuc' => Carbon::now()->addDays(10),
        ]);

        $checkinDate = Carbon::now()->addDays(10);
        $booking = DatPhong::create([
            'loai_phong_id' => $this->loaiPhong->id,
            'so_luong_da_dat' => 1,
            'trang_thai' => 'da_xac_nhan',
            'ngay_dat' => now(),
            'ngay_nhan' => $checkinDate->format('Y-m-d'),
            'ngay_tra' => $checkinDate->copy()->addDays(2)->format('Y-m-d'),
            'tong_tien' => 1800000, // Đã giảm giá
            'voucher_id' => $voucher->id,
            'username' => 'Test User',
            'email' => 'test@example.com',
            'sdt' => '0123456789',
            'cccd' => '123456789012',
        ]);

        $booking->phongs()->attach($this->phong->id);

        $invoice = Invoice::create([
            'dat_phong_id' => $booking->id,
            'tong_tien' => 1800000,
            'tien_phong' => 2000000,
            'tien_dich_vu' => 0,
            'giam_gia' => 200000,
            'trang_thai' => 'da_thanh_toan',
        ]);

        // Lưu số lượng voucher ban đầu
        $initialVoucherCount = $voucher->so_luong;
        $voucher->decrement('so_luong'); // Giả sử đã sử dụng

        // Admin hủy booking
        $response = $this->actingAs($this->admin)
            ->post(route('admin.dat_phong.cancel.submit', $booking->id), [
                'ly_do' => 'thay_doi_lich_trinh',
            ]);

        // Assertions
        $response->assertRedirect(route('admin.dat_phong.index'));

        // Kiểm tra voucher đã được hoàn trả
        $voucher->refresh();
        $this->assertEquals($initialVoucherCount, $voucher->so_luong);
    }

    /**
     * Test Case 8: Không thể hủy booking ở trạng thái terminal
     */
    public function test_cannot_cancel_terminal_status_booking(): void
    {
        // Tạo booking đã hoàn thành
        $booking = DatPhong::create([
            'loai_phong_id' => $this->loaiPhong->id,
            'so_luong_da_dat' => 1,
            'trang_thai' => 'da_tra', // Terminal state
            'ngay_dat' => now(),
            'ngay_nhan' => Carbon::now()->subDays(5)->format('Y-m-d'),
            'ngay_tra' => Carbon::now()->subDays(3)->format('Y-m-d'),
            'tong_tien' => 2000000,
            'username' => 'Test User',
            'email' => 'test@example.com',
            'sdt' => '0123456789',
            'cccd' => '123456789012',
        ]);

        // Admin cố gắng hủy booking đã hoàn thành
        $response = $this->actingAs($this->admin)
            ->post(route('admin.dat_phong.cancel.submit', $booking->id), [
                'ly_do' => 'thay_doi_lich_trinh',
            ]);

        // Assertions - Phải bị từ chối
        $response->assertRedirect(route('admin.dat_phong.index'));
        $response->assertSessionHas('error');

        // Kiểm tra booking không thay đổi
        $booking->refresh();
        $this->assertEquals('da_tra', $booking->trang_thai);
    }
}
