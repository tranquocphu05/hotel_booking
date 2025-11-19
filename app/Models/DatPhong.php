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
        // 'room_types',  // DEPRECATED - now using booking_room_types pivot table
        // 'phong_ids',  // DEPRECATED - now using booking_rooms pivot table
        'so_luong_da_dat',  // Number of rooms booked in this booking
        'phong_id',  // Specific room assigned (nullable, legacy support)
        'ngay_dat',
        'ngay_nhan',
        'ngay_tra',
        'so_nguoi',
        'trang_thai',
        'tong_tien',
        'tien_phong',  // Room total calculated by BookingPriceCalculator
        'tong_tien_dich_vu',  // Service total calculated by BookingPriceCalculator
        'voucher_id',
        'ly_do_huy',
        'ngay_huy',
        'ghi_chu_hoan_tien',
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
        // JSON fields deprecated - now using pivot tables
        // 'room_types' => 'array',
        // 'phong_ids' => 'array',
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
     * Get all room types for this booking via pivot table.
     * Returns relationship with pivot data: so_luong, gia_rieng
     */
    public function roomTypes()
    {
        return $this->belongsToMany(LoaiPhong::class, 'booking_room_types', 'dat_phong_id', 'loai_phong_id')
            ->withPivot('so_luong', 'gia_rieng')
            ->withTimestamps();
    }

    /**
     * Get all assigned rooms for this booking via pivot table.
     */
    public function assignedRooms()
    {
        return $this->belongsToMany(Phong::class, 'booking_rooms', 'dat_phong_id', 'phong_id')
            ->withTimestamps();
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
     * Get array of assigned room IDs.
     * Now uses pivot table instead of JSON.
     */
    public function getPhongIds()
    {
        // Use pivot table
        return $this->assignedRooms()->pluck('phong_id')->toArray();
    }

    /**
     * Get assigned Phong models.
     * Now uses pivot table relationship.
     */
    public function getAssignedPhongs()
    {
        return $this->assignedRooms;
    }

    /**
     * Set phong_ids array.
     * Now syncs with pivot table.
     */
    public function setPhongIds(array $phongIds)
    {
        $this->assignedRooms()->sync($phongIds);
        return $this;
    }

    /**
     * Add a room ID to assigned rooms.
     * Now uses pivot table.
     */
    public function addPhongId($phongId)
    {
        $this->assignedRooms()->syncWithoutDetaching([$phongId]);
        return $this;
    }

    /**
     * Remove a room ID from assigned rooms.
     * Now uses pivot table.
     */
    public function removePhongId($phongId)
    {
        $this->assignedRooms()->detach($phongId);
        return $this;
    }

    /**
     * Get all room types in this booking.
     * Now uses pivot table instead of JSON.
     */
    public function getRoomTypes()
    {
        return $this->roomTypes->map(function($loaiPhong) {
            return [
                'loai_phong_id' => $loaiPhong->id,
                'so_luong' => $loaiPhong->pivot->so_luong,
                'gia_rieng' => $loaiPhong->pivot->gia_rieng,
            ];
        })->toArray();
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
     * Get all booking services for this booking.
     */
    public function services()
    {
        return $this->hasMany(BookingService::class, 'dat_phong_id');
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
                LoaiPhong::where('id', $booking->loai_phong_id)
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
                            ->whereHas('assignedRooms', function($query) use ($phong) {
                                $query->where('phong_id', $phong->id);
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
                    LoaiPhong::where('id', $booking->loai_phong_id)
                        ->update(['so_luong_trong' => $trongCount]);
                }
            }
        });

        // When booking is deleted, recalculate so_luong_trong
        static::deleted(function ($booking) {
            $trongCount = \App\Models\Phong::where('loai_phong_id', $booking->loai_phong_id)
                ->where('trang_thai', 'trong')
                ->count();
            LoaiPhong::where('id', $booking->loai_phong_id)
                ->update(['so_luong_trong' => $trongCount]);
        });
    }
}
