<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'hoa_don';

    const CREATED_AT = 'ngay_tao';
    const UPDATED_AT = null;

    protected $fillable = [
        'dat_phong_id',
        'tong_tien',
        'phuong_thuc',
        'trang_thai',
    ];

    public function datPhong()
    {
        return $this->belongsTo(DatPhong::class, 'dat_phong_id');
    }
    

}
