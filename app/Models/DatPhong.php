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
        'loai_phong_id',  // Book by room type only (no specific room tracking)
        'so_luong_da_dat',  // Number of rooms booked in this booking
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
        'tong_tien' => 'decimal:2'
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

                // If changed from cancelled/failed to active -> decrease available rooms
                if (in_array($oldStatus, ['da_huy', 'tu_choi', 'thanh_toan_that_bai'])
                    && in_array($newStatus, ['cho_xac_nhan', 'da_xac_nhan'])) {
                    \App\Models\LoaiPhong::where('id', $booking->loai_phong_id)
                        ->decrement('so_luong_trong', $soLuong);
                }

                // If changed from active to cancelled/failed -> increase available rooms
                if (in_array($oldStatus, ['cho_xac_nhan', 'da_xac_nhan'])
                    && in_array($newStatus, ['da_huy', 'tu_choi', 'thanh_toan_that_bai'])) {
                    \App\Models\LoaiPhong::where('id', $booking->loai_phong_id)
                        ->increment('so_luong_trong', $soLuong);
                }
            }
        });

        // When booking is deleted, restore available room
        static::deleted(function ($booking) {
            if (in_array($booking->trang_thai, ['cho_xac_nhan', 'da_xac_nhan'])) {
                $soLuong = $booking->so_luong_da_dat ?? 1;
                \App\Models\LoaiPhong::where('id', $booking->loai_phong_id)
                    ->increment('so_luong_trong', $soLuong);
            }
        });
    }
}
