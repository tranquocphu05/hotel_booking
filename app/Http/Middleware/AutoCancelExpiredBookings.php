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
     * Tự động hủy booking quá hạn (chỉ check mỗi 1 phút để tránh chậm)
     */
    public function handle(Request $request, Closure $next)
    {
        // Chỉ check mỗi 1 phút để tránh làm chậm website
        $cacheKey = 'last_booking_cancel_check';
        $lastCheck = Cache::get($cacheKey);

        if (!$lastCheck || Carbon::now()->diffInMinutes($lastCheck) >= 1) {
            $this->cancelExpiredBookings();
            Cache::put($cacheKey, Carbon::now(), 60); // Cache 1 phút
        }

        return $next($request);
    }

    /**
     * Hủy các booking quá hạn (sau 5 phút chưa thanh toán)
     */
    private function cancelExpiredBookings()
    {
        try {
            $expiredDate = Carbon::now()->subMinutes(5);

            $expiredBookings = DatPhong::where('trang_thai', 'cho_xac_nhan')
                ->where('ngay_dat', '<=', $expiredDate)
                ->whereHas('invoice', function ($query) {
                    $query->where('trang_thai', 'cho_thanh_toan');
                })
                ->with(['invoice', 'loaiPhong', 'voucher'])
                ->get();

            foreach ($expiredBookings as $booking) {
                try {
                    DB::transaction(function () use ($booking) {
                        $booking->load(['phong', 'loaiPhong']);

                        // Cập nhật trạng thái
                        $booking->trang_thai = 'da_huy';
                        $booking->ly_do_huy = 'Tự động hủy do không thanh toán sau 5 phút';
                        $booking->ngay_huy = now();
                        $booking->save();

                        // Giải phóng phòng qua phong_id (legacy)
                        if ($booking->phong_id && $booking->phong) {
                            $hasOtherBooking = DatPhong::where('id', '!=', $booking->id)
                                ->where(function($q) use ($booking) {
                                    $q->where('phong_id', $booking->phong_id)
                                      ->orWhereJsonContains('phong_ids', $booking->phong_id);
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
                                          ->orWhereJsonContains('phong_ids', $phongId);
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

