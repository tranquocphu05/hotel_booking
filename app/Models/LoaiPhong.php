<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoaiPhong extends Model
{
    //
    use HasFactory;

    protected $table = 'loai_phong';

    protected $fillable = [
        'ten_loai',
        'mo_ta',
        'gia_co_ban',
        'gia_khuyen_mai',    // Promotional price
        'so_luong_phong',    // Total number of rooms
        'so_luong_trong',    // Available rooms
        'diem_danh_gia',
        'so_luong_danh_gia',
        'trang_thai',
        'anh',
    ];

    // The loai_phong table currently doesn't have timestamp columns.
    // Disable automatic timestamps to avoid SQL errors when inserting.
    public $timestamps = false;

    /**
     * Relationship với bảng dat_phong
     */
    public function datPhongs()
    {
        return $this->hasMany(DatPhong::class, 'loai_phong_id');
    }

    /**
     * Relationship với bảng danh_gia (comments)
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'loai_phong_id');
    }

    /**
     * Check if room type has available rooms
     */
    public function hasAvailableRooms()
    {
        return $this->so_luong_trong > 0;
    }

    /**
     * Get percentage of rooms occupied
     */
    public function getOccupancyRateAttribute()
    {
        if ($this->so_luong_phong == 0) return 0;
        return round((($this->so_luong_phong - $this->so_luong_trong) / $this->so_luong_phong) * 100, 2);
    }

    /**
     * Get number of booked rooms
     */
    public function getRoomsDatAttribute()
    {
        return $this->so_luong_phong - $this->so_luong_trong;
    }

    /**
     * Kiểm tra xem loại phòng có đánh giá cao không (>= 4.5 sao)
     */
    public function hasHighRating()
    {
        return $this->diem_danh_gia >= 4.5;
    }

    /**
     * Lấy số sao hiển thị
     */
    public function getStarsAttribute()
    {
        return round($this->diem_danh_gia, 1);
    }

    /**
     * Lấy text mô tả đánh giá
     */
    public function getRatingTextAttribute()
    {
        if ($this->diem_danh_gia >= 4.8) return 'Ngoại lệ';
        if ($this->diem_danh_gia >= 4.5) return 'Tuyệt vời';
        if ($this->diem_danh_gia >= 4.0) return 'Rất tốt';
        if ($this->diem_danh_gia >= 3.5) return 'Tốt';
        if ($this->diem_danh_gia >= 3.0) return 'Khá tốt';
        return 'Trung bình';
    }

    /**
     * Get display price (promotional price if available, otherwise base price)
     */
    public function getGiaHienThiAttribute()
    {
        return $this->gia_khuyen_mai ?? $this->gia_co_ban;
    }

    /**
     * Check if room type has promotional price
     */
    public function hasPromotion()
    {
        return !is_null($this->gia_khuyen_mai) && $this->gia_khuyen_mai < $this->gia_co_ban;
    }
}
