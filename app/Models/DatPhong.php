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
        'ghi_chu_hoan_tien',
        'username',
        'email',
        'sdt',
        'cccd',
        // Check-in/Check-out fields
        'thoi_gian_checkin',
        'thoi_gian_checkout',
        'nguoi_checkin',
        'nguoi_checkout',
        'phi_phat_sinh',
        'ghi_chu_checkin',
        'ghi_chu_checkout',
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
        'phi_phat_sinh' => 'decimal:2',
        'thoi_gian_checkin' => 'datetime',
        'thoi_gian_checkout' => 'datetime',
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
     * Get all rooms assigned to this booking via pivot table
     * Many-to-Many relationship through booking_rooms pivot table
     */
    public function phongs()
    {
        return $this->belongsToMany(Phong::class, 'booking_rooms', 'dat_phong_id', 'phong_id')
            ->withTimestamps();
    }

    /**
     * Get all room types in this booking via pivot table
     * Many-to-Many relationship through booking_room_types pivot table
     */
    public function roomTypes()
    {
        return $this->belongsToMany(LoaiPhong::class, 'booking_room_types', 'dat_phong_id', 'loai_phong_id')
            ->withPivot('so_luong', 'gia_rieng')
            ->withTimestamps();
    }

    /**
     * Get array of assigned room IDs from pivot table
     * Returns array of room IDs: [1, 2, 3]
     */
    public function getPhongIds()
    {
        // Get from pivot table
        $phongIds = $this->phongs()->pluck('phong_id')->toArray();
        
        // Fallback: If no rooms in pivot, try legacy phong_id
        if (empty($phongIds) && $this->phong_id) {
            return [$this->phong_id];
        }

        return $phongIds;
    }

    /**
     * Get assigned Phong models from pivot table
     */
    public function getAssignedPhongs()
    {
        return $this->phongs;
    }

    /**
     * Add a room to booking via pivot table
     */
    public function addPhong($phongId)
    {
        // Check if already attached
        if (!$this->phongs()->where('phong_id', $phongId)->exists()) {
            $this->phongs()->attach($phongId);
        }
        return $this;
    }

    /**
     * Remove a room from booking via pivot table
     */
    public function removePhong($phongId)
    {
        $this->phongs()->detach($phongId);
        return $this;
    }

    /**
     * Sync rooms with booking (replace all rooms)
     */
    public function syncPhongs(array $phongIds)
    {
        $this->phongs()->sync($phongIds);
        return $this;
    }

    /**
     * Get all room types in this booking from pivot table
     * Returns collection with loai_phong_id, so_luong, gia_rieng
     */
    public function getRoomTypes()
    {
        $roomTypes = $this->roomTypes()->get();
        
        // If no room types in pivot, fallback to legacy single room type
        if ($roomTypes->isEmpty() && $this->loai_phong_id) {
            return collect([[
                'loai_phong_id' => $this->loai_phong_id,
                'so_luong' => $this->so_luong_da_dat ?? 1,
                'gia_rieng' => $this->tong_tien ?? 0,
            ]]);
        }

        // Transform to array format for compatibility
        return $roomTypes->map(function($roomType) {
            return [
                'loai_phong_id' => $roomType->id,
                'so_luong' => $roomType->pivot->so_luong,
                'gia_rieng' => $roomType->pivot->gia_rieng,
            ];
        });
    }

    /**
     * Add room type to booking via pivot table
     */
    public function addRoomType($loaiPhongId, $soLuong, $giaRieng)
    {
        $this->roomTypes()->attach($loaiPhongId, [
            'so_luong' => $soLuong,
            'gia_rieng' => $giaRieng,
        ]);
        return $this;
    }

    /**
     * Sync room types with booking (replace all room types)
     */
    public function syncRoomTypes(array $roomTypesData)
    {
        $this->roomTypes()->sync($roomTypesData);
        return $this;
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
     * @deprecated Use whereHas('phongs', function($q) use ($phongId) { $q->where('phong_id', $phongId); }) instead
     * 
     * Legacy scope: safely filter bookings that contain a specific room id
     * Falls back to legacy `phong_id` when `phong_ids` JSON column is not present.
     * Kept for backward compatibility only. System now uses pivot table booking_rooms.
     * 
     * Usage: DatPhong::whereContainsPhongId($phongId)->get();
     */
    public function scopeWhereContainsPhongId($query, $phongId)
    {
        $table = $query->getModel()->getTable();
        if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'phong_ids')) {
            return $query->whereJsonContains('phong_ids', $phongId);
        }

        return $query->where('phong_id', $phongId);
    }

    /**
     * @deprecated Use orWhereHas('phongs', function($q) use ($phongId) { $q->where('phong_id', $phongId); }) instead
     * 
     * Legacy scope: safely add an OR condition for bookings that contain a specific room id
     * Falls back to legacy `phong_id` when `phong_ids` JSON column is not present.
     * Kept for backward compatibility only. System now uses pivot table booking_rooms.
     * 
     * Usage: DatPhong::orWhereContainsPhongId($phongId)
     */
    public function scopeOrWhereContainsPhongId($query, $phongId)
    {
        $table = $query->getModel()->getTable();
        if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'phong_ids')) {
            return $query->orWhere(function ($q) use ($phongId) {
                $q->whereJsonContains('phong_ids', $phongId);
            });
        }

        return $query->orWhere('phong_id', $phongId);
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
     * Check if guest can request services (checked in but not checked out)
     */
    public function canRequestService()
    {
        return $this->thoi_gian_checkin 
            && !$this->thoi_gian_checkout
            && $this->trang_thai === 'da_xac_nhan';
    }

    /**
     * Check if booking can be checked in
     */
    public function canCheckin()
    {
        return $this->trang_thai === 'da_xac_nhan' 
            && !$this->thoi_gian_checkin;
    }

    /**
     * Check if booking can be checked out
     */
    public function canCheckout()
    {
        return $this->thoi_gian_checkin 
            && !$this->thoi_gian_checkout;
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
                // Recalculate cho TẤT CẢ loại phòng trong booking
                $roomTypes = $booking->getRoomTypes();
                $loaiPhongIdsToUpdate = [];
                
                foreach ($roomTypes as $roomType) {
                    if (isset($roomType['loai_phong_id'])) {
                        $loaiPhongIdsToUpdate[] = $roomType['loai_phong_id'];
                    }
                }
                
                // Thêm loai_phong_id chính nếu chưa có
                if ($booking->loai_phong_id && !in_array($booking->loai_phong_id, $loaiPhongIdsToUpdate)) {
                    $loaiPhongIdsToUpdate[] = $booking->loai_phong_id;
                }
                
                // Recalculate cho tất cả
                foreach (array_unique($loaiPhongIdsToUpdate) as $loaiPhongId) {
                    $trongCount = \App\Models\Phong::where('loai_phong_id', $loaiPhongId)
                        ->where('trang_thai', 'trong')
                        ->count();
                    LoaiPhong::where('id', $loaiPhongId)
                        ->update(['so_luong_trong' => $trongCount]);
                }

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
                        // CRITICAL FIX: Kiểm tra xem phòng có đang được đặt cho booking khác không
                        // Sử dụng pivot table thay vì JSON field
                                        $hasOtherBooking = \App\Models\DatPhong::where('id', '!=', $booking->id)
                                                        ->whereHas('phongs', function($q) use ($phong) {
                                                                $q->where('phong_id', $phong->id);
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
                    foreach (array_unique($loaiPhongIdsToUpdate) as $loaiPhongId) {
                        $trongCount = \App\Models\Phong::where('loai_phong_id', $loaiPhongId)
                            ->where('trang_thai', 'trong')
                            ->count();
                        LoaiPhong::where('id', $loaiPhongId)
                            ->update(['so_luong_trong' => $trongCount]);
                    }
                }
            }
        });

        // When booking is deleted, recalculate so_luong_trong
        static::deleted(function ($booking) {
            $roomTypes = $booking->getRoomTypes();
            $loaiPhongIdsToUpdate = [];
            
            foreach ($roomTypes as $roomType) {
                if (isset($roomType['loai_phong_id'])) {
                    $loaiPhongIdsToUpdate[] = $roomType['loai_phong_id'];
                }
            }
            
            if ($booking->loai_phong_id && !in_array($booking->loai_phong_id, $loaiPhongIdsToUpdate)) {
                $loaiPhongIdsToUpdate[] = $booking->loai_phong_id;
            }
            
            foreach (array_unique($loaiPhongIdsToUpdate) as $loaiPhongId) {
                $trongCount = \App\Models\Phong::where('loai_phong_id', $loaiPhongId)
                    ->where('trang_thai', 'trong')
                    ->count();
                LoaiPhong::where('id', $loaiPhongId)
                    ->update(['so_luong_trong' => $trongCount]);
            }
        });
    }

    /**
     * Get all room type IDs affected by this booking
     * Includes both primary loai_phong_id and all room types in room_types JSON
     *
     * @param DatPhong $booking
     * @return array
     */
    protected static function getAffectedRoomTypeIds($booking): array
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
     * Recalculate so_luong_trong for a room type based on actual room status
     *
     * @param int $loaiPhongId
     * @return void
     */
    protected static function recalculateSoLuongTrong(int $loaiPhongId): void
    {
        $trongCount = \App\Models\Phong::where('loai_phong_id', $loaiPhongId)
            ->where('trang_thai', 'trong')
            ->count();
        
        LoaiPhong::where('id', $loaiPhongId)
            ->update(['so_luong_trong' => $trongCount]);
    }
}
