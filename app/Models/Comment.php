<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'danh_gia';

    protected $fillable = [
        'nguoi_dung_id',
        'phong_id',
        'noi_dung',
        'so_sao',
        'img',
        'ngay_danh_gia',
        'trang_thai',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'nguoi_dung_id');
    }

    public function room()
    {
        return $this->belongsTo(\App\Models\Phong::class, 'phong_id', 'id');
    }
}
