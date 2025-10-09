<?php

namespace App\Models;
use App\Models\LoaiPhong;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $table = 'voucher';

    protected $fillable = [
        'loai_phong_id',
        'ma_voucher',
        'gia_tri',
        'ngay_bat_dau',
        'ngay_ket_thuc',
        'so_luong',
        'dieu_kien',
        'trang_thai',
    ];

    public $timestamps = false;

    // Quan hệ với LoaiPhong
    public function loaiPhong()
    {
        return $this->belongsTo(LoaiPhong::class, 'loai_phong_id');
    }
}