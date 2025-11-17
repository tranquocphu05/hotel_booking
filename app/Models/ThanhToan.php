<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThanhToan extends Model
{
    use HasFactory;

    protected $table = 'thanh_toan';

    public $timestamps = false;

    protected $fillable = [
        'hoa_don_id',
        'so_tien',
        'ngay_thanh_toan',
        'trang_thai',
        'ghi_chu',
    ];

    protected $casts = [
        'ngay_thanh_toan' => 'datetime',
    ];

    public function hoaDon()
    {
        return $this->belongsTo(Invoice::class, 'hoa_don_id');
    }
}
