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
     * Get bookings that have this room assigned (via phong_ids JSON)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function bookings()
    {
        // Query bookings that have this room ID in phong_ids JSON
        return DatPhong::whereJsonContains('phong_ids', $this->id)->get();
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
     * Kiểm tra cả bookings qua phong_id (legacy) và qua phong_ids JSON
     * 
     * @param Carbon|string $ngayNhan
     * @param Carbon|string $ngayTra
     * @param int|null $excludeBookingId Booking ID để loại trừ khỏi kiểm tra (khi đang đổi phòng)
     * @return bool
     */
    public function isAvailableInPeriod($ngayNhan, $ngayTra, $excludeBookingId = null)
    {
        // Nếu phòng đang bảo trì, không khả dụng (không phụ thuộc vào khoảng thời gian)
        if ($this->trang_thai === 'bao_tri') {
            return false;
        }

        // Chuyển đổi sang Carbon nếu cần
        if (!$ngayNhan instanceof Carbon) {
            $ngayNhan = Carbon::parse($ngayNhan);
        }
        if (!$ngayTra instanceof Carbon) {
            $ngayTra = Carbon::parse($ngayTra);
        }
        
        // Kiểm tra conflict với bookings trong khoảng thời gian này trước
        // Nếu có conflict, phòng không khả dụng (dù trạng thái là gì)
        // Nếu không có conflict, phòng khả dụng (trừ khi đang bảo trì)

        // Kiểm tra bookings qua phong_id trực tiếp (legacy)
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

        // Kiểm tra bookings qua phong_ids JSON
        // Kiểm tra bookings qua phong_ids JSON (các booking chứa id của phòng này)
        $conflictFromPhongIds = \App\Models\DatPhong::where(function($query) use ($ngayNhan, $ngayTra, $excludeBookingId, $today) {
                $query->where(function($q) use ($ngayNhan, $ngayTra) {
                    $q->where('ngay_tra', '>', $ngayNhan)
                      ->where('ngay_nhan', '<', $ngayTra);
                })
                ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                ->where('ngay_tra', '>', $today)
                ->when($excludeBookingId, function($q) use ($excludeBookingId) {
                    $q->where('id', '!=', $excludeBookingId);
                });
            })
            ->whereJsonContains('phong_ids', $this->id)
            ->exists();

        // Phòng khả dụng nếu:
        // 1. Không có conflict với bookings trong khoảng thời gian này
        // 2. Phòng không đang bảo trì (đã check ở đầu method)
        // 
        // Lưu ý: Không check trạng thái 'dang_thue' hay 'trong' ở đây vì:
        // - Phòng có thể 'dang_thue' cho booking khác (không overlap)
        // - Phòng có thể 'trong' nhưng đã được đặt cho khoảng thời gian này (sẽ bị phát hiện bởi conflict check)
        return !$conflictFromDirect && !$conflictFromPhongIds;
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
    public static function countAvailableRooms($loaiPhongId, $ngayNhan, $ngayTra)
    {
        return static::findAvailableRooms($loaiPhongId, $ngayNhan, $ngayTra, 999)->count();
    }
}
