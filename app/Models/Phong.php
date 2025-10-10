<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phong extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'phong';

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
        'loai_phong_id',
        'ten_phong',
        'mo_ta',
        'gia',
        'trang_thai',
        'img'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'gia' => 'decimal:2'
    ];

    /**
     * Get the room type that owns the room.
     */
    public function loaiPhong()
    {
        return $this->belongsTo(LoaiPhong::class, 'loai_phong_id');
    }

    /**
     * Get the bookings for the room.
     */
    public function datPhong()
    {
        return $this->hasMany(DatPhong::class, 'phong_id');
    }

    /**
     * Scope a query to only include available rooms.
     */
    public function scopeTrong($query)
    {
        return $query->where('trang_thai', 'trong');
    }

    /**
     * Scope a query to only include booked rooms.
     */
    public function scopeDaDat($query)
    {
        return $query->where('trang_thai', 'da_dat');
    }

    /**
     * Scope a query to only include maintenance rooms.
     */
    public function scopeBaoTri($query)
    {
        return $query->where('trang_thai', 'bao_tri');
    }

    /**
     * Check if room is available
     */
    public function isTrong()
    {
        return $this->trang_thai === 'trong';
    }

    /**
     * Check if room is booked
     */
    public function isDaDat()
    {
        return $this->trang_thai === 'da_dat';
    }

    /**
     * Check if room is under maintenance
     */
    public function isBaoTri()
    {
        return $this->trang_thai === 'bao_tri';
    }
}