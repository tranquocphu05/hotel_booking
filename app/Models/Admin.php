<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admin extends Model
{
    protected $table = 'nguoi_dung';
    
    protected $fillable = [
        'username',
        'password',
        'email',
        'ho_ten',
        'sdt',
        'dia_chi',
        'cccd',
        'img',
        'vai_tro',
        'trang_thai'
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'ngay_tao' => 'datetime',
    ];

    /**
     * Quan hệ với News
     */
    public function news(): HasMany
    {
        return $this->hasMany(News::class, 'nguoi_dung_id');
    }

    /**
     * Scope để lấy admin
     */
    public function scopeAdmin($query)
    {
        return $query->where('vai_tro', 'admin');
    }
}
