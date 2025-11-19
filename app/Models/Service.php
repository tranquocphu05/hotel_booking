<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Service extends Model
{
    
    use HasFactory;

    protected $table = 'services';

    protected $fillable = [
        'name',
        'price',
        'unit',
        'loai',
        'anh',
        'describe',
        'status',
    ];

    // Quan hệ: 1 dịch vụ có thể được dùng trong nhiều booking
    public function bookingServices()
    {
        return $this->hasMany(BookingService::class, 'service_id');
    }

    /**
     * Scope for active services
     */
    public function scopeHoatDong($query)
    {
        return $query->where('status', 'hoat_dong');
    }

}
