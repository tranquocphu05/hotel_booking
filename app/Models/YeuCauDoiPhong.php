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
        'phi_doi_phong',   // Phí đổi phòng (50% chênh lệch giá + 150k/đêm)
        'so_nguoi_moi',    // Số người lớn mới (nếu thêm người khi đổi phòng)
        'so_tre_em_moi',   // Số trẻ em mới
        'so_em_be_moi',    // Số em bé mới
        'so_nguoi_ban_dau', // Số người lớn ban đầu (trước khi đổi phòng)
        'so_tre_em_ban_dau', // Số trẻ em ban đầu
        'so_em_be_ban_dau',  // Số em bé ban đầu
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
