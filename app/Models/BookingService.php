<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingService extends Model
{
    use HasFactory;
    protected $table = 'booking_services';
    protected $fillable = [
        'dat_phong_id',
        'service_id',
        'quantity',
        'unit_price',
        'used_at',
        'note',
    ]; // Mỗi dòng dịch vụ thuộc về một đặt phòng 
    public function dat_phong()
    {
        return $this->belongsTo(DatPhong::class, 'dat_phong_id');
    } // Mỗi dòng dịch vụ thuộc về một loại dịch vụ public function service() { return $this->belongsTo(Service::class, 'service_id'); } // Tính tổng tiền của dòng dịch vụ (quantity * unit_price) public function getTotalAttribute() { return $this->quantity * $this->unit_price; }
}
