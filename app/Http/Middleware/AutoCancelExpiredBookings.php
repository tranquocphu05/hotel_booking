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

        // Tự động chuyển phòng từ 'dang_don' về 'trong' (check mỗi 30 giây)
        $roomCleanCacheKey = 'last_room_clean_check';
        $lastRoomClean = Cache::get($roomCleanCacheKey);

        // Chỉ check nếu chưa check trong 30 giây gần đây
        if (!$lastRoomClean || Carbon::now()->diffInSeconds($lastRoomClean) >= 30) {
            $this->autoCleanRooms();
            Cache::put($roomCleanCacheKey, Carbon::now(), 30); // Cache 30 giây
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

                        // BUG FIX #5: Lưu phong_ids trước khi xóa để có thể rollback nếu lỗi
                        $phongIdsToFree = $booking->getPhongIds();

                        // Cập nhật trạng thái booking
                        $booking->trang_thai = 'da_huy';
                        $booking->ly_do_huy = 'Tự động hủy do không thanh toán sau 5 phút';
                        $booking->ngay_huy = now();
                        $booking->save();

                        // Detach all rooms from pivot table
                        $booking->phongs()->detach();

                        Log::info("Đã tự động hủy booking #{$booking->id} - Đặt lúc: {$booking->ngay_dat}, Hủy lúc: " . now() . ", Thời gian: {$bookingAge} giây");

                        // Giải phóng phòng qua phong_id (legacy)
                        if ($booking->phong_id && $booking->phong) {
                            $this->freeRoomIfNoOtherBooking($booking->phong_id, $booking);
                        }

                        // Giải phóng phòng qua phong_ids JSON
                        foreach ($phongIdsToFree as $phongId) {
                            $this->freeRoomIfNoOtherBooking($phongId, $booking);
                        }

                        // Hoàn trả voucher
                        if ($booking->voucher_id) {
                            $voucher = Voucher::find($booking->voucher_id);
                            if ($voucher) {
                                $voucher->increment('so_luong');
                            }
                        }

                        // Cập nhật so_luong_trong cho tất cả loại phòng
                        $loaiPhongIdsToUpdate = $this->getAffectedRoomTypeIds($booking);
                        foreach (array_unique($loaiPhongIdsToUpdate) as $loaiPhongId) {
                            $this->recalculateSoLuongTrong($loaiPhongId);
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

    /**
     * Free room if no other booking is using it in the same period
     *
     * @param int $phongId
     * @param DatPhong $booking
     * @return void
     */
    private function freeRoomIfNoOtherBooking(int $phongId, DatPhong $booking): void
    {
        $phong = Phong::find($phongId);
        if (!$phong) {
            return;
        }

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

    /**
     * Get all room type IDs affected by this booking
     *
     * @param DatPhong $booking
     * @return array
     */
    private function getAffectedRoomTypeIds(DatPhong $booking): array
    {
        $loaiPhongIds = [];

        // Add primary loai_phong_id
        if ($booking->loai_phong_id) {
            $loaiPhongIds[] = $booking->loai_phong_id;
        }

        // Add all room types from room_types JSON
        $roomTypes = $booking->getRoomTypes();
        foreach ($roomTypes as $roomType) {
            if (isset($roomType['loai_phong_id'])) {
                $loaiPhongIds[] = $roomType['loai_phong_id'];
            }
        }

        return array_unique($loaiPhongIds);
    }

    /**
     * Recalculate so_luong_trong for a room type
     *
     * @param int $loaiPhongId
     * @return void
     */
    private function recalculateSoLuongTrong(int $loaiPhongId): void
    {
        $trongCount = Phong::where('loai_phong_id', $loaiPhongId)
            ->where('trang_thai', 'trong')
            ->count();

        \App\Models\LoaiPhong::where('id', $loaiPhongId)
            ->update(['so_luong_trong' => $trongCount]);
    }

    /**
     * Tự động chuyển phòng từ 'dang_don' về 'trong' sau khi ngày checkout đã qua
     */
    private function autoCleanRooms()
    {
        try {
            $today = Carbon::today();

            // Tìm các phòng đang ở trạng thái 'dang_don'
            $dangDonRooms = Phong::where('trang_thai', 'dang_don')->get();

            $cleanedCount = 0;

            foreach ($dangDonRooms as $phong) {
                // Tìm booking gần nhất đã checkout cho phòng này
                $lastCheckout = DatPhong::whereHas('phongs', function($q) use ($phong) {
                        $q->where('phong_id', $phong->id);
                    })
                    ->where('trang_thai', 'da_tra')
                    ->whereNotNull('thoi_gian_checkout')
                    ->orderBy('thoi_gian_checkout', 'desc')
                    ->first();

                if ($lastCheckout) {
                    $checkoutDate = Carbon::parse($lastCheckout->thoi_gian_checkout)->startOfDay();

                    // Nếu đã qua ngày checkout (sau 1 ngày), chuyển về 'trong'
                    // BUG FIX #2: Check for both future bookings AND ongoing bookings
                    // Ongoing bookings: started before/on today but end in future
                    // Future bookings: start and end in future
                    $hasFutureBooking = DatPhong::whereHas('phongs', function($q) use ($phong) {
                            $q->where('phong_id', $phong->id);
                        })
                        ->where(function($q) use ($today) {
                            // Booking trong tương lai (ngay_nhan > today)
                            $q->where(function($subQ) use ($today) {
                                $subQ->where('ngay_nhan', '>', $today)
                                     ->where('ngay_tra', '>', $today);
                            })
                            // Hoặc booking đang diễn ra (ngay_nhan <= today và ngay_tra > today)
                            ->orWhere(function($subQ) use ($today) {
                                $subQ->where('ngay_nhan', '<=', $today)
                                     ->where('ngay_tra', '>', $today);
                            });
                        })
                        ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                        ->exists();

                    // Chuyển về 'trong' nếu:
                    // 1. Đã qua ngày checkout (sau 1 ngày)
                    // 2. Không có booking conflict trong tương lai
                    if ($today->gt($checkoutDate->copy()->addDay()) && !$hasFutureBooking) {
                        DB::transaction(function () use ($phong) {
                            $phong->update(['trang_thai' => 'trong']);

                            // Recalculate so_luong_trong cho loại phòng
                            $loaiPhongId = $phong->loai_phong_id;
                            $this->recalculateSoLuongTrong($loaiPhongId);
                        });

                        $cleanedCount++;
                        Log::info("Auto cleaned room {$phong->so_phong} (ID: {$phong->id}) from dang_don to trong");
                    }
                } else {
                    // Nếu không tìm thấy booking checkout, có thể là phòng bị stuck ở dang_don
                    // Chuyển về 'trong' nếu không có booking conflict
                    $hasConflict = DatPhong::whereHas('phongs', function($q) use ($phong) {
                            $q->where('phong_id', $phong->id);
                        })
                        ->where(function($q) use ($today) {
                            $q->where('ngay_tra', '>', $today);
                        })
                        ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                        ->exists();

                    if (!$hasConflict) {
                        DB::transaction(function () use ($phong) {
                            $phong->update(['trang_thai' => 'trong']);

                            $loaiPhongId = $phong->loai_phong_id;
                            $this->recalculateSoLuongTrong($loaiPhongId);
                        });

                        $cleanedCount++;
                        Log::info("Auto cleaned stuck room {$phong->so_phong} (ID: {$phong->id}) from dang_don to trong");
                    }
                }
            }

            if ($cleanedCount > 0) {
                Log::info("AutoCleanRooms: Đã tự động chuyển {$cleanedCount} phòng từ 'dang_don' về 'trong'");
            }
        } catch (\Exception $e) {
            // Log lỗi nhưng không dừng request
            Log::error("Lỗi trong autoCleanRooms: " . $e->getMessage());
        }
    }
}

