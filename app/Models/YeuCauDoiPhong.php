<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YeuCauDoiPhong extends Model
{
    protected $table = 'yeu_cau_doi_phong';

    protected $fillable = [
        'dat_phong_id',
        'phong_cu_id',
        'phong_moi_id',
        'ly_do',
        'trang_thai',      // cho_duyet, da_duyet, tu_choi
        'nguoi_duyet',     // id admin
        'ghi_chu_admin',
    ];

    public function datPhong()
    {
        return $this->belongsTo(DatPhong::class, 'dat_phong_id');
    }

    public function phongCu()
    {
        return $this->belongsTo(Phong::class, 'phong_cu_id');
    }

    public function phongMoi()
    {
        return $this->belongsTo(Phong::class, 'phong_moi_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'nguoi_duyet');
    }
}
