<?php

namespace App\Models;

use App\Models\Invoice;
use App\Models\LoaiPhong;
use Illuminate\Database\Eloquent\Model;

class DatPhong extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dat_phong';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nguoi_dung_id',
        'loai_phong_id',  // Book by room type (primary/legacy support)
        'room_types',  // JSON array of room types: [{"loai_phong_id": 1, "so_luong": 2, "gia_rieng": 1000000}, ...]
        'phong_ids',  // JSON array of assigned room IDs: [1, 2, 3]
        'so_luong_da_dat',  // Number of rooms booked in this booking
        'phong_id',  // Specific room assigned (nullable, legacy support)
        'ngay_dat',
        'ngay_nhan',
        'ngay_tra',
        'so_nguoi',
        'trang_thai',
        'tong_tien',
        'voucher_id',
        'ly_do_huy',
        'ngay_huy',
        'username',
        'email',
        'sdt',
        'cccd'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'ngay_dat' => 'datetime',
        'ngay_nhan' => 'date',
        'ngay_tra' => 'date',
        'tong_tien' => 'decimal:2',
        'room_types' => 'array', // Auto decode/encode JSON
        'phong_ids' => 'array', // Auto decode/encode JSON
    ];

    /**
     * Get the user that owns the booking.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'nguoi_dung_id');
    }

    /**
     * Get the room type associated with the booking.
     */
    public function loaiPhong()
    {
        return $this->belongsTo(LoaiPhong::class, 'loai_phong_id');
    }

    /**
     * Get the voucher associated with the booking.
     */
    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    /**
     * Get the specific room assigned to this booking (single room - legacy support).
     */
    public function phong()
    {
        return $this->belongsTo(Phong::class, 'phong_id');
    }

    /**
     * Get all rooms assigned to this booking (via phong_ids JSON).
     * @deprecated Use getPhongIds() or getAssignedPhongs() instead. Pivot table has been removed.
     * This method returns a relationship that queries phong_ids JSON column.
     */
    public function phongs()
    {
        // Create a relationship that queries rooms based on phong_ids JSON column
        // Since we can't reference parent table columns directly in hasMany WHERE,
        // we need to use a subquery or get the IDs first
        // For now, return empty relationship and use getAssignedPhongs() instead
        $phongIds = $this->getPhongIds();
        
        if (empty($phongIds)) {
            // Return empty relationship if no phong_ids
            return $this->hasMany(Phong::class, 'id', 'id')
                ->whereRaw('1 = 0');
        }
        
        // Return relationship that filters by IDs from phong_ids JSON
        return $this->hasMany(Phong::class, 'id', 'id')
            ->whereIn('id', $phongIds);
    }

    /**
     * Get array of assigned room IDs from JSON column.
     * Returns array of room IDs: [1, 2, 3]
     */
    public function getPhongIds()
    {
        if ($this->phong_ids && is_array($this->phong_ids)) {
            return $this->phong_ids;
        }
        
        // Fallback: If no phong_ids, try to get from phong_id (legacy support)
        if ($this->phong_id) {
            return [$this->phong_id];
        }
        
        // Fallback: If no phong_ids, pivot table has been removed
        // All data should now be in phong_ids JSON column
        
        return [];
    }

    /**
     * Get assigned Phong models from phong_ids JSON.
     */
    public function getAssignedPhongs()
    {
        $phongIds = $this->getPhongIds();
        if (empty($phongIds)) {
            return collect([]);
        }
        
        return Phong::whereIn('id', $phongIds)->get();
    }

    /**
     * Set phong_ids array.
     */
    public function setPhongIds(array $phongIds)
    {
        $this->phong_ids = $phongIds;
        return $this;
    }

    /**
     * Add a room ID to phong_ids.
     */
    public function addPhongId($phongId)
    {
        $phongIds = $this->getPhongIds();
        if (!in_array($phongId, $phongIds)) {
            $phongIds[] = $phongId;
            $this->phong_ids = $phongIds;
        }
        return $this;
    }

    /**
     * Remove a room ID from phong_ids.
     */
    public function removePhongId($phongId)
    {
        $phongIds = $this->getPhongIds();
        $phongIds = array_values(array_filter($phongIds, function($id) use ($phongId) {
            return $id != $phongId;
        }));
        $this->phong_ids = $phongIds;
        return $this;
    }

    /**
     * Get all room types in this booking (from JSON field).
     * Returns array of room types with loai_phong_id, so_luong, gia_rieng
     */
    public function getRoomTypes()
    {
        if ($this->room_types && is_array($this->room_types)) {
            return $this->room_types;
        }
        
        // Fallback: If no room_types, return single room type (legacy support)
        if ($this->loai_phong_id) {
            return [[
                'loai_phong_id' => $this->loai_phong_id,
                'so_luong' => $this->so_luong_da_dat ?? 1,
                'gia_rieng' => $this->tong_tien ?? 0,
            ]];
        }
        
        return [];
    }

    /**
     * Get all assigned room models directly (via phong_ids JSON).
     * @deprecated Use getAssignedPhongs() instead
     */
    public function assignedPhongs()
    {
        return $this->getAssignedPhongs();
    }

    /**
     * Get the invoice associated with the booking.
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'dat_phong_id');
    }

    /**
     * Scope a query to only include bookings with a specific status.
     */
    public function scopeTrangThai($query, $trangThai)
    {
        return $query->where('trang_thai', $trangThai);
    }

    /**
     * Check if booking is confirmed
     */
    public function isDaXacNhan()
    {
        return $this->trang_thai === 'da_xac_nhan';
    }

    /**
     * Check if booking is cancelled
     */
    public function isDaHuy()
    {
        return $this->trang_thai === 'da_huy';
    }

    /**
     * Check if booking is completed
     */
    public function isDaTra()
    {
        return $this->trang_thai === 'da_tra';
    }

    /**
     * Check if room type has available rooms
     *
     * @param int $loaiPhongId
     * @return bool
     */
    public static function hasAvailableRooms($loaiPhongId)
    {
        $loaiPhong = LoaiPhong::find($loaiPhongId);
        return $loaiPhong && $loaiPhong->so_luong_trong > 0;
    }

    /**
     * Decrease available room count when booking is created/confirmed
     */
    public static function boot()
    {
        parent::boot();

        // Note: so_luong_trong is updated directly in BookingController transaction
        // to ensure atomicity with lockForUpdate. This prevents double-decrement.
        // The created event is kept for edge cases but should not decrement again.

        // When booking status changes
        static::updated(function ($booking) {
            if ($booking->isDirty('trang_thai')) {
                $oldStatus = $booking->getOriginal('trang_thai');
                $newStatus = $booking->trang_thai;
                $soLuong = $booking->so_luong_da_dat ?? 1;

                // Recalculate so_luong_trong dựa trên số phòng thực tế có trang_thai = 'trong'
                // Không dùng decrement/increment nữa vì có thể gây ra số âm hoặc không chính xác
                $trongCount = \App\Models\Phong::where('loai_phong_id', $booking->loai_phong_id)
                    ->where('trang_thai', 'trong')
                    ->count();
                \App\Models\LoaiPhong::where('id', $booking->loai_phong_id)
                    ->update(['so_luong_trong' => $trongCount]);

                // Load relationships
                $booking->load(['phong']);
                
                // Update Phong status if phong_id is set (legacy)
                if ($booking->phong_id) {
                    $phong = \App\Models\Phong::find($booking->phong_id);
                    if ($phong) {
                        // Khi booking được confirm -> phòng chuyển sang "đang thuê"
                        if ($newStatus === 'da_xac_nhan' && $oldStatus !== 'da_xac_nhan') {
                            $phong->update(['trang_thai' => 'dang_thue']);
                        }
                        // Khi booking bị hủy/từ chối -> phòng chuyển về "trống"
                        elseif (in_array($newStatus, ['da_huy', 'tu_choi', 'thanh_toan_that_bai'])
                            && in_array($oldStatus, ['cho_xac_nhan', 'da_xac_nhan'])) {
                            $phong->update(['trang_thai' => 'trong']);
                        }
                        // Khi booking hoàn thành (check-out) -> phòng chuyển sang "đang dọn"
                        elseif ($newStatus === 'da_tra' && $oldStatus !== 'da_tra') {
                            $phong->update(['trang_thai' => 'dang_don']);
                        }
                    }
                }
                
                // Update Phong status via phong_ids JSON
                $assignedPhongs = $booking->getAssignedPhongs();
                foreach ($assignedPhongs as $phong) {
                    // Khi booking được confirm -> phòng chuyển sang "đang thuê"
                    if ($newStatus === 'da_xac_nhan' && $oldStatus !== 'da_xac_nhan') {
                        $phong->update(['trang_thai' => 'dang_thue']);
                    }
                    // Khi booking bị hủy/từ chối -> phòng chuyển về "trống"
                    elseif (in_array($newStatus, ['da_huy', 'tu_choi', 'thanh_toan_that_bai'])
                        && in_array($oldStatus, ['cho_xac_nhan', 'da_xac_nhan'])) {
                        // Kiểm tra xem phòng có đang được đặt cho booking khác không
                        $hasOtherBooking = \App\Models\DatPhong::where('id', '!=', $booking->id)
                            ->whereJsonContains('phong_ids', $phong->id)
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
                    // Khi booking hoàn thành (check-out) -> phòng chuyển sang "đang dọn"
                    elseif ($newStatus === 'da_tra' && $oldStatus !== 'da_tra') {
                        $phong->update(['trang_thai' => 'dang_don']);
                    }
                }
                
                // Recalculate so_luong_trong based on actual room status
                if (in_array($newStatus, ['da_huy', 'tu_choi', 'thanh_toan_that_bai', 'da_tra'])) {
                    $trongCount = \App\Models\Phong::where('loai_phong_id', $booking->loai_phong_id)
                        ->where('trang_thai', 'trong')
                        ->count();
                    \App\Models\LoaiPhong::where('id', $booking->loai_phong_id)
                        ->update(['so_luong_trong' => $trongCount]);
                }
            }
        });

        // When booking is deleted, recalculate so_luong_trong
        static::deleted(function ($booking) {
            $trongCount = \App\Models\Phong::where('loai_phong_id', $booking->loai_phong_id)
                ->where('trang_thai', 'trong')
                ->count();
            \App\Models\LoaiPhong::where('id', $booking->loai_phong_id)
                ->update(['so_luong_trong' => $trongCount]);
        });
    }
}
