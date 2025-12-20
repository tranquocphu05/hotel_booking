<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Phong extends Model
{
    use HasFactory;

    protected $table = 'phong';

    protected $fillable = [
        'loai_phong_id',
        'so_phong',
        'ten_phong',
        'tang',
        'huong_cua_so',
        'trang_thai',
        'co_ban_cong',
        'co_view_dep',
        'gia_rieng',
        'gia_bo_sung',
        'ghi_chu',
    ];

    protected $casts = [
        'co_ban_cong' => 'boolean',
        'co_view_dep' => 'boolean',
        'gia_rieng' => 'decimal:2',
        'gia_bo_sung' => 'decimal:2',
    ];

    /**
     * Relationship với loai_phong
     */
    public function loaiPhong()
    {
        return $this->belongsTo(LoaiPhong::class, 'loai_phong_id');
    }

    /**
     * Relationship với dat_phong (bookings) - legacy support (direct phong_id)
     */
    public function datPhongs()
    {
        return $this->hasMany(DatPhong::class, 'phong_id');
    }

    /**
     * Get bookings that have this room assigned via pivot table booking_rooms
     * BUG FIX: Use belongsToMany to query pivot table instead of legacy hasMany
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function bookings(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        // Query bookings via pivot table booking_rooms (new system)
        return $this->belongsToMany(DatPhong::class, 'booking_rooms', 'phong_id', 'dat_phong_id')
            ->withTimestamps();
    }

    /**
     * Kiểm tra phòng có trống không
     */
    public function isTrong()
    {
        return $this->trang_thai === 'trong';
    }

    /**
     * Kiểm tra phòng có đang được thuê không
     */
    public function isDangThue()
    {
        return $this->trang_thai === 'dang_thue';
    }

    /**
     * Kiểm tra phòng có đang bảo trì không
     */
    public function isBaoTri()
    {
        return $this->trang_thai === 'bao_tri';
    }

    /**
     * Kiểm tra phòng có đang dọn dẹp không
     */
    public function isDangDon()
    {
        return $this->trang_thai === 'dang_don';
    }

    /**
     * Kiểm tra phòng có sẵn sàng để đặt không
     * (trống và không bảo trì)
     */
    public function isAvailable()
    {
        return $this->trang_thai === 'trong';
    }

    /**
     * Kiểm tra phòng có trống trong khoảng thời gian cụ thể không
     * Kiểm tra bookings qua phong_id (legacy) và qua pivot table booking_rooms
     *
     * BUG FIX #7: Làm rõ logic và thêm documentation
     *
     * Logic kiểm tra:
     * 1. Phòng bảo trì → LUÔN return false (không khả dụng)
     * 2. Check conflict với bookings trong khoảng thời gian
     * 3. Return true nếu không có conflict
     *
     * Lưu ý: KHÔNG dựa vào trang_thai 'trong' hay 'dang_thue' để check availability
     * vì phòng có thể 'dang_thue' cho booking khác (không overlap) → vẫn available
     *
     * @param Carbon|string $ngayNhan Check-in date
     * @param Carbon|string $ngayTra Check-out date
     * @param int|null $excludeBookingId Booking ID để loại trừ khỏi kiểm tra (khi đang đổi phòng)
     * @return bool true nếu phòng khả dụng, false nếu:
     *              - Phòng đang bảo trì
     *              - Phòng có booking conflict trong khoảng thời gian
     */
    public function isAvailableInPeriod($ngayNhan, $ngayTra, $excludeBookingId = null)
    {
        // Rule 1: Phòng bảo trì LUÔN không khả dụng (bất kể có booking hay không)
        if ($this->trang_thai === 'bao_tri') {
            return false;
        }

        // Rule 2: Check conflict với bookings
        // Convert to Carbon if needed
        if (!$ngayNhan instanceof Carbon) {
            $ngayNhan = Carbon::parse($ngayNhan);
        }
        if (!$ngayTra instanceof Carbon) {
            $ngayTra = Carbon::parse($ngayTra);
        }

        // Conflict detection logic:
        // Hai booking conflict nếu: existing.ngay_tra > new.ngay_nhan AND existing.ngay_nhan < new.ngay_tra
        // Chỉ tính conflict với các booking chưa kết thúc (ngay_tra > hôm nay)
        $today = Carbon::today();
        $conflictFromDirect = $this->datPhongs()
            ->when($excludeBookingId, function($query) use ($excludeBookingId) {
                $query->where('id', '!=', $excludeBookingId);
            })
            ->where(function($query) use ($ngayNhan, $ngayTra, $today) {
                $query->where(function($q) use ($ngayNhan, $ngayTra) {
                    // Logic đúng: Hai khoảng thời gian overlap nếu:
                    // existing.ngay_nhan < new.ngay_tra AND existing.ngay_tra > new.ngay_nhan
                    $q->where('ngay_tra', '>', $ngayNhan)
                      ->where('ngay_nhan', '<', $ngayTra);
                })
                ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                ->where('ngay_tra', '>', $today);
            })
            ->exists();

        // CRITICAL FIX: Kiểm tra bookings qua PIVOT TABLE booking_rooms
        // Đã chuyển từ phong_ids JSON sang pivot table
        $conflictFromPivot = \App\Models\DatPhong::whereHas('phongs', function($q) {
                $q->where('phong_id', $this->id);
            })
            ->when($excludeBookingId, function($query) use ($excludeBookingId) {
                $query->where('id', '!=', $excludeBookingId);
            })
            ->where(function($query) use ($ngayNhan, $ngayTra, $today) {
                $query->where(function($q) use ($ngayNhan, $ngayTra) {
                    $q->where('ngay_tra', '>', $ngayNhan)
                      ->where('ngay_nhan', '<', $ngayTra);
                })
                ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                ->where('ngay_tra', '>', $today);
            })
            ->exists();

        // Phòng khả dụng nếu:
        // 1. Không có conflict với bookings trong khoảng thời gian này
        // 2. Phòng không đang bảo trì (đã check ở đầu method)
        //
        // Lưu ý: Không check trạng thái 'dang_thue' hay 'trong' ở đây vì:
        // - Phòng có thể 'dang_thue' cho booking khác (không overlap)
        // - Phòng có thể 'trong' nhưng đã được đặt cho khoảng thời gian này (sẽ bị phát hiện bởi conflict check)
        return !$conflictFromDirect && !$conflictFromPivot;
    }

    /**
     * Lấy giá hiển thị của phòng
     * Ưu tiên: gia_rieng > (gia_co_ban + gia_bo_sung) > gia_co_ban
     */
    public function getGiaHienThiAttribute()
    {
        if (!is_null($this->gia_rieng)) {
            return $this->gia_rieng;
        }

        $loaiPhong = $this->loaiPhong;
        if (!$loaiPhong) {
            return 0;
        }

        $giaCoBan = $loaiPhong->gia_co_ban ?? 0;
        $giaBoSung = $this->gia_bo_sung ?? 0;

        return $giaCoBan + $giaBoSung;
    }

    /**
     * Scope: Lấy các phòng trống
     */
    public function scopeTrong($query)
    {
        return $query->where('trang_thai', 'trong');
    }

    /**
     * Scope: Lấy các phòng theo loại
     */
    public function scopeTheoLoai($query, $loaiPhongId)
    {
        return $query->where('loai_phong_id', $loaiPhongId);
    }

    /**
     * Scope: Lấy các phòng khả dụng (trống và không bảo trì)
     */
    public function scopeKhachDung($query)
    {
        return $query->where('trang_thai', 'trong');
    }

    /**
     * Tìm phòng trống trong loại phòng cho khoảng thời gian cụ thể
     *
     * @param int $loaiPhongId
     * @param Carbon|string $ngayNhan
     * @param Carbon|string $ngayTra
     * @param int $soLuong Số lượng phòng cần tìm
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function findAvailableRooms($loaiPhongId, $ngayNhan, $ngayTra, $soLuong = 1, $excludeBookingId = null)
    {
        // Chuyển đổi sang Carbon nếu cần
        if (!$ngayNhan instanceof Carbon) {
            $ngayNhan = Carbon::parse($ngayNhan);
        }
        if (!$ngayTra instanceof Carbon) {
            $ngayTra = Carbon::parse($ngayTra);
        }

        // Tìm các phòng của loại phòng này
        // KHÔNG filter theo trang_thai ở đây vì:
        // - Phòng có thể 'dang_thue' cho booking khác (không overlap) → vẫn available
        // - Phòng có thể 'trong' nhưng đã được đặt cho khoảng thời gian này → conflict
        // Logic isAvailableInPeriod sẽ quyết định dựa trên conflict check
        $availableRooms = static::where('loai_phong_id', $loaiPhongId)
            ->get()
            ->filter(function($phong) use ($ngayNhan, $ngayTra, $excludeBookingId) {
                return $phong->isAvailableInPeriod($ngayNhan, $ngayTra, $excludeBookingId);
            })
            ->take($soLuong)
            ->values(); // Đảm bảo collection có numeric keys

        return $availableRooms;
    }

    /**
     * Đếm số phòng trống trong loại phòng cho khoảng thời gian cụ thể
     *
     * Ví dụ: Nếu tất cả phòng đã được đặt từ 01/11 - 07/11,
     * nhưng khách muốn đặt từ 08/11 - 14/11, method này sẽ trả về
     * số phòng trống cho khoảng thời gian 08/11 - 14/11 (không conflict với booking 01/11 - 07/11)
     *
     * @param int $loaiPhongId
     * @param Carbon|string $ngayNhan
     * @param Carbon|string $ngayTra
     * @return int
     */
    public static function countAvailableRooms($loaiPhongId, $ngayNhan, $ngayTra, $excludeBookingId = null)
    {
        // Pass through excludeBookingId so findAvailableRooms can ignore rooms
        // already assigned to the provided booking when calculating availability.
        return static::findAvailableRooms($loaiPhongId, $ngayNhan, $ngayTra, 999, $excludeBookingId)->count();
    }

    /**
     * Boot method - Register model events
     */
    protected static function boot()
    {
        parent::boot();

        // Khi trạng thái phòng thay đổi, recalculate so_luong_trong của loại phòng
        static::updated(function ($phong) {
            if ($phong->isDirty('trang_thai') && $phong->loai_phong_id) {
                // Recalculate so_luong_trong: bao gồm cả 'trong' và 'dang_don'
                // Vì phòng đang dọn vẫn có thể đặt trước (pre-booking)
                $trongCount = static::where('loai_phong_id', $phong->loai_phong_id)
                    ->whereIn('trang_thai', ['trong', 'dang_don'])
                    ->count();

                LoaiPhong::where('id', $phong->loai_phong_id)
                    ->update(['so_luong_trong' => $trongCount]);
            }

            // Nếu đổi loại phòng, cần recalculate cho cả 2 loại phòng
            if ($phong->isDirty('loai_phong_id')) {
                $oldLoaiPhongId = $phong->getOriginal('loai_phong_id');
                $newLoaiPhongId = $phong->loai_phong_id;

                // Recalculate cho loại phòng cũ
                if ($oldLoaiPhongId) {
                    $trongCountOld = static::where('loai_phong_id', $oldLoaiPhongId)
                        ->whereIn('trang_thai', ['trong', 'dang_don'])
                        ->count();
                    LoaiPhong::where('id', $oldLoaiPhongId)
                        ->update(['so_luong_trong' => $trongCountOld]);
                }

                // Recalculate cho loại phòng mới
                if ($newLoaiPhongId) {
                    $trongCountNew = static::where('loai_phong_id', $newLoaiPhongId)
                        ->whereIn('trang_thai', ['trong', 'dang_don'])
                        ->count();
                    LoaiPhong::where('id', $newLoaiPhongId)
                        ->update(['so_luong_trong' => $trongCountNew]);
                }
            }
        });

        // Khi phòng bị xóa, recalculate so_luong_trong
        static::deleted(function ($phong) {
            if ($phong->loai_phong_id) {
                $trongCount = static::where('loai_phong_id', $phong->loai_phong_id)
                    ->whereIn('trang_thai', ['trong', 'dang_don'])
                    ->count();

                LoaiPhong::where('id', $phong->loai_phong_id)
                    ->update(['so_luong_trong' => $trongCount]);
            }
        });
    }
}
