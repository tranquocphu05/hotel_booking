<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LoaiPhong;
use App\Models\Comment;

class Phong extends Model
{
    use HasFactory;

    protected $table = 'phong';

    protected $fillable = [
        'ten_phong',
        'mo_ta',
        'gia',
        'gia_goc',
        'gia_khuyen_mai',
        'co_khuyen_mai',
        'trang_thai',
        'loai_phong_id',
        'img',
        'dich_vu'
    ];

    public $timestamps = false;

    public function loaiPhong()
    {
        return $this->belongsTo(LoaiPhong::class, 'loai_phong_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'phong_id', 'id');
    }

    public function reviews()
    {
        return $this->comments();
    }

    /**
     * Lấy giá hiển thị (giá khuyến mãi nếu có, không thì giá gốc)
     */
    public function getGiaHienThiAttribute()
    {
        return $this->co_khuyen_mai && $this->gia_khuyen_mai ? $this->gia_khuyen_mai : $this->gia;
    }

    /**
     * Lấy giá gốc để hiển thị
     */
    public function getGiaGocHienThiAttribute()
    {
        return $this->gia_goc ?: $this->gia;
    }

    /**
     * Kiểm tra có khuyến mãi không
     */
    public function hasPromotion()
    {
        return $this->co_khuyen_mai && $this->gia_khuyen_mai && $this->gia_khuyen_mai < $this->gia_goc;
    }
}
