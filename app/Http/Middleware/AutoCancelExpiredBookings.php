<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\DatPhong;
use App\Models\Phong;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
class AutoCancelExpiredBookings
{
    /**
     * Handle an incoming request.
     * Tự động hủy booking quá hạn (check mỗi 30 giây để đảm bảo hủy đúng thời gian)
     */
    public function handle(Request $request, Closure $next)
    {
        // Check mỗi 30 giây để đảm bảo hủy đúng thời gian (giảm từ 1 phút xuống 30 giây)
        $cacheKey = 'last_booking_cancel_check';
        $lastCheck = Cache::get($cacheKey);

        // Chỉ check nếu chưa check trong 30 giây gần đây
        if (!$lastCheck || Carbon::now()->diffInSeconds($lastCheck) >= 30) {
            $this->cancelExpiredBookings();
            Cache::put($cacheKey, Carbon::now(), 30); // Cache 30 giây
        }

        return $next($request);
    }

    /**
     * Hủy các booking quá hạn (sau 5 phút chưa thanh toán)
     */
    private function cancelExpiredBookings()
    {
        try {
            // Tính chính xác: booking quá 5 phút (300 giây) sẽ bị hủy
            $expiredDate = Carbon::now()->subSeconds(300); // 5 phút = 300 giây

            $expiredBookings = DatPhong::where('trang_thai', 'cho_xac_nhan')
                ->where('ngay_dat', '<=', $expiredDate)
                ->whereHas('invoice', function ($query) {
                    $query->where('trang_thai', 'cho_thanh_toan');
                })
                ->with(['invoice', 'loaiPhong', 'voucher'])
                ->get();

            if ($expiredBookings->count() > 0) {
                Log::info("AutoCancelExpiredBookings: Tìm thấy {$expiredBookings->count()} booking quá hạn cần hủy");
            }

            foreach ($expiredBookings as $booking) {
                try {
                    DB::transaction(function () use ($booking) {
                        $booking->load(['phong', 'loaiPhong']);

                        // Tính thời gian chính xác từ lúc đặt đến lúc hủy
                        $bookingAge = Carbon::now()->diffInSeconds($booking->ngay_dat);

                        // Cập nhật trạng thái
                        $booking->trang_thai = 'da_huy';
                        $booking->ly_do_huy = 'Tự động hủy do không thanh toán sau 5 phút';
                        $booking->ngay_huy = now();
                        $booking->save();

                        Log::info("Đã tự động hủy booking #{$booking->id} - Đặt lúc: {$booking->ngay_dat}, Hủy lúc: " . now() . ", Thời gian: {$bookingAge} giây");

                        // Giải phóng phòng qua phong_id (legacy)
                        if ($booking->phong_id && $booking->phong) {
                            $hasOtherBooking = DatPhong::where('id', '!=', $booking->id)
                                ->where(function($q) use ($booking) {
                                    $q->where('phong_id', $booking->phong_id)
                                      ->orWhereContainsPhongId($booking->phong_id);
                                })
                                ->where(function($q) use ($booking) {
                                    $q->where('ngay_tra', '>', $booking->ngay_nhan)
                                      ->where('ngay_nhan', '<', $booking->ngay_tra);
                                })
                                ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                                ->exists();

                            if (!$hasOtherBooking) {
                                $booking->phong->update(['trang_thai' => 'trong']);
                            }
                        }

                        // Giải phóng phòng qua phong_ids JSON
                        $phongIds = $booking->getPhongIds();
                        foreach ($phongIds as $phongId) {
                            $phong = Phong::find($phongId);
                            if ($phong) {
                                $hasOtherBooking = DatPhong::where('id', '!=', $booking->id)
                                    ->where(function($q) use ($phongId) {
                                        $q->where('phong_id', $phongId)
                                          ->orWhereContainsPhongId($phongId);
                                    })
                                    ->where(function($q) use ($booking) {
                                        $q->where('ngay_tra', '>', $booking->ngay_nhan)
                                          ->where('ngay_nhan', '<', $booking->ngay_tra);
                                    })
                                    ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                                    ->exists();

                                if (!$hasOtherBooking) {
                                    $phong->update(['trang_thai' => 'trong']);
                                }
                            }
                        }

                        // Xóa phong_ids
                        $booking->phong_ids = [];
                        $booking->save();

                        // Hoàn trả voucher
                        if ($booking->voucher_id) {
                            $voucher = Voucher::find($booking->voucher_id);
                            if ($voucher) {
                                $voucher->increment('so_luong');
                            }
                        }

                        // Cập nhật so_luong_trong cho tất cả loại phòng
                        $roomTypes = $booking->getRoomTypes();
                        $loaiPhongIdsToUpdate = [];

                        if (!empty($roomTypes)) {
                            foreach ($roomTypes as $roomType) {
                                if (isset($roomType['loai_phong_id'])) {
                                    $loaiPhongIdsToUpdate[] = $roomType['loai_phong_id'];
                                }
                            }
                        }

                        if ($booking->loai_phong_id && !in_array($booking->loai_phong_id, $loaiPhongIdsToUpdate)) {
                            $loaiPhongIdsToUpdate[] = $booking->loai_phong_id;
                        }

                        foreach (array_unique($loaiPhongIdsToUpdate) as $loaiPhongId) {
                            $trongCount = Phong::where('loai_phong_id', $loaiPhongId)
                                ->where('trang_thai', 'trong')
                                ->count();
                            \App\Models\LoaiPhong::where('id', $loaiPhongId)
                                ->update(['so_luong_trong' => $trongCount]);
                        }
                    });
                } catch (\Exception $e) {
                    // Log lỗi nhưng không dừng request
                    Log::error("Lỗi khi tự động hủy booking #{$booking->id}: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            // Log lỗi nhưng không dừng request
            Log::error("Lỗi trong AutoCancelExpiredBookings middleware: " . $e->getMessage());
        }
    }
}

