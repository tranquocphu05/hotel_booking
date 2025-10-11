<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phong extends Model
{
    use HasFactory;

    protected $table = 'phong';

    protected $fillable = [
        'ten_phong',
        'mo_ta',
        'gia',
        'trang_thai',
        'loai_phong_id',
        'img'
    ];

    public $timestamps = false;

    public function loaiPhong()
    {
        return $this->belongsTo(LoaiPhong::class, 'loai_phong_id');
    }
}
