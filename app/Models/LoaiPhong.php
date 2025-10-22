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
        'diem_danh_gia',
        'so_luong_danh_gia',
        'trang_thai',
        'anh',
    ];

    // The loai_phong table currently doesn't have timestamp columns.
    // Disable automatic timestamps to avoid SQL errors when inserting.
    public $timestamps = false;

    /**
     * Relationship với bảng phong
     */
    public function phongs()
    {
        return $this->hasMany(Phong::class, 'loai_phong_id');
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
}